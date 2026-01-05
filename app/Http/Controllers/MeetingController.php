<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingCategory;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class MeetingController extends Controller
{
    /**
     * Display a listing of meetings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        $canApproveMeetings = $user->hasPermission('approve_meetings') || 
                             $user->hasAnyRole(['System Admin', 'General Manager', 'HOD', 'HR Officer']);
        
        // Get user's branch
        $userBranchId = $user->branch_id ?? null;
        
        // Get selected branch from request
        $selectedBranchId = $request->input('branch_id', $userBranchId);
        
        // Get all branches for dropdown
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        
        // Check if user can view all branches
        $canViewAll = $user->hasAnyRole(['System Admin', 'General Manager', 'HR Officer']);
        
        return view('modules.meetings.index', compact(
            'branches',
            'selectedBranchId',
            'canManageMeetings',
            'canApproveMeetings',
            'canViewAll'
        ));
    }

    /**
     * Display the specified meeting.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Load meeting with relationships
        $meeting = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->leftJoin('branches', 'meetings.branch_id', '=', 'branches.id')
            ->leftJoin('users as creator', 'meetings.created_by', '=', 'creator.id')
            ->select(
                'meetings.*',
                'meeting_categories.name as category_name',
                'branches.name as branch_name',
                'creator.name as creator_name'
            )
            ->where('meetings.id', $id)
            ->first();

        if (!$meeting) {
            abort(404, 'Meeting not found');
        }

        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        $canApprove = $user->hasPermission('approve_meetings') || 
                     $user->hasAnyRole(['System Admin', 'General Manager', 'HOD', 'HR Officer']);
        
        $canEdit = $canManageMeetings || $meeting->created_by == $user->id;

        // Load participants
        $participants = DB::table('meeting_participants')
            ->leftJoin('users', function($join) {
                $join->on('meeting_participants.user_id', '=', 'users.id')
                     ->where('meeting_participants.participant_type', '=', 'staff');
            })
            ->where('meeting_participants.meeting_id', $id)
            ->select(
                'meeting_participants.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('meeting_participants.participant_type')
            ->orderBy('meeting_participants.name')
            ->get();

        // Load agendas with documents
        $orderColumn = Schema::hasColumn('meeting_agendas', 'sort_order') ? 'sort_order' : 'order_index';
        $agendas = DB::table('meeting_agendas')
            ->leftJoin('users as presenter', 'meeting_agendas.presenter_id', '=', 'presenter.id')
            ->where('meeting_agendas.meeting_id', $id)
            ->select(
                'meeting_agendas.*',
                'presenter.name as presenter_name'
            )
            ->orderBy($orderColumn)
            ->get()
            ->map(function($agenda) {
                // Load documents for each agenda
                $documents = DB::table('meeting_agenda_documents')
                    ->where('meeting_agenda_id', $agenda->id)
                    ->get();
                $agenda->documents = $documents;
                return $agenda;
            });

        // Load meeting minutes
        $minutes = DB::table('meeting_minutes')
            ->leftJoin('users as preparedBy', 'meeting_minutes.prepared_by', '=', 'preparedBy.id')
            ->leftJoin('users as approvedBy', 'meeting_minutes.approved_by', '=', 'approvedBy.id')
            ->where('meeting_minutes.meeting_id', $id)
            ->select(
                'meeting_minutes.*',
                'preparedBy.name as prepared_by_name',
                'approvedBy.name as approved_by_name'
            )
            ->first();

        // Separate participants into staff and external
        $staffParticipants = $participants->where('participant_type', 'staff');
        $externalParticipants = $participants->where('participant_type', 'external');

        // Load approval history
        $approvalHistory = [];
        if (isset($meeting->submitted_at) && $meeting->submitted_at) {
            $submittedBy = $meeting->submitted_by ? DB::table('users')->where('id', $meeting->submitted_by)->first() : null;
            $approvalHistory[] = [
                'action' => 'Submitted',
                'user' => $submittedBy->name ?? 'N/A',
                'date' => $meeting->submitted_at,
                'type' => 'submitted'
            ];
        }
        if (isset($meeting->approved_at) && $meeting->approved_at) {
            $approvedBy = $meeting->approved_by ? DB::table('users')->where('id', $meeting->approved_by)->first() : null;
            $approvalHistory[] = [
                'action' => 'Approved',
                'user' => $approvedBy->name ?? 'N/A',
                'date' => $meeting->approved_at,
                'type' => 'approved'
            ];
        }
        if (Schema::hasColumn('meetings', 'rejected_at') && isset($meeting->rejected_at) && $meeting->rejected_at) {
            $rejectedBy = isset($meeting->rejected_by) && $meeting->rejected_by ? DB::table('users')->where('id', $meeting->rejected_by)->first() : null;
            $approvalHistory[] = [
                'action' => 'Rejected',
                'user' => $rejectedBy->name ?? 'N/A',
                'date' => $meeting->rejected_at,
                'reason' => (isset($meeting->rejection_reason) && $meeting->rejection_reason) ? $meeting->rejection_reason : null,
                'type' => 'rejected'
            ];
        }

        // Calculate statistics
        $stats = [
            'total_participants' => $participants->count(),
            'staff_participants' => $staffParticipants->count(),
            'external_participants' => $externalParticipants->count(),
            'total_agendas' => $agendas->count(),
            'total_documents' => $agendas->sum(function($agenda) {
                return $agenda->documents->count();
            }),
            'confirmed_attendees' => $participants->where('attendance_status', 'confirmed')->count(),
            'invited_count' => $participants->where('attendance_status', 'invited')->count(),
        ];

        // Get updated by user (check if column exists first)
        $updatedBy = null;
        if (Schema::hasColumn('meetings', 'updated_by')) {
            $updatedById = property_exists($meeting, 'updated_by') ? $meeting->updated_by : null;
            if ($updatedById) {
                $updatedBy = DB::table('users')->where('id', $updatedById)->first();
            }
        }

        // Convert to object for view compatibility
        $meeting = (object) $meeting;
        $minutes = $minutes ? (object) $minutes : null;

        return view('modules.meetings.show', compact(
            'meeting', 
            'canEdit', 
            'canApprove', 
            'participants', 
            'staffParticipants',
            'externalParticipants',
            'agendas', 
            'minutes',
            'approvalHistory',
            'stats',
            'updatedBy'
        ));
    }

    /**
     * Show the form for creating a new meeting.
     */
    public function create()
    {
        $user = Auth::user();
        $canManageMeetings = $user->hasPermission('manage_meetings') || $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings) {
            return redirect()->route('modules.meetings.index')->with('error', 'You do not have permission to create meetings');
        }

        $categories = \App\Models\MeetingCategory::where('is_active', true)->orderBy('name')->get();
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        $users = \App\Models\User::with(['primaryDepartment', 'employee'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.meetings.create', compact('categories', 'branches', 'departments', 'users'));
    }

    /**
     * Store a newly created meeting in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $canManageMeetings = $user->hasPermission('manage_meetings') || $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings) {
            return redirect()->back()->with('error', 'You do not have permission to create meetings');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:meeting_categories,id',
            'meeting_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'venue' => 'required|string|max:255',
            'meeting_type' => 'required|in:physical,virtual,hybrid',
            'branch_id' => 'nullable|exists:branches,id',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $branchId = $request->branch_id ?? $user->branch_id ?? null;
            
            // Create meeting
            $meetingId = DB::table('meetings')->insertGetId([
                    'title' => $request->title,
                'category_id' => $request->category_id ?: null,
                'branch_id' => $branchId,
                    'meeting_date' => $request->meeting_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                'venue' => $request->venue ?? $request->location ?? null,
                'meeting_type' => $request->meeting_type ?? 'physical',
                'description' => $request->description ?? null,
                'status' => $request->action === 'submit' ? 'pending_approval' : 'draft',
                'created_by' => Auth::id(),
                'created_at' => now(),
                    'updated_at' => now()
            ]);

            // Handle staff participants
            if ($request->has('staff_participants')) {
                $staffIds = is_array($request->staff_participants) ? $request->staff_participants : [];
                foreach ($staffIds as $staffId) {
                    $staffUser = DB::table('users')->where('id', $staffId)->first();
                    if ($staffUser) {
                        DB::table('meeting_participants')->insert([
                            'meeting_id' => $meetingId,
                            'user_id' => $staffId,
                            'participant_type' => 'staff',
                            'name' => $staffUser->name,
                            'attendance_status' => 'invited',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Handle external participants
            if ($request->has('external_name')) {
                $externalNames = is_array($request->external_name) ? $request->external_name : [];
                $externalEmails = is_array($request->external_email) ? $request->external_email : [];
                $externalPhones = is_array($request->external_phone) ? $request->external_phone : [];
                $externalInstitutions = is_array($request->external_institution) ? $request->external_institution : [];

                foreach ($externalNames as $i => $name) {
                    if ($name) {
                        DB::table('meeting_participants')->insert([
                            'meeting_id' => $meetingId,
                            'participant_type' => 'external',
                            'name' => $name,
                            'email' => $externalEmails[$i] ?? null,
                            'phone' => $externalPhones[$i] ?? null,
                            'institution' => $externalInstitutions[$i] ?? null,
                            'attendance_status' => 'invited',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Handle agenda items
            if ($request->has('agenda_title')) {
                $titles = is_array($request->agenda_title) ? $request->agenda_title : [];
                $durations = is_array($request->agenda_duration) ? $request->agenda_duration : [];
                $presenters = is_array($request->agenda_presenter) ? $request->agenda_presenter : [];
                $descriptions = is_array($request->agenda_description) ? $request->agenda_description : [];

                // Check which columns exist (do this once outside the loop)
                $orderColumn = Schema::hasColumn('meeting_agendas', 'sort_order') ? 'sort_order' : 'order_index';
                $hasDurationMinutes = Schema::hasColumn('meeting_agendas', 'duration_minutes');
                $hasDuration = Schema::hasColumn('meeting_agendas', 'duration');
                
                // Log for debugging (can be removed later)
                Log::debug('Meeting agenda columns check', [
                    'hasDurationMinutes' => $hasDurationMinutes,
                    'hasDuration' => $hasDuration,
                    'orderColumn' => $orderColumn
                ]);

                foreach ($titles as $i => $title) {
                    if ($title) {
                        // Parse duration string (e.g., "15 mins", "30 minutes", "1 hour") to minutes
                        $durationMinutes = null;
                        if (!empty($durations[$i])) {
                            $durationStr = strtolower(trim($durations[$i]));
                            if (preg_match('/(\d+)\s*(?:min|minute|mins|minutes|m)/', $durationStr, $matches)) {
                                $durationMinutes = (int)$matches[1];
                            } elseif (preg_match('/(\d+)\s*(?:hour|hours|hr|hrs|h)/', $durationStr, $matches)) {
                                $durationMinutes = (int)$matches[1] * 60;
                            } elseif (is_numeric($durationStr)) {
                                $durationMinutes = (int)$durationStr;
                            }
                        }
                        
                        // Insert agenda item
                        $agendaData = [
                            'meeting_id' => $meetingId,
                            'title' => $title,
                            'presenter_id' => $presenters[$i] ?: null,
                            'description' => $descriptions[$i] ?? null,
                            $orderColumn => $i + 1,
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        
                        // Add duration based on which column exists - ONLY add if column exists
                        // IMPORTANT: Only add the column that actually exists in the database
                        if ($hasDurationMinutes === true) {
                            // Use duration_minutes column (integer)
                            if ($durationMinutes !== null) {
                                $agendaData['duration_minutes'] = $durationMinutes;
                            }
                        } else {
                            // Use duration column (string) - this is the fallback
                            if (!empty($durations[$i])) {
                                $agendaData['duration'] = $durations[$i];
                            }
                        }
                        
                        // Double-check: Remove duration_minutes if column doesn't exist
                        if (!$hasDurationMinutes && isset($agendaData['duration_minutes'])) {
                            unset($agendaData['duration_minutes']);
                        }
                        
                        $agendaId = DB::table('meeting_agendas')->insertGetId($agendaData);
                        
                        // Handle file uploads for this agenda item
                        if ($request->hasFile("agenda_documents.{$i}")) {
                            $files = $request->file("agenda_documents.{$i}");
                            if (!is_array($files)) {
                                $files = [$files];
                            }
                            
                            // Ensure storage directory exists
                            if (!Storage::exists('public/meeting-agenda-documents')) {
                                Storage::makeDirectory('public/meeting-agenda-documents');
                            }
                            
                            foreach ($files as $file) {
                                if ($file && $file->isValid()) {
                                    // Generate safe filename
                                    $originalName = $file->getClientOriginalName();
                                    $extension = $file->getClientOriginalExtension();
                                    $safeFilename = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                                    $path = $file->storeAs('meeting-agenda-documents', $safeFilename, 'public');
                                    
                                    // Store document record
                                    DB::table('meeting_agenda_documents')->insert([
                                        'meeting_agenda_id' => $agendaId,
                                        'original_name' => $originalName,
                                        'file_path' => $path,
                                        'file_type' => $extension,
                                        'file_size' => $file->getSize(),
                                        'mime_type' => $file->getMimeType(),
                                        'uploaded_by' => Auth::id(),
                                        'created_at' => now(),
            'updated_at' => now()
        ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            if ($request->action === 'submit') {
                return redirect()->route('modules.meetings.show', $meetingId)->with('success', 'Meeting created and submitted for approval successfully.');
            }

            return redirect()->route('modules.meetings.show', $meetingId)->with('success', 'Meeting created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Meeting creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Failed to create meeting: ' . $e->getMessage());
        }
    }

    // Placeholder methods for other routes - to be implemented
    /**
     * Show the form for editing the specified meeting.
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        // Load meeting with relationships
        $meeting = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->leftJoin('branches', 'meetings.branch_id', '=', 'branches.id')
            ->leftJoin('users as creator', 'meetings.created_by', '=', 'creator.id')
            ->select(
                'meetings.*',
                'meeting_categories.name as category_name',
                'branches.name as branch_name',
                'creator.name as creator_name'
            )
            ->where('meetings.id', $id)
            ->first();

        if (!$meeting) {
            abort(404, 'Meeting not found');
        }

        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        $canEdit = $canManageMeetings || $meeting->created_by == $user->id;
        
        if (!$canEdit) {
            abort(403, 'You do not have permission to edit this meeting');
        }

        // Load participants
        $participants = DB::table('meeting_participants')
            ->leftJoin('users', function($join) {
                $join->on('meeting_participants.user_id', '=', 'users.id')
                     ->where('meeting_participants.participant_type', '=', 'staff');
            })
            ->where('meeting_participants.meeting_id', $id)
            ->select(
                'meeting_participants.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('meeting_participants.participant_type')
            ->orderBy('meeting_participants.name')
            ->get();

        // Load agendas
        $orderColumn = Schema::hasColumn('meeting_agendas', 'sort_order') ? 'sort_order' : 'order_index';
        $agendas = DB::table('meeting_agendas')
            ->leftJoin('users as presenter', 'meeting_agendas.presenter_id', '=', 'presenter.id')
            ->where('meeting_agendas.meeting_id', $id)
            ->select(
                'meeting_agendas.*',
                'presenter.name as presenter_name'
            )
            ->orderBy($orderColumn)
            ->get();

        // Get form data (same as create method)
        $categories = \App\Models\MeetingCategory::where('is_active', true)->orderBy('name')->get();
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        $users = \App\Models\User::with(['primaryDepartment', 'employee'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Convert to object for view compatibility
        $meeting = (object) $meeting;

        return view('modules.meetings.edit', compact(
            'meeting',
            'categories',
            'branches',
            'departments',
            'users',
            'participants',
            'agendas'
        ));
    }
    /**
     * Update the specified meeting in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        // Check if meeting exists
        $meeting = DB::table('meetings')->where('id', $id)->first();
        if (!$meeting) {
            abort(404, 'Meeting not found');
        }
        
        // Check permissions
        $canEdit = $canManageMeetings || $meeting->created_by == $user->id;
        if (!$canEdit) {
            return redirect()->back()->with('error', 'You do not have permission to edit this meeting');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:meeting_categories,id',
            'meeting_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'venue' => 'required|string|max:255',
            'meeting_type' => 'required|in:physical,virtual,hybrid',
            'branch_id' => 'nullable|exists:branches,id',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $branchId = $request->branch_id ?? $user->branch_id ?? $meeting->branch_id ?? null;
            
            // Update meeting
            $updateData = [
                'title' => $request->title,
                'category_id' => $request->category_id ?: null,
                'branch_id' => $branchId,
                'meeting_date' => $request->meeting_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'venue' => $request->venue ?? $request->location ?? $meeting->venue ?? null,
                'meeting_type' => $request->meeting_type ?? 'physical',
                'description' => $request->description ?? null,
                'updated_by' => Auth::id(),
                            'updated_at' => now()
            ];
            
            // Only update status if action is submit and meeting is draft
            if ($request->action === 'submit' && $meeting->status === 'draft') {
                $updateData['status'] = 'pending_approval';
            }
            
            DB::table('meetings')->where('id', $id)->update($updateData);

            // Handle staff participants - Delete existing and recreate
            DB::table('meeting_participants')->where('meeting_id', $id)->delete();
            
            if ($request->has('staff_participants')) {
                $staffIds = is_array($request->staff_participants) ? $request->staff_participants : [];
                foreach ($staffIds as $staffId) {
                    $staffUser = DB::table('users')->where('id', $staffId)->first();
                    if ($staffUser) {
                        DB::table('meeting_participants')->insert([
                            'meeting_id' => $id,
                            'user_id' => $staffId,
                            'participant_type' => 'staff',
                            'name' => $staffUser->name,
                            'attendance_status' => 'invited',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
            }

            // Handle external participants
            if ($request->has('external_name')) {
                $externalNames = is_array($request->external_name) ? $request->external_name : [];
                $externalEmails = is_array($request->external_email) ? $request->external_email : [];
                $externalPhones = is_array($request->external_phone) ? $request->external_phone : [];
                $externalInstitutions = is_array($request->external_institution) ? $request->external_institution : [];

                foreach ($externalNames as $i => $name) {
                    if ($name) {
                        DB::table('meeting_participants')->insert([
                            'meeting_id' => $id,
                            'participant_type' => 'external',
                            'name' => $name,
                            'email' => $externalEmails[$i] ?? null,
                            'phone' => $externalPhones[$i] ?? null,
                            'institution' => $externalInstitutions[$i] ?? null,
                            'attendance_status' => 'invited',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Handle agenda items - Delete existing and recreate
            DB::table('meeting_agendas')->where('meeting_id', $id)->delete();
            
            if ($request->has('agenda_title')) {
                $titles = is_array($request->agenda_title) ? $request->agenda_title : [];
                $durations = is_array($request->agenda_duration) ? $request->agenda_duration : [];
                $presenters = is_array($request->agenda_presenter) ? $request->agenda_presenter : [];
                $descriptions = is_array($request->agenda_description) ? $request->agenda_description : [];

                // Check which columns exist (do this once outside the loop)
                $orderColumn = Schema::hasColumn('meeting_agendas', 'sort_order') ? 'sort_order' : 'order_index';
                $hasDurationMinutes = Schema::hasColumn('meeting_agendas', 'duration_minutes');
                $hasDuration = Schema::hasColumn('meeting_agendas', 'duration');

                foreach ($titles as $i => $title) {
                    if ($title) {
                        // Parse duration string (e.g., "15 mins", "30 minutes", "1 hour") to minutes
                        $durationMinutes = null;
                        if (!empty($durations[$i])) {
                            $durationStr = strtolower(trim($durations[$i]));
                            if (preg_match('/(\d+)\s*(?:min|minute|mins|minutes|m)/', $durationStr, $matches)) {
                                $durationMinutes = (int)$matches[1];
                            } elseif (preg_match('/(\d+)\s*(?:hour|hours|hr|hrs|h)/', $durationStr, $matches)) {
                                $durationMinutes = (int)$matches[1] * 60;
                            } elseif (is_numeric($durationStr)) {
                                $durationMinutes = (int)$durationStr;
                            }
                        }
                        
                        // Insert agenda item
                        $agendaData = [
                            'meeting_id' => $id,
                            'title' => $title,
                            'presenter_id' => $presenters[$i] ?: null,
                            'description' => $descriptions[$i] ?? null,
                            $orderColumn => $i + 1,
                            'status' => 'pending',
                            'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                        // Add duration based on which column exists - ONLY add if column exists
                        if ($hasDurationMinutes === true) {
                            // Use duration_minutes column (integer)
                            if ($durationMinutes !== null) {
                                $agendaData['duration_minutes'] = $durationMinutes;
                            }
                        } else {
                            // Use duration column (string) - this is the fallback
                            if (!empty($durations[$i])) {
                                $agendaData['duration'] = $durations[$i];
                            }
                        }
                        
                        // Double-check: Remove duration_minutes if column doesn't exist
                        if (!$hasDurationMinutes && isset($agendaData['duration_minutes'])) {
                            unset($agendaData['duration_minutes']);
                        }
                        
                        $agendaId = DB::table('meeting_agendas')->insertGetId($agendaData);
                        
                        // Handle file uploads for this agenda item
                        if ($request->hasFile("agenda_documents.{$i}")) {
                            $files = $request->file("agenda_documents.{$i}");
                            if (!is_array($files)) {
                                $files = [$files];
                            }
                            
                            // Ensure storage directory exists
                            if (!Storage::exists('public/meeting-agenda-documents')) {
                                Storage::makeDirectory('public/meeting-agenda-documents');
                            }
                            
                            foreach ($files as $file) {
                                if ($file && $file->isValid()) {
                                    // Generate safe filename
                                    $originalName = $file->getClientOriginalName();
                                    $extension = $file->getClientOriginalExtension();
                                    $safeFilename = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                                    $path = $file->storeAs('meeting-agenda-documents', $safeFilename, 'public');
                                    
                                    // Store document record
                                    DB::table('meeting_agenda_documents')->insert([
                                        'meeting_agenda_id' => $agendaId,
                                        'original_name' => $originalName,
                                        'file_path' => $path,
                                        'file_type' => $extension,
                                        'file_size' => $file->getSize(),
                                        'mime_type' => $file->getMimeType(),
                                        'uploaded_by' => Auth::id(),
                                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            }
                            }
                        }
                    }
                }
            }

            DB::commit();

            if ($request->action === 'submit') {
                return redirect()->route('modules.meetings.show', $id)->with('success', 'Meeting updated and submitted for approval successfully.');
            }

            return redirect()->route('modules.meetings.show', $id)->with('success', 'Meeting updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Meeting update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Failed to update meeting: ' . $e->getMessage());
        }
    }
    public function destroy($id) { return redirect()->back(); }
    /**
     * Display pending approval meetings
     */
    public function pendingApproval(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        $canApproveMeetings = $user->hasPermission('approve_meetings') || 
                             $user->hasAnyRole(['System Admin', 'General Manager', 'HOD', 'HR Officer']);
        
        if (!$canApproveMeetings) {
            abort(403, 'You do not have permission to approve meetings');
        }
        
        // Get user's branch
        $userBranchId = $user->branch_id ?? null;
        
        // Get selected branch from request
        $selectedBranchId = $request->input('branch_id', $userBranchId);
        
        // Get all branches for dropdown
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.meetings.pending-approval', compact(
            'branches',
            'selectedBranchId',
            'canApproveMeetings'
        ));
    }
    public function analytics() { return view('modules.meetings.analytics'); }
    public function categories() { return view('modules.meetings.categories'); }
    public function minutes($id) { return view('modules.meetings.minutes'); }
    /**
     * Show the form for creating meeting minutes.
     */
    public function createMinutes($id)
    {
        $user = Auth::user();
        
        // Load meeting with relationships
        $meeting = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->leftJoin('branches', 'meetings.branch_id', '=', 'branches.id')
            ->leftJoin('users as creator', 'meetings.created_by', '=', 'creator.id')
            ->select(
                'meetings.*',
                'meeting_categories.name as category_name',
                'branches.name as branch_name',
                'branches.code as branch_code',
                'creator.name as creator_name'
            )
            ->where('meetings.id', $id)
            ->first();

        if (!$meeting) {
            abort(404, 'Meeting not found');
        }

        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings && $meeting->created_by != $user->id) {
            abort(403, 'You do not have permission to create minutes for this meeting');
        }

        // Load participants
        $participants = DB::table('meeting_participants')
            ->leftJoin('users', function($join) {
                $join->on('meeting_participants.user_id', '=', 'users.id')
                     ->where('meeting_participants.participant_type', '=', 'staff');
            })
            ->where('meeting_participants.meeting_id', $id)
            ->select(
                'meeting_participants.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('meeting_participants.participant_type')
            ->orderBy('meeting_participants.name')
            ->get();

        // Load agendas
        $orderColumn = Schema::hasColumn('meeting_agendas', 'sort_order') ? 'sort_order' : 'order_index';
        $agendas = DB::table('meeting_agendas')
            ->leftJoin('users as presenter', 'meeting_agendas.presenter_id', '=', 'presenter.id')
            ->where('meeting_agendas.meeting_id', $id)
                ->select(
                'meeting_agendas.*',
                'presenter.name as presenter_name'
            )
            ->orderBy($orderColumn)
                ->get();

        // Load existing minutes if any
        $existingMinutes = DB::table('meeting_minutes')
            ->where('meeting_id', $id)
            ->first();

        // Load previous actions from previous meetings (if any)
        $previousActions = collect();

        // Load previous meetings (meetings before this one that have minutes with actions)
        $previousMeetings = DB::table('meetings')
            ->where('meetings.meeting_date', '<', $meeting->meeting_date)
            ->where('meetings.status', '!=', 'cancelled')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('meeting_minutes')
                      ->whereColumn('meeting_minutes.meeting_id', 'meetings.id');
            })
            ->select('meetings.id', 'meetings.title', 'meetings.meeting_date')
            ->orderBy('meetings.meeting_date', 'desc')
            ->limit(10)
            ->get();

        // Get all users for assignment (using User model to get relationships)
        // Note: Don't limit columns when using with() for relationships to work properly
        $users = \App\Models\User::where('is_active', true)
            ->with('primaryDepartment')
            ->orderBy('name')
            ->get();

        // Also pass as allUsers for the minutes visibility dropdown
        $allUsers = $users;

        // Convert to object for view compatibility
        $meeting = (object) $meeting;
        $existingMinutes = $existingMinutes ? (object) $existingMinutes : null;

        return view('modules.meetings.minutes.create', compact(
            'meeting',
            'participants',
            'agendas',
            'existingMinutes',
            'previousActions',
            'previousMeetings',
            'users',
            'allUsers'
        ));
    }
    public function editMinutes($id) { return view('modules.meetings.minutes.edit'); }
    /**
     * Preview meeting minutes.
     */
    public function previewMinutesPage($id)
    {
        $user = Auth::user();
        
        // Load meeting with relationships
        $meeting = DB::table('meetings')
            ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
            ->leftJoin('branches', 'meetings.branch_id', '=', 'branches.id')
            ->leftJoin('users as creator', 'meetings.created_by', '=', 'creator.id')
            ->select(
                'meetings.*',
                'meeting_categories.name as category_name',
                'branches.name as branch_name',
                'branches.code as branch_code',
                'creator.name as creator_name'
            )
            ->where('meetings.id', $id)
            ->first();

        if (!$meeting) {
            abort(404, 'Meeting not found');
        }

        // Load meeting minutes
        $minutes = DB::table('meeting_minutes')
            ->leftJoin('users as preparedBy', 'meeting_minutes.prepared_by', '=', 'preparedBy.id')
            ->leftJoin('users as approvedBy', 'meeting_minutes.approved_by', '=', 'approvedBy.id')
            ->where('meeting_minutes.meeting_id', $id)
            ->select(
                'meeting_minutes.*',
                'preparedBy.name as prepared_by_name',
                'approvedBy.name as approved_by_name'
            )
            ->first();

        if (!$minutes) {
            return redirect()->route('modules.meetings.show', $id)
                ->with('error', 'Minutes have not been created for this meeting yet.');
        }

        // Load participants
        $participants = DB::table('meeting_participants')
            ->leftJoin('users', function($join) {
                $join->on('meeting_participants.user_id', '=', 'users.id')
                     ->where('meeting_participants.participant_type', '=', 'staff');
            })
            ->where('meeting_participants.meeting_id', $id)
            ->select(
                'meeting_participants.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('meeting_participants.participant_type')
            ->orderBy('meeting_participants.name')
            ->get();

        // Load attendees (participants who attended)
        $attendees = DB::table('meeting_participants')
            ->leftJoin('users', function($join) {
                $join->on('meeting_participants.user_id', '=', 'users.id')
                     ->where('meeting_participants.participant_type', '=', 'staff');
            })
            ->where('meeting_participants.meeting_id', $id)
            ->where(function($query) {
                $query->where('meeting_participants.attendance_status', 'attended')
                      ->orWhere('meeting_participants.attendance_status', 'confirmed')
                      ->orWhereNull('meeting_participants.attendance_status'); // Default to attended if not set
            })
            ->select(
                'meeting_participants.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('meeting_participants.participant_type')
            ->orderBy('meeting_participants.name')
            ->get();

        // Load agendas with minutes data
        $orderColumn = Schema::hasColumn('meeting_agendas', 'sort_order') ? 'sort_order' : 'order_index';
        $agendas = DB::table('meeting_agendas')
            ->leftJoin('users as presenter', 'meeting_agendas.presenter_id', '=', 'presenter.id')
            ->leftJoin('meeting_agenda_minutes', 'meeting_agendas.id', '=', 'meeting_agenda_minutes.agenda_id')
            ->where('meeting_agendas.meeting_id', $id)
            ->select(
                'meeting_agendas.*',
                'presenter.name as presenter_name',
                'meeting_agenda_minutes.discussion_notes',
                'meeting_agenda_minutes.resolution',
                'meeting_agenda_minutes.action_items'
            )
            ->orderBy($orderColumn)
            ->get();

        // Load action items from minutes
        $actionItems = collect(); // Initialize empty collection
        if (Schema::hasTable('meeting_action_items')) {
            $actionItems = DB::table('meeting_action_items')
                ->leftJoin('users as assignedTo', 'meeting_action_items.assigned_to', '=', 'assignedTo.id')
                ->where('meeting_action_items.meeting_id', $id)
                ->select(
                    'meeting_action_items.*',
                    'assignedTo.name as responsible_name'
                )
                ->orderBy('meeting_action_items.due_date')
                ->get();
        }

        // Convert to object for view compatibility
        $meeting = (object) $meeting;
        $minutes = (object) $minutes;

        return view('modules.meetings.minutes.preview', compact(
            'meeting',
            'minutes',
            'participants',
            'attendees',
            'agendas',
            'actionItems'
        ));
    }
    public function generateMinutesPdf($id) { return redirect()->back(); }
    public function minutesApproval($id) { return view('modules.meetings.minutes.approval'); }
    /**
     * Handle AJAX requests for meetings
     */
    public function ajax(Request $request)
    {
        $action = $request->input('action');
        $user = Auth::user();
        
        try {
            switch ($action) {
                case 'get_meetings':
                    return $this->getMeetings($request, $user);
                case 'get_dashboard_stats':
                    return $this->getDashboardStats($request, $user);
                case 'get_categories':
                    return $this->getCategories($request, $user);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unknown action'
                    ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Meeting AJAX error: ' . $e->getMessage(), [
                'action' => $action,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get meetings with filters
     */
    private function getMeetings(Request $request, $user)
    {
        try {
            $userBranchId = $user->branch_id ?? null;
            $canViewAll = $user->hasAnyRole(['System Admin', 'General Manager', 'HR Officer']);
            
            // Get filters
            $statusFilter = $request->input('status', '');
            $categoryFilter = $request->input('category_id', '');
            $searchFilter = $request->input('search', '');
            $branchFilter = $request->input('branch_id', '');
            $dateRange = $request->input('date_range', '');
            
            // Build query
            $query = DB::table('meetings')
                ->leftJoin('meeting_categories', 'meetings.category_id', '=', 'meeting_categories.id')
                ->leftJoin('branches', 'meetings.branch_id', '=', 'branches.id')
                ->leftJoin('users as creator', 'meetings.created_by', '=', 'creator.id')
                ->select(
                    'meetings.*',
                    'meeting_categories.name as category_name',
                    'branches.name as branch_name',
                    'branches.code as branch_code',
                    'creator.name as creator_name'
                );
            
            // Apply branch filter
            if ($branchFilter) {
                $query->where('meetings.branch_id', $branchFilter);
            } elseif (!$canViewAll && $userBranchId) {
                $query->where('meetings.branch_id', $userBranchId);
            }
            
            // Apply status filter
            if ($statusFilter) {
                $query->where('meetings.status', $statusFilter);
            }
            
            // Apply category filter
            if ($categoryFilter) {
                $query->where('meetings.category_id', $categoryFilter);
            }
            
            // Apply search filter
            if ($searchFilter) {
                $query->where(function($q) use ($searchFilter) {
                    $q->where('meetings.title', 'like', "%{$searchFilter}%")
                      ->orWhere('meetings.description', 'like', "%{$searchFilter}%");
                    
                    // Check which column exists and use it
                    if (Schema::hasColumn('meetings', 'venue')) {
                        $q->orWhere('meetings.venue', 'like', "%{$searchFilter}%");
                    } elseif (Schema::hasColumn('meetings', 'location')) {
                        $q->orWhere('meetings.location', 'like', "%{$searchFilter}%");
                    }
                });
            }
            
            // Apply date range filter
            if ($dateRange) {
                $dates = explode(' to ', $dateRange);
                if (count($dates) == 2) {
                    $query->whereBetween('meetings.meeting_date', [
                        date('Y-m-d', strtotime(trim($dates[0]))),
                        date('Y-m-d', strtotime(trim($dates[1])))
                    ]);
                } elseif (count($dates) == 1) {
                    $query->whereDate('meetings.meeting_date', date('Y-m-d', strtotime(trim($dates[0]))));
                }
            }
            
            // Get participant counts
            $meetings = $query->orderBy('meetings.meeting_date', 'desc')
                ->orderBy('meetings.start_time', 'desc')
                ->get()
                ->map(function($meeting) {
                    $participantsCount = DB::table('meeting_participants')
                        ->where('meeting_id', $meeting->id)
                        ->count();
                    
                    // Get venue/location - check which column exists
                    $venue = null;
                    if (property_exists($meeting, 'venue') && $meeting->venue) {
                        $venue = $meeting->venue;
                    } elseif (property_exists($meeting, 'location') && $meeting->location) {
                        $venue = $meeting->location;
                    }
                    
                    // Get meeting_type - check which column exists
                    $meetingType = 'physical'; // Default
                    if (property_exists($meeting, 'meeting_type') && $meeting->meeting_type) {
                        $meetingType = $meeting->meeting_type;
                    } elseif (property_exists($meeting, 'meeting_mode') && $meeting->meeting_mode) {
                        // Map old meeting_mode to new meeting_type
                        if ($meeting->meeting_mode == 'in_person') {
                            $meetingType = 'physical';
                        } else {
                            $meetingType = $meeting->meeting_mode;
                        }
                    }
                    
                    return [
                        'id' => $meeting->id,
                        'title' => $meeting->title ?? 'Untitled Meeting',
                        'meeting_date' => $meeting->meeting_date ?? null,
                        'start_time' => $meeting->start_time ?? null,
                        'end_time' => $meeting->end_time ?? null,
                        'venue' => $venue,
                        'meeting_type' => $meetingType,
                        'status' => $meeting->status ?? 'draft',
                        'category_name' => $meeting->category_name ?? null,
                        'branch_name' => $meeting->branch_name ?? null,
                        'branch_code' => $meeting->branch_code ?? null,
                        'creator_name' => $meeting->creator_name ?? 'Unknown',
                        'participants_count' => $participantsCount,
                        'created_at' => $meeting->created_at ?? now()->toDateTimeString(),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'meetings' => $meetings
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading meetings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load meetings: ' . $e->getMessage(),
                'meetings' => []
            ], 500);
        }
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(Request $request, $user)
    {
        $userBranchId = $user->branch_id ?? null;
        $canViewAll = $user->hasAnyRole(['System Admin', 'General Manager', 'HR Officer']);
        $branchFilter = $request->input('branch_id', '');
        
        $query = DB::table('meetings');
        
        // Apply branch filter
        if ($branchFilter) {
            $query->where('branch_id', $branchFilter);
        } elseif (!$canViewAll && $userBranchId) {
            $query->where('branch_id', $userBranchId);
        }
        
        $baseQuery = clone $query;
        
        // Get meetings without approved minutes
        $minutesPendingQuery = DB::table('meetings')
            ->leftJoin('meeting_minutes', function($join) {
                $join->on('meetings.id', '=', 'meeting_minutes.meeting_id')
                     ->where('meeting_minutes.status', '=', 'approved');
            })
            ->where('meetings.status', 'approved')
            ->whereNull('meeting_minutes.id');
        
        if ($branchFilter) {
            $minutesPendingQuery->where('meetings.branch_id', $branchFilter);
        } elseif (!$canViewAll && $userBranchId) {
            $minutesPendingQuery->where('meetings.branch_id', $userBranchId);
        }
        
        $stats = [
            'total_meetings' => $baseQuery->count(),
            'upcoming' => (clone $query)->where('meeting_date', '>=', now()->toDateString())
                ->whereIn('status', ['approved', 'pending_approval'])
                ->count(),
            'pending_approval' => (clone $query)->where('status', 'pending_approval')->count(),
            'minutes_pending' => $minutesPendingQuery->count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get meeting categories
     */
    private function getCategories(Request $request, $user)
    {
        $categories = DB::table('meeting_categories')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
        
        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
    public function agendas($id) { return response()->json([]); }
    public function previousActions($id) { return response()->json([]); }
    public function storeCategory(Request $request) { return redirect()->back(); }
    public function updateCategory(Request $request, $id) { return redirect()->back(); }
    public function submitForApproval($id) { return redirect()->back(); }
    public function approve($id) { return redirect()->back(); }
    public function reject($id) { return redirect()->back(); }
}
