@extends('layouts.app')

@section('title', 'Staff Dashboard')

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
                        <i class="bx bx-user me-2"></i>STAFF DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">welcome, {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Overview -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">MY PETTY CASH</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\PettyCashVoucher::where('requested_by', auth()->id())->where('status', '!=', 'retired')->count() }}</h3>
                            <small class="text-muted">to retire</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-wallet fs-4"></i>
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
                            <h6 class="text-muted mb-2">MY IMPREST REQUESTS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\ImprestRequest::where('requested_by', auth()->id())->whereIn('status', ['pending_hod', 'pending_accountant', 'pending_ceo', 'approved'])->count() }}</h3>
                            <small class="text-muted">in progress</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-money fs-4"></i>
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
                            <h6 class="text-muted mb-2">MY TASKS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\MainTask::where('assigned_to', auth()->id())->whereIn('status', ['pending', 'in_progress'])->count() }}</h3>
                            <small class="text-muted">assigned</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-task fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
