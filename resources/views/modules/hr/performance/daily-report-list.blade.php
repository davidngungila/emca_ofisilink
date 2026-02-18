@extends('layouts.app')

@section('title', 'Select Task to Report')

@section('content')
<style>
    :root {
        --pm-primary: #940000;
    }
    .card-pm { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 0.75rem; transition: transform 0.2s; }
    .card-pm:hover { transform: translateY(-4px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); }
    .btn-pm-primary { background-color: var(--pm-primary); border-color: var(--pm-primary); color: #fff; }
    .btn-pm-primary:hover { background-color: #7a0000; color: #fff; }
    .bg-pm-soft { background-color: rgba(148, 0, 0, 0.05); color: #940000; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h4 class="fw-bold mb-1">Select Activity</h4>
            <p class="text-muted mb-0 small">Choose a task to submit your progress report.</p>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <a href="{{ route('modules.performance_management_module') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>Back to Overview
            </a>
        </div>
    </div>

    <div class="row g-4">
        @forelse($myActivities ?? [] as $activity)
        <div class="col-md-6 col-lg-4">
            <div class="card card-pm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar bg-pm-soft p-2 rounded-3 text-pm-primary">
                            <i class="bx bx-task fs-4"></i>
                        </div>
                        <span class="badge bg-label-secondary small" style="text-transform: capitalize;">{{ $activity->reporting_frequency }}</span>
                    </div>
                    
                    <h6 class="fw-bold mb-2">{{ $activity->activity_name }}</h6>
                    <p class="text-muted small flex-grow-1 mb-3">{{ Str::limit($activity->description, 80) }}</p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted tiny">Current Progress</small>
                            <small class="fw-bold text-pm-primary">{{ $activity->current_progress ?? 0 }}%</small>
                        </div>
                        <div class="progress mb-4" style="height: 4px;">
                            <div class="progress-bar" style="width: {{ $activity->current_progress ?? 0 }}%; background-color: var(--pm-primary);"></div>
                        </div>
                        
                        <a href="{{ route('modules.performance_management_module.progress.create', $activity->id) }}" class="btn btn-pm-primary w-100 rounded-pill btn-sm py-2">
                            Add Progress Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card card-pm p-5 text-center">
                <i class="bx bx-info-circle display-4 text-muted mb-3"></i>
                <h5 class="fw-bold">No Active Tasks</h5>
                <p class="text-muted small">You don't have any activities assigned to your performance assessment yet.</p>
                <div class="mt-2">
                    <a href="{{ route('modules.performance_management_module') }}" class="btn btn-pm-primary px-4 btn-sm">Return Home</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
