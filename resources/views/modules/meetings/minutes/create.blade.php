@extends('layouts.app')

@section('title', 'Create Meeting Minutes - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .minutes-section {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 30px;
    }
    .agenda-minutes-item {
        background: #f8f9fa;
        border-left: 3px solid #28a745;
    }
    .action-item-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: white;
    }
    .previous-action-item {
        background: #fff3cd;
        border-left: 3px solid #ffc107;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
    }
    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 10px;
    }
    .save-section-btn {
        position: sticky;
        bottom: 20px;
        z-index: 100;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-file me-2"></i>Create Meeting Minutes
                            </h4>
                            <p class="card-text text-white-50 mb-0">{{ $meeting->title }}</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-light me-2">
                                <i class="bx bx-arrow-back me-1"></i>Back to Meeting
                            </a>
                            <button type="button" class="btn btn-light" id="preview-minutes-btn">
                                <i class="bx bx-show me-1"></i>Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-info-circle me-2"></i>Meeting Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <strong><i class="bx bx-calendar me-2"></i>Date:</strong><br>
                            {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, F d, Y') }}
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong><i class="bx bx-time me-2"></i>Time:</strong><br>
                            {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }}
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong><i class="bx bx-map me-2"></i>Venue:</strong><br>
                            {{ $meeting->venue ?? 'TBD' }}
                        </div>
                        <div class="col-md-3 mb-2">
                            <strong><i class="bx bx-category me-2"></i>Category:</strong><br>
                            {{ $meeting->category_name ?? 'N/A' }}
                            @if($meeting->branch_name)
                                <br><small class="text-muted"><i class="bx bx-map"></i> {{ $meeting->branch_name }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="minutesForm">
        @csrf
        <input type="hidden" name="meeting_id" id="minutes_meeting_id" value="{{ $meeting->id }}">

        {{-- Include Template Structure Sections --}}
        @include('modules.meetings.minutes.partials.template-form-sections')

        <!-- Previous Actions Section (Legacy - can be removed if using template format) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has-previous-actions">
                            <label class="form-check-label" for="has-previous-actions">
                                <h5 class="mb-0"><i class="bx bx-history me-2"></i>Reference Actions from Previous Meetings</h5>
                            </label>
                        </div>
                    </div>
                    <div class="card-body" id="previous-actions-container" style="display: none;">
                        <p class="text-muted">Select action items from previous meetings to track their completion in this meeting.</p>
                        <div id="previous-actions-list">
                            @if(isset($previousMeetings) && $previousMeetings && $previousMeetings->count() > 0)
                                @foreach($previousMeetings as $prevMeeting)
                                    <div class="mb-3">
                                        <strong>{{ $prevMeeting->title }}</strong> - {{ \Carbon\Carbon::parse($prevMeeting->meeting_date)->format('M d, Y') }}
                                        <button type="button" class="btn btn-sm btn-outline-primary load-prev-actions" data-meeting-id="{{ $prevMeeting->id }}">
                                            <i class="bx bx-download"></i> Load Actions
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No previous meetings found.</p>
                            @endif
                        </div>
                        <div id="selected-previous-actions">
                            <!-- Selected previous actions will appear here -->
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="previous_actions">
                                <i class="bx bx-save"></i> Save Previous Actions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-user-check me-2"></i>Attendance</h5>
                    </div>
                    <div class="card-body">
                        <div class="attendance-grid" id="attendance-list">
                            @foreach($participants as $participant)
                                <div class="form-check">
                                    <input class="form-check-input attendance-check" type="checkbox" 
                                           name="attendance[]" 
                                           value="{{ $participant->user_id ?? 'external_' . $participant->id }}" 
                                           id="att-{{ $participant->id }}"
                                           {{ $participant->attended ? 'checked' : '' }}>
                                    <label class="form-check-label" for="att-{{ $participant->id }}">
                                        <strong>{{ $participant->user_name ?? $participant->name }}</strong>
                                        @if(!$participant->user_id)
                                            <span class="badge bg-info ms-1">External</span>
                                        @endif
                                        @if($participant->user_email)
                                            <br><small class="text-muted">{{ $participant->user_email }}</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @if($participants->count() == 0)
                            <p class="text-muted">No participants added to this meeting.</p>
                        @endif
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="attendance">
                                <i class="bx bx-save"></i> Save Attendance
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agenda Discussions Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-list-check me-2"></i>Agenda Discussions & Resolutions</h5>
                    </div>
                    <div class="card-body" id="agenda-minutes-list">
                        @if($agendas->count() > 0)
                            @foreach($agendas as $index => $agenda)
                                <div class="agenda-minutes-item mb-4 p-3 border rounded">
                                    <h6 class="text-primary mb-3">
                                        <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                        {{ $agenda->title }}
                                        @if($agenda->presenter_name)
                                            <br><small class="text-muted"><i class="bx bx-user"></i> Presenter: {{ $agenda->presenter_name }}</small>
                                        @endif
                                        @if($agenda->duration)
                                            <small class="text-muted ms-2"><i class="bx bx-time"></i> {{ $agenda->duration }}</small>
                                        @endif
                                    </h6>
                                    @if($agenda->description)
                                        <p class="text-muted mb-3"><em>{{ $agenda->description }}</em></p>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="bx bx-message-dots"></i> Discussion Notes</label>
                                            <textarea name="agenda_discussion[{{ $agenda->id }}]" 
                                                      class="form-control agenda-discussion" 
                                                      rows="4" 
                                                      placeholder="Enter discussion notes for this agenda item...">{{ $agenda->discussion_notes ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="bx bx-check-circle"></i> Resolution/Decision</label>
                                            <textarea name="agenda_resolution[{{ $agenda->id }}]" 
                                                      class="form-control agenda-resolution" 
                                                      rows="4" 
                                                      placeholder="Enter resolution or decision made...">{{ $agenda->resolution ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-success btn-sm save-agenda-minutes" data-agenda-id="{{ $agenda->id }}">
                                            <i class="bx bx-save"></i> Save This Agenda
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No agenda items added to this meeting.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Items Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bx bx-task me-2"></i>Action Items</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="add-action-item-btn">
                            <i class="bx bx-plus"></i> Add Action Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="action-items-list">
                            <!-- Action items will be added here -->
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="action_items">
                                <i class="bx bx-save"></i> Save Action Items
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AOB Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-message-dots me-2"></i>Any Other Business (AOB)</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="aob" id="aob-text" class="form-control" rows="5" placeholder="Enter any other business discussed during the meeting..."></textarea>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="aob">
                                <i class="bx bx-save"></i> Save AOB
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Meeting Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-calendar-event me-2"></i>Next Meeting</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="next_meeting_date" id="next_meeting_date" class="form-control" placeholder="Select date">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Time</label>
                                <input type="time" name="next_meeting_time" id="next_meeting_time" class="form-control" placeholder="Select time">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Venue</label>
                                <input type="text" name="next_meeting_venue" id="next_meeting_venue" class="form-control" placeholder="Enter venue">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="next_meeting">
                                <i class="bx bx-save"></i> Save Next Meeting
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closing Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-time me-2"></i>Meeting Closing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Closing Time</label>
                                <input type="time" name="closing_time" id="closing_time" class="form-control" value="{{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Closing Hymn</label>
                                <input type="text" name="closing_hymn" id="closing_hymn" class="form-control" placeholder="e.g., 'Bwana u sehemu yangu'">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Closing Prayer Leader</label>
                                <input type="text" name="closing_prayer_leader" id="closing_prayer_leader" class="form-control" placeholder="Enter name of prayer leader">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Closing Words/Remarks</label>
                                <textarea name="closing_remarks" id="closing_remarks" class="form-control" rows="3" placeholder="Enter closing remarks/words (e.g., 'NEEMA')"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Motto/Tagline</label>
                                <input type="text" name="organization_motto" id="organization_motto" class="form-control" placeholder="e.g., 'Unity is Our Progress' / 'Umoja Wetu Ndiyo Maendeleo Yetu'">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="closing">
                                <i class="bx bx-save"></i> Save Closing
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Minutes Visibility Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0"><i class="bx bx-show me-2"></i>Minutes Visibility</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Select users who can view the finalized minutes. If no users are selected, minutes will be visible to all meeting participants.</p>
                        <select class="form-select select2-users-minutes" id="minutes-visible-users" name="minutes_visible_users[]" multiple="multiple" style="width: 100%;">
                            @if(isset($allUsers) && $allUsers)
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}@if(isset($u->primaryDepartment) && $u->primaryDepartment) - {{ $u->primaryDepartment->name }}@endif</option>
                                @endforeach
                            @elseif(isset($users) && $users)
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}@if(isset($u->primaryDepartment) && $u->primaryDepartment) - {{ $u->primaryDepartment->name }}@endif</option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted d-block mt-2">Leave empty to make minutes visible to all participants</small>
                    </div>
                </div>
                <div class="card border-info mt-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="bx bx-check-circle me-2"></i>Approval</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Approver <span class="text-danger">*</span></label>
                            <select class="form-select" id="minutes-approver-id" name="approver_id" required>
                                <option value="">Select Approver</option>
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}@if($u->primaryDepartment) - {{ $u->primaryDepartment->name }}@endif</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2">Select the person who will approve these minutes. Minutes will be pending approval after finalization.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="bx bx-file-blank me-2"></i>Meeting Summary</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="summary" id="summary-text" class="form-control" rows="6" placeholder="Enter a brief summary of the meeting..."></textarea>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="summary">
                                <i class="bx bx-save"></i> Save Summary
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-secondary">
                                <i class="bx bx-x me-1"></i>Cancel
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" id="preview-minutes-btn">
                                    <i class="bx bx-show me-1"></i>Preview Minutes
                                </button>
                                <button type="button" class="btn btn-success me-2" id="save-all-minutes-btn">
                                    <i class="bx bx-save me-1"></i>Save All Minutes
                                </button>
                                <button type="button" class="btn btn-primary" id="finalize-minutes-btn">
                                    <i class="bx bx-check-double me-1"></i>Finalize Minutes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Action Item Template -->
<template id="action-item-template">
    <div class="action-item-card" data-index="">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0"><span class="badge bg-primary action-number">1</span> Action Item</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-action-btn">
                <i class="bx bx-trash"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-12 mb-2">
                <label class="form-label">Action Title *</label>
                <input type="text" name="action_title[]" class="form-control" required placeholder="Enter action item title">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Responsible Person</label>
                <select name="action_responsible[]" class="form-select select2-action-responsible">
                    <option value="">Select Person</option>
                    @foreach($allUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}@if($u->primaryDepartment) - {{ $u->primaryDepartment->name }}@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Due Date</label>
                <input type="date" name="action_due_date[]" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Priority</label>
                <select name="action_priority[]" class="form-select">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div class="col-md-12 mb-2">
                <label class="form-label">Notes/Description</label>
                <textarea name="action_notes[]" class="form-control" rows="2" placeholder="Enter action item details..."></textarea>
            </div>
            <div class="col-md-12 mb-2">
                <label class="form-label">Decisions Made</label>
                <textarea name="action_decisions[]" class="form-control" rows="2" placeholder="Enter any decisions related to this action..."></textarea>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const ajaxUrl = '{{ route("modules.meetings.ajax") }}';
const meetingId = {{ $meeting->id }};
let actionItemIndex = 0;

$(document).ready(function() {
    // Initialize Select2
    $('.select2-users-minutes').select2({
        placeholder: 'Select users who can view minutes',
        allowClear: true
    });

    // Toggle previous actions section
    $('#has-previous-actions').on('change', function() {
        $('#previous-actions-container').toggle(this.checked);
    });

    // Load previous actions from a meeting
    $(document).on('click', '.load-prev-actions', function() {
        const prevMeetingId = $(this).data('meeting-id');
        loadPreviousActions(prevMeetingId);
    });

    // Add action item
    $('#add-action-item-btn').on('click', function() {
        addActionItem();
    });

    // Remove action item
    $(document).on('click', '.remove-action-btn', function() {
        $(this).closest('.action-item-card').remove();
        updateActionNumbers();
    });

    // Save minutes section
    $('.save-minutes-section').on('click', function() {
        const section = $(this).data('section');
        saveMinutesSection(section);
    });

    // Save agenda minutes
    $(document).on('click', '.save-agenda-minutes', function() {
        const agendaId = $(this).data('agenda-id');
        saveAgendaMinutes(agendaId);
    });

    // Save all minutes
    $('#save-all-minutes-btn').on('click', function() {
        saveAllMinutes();
    });

    // Finalize minutes
    $('#finalize-minutes-btn').on('click', function() {
        finalizeMinutes();
    });

    // Preview minutes
    $('#preview-minutes-btn').on('click', function() {
        previewMinutes();
    });

    // Add follow-up row
    $('#add-follow-up-btn').on('click', function() {
        addFollowUpRow();
    });

    // Remove follow-up row
    $(document).on('click', '.remove-followup-btn', function() {
        $(this).closest('tr').remove();
        updateFollowUpRefNumbers();
    });
});

// Load previous actions from a meeting
function loadPreviousActions(meetingId) {
    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: {
            _token: csrfToken,
            action: 'get_previous_actions',
            meeting_id: meetingId
        },
        success: function(response) {
            if (response.success && response.actions) {
                response.actions.forEach(action => {
                    addPreviousActionItem(action);
                });
            }
        }
    });
}

// Add previous action item
function addPreviousActionItem(action) {
    const html = `
        <div class="previous-action-item" data-action-id="${action.id}">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="previous_actions[]" value="${action.id}" id="prev-action-${action.id}" checked>
                <label class="form-check-label" for="prev-action-${action.id}">
                    <strong>${escapeHtml(action.title)}</strong>
                    ${action.responsible_name ? `<br><small>Responsible: ${escapeHtml(action.responsible_name)}</small>` : ''}
                    ${action.due_date ? `<br><small>Due: ${action.due_date}</small>` : ''}
                    ${action.status ? `<br><span class="badge bg-${action.status === 'done' ? 'success' : 'warning'}">${action.status}</span>` : ''}
                </label>
            </div>
        </div>
    `;
    $('#selected-previous-actions').append(html);
}

// Add action item
function addActionItem() {
    actionItemIndex++;
    const template = $('#action-item-template').html();
    const html = template.replace('data-index=""', `data-index="${actionItemIndex}"`);
    $('#action-items-list').append(html);
    
    // Initialize Select2 for new select
    $('.select2-action-responsible').select2({
        placeholder: 'Select responsible person',
        allowClear: true
    });
    
    updateActionNumbers();
}

// Update action item numbers
function updateActionNumbers() {
    $('#action-items-list .action-item-card').each(function(index) {
        $(this).find('.action-number').text(index + 1);
    });
}

// Save minutes section
function saveMinutesSection(section) {
    const formData = {
        _token: csrfToken,
        action: 'save_minutes_section',
        meeting_id: meetingId,
        section: section
    };

    switch(section) {
        case 'attendance':
            formData.attendance = $('.attendance-check:checked').map(function() { return $(this).val(); }).get();
            break;
        case 'action_items':
            formData.action_items = collectActionItems();
            break;
        case 'aob':
            formData.aob = $('#aob-text').val();
            break;
        case 'next_meeting':
            formData.next_meeting_date = $('#next_meeting_date').val();
            formData.next_meeting_time = $('#next_meeting_time').val();
            formData.next_meeting_venue = $('#next_meeting_venue').val();
            break;
        case 'closing':
            formData.closing_time = $('#closing_time').val();
            formData.closing_remarks = $('#closing_remarks').val();
            break;
        case 'summary':
            formData.summary = $('#summary-text').val();
            break;
        case 'previous_actions':
            formData.previous_actions = $('input[name="previous_actions[]"]:checked').map(function() { return $(this).val(); }).get();
            break;
    }

    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire('Saved!', 'Section saved successfully', 'success');
            } else {
                Swal.fire('Error', response.message || 'Failed to save section', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to save section. Please try again.', 'error');
        }
    });
}

// Save agenda minutes
function saveAgendaMinutes(agendaId) {
    const discussion = $(`textarea[name="agenda_discussion[${agendaId}]"]`).val();
    const resolution = $(`textarea[name="agenda_resolution[${agendaId}]"]`).val();

    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: {
            _token: csrfToken,
            action: 'save_agenda_minutes',
            meeting_id: meetingId,
            agenda_id: agendaId,
            discussion: discussion,
            resolution: resolution
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Saved!', 'Agenda minutes saved successfully', 'success');
            } else {
                Swal.fire('Error', response.message || 'Failed to save agenda minutes', 'error');
            }
        }
    });
}

// Collect action items
function collectActionItems() {
    const items = [];
    $('#action-items-list .action-item-card').each(function() {
        items.push({
            title: $(this).find('input[name="action_title[]"]').val(),
            responsible_id: $(this).find('select[name="action_responsible[]"]').val(),
            due_date: $(this).find('input[name="action_due_date[]"]').val(),
            priority: $(this).find('select[name="action_priority[]"]').val(),
            notes: $(this).find('textarea[name="action_notes[]"]').val(),
            decisions: $(this).find('textarea[name="action_decisions[]"]').val()
        });
    });
    return items;
}

// Save all minutes
function saveAllMinutes() {
    const formData = {
        _token: csrfToken,
        action: 'save_all_minutes',
        meeting_id: meetingId,
        attendance: $('.attendance-check:checked').map(function() { return $(this).val(); }).get(),
        agenda_discussions: {},
        agenda_resolutions: {},
        action_items: collectActionItems(),
        aob: $('#aob-text').val(),
        next_meeting_date: $('#next_meeting_date').val(),
        next_meeting_time: $('#next_meeting_time').val(),
        next_meeting_venue: $('#next_meeting_venue').val(),
        closing_time: $('#closing_time').val(),
        closing_remarks: $('#closing_remarks').val(),
        summary: $('#summary-text').val(),
        minutes_visible_users: $('#minutes-visible-users').val() || [],
        previous_actions: $('input[name="previous_actions[]"]:checked').map(function() { return $(this).val(); }).get()
    };

    // Collect agenda discussions and resolutions
    $('.agenda-discussion').each(function() {
        const name = $(this).attr('name');
        const agendaId = name.match(/\[(\d+)\]/)[1];
        formData.agenda_discussions[agendaId] = $(this).val();
    });

    $('.agenda-resolution').each(function() {
        const name = $(this).attr('name');
        const agendaId = name.match(/\[(\d+)\]/)[1];
        formData.agenda_resolutions[agendaId] = $(this).val();
    });

    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', 'All minutes saved successfully', 'success').then(() => {
                    window.location.href = '/modules/meetings/' + meetingId;
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to save minutes', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'Failed to save minutes. Please try again.', 'error');
        }
    });
}

// Finalize minutes
function finalizeMinutes() {
    const approverId = $('#minutes-approver-id').val();
    
    if (!approverId) {
        Swal.fire('Error', 'Please select an approver before finalizing', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Finalize Minutes?',
        text: 'This will submit the minutes for approval. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Submit for Approval'
    }).then((result) => {
        if (result.isConfirmed) {
            // First save all minutes
            const savePromise = new Promise((resolve, reject) => {
                const formData = {
                    _token: csrfToken,
                    action: 'save_all_minutes',
                    meeting_id: meetingId,
                    attendance: $('.attendance-check:checked').map(function() { return $(this).val(); }).get(),
                    agenda_discussions: {},
                    agenda_resolutions: {},
                    action_items: collectActionItems(),
                    aob: $('#aob-text').val(),
                    next_meeting_date: $('#next_meeting_date').val(),
                    next_meeting_time: $('#next_meeting_time').val(),
                    next_meeting_venue: $('#next_meeting_venue').val(),
                    closing_time: $('#closing_time').val(),
                    closing_remarks: $('#closing_remarks').val(),
                    summary: $('#summary-text').val(),
                    minutes_visible_users: $('#minutes-visible-users').val() || [],
                    previous_actions: $('input[name="previous_actions[]"]:checked').map(function() { return $(this).val(); }).get(),
                    approver_id: approverId
                };
                
                // Collect agenda discussions and resolutions
                $('.agenda-discussion').each(function() {
                    const name = $(this).attr('name');
                    const agendaId = name.match(/\[(\d+)\]/)[1];
                    formData.agenda_discussions[agendaId] = $(this).val();
                });
                
                $('.agenda-resolution').each(function() {
                    const name = $(this).attr('name');
                    const agendaId = name.match(/\[(\d+)\]/)[1];
                    formData.agenda_resolutions[agendaId] = $(this).val();
                });
                
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            resolve();
                        } else {
                            reject(response.message || 'Failed to save minutes');
                        }
                    },
                    error: function() {
                        reject('Failed to save minutes');
                    }
                });
            });
            
            // Then finalize
            savePromise.then(() => {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'finalize_minutes',
                        meeting_id: meetingId,
                        approver_id: approverId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Submitted!', response.message || 'Minutes have been submitted for approval', 'success').then(() => {
                                window.location.href = '/modules/meetings/' + meetingId;
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to finalize minutes', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to finalize minutes. Please try again.', 'error');
                    }
                });
            }).catch((error) => {
                Swal.fire('Error', error, 'error');
            });
        }
    });
}

// Preview minutes
function previewMinutes() {
    window.open('/modules/meetings/' + meetingId + '/minutes/preview', '_blank');
}

// Add follow-up row
function addFollowUpRow() {
    const template = document.getElementById('follow-up-row-template');
    if (!template) {
        console.error('Follow-up template not found');
        return;
    }
    const clone = template.content.cloneNode(true);
    $('#follow-ups-table').append(clone);
    updateFollowUpRefNumbers();
}

// Update follow-up reference numbers
function updateFollowUpRefNumbers() {
    $('#follow-ups-table .follow-up-row').each(function(index) {
        const refNo = index + 1;
        $(this).find('input[name="followup_ref_no[]"]').val('Ref. ' + refNo);
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}
</script>
@endpush
