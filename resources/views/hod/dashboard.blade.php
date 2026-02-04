@extends('layouts.app')

@section('title', 'HOD Dashboard')

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
                        <i class="bx bx-group me-2"></i>HOD DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">welcome, {{ $user->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PENDING VOUCHERS (DEPT)</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\PettyCashVoucher::where('department_id', $user->department_id)->where('status', 'pending_hod')->count() }}</h3>
                            <small class="text-muted">awaiting hod review</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-time fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PENDING IMPREST (DEPT)</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\ImprestRequest::where('department_id', $user->department_id)->where('status', 'pending_hod')->count() }}</h3>
                            <small class="text-muted">awaiting hod review</small>
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
    </div>
</div>
@endsection
