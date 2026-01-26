@extends('layouts.app')

@section('title', 'Job Applications Management - Recruitment')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-user"></i> Job Applications Management
                </h4>
                <p class="text-muted">Review, evaluate, and manage job applications with comprehensive tracking and status updates</p>
            </div>
            <div class="btn-group" role="group">
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
    .application-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid;
    }
    .application-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .application-card.status-applied { border-left-color: #17a2b8; }
    .application-card.status-shortlisted { border-left-color: #28a745; }
    .application-card.status-interviewing { border-left-color: #ffc107; }
    .application-card.status-offer_extended { border-left-color: #007bff; }
    .application-card.status-hired { border-left-color: #28a745; }
    .application-card.status-rejected { border-left-color: #dc3545; }
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
    .stat-card.info { border-left-color: #17a2b8; }
    .stat-card.danger { border-left-color: #dc3545; }
    .applications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .application-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .application-card:hover .application-actions {
        opacity: 1;
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
                        <h6 class="text-muted mb-1">Total Applications</h6>
                        <h3 class="mb-0 text-primary">{{ $advancedStats['total_applications'] ?? 0 }}</h3>
                        <small class="text-info">
                            <i class="bx bx-user"></i> All applications
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-user"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card success">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Shortlisted</h6>
                        <h3 class="mb-0 text-success">{{ $advancedStats['shortlisted'] ?? 0 }}</h3>
                        <small class="text-success">
                            <i class="bx bx-check-circle"></i> Selected candidates
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
                        <h6 class="text-muted mb-1">Interviewing</h6>
                        <h3 class="mb-0 text-warning">{{ $advancedStats['interviewing'] ?? 0 }}</h3>
                        <small class="text-warning">
                            <i class="bx bx-calendar"></i> In process
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card info">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Hired</h6>
                        <h3 class="mb-0 text-info">{{ $advancedStats['hired'] ?? 0 }}</h3>
                        <small class="text-success">
                            <i class="bx bx-check-double"></i> Successful hires
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-info rounded">
                            <i class="bx bx-check-double"></i>
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
                <label class="form-label">Search Applications</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Applied">Applied</option>
                    <option value="Shortlisted">Shortlisted</option>
                    <option value="Interviewing">Interviewing</option>
                    <option value="Offer Extended">Offer Extended</option>
                    <option value="Hired">Hired</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Job</label>
                <select id="jobFilter" class="form-select">
                    <option value="">All Jobs</option>
                    <!-- Jobs will be populated -->
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select id="sortBy" class="form-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">Name A-Z</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" id="applyFilters">
                    <i class="bx bx-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Applications Grid -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bx bx-user me-2"></i>Job Applications
            </h5>
        </div>
        <div class="card-body">
            <div id="applicationsContainer">
                <div class="applications-grid" id="applicationsGrid">
                    <!-- Applications will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Details Modal -->
<div class="modal fade" id="applicationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-info-circle"></i> Application Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="applicationDetailsContent">
                <!-- Application details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-edit"></i> Update Application Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="update_application_status">
                    <input type="hidden" name="application_id" id="statusApplicationId">
                    
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <input type="text" id="currentStatus" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Status *</label>
                        <select name="status" id="newStatus" class="form-select" required>
                            <option value="Applied">Applied</option>
                            <option value="Shortlisted">Shortlisted</option>
                            <option value="Interviewing">Interviewing</option>
                            <option value="Offer Extended">Offer Extended</option>
                            <option value="Hired">Hired</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="statusNotes" class="form-control" rows="3" placeholder="Optional notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save"></i> Update Status
                    </button>
                </div>
            </form>
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
    let allApplications = [];
    let allJobs = [];
    
    // Initialize
    loadJobs();
    loadApplications();
    
    // Refresh Button
    $('#refresh-btn').on('click', function() {
        loadApplications();
    });
    
    // Load Jobs for Filter
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
                    const select = $('#jobFilter');
                    select.append('<option value="">All Jobs</option>');
                    allJobs.forEach(job => {
                        select.append(`<option value="${job.id}">${escapeHtml(job.job_title)}</option>`);
                    });
                }
            }
        });
    }
    
    // Load Applications
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
                    renderApplications(allApplications);
                }
            }
        });
    }
    
    // Render Applications
    function renderApplications(applications) {
        const grid = $('#applicationsGrid');
        grid.empty();
        
        if (applications.length === 0) {
            grid.html(`
                <div class="col-12 text-center py-5">
                    <i class="bx bx-user" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No Applications Found</h5>
                    <p class="text-muted">Applications will appear here when candidates apply</p>
                </div>
            `);
            return;
        }
        
        applications.forEach(app => {
            const statusClass = app.status.toLowerCase().replace(' ', '-');
            const applicationDate = new Date(app.application_date || app.created_at);
            
            const appCard = `
                <div class="application-card position-relative status-${statusClass}">
                    <div class="card-body">
                        <div class="application-actions">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewApplicationDetails(${app.id})" title="View Details">
                                    <i class="bx bx-show"></i>
                                </button>
                                ${ {{ $canManageApplications ? 'true' : 'false' }} ? `
                                <button class="btn btn-sm btn-outline-warning" onclick="updateApplicationStatus(${app.id}, '${app.status}')" title="Update Status">
                                    <i class="bx bx-edit"></i>
                                </button>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">${escapeHtml(app.first_name)} ${escapeHtml(app.last_name)}</h5>
                                <span class="badge bg-${getStatusColor(app.status)}">${app.status}</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-1"><i class="bx bx-envelope"></i> ${escapeHtml(app.email)}</p>
                            <p class="mb-1"><i class="bx bx-phone"></i> ${escapeHtml(app.phone)}</p>
                            <p class="mb-0"><i class="bx bx-briefcase"></i> ${app.job ? escapeHtml(app.job.job_title) : 'N/A'}</p>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span><i class="bx bx-calendar"></i> ${formatDate(applicationDate)}</span>
                            ${app.documents_count ? `<span><i class="bx bx-file"></i> ${app.documents_count} documents</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
            grid.append(appCard);
        });
    }
    
    // Update Status Form
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#updateStatusModal').modal('hide');
                    loadApplications();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    });
    
    // Utility Functions
    function getStatusColor(status) {
        const colors = {
            'Applied': 'info',
            'Shortlisted': 'success',
            'Interviewing': 'warning',
            'Offer Extended': 'primary',
            'Hired': 'success',
            'Rejected': 'danger'
        };
        return colors[status] || 'secondary';
    }
    
    function formatDate(date) {
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
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
    
    // Global Functions
    window.viewApplicationDetails = function(applicationId) {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_application_details',
                application_id: applicationId
            },
            success: function(response) {
                if (response.success) {
                    const app = response.application;
                    const applicationDate = new Date(app.application_date || app.created_at);
                    
                    let documentsHtml = '<p class="text-muted">No documents uploaded</p>';
                    if (app.documents && app.documents.length > 0) {
                        documentsHtml = '<div class="list-group">';
                        app.documents.forEach(doc => {
                            documentsHtml += `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bx bx-file"></i> ${escapeHtml(doc.original_filename)}
                                            <br><small class="text-muted">${doc.document_type}</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="downloadDocument(${doc.id})">
                                            <i class="bx bx-download"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        documentsHtml += '</div>';
                    }
                    
                    const html = `
                        <div class="row">
                            <div class="col-md-8">
                                <h4>${escapeHtml(app.first_name)} ${escapeHtml(app.last_name)}</h4>
                                <span class="badge bg-${getStatusColor(app.status)} mb-3">${app.status}</span>
                                
                                <h6 class="mt-4">Contact Information</h6>
                                <div class="border rounded p-3 bg-light">
                                    <p class="mb-2"><strong>Email:</strong> ${escapeHtml(app.email)}</p>
                                    <p class="mb-2"><strong>Phone:</strong> ${escapeHtml(app.phone)}</p>
                                    ${app.current_address ? `<p class="mb-0"><strong>Address:</strong> ${escapeHtml(app.current_address)}</p>` : ''}
                                </div>
                                
                                ${app.cover_letter ? `
                                <h6 class="mt-4">Cover Letter</h6>
                                <div class="border rounded p-3 bg-light">
                                    ${escapeHtml(app.cover_letter)}
                                </div>
                                ` : ''}
                                
                                <h6 class="mt-4">Documents</h6>
                                ${documentsHtml}
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Application Information</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td><span class="badge bg-${getStatusColor(app.status)}">${app.status}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Job:</strong></td>
                                                <td>${app.job ? escapeHtml(app.job.job_title) : 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Applied:</strong></td>
                                                <td>${formatDate(applicationDate)}</td>
                                            </tr>
                                            ${app.shortlisted_at ? `
                                            <tr>
                                                <td><strong>Shortlisted:</strong></td>
                                                <td>${formatDate(new Date(app.shortlisted_at))}</td>
                                            </tr>
                                            ` : ''}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#applicationDetailsContent').html(html);
                    $('#applicationDetailsModal').modal('show');
                }
            }
        });
    };
    
    window.updateApplicationStatus = function(applicationId, currentStatus) {
        $('#statusApplicationId').val(applicationId);
        $('#currentStatus').val(currentStatus);
        $('#newStatus').val(currentStatus);
        $('#statusNotes').val('');
        $('#updateStatusModal').modal('show');
    };
    
    // Filter functionality
    $('#applyFilters').on('click', function() {
        const search = $('#searchInput').val().toLowerCase();
        const status = $('#statusFilter').val();
        const jobId = $('#jobFilter').val();
        const sort = $('#sortBy').val();
        
        let filtered = [...allApplications];
        
        if (search) {
            filtered = filtered.filter(app => 
                app.first_name.toLowerCase().includes(search) ||
                app.last_name.toLowerCase().includes(search) ||
                app.email.toLowerCase().includes(search)
            );
        }
        
        if (status) {
            filtered = filtered.filter(app => app.status === status);
        }
        
        if (jobId) {
            filtered = filtered.filter(app => app.job_id == jobId);
        }
        
        // Sort
        if (sort === 'newest') {
            filtered.sort((a, b) => new Date(b.application_date || b.created_at) - new Date(a.application_date || a.created_at));
        } else if (sort === 'oldest') {
            filtered.sort((a, b) => new Date(a.application_date || a.created_at) - new Date(b.application_date || b.created_at));
        } else if (sort === 'name') {
            filtered.sort((a, b) => {
                const nameA = `${a.first_name} ${a.last_name}`.toLowerCase();
                const nameB = `${b.first_name} ${b.last_name}`.toLowerCase();
                return nameA.localeCompare(nameB);
            });
        }
        
        renderApplications(filtered);
    });
});
</script>
@endpush

