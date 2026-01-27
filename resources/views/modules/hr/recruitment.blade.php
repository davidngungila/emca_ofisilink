@extends('layouts.app')

@section('title', 'Job Vacancies - Recruitment')

@push('styles')
<style>
    .job-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        height: 100%;
        background: white;
    }
    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border-color: #007bff;
    }
    .job-card-header {
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .job-card-body {
        padding: 20px;
    }
    .job-status-badge {
        font-size: 0.75rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
    }
    .job-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }
    .job-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .job-actions {
        display: flex;
        gap: 8px;
        margin-top: 15px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .job-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }
    @media (max-width: 768px) {
        .job-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Professional Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-briefcase me-2"></i>Job Vacancies
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Browse and manage all job vacancies in the system
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @if($canCreateJobs)
                            <button class="btn btn-light btn-lg shadow-sm" id="create-job-btn">
                                <i class="bx bx-plus-circle me-2"></i>Create New Job
                            </button>
                            @endif
                            <a href="{{ route('jobs.list') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-list-ul me-2"></i>Job Management
                            </a>
                            @if($canManageApplications)
                            <a href="{{ route('jobs.applications') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-user-check me-2"></i>Applications
                            </a>
                            <a href="{{ route('jobs.interviews') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-calendar me-2"></i>Interviews
                            </a>
                            <a href="{{ route('jobs.analytics') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-bar-chart me-2"></i>Analytics
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg me-3 bg-primary">
                        <i class="bx bx-briefcase fs-2 text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Total Jobs</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ $advancedStats['total_jobs'] ?? 0 }}</h3>
                        <small class="text-muted">All vacancies</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-left: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bx bx-check-circle fs-2 text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Active Jobs</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $advancedStats['active_jobs'] ?? 0 }}</h3>
                        <small class="text-success">Accepting applications</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-left: 4px solid #f59e0b !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="bx bx-time fs-2 text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pending Approval</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $advancedStats['pending_approval'] ?? 0 }}</h3>
                        <small class="text-warning">Awaiting review</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-left: 4px solid #6b7280 !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                        <i class="bx bx-lock fs-2 text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Closed Jobs</h6>
                        <h3 class="mb-0 fw-bold text-secondary">{{ $advancedStats['closed_jobs'] ?? 0 }}</h3>
                        <small class="text-muted">No longer accepting</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Search Jobs</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by job title...">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Pending Approval">Pending Approval</option>
                    <option value="Closed">Closed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Sort By</label>
                <select id="sortBy" class="form-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="deadline">Deadline Soon</option>
                    <option value="applications">Most Applications</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">View</label>
                <select id="viewType" class="form-select">
                    <option value="grid">Grid View</option>
                    <option value="list">List View</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="applyFilters">
                    <i class="bx bx-filter me-1"></i>Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Pending Approval Section (for approvers) -->
    @if($canApproveJobs && $pendingApprovalJobs->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bx bx-error-circle me-2"></i>Jobs Pending Your Approval ({{ $pendingApprovalJobs->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="job-grid" id="pendingApprovalGrid">
                        @foreach($pendingApprovalJobs as $job)
                        <div class="job-card" data-status="Pending Approval">
                            <div class="job-card-header bg-warning text-dark">
                                <h5 class="mb-1 fw-bold">{{ $job->job_title }}</h5>
                                <span class="job-status-badge bg-dark text-white">Pending Approval</span>
                            </div>
                            <div class="job-card-body">
                                <p class="text-muted mb-3" style="min-height: 60px;">
                                    {{ Str::limit($job->job_description ?? 'No description available', 120) }}
                                </p>
                                <div class="job-meta">
                                    <div class="job-meta-item">
                                        <i class="bx bx-user text-primary"></i>
                                        <span>{{ $job->creator->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="job-meta-item">
                                        <i class="bx bx-calendar text-info"></i>
                                        <span>{{ $job->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="btn btn-sm btn-primary btn-review" data-id="{{ $job->id }}">
                                        <i class="bx bx-show me-1"></i>Review
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- My Pending Jobs Section (for creators) -->
    @if($canEditPendingJobs && $myPendingJobs->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bx bx-edit me-2"></i>My Jobs Pending Approval ({{ $myPendingJobs->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="job-grid" id="myPendingGrid">
                        @foreach($myPendingJobs as $job)
                        <div class="job-card" data-status="Pending Approval">
                            <div class="job-card-header bg-info text-white">
                                <h5 class="mb-1 fw-bold">{{ $job->job_title }}</h5>
                                <span class="job-status-badge bg-white text-info">Pending Approval</span>
                            </div>
                            <div class="job-card-body">
                                <p class="text-muted mb-3" style="min-height: 60px;">
                                    {{ Str::limit($job->job_description ?? 'No description available', 120) }}
                                </p>
                                <div class="job-meta">
                                    <div class="job-meta-item">
                                        <i class="bx bx-calendar text-info"></i>
                                        <span>Deadline: {{ $job->application_deadline->format('M d, Y') }}</span>
                                    </div>
                                    <div class="job-meta-item">
                                        <i class="bx bx-user text-primary"></i>
                                        <span>{{ $job->applications_count }} applications</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="{{ $job->id }}">
                                        <i class="bx bx-show me-1"></i>View
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning btn-edit-job" data-id="{{ $job->id }}">
                                        <i class="bx bx-edit me-1"></i>Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- All Jobs Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2"></i>All Job Vacancies
                    </h5>
                </div>
                <div class="card-body">
                    <div class="job-grid" id="jobsGrid">
                        @forelse($jobs as $job)
                            @if($job->status !== 'Pending Approval')
                            <div class="job-card" data-status="{{ $job->status }}" data-title="{{ strtolower($job->job_title) }}">
                                @php
                                    $statusColors = [
                                        'Active' => ['bg' => 'success', 'text' => 'white', 'header' => 'bg-success'],
                                        'Closed' => ['bg' => 'secondary', 'text' => 'white', 'header' => 'bg-secondary'],
                                        'Rejected' => ['bg' => 'danger', 'text' => 'white', 'header' => 'bg-danger'],
                                    ];
                                    $statusConfig = $statusColors[$job->status] ?? ['bg' => 'info', 'text' => 'white', 'header' => 'bg-info'];
                                @endphp
                                <div class="job-card-header {{ $statusConfig['header'] }} text-white">
                                    <h5 class="mb-1 fw-bold">{{ $job->job_title }}</h5>
                                    <span class="job-status-badge bg-white {{ $statusConfig['text'] === 'white' ? 'text-' . $statusConfig['bg'] : 'text-dark' }}">
                                        {{ $job->status }}
                                    </span>
                                </div>
                                <div class="job-card-body">
                                    <p class="text-muted mb-3" style="min-height: 60px;">
                                        {{ Str::limit($job->job_description ?? 'No description available', 120) }}
                                    </p>
                                    <div class="job-meta">
                                        <div class="job-meta-item">
                                            <i class="bx bx-calendar text-danger"></i>
                                            <span>Deadline: {{ $job->application_deadline->format('M d, Y') }}</span>
                                        </div>
                                        <div class="job-meta-item">
                                            <i class="bx bx-user text-primary"></i>
                                            <span>{{ $job->applications_count }} applications</span>
                                        </div>
                                    </div>
                                    <div class="job-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="{{ $job->id }}">
                                            <i class="bx bx-show me-1"></i>View
                                        </button>
                                        @if($canManageApplications)
                                        <button class="btn btn-sm btn-outline-success btn-view-applications" data-id="{{ $job->id }}">
                                            <i class="bx bx-user me-1"></i>Applications
                                        </button>
                                        @endif
                                        @if($canCreateJobs && $job->status === 'Active')
                                        <button class="btn btn-sm btn-outline-secondary btn-close-job" data-id="{{ $job->id }}">
                                            <i class="bx bx-x me-1"></i>Close
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="bx bx-briefcase" style="font-size: 4rem; color: #dee2e6;"></i>
                                <h5 class="mt-3 text-muted">No Jobs Found</h5>
                                <p class="text-muted">Get started by creating your first job vacancy.</p>
                                @if($canCreateJobs)
                                <button class="btn btn-primary mt-2" id="create-job-btn-empty">
                                    <i class="bx bx-plus-circle me-2"></i>Create New Job
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforelse
                    </div>
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
                                <input type="date" name="application_deadline" id="applicationDeadline" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Interview Mode *</label>
                                <div class="border rounded p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Written" id="mode_written">
                                        <label class="form-check-label" for="mode_written">Written Test</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Oral" id="mode_oral">
                                        <label class="form-check-label" for="mode_oral">Oral Interview</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Practical" id="mode_practical">
                                        <label class="form-check-label" for="mode_practical">Practical Assessment</label>
                                    </div>
                                </div>
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
                    <button type="submit" class="btn btn-primary" id="jobSubmitBtn">
                        <i class="bx bx-save"></i> Submit for Approval
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
                <div class="text-center p-5">
                    <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                    <p>Loading Details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Review Job Modal (for CEO approval) -->
<div class="modal fade" id="reviewJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Review Job Vacancy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewJobModalBody">
                <div class="text-center p-5">
                    <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                    <p>Loading Details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger btn-reject-from-modal" data-id="">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-success btn-approve-from-modal" data-id="">
                    <i class="bx bx-check me-1"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const recruitmentUrl = '{{ route("recruitment.handle") }}';

    // Filter functionality
    $('#applyFilters').on('click', function() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const statusFilter = $('#statusFilter').val();
        const sortBy = $('#sortBy').val();
        const viewType = $('#viewType').val();

        let visibleCards = $('.job-card').filter(function() {
            const card = $(this);
            const title = card.data('title') || card.find('h5').text().toLowerCase();
            const status = card.data('status');
            
            const matchesSearch = !searchTerm || title.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            return matchesSearch && matchesStatus;
        });

        // Hide all cards first
        $('.job-card').hide();
        
        // Show matching cards
        visibleCards.show();

        // Sort cards
        const cardsArray = visibleCards.toArray();
        cardsArray.sort(function(a, b) {
            const cardA = $(a);
            const cardB = $(b);
            
            switch(sortBy) {
                case 'oldest':
                    return new Date(cardA.find('.job-meta-item').last().text()) - new Date(cardB.find('.job-meta-item').last().text());
                case 'deadline':
                    const deadlineA = cardA.find('.job-meta-item').first().text();
                    const deadlineB = cardB.find('.job-meta-item').first().text();
                    return deadlineA.localeCompare(deadlineB);
                case 'applications':
                    const appsA = parseInt(cardA.find('.job-meta-item').last().text()) || 0;
                    const appsB = parseInt(cardB.find('.job-meta-item').last().text()) || 0;
                    return appsB - appsA;
                default: // newest
                    return 0; // Already sorted by newest
            }
        });

        // Reorder in DOM
        const grid = $('#jobsGrid');
        cardsArray.forEach(card => grid.append(card));

        // Change view type
        if (viewType === 'list') {
            grid.css('grid-template-columns', '1fr');
        } else {
            grid.css('grid-template-columns', 'repeat(auto-fill, minmax(350px, 1fr))');
        }

        // Show message if no results
        if (visibleCards.length === 0) {
            if ($('#noResultsMessage').length === 0) {
                grid.append(`
                    <div class="col-12" id="noResultsMessage">
                        <div class="text-center py-5">
                            <i class="bx bx-search" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">No Jobs Match Your Filters</h5>
                            <p class="text-muted">Try adjusting your search criteria.</p>
                        </div>
                    </div>
                `);
            }
        } else {
            $('#noResultsMessage').remove();
        }
    });

    // Real-time search
    $('#searchInput').on('keyup', function() {
        $('#applyFilters').click();
    });

    // Status filter change
    $('#statusFilter, #sortBy, #viewType').on('change', function() {
        $('#applyFilters').click();
    });

    // Create job button
    $('#create-job-btn, #create-job-btn-empty').on('click', function() {
        $('#jobForm')[0].reset();
        $('#jobModalTitle').text('New Job Vacancy');
        $('#jobAction').val('create_job');
        $('#jobId').val('');
        $('#jobSubmitBtn').text('Submit for Approval');
        $('#jobModal').modal('show');
    });

    // View job details
    $(document).on('click', '.btn-view-details', function() {
        const jobId = $(this).data('id');
        const modal = $('#jobDetailsModal');
        const modalBody = $('#jobDetailsContent');
        modalBody.html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Details...</p></div>');
        modal.modal('show');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details', job_id: jobId },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.details;
                    const statusBadgeClass = {
                        'Active': 'success',
                        'Pending Approval': 'info',
                        'Rejected': 'danger',
                        'Closed': 'secondary'
                    }[d.status] || 'light';
                    
                    const content = `
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="mb-0">${d.job_title}</h3>
                            <span class="badge bg-${statusBadgeClass} fs-6">${d.status}</span>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><i class="bx bx-user me-2"></i>Created by ${d.creator ? d.creator.name : 'N/A'}</p>
                                <p class="text-muted"><i class="bx bx-calendar-plus me-2"></i>Created on ${new Date(d.created_at).toLocaleDateString()}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><i class="bx bx-calendar-times me-2"></i>Application Deadline</p>
                                <p class="fw-bold">${new Date(d.application_deadline).toLocaleDateString()}</p>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5><i class="bx bx-file-blank me-2"></i>Job Description</h5>
                            <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">${d.job_description}</div>
                        </div>
                        <div class="mb-4">
                            <h5><i class="bx bx-graduation me-2"></i>Qualifications / Requirements</h5>
                            <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">${d.qualifications}</div>
                        </div>
                    `;
                    modalBody.html(content);
                } else {
                    modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            },
            error: function() {
                modalBody.html('<div class="alert alert-danger">Failed to load job details.</div>');
            }
        });
    });

    // Review job (for approvers)
    $(document).on('click', '.btn-review', function() {
        const jobId = $(this).data('id');
        const modal = $('#reviewJobModal');
        const modalBody = $('#reviewJobModalBody');
        modalBody.html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Details...</p></div>');
        modal.find('.btn-approve-from-modal').data('id', jobId);
        modal.find('.btn-reject-from-modal').data('id', jobId);
        modal.modal('show');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details', job_id: jobId },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.details;
                    const content = `
                        <h3>${d.job_title}</h3>
                        <p class="text-muted">Created by ${d.creator ? d.creator.name : 'N/A'} on ${new Date(d.created_at).toLocaleDateString()}</p>
                        <hr>
                        <h5><i class="bx bx-file-blank me-2"></i>Job Description</h5>
                        <div class="p-3 bg-light rounded mb-3" style="white-space: pre-wrap;">${d.job_description}</div>
                        <h5><i class="bx bx-graduation me-2"></i>Qualifications / Requirements</h5>
                        <div class="p-3 bg-light rounded mb-3" style="white-space: pre-wrap;">${d.qualifications}</div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="bx bx-calendar-times me-2"></i>Deadline</h5>
                                <p>${new Date(d.application_deadline).toLocaleDateString()}</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="bx bx-comment me-2"></i>Interview Mode(s)</h5>
                                <p>${d.interview_mode ? d.interview_mode.join(', ') : 'Not specified'}</p>
                            </div>
                        </div>
                    `;
                    modalBody.html(content);
                } else {
                    modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            }
        });
    });

    // Approve job
    $('#reviewJobModal').on('click', '.btn-approve-from-modal', function() {
        const jobId = $(this).data('id');
        Swal.fire({
            title: 'Approve this Job?',
            text: 'This will make the vacancy active and public.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: recruitmentUrl,
                    data: { action: 'approve_job', job_id: jobId },
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire('Approved!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    });

    // Reject job
    $('#reviewJobModal').on('click', '.btn-reject-from-modal', function() {
        const jobId = $(this).data('id');
        Swal.fire({
            title: 'Reject this Job?',
            text: 'Please provide a reason for rejection:',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Reject Request',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a reason for rejection';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: recruitmentUrl,
                    data: { action: 'reject_job', job_id: jobId, reason: result.value },
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire('Rejected!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    });

    // Edit job
    $(document).on('click', '.btn-edit-job', function() {
        const jobId = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details_for_edit', job_id: jobId },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.details;
                    $('#jobForm')[0].reset();
                    $('#jobModalTitle').html('<i class="bx bx-edit"></i> Edit Job Vacancy');
                    $('#jobAction').val('edit_job');
                    $('#jobId').val(jobId);
                    $('#jobSubmitBtn').html('<i class="bx bx-save"></i> Update Job');
                    $('#jobTitle').val(d.job_title);
                    $('#jobDescription').val(d.job_description);
                    $('#qualifications').val(d.qualifications);
                    $('#applicationDeadline').val(d.application_deadline);
                    
                    if (d.interview_mode) {
                        d.interview_mode.forEach(mode => {
                            $(`input[name="interview_mode[]"][value="${mode}"]`).prop('checked', true);
                        });
                    }
                    
                    $('#jobModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    });

    // Close job
    $(document).on('click', '.btn-close-job', function() {
        const jobId = $(this).data('id');
        Swal.fire({
            title: 'Manually Close Job?',
            text: 'No new applications will be accepted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, close it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: recruitmentUrl,
                    data: { action: 'close_job', job_id: jobId },
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire('Job Closed', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    });

    // View applications
    $(document).on('click', '.btn-view-applications', function() {
        const jobId = $(this).data('id');
        window.location.href = '{{ route("jobs.applications") }}?job=' + jobId;
    });

    // Job form submission
    $('#jobForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate interview modes
        const interviewModes = $('input[name="interview_mode[]"]:checked').length;
        if (interviewModes === 0) {
            Swal.fire('Validation Error', 'Please select at least one interview mode.', 'error');
            return;
        }

        const formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                $('#jobModal').modal('hide');
                Swal.fire(
                    response.success ? 'Success!' : 'Error!',
                    response.message,
                    response.success ? 'success' : 'error'
                ).then(() => {
                    if (response.success) {
                        location.reload();
                    }
                });
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred';
                Swal.fire('Error!', message, 'error');
            }
        });
    });
});

// Attachment management functions
function addAttachment() {
    const container = $('#attachmentsContainer');
    const newInput = `
        <div class="input-group mb-2">
            <input type="text" name="required_attachments[]" class="form-control" placeholder="e.g., Resume/CV">
            <button type="button" class="btn btn-outline-danger" onclick="removeAttachment(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.append(newInput);
}

function removeAttachment(btn) {
    $(btn).closest('.input-group').remove();
}
</script>
@endpush
@endsection
