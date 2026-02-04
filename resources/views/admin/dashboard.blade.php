@extends('layouts.app')

@section('title', 'Admin Dashboard - OfisiLink')

@push('styles')
<style>
    .bg-custom-primary { background-color: #940000 !important; color: white !important; }
    .btn-custom-primary { background-color: #940000 !important; border-color: #940000 !important; color: white !important; }
    .btn-custom-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; color: white !important; }
    .btn-outline-custom { border-color: #940000 !important; color: #940000 !important; }
    .btn-outline-custom:hover { background-color: #940000 !important; color: white !important; }
    .text-custom-primary { color: #940000 !important; }
    .border-left-custom { border-left: 4px solid #940000 !important; }
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
                        <i class="bx bx-user-circle me-2"></i>ADMIN DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">welcome, {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">TOTAL USERS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\User::count() }}</h3>
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
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ACTIVE ROLES</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\Role::where('is_active', true)->count() }}</h3>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-shield fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">DEPARTMENTS</h6>
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
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PENDING APPROVALS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\PettyCashVoucher::whereIn('status', ['pending_accountant', 'pending_hod', 'pending_ceo'])->count() }}</h3>
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
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background-color: #940000; color: white;">
                    <h5 class="card-title mb-0 text-white">QUICK ACTIONS</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-custom-primary w-100">
                                <i class="bx bx-user-plus me-2"></i>Add New User
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-custom w-100">
                                <i class="bx bx-list-ul me-2"></i>Manage Users
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.roles') }}" class="btn btn-outline-custom w-100">
                                <i class="bx bx-cog me-2"></i>Manage Roles
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.settings') }}" class="btn btn-outline-custom w-100">
                                <i class="bx bx-settings me-2"></i>System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
