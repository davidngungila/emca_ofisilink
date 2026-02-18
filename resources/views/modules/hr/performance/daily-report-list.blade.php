@extends('layouts.app')

@section('title', 'My Daily Progress Reporting')

@section('content')
<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #940000 0%, #600000 100%) !important; }
    .text-primary { color: #940000 !important; }
    .btn-primary { background-color: #940000 !important; border-color: #940000 !important; }
    .btn-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; }
    .card-activity:hover { transform: translateY(-5px); transition: all 0.3s ease; box-shadow: 0 10px 20px rgba(148,0,0,0.1) !important; border: 1px solid #940000 !important; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Performance /</span> Daily Progress Reporting
            </h4>
            <p class="text-muted mb-0">Select an active activity to submit your daily achievement.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('modules.performance_management_module') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row g-4">
        @forelse($myActivities ?? [] as $activity)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 card-activity">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar bg-soft-primary p-2 rounded-3 text-primary">
                            <i class="bx bx-task fs-3"></i>
                        </div>
                        <span class="badge bg-soft-info text-info">{{ ucfirst($activity->reporting_frequency) }}</span>
                    </div>
                    
                    <h5 class="fw-bold mb-2">{{ $activity->activity_name }}</h5>
                    <p class="text-muted small flex-grow-1">{{ Str::limit($activity->description, 100) }}</p>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Current Progress</small>
                            <small class="fw-bold text-primary">{{ $activity->current_progress ?? 0 }}%</small>
                        </div>
                        <div class="progress mb-4" style="height: 6px;">
                            <div class="progress-bar" style="width: {{ $activity->current_progress ?? 0 }}%; background-color: #940000;"></div>
                        </div>
                        
                        <a href="{{ route('modules.performance_management_module.progress.create', $activity->id) }}" class="btn btn-primary w-100 rounded-pill">
                            <i class="bx bx-plus-circle me-1"></i>Report Today's Progress
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm rounded-4 p-5">
                <i class="bx bx-info-circle display-1 text-muted mb-4"></i>
                <h4 class="fw-bold">No Active Activities</h4>
                <p class="text-muted">You don't have any activities assigned to you at the moment.</p>
                <div class="mt-3">
                    <a href="{{ route('modules.performance_management_module') }}" class="btn btn-primary px-4">Return to Dashboard</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
