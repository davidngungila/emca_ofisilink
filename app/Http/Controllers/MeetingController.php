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
use Barryvdh\DomPDF\Facade\Pdf;

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
        try {
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
            
            $canApprove = $user->hasPermission('approve_meetings') || 
                         $user->hasAnyRole(['System Admin', 'General Manager', 'HOD', 'HR Officer']);
            
            // Safely get created_by
            $createdById = property_exists($meeting, 'created_by') ? $meeting->created_by : null;
            $canEdit = $canManageMeetings || ($createdById && $createdById == $user->id);

            // Load participants
            try {
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
            } catch (\Exception $e) {
                Log::error('Error loading participants: ' . $e->getMessage());
                $participants = collect([]);
            }

            // Load agendas with documents
            try {
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
                        try {
                            $documents = DB::table('meeting_agenda_documents')
                                ->where('meeting_agenda_id', $agenda->id ?? 0)
                                ->get();
                            $agenda->documents = $documents ?: collect([]);
                        } catch (\Exception $e) {
                            $agenda->documents = collect([]);
                        }
                        return $agenda;
                    });
            } catch (\Exception $e) {
                Log::error('Error loading agendas: ' . $e->getMessage());
                $agendas = collect([]);
            }

            // Load meeting minutes
            try {
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
            } catch (\Exception $e) {
                Log::error('Error loading minutes: ' . $e->getMessage());
                $minutes = null;
            }

            // Separate participants into staff and external
            $staffParticipants = $participants ? $participants->where('participant_type', 'staff') : collect([]);
            $externalParticipants = $participants ? $participants->where('participant_type', 'external') : collect([]);

            // Load approval history
            $approvalHistory = [];
            if (property_exists($meeting, 'submitted_at') && $meeting->submitted_at) {
                $submittedById = property_exists($meeting, 'submitted_by') ? $meeting->submitted_by : null;
                $submittedBy = $submittedById ? DB::table('users')->where('id', $submittedById)->first() : null;
                $approvalHistory[] = [
                    'action' => 'Submitted',
                    'user' => $submittedBy->name ?? 'N/A',
                    'date' => $meeting->submitted_at,
                    'type' => 'submitted'
                ];
            }
            if (property_exists($meeting, 'approved_at') && $meeting->approved_at) {
                $approvedById = property_exists($meeting, 'approved_by') ? $meeting->approved_by : null;
                $approvedBy = $approvedById ? DB::table('users')->where('id', $approvedById)->first() : null;
                $approvalHistory[] = [
                    'action' => 'Approved',
                    'user' => $approvedBy->name ?? 'N/A',
                    'date' => $meeting->approved_at,
                    'type' => 'approved'
                ];
            }
            if (Schema::hasColumn('meetings', 'rejected_at') && property_exists($meeting, 'rejected_at') && $meeting->rejected_at) {
                $rejectedById = property_exists($meeting, 'rejected_by') ? $meeting->rejected_by : null;
                $rejectedBy = $rejectedById ? DB::table('users')->where('id', $rejectedById)->first() : null;
                $approvalHistory[] = [
                    'action' => 'Rejected',
                    'user' => $rejectedBy->name ?? 'N/A',
                    'date' => $meeting->rejected_at,
                    'reason' => (property_exists($meeting, 'rejection_reason') && $meeting->rejection_reason) ? $meeting->rejection_reason : null,
                    'type' => 'rejected'
                ];
            }

            // Calculate statistics
            $stats = [
                'total_participants' => $participants ? $participants->count() : 0,
                'staff_participants' => $staffParticipants ? $staffParticipants->count() : 0,
                'external_participants' => $externalParticipants ? $externalParticipants->count() : 0,
                'total_agendas' => $agendas ? $agendas->count() : 0,
                'total_documents' => $agendas ? $agendas->sum(function($agenda) {
                    return (isset($agenda->documents) && is_object($agenda->documents) && method_exists($agenda->documents, 'count')) 
                        ? $agenda->documents->count() : 0;
                }) : 0,
                'confirmed_attendees' => $participants ? $participants->where('attendance_status', 'confirmed')->count() : 0,
                'invited_count' => $participants ? $participants->where('attendance_status', 'invited')->count() : 0,
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
            
            // Ensure agendas is a collection
            if (!$agendas) {
                $agendas = collect([]);
            }

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
        } catch (\Exception $e) {
            Log::error('Error loading meeting show page: ' . $e->getMessage(), [
                'meeting_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('modules.meetings.index')
                ->with('error', 'Failed to load meeting details: ' . $e->getMessage());
        }
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
            
            // Prepare meeting data
            $meetingData = [
                'title' => $request->title,
                'category_id' => $request->category_id ?: null,
                'branch_id' => $branchId,
                'meeting_date' => $request->meeting_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'venue' => $request->venue ?? $request->location ?? null,
                'meeting_type' => $request->meeting_type ?? 'physical',
                'description' => $request->description ?? null,
                'status' => 'pending_approval', // All new meetings are automatically pending for approval
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Add approver_id if column exists and approver is specified
            if ($request->approver_id) {
                if (Schema::hasColumn('meetings', 'approver_id')) {
                    $meetingData['approver_id'] = $request->approver_id;
                }
            }
            
            // Set submitted_by and submitted_at since meeting is automatically submitted for approval
            if (Schema::hasColumn('meetings', 'submitted_by')) {
                $meetingData['submitted_by'] = Auth::id();
            }
            if (Schema::hasColumn('meetings', 'submitted_at')) {
                $meetingData['submitted_at'] = now();
            }
            
            // Create meeting
            $meetingId = DB::table('meetings')->insertGetId($meetingData);

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
                $hasStatus = Schema::hasColumn('meeting_agendas', 'status');
                
                // Log for debugging (can be removed later)
                Log::debug('Meeting agenda columns check', [
                    'hasDurationMinutes' => $hasDurationMinutes,
                    'hasDuration' => $hasDuration,
                    'hasStatus' => $hasStatus,
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
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        
                        // Only add status if column exists
                        if ($hasStatus) {
                            $agendaData['status'] = 'pending';
                        }
                        
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

            return redirect()->route('modules.meetings.show', $meetingId)->with('success', 'Meeting created successfully and is pending for approval.');
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
        try {
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

            // Check permissions - safely get created_by
            $canManageMeetings = $user->hasPermission('manage_meetings') || 
                                $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
            
            $createdById = property_exists($meeting, 'created_by') ? $meeting->created_by : null;
            $canEdit = $canManageMeetings || ($createdById && $createdById == $user->id);
            
            if (!$canEdit) {
                abort(403, 'You do not have permission to edit this meeting');
            }

            // Load participants
            try {
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
            } catch (\Exception $e) {
                Log::error('Error loading participants in edit: ' . $e->getMessage());
                $participants = collect([]);
            }

            // Extract staff participant IDs for form
            $staffParticipantIds = $participants ? $participants->where('participant_type', 'staff')
                ->pluck('user_id')
                ->filter()
                ->toArray() : [];
            
            // Load external participants
            try {
                $externalParticipants = DB::table('meeting_participants')
                    ->where('meeting_participants.meeting_id', $id)
                    ->where('meeting_participants.participant_type', 'external')
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error loading external participants in edit: ' . $e->getMessage());
                $externalParticipants = collect([]);
            }

            // Load agendas
            try {
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
            } catch (\Exception $e) {
                Log::error('Error loading agendas in edit: ' . $e->getMessage());
                $agendas = collect([]);
            }

            // Get form data (same as create method)
            try {
                $categories = \App\Models\MeetingCategory::where('is_active', true)->orderBy('name')->get();
            } catch (\Exception $e) {
                Log::error('Error loading categories in edit: ' . $e->getMessage());
                $categories = collect([]);
            }
            
            try {
                $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
            } catch (\Exception $e) {
                Log::error('Error loading branches in edit: ' . $e->getMessage());
                $branches = collect([]);
            }
            
            try {
                $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
            } catch (\Exception $e) {
                Log::error('Error loading departments in edit: ' . $e->getMessage());
                $departments = collect([]);
            }
            
            try {
                $users = \App\Models\User::with(['primaryDepartment', 'employee'])
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error loading users in edit: ' . $e->getMessage());
                $users = collect([]);
            }

            // Convert to object for view compatibility and add staff_participants array
            $meeting = (object) $meeting;
            $meeting->staff_participants = $staffParticipantIds;
            $meeting->external_participants_data = $externalParticipants;

            return view('modules.meetings.edit', compact(
                'meeting',
                'categories',
                'branches',
                'departments',
                'users',
                'participants',
                'agendas',
                'externalParticipants'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading meeting edit page: ' . $e->getMessage(), [
                'meeting_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('modules.meetings.index')
                ->with('error', 'Failed to load meeting edit page: ' . $e->getMessage());
        }
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
                // Add approver_id if column exists and when submitting for approval
                if ($request->approver_id && Schema::hasColumn('meetings', 'approver_id')) {
                    $updateData['approver_id'] = $request->approver_id;
                }
                if (Schema::hasColumn('meetings', 'submitted_by')) {
                    $updateData['submitted_by'] = Auth::id();
                }
                if (Schema::hasColumn('meetings', 'submitted_at')) {
                    $updateData['submitted_at'] = now();
                }
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
                $hasStatus = Schema::hasColumn('meeting_agendas', 'status');

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
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        
                        // Only add status if column exists
                        if ($hasStatus) {
                            $agendaData['status'] = 'pending';
                        }
                    
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
    /**
     * Remove the specified meeting
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete meetings.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to delete meetings.');
        }
        
        $meeting = DB::table('meetings')->where('id', $id)->first();
        
        if (!$meeting) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meeting not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Meeting not found.');
        }
        
        // Check if meeting can be deleted (only draft or rejected meetings)
        if (!in_array($meeting->status, ['draft', 'rejected'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected meetings can be deleted.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Only draft or rejected meetings can be deleted.');
        }
        
        DB::beginTransaction();
        try {
            // Delete related records
            DB::table('meeting_participants')->where('meeting_id', $id)->delete();
            DB::table('meeting_agendas')->where('meeting_id', $id)->delete();
            DB::table('meeting_minutes')->where('meeting_id', $id)->delete();
            
            // Delete agenda documents if table exists
            if (Schema::hasTable('meeting_agenda_documents')) {
                $agendaIds = DB::table('meeting_agendas')->where('meeting_id', $id)->pluck('id');
                if ($agendaIds->isNotEmpty()) {
                    // Delete files from storage
                    $documents = DB::table('meeting_agenda_documents')
                        ->whereIn('meeting_agenda_id', $agendaIds)
                        ->get();
                    
                    foreach ($documents as $doc) {
                        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                    }
                    
                    DB::table('meeting_agenda_documents')->whereIn('meeting_agenda_id', $agendaIds)->delete();
                }
            }
            
            // Delete the meeting
            DB::table('meetings')->where('id', $id)->delete();
            
            DB::commit();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Meeting deleted successfully.'
                ]);
            }
            
            return redirect()->route('modules.meetings.index')->with('success', 'Meeting deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete meeting: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete meeting: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete meeting: ' . $e->getMessage());
        }
    }
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
    /**
     * Display meeting analytics page.
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        // Get user's branch
        $userBranchId = $user->branch_id ?? null;
        
        // Get selected branch from request
        $selectedBranchId = $request->input('branch_id', $userBranchId);
        
        // Get all branches for dropdown
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        
        // Check if user can view all branches
        $canViewAll = $user->hasAnyRole(['System Admin', 'General Manager', 'HR Officer']);
        
        // Initialize default stats (will be loaded via AJAX)
        $stats = [
            'total' => 0,
            'upcoming' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'pending_approval' => 0,
        ];
        
        return view('modules.meetings.analytics', compact(
            'branches',
            'selectedBranchId',
            'canViewAll',
            'stats'
        ));
    }
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

        // Check if meeting is approved - only allow minutes creation for approved or completed meetings
        if ($meeting->status !== 'approved' && $meeting->status !== 'completed') {
            return redirect()->route('modules.meetings.show', $id)
                ->with('error', 'Minutes can only be created for approved or completed meetings. Current status: ' . ucfirst(str_replace('_', ' ', $meeting->status)));
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
            ->where('meeting_agendas.meeting_id', $id)
            ->select(
                'meeting_agendas.*',
                'presenter.name as presenter_name'
            )
            ->orderBy($orderColumn)
            ->get();

        // Load action items from minutes
        $actionItems = collect(); // Initialize empty collection
        if (Schema::hasTable('meeting_action_items')) {
            $actionItems = DB::table('meeting_action_items')
                ->leftJoin('users as assignedTo', 'meeting_action_items.responsible_id', '=', 'assignedTo.id')
                ->where('meeting_action_items.meeting_id', $id)
                ->select(
                    'meeting_action_items.*',
                    'assignedTo.name as responsible_name'
                )
                ->orderBy('meeting_action_items.deadline')
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

    /**
     * Generate PDF for meeting minutes.
     */
    public function generateMinutesPdf($id)
    {
        try {
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
                ->where('meeting_agendas.meeting_id', $id)
                ->select(
                    'meeting_agendas.*',
                    'presenter.name as presenter_name'
                )
                ->orderBy($orderColumn)
                ->get();

            // Load action items from minutes
            $actionItems = collect(); // Initialize empty collection
            if (Schema::hasTable('meeting_action_items')) {
                $actionItems = DB::table('meeting_action_items')
                    ->leftJoin('users as assignedTo', 'meeting_action_items.responsible_id', '=', 'assignedTo.id')
                    ->where('meeting_action_items.meeting_id', $id)
                    ->select(
                        'meeting_action_items.*',
                        'assignedTo.name as responsible_name'
                    )
                    ->orderBy('meeting_action_items.deadline')
                    ->get();
            }

            // Get prepared by and approved by users
            $preparedByUser = null;
            $approvedByUser = null;
            if (isset($minutes->prepared_by) && $minutes->prepared_by) {
                $preparedByUser = DB::table('users')->where('id', $minutes->prepared_by)->first();
            }
            if (isset($minutes->approved_by) && $minutes->approved_by) {
                $approvedByUser = DB::table('users')->where('id', $minutes->approved_by)->first();
            }

            // Convert to object for view compatibility
            $meeting = (object) $meeting;
            $minutes = (object) $minutes;

            // Prepare data for PDF
            $data = compact(
                'meeting',
                'minutes',
                'participants',
                'attendees',
                'agendas',
                'actionItems',
                'preparedByUser',
                'approvedByUser'
            );

            // Generate PDF
            $pdf = Pdf::loadView('modules.meetings.minutes.pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            $filename = 'Meeting_Minutes_' . (isset($meeting->reference_code) && $meeting->reference_code ? $meeting->reference_code : $meeting->id) . '_' . \Carbon\Carbon::parse($meeting->meeting_date)->format('Ymd') . '.pdf';

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            Log::error('Meeting Minutes PDF generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'meeting_id' => $id
            ]);

            return redirect()->route('modules.meetings.show', $id)
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

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
                case 'create_category':
                    return $this->storeCategory($request);
                case 'update_category':
                    return $this->updateCategory($request, $request->input('category_id'));
                case 'delete_meeting':
                    return $this->destroy($request->input('meeting_id'));
                case 'save_minutes_section':
                    return $this->saveMinutesSection($request, $user);
                case 'save_agenda_minutes':
                    return $this->saveAgendaMinutes($request, $user);
                case 'save_all_minutes':
                    return $this->saveAllMinutes($request, $user);
                case 'finalize_minutes':
                    return $this->finalizeMinutes($request, $user);
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
    
    /**
     * Store a newly created meeting category
     */
    public function storeCategory(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create categories.'
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        
        try {
            $category = MeetingCategory::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create meeting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update an existing meeting category
     */
    public function updateCategory(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update categories.'
            ], 403);
        }
        
        $category = MeetingCategory::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        
        try {
            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : $category->is_active,
                'updated_by' => $user->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update meeting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Submit meeting for approval
     */
    public function submitForApproval(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check permissions
        $canManageMeetings = $user->hasPermission('manage_meetings') || 
                            $user->hasAnyRole(['System Admin', 'admin', 'super_admin', 'hod', 'ceo', 'General Manager', 'HR Officer']);
        
        if (!$canManageMeetings) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to submit meetings for approval.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to submit meetings for approval.');
        }
        
        $meeting = DB::table('meetings')->where('id', $id)->first();
        
        if (!$meeting) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meeting not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Meeting not found.');
        }
        
        // Check if meeting can be submitted (not already approved or pending)
        if ($meeting->status === 'pending_approval') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meeting is already pending for approval.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Meeting is already pending for approval.');
        }
        
        if ($meeting->status === 'approved') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meeting is already approved.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Meeting is already approved.');
        }
        
        try {
            $updateData = [
                'status' => 'pending_approval',
                'updated_at' => now()
            ];
            
            if (Schema::hasColumn('meetings', 'submitted_by')) {
                $updateData['submitted_by'] = $user->id;
            }
            if (Schema::hasColumn('meetings', 'submitted_at')) {
                $updateData['submitted_at'] = now();
            }
            if (Schema::hasColumn('meetings', 'updated_by')) {
                $updateData['updated_by'] = $user->id;
            }
            
            DB::table('meetings')->where('id', $id)->update($updateData);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Meeting submitted for approval successfully.'
                ]);
            }
            
            return redirect()->back()->with('success', 'Meeting submitted for approval successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to submit meeting for approval: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit meeting: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to submit meeting: ' . $e->getMessage());
        }
    }
    public function approve($id) { return redirect()->back(); }
    public function reject($id) { return redirect()->back(); }

    /**
     * Save a minutes section
     */
    private function saveMinutesSection(Request $request, $user)
    {
        try {
            $meetingId = $request->input('meeting_id');
            $section = $request->input('section');

            if (!$meetingId) {
                return response()->json(['success' => false, 'message' => 'Meeting ID is required'], 400);
            }

            $meeting = DB::table('meetings')->where('id', $meetingId)->first();
            if (!$meeting) {
                return response()->json(['success' => false, 'message' => 'Meeting not found'], 404);
            }

            $minutes = DB::table('meeting_minutes')->where('meeting_id', $meetingId)->first();
            $minutesData = ['meeting_id' => $meetingId, 'prepared_by' => $user->id, 'updated_at' => now()];

            switch ($section) {
                case 'attendance':
                    $attendance = $request->input('attendance', []);
                    foreach ($attendance as $participantId) {
                        if (strpos($participantId, 'external_') === 0) {
                            $extId = str_replace('external_', '', $participantId);
                            DB::table('meeting_participants')->where('id', $extId)->update(['attendance_status' => 'attended', 'updated_at' => now()]);
                        } else {
                            DB::table('meeting_participants')->where('user_id', $participantId)->where('meeting_id', $meetingId)->update(['attendance_status' => 'attended', 'updated_at' => now()]);
                        }
                    }
                    break;
                case 'aob':
                    if (Schema::hasColumn('meeting_minutes', 'aob')) $minutesData['aob'] = $request->input('aob');
                    break;
                case 'next_meeting':
                    if (Schema::hasColumn('meeting_minutes', 'next_meeting_date')) $minutesData['next_meeting_date'] = $request->input('next_meeting_date');
                    if (Schema::hasColumn('meeting_minutes', 'next_meeting_time')) $minutesData['next_meeting_time'] = $request->input('next_meeting_time');
                    if (Schema::hasColumn('meeting_minutes', 'next_meeting_venue')) $minutesData['next_meeting_venue'] = $request->input('next_meeting_venue');
                    break;
                case 'closing':
                    if (Schema::hasColumn('meeting_minutes', 'closing_time')) $minutesData['closing_time'] = $request->input('closing_time');
                    if (Schema::hasColumn('meeting_minutes', 'closing_remarks')) $minutesData['closing_remarks'] = $request->input('closing_remarks');
                    break;
                case 'summary':
                    if (Schema::hasColumn('meeting_minutes', 'summary')) $minutesData['summary'] = $request->input('summary');
                    break;
            }

            if ($minutes) {
                DB::table('meeting_minutes')->where('meeting_id', $meetingId)->update($minutesData);
            } else {
                $minutesData['created_at'] = now();
                if (!isset($minutesData['status'])) $minutesData['status'] = Schema::hasColumn('meeting_minutes', 'status') ? 'draft' : null;
                DB::table('meeting_minutes')->insert($minutesData);
            }

            return response()->json(['success' => true, 'message' => 'Section saved successfully']);
        } catch (\Exception $e) {
            Log::error('Save minutes section error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to save section: ' . $e->getMessage()], 500);
        }
    }

    private function saveAgendaMinutes(Request $request, $user)
    {
        try {
            $meetingId = $request->input('meeting_id');
            $agendaId = $request->input('agenda_id');
            $discussion = $request->input('discussion');
            $resolution = $request->input('resolution');

            if (!$meetingId || !$agendaId) {
                return response()->json(['success' => false, 'message' => 'Meeting ID and Agenda ID are required'], 400);
            }

            $updateData = [];
            if (Schema::hasColumn('meeting_agendas', 'discussion_notes')) $updateData['discussion_notes'] = $discussion;
            if (Schema::hasColumn('meeting_agendas', 'resolution')) $updateData['resolution'] = $resolution;

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                DB::table('meeting_agendas')->where('id', $agendaId)->where('meeting_id', $meetingId)->update($updateData);
            }

            return response()->json(['success' => true, 'message' => 'Agenda minutes saved successfully']);
        } catch (\Exception $e) {
            Log::error('Save agenda minutes error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to save agenda minutes: ' . $e->getMessage()], 500);
        }
    }

    private function saveAllMinutes(Request $request, $user)
    {
        try {
            $meetingId = $request->input('meeting_id');
            if (!$meetingId) {
                return response()->json(['success' => false, 'message' => 'Meeting ID is required'], 400);
            }

            DB::beginTransaction();

            $minutes = DB::table('meeting_minutes')->where('meeting_id', $meetingId)->first();
            $minutesData = ['meeting_id' => $meetingId, 'prepared_by' => $user->id, 'updated_at' => now()];

            $attendance = $request->input('attendance', []);
            foreach ($attendance as $participantId) {
                if (strpos($participantId, 'external_') === 0) {
                    $extId = str_replace('external_', '', $participantId);
                    DB::table('meeting_participants')->where('id', $extId)->update(['attendance_status' => 'attended', 'updated_at' => now()]);
                } else {
                    DB::table('meeting_participants')->where('user_id', $participantId)->where('meeting_id', $meetingId)->update(['attendance_status' => 'attended', 'updated_at' => now()]);
                }
            }

            $agendaDiscussions = $request->input('agenda_discussions', []);
            $agendaResolutions = $request->input('agenda_resolutions', []);
            foreach ($agendaDiscussions as $agendaId => $discussion) {
                $updateData = [];
                if (Schema::hasColumn('meeting_agendas', 'discussion_notes')) $updateData['discussion_notes'] = $discussion;
                if (Schema::hasColumn('meeting_agendas', 'resolution') && isset($agendaResolutions[$agendaId])) $updateData['resolution'] = $agendaResolutions[$agendaId];
                if (!empty($updateData)) {
                    $updateData['updated_at'] = now();
                    DB::table('meeting_agendas')->where('id', $agendaId)->where('meeting_id', $meetingId)->update($updateData);
                }
            }

            if ($request->has('aob') && Schema::hasColumn('meeting_minutes', 'aob')) $minutesData['aob'] = $request->input('aob');
            if ($request->has('summary') && Schema::hasColumn('meeting_minutes', 'summary')) $minutesData['summary'] = $request->input('summary');
            if ($request->has('next_meeting_date') && Schema::hasColumn('meeting_minutes', 'next_meeting_date')) $minutesData['next_meeting_date'] = $request->input('next_meeting_date');
            if ($request->has('next_meeting_time') && Schema::hasColumn('meeting_minutes', 'next_meeting_time')) $minutesData['next_meeting_time'] = $request->input('next_meeting_time');
            if ($request->has('next_meeting_venue') && Schema::hasColumn('meeting_minutes', 'next_meeting_venue')) $minutesData['next_meeting_venue'] = $request->input('next_meeting_venue');
            if ($request->has('closing_time') && Schema::hasColumn('meeting_minutes', 'closing_time')) $minutesData['closing_time'] = $request->input('closing_time');
            if ($request->has('closing_remarks') && Schema::hasColumn('meeting_minutes', 'closing_remarks')) $minutesData['closing_remarks'] = $request->input('closing_remarks');

            if ($minutes) {
                DB::table('meeting_minutes')->where('meeting_id', $meetingId)->update($minutesData);
            } else {
                $minutesData['created_at'] = now();
                if (!isset($minutesData['status'])) $minutesData['status'] = Schema::hasColumn('meeting_minutes', 'status') ? 'draft' : null;
                DB::table('meeting_minutes')->insert($minutesData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'All minutes saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save all minutes error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to save minutes: ' . $e->getMessage()], 500);
        }
    }

    private function finalizeMinutes(Request $request, $user)
    {
        try {
            $meetingId = $request->input('meeting_id');
            $approverId = $request->input('approver_id');

            if (!$meetingId || !$approverId) {
                return response()->json(['success' => false, 'message' => 'Meeting ID and Approver ID are required'], 400);
            }

            $saveRequest = new Request($request->all());
            $saveRequest->merge(['action' => 'save_all_minutes']);
            $saveResult = $this->saveAllMinutes($saveRequest, $user);
            if (!$saveResult->getData()->success) return $saveResult;

            $updateData = ['updated_at' => now()];
            if (Schema::hasColumn('meeting_minutes', 'status')) $updateData['status'] = 'pending_approval';
            if (Schema::hasColumn('meeting_minutes', 'approver_id')) $updateData['approver_id'] = $approverId;

            DB::table('meeting_minutes')->where('meeting_id', $meetingId)->update($updateData);

            return response()->json(['success' => true, 'message' => 'Minutes finalized and submitted for approval']);
        } catch (\Exception $e) {
            Log::error('Finalize minutes error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to finalize minutes: ' . $e->getMessage()], 500);
        }
    }
}
