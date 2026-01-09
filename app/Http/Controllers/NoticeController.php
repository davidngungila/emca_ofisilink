<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeAcknowledgment;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    /**
     * Display a listing of notices
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $notices = Notice::with(['creator', 'updater', 'acknowledgments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('modules.notices.index', compact('notices'));
    }

    /**
     * Show the form for creating a new notice
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.notices.create', compact('roles'));
    }

    /**
     * Store a newly created notice
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:normal,important,urgent',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'show_to_all' => 'boolean',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'exists:roles,id',
            'require_acknowledgment' => 'boolean',
            'allow_redisplay' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            // Handle checkbox values properly (checkboxes send '1' when checked, nothing when unchecked)
            $isActive = $request->has('is_active') && $request->is_active == '1';
            $showToAll = $request->has('show_to_all') && $request->show_to_all == '1';
            $requireAcknowledgment = $request->has('require_acknowledgment') && $request->require_acknowledgment == '1';
            $allowRedisplay = $request->has('allow_redisplay') && $request->allow_redisplay == '1';
            
            // Default values if not provided
            if (!$request->has('is_active')) $isActive = true;
            if (!$request->has('show_to_all')) $showToAll = true;
            if (!$request->has('require_acknowledgment')) $requireAcknowledgment = true;
            if (!$request->has('allow_redisplay')) $allowRedisplay = false;
            
            // Handle file uploads
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('advertisements', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }
            
            $notice = Notice::create([
                'title' => $request->title,
                'content' => $request->content,
                'attachments' => !empty($attachments) ? $attachments : null,
                'priority' => $request->priority,
                'start_date' => $request->start_date ?: null,
                'expiry_date' => $request->expiry_date ?: null,
                'is_active' => $isActive,
                'show_to_all' => $showToAll,
                'target_roles' => $showToAll ? null : ($request->target_roles ?? []),
                'require_acknowledgment' => $requireAcknowledgment,
                'allow_redisplay' => $allowRedisplay,
                'created_by' => $user->id,
            ]);
            
            Log::info('Notice created', [
                'notice_id' => $notice->id,
                'title' => $notice->title,
                'created_by' => $user->id
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notice created successfully',
                    'notice' => $notice
                ]);
            }
            
            return redirect()->route('notices.index')
                ->with('success', 'Notice created successfully');
                
        } catch (\Exception $e) {
            Log::error('Error creating notice: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating notice: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error creating notice')->withInput();
        }
    }

    /**
     * Display the specified notice
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $notice = Notice::with(['creator', 'updater', 'acknowledgments.user'])
            ->findOrFail($id);
        
        return view('modules.notices.show', compact('notice'));
    }

    /**
     * Show the form for editing the specified notice
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $notice = Notice::findOrFail($id);
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.notices.edit', compact('notice', 'roles'));
    }

    /**
     * Update the specified notice
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $notice = Notice::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:normal,important,urgent',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'show_to_all' => 'boolean',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'exists:roles,id',
            'require_acknowledgment' => 'boolean',
            'allow_redisplay' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $notice->update([
                'title' => $request->title,
                'content' => $request->content,
                'priority' => $request->priority,
                'start_date' => $request->start_date,
                'expiry_date' => $request->expiry_date,
                'is_active' => $request->has('is_active') ? $request->is_active : $notice->is_active,
                'show_to_all' => $request->has('show_to_all') ? $request->show_to_all : $notice->show_to_all,
                'target_roles' => $request->show_to_all ? null : $request->target_roles,
                'require_acknowledgment' => $request->has('require_acknowledgment') ? $request->require_acknowledgment : $notice->require_acknowledgment,
                'allow_redisplay' => $request->has('allow_redisplay') ? $request->allow_redisplay : $notice->allow_redisplay,
                'updated_by' => $user->id,
            ]);
            
            Log::info('Notice updated', [
                'notice_id' => $notice->id,
                'title' => $notice->title,
                'updated_by' => $user->id
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notice updated successfully',
                    'notice' => $notice
                ]);
            }
            
            return redirect()->route('notices.index')
                ->with('success', 'Notice updated successfully');
                
        } catch (\Exception $e) {
            Log::error('Error updating notice: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating notice: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error updating notice')->withInput();
        }
    }

    /**
     * Remove the specified notice
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            $notice = Notice::findOrFail($id);
            $notice->delete();
            
            Log::info('Notice deleted', [
                'notice_id' => $id,
                'deleted_by' => $user->id
            ]);
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notice deleted successfully'
                ]);
            }
            
            return redirect()->route('notices.index')
                ->with('success', 'Notice deleted successfully');
                
        } catch (\Exception $e) {
            Log::error('Error deleting notice: ' . $e->getMessage());
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting notice: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error deleting notice');
        }
    }

    /**
     * Get unacknowledged notices for current user
     */
    public function getUnacknowledged(Request $request)
    {
        $user = Auth::user();
        
        $notices = Notice::where('is_active', true)
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
                    'attachments' => $notice->getAttachmentUrls(),
                ];
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'notices' => $notices
        ]);
    }

    /**
     * Acknowledge a notice
     */
    public function acknowledge(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            $notice = Notice::findOrFail($id);
            
            // Check if already acknowledged
            $existing = NoticeAcknowledgment::where('advertisement_id', $id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existing) {
                // Update acknowledgment if allow_redisplay
                if ($notice->allow_redisplay) {
                    $existing->update([
                        'acknowledged_at' => now(),
                        'notes' => $request->notes ?? null,
                    ]);
                }
            } else {
                // Create new acknowledgment
                NoticeAcknowledgment::create([
                    'advertisement_id' => $id,
                    'user_id' => $user->id,
                    'acknowledged_at' => now(),
                    'notes' => $request->notes ?? null,
                ]);
            }
            
            Log::info('Notice acknowledged', [
                'notice_id' => $id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notice acknowledged successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error acknowledging notice: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error acknowledging notice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get acknowledgment statistics for a notice
     */
    public function getAcknowledgmentStats($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $notice = Notice::with('acknowledgments.user')->findOrFail($id);
        
        $totalUsers = 0;
        if ($notice->show_to_all) {
            $totalUsers = User::where('is_active', true)->count();
        } else {
            $roleIds = $notice->target_roles ?? [];
            $totalUsers = User::whereHas('roles', function($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            })->where('is_active', true)->count();
        }
        
        $acknowledgedCount = $notice->acknowledgments->count();
        $pendingCount = $totalUsers - $acknowledgedCount;
        
        return response()->json([
            'success' => true,
            'total_users' => $totalUsers,
            'acknowledged_count' => $acknowledgedCount,
            'pending_count' => $pendingCount,
            'acknowledgments' => $notice->acknowledgments->map(function($ack) {
                return [
                    'user_name' => $ack->user->name,
                    'user_email' => $ack->user->email,
                    'acknowledged_at' => $ack->acknowledged_at->format('Y-m-d H:i:s'),
                    'notes' => $ack->notes,
                ];
            })
        ]);
    }
}
