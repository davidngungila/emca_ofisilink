@extends('layouts.app')

@section('title', 'Interview Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<style>
    /* Premium Calendar Styling */
    .fc-event {
        cursor: pointer;
        padding: 4px 8px;
        font-size: 0.85rem;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border-radius: 6px;
        transition: transform 0.2s;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .fc-event-main { color: white; font-weight: 500; }
    .fc-toolbar-title { font-size: 1.5rem !important; font-weight: 700; color: #566a7f; }
    .fc-button { border-radius: 8px !important; text-transform: capitalize; font-weight: 600; }
    .fc-button-primary { background-color: #696cff !important; border-color: #696cff !important; }
    .fc-button-active { background-color: #5f61e6 !important; border-color: #5f61e6 !important; }
    .fc-daygrid-day-number { color: #697a8d; font-weight: 500; }
    .fc-col-header-cell-cushion { color: #566a7f; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
    
    /* List View Styling */
    .interview-list-item {
        transition: all 0.2s;
        border-left: 4px solid transparent;
        cursor: pointer;
    }
    .interview-list-item:hover { background-color: #f8f9fa; transform: translateX(2px); }
    .interview-list-item.status-scheduled { border-left-color: #03c3ec; }
    .interview-list-item.status-completed { border-left-color: #71dd37; }
    .interview-list-item.status-cancelled { border-left-color: #ff3e1d; }

    /* Modal Styling */
    .avatar-xl { width: 80px; height: 80px; font-size: 2rem; }
    .modal-header-profile { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); height: 100px; }
    .offset-avatar { margin-top: -40px; }
</style>
@endpush

@section('content')
<div class="container-fluid flex-grow-1 container-p-y h-100 d-flex flex-column">

    <!-- Header Section -->
    <div class="row mb-3 g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 bg-label-primary rounded">
                                <i class="bx bx-calendar-event fs-3 text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 text-primary fw-bold">Interview Schedules</h5>
                                <small class="text-muted">Manage candidate meetings & assessments</small>
                            </div>
                            
                            <div class="vr h-100 mx-3 d-none d-md-block"></div>
                            
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="viewToggle" id="viewCalendar" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="viewCalendar"><i class="bx bx-calendar me-1"></i> Calendar</label>

                                <input type="radio" class="btn-check" name="viewToggle" id="viewList" autocomplete="off">
                                <label class="btn btn-outline-primary" for="viewList"><i class="bx bx-list-ul me-1"></i> List</label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                             <div class="input-group input-group-merge shadow-sm" style="width: 250px;">
                                <span class="input-group-text border-0 bg-light pl-2"><i class="bx bx-search small"></i></span>
                                <input type="text" class="form-control border-0 bg-light" id="filterInput" placeholder="Filter interviews...">
                            </div>
                            @if($canScheduleInterviews ?? true)
                            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                <i class="bx bx-plus me-1"></i> Schedule
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow-1 card border-0 shadow-sm overflow-hidden position-relative h-100">
        <div class="card-body p-0 h-100 position-relative">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="position-absolute top-50 start-50 translate-middle text-center" style="z-index: 10;">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted fw-semibold">Loading Schedule...</div>
            </div>

            <!-- Calendar View -->
            <div id="calendarView" class="h-100 p-3 fade show">
                <div id="calendar" class="h-100"></div>
            </div>

            <!-- List View -->
            <div id="listView" class="h-100 p-0" style="display: none; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th class="ps-4">Candidate</th>
                            <th>Schedule</th>
                            <th>Type</th>
                            <th>Interviewer</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="interviewListBody"></tbody>
                </table>
                <div id="listEmptyState" class="text-center py-5 d-none">
                    <div class="mb-3">
                         <div class="avatar avatar-xl bg-label-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="bx bx-calendar-x fs-1"></i>
                         </div>
                    </div>
                    <h5 class="text-muted">No interviews found</h5>
                    <p class="text-muted mb-0">Schedule a new interview to get started.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-calendar-plus me-2"></i>Schedule Interview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm">
                <div class="modal-body p-4">
                    @csrf
                    <input type="hidden" name="action" value="schedule_interview">
                    
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Candidate Application <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" name="application_id" id="appSelect" required>
                                <option value="">Loading applications...</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Interview Type <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-task"></i></span>
                                <select class="form-select" name="interview_type" required>
                                    <option value="Written">Written Test</option>
                                    <option value="Oral">Oral Interview</option>
                                    <option value="Practical">Practical Assessment</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date & Time <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-time"></i></span>
                                <input type="datetime-local" class="form-control" name="scheduled_at" required min="{{ date('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                             <label class="form-label fw-semibold">Interviewer</label>
                             <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <select class="form-select" name="interviewer_id">
                                    <option value="">Select Interviewer (Optional)</option>
                                    @foreach($interviewers ?? [] as $int)
                                        <option value="{{ $int->id }}">{{ $int->name }}</option>
                                    @endforeach
                                </select>
                             </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location/Link</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" class="form-control" name="location" placeholder="e.g. Conf Room B Or Zoom Link">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Instructions / Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Enter any specific instructions for the candidate or interviewer..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Schedule Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header-profile position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                
                <div class="d-flex flex-column align-items-center mb-4 offset-avatar">
                    <div class="avatar avatar-xl bg-white border border-4 border-white rounded-circle shadow-sm d-flex align-items-center justify-content-center text-primary fw-bold mb-3" id="detInitials">
                        JD
                    </div>
                    <h4 class="mb-0 fw-bold text-dark" id="detName">John Doe</h4>
                    <span class="badge bg-label-primary mt-2" id="detJob">Senior Developer</span>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light text-center h-100">
                            <i class="bx bx-calendar text-primary mb-1 fs-4"></i>
                            <div class="small text-muted text-uppercase fw-bold">Date</div>
                            <div class="fw-bold text-dark" id="detDate">Jan 12</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light text-center h-100">
                            <i class="bx bx-time text-primary mb-1 fs-4"></i>
                            <div class="small text-muted text-uppercase fw-bold">Time</div>
                            <div class="fw-bold text-dark" id="detTime">10:00 AM</div>
                        </div>
                    </div>
                </div>

                <div class="card bg-label-secondary border-0 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Status</small>
                                <span class="fw-semibold" id="detStatus">Scheduled</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Type</small>
                                <span class="fw-semibold" id="detType">Oral</span>
                            </div>
                            <div class="col-12 pt-2 border-top border-light mt-2">
                                <small class="text-muted d-block">Location</small>
                                <span class="fw-semibold" id="detLoc"><i class="bx bx-map me-1"></i> Room 303</span>
                            </div>
                             <div class="col-12 pt-2 border-top border-light mt-2">
                                <small class="text-muted d-block">Notes</small>
                                <span class="small text-dark" id="detNotes">No notes available.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2" id="detActions">
                    <button class="btn btn-primary btn-lg" onclick="openEvaluation()">
                        <i class="bx bx-edit me-1"></i> Evaluate Candidate
                    </button>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success w-100" onclick="updateStatus('Completed')">
                            <i class="bx bx-check"></i> Complete
                        </button>
                        <button class="btn btn-outline-danger w-100" onclick="updateStatus('Cancelled')">
                            <i class="bx bx-x"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
            <input type="hidden" id="detId">
            <input type="hidden" id="detAppId">
        </div>
    </div>
</div>

<!-- Include Evaluation Modal -->
@include('modules.hr.recruitment.modals.evaluation')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const calendarEl = document.getElementById('calendar');
        let calendar = null;
        let interviews = [];
        const csrfToken = '{{ csrf_token() }}';
        const recruitmentUrl = '{{ route("recruitment.handle") }}';

        // Initialization
        loadData();

        // View Toggles
        document.querySelectorAll('input[name="viewToggle"]').forEach(el => {
            el.addEventListener('change', (e) => {
                const isCalendar = e.target.id === 'viewCalendar';
                $('#listView').toggle(!isCalendar);
                $('#calendarView').toggle(isCalendar);
                if(isCalendar && calendar) calendar.render();
            });
        });

         // Filter Input
        $('#filterInput').on('keyup', function() {
            const val = $(this).val().toLowerCase();
            // Filter List View
            $('#interviewListBody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1)
            });
            // Note: Filtering Calendar events is more complex, usually requires refetching/re-adding events.
            // For simplicity, we filter list view primarily or would need to filter `interviews` array and re-render calendar.
        });

        // Load Data
        function loadData() {
            $('#loadingIndicator').show();
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { _token: csrfToken, action: 'get_interview_schedules' },
                success: function(res) {
                    if(res.success) {
                        interviews = res.interviews || [];
                        initCalendar();
                        renderList();
                    }
                },
                complete: () => $('#loadingIndicator').hide()
            });

            // Load Apps for Form
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { _token: csrfToken, action: 'get_bulk_applications' },
                success: function(res) {
                    if(res.success) {
                        const sel = $('#appSelect');
                        sel.empty().append('<option value="">Select Candidate...</option>');
                        res.applications.forEach(app => {
                            if(app.status !== 'Rejected') {
                                sel.append(`<option value="${app.id}">${app.first_name} ${app.last_name} (${app.job ? app.job.job_title : '?'})</option>`);
                            }
                        });
                    }
                }
            });
        }

        // Calendar Logic
        function initCalendar() {
            if(calendar) calendar.destroy();
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap5',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: interviews.map(i => ({
                    id: i.id,
                    title: `${i.interview_type}: ${i.application ? i.application.first_name : 'Unknown'}`,
                    start: i.scheduled_at,
                    backgroundColor: getStatusColor(i.status),
                    borderColor: getStatusColor(i.status),
                    extendedProps: i,
                    classNames: ['shadow-sm']
                })),
                eventClick: function(info) {
                    showDetails(info.event.extendedProps);
                },
                height: '100%',
                dayMaxEvents: true // allow "more" link when too many events
            });
            calendar.render();
        }

        function getStatusColor(status) {
            const map = { 'Scheduled': '#03c3ec', 'Completed': '#71dd37', 'Cancelled': '#ff3e1d' };
            return map[status] || '#8592a3';
        }

        // List View Logic
        function renderList() {
            const tbody = $('#interviewListBody');
            tbody.empty();
            if(interviews.length === 0) {
                $('#listEmptyState').removeClass('d-none');
            } else {
                $('#listEmptyState').addClass('d-none');
                interviews.forEach(i => {
                    const name = i.application ? `${i.application.first_name} ${i.application.last_name}` : 'Unknown';
                    const date = new Date(i.scheduled_at).toLocaleDateString(undefined, {weekday:'short', month:'short', day:'numeric'});
                    const time = new Date(i.scheduled_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    const badge = getStatusBadge(i.status);
                    
                    tbody.append(`
                        <tr class="interview-list-item status-${i.status.toLowerCase()}" onclick="window.viewDetails(${i.id})">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">${name}</div>
                                <div class="small text-muted">${i.application && i.application.job ? i.application.job.job_title : ''}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">${date}</div>
                                <div class="small text-muted">${time}</div>
                            </td>
                            <td><span class="badge bg-label-secondary">${i.interview_type}</span></td>
                            <td>${i.interviewer ? i.interviewer.name : '<span class="text-muted">-</span>'}</td>
                            <td><span class="badge bg-label-${badge}">${i.status}</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-icon btn-outline-primary"><i class="bx bx-chevron-right"></i></button>
                            </td>
                        </tr>
                    `);
                });
            }
        }

        function getStatusBadge(s) {
            return {'Scheduled':'info', 'Completed':'success', 'Cancelled':'danger'}[s] || 'secondary';
        }

        // Form Submission
        $('#scheduleForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        $('#scheduleModal').modal('hide');
                        Swal.fire({icon: 'success', title: 'Scheduled', text: 'Interview successfully scheduled.', timer: 1500, showConfirmButton: false});
                        loadData();
                        $('#scheduleForm')[0].reset();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });

        // Details & Actions
        window.viewDetails = function(id) {
            const i = interviews.find(x => x.id === id);
            if(i) showDetails(i);
        }

        function showDetails(i) {
            $('#detId').val(i.id);
            const app = i.application || {};
            $('#detAppId').val(app.id);
            
            const name = `${app.first_name || ''} ${app.last_name || ''}`;
            $('#detName').text(name);
            $('#detInitials').text(name.split(' ').map(n=>n?n[0]:'').join('').toUpperCase().substring(0,2) || 'NA');
            $('#detJob').text(app.job ? app.job.job_title : 'Unknown Position');
            
            const d = new Date(i.scheduled_at);
            $('#detDate').text(d.toLocaleDateString(undefined, {weekday: 'short', month:'short', day:'numeric'}));
            $('#detTime').text(d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
            
            $('#detStatus').text(i.status);
            $('#detStatus').attr('class', `fw-semibold text-${getStatusBadge(i.status) === 'info' ? 'primary' : getStatusBadge(i.status)}`);
            
            $('#detType').text(i.interview_type);
            $('#detLoc').html(`<i class='bx bx-map me-1'></i> ${i.location || 'Not specified'}`);
            $('#detNotes').text(i.notes || 'No specific instructions.');

            // Action Visibility
            if(i.status === 'Completed' || i.status === 'Cancelled') {
               // You might want to hide "Complete/Cancel" buttons but show "Evaluate"
               // For this advanced version, we allow Evaluation always.
            }
            $('#detailsModal').modal('show');
        }

        // Use same evaluation logic from modal include
        // Linked via window.openEvaluation defined in included file or add listener here if needed.
        // The included evaluation modal script should handle openEvaluation. 
        // We will duplicate the trigger logic just in case the include doesn't export it globally in time.
        
        window.openEvaluation = function() {
             const appId = $('#detAppId').val();
            const candName = $('#detName').text();

            $('#evalAppId').val(appId);
            $('#evalCandidateName').text(candName);
            
            // Clear or Load existing
            $('#evaluationForm')[0].reset();
            
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { _token: csrfToken, action: 'get_evaluation', application_id: appId },
                success: function(res) {
                    if(res.success && res.evaluation) {
                        $('#scoreWritten').val(res.evaluation.written_score);
                        $('#scorePractical').val(res.evaluation.practical_score);
                        $('#scoreOral').val(res.evaluation.oral_score);
                        $('#evalComments').val(res.evaluation.comments);
                        $('#evalRec').val(res.evaluation.recommendation);
                    }
                    $('#detailsModal').modal('hide');
                    $('#evaluationModal').modal('show');
                }
            });
        }
        
        // Re-attach submit handler for evaluation form just in case
        $('#evaluationForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        $('#evaluationModal').modal('hide');
                        Swal.fire({icon: 'success', title: 'Saved', text: 'Evaluation saved.', timer: 1500, showConfirmButton: false});
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        });

        window.updateStatus = function(status) {
            const id = $('#detId').val();
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { 
                    _token: csrfToken, 
                    action: 'update_interview_status',
                    interview_id: id,
                    status: status
                },
                success: function(res) {
                    if(res.success) {
                        $('#detailsModal').modal('hide');
                        Swal.fire({icon: 'success', title: 'Updated', text: `Interview marked as ${status}.`, timer: 1500, showConfirmButton: false});
                        loadData();
                    }
                }
            });
        }
    });
</script>
@endpush
