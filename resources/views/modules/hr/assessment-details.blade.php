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
                            <a href="{{ route('modules.hr.assessments') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            @if($isAdmin || $isHR)
                            <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-outline-warning">
                                <i class="bx bx-edit me-1"></i>Edit Assessment
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Details -->
        <div class="col-lg-8">

            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-{{ $assessment->status === 'approved' ? 'success' : ($assessment->status === 'rejected' ? 'danger' : 'warning') }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-info-circle me-2"></i>Assessment Status
                        </h5>
                        <span class="badge bg-light text-dark fs-6">{{ ucfirst(str_replace('_', ' ', $assessment->status)) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bx bx-user me-2"></i>Employee Name:</strong><br>
                                <span class="ms-4">{{ $assessment->employee->name ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-building me-2"></i>Department:</strong><br>
                                <span class="ms-4">{{ $assessment->employee->primaryDepartment->name ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-envelope me-2"></i>Email:</strong><br>
                                <span class="ms-4">{{ $assessment->employee->email ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bx bx-calendar me-2"></i>Created:</strong><br>
                                <span class="ms-4">{{ $assessment->created_at->format('M j, Y g:i A') }}</span>
                            </p>
                            @if($assessment->hod_approved_at)
                            <p class="mb-2"><strong><i class="bx bx-check-circle me-2"></i>{{ $assessment->status === 'approved' ? 'Approved' : 'Rejected' }}:</strong><br>
                                <span class="ms-4">{{ $assessment->hod_approved_at->format('M j, Y g:i A') }}</span>
                                @if($assessment->hodApprover)
                                <br><small class="ms-4 text-muted">by {{ $assessment->hodApprover->name }}</small>
                                @endif
                            </p>
                            @endif
                            <p class="mb-2"><strong><i class="bx bx-percent me-2"></i>Contribution:</strong><br>
                                <span class="ms-4 badge bg-primary fs-6">{{ $assessment->contribution_percentage }}%</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Responsibility -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-target-lock me-2"></i>Main Responsibility</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">{{ $assessment->main_responsibility }}</h4>
                    @if($assessment->description)
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block mb-2">Description</small>
                        <p class="mb-0">{{ $assessment->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Performance Metrics ({{ $currentYear }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-primary">{{ $performanceData['total_reports'] }}</h3>
                                <small class="text-muted">Total Reports</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-success">{{ $performanceData['approved_reports'] }}</h3>
                                <small class="text-muted">Approved</small>
                                <br><small class="text-success">{{ $performanceData['approval_rate'] }}% Rate</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-warning">{{ $performanceData['pending_reports'] }}</h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="mb-0 text-danger">{{ $performanceData['rejected_reports'] }}</h3>
                                <small class="text-muted">Rejected</small>
                                <br><small class="text-danger">{{ $performanceData['rejection_rate'] }}% Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities and Reports - Advanced Design -->
            <div class="card border-0 shadow-lg mb-4" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-white fw-bold">
                                <i class="bx bx-list-ul me-2"></i>Activities & Progress Reports
                            </h5>
                            <small class="text-white-50">Comprehensive view of all activities and their progress</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light btn-sm" id="expandAllActivities">
                                <i class="bx bx-expand me-1"></i>Expand All
                            </button>
                            <button class="btn btn-light btn-sm" id="collapseAllActivities">
                                <i class="bx bx-collapse me-1"></i>Collapse All
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($assessment->activities->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="mb-3">
                            <i class="bx bx-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                        <h5 class="text-muted">No Activities Found</h5>
                        <p class="text-muted">This assessment doesn't have any activities yet.</p>
                    </div>
                    @else
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
                            <div class="card border-0 shadow-sm activity-item" style="border-radius: 12px; transition: all 0.3s ease;">
                                <div class="card-header bg-white border-bottom" style="border-radius: 12px 12px 0 0; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#activityCollapse{{ $activity->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="activity-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bx bx-task text-white fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark">{{ $activity->activity_name }}</h6>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <span class="badge bg-secondary">
                                                            <i class="bx bx-time me-1"></i>{{ ucfirst($activity->reporting_frequency) }}
                                                        </span>
                                                        <span class="badge bg-info">
                                                            <i class="bx bx-percent me-1"></i>{{ $activity->contribution_percentage }}% Contribution
                                                        </span>
                                                        @if($totalReports > 0)
                                                        <span class="badge bg-success">
                                                            <i class="bx bx-check-circle me-1"></i>{{ $approvalRate }}% Approval Rate
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end ms-3">
                                            <div class="mb-2">
                                                <span class="badge bg-primary fs-6">{{ $totalReports }} Report{{ $totalReports !== 1 ? 's' : '' }}</span>
                                            </div>
                                            <div class="d-flex gap-1 justify-content-end">
                                                @if($approvedCount > 0)
                                                <span class="badge bg-success">{{ $approvedCount }} Approved</span>
                                                @endif
                                                @if($pendingCount > 0)
                                                <span class="badge bg-warning">{{ $pendingCount }} Pending</span>
                                                @endif
                                                @if($rejectedCount > 0)
                                                <span class="badge bg-danger">{{ $rejectedCount }} Rejected</span>
                                                @endif
                                            </div>
                                            <i class="bx bx-chevron-down activity-chevron mt-2" style="font-size: 1.5rem; color: #667eea; transition: transform 0.3s;"></i>
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
                                        @endif

                                        @if($activity->progressReports->isEmpty())
                                        <div class="alert alert-info border-0 shadow-sm" style="border-radius: 10px;">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-info-circle fs-4 me-3"></i>
                                                <div>
                                                    <strong>No Progress Reports</strong>
                                                    <p class="mb-0">No progress reports have been submitted for this activity yet.</p>
                                                    @if($isOwn && $assessment->status === 'approved')
                                                    <a href="{{ route('assessments.progress.create', $activity->id) }}" class="btn btn-primary btn-sm mt-2">
                                                        <i class="bx bx-plus me-1"></i>Submit First Report
                                                    </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <!-- Reports Table -->
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0" style="border-radius: 10px; overflow: hidden;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="border-top-left-radius: 10px;">Report Date</th>
                                                        <th>Status</th>
                                                        <th>Progress Summary</th>
                                                        <th>Approved By</th>
                                                        <th>Date/Time</th>
                                                        <th style="border-top-right-radius: 10px;" class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($reports as $report)
                                                    <tr class="report-row" style="transition: background-color 0.2s;">
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="date-icon me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem;">
                                                                    {{ $report->report_date ? $report->report_date->format('d') : 'N/A' }}
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold">{{ $report->report_date ? $report->report_date->format('M Y') : 'N/A' }}</div>
                                                                    <small class="text-muted">{{ $report->report_date ? $report->report_date->format('l') : '' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statusConfig = [
                                                                    'approved' => ['bg' => 'success', 'icon' => 'bx-check-circle', 'text' => 'Approved'],
                                                                    'pending_approval' => ['bg' => 'warning', 'icon' => 'bx-time-five', 'text' => 'Pending'],
                                                                    'rejected' => ['bg' => 'danger', 'icon' => 'bx-x-circle', 'text' => 'Rejected']
                                                                ];
                                                                $status = $statusConfig[$report->status] ?? $statusConfig['pending_approval'];
                                                            @endphp
                                                            <span class="badge bg-{{ $status['bg'] }} px-3 py-2" style="font-size: 0.85rem;">
                                                                <i class="bx {{ $status['icon'] }} me-1"></i>{{ $status['text'] }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="progress-text-container" style="max-width: 300px;">
                                                                <p class="mb-0 text-dark fw-medium">{{ Str::limit($report->progress_text, 80) }}</p>
                                                                @if(strlen($report->progress_text) > 80)
                                                                <button class="btn btn-link btn-sm p-0 mt-1 view-full-text" data-text="{{ $report->progress_text }}" style="font-size: 0.8rem;">
                                                                    <i class="bx bx-show me-1"></i>View Full Text
                                                                </button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($report->hodApprover)
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                                    {{ substr($report->hodApprover->name, 0, 1) }}
                                                                </div>
                                                                <span class="text-dark">{{ $report->hodApprover->name }}</span>
                                                            </div>
                                                            @else
                                                            <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($report->hod_approved_at)
                                                            <div class="text-dark">{{ $report->hod_approved_at->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $report->hod_approved_at->format('H:i A') }}</small>
                                                            @else
                                                            <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary view-report-details" data-report-id="{{ $report->id }}" title="View Details">
                                                                    <i class="bx bx-show"></i>
                                                                </button>
                                                                @if($isHOD || $isHR || $isAdmin)
                                                                @if($report->status === 'pending_approval')
                                                                <button class="btn btn-outline-success approve-report-btn" data-report-id="{{ $report->id }}" title="Approve">
                                                                    <i class="bx bx-check"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger reject-report-btn" data-report-id="{{ $report->id }}" title="Reject">
                                                                    <i class="bx bx-x"></i>
                                                                </button>
                                                                @endif
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
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

            <!-- Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-time me-2"></i>Timeline</h5>
                </div>
        <div class="card-body">
            @if(empty($timeline))
            <div class="text-center text-muted py-4">
                <p>No timeline events available.</p>
            </div>
            @else
            <div class="timeline">
                @foreach($timeline as $event)
                <div class="timeline-item mb-4">
                    <div class="d-flex">
                        <div class="timeline-marker me-3">
                            <div class="bg-{{ $event['color'] }} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bx {{ $event['icon'] }} text-white"></i>
                            </div>
                        </div>
                        <div class="timeline-content flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0">{{ $event['event'] }}</h6>
                                <small class="text-muted">{{ $event['date']->format('M d, Y H:i') }}</small>
                            </div>
                            <p class="text-muted small mb-1"><strong>By:</strong> {{ $event['user'] }}</p>
                            @if($event['description'])
                            <p class="mb-0 small">{{ $event['description'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

        </div>

        <!-- Right Column - Quick Actions & Info -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            @if($isHOD || $isHR || $isAdmin)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($assessment->status === 'pending_hod')
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('assessments.approve', $assessment->id) }}" class="btn btn-success">
                            <i class="bx bx-check me-2"></i>Approve Assessment
                        </a>
                        <a href="{{ route('assessments.reject', $assessment->id) }}" class="btn btn-danger">
                            <i class="bx bx-x me-2"></i>Reject Assessment
                        </a>
                    </div>
                    @endif
                    @if($isAdmin || $isHR)
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-2"></i>Edit Assessment
                        </a>
                    </div>
                    @endif
                    @if($isAdmin)
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger btn-delete-assessment" 
                                data-id="{{ $assessment->id }}"
                                data-name="{{ $assessment->main_responsibility }}">
                            <i class="bx bx-trash me-2"></i>Delete Assessment
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Assessment Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Assessment Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Activities Count:</strong> {{ $assessment->activities->count() }}</p>
                    <p class="mb-2"><strong>Contribution:</strong> {{ $assessment->contribution_percentage }}%</p>
                    <p class="mb-0"><strong>Status:</strong> 
                        <span class="badge bg-{{ $assessment->status === 'approved' ? 'success' : ($assessment->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                        </span>
                    </p>
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
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Expand/Collapse All Activities
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
            url: `/assessments/progress-reports/${reportId}/approve`,
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
                    url: `/assessments/${id}`,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', 'Assessment deleted successfully', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("modules.hr.assessments") }}';
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

