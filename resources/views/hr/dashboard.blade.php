@extends('layouts.app')

@section('title', 'HR Officer Dashboard')

@push('styles')
<style>
    .bg-custom-primary { background-color: #940000 !important; color: white !important; }
    .btn-custom-primary { background-color: #940000 !important; border-color: #940000 !important; color: white !important; }
    .btn-custom-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; color: white !important; }
    .text-custom-primary { color: #940000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background-color: #940000; color: white;">
                <div class="card-body">
                    <h4 class="card-title text-white mb-1">
                        <i class="bx bx-user-circle me-2"></i>HR DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">human resources management overview</p>
                </div>
            </div>
        </div>
    </div>

    <!-- HR Metrics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">LEAVE REQUESTS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\LeaveRequest::whereIn('status', ['pending_hr_review', 'pending_hod_approval'])->count() }}</h3>
                            <small class="text-muted">pending approval</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-calendar fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">EMPLOYEES</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\User::where('is_active', true)->count() }}</h3>
                            <small class="text-muted">total active</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-user fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">DEPARTMENTS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\Department::where('is_active', true)->count() }}</h3>
                            <small class="text-muted">active departments</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-building fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
