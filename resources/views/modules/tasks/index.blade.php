@extends('layouts.app')

@section('title', 'Task Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    .task-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }
    .task-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-task me-2"></i>Task Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive task management system with activity tracking and progress monitoring
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @if($canCreateTask ?? false)
                                <a href="{{ route('modules.tasks.create') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-plus me-2"></i>Create Task
                                </a>
                            @endif
                            <a href="{{ route('modules.tasks.analytics') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-bar-chart me-2"></i>Analytics
                            </a>
                            @if($isManager)
                                <a href="{{ route('modules.tasks.categories') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-category me-2"></i>Categories
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        @if($isManager)
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-task fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Tasks</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $dashboardStats['total'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-trending-up me-1"></i>All Tasks
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
                            <i class="bx bx-loader-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">In Progress</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ $dashboardStats['in_progress'] ?? 0 }}</h3>
                            <small class="text-info">
                                <i class="bx bx-time me-1"></i>Active
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
                            <h3 class="mb-0 fw-bold text-success">{{ $dashboardStats['completed'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-check me-1"></i>Done
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="bx bx-error-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Overdue</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $dashboardStats['overdue'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="bx bx-time-five me-1"></i>Late
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-user-check fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">My Tasks</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $dashboardStats['total_tasks'] ?? 0 }}</h3>
                            <small class="text-primary">
                                <i class="bx bx-list-ul me-1"></i>Assigned
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="bx bx-time fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Pending</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $dashboardStats['pending'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bx bx-hourglass me-1"></i>Awaiting
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="bx bx-error-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Overdue</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $dashboardStats['overdue'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="bx bx-time-five me-1"></i>Late
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-bottom">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-bolt-circle me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($canCreateTask ?? false)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.create') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                        <i class="bx bx-plus fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Create Task</h6>
                                    <small class="text-muted">Schedule new task</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.analytics') }}" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                        <i class="bx bx-bar-chart fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Analytics</h6>
                                    <small class="text-muted">View statistics</small>
                                </div>
                            </a>
                        </div>
                        
                        @if($isManager)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.categories') }}" class="card border-secondary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-secondary">
                                        <i class="bx bx-category fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Categories</h6>
                                    <small class="text-muted">Manage categories</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.index') }}?status=in_progress" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                        <i class="bx bx-loader-circle fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">In Progress</h6>
                                    <small class="text-muted">View active tasks</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.index') }}?status=completed" class="card border-success h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <i class="bx bx-check-double fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Completed</h6>
                                    <small class="text-muted">View completed</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.reports') }}" class="card border-warning h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <i class="bx bx-file-blank fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Reports</h6>
                                    <small class="text-muted">View all reports</small>
                                </div>
                            </a>
                        </div>
                        
                        @if($isManager)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.tasks.reports.pending-approval') }}" class="card border-danger h-100 text-decoration-none hover-lift position-relative">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                        <i class="bx bx-time fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Pending Approval</h6>
                                    <small class="text-muted">Review reports</small>
                                    @php
                                        $pendingCount = \App\Models\ActivityReport::where('status', 'Pending')->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingCount }}</span>
                                    @endif
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-list-ul me-2"></i>Tasks
                        </h5>
                        <div class="d-flex gap-2">
                            <!-- Filters -->
                            <form method="GET" action="{{ route('modules.tasks.index') }}" class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                </select>
                                @if($isManager)
                                <select name="priority" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                                    <option value="">All Priority</option>
                                    <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                                    <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Critical" {{ request('priority') == 'Critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                <select name="leader" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                                    <option value="">All Leaders</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('leader') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @endif
                                <div class="input-group" style="max-width: 250px;">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search', '') }}" onkeypress="if(event.key==='Enter') this.form.submit()">
                                </div>
                            </form>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                                <i class="bx bx-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tasks-container">
                        @if($mainTasks->count() > 0)
                            <div class="row g-3">
                                @foreach($mainTasks as $task)
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="task-card card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-1">
                                                            <a href="{{ route('modules.tasks.show', $task->id) }}" class="text-decoration-none">
                                                                {{ $task->name }}
                                                            </a>
                                                        </h6>
                                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'info' : ($task->status == 'delayed' ? 'danger' : 'warning')) }}">
                                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                        </span>
                                                        <span class="badge bg-{{ $task->priority == 'Critical' ? 'danger' : ($task->priority == 'High' ? 'warning' : ($task->priority == 'Low' ? 'secondary' : 'primary')) }} ms-1">
                                                            {{ $task->priority }}
                                                        </span>
                                                    </div>
                                                    @if($isManager || $task->team_leader_id == Auth::id())
                                                        <a href="{{ route('modules.tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                                @if($task->description)
                                                    <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                                                @endif
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bx bx-user me-1"></i>{{ $task->teamLeader->name ?? 'N/A' }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="bx bx-calendar me-1"></i>{{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('M d, Y') : 'No deadline' }}
                                                    </small>
                                                </div>
                                                @if($task->activities->count() > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="bx bx-list-check me-1"></i>{{ $task->activities->count() }} Activities
                                                        </small>
                                                    </div>
                                                @endif
                                                @php
                                                    $hasPerformanceLink = false;
                                                    $performanceLinks = [];
                                                    if ($task->organizational_goal_id) {
                                                        $hasPerformanceLink = true;
                                                        $goal = \App\Models\OrganizationalGoal::find($task->organizational_goal_id);
                                                        if ($goal) {
                                                            $performanceLinks[] = ['type' => 'goal', 'name' => $goal->title];
                                                        }
                                                    }
                                                    if ($task->assessment_id) {
                                                        $hasPerformanceLink = true;
                                                        $assessment = \App\Models\Assessment::find($task->assessment_id);
                                                        if ($assessment) {
                                                            $performanceLinks[] = ['type' => 'assessment', 'name' => $assessment->main_responsibility];
                                                        }
                                                    }
                                                    $linkedActivities = $task->activities()->whereNotNull('assessment_activity_id')->count();
                                                    if ($linkedActivities > 0) {
                                                        $hasPerformanceLink = true;
                                                    }
                                                @endphp
                                                @if($hasPerformanceLink)
                                                    <div class="mt-2 pt-2 border-top">
                                                        <small class="text-success">
                                                            <i class="bx bx-target-lock me-1"></i>Linked to Performance
                                                        </small>
                                                        @if($task->link_type && $task->link_type !== 'none')
                                                            <br><small class="text-muted">
                                                                Type: <span class="badge bg-info">{{ ucfirst($task->link_type) }}</span>
                                                                @if($task->performance_weight)
                                                                    | Weight: {{ $task->performance_weight }}%
                                                                @endif
                                                            </small>
                                                        @endif
                                                        @if($linkedActivities > 0)
                                                            <br><small class="text-muted">
                                                                <i class="bx bx-link me-1"></i>{{ $linkedActivities }} activity(ies) linked
                                                            </small>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-task fs-1 text-muted mb-3"></i>
                                <p class="text-muted">No tasks found. @if($canCreateTask ?? false)<a href="{{ route('modules.tasks.create') }}">Create your first task</a>@endif</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Task management scripts can be added here
</script>
@endpush
