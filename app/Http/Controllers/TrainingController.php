<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingReport;
use App\Models\TrainingDocument;
use App\Models\TrainingParticipant;
use App\Models\TrainingEvaluation;
use App\Models\PermissionRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TrainingController extends Controller
{
    /**
     * Display a listing of trainings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $canReportTrainings = $user->hasPermission('trainings.report') || 
                             $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        // Get trainings
        $query = Training::with(['creator', 'documents', 'reports', 'participants']);
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // If user doesn't have manage permission, show only trainings they're participating in
        if (!$canManageTrainings) {
            $query->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $trainings = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('modules.trainings.index', compact(
            'trainings',
            'canManageTrainings',
            'canReportTrainings'
        ));
    }

    /**
     * Show the form for creating a new training.
     */
    public function create()
    {
        $user = Auth::user();
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        if (!$canManageTrainings) {
            return redirect()->route('trainings.index')->with('error', 'You do not have permission to create trainings');
        }

        $users = User::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.trainings.create', compact('users'));
    }

    /**
     * Store a newly created training in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        if (!$canManageTrainings) {
            return redirect()->back()->with('error', 'You do not have permission to create trainings');
        }

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'objectives' => 'nullable|string',
            'what_learn' => 'nullable|string',
            'who_teach' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'suggestion_to_saccos' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'requires_certificate' => 'nullable|boolean',
            'send_notifications' => 'nullable|boolean',
            'training_timetable' => 'nullable|array',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Parse training_timetable if it's a JSON string
            $trainingTimetable = null;
            if (!empty($validated['training_timetable'])) {
                if (is_string($validated['training_timetable'])) {
                    $decoded = json_decode($validated['training_timetable'], true);
                    $trainingTimetable = $decoded ?: $validated['training_timetable'];
                } else {
                    $trainingTimetable = $validated['training_timetable'];
                }
            }

            $training = Training::create([
                'topic' => $validated['topic'],
                'category' => $validated['category'] ?? null,
                'content' => $validated['content'] ?? null,
                'objectives' => $validated['objectives'] ?? null,
                'what_learn' => $validated['what_learn'] ?? null,
                'who_teach' => $validated['who_teach'] ?? null,
                'location' => $validated['location'] ?? null,
                'suggestion_to_saccos' => $validated['suggestion_to_saccos'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'max_participants' => $validated['max_participants'] ?? null,
                'cost' => $validated['cost'] ?? null,
                'requires_certificate' => $validated['requires_certificate'] ?? false,
                'send_notifications' => $validated['send_notifications'] ?? true,
                'training_timetable' => $trainingTimetable,
                'status' => 'published',
                'created_by' => Auth::id(),
            ]);

            // Add participants
            if ($request->has('participants') && is_array($request->participants)) {
                $participantUserIds = [];
                foreach ($request->participants as $userId) {
                    TrainingParticipant::create([
                        'training_id' => $training->id,
                        'user_id' => $userId,
                        'status' => 'registered',
                    ]);
                    $participantUserIds[] = $userId;
                }
                
                // Send notifications if enabled
                if ($training->send_notifications && !empty($participantUserIds)) {
                    try {
                        $notificationService = app(NotificationService::class);
                        $message = "You have been assigned to training: {$training->topic}. " .
                                  "Start Date: " . ($training->start_date ? $training->start_date->format('M d, Y') : 'TBA') .
                                  ". Location: {$training->location}";
                        $link = route('trainings.show', $training->id);
                        
                        $notificationService->notify(
                            $participantUserIds,
                            "Training Assignment: {$training->topic}",
                            $message,
                            $link
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to send training notifications: ' . $e->getMessage());
                    }
                }
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $storedName = time() . '_' . uniqid() . '_' . $originalName;
                    $filePath = $file->storeAs('trainings/documents', $storedName, 'public');
                    
                    TrainingDocument::create([
                        'training_id' => $training->id,
                        'original_name' => $originalName,
                        'stored_name' => $storedName,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            
            return redirect()->route('trainings.show', $training->id)
                ->with('success', 'Training created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating training: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create training: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified training.
     */
    public function show($id)
    {
        $training = Training::with(['creator', 'documents.uploader', 'reports.reporter', 'participants.user'])
            ->findOrFail($id);
        
        $user = Auth::user();
        
        // Check if user can view this training
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $canReportTrainings = $user->hasPermission('trainings.report') || 
                             $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        if (!$canManageTrainings && !$isParticipant) {
            abort(403, 'You do not have access to this training.');
        }
        
        $canEdit = $canManageTrainings || ($training->created_by == $user->id);
        $canSubmit = $isParticipant && $training->status == 'published';
        
        // Get user's reports for this training
        $userReports = $training->reports()->where('created_by', $user->id)->get();
        $reportedDates = $userReports->pluck('report_date')->map(function($date) {
            return $date->format('Y-m-d');
        })->toArray();
        
        // Get all dates between start and end date (if available)
        $trainingDates = [];
        if ($training->start_date && $training->end_date) {
            $current = $training->start_date->copy();
            while ($current <= $training->end_date) {
                $trainingDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }
        
        return view('modules.trainings.show', compact(
            'training',
            'canEdit',
            'canManageTrainings',
            'canReportTrainings',
            'canSubmit',
            'isParticipant',
            'userReports',
            'reportedDates',
            'trainingDates'
        ));
    }

    /**
     * Show the form for editing the specified training.
     */
    public function edit($id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $canEdit = $canManageTrainings || ($training->created_by == $user->id);
        
        if (!$canEdit) {
            return redirect()->route('trainings.show', $id)
                ->with('error', 'You do not have permission to edit this training');
        }

        $users = User::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.trainings.edit', compact('training', 'users'));
    }

    /**
     * Update the specified training in storage.
     */
    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $canEdit = $canManageTrainings || ($training->created_by == $user->id);
        
        if (!$canEdit) {
            return redirect()->back()->with('error', 'You do not have permission to edit this training');
        }

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'content' => 'nullable|string',
            'what_learn' => 'nullable|string',
            'who_teach' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'suggestion_to_saccos' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'training_timetable' => 'nullable|array',
            'status' => 'nullable|in:draft,published,ongoing,completed,cancelled',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Parse training_timetable if it's a JSON string
            $trainingTimetable = $training->training_timetable;
            if (isset($validated['training_timetable'])) {
                if (is_string($validated['training_timetable']) && !empty($validated['training_timetable'])) {
                    $decoded = json_decode($validated['training_timetable'], true);
                    $trainingTimetable = $decoded ?: $validated['training_timetable'];
                } else {
                    $trainingTimetable = $validated['training_timetable'];
                }
            }

            $training->update([
                'topic' => $validated['topic'],
                'content' => $validated['content'] ?? null,
                'what_learn' => $validated['what_learn'] ?? null,
                'who_teach' => $validated['who_teach'] ?? null,
                'location' => $validated['location'] ?? null,
                'suggestion_to_saccos' => $validated['suggestion_to_saccos'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'training_timetable' => $trainingTimetable,
                'status' => $validated['status'] ?? $training->status,
                'updated_by' => Auth::id(),
            ]);

            // Update participants if provided
            if ($request->has('participants')) {
                // Remove existing participants
                $training->participants()->delete();
                
                // Add new participants
                if (is_array($request->participants)) {
                    foreach ($request->participants as $userId) {
                        TrainingParticipant::create([
                            'training_id' => $training->id,
                            'user_id' => $userId,
                            'status' => 'registered',
                        ]);
                    }
                }
            }

            // Handle new document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $storedName = time() . '_' . uniqid() . '_' . $originalName;
                    $filePath = $file->storeAs('trainings/documents', $storedName, 'public');
                    
                    TrainingDocument::create([
                        'training_id' => $training->id,
                        'original_name' => $originalName,
                        'stored_name' => $storedName,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            
            return redirect()->route('trainings.show', $training->id)
                ->with('success', 'Training updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating training: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update training: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified training from storage.
     */
    public function destroy($id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        if (!$canManageTrainings) {
            return redirect()->back()->with('error', 'You do not have permission to delete trainings');
        }

        try {
            // Delete associated documents
            foreach ($training->documents as $document) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
            }
            
            $training->delete();
            
            return redirect()->route('trainings.index')
                ->with('success', 'Training deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting training: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete training: ' . $e->getMessage());
        }
    }

    /**
     * Show form to submit training participation
     */
    public function showSubmitForm($id)
    {
        $training = Training::with(['participants'])->findOrFail($id);
        $user = Auth::user();
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        if (!$isParticipant) {
            return redirect()->route('trainings.show', $id)
                ->with('error', 'You are not registered for this training.');
        }
        
        return view('modules.trainings.submit', compact('training'));
    }

    /**
     * Submit training participation form
     */
    public function submit(Request $request, $id)
    {
        $training = Training::with(['participants'])->findOrFail($id);
        $user = Auth::user();
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        if (!$isParticipant) {
            return redirect()->back()->with('error', 'You are not registered for this training.');
        }

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'content' => 'nullable|string',
            'what_learn' => 'nullable|string',
            'who_teach' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'suggestion_to_saccos' => 'nullable|string',
            'training_timetable' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Parse training_timetable if it's a JSON string
            $trainingTimetable = $training->training_timetable;
            if (isset($validated['training_timetable']) && !empty($validated['training_timetable'])) {
                if (is_string($validated['training_timetable'])) {
                    $decoded = json_decode($validated['training_timetable'], true);
                    $trainingTimetable = $decoded ?: $validated['training_timetable'];
                } else {
                    $trainingTimetable = $validated['training_timetable'];
                }
            }

            // Update training with submitted data
            $training->update([
                'topic' => $validated['topic'],
                'content' => $validated['content'] ?? $training->content,
                'what_learn' => $validated['what_learn'] ?? $training->what_learn,
                'who_teach' => $validated['who_teach'] ?? $training->who_teach,
                'location' => $validated['location'] ?? $training->location,
                'suggestion_to_saccos' => $validated['suggestion_to_saccos'] ?? $training->suggestion_to_saccos,
                'training_timetable' => $trainingTimetable,
                'updated_by' => Auth::id(),
            ]);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $storedName = time() . '_' . uniqid() . '_' . $originalName;
                    $filePath = $file->storeAs('trainings/documents', $storedName, 'public');
                    
                    TrainingDocument::create([
                        'training_id' => $training->id,
                        'original_name' => $originalName,
                        'stored_name' => $storedName,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // Update participant status
            $participant = $training->participants->where('user_id', $user->id)->first();
            if ($participant) {
                $participant->update(['status' => 'attending']);
            }

            DB::commit();
            
            return redirect()->route('trainings.show', $training->id)
                ->with('success', 'Training form submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting training form: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit form: ' . $e->getMessage());
        }
    }

    /**
     * Show form for daily report
     */
    public function showReportForm($id, Request $request)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $canReportTrainings = $user->hasPermission('trainings.report') || 
                             $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        // Check if user has approved permission request for this training
        $permissionRequestId = $request->input('permission_request_id');
        $permissionRequest = null;
        $permissionDates = [];
        
        if ($permissionRequestId) {
            $permissionRequest = PermissionRequest::where('id', $permissionRequestId)
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();
            
            if ($permissionRequest && $permissionRequest->isForTraining()) {
                $permissionDates = $permissionRequest->requested_dates;
            }
        } else {
            // Auto-detect permission request for this training
            $permissionRequest = PermissionRequest::where('user_id', $user->id)
                ->where(function($q) use ($training) {
                    $q->where('training_id', $training->id)
                      ->orWhere('is_for_training', true);
                })
                ->where('status', 'approved')
                ->whereDate('start_datetime', '<=', now())
                ->whereDate('end_datetime', '>=', now())
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($permissionRequest) {
                $permissionDates = $permissionRequest->requested_dates;
            }
        }
        
        if (!$canReportTrainings && !$isParticipant && !$permissionRequest) {
            return redirect()->route('trainings.show', $id)
                ->with('error', 'You do not have permission to report on this training.');
        }
        
        // Get existing reports for this training
        $reports = $training->reports()->orderBy('report_date', 'desc')->get();
        
        // Get user's reports
        $userReports = $training->reports()->where('created_by', $user->id)->orderBy('report_date', 'desc')->get();
        $reportedDates = $userReports->pluck('report_date')->map(function($date) {
            return $date->format('Y-m-d');
        })->toArray();
        
        return view('modules.trainings.report', compact(
            'training', 
            'reports', 
            'userReports', 
            'reportedDates',
            'permissionRequest',
            'permissionDates'
        ));
    }

    /**
     * Store daily report
     */
    public function storeReport(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $canReportTrainings = $user->hasPermission('trainings.report') || 
                             $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        if (!$canReportTrainings && !$isParticipant) {
            return redirect()->back()->with('error', 'You do not have permission to report on this training.');
        }

        $validated = $request->validate([
            'report_date' => 'required|date',
            'report_content' => 'required|string',
            'activities_completed' => 'nullable|string',
            'challenges_faced' => 'nullable|string',
            'next_day_plan' => 'nullable|string',
            'permission_request_id' => 'nullable|exists:permission_requests,id',
        ]);

        // Verify permission request if provided
        $permissionRequest = null;
        if (!empty($validated['permission_request_id'])) {
            $permissionRequest = PermissionRequest::where('id', $validated['permission_request_id'])
                ->where('user_id', Auth::id())
                ->where('status', 'approved')
                ->first();
            
            if (!$permissionRequest || !$permissionRequest->isForTraining()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Invalid or unapproved permission request for training.');
            }
            
            // Verify report date is within permission dates
            $permissionDates = $permissionRequest->requested_dates;
            if (!in_array($validated['report_date'], $permissionDates)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Report date must be within your approved permission dates.');
            }
        }
        
        // Check if report already exists for this date by this user
        $existingReport = TrainingReport::where('training_id', $id)
            ->where('report_date', $validated['report_date'])
            ->where('created_by', Auth::id())
            ->first();

        // If report exists, update it instead of creating new one
        if ($existingReport) {
            $existingReport->update([
                'permission_request_id' => $validated['permission_request_id'] ?? null,
                'report_content' => $validated['report_content'],
                'activities_completed' => $validated['activities_completed'] ?? null,
                'challenges_faced' => $validated['challenges_faced'] ?? null,
                'next_day_plan' => $validated['next_day_plan'] ?? null,
            ]);

            return redirect()->route('trainings.show', $id)
                ->with('success', 'Daily report updated successfully.');
        }

        try {
            TrainingReport::create([
                'training_id' => $id,
                'permission_request_id' => $validated['permission_request_id'] ?? null,
                'report_date' => $validated['report_date'],
                'report_content' => $validated['report_content'],
                'activities_completed' => $validated['activities_completed'] ?? null,
                'challenges_faced' => $validated['challenges_faced'] ?? null,
                'next_day_plan' => $validated['next_day_plan'] ?? null,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('trainings.show', $id)
                ->with('success', 'Daily report submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Error storing training report: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument($trainingId, $documentId)
    {
        $training = Training::findOrFail($trainingId);
        $document = TrainingDocument::findOrFail($documentId);
        
        if ($document->training_id != $training->id) {
            abort(404);
        }
        
        $user = Auth::user();
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $canDelete = $canManageTrainings || ($document->uploaded_by == $user->id);
        
        if (!$canDelete) {
            return redirect()->back()->with('error', 'You do not have permission to delete this document.');
        }

        try {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $document->delete();
            
            return redirect()->back()->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    /**
     * Training Analytics Dashboard
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        if (!$canManageTrainings) {
            return redirect()->route('trainings.index')
                ->with('error', 'You do not have permission to view analytics.');
        }

        // Get date range
        $startDate = $request->input('start_date', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Statistics
        $totalTrainings = Training::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedTrainings = Training::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalParticipants = TrainingParticipant::whereHas('training', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();
        $totalReports = TrainingReport::whereHas('training', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Training by status
        $trainingsByStatus = Training::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Training by category
        $trainingsByCategory = Training::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('category')
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();

        // Monthly training trend
        $monthlyTrend = Training::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top rated trainings
        $topRatedTrainings = Training::withCount('evaluations')
            ->withAvg('evaluations', 'overall_rating')
            ->whereHas('evaluations')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('evaluations_avg_overall_rating', 'desc')
            ->limit(10)
            ->get();

        // Participant attendance rate
        $attendanceStats = TrainingParticipant::whereHas('training', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

        return view('modules.trainings.analytics', compact(
            'totalTrainings',
            'completedTrainings',
            'totalParticipants',
            'totalReports',
            'trainingsByStatus',
            'trainingsByCategory',
            'monthlyTrend',
            'topRatedTrainings',
            'attendanceStats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Training Calendar View
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        // Get trainings for calendar
        $query = Training::with(['creator', 'participants']);
        
        if (!$canManageTrainings) {
            $query->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $trainings = $query->whereNotNull('start_date')->get();

        return view('modules.trainings.calendar', compact('trainings', 'canManageTrainings'));
    }

    /**
     * Export trainings to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        if (!$canManageTrainings) {
            abort(403);
        }

        $query = Training::with(['creator', 'participants.user', 'reports', 'documents']);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $trainings = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('modules.trainings.pdf.export', compact('trainings'));
        
        return $pdf->download('trainings-export-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Show evaluation form
     */
    public function showEvaluation($id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        if (!$isParticipant) {
            return redirect()->route('trainings.show', $id)
                ->with('error', 'You must be a participant to evaluate this training.');
        }

        $evaluation = TrainingEvaluation::where('training_id', $id)
            ->where('user_id', $user->id)
            ->first();

        return view('modules.trainings.evaluation', compact('training', 'evaluation'));
    }

    /**
     * Store evaluation
     */
    public function storeEvaluation(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        
        $isParticipant = $training->participants->contains('user_id', $user->id);
        
        if (!$isParticipant) {
            return redirect()->back()->with('error', 'You must be a participant to evaluate this training.');
        }

        $validated = $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'content_rating' => 'nullable|integer|min:1|max:5',
            'instructor_rating' => 'nullable|integer|min:1|max:5',
            'venue_rating' => 'nullable|integer|min:1|max:5',
            'what_you_liked' => 'nullable|string',
            'what_can_be_improved' => 'nullable|string',
            'additional_comments' => 'nullable|string',
            'would_recommend' => 'nullable|boolean',
        ]);

        TrainingEvaluation::updateOrCreate(
            [
                'training_id' => $id,
                'user_id' => $user->id,
            ],
            $validated
        );

        return redirect()->route('trainings.show', $id)
            ->with('success', 'Thank you for your evaluation!');
    }

    /**
     * Send notifications to participants
     */
    public function sendNotifications($id)
    {
        $training = Training::with('participants.user')->findOrFail($id);
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        if (!$canManageTrainings) {
            return redirect()->back()->with('error', 'You do not have permission to send notifications.');
        }

        $notificationService = app(NotificationService::class);
        
        foreach ($training->participants as $participant) {
            if ($participant->user) {
                $message = "You have been assigned to training: {$training->topic}. " .
                          "Start Date: " . ($training->start_date ? $training->start_date->format('M d, Y') : 'TBA') .
                          ". Location: {$training->location}";
                
                $link = route('trainings.show', $training->id);
                
                $notificationService->notify(
                    [$participant->user->id],
                    "Training Assignment: {$training->topic}",
                    $message,
                    $link
                );
            }
        }

        return redirect()->back()->with('success', 'Notifications sent to all participants.');
    }

    /**
     * Advanced search
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $canManageTrainings = $user->hasPermission('trainings.manage') || 
                            $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'General Manager']);
        
        $query = Training::with(['creator', 'participants', 'reports']);
        
        // Search filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('topic', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('who_teach', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        if (!$canManageTrainings) {
            $query->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $trainings = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get unique categories for filter
        $categories = Training::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('modules.trainings.index', compact('trainings', 'canManageTrainings', 'categories'))
            ->with('search', $request->search)
            ->with('filters', $request->only(['category', 'status', 'start_date', 'end_date']));
    }

    /**
     * Show user's training reports
     */
    public function myReports(Request $request)
    {
        $user = Auth::user();
        
        // Get all approved training permissions for this user
        $trainingPermissions = PermissionRequest::where('user_id', $user->id)
            ->where('is_for_training', true)
            ->where('status', 'approved')
            ->whereNotNull('training_id')
            ->with(['training', 'training.reports' => function($q) use ($user) {
                $q->where('created_by', $user->id);
            }])
            ->orderBy('start_datetime', 'desc')
            ->get();
        
        // Get all training reports by this user
        $myReports = TrainingReport::where('created_by', $user->id)
            ->with(['training', 'permissionRequest'])
            ->orderBy('report_date', 'desc')
            ->get();
        
        return view('modules.trainings.my-reports', compact('trainingPermissions', 'myReports'));
    }
}

