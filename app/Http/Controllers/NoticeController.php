<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeAcknowledgment;
use App\Models\NoticeAttachment;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NoticeController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notices/announcements
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user can manage notices (HR, HOD, General Manager, System Admin)
        $canManage = $user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin']);
        
        if ($canManage) {
            // Managers see all notices
            $notices = Notice::with(['creator', 'acknowledgments', 'roles'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Regular users see only active notices they should see
            $notices = Notice::with(['creator', 'acknowledgments', 'roles'])
                ->where('is_active', true)
                ->where(function($query) use ($user) {
                    $query->where('show_to_all', true)
                        ->orWhereHas('roles', function($q) use ($user) {
                            $q->whereIn('roles.id', $user->roles->pluck('id'));
                        });
                })
                ->where(function($query) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>=', now());
                })
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        
        return view('modules.notices.index', compact('notices', 'canManage'));
    }

    /**
     * Show the form for creating a new notice
     */
    public function create()
    {
        $user = Auth::user();
        
        // Only HR, HOD, General Manager, or System Admin can create notices
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403, 'You do not have permission to create notices.');
        }
        
        $roles = Role::orderBy('name')->get();
        
        return view('modules.notices.create', compact('roles'));
    }

    /**
     * Store a newly created notice
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only HR, HOD, General Manager, or System Admin can create notices
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403, 'You do not have permission to create notices.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:normal,important,urgent',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'show_to_all' => 'boolean',
            'is_active' => 'boolean',
            'require_acknowledgment' => 'boolean',
            'allow_redisplay' => 'boolean',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'exists:roles,id',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx',
        ]);
        
        DB::beginTransaction();
        try {
            $notice = Notice::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'priority' => $validated['priority'],
                'start_date' => $validated['start_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'show_to_all' => $request->has('show_to_all'),
                'is_active' => $request->has('is_active'),
                'require_acknowledgment' => $request->has('require_acknowledgment'),
                'allow_redisplay' => $request->has('allow_redisplay'),
                'created_by' => $user->id,
            ]);
            
            // Attach roles if not showing to all
            if (!$notice->show_to_all && !empty($validated['target_roles'])) {
                $notice->roles()->attach($validated['target_roles']);
            }
            
            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $storedName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('notices/attachments', $storedName, 'public');
                    
                    NoticeAttachment::create([
                        'notice_id' => $notice->id,
                        'original_name' => $originalName,
                        'stored_name' => $storedName,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }
            
            DB::commit();
            
            // Send notifications via Email and SMS to all applicable users
            try {
                $this->sendNoticeNotifications($notice);
            } catch (\Exception $e) {
                // Log error but don't fail notice creation
                Log::error('Failed to send notice notifications: ' . $e->getMessage(), [
                    'notice_id' => $notice->id,
                    'error' => $e->getTraceAsString()
                ]);
            }
            
            return redirect()->route('notices.index')
                ->with('success', 'Notice created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create notice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified notice
     */
    public function show($id)
    {
        $notice = Notice::with(['creator', 'acknowledgments.user', 'attachments', 'roles'])->findOrFail($id);
        $user = Auth::user();
        
        // Check if user can view this notice
        if (!$notice->shouldShowToUser($user) && !$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403, 'You do not have permission to view this notice.');
        }
        
        $hasAcknowledged = $notice->hasAcknowledged($user->id);
        
        return view('modules.notices.show', compact('notice', 'hasAcknowledged'));
    }

    /**
     * Show the form for editing the specified notice
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        // Only HR, HOD, General Manager, or System Admin can edit notices
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403, 'You do not have permission to edit notices.');
        }
        
        $notice = Notice::with(['roles'])->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        
        return view('modules.notices.edit', compact('notice', 'roles'));
    }

    /**
     * Update the specified notice
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Only HR, HOD, General Manager, or System Admin can update notices
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403, 'You do not have permission to update notices.');
        }
        
        $notice = Notice::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:normal,important,urgent',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'show_to_all' => 'boolean',
            'is_active' => 'boolean',
            'require_acknowledgment' => 'boolean',
            'allow_redisplay' => 'boolean',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'exists:roles,id',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx,ppt,pptx',
        ]);
        
        DB::beginTransaction();
        try {
            $notice->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'priority' => $validated['priority'],
                'start_date' => $validated['start_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'show_to_all' => $request->has('show_to_all'),
                'is_active' => $request->has('is_active'),
                'require_acknowledgment' => $request->has('require_acknowledgment'),
                'allow_redisplay' => $request->has('allow_redisplay'),
            ]);
            
            // Update roles
            if ($notice->show_to_all) {
                $notice->roles()->detach();
            } else {
                $notice->roles()->sync($validated['target_roles'] ?? []);
            }
            
            // Handle new attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $storedName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('notices/attachments', $storedName, 'public');
                    
                    NoticeAttachment::create([
                        'notice_id' => $notice->id,
                        'original_name' => $originalName,
                        'stored_name' => $storedName,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('notices.index')
                ->with('success', 'Notice updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update notice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified notice
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // Only HR, HOD, General Manager, or System Admin can delete notices
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403, 'You do not have permission to delete notices.');
        }
        
        $notice = Notice::findOrFail($id);
        
        // Delete attachments
        foreach ($notice->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
        
        $notice->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notice deleted successfully.'
        ]);
    }

    /**
     * Acknowledge a notice
     */
    public function acknowledge(Request $request, $id)
    {
        $user = Auth::user();
        $notice = Notice::findOrFail($id);
        
        if (!$notice->shouldShowToUser($user)) {
            abort(403, 'You cannot acknowledge this notice.');
        }
        
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);
        
        NoticeAcknowledgment::updateOrCreate(
            [
                'notice_id' => $notice->id,
                'user_id' => $user->id,
            ],
            [
                'notes' => $validated['notes'] ?? null,
                'acknowledged_at' => now(),
            ]
        );
        
        return redirect()->back()
            ->with('success', 'Notice acknowledged successfully.');
    }

    /**
     * Get unacknowledged notices for current user (for popup display)
     */
    public function getUnacknowledged(Request $request)
    {
        $user = Auth::user();
        
        // Get all active notices that should be shown to user and haven't been acknowledged
        $notices = Notice::with(['creator', 'attachments', 'roles'])
            ->where('is_active', true)
            ->where(function($query) use ($user) {
                $query->where('show_to_all', true)
                    ->orWhereHas('roles', function($q) use ($user) {
                        $q->whereIn('roles.id', $user->roles->pluck('id'));
                    });
            })
            ->where(function($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            })
            ->whereDoesntHave('acknowledgments', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($notice) use ($user) {
                return $notice->shouldShowToUser($user);
            })
            ->map(function($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'content' => $notice->content,
                    'priority' => $notice->priority,
                    'require_acknowledgment' => $notice->require_acknowledgment,
                    'created_at' => $notice->created_at->format('Y-m-d H:i:s'),
                    'creator_name' => $notice->creator->name ?? 'System',
                ];
            });
        
        return response()->json([
            'success' => true,
            'notices' => $notices->values(),
        ]);
    }

    /**
     * Get acknowledgment statistics
     */
    public function acknowledgmentStats($id)
    {
        $user = Auth::user();
        
        // Only HR, HOD, General Manager, or System Admin can view stats
        if (!$user->hasAnyRole(['HR Officer', 'HOD', 'General Manager', 'System Admin'])) {
            abort(403);
        }
        
        $notice = Notice::with(['acknowledgments.user'])->findOrFail($id);
        
        $totalUsers = 0;
        if ($notice->show_to_all) {
            $totalUsers = User::where('is_active', true)->count();
        } else {
            $roleIds = $notice->roles->pluck('id');
            $totalUsers = User::whereHas('roles', function($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            })->where('is_active', true)->count();
        }
        
        $acknowledgedCount = $notice->acknowledgments->count();
        $pendingCount = $totalUsers - $acknowledgedCount;
        
        $acknowledgments = $notice->acknowledgments->map(function($ack) {
            return [
                'user_name' => $ack->user->name ?? 'N/A',
                'user_email' => $ack->user->email ?? 'N/A',
                'acknowledged_at' => $ack->acknowledged_at->format('Y-m-d H:i:s'),
                'notes' => $ack->notes,
            ];
        });
        
        return response()->json([
            'success' => true,
            'total_users' => $totalUsers,
            'acknowledged_count' => $acknowledgedCount,
            'pending_count' => $pendingCount,
            'acknowledgments' => $acknowledgments,
        ]);
    }
}
