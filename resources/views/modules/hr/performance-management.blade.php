@extends('layouts.app')

@section('title', 'Performance Management')

@section('content')
<style>
    :root {
        --pm-primary: #940000;
        --pm-primary-dark: #7a0000;
        --pm-bg-soft: rgba(148, 0, 0, 0.05);
        --pm-border: rgba(148, 0, 0, 0.1);
    }
    
    .bg-pm-primary { background-color: var(--pm-primary) !important; }
    .text-pm-primary { color: var(--pm-primary) !important; }
    .btn-pm-primary { background-color: var(--pm-primary) !important; border-color: var(--pm-primary) !important; color: #fff !important; }
    .btn-pm-primary:hover { background-color: var(--pm-primary-dark) !important; border-color: var(--pm-primary-dark) !important; color: #fff !important; }
    
    .card-pm { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 0.75rem; transition: transform 0.2s; }
    .card-pm:hover { transform: translateY(-2px); }
    
    .nav-pills .nav-link.active { background-color: var(--pm-primary) !important; }
    .status-badge { padding: 0.35em 0.65em; font-size: 0.75em; font-weight: 700; border-radius: 50rem; }
    
    .glass-header {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 1rem;
        padding: 2.5rem;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Action Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-header shadow-lg text-white">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h2 class="fw-bold text-white mb-2">Performance Management</h2>
                        <p class="mb-4 opacity-75">Track performance objectives, manage progress reports, and align organizational goals.</p>
                        <div class="d-flex flex-wrap gap-2">
                            @if(Auth::user()->hasAnyRole(['CEO', 'General Manager', 'System Admin']))
                                <a href="{{ route('modules.performance_management_module.set-org-goal') }}" class="btn btn-outline-light px-4">
                                    <i class="bx bx-bullseye me-1"></i> Configure Goals
                                </a>
                            @endif
                            @if(Auth::user()->hasAnyRole(['HOD', 'CEO', 'General Manager']))
                                <a href="{{ route('modules.performance_management_module.delegate-activity') }}" class="btn btn-white px-4">
                                    <i class="bx bx-task me-1"></i> Delegate Task
                                </a>
                            @endif
                            <a href="{{ route('modules.performance_management_module.daily-report') }}" class="btn btn-light px-4 shadow-sm">
                                <i class="bx bx-plus-circle me-1"></i> Submit Report
                            </a>
                        </div>
                    </div>
                    <div class="col-md-5 d-none d-md-block text-end">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" alt="Performance" style="max-height: 140px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-pm p-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar bg-pm-soft p-2 rounded-3">
                            <i class="bx bx-rocket fs-3 text-pm-primary"></i>
                        </div>
                        <h6 class="mb-0 text-muted">Org Progress</h6>
                    </div>
                    <h3 class="fw-bold mb-1">{{ number_format($orgProgress, 1) }}%</h3>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-pm-primary" role="progressbar" style="width: {{ $orgProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-pm p-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar bg-label-info p-2 rounded-3">
                            <i class="bx bx-check-double fs-3 text-info"></i>
                        </div>
                        <h6 class="mb-0 text-muted">Completion</h6>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $completedCount }}/{{ $totalTasks }}</h3>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalTasks > 0 ? ($completedCount / $totalTasks) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-pm p-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar bg-label-success p-2 rounded-3">
                            <i class="bx bx-medal fs-3 text-success"></i>
                        </div>
                        <h6 class="mb-0 text-muted">Quality Score</h6>
                    </div>
                    <h3 class="fw-bold mb-1">{{ number_format($overallQualScore, 1) }}%</h3>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $overallQualScore }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-pm p-1">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar bg-label-danger p-2 rounded-3">
                            <i class="bx bx-shield-quarter fs-3 text-danger"></i>
                        </div>
                        <h6 class="mb-0 text-muted">Active Risks</h6>
                    </div>
                    <h3 class="fw-bold mb-1 text-{{ $criticalTasksCount > 0 ? 'danger' : 'success' }}">
                        {{ $criticalTasksCount }} Issues
                    </h3>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $criticalTasksCount > 0 ? 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dashboard Workspace -->
        <div class="col-lg-8">
            <div class="card card-pm h-100">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <h5 class="fw-bold mb-0">Performance Workspace</h5>
                        <ul class="nav nav-pills nav-pills-rounded" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active btn-sm" data-bs-toggle="tab" data-bs-target="#my-tasks">My Tasks</button>
                            </li>
                            @if(Auth::user()->hasAnyRole(['HOD', 'CEO', 'Director', 'General Manager', 'System Admin']))
                            <li class="nav-item">
                                <button class="nav-link btn-sm" data-bs-toggle="tab" data-bs-target="#team-oversight">Approvals</button>
                            </li>
                            @endif
                            <li class="nav-item">
                                <button class="nav-link btn-sm" data-bs-toggle="tab" data-bs-target="#goal-cascade">Strategic Grid</button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="tab-content">
                        <!-- My Tasks -->
                        <div class="tab-pane fade show active" id="my-tasks">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">Activity Name</th>
                                            <th class="border-0">Assigned By</th>
                                            <th class="border-0" style="width: 180px;">Progress</th>
                                            <th class="border-0 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($myActivities ?? [] as $activity)
                                        <tr>
                                            <td>
                                                <div class="fw-bold mb-1">{{ $activity->activity_name }}</div>
                                                <span class="badge rounded-pill bg-label-{{ $activity->status == 'completed' ? 'success' : ($activity->status == 'in_progress' ? 'info' : 'warning') }} small">
                                                    {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $activity->assignedBy->photo ? asset('storage/' . $activity->assignedBy->photo) : asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="28">
                                                    <span class="small">{{ $activity->assignedBy->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress w-100" style="height: 5px;">
                                                        <div class="progress-bar bg-pm-primary" style="width: {{ $activity->current_progress ?? 0 }}%"></div>
                                                    </div>
                                                    <small class="fw-bold">{{ $activity->current_progress ?? 0 }}%</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-icon btn-label-pm" title="Add Progress" onclick="prepareReport({{ $activity->id }})">
                                                    <i class="bx bx-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <p class="text-muted mb-0">No active tasks assigned to you.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Approvals -->
                        <div class="tab-pane fade" id="team-oversight">
                            <div class="list-group list-group-flush">
                                @forelse($pendingApprovals ?? [] as $report)
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <img src="{{ $report->activity->assessment->employee->photo ? asset('storage/' . $report->activity->assessment->employee->photo) : asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="38">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between mb-1">
                                                <h6 class="fw-bold mb-0 small text-uppercase">{{ $report->activity->assessment->employee->name }}</h6>
                                                <small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="mb-2">
                                                <span class="badge bg-label-pm btn-sm px-2 py-1 small">{{ $report->activity->activity_name }}</span>
                                            </div>
                                            <p class="mb-3 small text-secondary">{{ Str::limit($report->progress_text, 120) }}</p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-pm-primary px-3" onclick="approveReport({{ $report->id }})">Approve</button>
                                                <button class="btn btn-sm btn-outline-danger px-3" onclick="rejectReport({{ $report->id }})">Reject</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5">
                                    <p class="text-muted mb-0">No reports waiting for approval.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Strategy Grid -->
                        <div class="tab-pane fade" id="goal-cascade">
                            @foreach($orgGoals ?? [] as $goal)
                            <div class="mb-4 pb-3 border-bottom border-light">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="bg-pm-soft p-2 rounded">
                                        <i class="bx bx-compass text-pm-primary"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">{{ $goal->title }}</h6>
                                </div>
                                <div class="ps-4 ms-2 border-start border-light">
                                    @foreach($goal->children as $deptGoal)
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-bold small">{{ $deptGoal->title }}</span>
                                            <span class="badge bg-label-secondary x-small">{{ $deptGoal->department->name ?? 'Dept' }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($deptGoal->assessments as $ast)
                                            <span class="badge bg-pm-soft text-pm-primary small border-0 font-light">
                                                {{ $ast->employee->name }} ({{ $ast->contribution_percentage }}%)
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

        <!-- Sidebar Insights -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <!-- Score Card -->
            <div class="card card-pm mb-4 bg-pm-primary text-white border-0 shadow">
                <div class="card-body text-center p-4">
                    <h6 class="text-white-50 small mb-2 text-uppercase fw-bold">My Performance Index</h6>
                    <h1 class="display-4 fw-bold text-white mb-2">{{ number_format($compositeScore, 1) }}</h1>
                    <div class="badge bg-white text-pm-primary px-3 py-2 rounded-pill shadow-sm">
                        @if($compositeScore >= 90) ELITE PERFORMANCE
                        @elseif($compositeScore >= 75) EXCEEDS EXPECTATIONS
                        @elseif($compositeScore >= 50) MEETING STANDARDS
                        @else ACTION REQUIRED
                        @endif
                    </div>
                    
                    <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small opacity-75">Quantitative (40%)</span>
                            <span class="fw-bold">{{ number_format($quantScore, 1) }}%</span>
                        </div>
                        <div class="progress mb-3 shadow-sm" style="height: 4px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-white" style="width: {{ $quantScore }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small opacity-75">Qualitative (60%)</span>
                            <span class="fw-bold">{{ number_format($qualScore, 1) }}%</span>
                        </div>
                        <div class="progress shadow-sm" style="height: 4px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-white" style="width: {{ $qualScore }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="card card-pm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0">High Performers</h6>
                </div>
                <div class="card-body px-4">
                    <div class="d-flex flex-column gap-3">
                        @foreach($topPerformers ?? [] as $performer)
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $performer->photo ? asset('storage/' . $performer->photo) : asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="30">
                                <div>
                                    <h6 class="small fw-bold mb-0">{{ $performer->name }}</h6>
                                    <span class="text-muted x-small">{{ $performer->department->name ?? 'Staff' }}</span>
                                </div>
                            </div>
                            <span class="badge bg-label-pm small">{{ $performer->performance_score ?? '95.0' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Reminders -->
            <div class="card card-pm bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bx bx-bell text-warning"></i> Quick Alerts
                    </h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li class="small d-flex gap-2">
                            <div class="dot bg-warning mt-1" style="min-width: 6px; height: 6px; border-radius: 50%;"></div>
                            2 Pending reports require your review
                        </li>
                        <li class="small d-flex gap-2 text-muted">
                            <div class="dot bg-secondary mt-1" style="min-width: 6px; height: 6px; border-radius: 50%;"></div>
                            Next evaluation cycle starts in 2 weeks
                        </li>
                    </ul>
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
    .bg-pm-soft { background-color: rgba(148, 0, 0, 0.05); }
    .bg-label-pm { background-color: rgba(148, 0, 0, 0.1); color: #940000; }
    .btn-label-pm { background-color: rgba(148, 0, 0, 0.1); color: #940000; border: none; }
    .btn-label-pm:hover { background-color: #940000; color: white; }
    
    .x-small { font-size: 10px; }
    .font-light { font-weight: 400; }
    .nav-pills-rounded .nav-link { border-radius: 50px; padding: 0.5rem 1.25rem; font-weight: 600; font-size: 0.85rem; }
    
    .btn-white { background: #fff; color: #940000; border: none; }
    .btn-white:hover { background: #eee; color: #7a0000; }
</style>
@endpush

@push('scripts')
<script>
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
