@extends('layouts.app')

@section('title', 'Job Vacancies Management - Recruitment')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-briefcase"></i> Job Vacancies Management
                </h4>
                <p class="text-muted">Create, manage, and approve job vacancies with comprehensive tracking and analytics</p>
            </div>
            <div class="btn-group" role="group">
                @if($canCreateJobs)
                    <button class="btn btn-primary" id="create-job-btn">
                        <i class="bx bx-plus"></i> New Job Vacancy
                    </button>
                @endif
                <button class="btn btn-outline-secondary" id="refresh-btn">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
                <a href="{{ route('modules.hr.recruitment') }}" class="btn btn-outline-dark">
                    <i class="bx bx-arrow-back"></i> Back to Recruitment
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .job-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #007bff;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border-left: 4px solid;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
    .stat-card.primary { border-left-color: #007bff; }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.danger { border-left-color: #dc3545; }
    .stat-card.info { border-left-color: #17a2b8; }
    .job-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
    }
    .job-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .job-card:hover .job-actions {
        opacity: 1;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card primary">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Jobs</h6>
                        <h3 class="mb-0 text-primary">{{ $advancedStats['total_jobs'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-briefcase"></i> All vacancies
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-briefcase"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card success">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Active Jobs</h6>
                        <h3 class="mb-0 text-success">{{ $advancedStats['active_jobs'] ?? 0 }}</h3>
                        <small class="text-info">
                            <i class="bx bx-check-circle"></i> Accepting applications
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-check-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card warning">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending Approval</h6>
                        <h3 class="mb-0 text-warning">{{ $advancedStats['pending_approval'] ?? 0 }}</h3>
                        <small class="text-warning">
                            <i class="bx bx-time"></i> Awaiting review
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-time"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card info">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Closed Jobs</h6>
                        <h3 class="mb-0 text-info">{{ $advancedStats['closed_jobs'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-lock"></i> No longer accepting
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-info rounded">
                            <i class="bx bx-lock"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Jobs</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by title...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Pending Approval">Pending Approval</option>
                    <option value="Closed">Closed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select id="sortBy" class="form-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="deadline">Deadline Soon</option>
                    <option value="applications">Most Applications</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <input type="date" id="dateFrom" class="form-control" placeholder="From">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="applyFilters">
                    <i class="bx bx-filter"></i> Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Jobs Grid -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bx bx-briefcase me-2"></i>Job Vacancies
            </h5>
        </div>
        <div class="card-body">
            <div id="jobsContainer">
                <div class="job-grid" id="jobsGrid">
                    <!-- Jobs will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="jobModalTitle">
                    <i class="bx bx-plus"></i> Create New Job Vacancy
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="jobForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" id="jobAction" value="create_job">
                    <input type="hidden" name="job_id" id="jobId">
                    
                    <div class="mb-3">
                        <label class="form-label">Job Title *</label>
                        <input type="text" name="job_title" id="jobTitle" class="form-control" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Description *</label>
                        <textarea name="job_description" id="jobDescription" class="form-control" rows="5" required maxlength="2000"></textarea>
                        <small class="text-muted">Maximum 2000 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Qualifications & Requirements *</label>
                        <textarea name="qualifications" id="qualifications" class="form-control" rows="5" required maxlength="2000"></textarea>
                        <small class="text-muted">Maximum 2000 characters</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Application Deadline *</label>
                                <input type="date" name="application_deadline" id="applicationDeadline" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Interview Mode *</label>
                                <select name="interview_mode[]" id="interviewMode" class="form-select" multiple required>
                                    <option value="Written">Written</option>
                                    <option value="Oral">Oral</option>
                                    <option value="Practical">Practical</option>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Required Attachments</label>
                        <div id="attachmentsContainer">
                            <div class="input-group mb-2">
                                <input type="text" name="required_attachments[]" class="form-control" placeholder="e.g., Resume/CV">
                                <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAttachment()">
                            <i class="bx bx-plus"></i> Add Attachment
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Job
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-info-circle"></i> Job Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobDetailsContent">
                <!-- Job details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const recruitmentUrl = '{{ route("recruitment.handle") }}';
    let allJobs = [];
    
    // Initialize
    loadJobs();
    
    // Create Job Button
    $('#create-job-btn').on('click', function() {
        resetJobForm();
        $('#jobModalTitle').html('<i class="bx bx-plus"></i> Create New Job Vacancy');
        $('#jobAction').val('create_job');
        $('#jobModal').modal('show');
    });
    
    // Refresh Button
    $('#refresh-btn').on('click', function() {
        loadJobs();
    });
    
    // Load Jobs
    function loadJobs() {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_all_jobs'
            },
            success: function(response) {
                if (response.success) {
                    allJobs = response.jobs || [];
                    renderJobs(allJobs);
                }
            }
        });
    }
    
    // Render Jobs
    function renderJobs(jobs) {
        const grid = $('#jobsGrid');
        grid.empty();
        
        if (jobs.length === 0) {
            grid.html(`
                <div class="col-12 text-center py-5">
                    <i class="bx bx-briefcase" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No Jobs Found</h5>
                    <p class="text-muted">Create your first job vacancy to get started</p>
                </div>
            `);
            return;
        }
        
        jobs.forEach(job => {
            const statusClass = getStatusClass(job.status);
            const deadline = new Date(job.application_deadline);
            const isExpired = deadline < new Date();
            
            const jobCard = `
                <div class="job-card position-relative">
                    <div class="card-body">
                        <div class="job-actions">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewJobDetails(${job.id})" title="View Details">
                                    <i class="bx bx-show"></i>
                                </button>
                                ${job.status === 'Pending Approval' && {{ $canEditPendingJobs ? 'true' : 'false' }} ? `
                                <button class="btn btn-sm btn-outline-warning" onclick="editJob(${job.id})" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </button>
                                ` : ''}
                                ${job.status === 'Pending Approval' && {{ $canApproveJobs ? 'true' : 'false' }} ? `
                                <button class="btn btn-sm btn-outline-success" onclick="approveJob(${job.id})" title="Approve">
                                    <i class="bx bx-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="rejectJob(${job.id})" title="Reject">
                                    <i class="bx bx-x"></i>
                                </button>
                                ` : ''}
                                ${job.status === 'Active' ? `
                                <button class="btn btn-sm btn-outline-warning" onclick="closeJob(${job.id})" title="Close">
                                    <i class="bx bx-lock"></i>
                                </button>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">${escapeHtml(job.job_title)}</h5>
                                <span class="status-badge badge bg-${statusClass}">${job.status}</span>
                            </div>
                        </div>
                        
                        <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            ${escapeHtml(job.job_description || 'No description')}
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span><i class="bx bx-calendar"></i> Deadline: ${formatDate(deadline)}</span>
                            <span><i class="bx bx-user"></i> ${job.applications_count || 0} applications</span>
                        </div>
                        
                        ${isExpired && job.status === 'Active' ? `
                        <div class="alert alert-warning mt-2 mb-0 py-1">
                            <small><i class="bx bx-error"></i> Deadline has passed</small>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            grid.append(jobCard);
        });
    }
    
    // Job Form Submit
    $('#jobForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const action = $('#jobAction').val();
        
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#jobModal').modal('hide');
                    loadJobs();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Operation failed', 'error');
            }
        });
    });
    
    // Utility Functions
    function getStatusClass(status) {
        const classes = {
            'Active': 'success',
            'Pending Approval': 'warning',
            'Closed': 'secondary',
            'Rejected': 'danger'
        };
        return classes[status] || 'secondary';
    }
    
    function formatDate(date) {
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.toString().replace(/[&<>"']/g, m => map[m]) : '';
    }
    
    function resetJobForm() {
        $('#jobForm')[0].reset();
        $('#jobId').val('');
        $('#jobAction').val('create_job');
        $('#attachmentsContainer').html(`
            <div class="input-group mb-2">
                <input type="text" name="required_attachments[]" class="form-control" placeholder="e.g., Resume/CV">
                <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </div>
        `);
    }
    
    // Global Functions
    window.addAttachment = function() {
        $('#attachmentsContainer').append(`
            <div class="input-group mb-2">
                <input type="text" name="required_attachments[]" class="form-control" placeholder="e.g., Resume/CV">
                <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </div>
        `);
    };
    
    window.removeAttachment = function(btn) {
        $(btn).closest('.input-group').remove();
    };
    
    window.viewJobDetails = function(jobId) {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_job_details',
                job_id: jobId
            },
            success: function(response) {
                if (response.success) {
                    const job = response.details;
                    const deadline = new Date(job.application_deadline);
                    
                    let attachmentsHtml = '<p class="text-muted">None specified</p>';
                    if (job.required_attachments && job.required_attachments.length > 0) {
                        attachmentsHtml = '<ul class="list-unstyled">';
                        job.required_attachments.forEach(item => {
                            attachmentsHtml += `<li><i class="bx bx-check text-success"></i> ${escapeHtml(item)}</li>`;
                        });
                        attachmentsHtml += '</ul>';
                    }
                    
                    let modesHtml = '<p class="text-muted">Not specified</p>';
                    if (job.interview_mode && job.interview_mode.length > 0) {
                        modesHtml = '<div class="d-flex gap-2">';
                        job.interview_mode.forEach(mode => {
                            modesHtml += `<span class="badge bg-primary">${escapeHtml(mode)}</span>`;
                        });
                        modesHtml += '</div>';
                    }
                    
                    const html = `
                        <div class="row">
                            <div class="col-md-8">
                                <h4>${escapeHtml(job.job_title)}</h4>
                                <span class="badge bg-${getStatusClass(job.status)} mb-3">${job.status}</span>
                                
                                <h6 class="mt-4">Job Description</h6>
                                <div class="border rounded p-3 bg-light">
                                    ${escapeHtml(job.job_description || 'No description provided')}
                                </div>
                                
                                <h6 class="mt-4">Qualifications & Requirements</h6>
                                <div class="border rounded p-3 bg-light">
                                    ${escapeHtml(job.qualifications || 'No qualifications specified')}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Job Information</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td><span class="badge bg-${getStatusClass(job.status)}">${job.status}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Deadline:</strong></td>
                                                <td>${formatDate(deadline)}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Applications:</strong></td>
                                                <td>${job.applications_count || 0}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created:</strong></td>
                                                <td>${new Date(job.created_at).toLocaleDateString()}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created By:</strong></td>
                                                <td>${job.creator ? escapeHtml(job.creator.name) : 'N/A'}</td>
                                            </tr>
                                        </table>
                                        
                                        <h6 class="mt-3">Interview Mode</h6>
                                        ${modesHtml}
                                        
                                        <h6 class="mt-3">Required Attachments</h6>
                                        ${attachmentsHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#jobDetailsContent').html(html);
                    $('#jobDetailsModal').modal('show');
                }
            }
        });
    };
    
    window.editJob = function(jobId) {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_job_details_for_edit',
                job_id: jobId
            },
            success: function(response) {
                if (response.success) {
                    const job = response.details;
                    $('#jobId').val(job.id);
                    $('#jobTitle').val(job.job_title);
                    $('#jobDescription').val(job.job_description);
                    $('#qualifications').val(job.qualifications);
                    $('#applicationDeadline').val(job.application_deadline);
                    
                    // Set interview mode
                    if (Array.isArray(job.interview_mode)) {
                        $('#interviewMode').val(job.interview_mode);
                    }
                    
                    // Set attachments
                    $('#attachmentsContainer').empty();
                    if (job.required_attachments && job.required_attachments.length > 0) {
                        job.required_attachments.forEach(att => {
                            $('#attachmentsContainer').append(`
                                <div class="input-group mb-2">
                                    <input type="text" name="required_attachments[]" class="form-control" value="${escapeHtml(att)}">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this)">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            `);
                        });
                    } else {
                        addAttachment();
                    }
                    
                    $('#jobAction').val('edit_job');
                    $('#jobModalTitle').html('<i class="bx bx-edit"></i> Edit Job Vacancy');
                    $('#jobModal').modal('show');
                }
            }
        });
    };
    
    window.approveJob = function(jobId) {
        Swal.fire({
            title: 'Approve Job?',
            text: 'This job will be activated and visible to applicants',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: recruitmentUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'approve_job',
                        job_id: jobId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Approved!', response.message, 'success');
                            loadJobs();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    };
    
    window.rejectJob = function(jobId) {
        Swal.fire({
            title: 'Reject Job?',
            input: 'textarea',
            inputLabel: 'Rejection Reason',
            inputPlaceholder: 'Enter reason for rejection...',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a rejection reason';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: recruitmentUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'reject_job',
                        job_id: jobId,
                        rejection_reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Rejected!', response.message, 'success');
                            loadJobs();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    };
    
    window.closeJob = function(jobId) {
        Swal.fire({
            title: 'Close Job?',
            text: 'This job will no longer accept applications',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, close it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: recruitmentUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'close_job',
                        job_id: jobId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Closed!', response.message, 'success');
                            loadJobs();
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
        const sort = $('#sortBy').val();
        const dateFrom = $('#dateFrom').val();
        
        let filtered = [...allJobs];
        
        if (search) {
            filtered = filtered.filter(job => 
                job.job_title.toLowerCase().includes(search)
            );
        }
        
        if (status) {
            filtered = filtered.filter(job => job.status === status);
        }
        
        if (dateFrom) {
            filtered = filtered.filter(job => job.created_at >= dateFrom);
        }
        
        // Sort
        if (sort === 'newest') {
            filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        } else if (sort === 'oldest') {
            filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        } else if (sort === 'deadline') {
            filtered.sort((a, b) => new Date(a.application_deadline) - new Date(b.application_deadline));
        } else if (sort === 'applications') {
            filtered.sort((a, b) => (b.applications_count || 0) - (a.applications_count || 0));
        }
        
        renderJobs(filtered);
    });
});
</script>
@endpush

