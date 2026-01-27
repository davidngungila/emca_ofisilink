@extends('layouts.app')

@section('title', 'Interview Scheduling - Recruitment')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Professional Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-warning" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calendar me-2"></i>Interview Scheduling & Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Schedule, manage, and track interviews with comprehensive calendar view and notifications
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @if($canScheduleInterviews)
                            <button class="btn btn-light btn-lg shadow-sm" id="schedule-interview-btn">
                                <i class="bx bx-plus-circle me-2"></i>Schedule Interview
                            </button>
                            @endif
                            <a href="{{ route('jobs.list') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-briefcase me-2"></i>Job Vacancies
                            </a>
                            <a href="{{ route('jobs.applications') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-user-check me-2"></i>Applications
                            </a>
                            <a href="{{ route('jobs.analytics') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-bar-chart me-2"></i>Analytics
                            </a>
                            <a href="{{ route('jobs') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning" style="border-left: 4px solid var(--bs-warning) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-calendar fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Upcoming Interviews</h6>
                            <h3 class="mb-0 fw-bold text-warning" id="upcomingCount">{{ $advancedStats['upcoming_interviews'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bx bx-time me-1"></i>Scheduled
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bx bx-check-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Completed</h6>
                            <h3 class="mb-0 fw-bold text-success" id="completedCount">0</h3>
                            <small class="text-success">
                                <i class="bx bx-check-double me-1"></i>Finished
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class="bx bx-calendar-check fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">This Week</h6>
                            <h3 class="mb-0 fw-bold text-primary" id="thisWeekCount">0</h3>
                            <small class="text-primary">
                                <i class="bx bx-time-five me-1"></i>Scheduled
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                            <i class="bx bx-x-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Cancelled</h6>
                            <h3 class="mb-0 fw-bold text-danger" id="cancelledCount">0</h3>
                            <small class="text-danger">
                                <i class="bx bx-x me-1"></i>Cancelled
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by applicant name...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="rescheduled">Rescheduled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" id="dateFrom" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" id="dateTo" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" id="applyFilters">
                                <i class="bx bx-filter me-2"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interviews Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bx bx-calendar me-2"></i>Interview Schedules
                    </h5>
                </div>
                <div class="card-body">
                    <div id="interviewsContainer">
                        <div class="row g-4" id="interviewsGrid">
                            <!-- Interviews will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-calendar-plus"></i> Schedule Interview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleInterviewForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="schedule_interview">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Application *</label>
                        <select name="application_id" id="interviewApplicationId" class="form-select" required>
                            <option value="">Select Application</option>
                            <!-- Applications will be populated -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Interview Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduledAt" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Interview Type *</label>
                        <select name="interview_type" id="interviewType" class="form-select" required>
                            <option value="Written">Written</option>
                            <option value="Oral">Oral</option>
                            <option value="Practical">Practical</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Interviewer</label>
                        <select name="interviewer_id" id="interviewerId" class="form-select">
                            <option value="">Select Interviewer (Optional)</option>
                            @foreach($interviewers as $interviewer)
                                <option value="{{ $interviewer->id }}">{{ $interviewer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location/Venue</label>
                        <input type="text" name="location" id="interviewLocation" class="form-control" placeholder="e.g., Conference Room A, Online (Zoom)">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Interviewers</label>
                        <input type="text" name="interviewers" id="interviewers" class="form-control" placeholder="Comma-separated list of interviewer names">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes/Instructions</label>
                        <textarea name="notes" id="interviewNotes" class="form-control" rows="3" placeholder="Additional notes or instructions for the candidate..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Schedule Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interview Details Modal -->
<div class="modal fade" id="interviewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-info-circle"></i> Interview Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="interviewDetailsContent">
                <!-- Interview details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .interview-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: 100%;
        border-left: 4px solid;
    }
    .interview-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .interview-card.status-scheduled { border-left-color: #17a2b8; }
    .interview-card.status-completed { border-left-color: #28a745; }
    .interview-card.status-cancelled { border-left-color: #dc3545; }
    .interview-card.status-rescheduled { border-left-color: #ffc107; }
    .interview-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }
    .interview-card:hover .interview-actions {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const recruitmentUrl = '{{ route("recruitment.handle") }}';
    let allInterviews = [];
    let allApplications = [];
    
    // Initialize
    loadApplications();
    loadInterviews();
    
    // Schedule Interview Button
    $('#schedule-interview-btn').on('click', function() {
        resetScheduleForm();
        $('#scheduleInterviewModal').modal('show');
    });
    
    // Load Applications for Dropdown
    function loadApplications() {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_bulk_applications'
            },
            success: function(response) {
                if (response.success) {
                    allApplications = response.applications || [];
                    const select = $('#interviewApplicationId');
                    select.empty();
                    select.append('<option value="">Select Application</option>');
                    allApplications.forEach(app => {
                        if (app.status !== 'Rejected' && app.status !== 'Hired') {
                            select.append(`<option value="${app.id}">${escapeHtml(app.first_name)} ${escapeHtml(app.last_name)} - ${app.job ? escapeHtml(app.job.job_title) : 'N/A'}</option>`);
                        }
                    });
                }
            }
        });
    }
    
    // Load Interviews
    function loadInterviews() {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_interview_schedules'
            },
            success: function(response) {
                if (response.success) {
                    allInterviews = response.interviews || response.schedules || [];
                    renderInterviews(allInterviews);
                    updateStats(allInterviews);
                }
            }
        });
    }
    
    // Render Interviews
    function renderInterviews(interviews) {
        const grid = $('#interviewsGrid');
        grid.empty();
        
        if (interviews.length === 0) {
            grid.html(`
                <div class="col-12 text-center py-5">
                    <i class="bx bx-calendar" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No Interviews Scheduled</h5>
                    <p class="text-muted">Schedule interviews to manage the hiring process</p>
                </div>
            `);
            return;
        }
        
        interviews.forEach(interview => {
            const statusClass = (interview.status || 'scheduled').toLowerCase();
            let scheduledAt;
            if (interview.scheduled_at) {
                scheduledAt = new Date(interview.scheduled_at);
            } else if (interview.interview_date && interview.interview_time) {
                scheduledAt = new Date(`${interview.interview_date}T${interview.interview_time}`);
            } else if (interview.interview_date) {
                scheduledAt = new Date(interview.interview_date);
            } else {
                scheduledAt = new Date();
            }
            const isPast = scheduledAt < new Date();
            const status = interview.status || 'Scheduled';
            
            const interviewCard = `
                <div class="col-md-6 col-lg-4">
                    <div class="interview-card position-relative status-${statusClass}">
                        <div class="card-body">
                            <div class="interview-actions">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewInterviewDetails(${interview.id})" title="View Details">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    ${status.toLowerCase() === 'scheduled' ? `
                                    <button class="btn btn-sm btn-outline-success" onclick="updateInterviewStatus(${interview.id}, 'Completed')" title="Mark Complete">
                                        <i class="bx bx-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="updateInterviewStatus(${interview.id}, 'Cancelled')" title="Cancel">
                                        <i class="bx bx-x"></i>
                                    </button>
                                    ` : ''}
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">${interview.application ? escapeHtml(interview.application.first_name + ' ' + interview.application.last_name) : 'N/A'}</h5>
                                    <span class="badge bg-${getStatusColor(status)}">${status}</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><i class="bx bx-calendar"></i> <strong>Date & Time:</strong> ${formatDateTime(scheduledAt)}</p>
                                ${interview.location ? `<p class="mb-1"><i class="bx bx-map"></i> <strong>Location:</strong> ${escapeHtml(interview.location)}</p>` : ''}
                                <p class="mb-0"><i class="bx bx-briefcase"></i> <strong>Type:</strong> ${escapeHtml(interview.interview_type || interview.interview_mode || 'N/A')}</p>
                            </div>
                            
                            ${isPast && status.toLowerCase() === 'scheduled' ? `
                            <div class="alert alert-warning mt-2 mb-0 py-1">
                                <small><i class="bx bx-error"></i> Interview time has passed</small>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            grid.append(interviewCard);
        });
    }
    
    // Update Stats
    function updateStats(interviews) {
        const now = new Date();
        const weekFromNow = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
        
        const completed = interviews.filter(i => (i.status || '').toLowerCase() === 'completed').length;
        const thisWeek = interviews.filter(i => {
            let date;
            if (i.scheduled_at) {
                date = new Date(i.scheduled_at);
            } else if (i.interview_date && i.interview_time) {
                date = new Date(`${i.interview_date}T${i.interview_time}`);
            } else if (i.interview_date) {
                date = new Date(i.interview_date);
            } else {
                return false;
            }
            return date >= now && date <= weekFromNow && (i.status || '').toLowerCase() === 'scheduled';
        }).length;
        const cancelled = interviews.filter(i => (i.status || '').toLowerCase() === 'cancelled').length;
        
        $('#completedCount').text(completed);
        $('#thisWeekCount').text(thisWeek);
        $('#cancelledCount').text(cancelled);
    }
    
    // Schedule Interview Form
    $('#scheduleInterviewForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#scheduleInterviewModal').modal('hide');
                    loadInterviews();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    });
    
    // Utility Functions
    function getStatusColor(status) {
        const colors = {
            'scheduled': 'info',
            'completed': 'success',
            'cancelled': 'danger',
            'rescheduled': 'warning'
        };
        return colors[status.toLowerCase()] || 'secondary';
    }
    
    function formatDateTime(date) {
        return date.toLocaleString('en-GB', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
    
    function resetScheduleForm() {
        $('#scheduleInterviewForm')[0].reset();
        loadApplications();
    }
    
    // Global Functions
    window.viewInterviewDetails = function(interviewId) {
        Swal.fire('Info', 'Interview details view coming soon', 'info');
    };
    
    window.updateInterviewStatus = function(interviewId, status) {
        const statusText = status === 'Completed' ? 'complete' : 'cancel';
        Swal.fire({
            title: `${statusText.charAt(0).toUpperCase() + statusText.slice(1)} Interview?`,
            text: `Are you sure you want to mark this interview as ${statusText}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, mark as ${statusText}!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: recruitmentUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'update_interview_status',
                        interview_id: interviewId,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            loadInterviews();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    };
    
    // Filter functionality
    $('#applyFilters').on('click', function() {
        const search = $('#searchInput').val().toLowerCase();
        const status = $('#statusFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        let filtered = [...allInterviews];
        
        if (search) {
            filtered = filtered.filter(interview => {
                const app = interview.application;
                if (!app) return false;
                const name = `${app.first_name} ${app.last_name}`.toLowerCase();
                return name.includes(search);
            });
        }
        
        if (status) {
            filtered = filtered.filter(interview => (interview.status || '').toLowerCase() === status.toLowerCase());
        }
        
        if (dateFrom) {
            filtered = filtered.filter(interview => {
                const date = interview.scheduled_at || interview.interview_date;
                return date && date >= dateFrom;
            });
        }
        
        if (dateTo) {
            filtered = filtered.filter(interview => {
                const date = interview.scheduled_at || interview.interview_date;
                return date && date <= dateTo;
            });
        }
        
        renderInterviews(filtered);
    });
});
</script>
@endpush
