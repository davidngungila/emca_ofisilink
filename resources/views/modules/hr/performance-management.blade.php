@extends('layouts.app')

@section('title', 'Advanced Performance Management System')

@section('content')
<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #940000 0%, #600000 100%) !important; }
    .text-primary { color: #940000 !important; }
    .bg-primary { background-color: #940000 !important; }
    .btn-primary { background-color: #940000 !important; border-color: #940000 !important; }
    .btn-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; }
    .nav-pills .nav-link.active { background-color: #940000 !important; color: #fff !important; }
    .bg-soft-primary { background-color: rgba(148, 0, 0, 0.1) !important; }
    .border-primary { border-color: #940000 !important; }
    .badge.bg-label-primary { background-color: rgba(148, 0, 0, 0.1); color: #940000; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Premium Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-gradient-primary rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #940000 0%, #600000 100%);">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8 text-white">
                            <h2 class="display-6 fw-bold mb-2">Performance Management System</h2>
                            <p class="fs-5 opacity-75 mb-4">Mfumo wa Kisasa wa Usimamizi wa Utendaji - Driving organizational excellence through accountability.</p>
                            <div class="d-flex flex-wrap gap-3">
                                @if(Auth::user()->hasAnyRole(['CEO', 'General Manager', 'System Admin']))
                                    <a href="{{ route('modules.performance_management_module.set-org-goal') }}" class="btn btn-glass btn-lg">
                                        <i class="bx bx-bullseye me-2"></i>Set Org Goal
                                    </a>
                                @endif
                                @if(Auth::user()->hasAnyRole(['HOD', 'CEO', 'General Manager']))
                                    <a href="{{ route('modules.performance_management_module.delegate-activity') }}" class="btn btn-white btn-lg">
                                        <i class="bx bx-task me-2"></i>Delegate Activity
                                    </a>
                                @endif
                                <a href="{{ route('modules.performance_management_module.daily-report') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-plus-circle me-2"></i>Daily Report
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-block text-end">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" alt="Performance" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar bg-soft-primary p-2 rounded-3">
                            <i class="bx bx-target-lock fs-3 text-primary"></i>
                        </div>
                        <span class="badge bg-soft-success text-success">Live Data</span>
                    </div>
                    <h6 class="text-muted mb-1">Org Progress</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($orgProgress, 1) }}%</h3>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $orgProgress }}%; background-color: #940000;" aria-valuenow="{{ $orgProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar bg-soft-info p-2 rounded-3">
                            <i class="bx bx-list-check fs-3 text-info"></i>
                        </div>
                        <span class="badge bg-soft-warning text-warning">Pending {{ ($pendingApprovals ?? collect())->count() }}</span>
                    </div>
                    <h6 class="text-muted mb-1">Tasks Completed</h6>
                    <h3 class="fw-bold mb-0">{{ $completedCount }}/{{ $totalTasks }}</h3>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalTasks > 0 ? ($completedCount / $totalTasks) * 100 : 0 }}%" aria-valuenow="{{ $totalTasks > 0 ? ($completedCount / $totalTasks) * 100 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar bg-soft-success p-2 rounded-3">
                            <i class="bx bx-trending-up fs-3 text-success"></i>
                        </div>
                        <span class="badge bg-soft-info text-info">Overall Avg</span>
                    </div>
                    <h6 class="text-muted mb-1">Qualitative Score</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($overallQualScore, 1) }}%</h3>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $overallQualScore }}%" aria-valuenow="{{ $overallQualScore }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar bg-soft-danger p-2 rounded-3">
                            <i class="bx bx-error-circle fs-3 text-danger"></i>
                        </div>
                        <span class="badge bg-soft-danger text-danger">Critical {{ $criticalTasksCount }}</span>
                    </div>
                    <h6 class="text-muted mb-1">Risk Assessment</h6>
                    <h3 class="fw-bold mb-0">{{ $criticalTasksCount > 0 ? 'HIGH' : 'LOW' }}</h3>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $criticalTasksCount > 0 ? 15 : 0 }}%" aria-valuenow="{{ $criticalTasksCount > 0 ? 15 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Left Column: Navigation & Goals -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">Performance Overview</h5>
                        <ul class="nav nav-pills bg-soft-light p-1 rounded-3" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-2 px-3 fw-medium" data-bs-toggle="tab" data-bs-target="#tab-my-performance">My Tasks</button>
                            </li>
                            @if(Auth::user()->hasAnyRole(['HOD', 'CEO', 'Director', 'General Manager', 'System Admin']))
                            <li class="nav-item">
                                <button class="nav-link py-2 px-3 fw-medium" data-bs-toggle="tab" data-bs-target="#tab-team-oversight">Team Oversight</button>
                            </li>
                            @endif
                            <li class="nav-item">
                                <button class="nav-link py-2 px-3 fw-medium" data-bs-toggle="tab" data-bs-target="#tab-goals-cascade">Goal Cascade</button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- My Performance Tab -->
                        <div class="tab-pane fade show active" id="tab-my-performance">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border-top-0">
                                    <thead class="text-muted small text-uppercase">
                                        <tr>
                                            <th>Activity & Status</th>
                                            <th>Assigned By</th>
                                            <th>Daily Progress</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($myActivities ?? [] as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="ms-0">
                                                        <h6 class="fw-bold mb-1">{{ $activity->activity_name }}</h6>
                                                        <span class="badge bg-soft-{{ $activity->status == 'completed' ? 'success' : ($activity->status == 'in_progress' ? 'info' : 'warning') }} status-dot">
                                                            {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $activity->assignedBy->photo ? asset('storage/' . $activity->assignedBy->photo) : asset('assets/img/avatars/1.png') }}" class="avatar avatar-sm rounded-circle me-2">
                                                    <span class="small">{{ $activity->assignedBy->name }}</span>
                                                </div>
                                            </td>
                                            <td style="min-width: 150px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress w-100" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" style="width: {{ $activity->current_progress ?? 0 }}%"></div>
                                                    </div>
                                                    <span class="small fw-bold">{{ $activity->current_progress ?? 0 }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-icon btn-sm rounded-circle" style="background-color: rgba(148, 0, 0, 0.1); color: #940000;" title="Report Progress" onclick="prepareReport({{ $activity->id }})">
                                                    <i class="bx bx-plus-circle"></i>
                                                </button>
                                                <button class="btn btn-icon btn-sm rounded-circle ms-1" style="background-color: rgba(107, 114, 128, 0.1); color: #6b7280;" title="View Details">
                                                    <i class="bx bx-show-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bx bx-info-circle display-4 mb-2"></i>
                                                    <p>No active activities found for you.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Team Oversight Tab -->
                        <div class="tab-pane fade" id="tab-team-oversight">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Pending Approvals</h6>
                                <span class="badge bg-label-danger">{{ ($pendingApprovals ?? collect())->count() }} Reports</span>
                            </div>
                            <div class="list-group list-group-flush border-top">
                                @forelse($pendingApprovals ?? [] as $report)
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <img src="{{ $report->activity->assessment->employee->photo ? asset('storage/' . $report->activity->assessment->employee->photo) : asset('assets/img/avatars/1.png') }}" class="avatar rounded-circle">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between mb-1">
                                                <h6 class="fw-bold mb-0">{{ $report->activity->assessment->employee->name }}</h6>
                                                <small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-2 small text-muted"><strong>Activity:</strong> {{ $report->activity->activity_name }}</p>
                                            <p class="mb-3 small">{{ Str::limit($report->progress_text, 100) }}</p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-success px-3" onclick="approveReport({{ $report->id }})">Approve</button>
                                                <button class="btn btn-sm btn-soft-danger px-3" onclick="rejectReport({{ $report->id }})">Reject</button>
                                                <button class="btn btn-sm btn-soft-info px-3" onclick="requestChanges({{ $report->id }})">Edit Req.</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5">
                                    <p class="text-muted">No pending approvals found for your team.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Goal Cascade Tab -->
                        <div class="tab-pane fade" id="tab-goals-cascade">
                            <div class="goal-tree">
                                @foreach($orgGoals ?? [] as $goal)
                                <div class="goal-item mb-4 pb-4 border-bottom">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="avatar bg-soft-primary p-2 rounded-circle">
                                            <i class="bx bx-crown text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0">{{ $goal->title }}</h5>
                                            <span class="badge bg-label-primary px-2 py-1">Organizational Level</span>
                                        </div>
                                    </div>
                                    <div class="ms-5">
                                        @foreach($goal->children as $deptGoal)
                                        <div class="dept-goal mb-3 ps-3 border-start border-primary border-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-1">{{ $deptGoal->title }}</h6>
                                                <span class="badge bg-label-info">{{ $deptGoal->department->name ?? 'Dept.' }}</span>
                                            </div>
                                            <p class="small text-muted mb-2">{{ $deptGoal->description }}</p>
                                            <!-- Activities linked to this -->
                                            <div class="staff-goals d-flex flex-wrap gap-2 mt-2">
                                                @foreach($deptGoal->assessments as $ast)
                                                <span class="badge bg-soft-light text-dark border d-flex align-items-center gap-1">
                                                    <img src="{{ $ast->employee->photo ? asset('storage/' . $ast->employee->photo) : asset('assets/img/avatars/1.png') }}" class="avatar avatar-xs rounded-circle" style="width: 18px; height: 18px;">
                                                    {{ $ast->employee->name }}: {{ $ast->contribution_percentage }}%
                                                </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Personal Stats & Insights -->
        <div class="col-lg-4">
            <!-- Composite Score Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 text-white text-center" style="background-color: #940000;">
                        <h6 class="text-white opacity-75 mb-2">My Composite Score</h6>
                        <h2 class="display-5 fw-bold text-white mb-0">{{ number_format($compositeScore, 1) }}</h2>
                        <span class="badge bg-white mt-2" style="color: #940000;">
                            @if($compositeScore >= 90) Excellent Rank
                            @elseif($compositeScore >= 75) Good Rank
                            @elseif($compositeScore >= 50) Average Rank
                            @else Improvement Needed
                            @endif
                        </span>
                    </div>
                    <div class="p-4 bg-white">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Quantitative (40%)</span>
                            <span class="fw-bold small">{{ number_format($quantScore, 1) }}%</span>
                        </div>
                        <div class="progress mb-4" style="height: 4px;">
                            <div class="progress-bar" style="width: {{ $quantScore }}%; background-color: #940000;"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Qualitative (60%)</span>
                            <span class="fw-bold small">{{ number_format($qualScore, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" style="width: {{ $qualScore }}%; background-color: #600000;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0">Top Performers</h6>
                </div>
                <div class="card-body px-4">
                    <div class="d-flex flex-column gap-3">
                        @foreach($topPerformers ?? [] as $performer)
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $performer->photo ? asset('storage/' . $performer->photo) : asset('assets/img/avatars/1.png') }}" class="avatar rounded-circle">
                                <div>
                                    <h6 class="small fw-bold mb-0">{{ $performer->name }}</h6>
                                    <span class="text-muted tiny">{{ $performer->department->name ?? 'Staff' }}</span>
                                </div>
                            </div>
                            <span class="fw-bold text-primary">{{ $performer->performance_score ?? '95.0' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Reminders -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-soft-warning">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Quick Reminders</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-2 small">
                            <i class="bx bx-bell text-warning"></i> 2 Reports pending for 3 days
                        </div>
                        <div class="d-flex align-items-center gap-2 small">
                            <i class="bx bx-time text-warning"></i> Quarterly targets end in 14 days
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('modules.hr.partials.performance-modals')

@endsection

@push('styles')
<style>
    .bg-soft-primary { background-color: rgba(99, 102, 241, 0.1); }
    .bg-soft-info { background-color: rgba(59, 130, 246, 0.1); }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .bg-soft-light { background-color: rgba(243, 244, 246, 0.7); }
    
    .status-dot { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; }
    .tiny { font-size: 10px; }
    .btn-glass { 
        background: rgba(255, 255, 255, 0.2); 
        backdrop-filter: blur(10px); 
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }
    .btn-glass:hover { background: rgba(255, 255, 255, 0.3); color: white; }
    .btn-white { background: white; color: #3b82f6; border: none; }
    .btn-white:hover { background: #f8fafc; color: #2563eb; }
    
    .goal-tree .goal-item:last-child { border-bottom: none !important; }
    .avatar-xs { width: 24px; height: 24px; }
    
    .btn-soft-primary { background-color: rgba(99, 102, 241, 0.1); color: #6366f1; border: none; }
    .btn-soft-primary:hover { background-color: #6366f1; color: white; }

    /* Glassmorphism utility */
    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@push('scripts')
<script>
    // Placeholder JS for modal controls
    function prepareReport(activityId) {
        window.location.href = "{{ route('modules.performance_management_module') }}/activities/" + activityId + "/progress/create";
    }

    function approveReport(reportId) {
        $('#approveReportId').val(reportId);
        $('#approveReportModal').modal('show');
    }

    function rejectReport(reportId) {
        $('#rejectReportId').val(reportId);
        $('#rejectReportModal').modal('show');
    }

    function requestChanges(reportId) {
        $('#requestChangesReportId').val(reportId);
        $('#requestChangesModal').modal('show');
    }
</script>
@endpush
