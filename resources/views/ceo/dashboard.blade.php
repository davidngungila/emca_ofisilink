@extends('layouts.app')

@section('title', 'CEO/Director Dashboard')

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
                        <i class="bx bx-trophy me-2"></i>EXECUTIVE DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">welcome, {{ $user->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PENDING VOUCHER APPROVALS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\PettyCashVoucher::where('status', 'pending_ceo')->count() }}</h3>
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
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PENDING IMPREST APPROVALS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\ImprestRequest::where('status', 'pending_ceo')->count() }}</h3>
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
                            <h6 class="text-muted mb-2">ACTIVE DEPARTMENTS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\Department::where('is_active', true)->count() }}</h3>
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
