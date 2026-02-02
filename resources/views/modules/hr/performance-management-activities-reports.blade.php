@extends('layouts.app')

@section('title', 'Activities & Progress Reports - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-list-ul me-2"></i>Activities & Progress Reports
                            </h3>
                            <p class="mb-0 text-white-50">
                                <strong>Assessment:</strong> {{ $assessment->main_responsibility }} | 
                                <strong>Employee:</strong> {{ $assessment->employee->name ?? 'N/A' }} |
                                <strong>Status:</strong> 
                                <span class="badge bg-light text-dark">
                                    {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                                </span>
                            </p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <a href="{{ route('performance_management_module.show', $assessment->id) }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Assessment
                            </a>
                            <a href="{{ route('modules.hr.performance_management_module') }}" class="btn btn-outline-light">
                                <i class="bx bx-list-ul me-1"></i>All Assessments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    @if(isset($performanceData))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <h3 class="mb-0 text-success fw-bold">{{ $performanceData['total_reports'] }}</h3>
                    <small class="text-muted">Total Reports</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <h3 class="mb-0 text-success fw-bold">{{ $performanceData['approved_reports'] }}</h3>
                    <small class="text-muted">Approved ({{ $performanceData['approval_rate'] }}%)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <h3 class="mb-0 text-warning fw-bold">{{ $performanceData['pending_reports'] }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <h3 class="mb-0 text-danger fw-bold">{{ $performanceData['rejected_reports'] }}</h3>
                    <small class="text-muted">Rejected ({{ $performanceData['rejection_rate'] }}%)</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="bx bx-search"></i>
                </span>
                <input type="text" class="form-control" id="searchActivities" placeholder="Search activities...">
            </div>
        </div>
        <div class="col-md-6">
            <select class="form-select" id="filterFrequency">
                <option value="">All Frequencies</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
            </select>
        </div>
    </div>

    <!-- Activities & Reports -->
    <div class="card border-0 shadow-lg mb-4" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bx bx-list-ul me-2 text-primary"></i>All Activities & Their Progress Reports
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" id="expandAllActivities">
                        <i class="bx bx-expand me-1"></i>Expand All
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="collapseAllActivities">
                        <i class="bx bx-collapse me-1"></i>Collapse All
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            @if($assessment->activities->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bx bx-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                <h5 class="text-muted mt-3">No Activities Found</h5>
                <p class="text-muted">This assessment doesn't have any activities yet.</p>
            </div>
            @else
            <div class="activities-container">
                @foreach($assessment->activities as $index => $activity)
                @php
                    $reports = $activity->progressReports;
                    $approvedCount = $reports->where('status', 'approved')->count();
                    $pendingCount = $reports->where('status', 'pending_approval')->count();
                    $rejectedCount = $reports->where('status', 'rejected')->count();
                    $totalReports = $reports->count();
                    $approvalRate = $totalReports > 0 ? round(($approvedCount / $totalReports) * 100, 1) : 0;
                @endphp
                <div class="activity-card mb-4" data-activity-id="{{ $activity->id }}" data-frequency="{{ strtolower($activity->reporting_frequency) }}" data-name="{{ strtolower($activity->activity_name) }}">
                            <div class="card border-0 shadow-sm activity-item overflow-hidden" style="border-radius: 12px; transition: all 0.3s ease;">
                                <div class="card-header bg-white border-bottom-0 p-3 pt-3 pb-2 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#activityCollapse{{ $activity->id }}">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <div class="avatar bg-label-primary rounded d-flex align-items-center justify-content-center flex-shrink-0 me-2" style="width: 2.5rem; height: 2.5rem;">
                                            <span class="fw-bold fs-5">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 200px;">
                                            <h6 class="mb-1 text-dark fw-bold text-break">{{ $activity->activity_name }}</h6>
                                            <div class="d-flex flex-wrap gap-3 align-items-center text-muted small">
                                                <span class="badge bg-label-info"><i class="bx bx-time-five me-1"></i>{{ ucfirst($activity->reporting_frequency) }}</span>
                                                <span class="badge bg-label-warning"><i class="bx bx-pie-chart-alt-2 me-1"></i>{{ $activity->contribution_percentage }}% Contrib.</span>
                                                @if($totalReports > 0)
                                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>{{ $approvalRate }}% Approval</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-auto d-flex flex-column align-items-end justify-content-center">
                                            <span class="badge bg-secondary mb-1">{{ $totalReports }} Reports</span>
                                            <i class="bx bx-chevron-down text-muted fs-4 activity-chevron"></i>
                                        </div>
                                    </div>
                                </div>
                        <div id="activityCollapse{{ $activity->id }}" class="collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent=".activities-container">
                            <div class="card-body p-4">
                                @if($activity->description)
                                <div class="alert alert-light border mb-4" style="border-radius: 10px;">
                                    <div class="d-flex align-items-start">
                                        <i class="bx bx-info-circle text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong class="d-block mb-1">Activity Description</strong>
                                            <p class="mb-0 text-muted">{{ $activity->description }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($totalReports > 0)
                                <!-- Progress Statistics -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="stat-card text-center p-3 border rounded" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 10px !important;">
                                            <h3 class="mb-0 text-success fw-bold">{{ $approvedCount }}</h3>
                                            <small class="text-muted">Approved</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card text-center p-3 border rounded" style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); border-radius: 10px !important;">
                                            <h3 class="mb-0 text-warning fw-bold">{{ $pendingCount }}</h3>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card text-center p-3 border rounded" style="background: linear-gradient(135deg, #fab1a0 0%, #e17055 100%); border-radius: 10px !important;">
                                            <h3 class="mb-0 text-danger fw-bold">{{ $rejectedCount }}</h3>
                                            <small class="text-muted">Rejected</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card text-center p-3 border rounded" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 10px !important;">
                                            <h3 class="mb-0 text-primary fw-bold">{{ $approvalRate }}%</h3>
                                            <small class="text-muted">Success Rate</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reports with Indentation -->
                                <div class="reports-container">
                                    <h6 class="mb-3 fw-bold text-dark">
                                        <i class="bx bx-file me-2"></i>Progress Reports (Hierarchical View)
                                    </h6>
                                    @foreach($reports as $reportIndex => $report)
                                    <div class="report-item mb-3" style="margin-left: {{ ($reportIndex * 20) }}px; padding-left: 20px; border-left: 3px solid #667eea; position: relative;">
                                        <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="date-icon me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem;">
                                                                {{ $report->report_date ? $report->report_date->format('d') : 'N/A' }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark">{{ $report->report_date ? $report->report_date->format('M d, Y') : 'N/A' }}</div>
                                                                <small class="text-muted">{{ $report->report_date ? $report->report_date->format('l, H:i A') : '' }}</small>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $statusConfig = [
                                                                'approved' => ['bg' => 'success', 'icon' => 'bx-check-circle', 'text' => 'Approved'],
                                                                'pending_approval' => ['bg' => 'warning', 'icon' => 'bx-time-five', 'text' => 'Pending Approval'],
                                                                'rejected' => ['bg' => 'danger', 'icon' => 'bx-x-circle', 'text' => 'Rejected']
                                                            ];
                                                            $status = $statusConfig[$report->status] ?? $statusConfig['pending_approval'];
                                                        @endphp
                                                        <div class="mb-2">
                                                            <span class="badge bg-{{ $status['bg'] }} px-3 py-2" style="font-size: 0.85rem;">
                                                                <i class="bx {{ $status['icon'] }} me-1"></i>{{ $status['text'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        @if($isHOD || $isHR || $isAdmin)
                                                        @if($report->status === 'pending_approval')
                                                        <div class="btn-group btn-group-sm mb-2">
                                                            <button class="btn btn-outline-success approve-report-btn" data-report-id="{{ $report->id }}" title="Approve">
                                                                <i class="bx bx-check"></i> Approve
                                                            </button>
                                                            <button class="btn btn-outline-danger reject-report-btn" data-report-id="{{ $report->id }}" title="Reject">
                                                                <i class="bx bx-x"></i> Reject
                                                            </button>
                                                        </div>
                                                        @endif
                                                        @endif
                                                        <button class="btn btn-outline-primary btn-sm view-report-details" data-report-id="{{ $report->id }}" title="View Details">
                                                            <i class="bx bx-show"></i> View
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Progress Text with Indentation -->
                                                <div class="progress-text-container mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                                                    <div class="d-flex align-items-start">
                                                        <i class="bx bx-file-blank text-primary me-2 mt-1"></i>
                                                        <div class="flex-grow-1">
                                                            <strong class="d-block mb-2 text-dark">Progress Report:</strong>
                                                            <p class="mb-0 text-dark" style="line-height: 1.6; white-space: pre-wrap;">{{ $report->progress_text }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($report->hodApprover || $report->hod_approved_at)
                                                <div class="mt-3 pt-3 border-top">
                                                    <div class="row">
                                                        @if($report->hodApprover)
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                                    {{ substr($report->hodApprover->name, 0, 1) }}
                                                                </div>
                                                                <div>
                                                                    <small class="text-muted d-block">Approved By</small>
                                                                    <strong class="text-dark">{{ $report->hodApprover->name }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if($report->hod_approved_at)
                                                        <div class="col-md-6">
                                                            <small class="text-muted d-block">Approved At</small>
                                                            <strong class="text-dark">{{ $report->hod_approved_at->format('M d, Y H:i A') }}</strong>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @if($report->hod_comments)
                                                    <div class="mt-2 pt-2 border-top">
                                                        <small class="text-muted d-block mb-1">Comments:</small>
                                                        <p class="mb-0 text-dark" style="font-style: italic;">{{ $report->hod_comments }}</p>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="alert alert-info border-0 shadow-sm" style="border-radius: 10px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-info-circle fs-4 me-3"></i>
                                        <div>
                                            <strong>No Progress Reports</strong>
                                            <p class="mb-0">No progress reports have been submitted for this activity yet.</p>
                                            @if($isOwn && $assessment->status === 'approved')
                                            <a href="{{ route('performance_management_module.progress.create', $activity->id) }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bx bx-plus me-1"></i>Submit First Report
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.activity-card {
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.activity-item {
    transition: all 0.3s ease;
}

.activity-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.activity-chevron {
    transition: transform 0.3s ease;
}

.activity-item .card-header[aria-expanded="true"] .activity-chevron {
    transform: rotate(180deg);
}

.report-item {
    position: relative;
    transition: all 0.3s ease;
}

.report-item:hover {
    transform: translateX(5px);
}

.report-item::before {
    content: '';
    position: absolute;
    left: -3px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.activity-card.hidden {
    display: none;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Expand/Collapse All
    $('#expandAllActivities').on('click', function() {
        $('.activity-item .collapse').collapse('show');
    });
    
    $('#collapseAllActivities').on('click', function() {
        $('.activity-item .collapse').collapse('hide');
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
    
    // View Report Details
    $(document).on('click', '.view-report-details', function() {
        const reportId = $(this).data('report-id');
        // You can implement AJAX call here to fetch full report details
        Swal.fire({
            title: 'Report Details',
            html: '<p>Full report details will be displayed here.</p>',
            confirmButtonText: 'Close',
            confirmButtonColor: '#667eea'
        });
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
});
</script>
@endpush

