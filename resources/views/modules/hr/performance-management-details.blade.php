@extends('layouts.app')

@section('title', 'Assessment Details - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bx bx-target-lock me-2"></i>Assessment Details
                            </h4>
                            <p class="mb-0 text-muted">Assessment ID: <strong>#{{ $assessment->id }}</strong></p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.performance_management_module') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            @if($isAdmin || $isHR)
                            <a href="{{ route('performance_management_module.edit', $assessment->id) }}" class="btn btn-outline-warning">
                                <i class="bx bx-edit me-1"></i>Edit Assessment
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #696cff 0%, #435971 100%);">
                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-white text-primary me-2">ID: #{{ $assessment->id }}</span>
                                <span class="badge bg-{{ $assessment->status === 'approved' ? 'success' : ($assessment->status === 'rejected' ? 'danger' : 'warning') }} bg-opacity-75 border-0">
                                    {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                                </span>
                            </div>
                            <h2 class="text-white mb-1">{{ $assessment->main_responsibility }}</h2>
                            <p class="text-white-50 mb-0 d-flex align-items-center">
                                <i class="bx bx-user me-2"></i> {{ $assessment->employee->name ?? 'N/A' }} 
                                <span class="mx-2">•</span> 
                                <i class="bx bx-building me-2"></i> {{ $assessment->employee->primaryDepartment->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($isAdmin || $isHR)
                            <a href="{{ route('performance_management_module.edit', $assessment->id) }}" class="btn btn-warning shadow-sm">
                                <i class="bx bx-edit me-1"></i> Edit
                            </a>
                            @endif
                            <a href="{{ route('modules.hr.performance_management_module') }}" class="btn btn-outline-white shadow-sm" style="border-color: rgba(255,255,255,0.5); color: white;">
                                <i class="bx bx-arrow-back me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Decorative Circle -->
                <div class="position-absolute top-0 end-0 rounded-circle bg-white opacity-10" style="width: 200px; height: 200px; margin-top: -50px; margin-right: -50px;"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Column -->
        <div class="col-lg-4 order-lg-2 mb-4">
            <!-- Progress Card -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h5 class="mb-0 card-title">Performance Overview</h5>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3" style="width: 150px; height: 150px;">
                        <canvas id="performanceChart"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h4 class="mb-0 fw-bold text-primary" id="approvalRateText">{{ $performanceData['approval_rate'] }}%</h4>
                            <small class="text-muted">Approval</small>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <h5 class="mb-0 text-success">{{ $performanceData['approved_reports'] }}</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Approved</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <h5 class="mb-0 text-warning">{{ $performanceData['pending_reports'] }}</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <h5 class="mb-0 text-danger">{{ $performanceData['rejected_reports'] }}</h5>
                                <small class="text-muted" style="font-size: 0.7rem;">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alignment Card -->
            @if($assessment->organizationalGoal)
            <div class="card shadow-sm mb-4 border-0 bg-label-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm rounded bg-white text-primary d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="bx bx-bullseye fs-4"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-primary">Strategic Alignment</h6>
                    </div>
                    <h5 class="mb-2 text-dark">{{ $assessment->organizationalGoal->title }}</h5>
                    <p class="mb-0 text-muted small">{{ Str::limit($assessment->organizationalGoal->description, 100) }}</p>
                </div>
            </div>
            @endif

            <!-- Info List -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 pt-0">
                            <span class="text-muted"><i class="bx bx-calendar me-2"></i>Created Date</span>
                            <span class="fw-medium">{{ $assessment->created_at->format('M j, Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="bx bx-pie-chart-alt-2 me-2"></i>Contribution</span>
                            <span class="fw-medium">{{ $assessment->contribution_percentage }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="bx bx-list-check me-2"></i>Activities</span>
                            <span class="fw-medium">{{ $assessment->activities->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Quick Actions -->
            @if(($isHOD || $isHR || $isAdmin) && $assessment->status === 'pending_hod')
            <div class="card shadow-sm mb-4 border-0">
                 <div class="card-body">
                    <h6 class="mb-3">Approval Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('performance_management_module.approve', $assessment->id) }}" class="btn btn-success">
                            <i class="bx bx-check me-2"></i>Approve Assessment
                        </a>
                        <a href="{{ route('performance_management_module.reject', $assessment->id) }}" class="btn btn-danger">
                            <i class="bx bx-x me-2"></i>Reject Assessment
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content Column -->
        <div class="col-lg-8 order-lg-1">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills mb-4" id="assessmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                        <i class="bx bx-detail me-1"></i> Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities" type="button" role="tab">
                        <i class="bx bx-list-ul me-1"></i> Activities & Reports
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">
                        <i class="bx bx-time-five me-1"></i> Timeline
                    </button>
                </li>
            </ul>

            <div class="tab-content p-0" id="assessmentTabsContent">
                
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">Description</h5>
                            @if($assessment->description)
                                <p class="text-secondary leading-relaxed">{{ $assessment->description }}</p>
                            @else
                                <p class="text-muted fst-italic">No detailed description provided.</p>
                            @endif

                            <h5 class="card-title text-primary mt-4 mb-3">At a Glance</h5>
                            <div class="row g-3">
                                @forelse($assessment->activities->take(4) as $activity)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 border rounded h-100 hover-shadow bg-light">
                                        <div class="avatar-sm bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3 text-primary">
                                            <i class="bx bx-task"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-truncate" style="max-width: 200px;">{{ $activity->activity_name }}</h6>
                                            <div class="d-flex gap-2 text-muted small">
                                                <span><i class="bx bx-refresh"></i> {{ ucfirst($activity->reporting_frequency) }}</span>
                                                <span><i class="bx bx-pie-chart-alt"></i> {{ $activity->contribution_percentage }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12 text-muted">No activities defined yet.</div>
                                @endforelse
                            </div>
                            
                            <div class="mt-4 text-center">
                                <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('activities-tab').click()">
                                    View All Activities <i class="bx bx-right-arrow-alt ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activities Tab -->
                <div class="tab-pane fade" id="activities" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Detailed Activities</h5>
                        <div class="d-flex gap-2">
                             <button class="btn btn-light btn-sm" id="expandAllActivities">
                                <i class="bx bx-expand me-1"></i> Expand
                            </button>
                            <button class="btn btn-light btn-sm" id="collapseAllActivities">
                                <i class="bx bx-collapse me-1"></i> Collapse
                            </button>
                        </div>
                    </div>
                    
                    @if($assessment->activities->isEmpty())
                        <div class="card border-0 shadow-sm p-5 text-center">
                            <i class="bx bx-list-ul fs-1 text-muted opacity-50 mb-3"></i>
                            <h5>No Activities</h5>
                            <p class="text-muted">There are no activities listed for this assessment.</p>
                        </div>
                    @else
                        <!-- Refined Activities Container -->
                        <div class="activities-container">
                            @foreach($assessment->activities as $index => $activity)
                            @php
                                $reports = $activity->progressReports;
                                $approvedCount = $reports->where('status', 'approved')->count();
                                $pendingCount = $reports->where('status', 'pending_approval')->count();
                                $totalReports = $reports->count();
                            @endphp
                            <div class="card border-0 shadow-sm mb-3 activity-item overflow-hidden" data-activity-id="{{ $activity->id }}" data-name="{{ strtolower($activity->activity_name) }}">
                                <div class="card-header bg-white border-bottom-0 p-3 pt-3 pb-2">
                                    <div class="d-flex align-items-start flex-wrap gap-2">
                                        <div class="avatar bg-label-primary rounded d-flex align-items-center justify-content-center flex-shrink-0 me-2" style="width: 2.5rem; height: 2.5rem;">
                                            <span class="fw-bold fs-5">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 200px;">
                                            <h6 class="mb-1 text-dark fw-bold text-break">{{ $activity->activity_name }}</h6>
                                            @if($activity->description)
                                                <p class="mb-2 text-muted small" style="font-size: 0.85rem; line-height: 1.4; word-wrap: break-word; white-space: normal;">{{ $activity->description }}</p>
                                            @endif
                                            <div class="d-flex flex-wrap gap-3 align-items-center text-muted small">
                                                <span class="badge bg-label-info"><i class="bx bx-time-five me-1"></i>{{ ucfirst($activity->reporting_frequency) }}</span>
                                                <span class="badge bg-label-warning"><i class="bx bx-pie-chart-alt-2 me-1"></i>{{ $activity->contribution_percentage }}% Contrib.</span>
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-auto d-flex flex-column align-items-end justify-content-center">
                                            <span class="badge bg-secondary mb-1">{{ $totalReports }} Reports</span>
                                            <button class="btn btn-sm btn-link p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#act-{{ $activity->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="act-{{ $activity->id }}">
                                                <i class="bx bx-chevron-down text-muted fs-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="act-{{ $activity->id }}" class="collapse {{ $index === 0 ? 'show' : '' }} border-top border-light">
                                    <div class="card-body bg-light bg-opacity-25 p-3">
                                        
                                        @if($totalReports > 0)
                                            <div class="table-responsive bg-white rounded shadow-sm border">
                                                <table class="table table-sm table-hover mb-0 font-size-13">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th>Summary</th>
                                                            <th class="text-end">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($reports as $report)
                                                        <tr>
                                                            <td>{{ $report->report_date ? $report->report_date->format('M d') : '-' }}</td>
                                                            <td>
                                                                @if($report->status == 'approved') <span class="badge bg-label-success fs-tiny">Approved</span>
                                                                @elseif($report->status == 'rejected') <span class="badge bg-label-danger fs-tiny">Rejected</span>
                                                                @else <span class="badge bg-label-warning fs-tiny">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-truncate" style="max-width: 250px;">{{ Str::limit($report->progress_text, 50) }}</td>
                                                            <td class="text-end">
                                                                <button class="btn btn-xs btn-icon btn-outline-primary view-report-details" data-report-id="{{ $report->id }}">
                                                                    <i class="bx bx-show"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-3 text-muted small">
                                                No progress reports submitted yet.
                                                @if($isOwn && $assessment->status === 'approved')
                                                <div class="mt-2">
                                                    <a href="{{ route('performance_management_module.progress.create', $activity->id) }}" class="btn btn-primary btn-sm">
                                                        <i class="bx bx-plus"></i> Submit Report
                                                    </a>
                                                </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Timeline Tab -->
                <div class="tab-pane fade" id="timeline" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                             @if(empty($timeline))
                            <div class="text-center text-muted py-4">
                                <p>No timeline events available.</p>
                            </div>
                            @else
                            <ul class="timeline-list list-unstyled position-relative ps-4">
                                @foreach($timeline as $event)
                                <li class="timeline-item position-relative mb-4">
                                    <div class="position-absolute start-0 top-0 translate-middle rounded-circle bg-{{ $event['color'] }} border border-white" style="width: 12px; height: 12px; margin-left: -1px;"></div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="mb-0 fw-bold">{{ $event['event'] }}</h6>
                                        <small class="text-muted">{{ $event['date']->format('M d, H:i') }}</small>
                                    </div>
                                    <p class="mb-0 small text-muted">by <span class="fw-medium text-dark">{{ $event['user'] }}</span></p>
                                    @if($event['description'])
                                    <div class="bg-light p-2 rounded mt-2 small text-secondary">
                                        {{ $event['description'] }}
                                    </div>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                            <!-- Vertical Line CSS -->
                            <style>
                                .timeline-list::before {
                                    content: '';
                                    position: absolute;
                                    top: 5px;
                                    bottom: 0;
                                    left: 20px;
                                    width: 2px;
                                    background: #e9ecef;
                                }
                            </style>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline-item {
    position: relative;
}
.timeline-marker {
    flex-shrink: 0;
}
.timeline-content {
    padding-top: 5px;
}
.modal {
    z-index: 9999 !important;
}
.modal-backdrop {
    z-index: 9998 !important;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Initialize Performance Chart
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [
                        {{ $performanceData['approved_reports'] }},
                        {{ $performanceData['pending_reports'] }},
                        {{ $performanceData['rejected_reports'] }}
                    ],
                    backgroundColor: ['#71dd37', '#ffc107', '#ff3e1d'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) { label += context.parsed; }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Expand/Collapse All Activities
    $('#expandAllActivities').on('click', function() {
        $('.activity-item .collapse').collapse('show');
    });
    
    $('#collapseAllActivities').on('click', function() {
        $('.activity-item .collapse').collapse('hide');
    });
    
    // Rotate chevron icon on collapse/expand
    $('.activity-item .collapse').on('show.bs.collapse', function() {
        $(this).closest('.activity-item').find('.btn-link i').removeClass('bx-chevron-down').addClass('bx-chevron-up');
    });
    
    $('.activity-item .collapse').on('hide.bs.collapse', function() {
        $(this).closest('.activity-item').find('.btn-link i').removeClass('bx-chevron-up').addClass('bx-chevron-down');
    });
    
    // Search Activities
    $('#searchActivities').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.activity-card').each(function() {
            const activityName = $(this).data('name') || '';
            if (activityName.includes(searchTerm)) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
    });
    
    // Filter by Frequency
    $('#filterFrequency').on('change', function() {
        const frequency = $(this).val().toLowerCase();
        $('.activity-card').each(function() {
            const activityFrequency = $(this).data('frequency') || '';
            if (!frequency || activityFrequency === frequency) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
    });
    
    // View Full Text
    $(document).on('click', '.view-full-text', function() {
        const text = $(this).data('text');
        Swal.fire({
            title: 'Full Progress Text',
            html: '<div class="text-start p-3" style="max-height: 400px; overflow-y: auto;">' + 
                  '<p class="mb-0">' + escapeHtml(text) + '</p></div>',
            width: '700px',
            confirmButtonText: 'Close',
            confirmButtonColor: '#667eea'
        });
    });
    
    // View Report Details
    $(document).on('click', '.view-report-details', function() {
        const reportId = $(this).data('report-id');
        // Fetch report details via AJAX and show in modal
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching report details',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // You can implement AJAX call here to fetch full report details
        // For now, showing a placeholder
        setTimeout(() => {
            Swal.fire({
                title: 'Report Details',
                html: '<p>Full report details will be displayed here.</p>',
                confirmButtonText: 'Close',
                confirmButtonColor: '#667eea'
            });
        }, 500);
    });
    
    // Approve/Reject Report
    function handleReportDecision(reportId, decision) {
        const actionText = decision === 'approve' ? 'approve' : 'reject';
        const actionTitle = decision === 'approve' ? 'Approve Progress Report' : 'Reject Progress Report';
        const actionIcon = decision === 'approve' ? 'question' : 'warning';
        const confirmButtonText = decision === 'approve' ? 'Yes, Approve' : 'Yes, Reject';
        const confirmButtonColor = decision === 'approve' ? '#28a745' : '#dc3545';
        
        if (decision === 'approve') {
            Swal.fire({
                title: actionTitle,
                text: 'Are you sure you want to approve this progress report?',
                icon: actionIcon,
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancel',
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitReportDecision(reportId, decision, '');
                }
            });
        } else {
            Swal.fire({
                title: actionTitle,
                html: '<div class="mb-3"><label class="form-label">Enter rejection comments (optional):</label><textarea id="swal-comments" class="form-control" rows="4" placeholder="Enter rejection comments here..."></textarea></div>',
                icon: actionIcon,
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancel',
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#6c757d',
                focusConfirm: false,
                preConfirm: () => {
                    return document.getElementById('swal-comments').value || '';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitReportDecision(reportId, decision, result.value || '');
                }
            });
        }
    }
    
    function submitReportDecision(reportId, decision, comments) {
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: `/performance-management-module/progress-reports/${reportId}/approve`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ decision: decision, comments: comments }),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Report ' + decision + 'd successfully',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to process request',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }
    
    $(document).on('click', '.approve-report-btn', function() {
        const reportId = $(this).data('report-id');
        handleReportDecision(reportId, 'approve');
    });
    
    $(document).on('click', '.reject-report-btn', function() {
        const reportId = $(this).data('report-id');
        handleReportDecision(reportId, 'reject');
    });
    
    // Helper function to escape HTML
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
    
    // Delete Assessment
    $('.btn-delete-assessment').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Delete assessment "${name}"? This will also delete all related activities and progress reports. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: `/performance-management-module/${id}`,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', 'Assessment deleted successfully', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("modules.hr.performance_management_module") }}';
                            }, 1500);
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete assessment', 'error');
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

