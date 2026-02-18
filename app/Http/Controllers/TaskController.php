<?php

namespace App\Http\Controllers;

use App\Models\MainTask;
use App\Models\TaskActivity;
use App\Models\ActivityAssignment;
use App\Models\ActivityReport;
use App\Models\ActivityReportAttachment;
use App\Models\TaskCategory;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Check if user is a manager (General Manager, HOD, Branch Manager, System Admin, etc.)
     */
    private function isManager($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user) {
            return false;
        }
        
        $userRoleNames = method_exists($user, 'roles') ? $user->roles()->pluck('name')->toArray() : [];
        
        // Check for manager roles (including General Manager and Branch Manager)
        $managerRoles = ['System Admin', 'General Manager', 'HOD', 'Manager', 'Director', 'CEO', 'HR Officer', 'Branch Manager'];
        $isManager = count(array_intersect($userRoleNames, $managerRoles)) > 0;
        
        // Also check if user is a branch manager via managedBranches relationship
        if (!$isManager && method_exists($user, 'managedBranches')) {
            $isManager = $user->managedBranches()->exists();
        }
        
        return $isManager;
    }

    /**
     * Check if user can create tasks (only HOD, CEO, or HR Officer)
     */
    private function canCreateTask($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user) {
            return false;
        }
        
        $userRoleNames = method_exists($user, 'roles') ? $user->roles()->pluck('name')->toArray() : [];
        
        // Only HOD, CEO, and HR Officer can create tasks
        $allowedRoles = ['HOD', 'CEO', 'HR Officer', 'System Admin'];
        return count(array_intersect($userRoleNames, $allowedRoles)) > 0;
    }
    public function create()
    {
        $user = Auth::user();
        $canCreateTask = $this->canCreateTask($user);
        
        if (!$canCreateTask) {
            abort(403, 'Only HOD, CEO, or HR Officer can create tasks and assign them to staff');
        }
        
        $users = User::orderBy('name')->get(['id','name']);
        $categories = TaskCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('modules.tasks.create', [
            'users' => $users,
            'categories' => $categories,
        ]);
    }

    /**
     * Display the specified task
     */
    public function show($id)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $task = MainTask::with([
            'teamLeader:id,name,email',
            'activities.assignedUsers:id,name,email',
            'activities.reports.user:id,name',
            'activities.reports.approver:id,name',
            'activities.reports.attachments',
            'creator:id,name'
        ])->findOrFail($id);

        // Get all reports for this task (from all activities)
        $allReports = ActivityReport::with([
            'user:id,name,email',
            'approver:id,name',
            'activity:id,name,main_task_id',
            'attachments'
        ])
        ->whereHas('activity', function($query) use ($id) {
            $query->where('main_task_id', $id);
        })
        ->orderByDesc('report_date')
        ->orderByDesc('created_at')
        ->get();

        // Check permissions - staff can only view tasks they're assigned to
        if (!$isManager) {
            $canView = $task->team_leader_id == $user->id || 
                      $task->activities()->whereHas('assignedUsers', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      })->exists();
            
            if (!$canView) {
                abort(403, 'You do not have permission to view this task');
            }
        }

        $canEdit = $isManager || $task->team_leader_id == $user->id;
        $categories = TaskCategory::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get(['id','name']);
        
        // Prepare flat activities list for the modal
        $flatActivities = [];
        foreach($task->activities as $activity) {
            $flatActivities[] = [
                'id' => $activity->id,
                'name' => $activity->name,
                'task' => $task->name
            ];
        }

        return view('modules.tasks.show', compact('task', 'isManager', 'canEdit', 'categories', 'users', 'flatActivities', 'allReports'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit($id)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $task = MainTask::with([
            'teamLeader',
            'activities.assignedUsers:id,name',
            'activities.reports',
            'creator'
        ])->findOrFail($id);

        // Check permissions
        if (!$isManager && $task->team_leader_id != $user->id) {
            abort(403, 'You do not have permission to edit this task');
        }

        $users = User::orderBy('name')->get(['id','name']);
        $categories = TaskCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modules.tasks.edit', compact('task', 'users', 'categories', 'isManager'));
    }

    /**
     * Display analytics page
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        // Apply filters
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $statusFilter = $request->query('status');
        $priorityFilter = $request->query('priority');

        // Get statistics
        $query = MainTask::with(['activities', 'teamLeader']);
        if (!$isManager) {
            $query->where(function($q) use ($user) {
                $q->where('team_leader_id', $user->id)
                  ->orWhereHas('activities.assignedUsers', function($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  });
            });
        }

        // Apply filters
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($priorityFilter) {
            $query->where('priority', $priorityFilter);
        }

        $baseQuery = clone $query;
        $tasks = $baseQuery->get();

        // Calculate statistics
        $totalActivities = TaskActivity::whereHas('mainTask', function($q) use ($query) {
            $q->whereIn('id', (clone $query)->pluck('id'));
        })->count();

        $totalReports = ActivityReport::whereHas('activity.mainTask', function($q) use ($query) {
            $q->whereIn('id', (clone $query)->pluck('id'));
        })->count();

        $pendingReports = ActivityReport::whereHas('activity.mainTask', function($q) use ($query) {
            $q->whereIn('id', (clone $query)->pluck('id'));
        })->where('status', 'Pending')->count();

        $avgProgress = $tasks->whereNotNull('progress_percentage')->avg('progress_percentage') ?? 0;

        $stats = [
            'total' => $tasks->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'on_hold' => $tasks->where('status', 'on_hold')->count(),
            'overdue' => $tasks->filter(function($task) {
                return $task->status != 'completed' && 
                       $task->end_date && 
                       $task->end_date < now();
            })->count(),
            'total_activities' => $totalActivities,
            'total_reports' => $totalReports,
            'pending_reports' => $pendingReports,
            'avg_progress' => round($avgProgress, 1),
            'completion_rate' => $tasks->count() > 0 ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 1) : 0,
            'on_time_rate' => $tasks->where('status', 'completed')->count() > 0 ? 
                round(($tasks->where('status', 'completed')->filter(function($task) {
                    return $task->end_date && $task->end_date >= $task->updated_at;
                })->count() / $tasks->where('status', 'completed')->count()) * 100, 1) : 0,
            'avg_duration' => $tasks->where('status', 'completed')->filter(function($task) {
                return $task->start_date && $task->end_date;
            })->map(function($task) {
                return \Carbon\Carbon::parse($task->start_date)->diffInDays(\Carbon\Carbon::parse($task->end_date));
            })->avg() ?? 0,
            'active_users' => User::whereHas('assignedActivities', function($q) use ($query) {
                $q->whereHas('mainTask', function($subQ) use ($query) {
                    $subQ->whereIn('id', (clone $query)->pluck('id'));
                });
            })->distinct()->count(),
        ];

        // Get tasks by priority
        $byPriority = $tasks->groupBy('priority')->map->count();

        // Get tasks by category
        $byCategory = $tasks->whereNotNull('category')->groupBy('category')->map->count();

        // Get tasks by status
        $byStatus = $tasks->groupBy('status')->map->count();

        // Get progress distribution
        $byProgress = [
            '0-25%' => $tasks->whereBetween('progress_percentage', [0, 25])->count(),
            '26-50%' => $tasks->whereBetween('progress_percentage', [26, 50])->count(),
            '51-75%' => $tasks->whereBetween('progress_percentage', [51, 75])->count(),
            '76-99%' => $tasks->whereBetween('progress_percentage', [76, 99])->count(),
            '100%' => $tasks->where('progress_percentage', 100)->count(),
        ];

        // Top team leaders
        $topLeaders = $tasks->whereNotNull('team_leader_id')
            ->groupBy('team_leader_id')
            ->map(function($leaderTasks, $leaderId) {
                $leader = \App\Models\User::find($leaderId);
                if (!$leader) return null;
                
                return [
                    'id' => $leaderId,
                    'name' => $leader->name,
                    'total_tasks' => $leaderTasks->count(),
                    'completed_tasks' => $leaderTasks->where('status', 'completed')->count(),
                    'avg_progress' => round($leaderTasks->whereNotNull('progress_percentage')->avg('progress_percentage') ?? 0, 1),
                ];
            })
            ->filter()
            ->sortByDesc('total_tasks')
            ->take(5)
            ->values();

        // Recent activity
        $recentActivity = ActivityReport::with(['activity.mainTask:id,name', 'user:id,name'])
            ->whereHas('activity.mainTask', function($q) use ($query) {
                $q->whereIn('id', (clone $query)->pluck('id'));
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function($report) {
                return [
                    'date' => $report->created_at->format('M d, Y H:i'),
                    'task' => $report->activity->mainTask->name ?? 'N/A',
                    'activity' => $report->activity->name ?? 'N/A',
                    'status' => ucfirst($report->status),
                    'status_badge' => $report->status == 'Approved' ? 'success' : ($report->status == 'Pending' ? 'warning' : 'danger'),
                ];
            });

        return view('modules.tasks.analytics', compact(
            'stats', 
            'byPriority', 
            'byCategory', 
            'byStatus',
            'byProgress',
            'topLeaders',
            'recentActivity',
            'isManager'
        ));
    }

    /**
     * Display categories management page
     */
    public function categories()
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        if (!$isManager) {
            abort(403, 'You do not have permission to manage categories');
        }

        $categories = TaskCategory::withCount('tasks')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modules.tasks.categories', compact('categories'));
    }

    /**
     * Display report progress page for a specific activity
     */
    public function reportProgress($activityId)
    {
        $user = Auth::user();
        $activity = TaskActivity::with([
            'mainTask.teamLeader:id,name',
            'assignedUsers:id,name,email',
            'reports.user:id,name',
            'reports.attachments'
        ])->findOrFail($activityId);

        // Check if user is assigned to this activity
        $isAssigned = $activity->assignedUsers()->where('user_id', $user->id)->exists();
        if (!$isAssigned) {
            abort(403, 'You are not assigned to this activity.');
        }

        // Get recent reports for this activity
        $recentReports = $activity->reports()
            ->with(['user:id,name', 'approver:id,name', 'attachments'])
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('modules.tasks.report-progress', compact('activity', 'recentReports', 'user'));
    }

    /**
     * Show the form for creating a new activity for a task
     */
    public function createActivity($taskId)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $task = MainTask::with('teamLeader:id,name')->findOrFail($taskId);

        // Check permissions
        $canCreate = $isManager || $task->team_leader_id == $user->id;
        if (!$canCreate) {
            abort(403, 'You do not have permission to create activities for this task');
        }

        $users = User::orderBy('name')->get(['id','name']);
        $activities = TaskActivity::where('main_task_id', $taskId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.tasks.activities.create', compact('task', 'users', 'activities', 'isManager', 'user'));
    }

    /**
     * Display detailed view of a specific activity
     */
    public function showActivity($activityId)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $activity = TaskActivity::with([
            'mainTask.teamLeader:id,name',
            'assignedUsers:id,name,email',
            'reports.user:id,name',
            'reports.approver:id,name',
            'reports.attachments',
            'attachments',
            'comments.user:id,name',
            'dependsOn:id,name',
            'dependents:id,name'
        ])->findOrFail($activityId);

        // Check permissions
        $canView = $isManager || 
                  $activity->mainTask->team_leader_id == $user->id ||
                  $activity->assignedUsers()->where('user_id', $user->id)->exists();
        
        if (!$canView) {
            abort(403, 'You do not have permission to view this activity');
        }

        $canEdit = $isManager || $activity->mainTask->team_leader_id == $user->id;

        return view('modules.tasks.activities.show', compact('activity', 'canEdit', 'isManager', 'user'));
    }

    /**
     * Show the form for editing a specific activity
     */
    public function editActivity($activityId)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $activity = TaskActivity::with([
            'mainTask.teamLeader:id,name',
            'assignedUsers:id,name,email',
            'attachments',
            'dependsOn:id,name'
        ])->findOrFail($activityId);

        // Check permissions
        $canEdit = $isManager || $activity->mainTask->team_leader_id == $user->id;
        
        if (!$canEdit) {
            abort(403, 'You do not have permission to edit this activity');
        }

        $users = User::orderBy('name')->get(['id','name']);
        $activities = TaskActivity::where('main_task_id', $activity->main_task_id)
            ->where('id', '!=', $activity->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.tasks.activities.edit', compact('activity', 'users', 'activities', 'isManager', 'user'));
    }

    /**
     * Display detailed view of a specific report
     */
    public function showReport($id)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $report = ActivityReport::with([
            'user:id,name,email',
            'approver:id,name,email',
            'activity:id,name,main_task_id',
            'activity.mainTask:id,name,team_leader_id',
            'activity.assignedUsers:id,name,email',
            'attachments'
        ])->findOrFail($id);

        // Check permissions
        // Managers can view all reports
        // Staff can only view reports for activities they're assigned to or their own reports
        if (!$isManager) {
            $canView = $report->user_id == $user->id || 
                      $report->activity->assignedUsers()->where('user_id', $user->id)->exists();
            
            if (!$canView) {
                abort(403, 'You do not have permission to view this report');
            }
        }

        // Get related reports for the same activity
        $relatedReports = ActivityReport::with(['user:id,name', 'approver:id,name'])
            ->where('activity_id', $report->activity_id)
            ->where('id', '!=', $report->id)
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Check if user can approve this report
        $canApprove = $isManager && $report->status === 'Pending';
        $canReject = $isManager && $report->status === 'Pending';

        return view('modules.tasks.report-show', compact('report', 'relatedReports', 'canApprove', 'canReject', 'isManager', 'user'));
    }

    /**
     * Display pending approval reports page for managers
     */
    public function pendingApprovalReports(Request $request)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);
        
        if (!$isManager) {
            abort(403, 'Only managers can view pending approval reports');
        }

        $query = ActivityReport::with([
            'user:id,name,email',
            'activity:id,name,main_task_id',
            'activity.mainTask:id,name,team_leader_id',
            'activity.assignedUsers:id,name',
        ])->where('status', 'Pending');

        // Filter by manager's scope
        // HOD can only see reports from their department
        if ($user->hasRole('HOD') && !$user->hasAnyRole(['System Admin', 'General Manager'])) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }

        // Branch Manager can only see reports from their branch
        if ($user->managedBranches()->exists() && !$user->hasAnyRole(['System Admin', 'General Manager', 'HOD'])) {
            $branchIds = $user->managedBranches()->pluck('branches.id')->toArray();
            $query->whereHas('user', function($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
            });
        }

        // Search filter
        $searchFilter = $request->query('search', '');
        if ($searchFilter) {
            $query->where(function($q) use ($searchFilter) {
                $q->where('work_description', 'like', "%{$searchFilter}%")
                  ->orWhereHas('activity', function($subQ) use ($searchFilter) {
                      $subQ->where('name', 'like', "%{$searchFilter}%");
                  })
                  ->orWhereHas('activity.mainTask', function($subQ) use ($searchFilter) {
                      $subQ->where('name', 'like', "%{$searchFilter}%");
                  })
                  ->orWhereHas('user', function($subQ) use ($searchFilter) {
                      $subQ->where('name', 'like', "%{$searchFilter}%");
                  });
            });
        }

        $reports = $query->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_pending' => ActivityReport::where('status', 'Pending')->count(),
            'pending_today' => ActivityReport::where('status', 'Pending')
                ->whereDate('created_at', today())
                ->count(),
            'pending_this_week' => ActivityReport::where('status', 'Pending')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return view('modules.tasks.pending-approval-reports', compact('reports', 'stats', 'isManager', 'searchFilter'));
    }

    /**
     * Display all progress reports page
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        // Filters
        $statusFilter = $request->query('status', '');
        $completionStatusFilter = $request->query('completion_status', '');
        $searchFilter = $request->query('search', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');
        $taskIdFilter = $request->query('task_id');
        $activityIdFilter = $request->query('activity_id');

        $query = ActivityReport::with([
            'user:id,name,email',
            'approver:id,name',
            'activity:id,name,main_task_id',
            'activity.mainTask:id,name,team_leader_id',
        ]);

        // Filter by task_id if provided
        if ($taskIdFilter) {
            $query->whereHas('activity', function($q) use ($taskIdFilter) {
                $q->where('main_task_id', $taskIdFilter);
            });
        }

        // Filter by activity_id if provided
        if ($activityIdFilter) {
            $query->where('activity_id', $activityIdFilter);
        }

        // If not manager, only show reports for activities they're assigned to
        if (!$isManager) {
            $query->whereHas('activity.assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhere('user_id', $user->id);
        }

        // Apply filters
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($completionStatusFilter) {
            $query->where('completion_status', $completionStatusFilter);
        }

        if ($searchFilter) {
            $query->where(function($q) use ($searchFilter) {
                $q->where('work_description', 'like', "%{$searchFilter}%")
                  ->orWhereHas('activity', function($subQ) use ($searchFilter) {
                      $subQ->where('name', 'like', "%{$searchFilter}%");
                  })
                  ->orWhereHas('activity.mainTask', function($subQ) use ($searchFilter) {
                      $subQ->where('name', 'like', "%{$searchFilter}%");
                  })
                  ->orWhereHas('user', function($subQ) use ($searchFilter) {
                      $subQ->where('name', 'like', "%{$searchFilter}%");
                  });
            });
        }

        if ($dateFrom) {
            $query->where('report_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('report_date', '<=', $dateTo);
        }

        $reports = $query->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => ActivityReport::count(),
            'pending' => ActivityReport::where('status', 'Pending')->count(),
            'approved' => ActivityReport::where('status', 'Approved')->count(),
            'rejected' => ActivityReport::where('status', 'Rejected')->count(),
            'in_progress' => ActivityReport::where('completion_status', 'In Progress')->count(),
            'completed' => ActivityReport::where('completion_status', 'Completed')->count(),
            'delayed' => ActivityReport::where('completion_status', 'Delayed')->count(),
        ];

        return view('modules.tasks.reports', compact('reports', 'stats', 'isManager', 'statusFilter', 'completionStatusFilter', 'searchFilter', 'dateFrom', 'dateTo', 'taskIdFilter', 'activityIdFilter'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);

        $statusFilter = $request->query('status', '');
        $leaderFilter = $request->query('leader', '');
        $priorityFilter = $request->query('priority', '');
        $searchFilter = $request->query('search', '');

        $query = MainTask::with(['teamLeader', 'activities']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($priorityFilter) {
            $query->where('priority', $priorityFilter);
        }

        if ($searchFilter) {
            $query->where(function($q) use ($searchFilter) {
                $q->where('name', 'like', "%{$searchFilter}%")
                  ->orWhere('description', 'like', "%{$searchFilter}%")
                  ->orWhere('category', 'like', "%{$searchFilter}%");
            });
        }

        if ($isManager && $leaderFilter) {
            $query->where('team_leader_id', $leaderFilter);
        }

        if ($isManager) {
            $mainTasks = $query->orderByDesc('created_at')->get();
        } else {
            // Staff view - only tasks they're assigned to or leading
            $mainTasks = MainTask::with(['teamLeader', 'activities'])
                ->where(function($q) use ($user) {
                    $q->where('team_leader_id', $user->id)
                      ->orWhereHas('activities.assignedUsers', function($subQ) use ($user) {
                          $subQ->where('user_id', $user->id);
                      });
                })
                ->when($statusFilter, function($q) use ($statusFilter) {
                    $q->where('status', $statusFilter);
                })
                ->orderByDesc('created_at')
                ->get();
        }

        $users = User::orderBy('name')->get(['id','name']);

        // Calculate dashboard stats
        $dashboardStats = [];
        if ($isManager) {
            $dashboardStats = [
                'total' => $mainTasks->count(),
                'in_progress' => $mainTasks->where('status', 'in_progress')->count(),
                'completed' => $mainTasks->where('status', 'completed')->count(),
                'overdue' => $mainTasks->filter(function($task) {
                    return !empty($task->end_date) && 
                           $task->end_date < now() && 
                           $task->status !== 'completed';
                })->count(),
            ];
        } else {
            $pendingActivities = TaskActivity::whereHas('assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['Not Started', 'In Progress'])->count();

            $overdueActivities = TaskActivity::whereHas('assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', '!=', 'Completed')
              ->whereNotNull('end_date')
              ->where('end_date', '<', now())
              ->count();

            $dashboardStats = [
                'total_tasks' => $mainTasks->count(),
                'pending' => $pendingActivities,
                'overdue' => $overdueActivities,
            ];
        }

        // Eager load extra context for the new simplified UI
        $mainTasks->load([
            'teamLeader:id,name',
            'activities.assignedUsers:id,name',
            'activities.reports' => function ($q) {
                $q->latest('report_date')->latest();
            },
        ]);

        // Flatten activities for quick selects in the UI
        $flatActivities = $mainTasks->flatMap(function ($task) {
            return $task->activities->map(function ($activity) use ($task) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'task' => $task->name,
                    'task_id' => $task->id,
                    'status' => $activity->status,
                    'end_date' => $activity->end_date,
                    'priority' => $activity->priority ?? 'Normal',
                ];
            });
        })->values();

        // Surface the most recent reports that are waiting for action
        $pendingReportsQuery = ActivityReport::with([
            'user:id,name',
            'approver:id,name',
            'activity:id,name,main_task_id',
            'activity.mainTask:id,name,team_leader_id',
        ])->where('status', 'Pending')
          ->orderByDesc('created_at');

        if (!$isManager) {
            $pendingReportsQuery->where('user_id', $user->id);
        }

        $pendingReports = $pendingReportsQuery->limit(8)->get();

        $categories = TaskCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $canCreateTask = $this->canCreateTask($user);

        return view('modules.tasks.index', [
            'mainTasks' => $mainTasks,
            'users' => $users,
            'isManager' => $isManager,
            'canCreateTask' => $canCreateTask,
            'dashboardStats' => $dashboardStats,
            'categories' => $categories,
            'pendingReports' => $pendingReports,
            'flatActivities' => $flatActivities,
            'filters' => [
                'status' => $statusFilter,
                'leader' => $leaderFilter,
                'priority' => $priorityFilter,
            ],
        ]);
    }

    public function action(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401)->header('Content-Type', 'application/json');
            }

            $isManager = $this->isManager($user);

            $action = $request->input('action');
            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action parameter is required'
                ], 400)->header('Content-Type', 'application/json');
            }

            return DB::transaction(function () use ($request, $user, $isManager, $action) {
            switch ($action) {
                case 'task_create_main':
                    $canCreateTask = $this->canCreateTask($user);
                    if (!$canCreateTask) abort(403, 'Only HOD, CEO, or HR Officer can create tasks');
                    
                    // Financial year validation
                    $financialYearService = app(\App\Services\FinancialYearService::class);
                    $startDate = $request->date('start_date');
                    $endDate = $request->date('end_date');
                    $linkToObjective = $request->input('organizational_goal_id') || $request->input('assessment_id');
                    
                    $fyValidation = $financialYearService->validateTaskCreation($startDate, $endDate, $linkToObjective);
                    if (!$fyValidation['valid']) {
                        return response()->json([
                            'success' => false,
                            'message' => $fyValidation['message']
                        ], 400);
                    }
                    
                    $financialYear = $fyValidation['financial_year'] ?? $financialYearService->getFinancialYearForDate($startDate);
                    
                    $tags = $request->input('tags');
                    $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];
                    
                    $mainTask = MainTask::create([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'timeframe' => $request->string('timeframe'),
                        'team_leader_id' => $request->integer('team_leader_id'),
                        'status' => $request->string('status', 'in_progress'),
                        'priority' => $request->string('priority', 'Normal'),
                        'category' => $request->input('category'),
                        'tags' => $tagsArray,
                        'budget' => $request->input('budget'),
                        'created_by' => $user->id,
                        'financial_year' => $financialYear,
                        'organizational_goal_id' => $request->input('organizational_goal_id'),
                        'assessment_id' => $request->input('assessment_id'),
                        'link_type' => $request->input('link_type', 'none'),
                        'performance_weight' => $request->input('performance_weight'),
                    ]);

                    // Create initial activities if provided
                    $assignedUserIds = [];
                    if ($request->has('activities') && is_array($request->input('activities'))) {
                        foreach ($request->input('activities') as $activityData) {
                            if (!empty($activityData['name'])) {
                                $activityStartDate = $activityData['start_date'] ?? $mainTask->start_date;
                                $activityFY = $financialYearService->getFinancialYearForDate($activityStartDate);
                                
                                $activity = TaskActivity::create([
                                    'main_task_id' => $mainTask->id,
                                    'name' => $activityData['name'],
                                    'start_date' => $activityStartDate,
                                    'status' => 'Not Started',
                                    'financial_year' => $activityFY,
                                    'assessment_activity_id' => $activityData['assessment_activity_id'] ?? null,
                                ]);

                                // Assign users to activity
                                if (isset($activityData['users']) && is_array($activityData['users'])) {
                                    foreach ($activityData['users'] as $userId) {
                                        ActivityAssignment::create([
                                            'activity_id' => $activity->id,
                                            'user_id' => $userId,
                                            'assigned_by' => $user->id,
                                        ]);
                                        $assignedUserIds[] = $userId;
                                    }
                                }
                            }
                        }
                    }

                    // Send SMS and Email notifications
                    try {
                        // Notify team leader
                        if ($mainTask->team_leader_id) {
                            $teamLeader = User::find($mainTask->team_leader_id);
                            if ($teamLeader) {
                                $message = "New Task Assigned: You have been assigned as team leader for task '{$mainTask->name}'. Priority: {$mainTask->priority}. Start Date: {$mainTask->start_date->format('M d, Y')}. End Date: {$mainTask->end_date->format('M d, Y')}.";
                                $this->notificationService->notify(
                                    $teamLeader->id,
                                    $message,
                                    route('modules.tasks.show', $mainTask->id),
                                    'New Task Assignment - ' . $mainTask->name
                                );
                            }
                        }

                        // Notify assigned users
                        if (!empty($assignedUserIds)) {
                            $uniqueUserIds = array_unique($assignedUserIds);
                            foreach ($uniqueUserIds as $userId) {
                                $assignedUser = User::find($userId);
                                if ($assignedUser && $userId != $mainTask->team_leader_id) {
                                    $message = "Task Activity Assigned: You have been assigned to activity in task '{$mainTask->name}'. Please check your assigned activities and start working on them.";
                                    $this->notificationService->notify(
                                        $userId,
                                        $message,
                                        route('modules.tasks.show', $mainTask->id),
                                        'Task Activity Assignment - ' . $mainTask->name
                                    );
                                }
                            }
                        }

                        \Log::info('Task created - SMS and Email notifications sent', [
                            'task_id' => $mainTask->id,
                            'team_leader_id' => $mainTask->team_leader_id,
                            'assigned_users' => $uniqueUserIds ?? []
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_create_main: ' . $e->getMessage());
                    }

                    // Log activity
                    ActivityLogService::logCreated($mainTask, "Created main task: {$mainTask->name}", [
                        'task_name' => $mainTask->name,
                        'status' => $mainTask->status,
                        'priority' => $mainTask->priority,
                        'team_leader_id' => $mainTask->team_leader_id,
                        'activities_count' => count($request->input('activities', [])),
                    ]);

                    return response()->json(['success' => true, 'message' => 'Main task created successfully!']);

                case 'task_edit_main':
                    if (!$isManager) abort(403);
                    
                    $mainTask = MainTask::findOrFail($request->integer('main_task_id'));
                    $oldTeamLeaderId = $mainTask->team_leader_id;
                    $oldStatus = $mainTask->status;
                    $tags = $request->input('tags');
                    $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];
                    
                    $mainTask->update([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'team_leader_id' => $request->integer('team_leader_id'),
                        'status' => $request->string('status'),
                        'priority' => $request->string('priority', 'Normal'),
                        'category' => $request->input('category'),
                        'tags' => $tagsArray,
                        'budget' => $request->input('budget'),
                    ]);

                    // Create new activities if provided
                    $newActivityIds = [];
                    if ($request->has('new_activities') && is_array($request->input('new_activities'))) {
                        foreach ($request->input('new_activities') as $activityData) {
                            if (!empty($activityData['name'])) {
                                // Calculate timeframe if not provided
                                $timeframe = $activityData['timeframe'] ?? '';
                                if (empty($timeframe) && !empty($activityData['start_date']) && !empty($activityData['end_date'])) {
                                    $start = \Carbon\Carbon::parse($activityData['start_date']);
                                    $end = \Carbon\Carbon::parse($activityData['end_date']);
                                    $diffDays = $start->diffInDays($end);
                                    $timeframe = $diffDays . ' Day(s)';
                                }

                                $activity = TaskActivity::create([
                                    'main_task_id' => $mainTask->id,
                                    'name' => $activityData['name'],
                                    'start_date' => $activityData['start_date'] ?? null,
                                    'end_date' => $activityData['end_date'] ?? null,
                                    'timeframe' => $timeframe,
                                    'status' => $activityData['status'] ?? 'Not Started',
                                    'priority' => $activityData['priority'] ?? 'Normal',
                                    'estimated_hours' => isset($activityData['estimated_hours']) ? (int)$activityData['estimated_hours'] : null,
                                ]);

                                $newActivityIds[] = $activity->id;

                                // Assign users to activity
                                if (isset($activityData['user_ids']) && is_array($activityData['user_ids'])) {
                                    foreach ($activityData['user_ids'] as $userId) {
                                        ActivityAssignment::create([
                                            'activity_id' => $activity->id,
                                            'user_id' => $userId,
                                            'assigned_by' => $user->id,
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    // Send SMS and Email notifications
                    try {
                        $newTeamLeaderId = $mainTask->team_leader_id;
                        $newStatus = $mainTask->status;

                        // Notify if team leader changed
                        if ($oldTeamLeaderId != $newTeamLeaderId && $newTeamLeaderId) {
                            $newLeader = User::find($newTeamLeaderId);
                            if ($newLeader) {
                                $message = "Task Leader Assignment: You have been assigned as team leader for task '{$mainTask->name}'. Please review the task details and assigned activities.";
                                $this->notificationService->notify(
                                    $newLeader->id,
                                    $message,
                                    route('modules.tasks.show', $mainTask->id),
                                    'Task Leader Assignment - ' . $mainTask->name
                                );
                            }
                        }

                        // Notify team leader if status changed
                        if ($oldStatus != $newStatus && $newTeamLeaderId) {
                            $teamLeader = User::find($newTeamLeaderId);
                            if ($teamLeader) {
                                $message = "Task Status Updated: Task '{$mainTask->name}' status has been changed to '{$newStatus}'.";
                                $this->notificationService->notify(
                                    $teamLeader->id,
                                    $message,
                                    route('modules.tasks.index'),
                                    'Task Status Updated'
                                );
                            }
                        }

                        // Notify users assigned to new activities
                        if (!empty($newActivityIds)) {
                            foreach ($newActivityIds as $activityId) {
                                $activity = TaskActivity::with('assignedUsers')->find($activityId);
                                if ($activity && $activity->assignedUsers) {
                                    foreach ($activity->assignedUsers as $assignedUser) {
                                        if ($assignedUser->id != $mainTask->team_leader_id) {
                                            $message = "Activity Assigned: You have been assigned to activity '{$activity->name}' in task '{$mainTask->name}'.";
                                            $this->notificationService->notify(
                                                $assignedUser->id,
                                                $message,
                                                route('modules.tasks.index'),
                                                'Activity Assignment'
                                            );
                                        }
                                    }
                                }
                            }
                        }

                        \Log::info('Task updated - SMS and Email notifications sent', [
                            'task_id' => $mainTask->id,
                            'status_changed' => $oldStatus != $newStatus,
                            'leader_changed' => $oldTeamLeaderId != $newTeamLeaderId,
                            'new_activities_count' => count($newActivityIds)
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_edit_main: ' . $e->getMessage());
                    }

                    // Log activity
                    $oldValues = array_intersect_key($mainTask->getOriginal(), $mainTask->getChanges());
                    ActivityLogService::logUpdated($mainTask, $oldValues, $mainTask->getChanges(), "Updated main task: {$mainTask->name}", [
                        'task_name' => $mainTask->name,
                        'status_changed' => $oldStatus != $newStatus,
                        'new_activities_count' => count($newActivityIds),
                    ]);

                    $message = 'Main task updated successfully!';
                    if (!empty($newActivityIds)) {
                        $message .= ' ' . count($newActivityIds) . ' new activity(ies) added.';
                    }

                    return response()->json(['success' => true, 'message' => $message]);

                case 'task_add_activity':
                    $mainTask = MainTask::findOrFail($request->integer('main_task_id'));
                    $activity = TaskActivity::create([
                        'main_task_id' => $mainTask->id,
                        'name' => $request->string('name'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'status' => $request->string('status', 'Not Started'),
                        'priority' => $request->string('priority', 'Normal'),
                    ]);

                    // Assign users
                    $userIds = $request->input('user_ids', []);
                    foreach ($userIds as $userId) {
                        ActivityAssignment::create([
                            'activity_id' => $activity->id,
                            'user_id' => $userId,
                            'assigned_by' => $user->id,
                        ]);
                    }

                    // Send SMS and Email notifications to team leader and assigned users
                    try {
                        // Notify team leader
                        if ($mainTask->team_leader_id) {
                            $message = "New Activity Added: Activity '{$activity->name}' has been added to task '{$mainTask->name}'. Please review and assign team members if needed.";
                            $this->notificationService->notify(
                                $mainTask->team_leader_id,
                                $message,
                                route('modules.tasks.show', $mainTask->id),
                                'New Activity Added - ' . $activity->name
                            );
                        }

                        // Notify assigned users
                        if (!empty($userIds)) {
                            foreach ($userIds as $userId) {
                                $assignedUser = User::find($userId);
                                if ($assignedUser && $userId != $mainTask->team_leader_id) {
                                    $message = "Activity Assignment: You have been assigned to activity '{$activity->name}' in task '{$mainTask->name}'. Please check your assigned activities and start working on them.";
                                    $this->notificationService->notify(
                                        $userId,
                                        $message,
                                        route('modules.tasks.show', $mainTask->id),
                                        'Activity Assignment - ' . $activity->name
                                    );
                                }
                            }
                        }
                            
                        \Log::info('Activity added - SMS and Email notifications sent', [
                            'activity_id' => $activity->id,
                            'task_id' => $mainTask->id,
                            'team_leader_id' => $mainTask->team_leader_id,
                            'assigned_users' => $userIds ?? []
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_add_activity: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'New activity added successfully!']);

                case 'task_get_details':
                    $mainTaskId = $request->integer('main_task_id');
                    $activities = TaskActivity::with('assignedUsers')
                        ->where('main_task_id', $mainTaskId)
                        ->orderBy('created_at')
                        ->get();

                    return response()->json(['success' => true, 'activities' => $activities]);

                case 'get_activity_details':
                    $activity = TaskActivity::with([
                        'assignedUsers:id,name',
                        'reports' => function($query) {
                            $query->orderByDesc('report_date')->limit(10);
                        }
                    ])->findOrFail($request->integer('activity_id'));

                    return response()->json([
                        'success' => true,
                        'activity' => [
                            'id' => $activity->id,
                            'name' => $activity->name,
                            'status' => $activity->status,
                            'priority' => $activity->priority,
                            'start_date' => $activity->start_date ? $activity->start_date->format('M d, Y') : null,
                            'end_date' => $activity->end_date ? $activity->end_date->format('M d, Y') : null,
                            'timeframe' => $activity->timeframe,
                            'assigned_users' => $activity->assignedUsers->map(function($user) {
                                return ['id' => $user->id, 'name' => $user->name];
                            }),
                            'reports' => $activity->reports->map(function($report) {
                                return [
                                    'report_date' => $report->report_date->format('M d, Y'),
                                    'status' => $report->status,
                                    'completion_status' => $report->completion_status,
                                ];
                            }),
                            'reports_count' => $activity->reports->count(),
                        ]
                    ]);

                case 'task_update_activity':
                    $activity = TaskActivity::with('mainTask')->findOrFail($request->integer('activity_id'));
                    $oldAssignedUserIds = $activity->assignedUsers()->pluck('user_id')->toArray();
                    
                    $activity->update([
                        'name' => $request->string('name'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'timeframe' => $request->string('timeframe'),
                        'status' => $request->string('status', $activity->status),
                        'priority' => $request->string('priority', 'Normal'),
                        'depends_on_id' => $request->input('depends_on_id') ? $request->integer('depends_on_id') : null,
                        'estimated_hours' => $request->integer('estimated_hours', $activity->estimated_hours ?? null),
                    ]);

                    // Update assignments
                    $activity->assignments()->delete();
                    $userIds = $request->input('user_ids', []);
                    $newAssignedUserIds = [];
                    foreach ($userIds as $userId) {
                        ActivityAssignment::create([
                            'activity_id' => $activity->id,
                            'user_id' => $userId,
                            'assigned_by' => $user->id,
                        ]);
                        $newAssignedUserIds[] = $userId;
                    }

                    // Send SMS and Email notifications
                    try {
                        // Notify newly assigned users
                        $newlyAssigned = array_diff($newAssignedUserIds, $oldAssignedUserIds);
                        foreach ($newlyAssigned as $userId) {
                            $assignedUser = User::find($userId);
                            if ($assignedUser) {
                                $message = "Activity Assigned: You have been assigned to activity '{$activity->name}' in task '{$activity->mainTask->name}'. Please check your assigned activities and start working on them.";
                                $this->notificationService->notify(
                                    $userId,
                                    $message,
                                    route('modules.tasks.show', $activity->mainTask->id),
                                    'Activity Assignment - ' . $activity->name
                                );
                            }
                        }

                        // Notify team leader if assignments changed
                        if ($activity->mainTask && $activity->mainTask->team_leader_id) {
                            $message = "Activity Updated: Activity '{$activity->name}' in task '{$activity->mainTask->name}' has been updated. Please review the changes.";
                            $this->notificationService->notify(
                                $activity->mainTask->team_leader_id,
                                $message,
                                route('modules.tasks.show', $activity->mainTask->id),
                                'Activity Updated - ' . $activity->name
                            );
                        }

                        \Log::info('Activity updated - SMS and Email notifications sent', [
                            'activity_id' => $activity->id,
                            'newly_assigned_users' => $newlyAssigned
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_update_activity: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Activity updated successfully.']);

                case 'task_delete_activity':
                    if (!$isManager) abort(403);
                    
                    $activity = TaskActivity::with(['mainTask', 'assignedUsers'])->findOrFail($request->integer('activity_id'));
                    $activityName = $activity->name;
                    $mainTask = $activity->mainTask;
                    $taskName = $mainTask->name ?? 'Unknown Task';
                    $mainTaskId = $mainTask->id ?? null;
                    $teamLeaderId = $mainTask->team_leader_id ?? null;
                    
                    // Get assigned user IDs before deletion
                    $assignedUserIds = $activity->assignedUsers->pluck('id')->toArray();
                    $activityId = $activity->id;
                    
                    // Delete the activity
                    $activity->delete();

                    // Send SMS and Email notifications to team leader and assigned users
                    try {
                        // Notify team leader
                        if ($mainTaskId && $teamLeaderId) {
                            $message = "Activity Deleted: Activity '{$activityName}' has been removed from task '{$taskName}'. Please review the updated task structure.";
                            $this->notificationService->notify(
                                $teamLeaderId,
                                $message,
                                route('modules.tasks.show', $mainTaskId),
                                'Activity Deleted - ' . $activityName
                            );
                        }

                        // Notify assigned users
                        if (!empty($assignedUserIds)) {
                            foreach ($assignedUserIds as $userId) {
                                if ($userId != $teamLeaderId) {
                                    $assignedUser = User::find($userId);
                                    if ($assignedUser) {
                                        $message = "Activity Removed: Activity '{$activityName}' that you were assigned to in task '{$taskName}' has been removed. Please check with your team leader for updates.";
                                        $this->notificationService->notify(
                                            $userId,
                                            $message,
                                            $mainTaskId ? route('modules.tasks.show', $mainTaskId) : route('modules.tasks.index'),
                                            'Activity Removed - ' . $activityName
                                        );
                                    }
                                }
                            }
                        }
                            
                        \Log::info('Activity deleted - SMS and Email notifications sent', [
                            'activity_id' => $activityId,
                            'task_id' => $mainTaskId,
                            'team_leader_id' => $teamLeaderId
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_delete_activity: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Activity deleted successfully.']);

                case 'upload_activity_attachment':
                    $activity = TaskActivity::findOrFail($request->integer('activity_id'));
                    
                    // Check permissions
                    $canEdit = $isManager || $activity->mainTask->team_leader_id == $user->id;
                    if (!$canEdit) {
                        return response()->json(['success' => false, 'message' => 'You do not have permission to upload attachments'], 403);
                    }

                    $filePath = null;
                    $fileName = null;
                    $fileType = null;
                    $fileSize = null;

                    // Handle file upload
                    if ($request->hasFile('file')) {
                        $file = $request->file('file');
                        $fileName = $file->getClientOriginalName();
                        $fileType = $file->getMimeType();
                        $fileSize = $file->getSize();
                        
                        $path = $file->store('task_attachments/activities/' . $activity->id, 'public');
                        $filePath = $path;
                    } 
                    // Handle storage link
                    elseif ($request->filled('storage_link')) {
                        $storageLink = $request->string('storage_link');
                        $fileName = basename(parse_url($storageLink, PHP_URL_PATH)) ?: 'External Link';
                        $filePath = $storageLink;
                        $fileType = 'external_link';
                        $fileSize = 0;
                    } else {
                        return response()->json(['success' => false, 'message' => 'Please provide either a file or storage link'], 422);
                    }

                    $attachment = TaskAttachment::create([
                        'main_task_id' => $activity->main_task_id,
                        'activity_id' => $activity->id,
                        'user_id' => $user->id,
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'file_type' => $fileType,
                        'file_size' => $fileSize,
                    ]);

                    return response()->json([
                        'success' => true, 
                        'message' => 'Attachment uploaded successfully',
                        'attachment' => $attachment
                    ]);

                case 'task_submit_report':
                    $activityId = $request->integer('activity_id');
                    $activity = TaskActivity::findOrFail($activityId);

                    // Check if user is assigned to this activity
                    $isAssigned = $activity->assignedUsers()->where('user_id', $user->id)->exists();
                    if (!$isAssigned) {
                        return response()->json(['success' => false, 'message' => 'You are not assigned to this activity.']);
                    }

                    // Allow multiple reports per activity - removed the pending check
                    // Users can submit multiple reports for the same activity

                    // Get financial year for report date
                    $reportDate = $request->date('report_date');
                    $orgSettings = \App\Models\OrganizationSetting::getSettings();
                    $financialYear = $orgSettings->getFinancialYearForDate($reportDate);

                    $report = ActivityReport::create([
                        'activity_id' => $activityId,
                        'user_id' => $user->id,
                        'report_date' => $reportDate,
                        'work_description' => $request->input('work_description'),
                        'next_activities' => $request->input('next_activities'),
                        'attachment_path' => null, // Keep for backward compatibility, but use attachments table
                        'completion_status' => $request->string('completion_status'),
                        'reason_if_delayed' => $request->input('reason_if_delayed'),
                        'status' => 'Pending',
                        'financial_year' => $financialYear,
                    ]);

                    // Handle multiple file uploads
                    if ($request->hasFile('attachments')) {
                        foreach ($request->file('attachments') as $file) {
                            if ($file->isValid()) {
                                $filePath = $file->store('activity_reports/' . $report->id, 'public');
                                $mimeType = $file->getMimeType();
                                $fileType = str_starts_with($mimeType, 'image/') ? 'image' : 'document';
                                
                                \App\Models\ActivityReportAttachment::create([
                                    'report_id' => $report->id,
                                    'file_name' => $file->getClientOriginalName(),
                                    'file_path' => $filePath,
                                    'file_type' => $fileType,
                                    'file_size' => $file->getSize(),
                                    'mime_type' => $mimeType,
                                ]);
                            }
                        }
                    }

                    // Also handle single attachment for backward compatibility
                    if ($request->hasFile('attachment')) {
                        $file = $request->file('attachment');
                        if ($file->isValid()) {
                            $filePath = $file->store('activity_reports/' . $report->id, 'public');
                            $mimeType = $file->getMimeType();
                            $fileType = str_starts_with($mimeType, 'image/') ? 'image' : 'document';
                            
                            \App\Models\ActivityReportAttachment::create([
                                'report_id' => $report->id,
                                'file_name' => $file->getClientOriginalName(),
                                'file_path' => $filePath,
                                'file_type' => $fileType,
                                'file_size' => $file->getSize(),
                                'mime_type' => $mimeType,
                            ]);
                            
                            // Update attachment_path for backward compatibility
                            $report->update(['attachment_path' => $filePath]);
                        }
                    }

                    // Update activity status based on completion status
                    $mainTask = $activity->mainTask;
                    $completionStatus = $request->string('completion_status');
                    $allCompleted = false;
                    
                    if ($completionStatus === 'Completed') {
                        $activity->update([
                            'status' => 'Completed',
                            'actual_end_date' => now()->toDateString(),
                        ]);

                        // Sync task completion to performance management
                        try {
                            $syncService = new \App\Services\TaskPerformanceSyncService();
                            $syncService->syncTaskToPerformance($activity, $user->id);
                        } catch (\Exception $e) {
                            \Log::error('Failed to sync task to performance: ' . $e->getMessage());
                        }

                        // Check if all activities are completed to auto-complete main task
                        $allCompleted = $mainTask->activities()->where('status', '!=', 'Completed')->count() === 0;
                        if ($allCompleted) {
                            // Don't auto-complete - only HOD can complete
                            // $mainTask->update(['status' => 'completed']);
                        }
                    } else {
                        $activity->update(['status' => 'In Progress']);
                        
                        // Sync task progress to performance management
                        try {
                            $syncService = new \App\Services\TaskPerformanceSyncService();
                            $syncService->syncTaskToPerformance($activity, $user->id);
                        } catch (\Exception $e) {
                            \Log::error('Failed to sync task to performance: ' . $e->getMessage());
                        }
                        
                        // Update main task from planning to in_progress
                        if ($mainTask->status === 'planning') {
                            $mainTask->update(['status' => 'in_progress']);
                        }
                    }

                    // Calculate progress (will update when report is approved)
                    // Note: Progress is calculated when report is approved, not on submission

                    // Send SMS and Email notifications
                    try {
                        $reporterName = $user->name;
                        $activityName = $activity->name;
                        $taskName = $mainTask->name;

                        // Notify team leader
                        if ($mainTask->team_leader_id) {
                            $message = "Progress Report Submitted: {$reporterName} has submitted a progress report for activity '{$activityName}' in task '{$taskName}'. Completion Status: {$completionStatus}. Please review and approve or reject the report.";
                            $this->notificationService->notify(
                                $mainTask->team_leader_id,
                                $message,
                                route('modules.tasks.reports.show', $report->id),
                                'Progress Report Submitted - ' . $activityName
                            );
                        }

                        // Notify reporter
                        $reporterMessage = "Report Submitted: Your progress report for activity '{$activityName}' in task '{$taskName}' has been submitted successfully. Completion Status: {$completionStatus}. It is pending approval from your manager.";
                        $this->notificationService->notify(
                            $user->id,
                            $reporterMessage,
                            route('modules.tasks.reports.show', $report->id),
                            'Report Submitted - ' . $activityName
                        );

                        // Notify all action owners (leaders + approvers) as SMS when a report arrives
                        $actionUserIds = [];
                        if ($mainTask->team_leader_id) {
                            $actionUserIds[] = $mainTask->team_leader_id;
                        }
                        if ($mainTask->created_by) {
                            $actionUserIds[] = $mainTask->created_by;
                        }

                        $managerRoles = ['System Admin','CEO','HOD','Manager','Director'];
                        $departmentId = optional($mainTask->teamLeader)->primary_department_id;
                        $managers = User::whereHas('roles', function($q) use ($managerRoles) {
                                $q->whereIn('name', $managerRoles);
                            })
                            ->when($departmentId, function($q) use ($departmentId) {
                                $q->where('primary_department_id', $departmentId);
                            })
                            ->pluck('id')
                            ->toArray();

                        $actionUserIds = array_unique(array_merge($actionUserIds, $managers));
                        $actionUserIds = array_values(array_filter($actionUserIds, function ($id) use ($user) {
                            return $id && $id != $user->id;
                        }));

                        if (!empty($actionUserIds)) {
                            $actionMessage = "Action Needed: Progress report for '{$activityName}' in task '{$taskName}' was submitted by {$reporterName}. Completion Status: {$completionStatus}. Please review and take action (approve/reject).";
                            $this->notificationService->notify(
                                $actionUserIds,
                                $actionMessage,
                                route('modules.tasks.reports.show', $report->id),
                                'Progress Report Action Needed - ' . $activityName
                            );
                        }

                        // Notify HOD if task is completed
                        if ($allCompleted && $mainTask->team_leader_id) {
                            $teamLeader = User::find($mainTask->team_leader_id);
                            if ($teamLeader && $teamLeader->primary_department_id) {
                                $message = "Task Completed: Task '{$taskName}' has been completed by the team. All activities are now finished. Please review and close the task.";
                                $this->notificationService->notifyHOD(
                                    $teamLeader->primary_department_id,
                                    $message,
                                    route('modules.tasks.show', $mainTask->id),
                                    'Task Completed - ' . $taskName
                                );
                            }
                        }

                        \Log::info('Progress report submitted - SMS and Email notifications sent', [
                            'report_id' => $report->id,
                            'activity_id' => $activity->id,
                            'task_id' => $mainTask->id,
                            'reporter_id' => $user->id,
                            'action_owners_notified' => $actionUserIds
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_submit_report: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Report submitted successfully.']);

                case 'task_get_report_details':
                    $activityId = $request->integer('activity_id');
                    $activity = TaskActivity::with('assignedUsers')->findOrFail($activityId);
                    
                    $reports = ActivityReport::with(['user', 'approver'])
                        ->where('activity_id', $activityId)
                        ->orderByDesc('report_date')
                        ->orderByDesc('created_at')
                        ->get();

                    $isAssigned = $activity->assignedUsers()->where('user_id', $user->id)->exists();
                    $hasPendingReport = ActivityReport::where('activity_id', $activityId)
                        ->where('user_id', $user->id)
                        ->where('status', 'Pending')
                        ->exists();

                    return response()->json([
                        'success' => true,
                        'activity' => $activity,
                        'reports' => $reports,
                        'is_assigned' => $isAssigned,
                        'has_pending_report' => $hasPendingReport,
                    ]);

                case 'task_approve_report':
                    // Check authorization - only managers can approve reports
                    if (!$isManager) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only managers can approve activity reports.'
                        ], 403);
                    }

                    $reportId = $request->integer('report_id');
                    if (!$reportId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report ID is required.'
                        ], 400);
                    }

                    $report = ActivityReport::with(['user', 'activity.mainTask'])->find($reportId);
                    if (!$report) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report not found.'
                        ], 404);
                    }

                    // Check if report is pending
                    if ($report->status !== 'Pending') {
                        return response()->json([
                            'success' => false,
                            'message' => 'This report has already been reviewed. Current status: ' . $report->status
                        ], 400);
                    }

                    // HOD can only approve reports from their department
                    $isHOD = $user->hasRole('HOD') && !$user->hasAnyRole(['System Admin', 'CEO', 'HR Officer']);
                    if ($isHOD) {
                        $reportUser = $report->user;
                        if (!$reportUser || ($reportUser->primary_department_id !== $user->primary_department_id)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only approve reports from staff in your department.'
                            ], 403);
                        }
                    }

                    // Get quality assessment data
                    $qualityRating = $request->input('quality_rating');
                    $complexityTag = $request->input('complexity_tag');
                    $initiativeBonus = $request->boolean('initiative_bonus', false);
                    $qualityComments = $request->input('quality_comments', '');

                    // Calculate performance score
                    $performanceScoringService = app(\App\Services\PerformanceScoringService::class);
                    $performanceScore = $performanceScoringService->calculateReportPerformanceScore($report);
                    
                    // Apply quality rating if provided
                    if ($qualityRating) {
                        $performanceScore = ($qualityRating / 5) * 100;
                        // Apply complexity multiplier
                        $complexityMultiplier = match($complexityTag) {
                            'complex' => 1.2,
                            'standard' => 1.0,
                            'routine' => 0.9,
                            default => 1.0
                        };
                        $performanceScore = min(100, $performanceScore * $complexityMultiplier);
                        // Add initiative bonus
                        if ($initiativeBonus) {
                            $performanceScore = min(100, $performanceScore + 10);
                        }
                    }

                    // Get financial year
                    $orgSettings = \App\Models\OrganizationSetting::getSettings();
                    $financialYear = $orgSettings->getFinancialYearForDate($report->report_date ?? now());

                    $report->update([
                        'status' => 'Approved',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'approver_comments' => $request->input('comments', ''),
                        'quality_rating' => $qualityRating,
                        'complexity_tag' => $complexityTag,
                        'initiative_bonus' => $initiativeBonus,
                        'quality_comments' => $qualityComments,
                        'performance_score' => round($performanceScore, 2),
                        'financial_year' => $financialYear,
                    ]);

                    // Auto-calculate task progress based on approved reports
                    if ($report->activity && $report->activity->mainTask) {
                        $mainTask = $report->activity->mainTask;
                        $this->calculateTaskProgress($mainTask);
                    }

                    // Sync to performance module if linked
                    if ($report->activity && $report->activity->assessment_activity_id) {
                        $performanceScoringService->syncReportToPerformance($report);
                    }

                    // Send SMS and Email notifications
                    try {
                        $reporter = $report->user;
                        $activity = $report->activity;
                        $mainTask = $activity ? $activity->mainTask : null;
                        $approverName = $user->name;

                        if ($reporter && $activity) {
                            $activityName = $activity->name ?? 'Activity';
                            $taskName = $mainTask ? $mainTask->name : 'Task';
                            $approverComments = $report->approver_comments ? "\n\nApprover Comments: {$report->approver_comments}" : "";
                            $message = "Report Approved: Your progress report for activity '{$activityName}' in task '{$taskName}' has been approved by {$approverName}.{$approverComments}";
                            $this->notificationService->notify(
                                $reporter->id,
                                $message,
                                route('modules.tasks.reports.show', $report->id),
                                'Report Approved - ' . $activityName
                            );

                            // Also notify team leader about approval
                            if ($mainTask && $mainTask->team_leader_id && $mainTask->team_leader_id != $user->id) {
                                $leaderMessage = "Report Approved: Progress report for activity '{$activityName}' in task '{$taskName}' submitted by {$reporter->name} has been approved by {$approverName}.";
                                $this->notificationService->notify(
                                    $mainTask->team_leader_id,
                                    $leaderMessage,
                                    route('modules.tasks.reports.show', $report->id),
                                    'Report Approved - ' . $activityName
                                );
                            }

                            \Log::info('Report approved - SMS and Email notifications sent', [
                                'report_id' => $report->id,
                                'reporter_id' => $reporter->id,
                                'approver_id' => $user->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_approve_report: ' . $e->getMessage());
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Report approved successfully.',
                        'completion_status' => $report->completion_status
                    ]);

                case 'task_reject_report':
                    // Check authorization - only managers can reject reports
                    if (!$isManager) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only managers can reject activity reports.'
                        ], 403);
                    }

                    $reportId = $request->integer('report_id');
                    if (!$reportId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report ID is required.'
                        ], 400);
                    }

                    $report = ActivityReport::with(['user', 'activity.mainTask'])->find($reportId);
                    if (!$report) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Report not found.'
                        ], 404);
                    }

                    // Check if report is pending
                    if ($report->status !== 'Pending') {
                        return response()->json([
                            'success' => false,
                            'message' => 'This report has already been reviewed. Current status: ' . $report->status
                        ], 400);
                    }

                    // HOD can only reject reports from their department
                    $isHOD = $user->hasRole('HOD') && !$user->hasAnyRole(['System Admin', 'CEO', 'HR Officer']);
                    if ($isHOD) {
                        $reportUser = $report->user;
                        if (!$reportUser || ($reportUser->primary_department_id !== $user->primary_department_id)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only reject reports from staff in your department.'
                            ], 403);
                        }
                    }

                    $comments = $request->input('comments', '');
                    
                    if (empty(trim($comments))) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A comment is required to reject a report.'
                        ], 422);
                    }

                    $report->update([
                        'status' => 'Rejected',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'approver_comments' => $comments,
                    ]);

                    // Send SMS and Email notifications
                    try {
                        $reporter = $report->user;
                        $activity = $report->activity;
                        $mainTask = $activity ? $activity->mainTask : null;
                        $approverName = $user->name;

                        if ($reporter && $activity) {
                            $activityName = $activity->name ?? 'Activity';
                            $taskName = $mainTask ? $mainTask->name : 'Task';
                            $rejectionComments = $comments ? "\n\nRejection Reason: {$comments}" : "";
                            $message = "Report Rejected: Your progress report for activity '{$activityName}' in task '{$taskName}' has been rejected by {$approverName}. Please review the comments, make necessary corrections, and resubmit.{$rejectionComments}";
                            $this->notificationService->notify(
                                $reporter->id,
                                $message,
                                route('modules.tasks.reports.show', $report->id),
                                'Report Rejected - ' . $activityName
                            );

                            // Also notify team leader about rejection
                            if ($mainTask && $mainTask->team_leader_id && $mainTask->team_leader_id != $user->id) {
                                $leaderMessage = "Report Rejected: Progress report for activity '{$activityName}' in task '{$taskName}' submitted by {$reporter->name} has been rejected by {$approverName}.";
                                $this->notificationService->notify(
                                    $mainTask->team_leader_id,
                                    $leaderMessage,
                                    route('modules.tasks.reports.show', $report->id),
                                    'Report Rejected - ' . $activityName
                                );
                            }

                            \Log::info('Report rejected - SMS and Email notifications sent', [
                                'report_id' => $report->id,
                                'reporter_id' => $reporter->id,
                                'approver_id' => $user->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Notification error in task_reject_report: ' . $e->getMessage());
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Report rejected with feedback.'
                    ]);

                case 'get_activities_for_date':
                    $date = $request->date('date');
                    
                    // Get all tasks that include this date
                    $tasksOnDate = MainTask::where(function($query) use ($date) {
                        $query->where('start_date', '<=', $date)
                              ->where('end_date', '>=', $date);
                    })->pluck('id');
                    
                    // Get all activities for these tasks that are active on this date
                    $activities = TaskActivity::whereIn('main_task_id', $tasksOnDate)
                        ->where(function($query) use ($date) {
                            $query->where(function($q) use ($date) {
                                $q->where('start_date', '<=', $date)
                                  ->where('end_date', '>=', $date);
                            })
                            ->whereIn('status', ['Not Started', 'In Progress', 'Completed']);
                        })
                        ->with(['mainTask:id,name'])
                        ->get()
                        ->map(function($activity) {
                            return [
                                'id' => $activity->id,
                                'name' => $activity->name,
                                'status' => $activity->status,
                                'priority' => $activity->priority,
                                'start_date' => $activity->start_date,
                                'end_date' => $activity->end_date,
                                'task_name' => $activity->mainTask->name ?? 'N/A',
                            ];
                        });
                    
                    return response()->json([
                        'success' => true,
                        'activities' => $activities,
                        'date' => $date->format('Y-m-d')
                    ]);

                case 'get_task_full_details':
                    $taskId = $request->integer('task_id');
                    $task = MainTask::with([
                        'teamLeader:id,name',
                        'creator:id,name',
                        'activities.assignedUsers:id,name',
                        'activities.reports.user:id,name',
                        'activities.reports.approver:id,name',
                        'activities.comments.user:id,name',
                        'activities.attachments.user:id,name',
                        'comments.user:id,name',
                        'attachments.user:id,name'
                    ])->findOrFail($taskId);
                    
                    // Calculate current progress
                    $this->calculateTaskProgress($task);
                    $task->refresh();
                    
                    return response()->json(['success' => true, 'task' => $task]);

                case 'update_task_status':
                    if (!$isManager) abort(403);
                    
                    $task = MainTask::with(['activities.assignedUsers'])->findOrFail($request->integer('task_id'));
                    $oldStatus = $task->status;
                    $newStatus = $request->string('status');
                    
                    // Only HOD can mark task as completed or closed
                    if (in_array($newStatus, ['completed', 'closed'])) {
                        $isHOD = $user->hasRole('HOD') || $user->hasRole('System Admin');
                        if (!$isHOD) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Only HOD (Head of Department) can mark tasks as completed or closed.'
                            ], 403);
                        }
                    }
                    
                    $task->update(['status' => $newStatus]);

                    // Send SMS and Email notifications
                    try {
                        // Notify team leader
                        if ($task->team_leader_id) {
                            $message = "Task Status Updated: Task '{$task->name}' status has been changed from '{$oldStatus}' to '{$newStatus}'. Please review the updated task details.";
                            $this->notificationService->notify(
                                $task->team_leader_id,
                                $message,
                                route('modules.tasks.show', $task->id),
                                'Task Status Updated - ' . $task->name
                            );
                        }

                        // Notify all assigned users
                        $assignedUserIds = [];
                        foreach ($task->activities as $activity) {
                            foreach ($activity->assignedUsers as $assignedUser) {
                                if (!in_array($assignedUser->id, $assignedUserIds)) {
                                    $assignedUserIds[] = $assignedUser->id;
                                    if ($assignedUser->id != $task->team_leader_id) {
                                        $message = "Task Status Updated: Task '{$task->name}' status has been changed from '{$oldStatus}' to '{$newStatus}'. Please review the updated task details and adjust your work accordingly.";
                                        $this->notificationService->notify(
                                            $assignedUser->id,
                                            $message,
                                            route('modules.tasks.show', $task->id),
                                            'Task Status Updated - ' . $task->name
                                        );
                                    }
                                }
                            }
                        }

                        \Log::info('Task status updated - SMS and Email notifications sent', [
                            'task_id' => $task->id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'notified_users' => count($assignedUserIds) + ($task->team_leader_id ? 1 : 0)
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Notification error in update_task_status: ' . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Task status updated successfully.']);

                case 'get_calendar_events':
                    // Get date range from request (default to current month)
                    $start = $request->input('start', now()->startOfMonth()->toDateString());
                    $end = $request->input('end', now()->endOfMonth()->toDateString());
                    
                    // Build query based on user role
                    $query = MainTask::with(['teamLeader:id,name']);
                    
                    if (!$isManager) {
                        // Staff: only tasks they're assigned to or leading
                        $query->where(function($q) use ($user) {
                            $q->where('team_leader_id', $user->id)
                              ->orWhereHas('activities.assignedUsers', function($subQ) use ($user) {
                                  $subQ->where('user_id', $user->id);
                              });
                        });
                    }
                    
                    // Get tasks that overlap with the date range
                    $tasks = $query->where(function($q) use ($start, $end) {
                        $q->whereBetween('start_date', [$start, $end])
                          ->orWhereBetween('end_date', [$start, $end])
                          ->orWhere(function($subQ) use ($start, $end) {
                              $subQ->where('start_date', '<=', $start)
                                   ->where('end_date', '>=', $end);
                          });
                    })->get();
                    
                    // Format tasks as FullCalendar events
                    $events = [];
                    foreach ($tasks as $task) {
                        $color = match($task->priority) {
                            'Critical' => '#dc2626',
                            'High' => '#d97706',
                            default => '#2563eb',
                        };
                        
                        $events[] = [
                            'id' => 'task-' . $task->id,
                            'title' => $task->name,
                            'start' => $task->start_date,
                            'end' => $task->end_date ? date('Y-m-d', strtotime($task->end_date . ' +1 day')) : $task->start_date,
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                            'textColor' => '#ffffff',
                            'extendedProps' => [
                                'taskId' => $task->id,
                                'priority' => $task->priority,
                                'status' => $task->status,
                                'teamLeader' => $task->teamLeader->name ?? 'N/A',
                            ],
                        ];
                    }
                    
                    return response()->json([
                        'success' => true,
                        'events' => $events
                    ]);

                case 'task_update_main':
                    // Alias for task_edit_main
                    if (!$isManager) abort(403);
                    
                    $mainTask = MainTask::findOrFail($request->integer('task_id'));
                    $tags = $request->input('tags');
                    $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];
                    
                    $mainTask->update([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'start_date' => $request->date('start_date'),
                        'end_date' => $request->date('end_date'),
                        'team_leader_id' => $request->integer('team_leader_id'),
                        'status' => $request->string('status'),
                        'priority' => $request->string('priority', 'Normal'),
                        'category' => $request->input('category'),
                        'tags' => $tagsArray,
                        'budget' => $request->input('budget'),
                    ]);

                    return response()->json(['success' => true, 'message' => 'Task updated successfully!']);

                case 'create_category':
                    if (!$isManager) abort(403);
                    
                    $category = TaskCategory::create([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'sort_order' => $request->integer('sort_order', 0),
                        'is_active' => $request->boolean('is_active', true),
                    ]);

                    return response()->json(['success' => true, 'message' => 'Category created successfully', 'category' => $category]);

                case 'update_category':
                    if (!$isManager) abort(403);
                    
                    $category = TaskCategory::findOrFail($request->integer('category_id'));
                    $category->update([
                        'name' => $request->string('name'),
                        'description' => $request->input('description'),
                        'sort_order' => $request->integer('sort_order', 0),
                        'is_active' => $request->boolean('is_active', true),
                    ]);

                    return response()->json(['success' => true, 'message' => 'Category updated successfully', 'category' => $category]);

                case 'delete_category':
                    if (!$isManager) abort(403);
                    
                    $category = TaskCategory::findOrFail($request->integer('category_id'));
                    $category->delete();

                    return response()->json(['success' => true, 'message' => 'Category deleted successfully']);

                case 'get_category':
                    if (!$isManager) abort(403);
                    
                    $category = TaskCategory::findOrFail($request->integer('category_id'));
                    return response()->json(['success' => true, 'category' => $category]);

                case 'get_report':
                    $reportId = $request->integer('report_id');
                    $report = ActivityReport::with([
                        'user:id,name,email',
                        'approver:id,name',
                        'activity:id,name,main_task_id',
                        'activity.mainTask:id,name',
                        'attachments'
                    ])->findOrFail($reportId);

                    // Check permissions - user can view their own reports or managers can view all
                    if (!$isManager && $report->user_id != $user->id) {
                        // Also check if user is assigned to the activity
                        $isAssigned = $report->activity->assignedUsers()->where('user_id', $user->id)->exists();
                        if (!$isAssigned) {
                            abort(403, 'You do not have permission to view this report');
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'report' => [
                            'id' => $report->id,
                            'report_date' => $report->report_date->format('M d, Y'),
                            'user_name' => $report->user->name ?? 'N/A',
                            'user_email' => $report->user->email ?? 'N/A',
                            'activity_name' => $report->activity->name ?? 'N/A',
                            'task_name' => $report->activity->mainTask->name ?? 'N/A',
                            'work_description' => $report->work_description ?? 'N/A',
                            'next_activities' => $report->next_activities,
                            'completion_status' => $report->completion_status,
                            'reason_if_delayed' => $report->reason_if_delayed,
                            'status' => $report->status,
                            'approver_name' => $report->approver->name ?? null,
                            'approved_at' => $report->approved_at ? $report->approved_at->format('M d, Y H:i') : null,
                            'approver_comments' => $report->approver_comments,
                            'attachment_path' => $report->attachment_path, // Backward compatibility
                            'attachments' => $report->attachments->map(function($attachment) {
                                return [
                                    'id' => $attachment->id,
                                    'file_name' => $attachment->file_name,
                                    'file_path' => $attachment->file_path,
                                    'file_type' => $attachment->file_type,
                                    'file_size' => $attachment->file_size,
                                    'mime_type' => $attachment->mime_type,
                                    'url' => asset('storage/' . $attachment->file_path),
                                    'is_image' => $attachment->isImage(),
                                ];
                            }),
                        ]
                    ]);
            }

            return response()->json(['success' => false, 'message' => 'Unknown action.'], 400);
        });
        } catch (\Throwable $e) {
            \Log::error('Task action error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'action' => $request->input('action', 'unknown')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Calculate task progress automatically based on approved reports
     * Progress is calculated as: (Sum of activity progress) / (Total activities) * 100
     * Activity progress is based on:
     * - Completed activities: 100%
     * - Activities with approved reports: Progress based on completion_status and report count
     * - Activities without reports: Status-based (In Progress = 50%, Not Started = 0%)
     */
    private function calculateTaskProgress(MainTask $mainTask)
    {
        $totalActivities = $mainTask->activities()->count();
        if ($totalActivities === 0) {
            $mainTask->update(['progress_percentage' => 0]);
            return;
        }

        $totalProgress = 0;

        foreach ($mainTask->activities as $activity) {
            $approvedReports = $activity->reports()->where('status', 'Approved')->count();
            $totalReports = $activity->reports()->where('status', 'Approved')->count();
            
            // If activity is completed, it contributes 100%
            if ($activity->status === 'Completed') {
                $activityProgress = 100;
            } 
            // If activity has approved reports, calculate based on completion status and report frequency
            elseif ($approvedReports > 0) {
                // Get the latest approved report to check completion status
                $latestReport = $activity->reports()
                    ->where('status', 'Approved')
                    ->orderByDesc('report_date')
                    ->orderByDesc('created_at')
                    ->first();
                
                if ($latestReport) {
                    // Base progress on completion status
                    switch ($latestReport->completion_status) {
                        case 'Completed':
                            $activityProgress = 100;
                            break;
                        case 'Delayed':
                            // Delayed activities get lower progress based on reports
                            $activityProgress = min(70, 40 + ($approvedReports * 10)); // 40% base + 10% per report, max 70%
                            break;
                        case 'In Progress':
                        default:
                            // In progress: base progress increases with each approved report
                            // First report: 30%, each additional: +15%, max 90% until completed
                            $activityProgress = min(90, 30 + (($approvedReports - 1) * 15));
                            break;
                    }
                } else {
                    $activityProgress = 0;
                }
            } 
            // Activities without approved reports: use status-based progress
            else {
                if ($activity->status === 'In Progress') {
                    $activityProgress = 25; // Started but no reports yet
                } elseif ($activity->status === 'Not Started') {
                    $activityProgress = 0;
                } else {
                    $activityProgress = 0;
                }
            }
            
            $totalProgress += $activityProgress;
        }

        // Calculate overall progress percentage (average of all activities)
        $overallProgress = round($totalProgress / $totalActivities);
        
        // Ensure progress is between 0 and 100
        $overallProgress = max(0, min(100, $overallProgress));
        
        $mainTask->update(['progress_percentage' => $overallProgress]);
        
        \Log::info('Task progress calculated automatically', [
            'task_id' => $mainTask->id,
            'task_name' => $mainTask->name,
            'progress_percentage' => $overallProgress,
            'total_activities' => $totalActivities,
            'calculated_at' => now()->toDateTimeString()
        ]);
    }

    public function generatePdf(Request $request)
    {
        $type = $request->query('type');
        $taskId = $request->query('task_id');
        $month = $request->query('month');
        $year = $request->query('year');

        // Handle calendar export
        if ($type === 'calendar' && $month && $year) {
            return $this->generateCalendarPdf($month, $year);
        }

        // Handle summary or detailed reports
        if ($type === 'summary' || $type === 'detailed') {
            return $this->generateTasksReportPdf($type);
        }

        // Handle single task PDF (default behavior)
        if ($taskId) {
            $mainTask = MainTask::with([
                'teamLeader',
                'creator',
                'activities.assignedUsers',
                'activities.reports.user',
                'activities.reports.approver',
                'activities.comments.user',
                'comments.user',
                'attachments.user'
            ])->findOrFail($taskId);

            // Calculate current progress
            $this->calculateTaskProgress($mainTask);
            $mainTask->refresh();

            // Collect all issues/delays from reports
            $allIssues = [];
            $allDelays = [];
            foreach ($mainTask->activities as $activity) {
                foreach ($activity->reports as $report) {
                    if ($report->reason_if_delayed) {
                        $allDelays[] = [
                            'activity' => $activity->name,
                            'reporter' => $report->user->name ?? 'Unknown',
                            'date' => $report->report_date,
                            'reason' => $report->reason_if_delayed,
                            'status' => $report->status
                        ];
                    }
                    if ($report->completion_status === 'Delayed' || $report->completion_status === 'Behind Schedule') {
                        $allIssues[] = [
                            'activity' => $activity->name,
                            'reporter' => $report->user->name ?? 'Unknown',
                            'date' => $report->report_date,
                            'issue' => $report->work_description,
                            'status' => $report->completion_status
                        ];
                    }
                }
            }

            $data = [
                'mainTask' => $mainTask,
                'logoPath' => public_path('assets/img/logo.png'),
                'allIssues' => $allIssues,
                'allDelays' => $allDelays,
            ];

            $pdf = Pdf::loadView('modules.tasks.pdf-report', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $fileName = 'Task_Report_' . str_replace(' ', '_', $mainTask->name) . '_' . $taskId . '.pdf';
            
            return $pdf->stream($fileName);
        }

        // Default: return error if no valid parameters
        return redirect()->back()->with('error', 'Invalid PDF export parameters');
    }

    private function generateTasksReportPdf($type)
    {
        $mainTasks = MainTask::with(['teamLeader', 'activities'])
            ->orderBy('start_date', 'desc')
            ->get();

        foreach ($mainTasks as $task) {
            $this->calculateTaskProgress($task);
        }

        $data = [
            'mainTasks' => $mainTasks,
            'type' => $type,
            'logoPath' => public_path('assets/img/logo.png'),
        ];

        $view = $type === 'summary' ? 'modules.tasks.pdf-summary' : 'modules.tasks.pdf-detailed';
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', $type === 'summary' ? 'portrait' : 'landscape');
        
        $fileName = 'Tasks_' . ucfirst($type) . '_Report_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->stream($fileName);
    }

    private function generateCalendarPdf($month, $year)
    {
        $month = (int)$month;
        $year = (int)$year;
        
        // Get all tasks that overlap with the specified month
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        
        $mainTasks = MainTask::with(['teamLeader'])
            ->where(function($query) use ($monthStart, $monthEnd) {
                $query->where(function($q) use ($monthStart, $monthEnd) {
                    $q->where('start_date', '<=', $monthEnd)
                      ->where('end_date', '>=', $monthStart);
                });
            })
            ->orderBy('start_date', 'asc')
            ->get();

        // Group tasks by date
        $tasksByDate = [];
        foreach ($mainTasks as $task) {
            $start = \Carbon\Carbon::parse($task->start_date);
            $end = \Carbon\Carbon::parse($task->end_date);
            $taskStart = $start->copy()->max($monthStart);
            $taskEnd = $end->copy()->min($monthEnd);
            
            for ($date = $taskStart->copy(); $date->lte($taskEnd); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                if (!isset($tasksByDate[$dateKey])) {
                    $tasksByDate[$dateKey] = [];
                }
                $tasksByDate[$dateKey][] = $task;
            }
        }

        $data = [
            'mainTasks' => $mainTasks,
            'tasksByDate' => $tasksByDate,
            'month' => $month,
            'year' => $year,
            'monthName' => \Carbon\Carbon::create($year, $month, 1)->format('F Y'),
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'logoPath' => public_path('assets/img/logo.png'),
        ];

        $pdf = Pdf::loadView('modules.tasks.pdf-calendar', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F_Y');
        $fileName = 'Calendar_Export_' . $monthName . '.pdf';
        
        return $pdf->stream($fileName);
    }

    /**
     * Generate comprehensive PDF report for a specific task
     */
    public function generateTaskReportPdf($id)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);
        
        $task = MainTask::with([
            'teamLeader:id,name,email',
            'activities.assignedUsers:id,name,email',
            'activities.reports.user:id,name,email',
            'activities.reports.approver:id,name',
            'activities.reports.attachments'
        ])->findOrFail($id);
        
        // Check permissions - staff can only view tasks they're assigned to
        if (!$isManager) {
            $canView = $task->team_leader_id == $user->id || 
                      $task->activities()->whereHas('assignedUsers', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      })->exists();
            
            if (!$canView) {
                abort(403, 'You do not have permission to view this task report');
            }
        }
        
        // Calculate current progress
        $this->calculateTaskProgress($task);
        $task->refresh();
        
        // Prepare data for PDF
        $data = [
            'task' => $task,
            'logoPath' => public_path('assets/img/logo.png'),
            'generated_at' => now()->format('F d, Y \a\t H:i'),
            'generated_by' => $user->name,
        ];
        
        $pdf = Pdf::loadView('modules.tasks.task-report-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $fileName = 'Task_Report_' . str_replace(' ', '_', $task->name) . '_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->stream($fileName);
    }

    public function analyticsPdf(Request $request)
    {
        $user = Auth::user();
        $isManager = $this->isManager($user);
        
        if (!$isManager) {
            abort(403, 'Unauthorized access');
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $statusFilter = $request->query('status');

        $query = MainTask::with(['teamLeader', 'activities']);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $mainTasks = $query->orderByDesc('created_at')->get();

        // Calculate statistics
        foreach ($mainTasks as $task) {
            $this->calculateTaskProgress($task);
        }

        $totalTasks = $mainTasks->count();
        $completedTasks = $mainTasks->where('status', 'completed')->count();
        $inProgressTasks = $mainTasks->where('status', 'in_progress')->count();
        $planningTasks = $mainTasks->where('status', 'planning')->count();
        $delayedTasks = $mainTasks->where('status', 'delayed')->count();
        $avgProgress = round($mainTasks->avg('progress_percentage') ?? 0);
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Priority breakdown
        $lowPriority = $mainTasks->where('priority', 'Low')->count();
        $normalPriority = $mainTasks->filter(function($task) {
            return $task->priority === 'Normal' || $task->priority === null;
        })->count();
        $highPriority = $mainTasks->where('priority', 'High')->count();
        $criticalPriority = $mainTasks->where('priority', 'Critical')->count();

        // Category statistics
        $categoryStats = $mainTasks->groupBy('category')->map(function($tasks) {
            return [
                'count' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                'avg_progress' => round($tasks->avg('progress_percentage') ?? 0)
            ];
        });

        // Team leader statistics
        $leaderStats = $mainTasks->groupBy('team_leader_id')->map(function($tasks) {
            $leader = $tasks->first()->teamLeader ?? null;
            return [
                'name' => $leader ? $leader->name : 'Unassigned',
                'count' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
            ];
        });

        $data = [
            'mainTasks' => $mainTasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'planningTasks' => $planningTasks,
            'delayedTasks' => $delayedTasks,
            'avgProgress' => $avgProgress,
            'completionRate' => $completionRate,
            'lowPriority' => $lowPriority,
            'normalPriority' => $normalPriority,
            'highPriority' => $highPriority,
            'criticalPriority' => $criticalPriority,
            'categoryStats' => $categoryStats,
            'leaderStats' => $leaderStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statusFilter' => $statusFilter,
            'generatedAt' => now()->format('d M Y, H:i:s'),
        ];

        $pdf = Pdf::loadView('modules.tasks.analytics-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $fileName = 'Tasks_Analytics_Report_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->stream($fileName);
    }

    /**
     * Serve activity report attachment with permission checks
     */
    public function serveAttachment($reportId, $filename)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                abort(403, 'Authentication required');
            }

            // Load the report with relationships
            $report = ActivityReport::with(['user', 'activity.mainTask', 'activity.assignedUsers'])
                ->findOrFail($reportId);

            // Check permissions
            $isManager = $this->isManager($user);
            $isReporter = $report->user_id == $user->id;
            $isAssigned = $report->activity->assignedUsers->contains('id', $user->id);
            $isTeamLeader = $report->activity->mainTask && $report->activity->mainTask->team_leader_id == $user->id;

            // Allow access if user is manager, reporter, assigned to activity, or team leader
            if (!$isManager && !$isReporter && !$isAssigned && !$isTeamLeader) {
                abort(403, 'You do not have permission to access this file');
            }

            // Find the attachment
            $attachment = ActivityReportAttachment::where('report_id', $reportId)
                ->where('file_name', $filename)
                ->first();

            if (!$attachment) {
                // Try to find by file path
                $attachment = ActivityReportAttachment::where('report_id', $reportId)
                    ->where('file_path', 'like', '%' . $filename)
                    ->first();
            }

            if (!$attachment) {
                abort(404, 'Attachment not found');
            }

            // Try multiple paths to find the file
            $paths = [
                storage_path('app/public/' . $attachment->file_path),
                storage_path('app/' . $attachment->file_path),
                public_path('storage/' . $attachment->file_path),
            ];

            // Also try with just the filename in the report directory
            if (strpos($attachment->file_path, 'activity_reports/') === false) {
                $paths[] = storage_path('app/public/activity_reports/' . $reportId . '/' . $filename);
                $paths[] = public_path('storage/activity_reports/' . $reportId . '/' . $filename);
            }

            $path = null;
            foreach ($paths as $testPath) {
                if (file_exists($testPath) && is_file($testPath)) {
                    $path = $testPath;
                    break;
                }
            }

            if (!$path || !file_exists($path)) {
                \Log::error('Activity report attachment not found', [
                    'report_id' => $reportId,
                    'filename' => $filename,
                    'attachment_file_path' => $attachment->file_path,
                    'tried_paths' => $paths,
                ]);
                abort(404, 'File not found');
            }

            // Get file contents and MIME type
            $file = file_get_contents($path);
            $mimeType = mime_content_type($path);

            if (!$mimeType) {
                // Fallback MIME type based on extension
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'txt' => 'text/plain',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            }

            // Determine content disposition (inline for images/PDFs, attachment for others)
            $isInline = in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']);
            $disposition = $isInline ? 'inline' : 'attachment';

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', $disposition . '; filename="' . $attachment->file_name . '"')
                ->header('Content-Length', filesize($path))
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Pragma', 'public');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Report or attachment not found');
        } catch (\Exception $e) {
            \Log::error('Error serving activity report attachment', [
                'report_id' => $reportId,
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error serving file');
        }
    }

    /**
     * Get user tasks for performance module sync
     */
    public function getUserTasks(Request $request) {
        $user = Auth::user();
        
        $taskActivities = TaskActivity::whereHas('assignedUsers', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with('mainTask:id,name')
        ->orderBy('end_date', 'desc')
        ->get(['id', 'name', 'main_task_id', 'status', 'end_date']);

        return response()->json([
            'success' => true,
            'tasks' => $taskActivities
        ]);
    }
}
