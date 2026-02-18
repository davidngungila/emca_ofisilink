<?php

namespace App\Http\Controllers;

use App\Services\PerformanceScoringService;
use App\Models\Assessment;
use App\Models\MainTask;
use App\Models\TaskActivity;
use App\Models\ActivityReport;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PerformanceReportController extends Controller
{
    protected $scoringService;

    public function __construct(PerformanceScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Show unified performance report page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'CEO', 'HOD'])) {
            abort(403, 'Unauthorized access');
        }

        $orgSettings = OrganizationSetting::getSettings();
        
        // Get employees - include all active users, not just those with employee records
        $employees = \App\Models\User::where('is_active', true)
            ->with('primaryDepartment')
            ->orderBy('name')
            ->get();
            
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        return view('modules.hr.performance-report-unified', compact('orgSettings', 'employees', 'departments'));
    }

    /**
     * Generate unified performance report with task details
     */
    public function generateUnifiedReport(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'CEO', 'HOD'])) {
            abort(403, 'Unauthorized access');
        }

        $orgSettings = OrganizationSetting::getSettings();
        $financialYear = $request->integer('financial_year', $orgSettings->current_financial_year ?? date('Y'));
        $employeeId = $request->input('employee_id');
        $departmentId = $request->input('department_id');
        $reportType = $request->string('type', 'individual'); // individual, department, organization

        // Validate employee_id if provided
        if ($employeeId && (!is_numeric($employeeId) || $employeeId <= 0)) {
            return response()->json(['success' => false, 'message' => 'Invalid employee ID'], 400);
        }

        // If individual report but no employee_id provided, use current user
        if ($reportType === 'individual' && empty($employeeId)) {
            $employeeId = $user->id;
        }

        $fyDates = $orgSettings->getFinancialYearDates($financialYear);

        $report = [];

        try {
            switch ($reportType) {
                case 'individual':
                    if (empty($employeeId)) {
                        return response()->json(['success' => false, 'message' => 'Employee ID is required for individual reports'], 400);
                    }
                    $report = $this->generateIndividualReport($employeeId, $financialYear, $fyDates);
                    break;
                case 'department':
                    if (!$departmentId) {
                        return response()->json(['success' => false, 'message' => 'Department ID required'], 400);
                    }
                    $report = $this->generateDepartmentReport($departmentId, $financialYear, $fyDates);
                    break;
                case 'organization':
                    $report = $this->generateOrganizationReport($financialYear, $fyDates);
                    break;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Employee or department not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Error generating performance report: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Generate individual performance report with task details
     */
    private function generateIndividualReport($employeeId, $financialYear, $fyDates)
    {
        if (empty($employeeId) || $employeeId <= 0) {
            throw new \InvalidArgumentException('Invalid employee ID');
        }
        
        $employee = \App\Models\User::find($employeeId);
        if (!$employee) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }
        
        // Calculate performance score
        $performanceScore = $this->scoringService->calculateIndividualPerformance($employeeId, $financialYear);

        // Get assessments
        $assessments = Assessment::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$fyDates['start'], $fyDates['end']])
            ->with(['activities', 'activities.progressReports'])
            ->get();

        // Get tasks assigned to employee
        $tasks = TaskActivity::whereHas('assignedUsers', function($q) use ($employeeId) {
            $q->where('user_id', $employeeId);
        })
        ->where(function($q) use ($fyDates) {
            $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
              ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
        })
        ->with(['mainTask', 'assessmentActivity', 'reports' => function($q) use ($employeeId) {
            $q->where('user_id', $employeeId)->where('status', 'Approved');
        }])
        ->get();

        // Calculate task metrics
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'Completed')->count();
        $strategicTasks = $tasks->filter(fn($t) => $t->assessment_activity_id !== null);
        $completedStrategicTasks = $strategicTasks->where('status', 'Completed')->count();

        // Get quality metrics
        $reports = ActivityReport::where('user_id', $employeeId)
            ->where('status', 'Approved')
            ->whereBetween('report_date', [$fyDates['start'], $fyDates['end']])
            ->get();

        $qualityAverage = $reports->whereNotNull('quality_rating')->avg('quality_rating') ?? 0;
        $initiativeTasks = $reports->where('initiative_bonus', true)->count();

        // Get top contributing tasks
        $topTasks = $tasks->filter(function($task) {
            return $task->assessment_activity_id !== null && $task->status === 'Completed';
        })->take(5)->map(function($task) {
            $assessmentActivity = $task->assessmentActivity;
            return [
                'task_name' => $task->name,
                'main_task' => $task->mainTask->name ?? 'N/A',
                'performance_objective' => $assessmentActivity->activity_name ?? 'N/A',
                'contribution_percentage' => $assessmentActivity->contribution_percentage ?? 0,
                'completed_date' => $task->actual_end_date?->format('Y-m-d'),
            ];
        });

        return [
            'type' => 'individual',
            'financial_year' => $financialYear,
            'period' => [
                'start' => $fyDates['start']->format('Y-m-d'),
                'end' => $fyDates['end']->format('Y-m-d'),
            ],
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'department' => $employee->primaryDepartment->name ?? 'N/A',
            ],
            'performance_score' => $performanceScore,
            'task_metrics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
                'strategic_tasks' => $strategicTasks->count(),
                'completed_strategic_tasks' => $completedStrategicTasks,
                'strategic_completion_rate' => $strategicTasks->count() > 0 
                    ? round(($completedStrategicTasks / $strategicTasks->count()) * 100, 2) 
                    : 0,
            ],
            'quality_metrics' => [
                'average_rating' => round($qualityAverage, 2),
                'total_reports' => $reports->count(),
                'initiative_tasks' => $initiativeTasks,
            ],
            'top_contributing_tasks' => $topTasks->values(),
            'assessments' => $assessments->map(function($assessment) {
                return [
                    'main_responsibility' => $assessment->main_responsibility,
                    'activities_count' => $assessment->activities->count(),
                    'contribution_percentage' => $assessment->contribution_percentage,
                ];
            }),
            'components' => [
                [
                    'name' => 'Task Completion',
                    'score' => $performanceScore['components']['task_completion']['score'],
                    'weight' => $performanceScore['components']['task_completion']['weight'],
                    'contribution' => $performanceScore['components']['task_completion']['contribution'],
                ],
                [
                    'name' => 'Quality',
                    'score' => $performanceScore['components']['quality']['score'],
                    'weight' => $performanceScore['components']['quality']['weight'],
                    'contribution' => $performanceScore['components']['quality']['contribution'],
                ],
                [
                    'name' => 'Strategic Tasks',
                    'score' => $performanceScore['components']['strategic']['score'],
                    'weight' => $performanceScore['components']['strategic']['weight'],
                    'contribution' => $performanceScore['components']['strategic']['contribution'],
                ],
                [
                    'name' => 'Initiative',
                    'score' => $performanceScore['components']['initiative']['score'],
                    'weight' => $performanceScore['components']['initiative']['weight'],
                    'contribution' => $performanceScore['components']['initiative']['contribution'],
                ],
            ],
        ];
    }

    /**
     * Generate department performance report
     */
    private function generateDepartmentReport($departmentId, $financialYear, $fyDates)
    {
        $department = \App\Models\Department::findOrFail($departmentId);
        
        $employees = \App\Models\User::where('primary_department_id', $departmentId)
            ->where('is_active', true)
            ->get();

        $departmentScore = 0;
        $employeeReports = [];
        $totalTasks = 0;
        $totalCompletedTasks = 0;
        $totalStrategicTasks = 0;
        $totalCompletedStrategic = 0;

        foreach ($employees as $employee) {
            $employeeReport = $this->generateIndividualReport($employee->id, $financialYear, $fyDates);
            $employeeReports[] = $employeeReport;
            
            $departmentScore += $employeeReport['performance_score']['final_score'];
            $totalTasks += $employeeReport['task_metrics']['total_tasks'];
            $totalCompletedTasks += $employeeReport['task_metrics']['completed_tasks'];
            $totalStrategicTasks += $employeeReport['task_metrics']['strategic_tasks'];
            $totalCompletedStrategic += $employeeReport['task_metrics']['completed_strategic_tasks'];
        }

        $avgScore = $employees->count() > 0 ? $departmentScore / $employees->count() : 0;

        return [
            'type' => 'department',
            'financial_year' => $financialYear,
            'period' => [
                'start' => $fyDates['start']->format('Y-m-d'),
                'end' => $fyDates['end']->format('Y-m-d'),
            ],
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
            ],
            'overall_score' => round($avgScore, 2),
            'employee_count' => $employees->count(),
            'aggregated_metrics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $totalCompletedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($totalCompletedTasks / $totalTasks) * 100, 2) : 0,
                'strategic_tasks' => $totalStrategicTasks,
                'completed_strategic_tasks' => $totalCompletedStrategic,
                'strategic_completion_rate' => $totalStrategicTasks > 0 
                    ? round(($totalCompletedStrategic / $totalStrategicTasks) * 100, 2) 
                    : 0,
            ],
            'employees' => $employeeReports,
        ];
    }

    /**
     * Generate organization performance report
     */
    private function generateOrganizationReport($financialYear, $fyDates)
    {
        $departments = \App\Models\Department::where('is_active', true)->get();
        
        $orgScore = 0;
        $departmentReports = [];
        $totalTasks = 0;
        $totalCompletedTasks = 0;

        foreach ($departments as $department) {
            $deptReport = $this->generateDepartmentReport($department->id, $financialYear, $fyDates);
            $departmentReports[] = $deptReport;
            
            $orgScore += $deptReport['overall_score'];
            $totalTasks += $deptReport['aggregated_metrics']['total_tasks'];
            $totalCompletedTasks += $deptReport['aggregated_metrics']['completed_tasks'];
        }

        $avgScore = $departments->count() > 0 ? $orgScore / $departments->count() : 0;

        return [
            'type' => 'organization',
            'financial_year' => $financialYear,
            'period' => [
                'start' => $fyDates['start']->format('Y-m-d'),
                'end' => $fyDates['end']->format('Y-m-d'),
            ],
            'overall_score' => round($avgScore, 2),
            'department_count' => $departments->count(),
            'aggregated_metrics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $totalCompletedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($totalCompletedTasks / $totalTasks) * 100, 2) : 0,
            ],
            'departments' => $departmentReports,
        ];
    }

    /**
     * Export report as PDF
     */
    public function exportPdf(Request $request)
    {
        $reportData = $this->generateUnifiedReport($request)->getData(true);
        
        // Generate PDF using your PDF library (e.g., DomPDF, TCPDF)
        // This is a placeholder - implement based on your PDF generation setup
        
        return response()->json([
            'success' => true,
            'message' => 'PDF export functionality to be implemented',
            'data' => $reportData['report']
        ]);
    }

    /**
     * Get quarterly performance breakdown
     */
    public function getQuarterlyBreakdown(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'CEO', 'HOD'])) {
            abort(403, 'Unauthorized access');
        }

        $orgSettings = OrganizationSetting::getSettings();
        $financialYear = $request->integer('financial_year', $orgSettings->current_financial_year ?? date('Y'));
        $employeeId = $request->integer('employee_id');

        $quarters = [];
        
        for ($q = 1; $q <= 4; $q++) {
            $quarterData = app(\App\Services\FinancialYearService::class)->getQuarterlyPeriod(
                Carbon::create($financialYear, 1, 1)->addMonths(($q - 1) * 3)
            );
            
            $quarterStart = $quarterData['start'];
            $quarterEnd = $quarterData['end'];

            if ($employeeId) {
                $quarterScore = $this->scoringService->calculateIndividualPerformance($employeeId, $financialYear);
                // Filter to quarter period (would need to modify scoring service)
            }

            $quarters[$q] = [
                'quarter' => $q,
                'label' => $quarterData['label'],
                'start' => $quarterStart->format('Y-m-d'),
                'end' => $quarterEnd->format('Y-m-d'),
                'tasks' => TaskActivity::whereHas('assignedUsers', function($query) use ($employeeId) {
                    if ($employeeId) {
                        $query->where('user_id', $employeeId);
                    }
                })
                ->where(function($query) use ($quarterStart, $quarterEnd) {
                    $query->whereBetween('start_date', [$quarterStart, $quarterEnd])
                          ->orWhereBetween('end_date', [$quarterStart, $quarterEnd]);
                })
                ->count(),
                'completed_tasks' => TaskActivity::whereHas('assignedUsers', function($query) use ($employeeId) {
                    if ($employeeId) {
                        $query->where('user_id', $employeeId);
                    }
                })
                ->where('status', 'Completed')
                ->where(function($query) use ($quarterStart, $quarterEnd) {
                    $query->whereBetween('start_date', [$quarterStart, $quarterEnd])
                          ->orWhereBetween('end_date', [$quarterStart, $quarterEnd]);
                })
                ->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'financial_year' => $financialYear,
            'quarters' => $quarters
        ]);
    }
}

