<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\AdvertisementAcknowledgment;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of advertisements
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $advertisements = Advertisement::with(['creator', 'updater', 'acknowledgments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('modules.advertisements.index', compact('advertisements'));
    }

    /**
     * Show the form for creating a new advertisement
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.advertisements.create', compact('roles'));
    }

    /**
     * Store a newly created advertisement
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
            
            $advertisement = Advertisement::create([
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
            
            Log::info('Advertisement created', [
                'advertisement_id' => $advertisement->id,
                'title' => $advertisement->title,
                'created_by' => $user->id
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Advertisement created successfully',
                    'advertisement' => $advertisement
                ]);
            }
            
            return redirect()->route('advertisements.index')
                ->with('success', 'Advertisement created successfully');
                
        } catch (\Exception $e) {
            Log::error('Error creating advertisement: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating advertisement: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error creating advertisement')->withInput();
        }
    }

    /**
     * Display the specified advertisement
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $advertisement = Advertisement::with(['creator', 'updater', 'acknowledgments.user'])
            ->findOrFail($id);
        
        return view('modules.advertisements.show', compact('advertisement'));
    }

    /**
     * Show the form for editing the specified advertisement
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $advertisement = Advertisement::findOrFail($id);
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.advertisements.edit', compact('advertisement', 'roles'));
    }

    /**
     * Update the specified advertisement
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
        
        $advertisement = Advertisement::findOrFail($id);
        
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
            $advertisement->update([
                'title' => $request->title,
                'content' => $request->content,
                'priority' => $request->priority,
                'start_date' => $request->start_date,
                'expiry_date' => $request->expiry_date,
                'is_active' => $request->has('is_active') ? $request->is_active : $advertisement->is_active,
                'show_to_all' => $request->has('show_to_all') ? $request->show_to_all : $advertisement->show_to_all,
                'target_roles' => $request->show_to_all ? null : $request->target_roles,
                'require_acknowledgment' => $request->has('require_acknowledgment') ? $request->require_acknowledgment : $advertisement->require_acknowledgment,
                'allow_redisplay' => $request->has('allow_redisplay') ? $request->allow_redisplay : $advertisement->allow_redisplay,
                'updated_by' => $user->id,
            ]);
            
            Log::info('Advertisement updated', [
                'advertisement_id' => $advertisement->id,
                'title' => $advertisement->title,
                'updated_by' => $user->id
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Advertisement updated successfully',
                    'advertisement' => $advertisement
                ]);
            }
            
            return redirect()->route('advertisements.index')
                ->with('success', 'Advertisement updated successfully');
                
        } catch (\Exception $e) {
            Log::error('Error updating advertisement: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating advertisement: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error updating advertisement')->withInput();
        }
    }

    /**
     * Remove the specified advertisement
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
            $advertisement = Advertisement::findOrFail($id);
            $advertisement->delete();
            
            Log::info('Advertisement deleted', [
                'advertisement_id' => $id,
                'deleted_by' => $user->id
            ]);
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Advertisement deleted successfully'
                ]);
            }
            
            return redirect()->route('advertisements.index')
                ->with('success', 'Advertisement deleted successfully');
                
        } catch (\Exception $e) {
            Log::error('Error deleting advertisement: ' . $e->getMessage());
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting advertisement: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error deleting advertisement');
        }
    }

    /**
     * Get unacknowledged advertisements for current user
     */
    public function getUnacknowledged(Request $request)
    {
        $user = Auth::user();
        
        $advertisements = Advertisement::where('is_active', true)
            ->get()
            ->filter(function($ad) use ($user) {
                return $ad->shouldShowToUser($user);
            })
            ->map(function($ad) {
                return [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'content' => $ad->content,
                    'priority' => $ad->priority,
                    'require_acknowledgment' => $ad->require_acknowledgment,
                ];
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'advertisements' => $advertisements
        ]);
    }

    /**
     * Acknowledge an advertisement
     */
    public function acknowledge(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            $advertisement = Advertisement::findOrFail($id);
            
            // Check if already acknowledged
            $existing = AdvertisementAcknowledgment::where('advertisement_id', $id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existing) {
                // Update acknowledgment if allow_redisplay
                if ($advertisement->allow_redisplay) {
                    $existing->update([
                        'acknowledged_at' => now(),
                        'notes' => $request->notes ?? null,
                    ]);
                }
            } else {
                // Create new acknowledgment
                AdvertisementAcknowledgment::create([
                    'advertisement_id' => $id,
                    'user_id' => $user->id,
                    'acknowledged_at' => now(),
                    'notes' => $request->notes ?? null,
                ]);
            }
            
            Log::info('Advertisement acknowledged', [
                'advertisement_id' => $id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Advertisement acknowledged successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error acknowledging advertisement: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error acknowledging advertisement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get acknowledgment statistics for an advertisement
     */
    public function getAcknowledgmentStats($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        
        $advertisement = Advertisement::with('acknowledgments.user')->findOrFail($id);
        
        $totalUsers = 0;
        if ($advertisement->show_to_all) {
            $totalUsers = User::where('is_active', true)->count();
        } else {
            $roleIds = $advertisement->target_roles ?? [];
            $totalUsers = User::whereHas('roles', function($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            })->where('is_active', true)->count();
        }
        
        $acknowledgedCount = $advertisement->acknowledgments->count();
        $pendingCount = $totalUsers - $acknowledgedCount;
        
        return response()->json([
            'success' => true,
            'total_users' => $totalUsers,
            'acknowledged_count' => $acknowledgedCount,
            'pending_count' => $pendingCount,
            'acknowledgments' => $advertisement->acknowledgments->map(function($ack) {
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
