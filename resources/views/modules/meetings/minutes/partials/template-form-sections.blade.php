{{-- Template-based form sections for meeting minutes --}}
{{-- This partial provides the structured form sections following the template format --}}

{{-- 1. OPENING SECTION --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-calendar me-2"></i>AGENDA NO. 1: OPENING OF MEETING
                </h5>
            </div>
            <div class="card-body">
                <input type="hidden" name="opening_ref_no" value="1">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Opening Prayer Leader <span class="text-muted">(e.g., Chairperson/Vice Chairperson)</span></label>
                        <input type="text" name="opening_prayer_leader" class="form-control" 
                               placeholder="Enter name of prayer leader">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hymn Number</label>
                        <input type="text" name="opening_hymn" class="form-control" 
                               placeholder="e.g., 301">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Scripture Reading Reference</label>
                        <input type="text" name="opening_scripture" class="form-control" 
                               placeholder="e.g., Ephesians 5:1-2">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Closing Prayer Leader</label>
                        <input type="text" name="opening_closing_prayer_leader" class="form-control" 
                               placeholder="Enter name of closing prayer leader">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Opening Remarks/Theme</label>
                        <textarea name="opening_remarks" class="form-control" rows="4" 
                                  placeholder="Enter opening remarks or theme of the meeting (e.g., All matters should be done with love...)"></textarea>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="opening">
                        <i class="bx bx-save"></i> Save Opening Section
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. ATTENDANCE SECTION (Template Format) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-user-check me-2"></i>AGENDA NO. 2: ATTENDANCE REVIEW AND AGENDA REVIEW
                </h5>
            </div>
            <div class="card-body">
                <input type="hidden" name="attendance_ref_no" value="2">
                
                {{-- Quorum Verification --}}
                <div class="mb-4">
                    <label class="form-label"><strong>Quorum Verification Statement</strong></label>
                    <textarea name="quorum_statement" class="form-control" rows="2" 
                              placeholder="The Chairperson reviewed attendance of Board Members considering the [ORGANIZATION] Constitution Article [X] which states 'Board meetings will be valid if members in attendance are half of all Board Members'. The quorum was satisfied with the attendance of [NUMBER] Board Members..."></textarea>
                </div>

                {{-- Board Members (Staff) Attendance Table --}}
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">ATTENDEES (Board Members):</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="30%">Name</th>
                                    <th width="35%">Position/Title</th>
                                    <th width="30%">Attendance</th>
                                </tr>
                            </thead>
                            <tbody id="board-members-attendance">
                                @php
                                    $boardMembers = $participants->where('participant_type', 'staff');
                                    $index = 1;
                                @endphp
                                @foreach($boardMembers as $member)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>
                                        <strong>{{ $member->user_name ?? $member->name }}</strong>
                                    </td>
                                    <td>
                                        <input type="text" name="board_member_position[{{ $member->id }}]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $member->role ?? '' }}" 
                                               placeholder="Position/Title">
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input attendance-check" type="checkbox" 
                                                   name="attendance[]" 
                                                   value="{{ $member->user_id ?? 'external_' . $member->id }}" 
                                                   id="att-{{ $member->id }}"
                                                   {{ $member->attendance_status == 'attended' || $member->attendance_status == 'confirmed' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="att-{{ $member->id }}">
                                                Present
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @if($boardMembers->count() == 0)
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No board members added to this meeting.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Invitees (External) Attendance Table --}}
                @php
                    $invitees = $participants->where('participant_type', 'external');
                @endphp
                @if($invitees->count() > 0)
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">INVITEES:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="30%">Name</th>
                                    <th width="35%">Position/Title</th>
                                    <th width="30%">Attendance</th>
                                </tr>
                            </thead>
                            <tbody id="invitees-attendance">
                                @php $index = 1; @endphp
                                @foreach($invitees as $invitee)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>
                                        <strong>{{ $invitee->name }}</strong>
                                    </td>
                                    <td>
                                        <input type="text" name="invitee_position[{{ $invitee->id }}]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $invitee->role ?? '' }}" 
                                               placeholder="Position/Title">
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input attendance-check" type="checkbox" 
                                                   name="attendance[]" 
                                                   value="external_{{ $invitee->id }}" 
                                                   id="att-{{ $invitee->id }}"
                                                   {{ $invitee->attendance_status == 'attended' || $invitee->attendance_status == 'confirmed' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="att-{{ $invitee->id }}">
                                                Present
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <div class="text-end">
                    <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="attendance">
                        <i class="bx bx-save"></i> Save Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. PREVIOUS MEETING MINUTES --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-history me-2"></i>AGENDA NO. 3: READING OF PREVIOUS MEETING MINUTES
                </h5>
            </div>
            <div class="card-body">
                <input type="hidden" name="previous_minutes_ref_no" value="3">
                <div class="mb-3">
                    <label class="form-label">Previous Meeting Date</label>
                    <input type="date" name="previous_meeting_date" class="form-control" 
                           value="{{ $previousMeetings->first()->meeting_date ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmation Statement</label>
                    <textarea name="previous_minutes_confirmation" class="form-control" rows="2" 
                              placeholder="Members confirmed that these accurately reflect what was discussed in those meetings."></textarea>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="previous_minutes">
                        <i class="bx bx-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. FOLLOW-UPS FROM PREVIOUS MEETING (YATOKANAYO) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-list-check me-2"></i>AGENDA NO. 4: FOLLOW-UPS FROM PREVIOUS MEETING
                    </h5>
                    <button type="button" class="btn btn-sm btn-dark" id="add-follow-up-btn">
                        <i class="bx bx-plus"></i> Add Follow-up
                    </button>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" name="followups_ref_no" value="4">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">Ref. No.</th>
                                <th width="35%">DESCRIPTION</th>
                                <th width="35%">BOARD ORDERS/RESOLUTIONS</th>
                                <th width="20%">IMPLEMENTATION STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="follow-ups-table">
                            <!-- Follow-up items will be added here -->
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="followups">
                        <i class="bx bx-save"></i> Save Follow-ups
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template for Follow-up Row --}}
<template id="follow-up-row-template">
    <tr class="follow-up-row">
        <td>
            <input type="text" name="followup_ref_no[]" class="form-control form-control-sm" 
                   placeholder="Ref. 1" value="Ref. " readonly>
        </td>
        <td>
            <textarea name="followup_description[]" class="form-control form-control-sm" 
                      rows="2" placeholder="Description of the matter"></textarea>
        </td>
        <td>
            <textarea name="followup_board_orders[]" class="form-control form-control-sm" 
                      rows="2" placeholder="Board orders/resolutions"></textarea>
        </td>
        <td>
            <select name="followup_status[]" class="form-select form-select-sm">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="deferred">Deferred</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-followup-btn">
                <i class="bx bx-trash"></i> Remove
            </button>
        </td>
    </tr>
</template>





