@extends('layouts.app')

@section('title', 'Recruitment Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.css">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="rgba(255,255,255,0.1)" stroke-width="2" fill="none"/></svg>') 0 0/50px 50px;
        opacity: 0.3;
    }

    .nav-pills .nav-link {
        color: #64748b;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        transition: all 0.2s;
    }

    .nav-pills .nav-link.active {
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 600;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-hover-shadow);
        border-color: #e2e8f0;
    }

    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .job-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .job-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-hover-shadow);
        border-color: #3b82f6;
    }

    .job-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f8fafc;
        position: relative;
    }

    .status-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.025em;
        text-transform: uppercase;
    }

    .status-active { background: #dcfce7; color: #166534; }
    .status-pending { background: #fef9c3; color: #854d0e; }
    .status-closed { background: #f1f5f9; color: #475569; }
    .status-rejected { background: #fee2e2; color: #991b1b; }

    .job-card-body {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .job-meta-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: auto;
        padding-top: 1.5rem;
    }

    .job-meta-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .job-actions {
        padding: 1.25rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        display: flex;
        gap: 0.75rem;
    }

    .avatar-group {
        display: flex;
        align-items: center;
    }
    .avatar-group .avatar {
        width: 32px;
        height: 32px;
        border: 2px solid white;
        border-radius: 50%;
        margin-left: -8px;
        background: #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #475569;
        font-weight: 600;
    }
    .avatar-group .avatar:first-child { margin-left: 0; }

    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .quick-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        color: white;
        transition: all 0.2s;
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(4px);
    }
    
    .quick-action-btn:hover {
        background: white;
        color: var(--bs-primary);
        transform: scale(1.05);
    }

    /* Scrollbar styling for lists */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Hero Header -->
    <div class="dashboard-header mb-4 shadow-lg">
        <div class="p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2 class="text-white fw-bold mb-2 display-6">Recruitment Center</h2>
                    <p class="text-white opacity-75 fs-5 mb-0">Manage talent acquisition, track applications, and optimize your hiring pipeline.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex justify-content-lg-end gap-3 flex-wrap">
                         @if($canCreateJobs)
                        <div class="text-center">
                            <button class="quick-action-btn mb-2" id="create-job-btn" title="Create Job Vacancy">
                                <i class="bx bx-plus fs-4"></i>
                            </button>
                            <div class="text-white small fw-medium">Post Job</div>
                        </div>
                        @endif
                        <div class="text-center">
                            <a href="{{ route('jobs.manpower-planning') }}" class="quick-action-btn mb-2" title="Manpower Planning">
                                <i class="bx bx-buildings fs-4"></i>
                            </a>
                            <div class="text-white small fw-medium">Planning</div>
                        </div>
                        @if($canManageApplications)
                        <div class="text-center">
                            <a href="{{ route('jobs.analytics') }}" class="quick-action-btn mb-2" title="Analytics">
                                <i class="bx bx-line-chart fs-4"></i>
                            </a>
                            <div class="text-white small fw-medium">Analytics</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Strip -->
        <div class="bg-white bg-opacity-10 backdrop-blur-sm border-top border-white border-opacity-10 py-3 px-4">
            <div class="row g-4 text-white text-center text-md-start">
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                        <div class="bg-white bg-opacity-20 rounded p-2"><i class="bx bx-briefcase fs-4"></i></div>
                        <div>
                            <div class="h5 mb-0 fw-bold">{{ $advancedStats['total_jobs'] ?? 0 }}</div>
                            <div class="small opacity-75">Total Vacancies</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                        <div class="bg-success bg-opacity-25 rounded p-2"><i class="bx bx-check-circle fs-4"></i></div>
                        <div>
                            <div class="h5 mb-0 fw-bold">{{ $advancedStats['active_jobs'] ?? 0 }}</div>
                            <div class="small opacity-75">Active & Hiring</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                        <div class="bg-white bg-opacity-20 rounded p-2"><i class="bx bx-user-voice fs-4"></i></div>
                        <div>
                            <div class="h5 mb-0 fw-bold">{{ $advancedStats['upcoming_interviews'] ?? 0 }}</div>
                            <div class="small opacity-75">Upcoming Interviews</div>
                        </div>
                    </div>
                </div>
                 <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                        <div class="bg-info bg-opacity-25 rounded p-2"><i class="bx bx-file fs-4"></i></div>
                        <div>
                            <div class="h5 mb-0 fw-bold">{{ $advancedStats['total_applications'] ?? 0 }}</div>
                            <div class="small opacity-75">Total Applications</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content Area -->
        <div class="col-lg-9">
            
            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                     <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                                <input type="text" id="searchInput" class="form-control bg-light border-0" placeholder="Search vacancies...">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex gap-2 justify-content-md-end overflow-auto pb-1 pb-md-0">
                                <select id="statusFilter" class="form-select border-0 bg-light" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Pending Approval">Pending</option>
                                    <option value="Closed">Closed</option>
                                </select>
                                <select id="sortBy" class="form-select border-0 bg-light" style="width: auto;">
                                    <option value="newest">Newest</option>
                                    <option value="deadline">Deadline</option>
                                    <option value="applications">Applications</option>
                                </select>
                                <div class="btn-group bg-light rounded" role="group">
                                    <button type="button" class="btn btn-sm btn-light active" id="viewGrid"><i class="bx bx-grid-alt"></i></button>
                                    <button type="button" class="btn btn-sm btn-light" id="viewList"><i class="bx bx-list-ul"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Alert -->
            @if($canApproveJobs && $pendingApprovalJobs->isNotEmpty())
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert" style="background-color: #fffbeb; border-left: 4px solid #f59e0b !important;">
                <div class="bg-warning bg-opacity-25 rounded p-2 me-3">
                    <i class="bx bx-bell text-warning fs-4"></i>
                </div>
                <div>
                     <h6 class="alert-heading mb-1 fw-bold text-dark">Action Required</h6>
                    <p class="mb-0 text-muted small">You have <strong>{{ $pendingApprovalJobs->count() }}</strong> job vacancies waiting for your approval.</p>
                </div>
                <a href="#pendingApprovalSection" class="btn btn-sm btn-warning ms-auto">Review</a>
            </div>
            @endif

            <!-- Jobs Grid -->
            <div class="row g-4" id="jobsGrid" style="min-height: 400px;">
                <!-- Pending Jobs First -->
                 @if($pendingApprovalJobs->isNotEmpty())
                    <div class="col-12" id="pendingApprovalSection">
                        <h6 class="text-muted fw-bold mb-3 small text-uppercase ls-1">Pending Approval</h6>
                    </div>
                     @foreach($pendingApprovalJobs as $job)
                        <div class="col-12 col-md-6 col-xl-4 job-item" data-status="Pending Approval" data-title="{{ strtolower($job->job_title) }}">
                             <div class="job-card" style="border-left: 4px solid #f59e0b;">
                                <div class="job-card-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1">{{ $job->job_title }}</h5>
                                            <div class="text-muted small">{{ $job->creator ? $job->creator->name : 'Unknown' }}</div>
                                        </div>
                                         <span class="status-badge status-pending">Pending</span>
                                    </div>
                                </div>
                                <div class="job-card-body">
                                    <p class="text-muted small mb-0 flex-grow-1">
                                         {{ Str::limit($job->job_description ?? 'No description...', 100) }}
                                    </p>
                                    <div class="job-meta-list">
                                        <div class="job-meta-item">
                                            <i class="bx bx-calendar text-muted"></i>
                                            <span>Created: {{ $job->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="btn btn-sm btn-warning w-100 btn-review" data-id="{{ $job->id }}">
                                        <i class="bx bx-check-shield me-1"></i> Review & Approve
                                    </button>
                                </div>
                             </div>
                        </div>
                     @endforeach
                 @endif

                 @if($myPendingJobs->isNotEmpty())
                     <!-- My Pending Jobs -->
                     @foreach($myPendingJobs as $job)
                        <div class="col-12 col-md-6 col-xl-4 job-item" data-status="Pending Approval" data-title="{{ strtolower($job->job_title) }}">
                             <div class="job-card" style="border-left: 4px solid #3b82f6;">
                                <div class="job-card-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1">{{ $job->job_title }}</h5>
                                            <div class="text-muted small">My Draft</div>
                                        </div>
                                         <span class="status-badge status-pending" style="color:#2563eb; background:#eff6ff;">In Review</span>
                                    </div>
                                </div>
                                <div class="job-card-body">
                                    <p class="text-muted small mb-0 flex-grow-1">
                                         {{ Str::limit($job->job_description ?? 'No description...', 100) }}
                                    </p>
                                    <div class="job-meta-list">
                                        <div class="job-meta-item">
                                            <i class="bx bx-calendar text-muted"></i>
                                            <span>Deadline: {{ $job->application_deadline->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="btn btn-sm btn-outline-primary w-50 btn-edit-job" data-id="{{ $job->id }}"><i class="bx bx-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-outline-secondary w-50 btn-view-details" data-id="{{ $job->id }}"><i class="bx bx-show"></i> View</button>
                                </div>
                             </div>
                        </div>
                     @endforeach
                 @endif

                 <!-- Active & Others -->
                 <div class="col-12 mt-4">
                     <h6 class="text-muted fw-bold mb-3 small text-uppercase ls-1">Active Listings</h6>
                 </div>
                 @forelse($jobs as $job)
                    @if($job->status !== 'Pending Approval')
                        @php
                            $isUrgent = $job->application_deadline->diffInDays(now()) < 3 && $job->status === 'Active';
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4 job-item" data-status="{{ $job->status }}" data-title="{{ strtolower($job->job_title) }}">
                            <div class="job-card {{ $isUrgent ? 'border-danger' : '' }}">
                                <div class="job-card-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="fw-bold text-dark mb-0 text-truncate pe-2" style="max-width: 70%;" title="{{ $job->job_title }}">
                                            {{ $job->job_title }}
                                             @if($isUrgent) <i class="bx bxs-flame text-danger" title="Deadline Approaching"></i> @endif
                                        </h5>
                                        <span class="status-badge {{ $job->status === 'Active' ? 'status-active' : ($job->status === 'Rejected' ? 'status-rejected' : 'status-closed') }}">
                                            {{ $job->status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="job-card-body">
                                    <p class="text-muted small mb-0 flex-grow-1">
                                        {{ Str::limit($job->job_description ?? 'No details available.', 90) }}
                                    </p>
                                    
                                    <div class="job-meta-list">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <div class="job-meta-item">
                                                <i class="bx bx-calendar {{ $isUrgent ? 'text-danger fw-bold' : 'text-muted' }}"></i>
                                                <span class="{{ $isUrgent ? 'text-danger fw-bold' : '' }}">{{ $job->application_deadline->format('M d') }}</span>
                                            </div>
                                             <div class="job-meta-item">
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ $job->applications_count }} Applicants</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="btn btn-sm btn-outline-dark flex-grow-1 btn-view-details" data-id="{{ $job->id }}"><i class="bx bx-show"></i></button>
                                     @if($canManageApplications)
                                    <a href="{{ route('jobs.applications') }}?job_id={{ $job->id }}" class="btn btn-sm btn-primary flex-grow-1 opacity-90"><i class="bx bx-user-check"></i> Candidates</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-12 text-center py-5">
                       <img src="{{ asset('assets/img/illustrations/empty-box.png') }}" class="mb-3" style="width: 150px; opacity: 0.5;">
                        <h5 class="text-muted">No active job listings found</h5>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-3">
             <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom border-light">
                    <i class="bx bx-filter-alt me-2 text-primary"></i> Recruitment Funnel
                </div>
                <div class="card-body">
                     <div id="funnelChart" style="min-height: 250px;"></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                 <div class="card-header bg-white fw-bold py-3 border-bottom border-light">
                    <i class="bx bx-trending-up me-2 text-success"></i> Recent Activity
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush custom-scroll" style="max-height: 300px; overflow-y: auto;">
                        @if(isset($advancedStats['recent_applications']) && $advancedStats['recent_applications'] > 0)
                         <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div class="bg-primary bg-opacity-10 p-2 rounded text-primary"><i class="bx bx-file"></i></div>
                            <div>
                                <div class="small fw-bold">{{ $advancedStats['recent_applications'] }} New Applications</div>
                                <div class="text-xs text-muted">In the last 7 days</div>
                            </div>
                        </li>
                        @endif
                         <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div class="bg-success bg-opacity-10 p-2 rounded text-success"><i class="bx bx-check-circle"></i></div>
                            <div>
                                <div class="small fw-bold">{{ $advancedStats['hired'] ?? 0 }} Hired Candidates</div>
                                <div class="text-xs text-muted">Total hires this year</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Includes for Modals -->
@include('modules.hr.recruitment.modals.create-job')
@include('modules.hr.recruitment.modals.view-job')
@include('modules.hr.recruitment.modals.review-job')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    // --- Funnel Chart ---
    document.addEventListener("DOMContentLoaded", function() {
        var options = {
            series: [
                {
                    name: "Count",
                    data: [
                        {{ $advancedStats['total_applications'] ?? 0 }}, 
                        {{ $advancedStats['shortlisted'] ?? 0 }}, 
                        {{ $advancedStats['interviewing'] ?? 0 }}, 
                        {{ $advancedStats['offer_extended'] ?? 0 }},
                        {{ $advancedStats['hired'] ?? 0 }}
                    ]
                }
            ],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    distributed: true,
                    dataLabels: { position: 'bottom' },
                }
            },
            colors: ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#14532d'],
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: { colors: ['#fff'] },
                formatter: function (val, opt) {
                    return val
                },
                offsetX: 0,
            },
            xaxis: {
                categories: ['Applied', 'Shortlisted', 'Interview', 'Offer', 'Hired'],
            },
            legend: { show: false },
            tooltip: {
                theme: 'dark',
                y: { formatter: function (val) { return val + " Candidates" } }
            }
        };

        var chart = new ApexCharts(document.querySelector("#funnelChart"), options);
        chart.render();
    });

    // --- Filter Logic ---
    $(document).ready(function() {
        const jobsGrid = $('#jobsGrid');
        
        $('#searchInput').on('keyup', filterJobs);
        $('#statusFilter').on('change', filterJobs);
        $('#sortBy').on('change', sortJobs);
        
        // View Toggles
        $('#viewList').click(function() {
            $('.job-item').removeClass('col-xl-4 col-md-6').addClass('col-12');
            $(this).addClass('active');
            $('#viewGrid').removeClass('active');
        });
        
        $('#viewGrid').click(function() {
            $('.job-item').addClass('col-xl-4 col-md-6').removeClass('col-12');
            $(this).addClass('active');
            $('#viewList').removeClass('active');
        });

        function filterJobs() {
            const search = $('#searchInput').val().toLowerCase();
            const status = $('#statusFilter').val();
            
            $('.job-item').each(function() {
                const title = $(this).data('title');
                const jobStatus = $(this).data('status');
                
                const matchesSearch = !search || title.includes(search);
                const matchesStatus = !status || jobStatus === status;
                
                if(matchesSearch && matchesStatus) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function sortJobs() {
            // Implementation of sorting logic would go here, 
            // necessitating re-appending elements to the container.
            // Simplified for now as server-side sorting is often better for complex data.
        }
    });
</script>

{{-- Re-include the JS logic for modals from the original file, adapted for the new layout --}}
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const recruitmentUrl = '{{ route("recruitment.handle") }}';

    // Create job button
    $('#create-job-btn').on('click', function() {
        $('#jobForm')[0].reset();
        $('#jobModalTitle').text('New Job Vacancy');
        $('#jobAction').val('create_job');
        $('#jobId').val('');
        $('#jobSubmitBtn').text('Submit for Approval');
        $('#jobModal').modal('show');
    });

    // Auto-fill qualifications from Salary Structure
    $('#salaryStructureId').change(function() {
        const structId = $(this).val();
        if(structId) {
            $.ajax({
                type: 'POST',
                url: recruitmentUrl,
                data: { action: 'get_salary_structure_details', salary_structure_id: structId },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                dataType: 'json',
                success: function(response) {
                    if(response.success && response.qualifications) {
                        let quals = response.qualifications;
                        if(Array.isArray(quals)) quals = quals.join('\n');
                        if($('#qualifications').val()) {
                            if(confirm('Overwrite existing qualifications?')) $('#qualifications').val(quals);
                        } else {
                            $('#qualifications').val(quals);
                        }
                    }
                }
            });
        }
    });

    // View Details
    $(document).on('click', '.btn-view-details', function() {
        const jobId = $(this).data('id');
        $('#jobDetailsModal').modal('show');
        $('#jobDetailsContent').html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i></div>');
        
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details', job_id: jobId },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(res) {
                if(res.success) {
                    const d = res.details;
                    $('#jobDetailsContent').html(`
                        <h4 class="mb-3">${d.job_title} <span class="badge bg-secondary fs-6 align-middle ms-2">${d.status}</span></h4>
                        <div class="row g-3">
                            <div class="col-md-6"><strong>Deadline:</strong> ${d.application_deadline}</div>
                            <div class="col-md-6"><strong>Interview Mode:</strong> ${d.interview_mode}</div>
                            <div class="col-12"><div class="p-3 bg-light rounded">${d.job_description}</div></div>
                        </div>
                    `);
                }
            }
        });
    });

     // Review job
    $(document).on('click', '.btn-review', function() {
        const jobId = $(this).data('id');
         $('#reviewJobModal').modal('show');
         // ... Similar AJAX logic for review modal ...
         $('#reviewJobModal .btn-approve-from-modal').data('id', jobId);
         $('#reviewJobModal .btn-reject-from-modal').data('id', jobId);
    });
    
    // Approval Actions
    $('.btn-approve-from-modal').click(function() {
        const id = $(this).data('id');
        $.ajax({
            url: recruitmentUrl, type: 'POST',
            data: { action: 'approve_job', job_id: id },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(res) {
                if(res.success) { location.reload(); }
            }
        });
    });

    // Reject Actions
     $('.btn-reject-from-modal').click(function() {
        const id = $(this).data('id');
        const reason = prompt("Enter Rejection Reason:");
        if(reason) {
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { action: 'reject_job', job_id: id, reason: reason },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function(res) {
                    if(res.success) { location.reload(); }
                }
            });
        }
    });
    
    // Save Job
    $('#jobForm').submit(function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.ajax({
            url: recruitmentUrl, type: 'POST', data: data,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(res) {
                if(res.success) { location.reload(); }
                else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    });

});
</script>
@endpush
