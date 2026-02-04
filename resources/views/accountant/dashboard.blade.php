@extends('layouts.app')

@section('title', 'Accountant Dashboard')

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
                        <i class="bx bx-calculator me-2"></i>ACCOUNTANT DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">finance and accounting overview</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Finance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">PETTY CASH VOUCHERS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\PettyCashVoucher::whereIn('status', ['pending_accountant', 'pending_ceo'])->count() }}</h3>
                            <small class="text-muted">pending / processing</small>
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
                            <h6 class="text-muted mb-2">IMPREST REQUESTS</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\ImprestRequest::whereIn('status', ['pending_accountant', 'pending_ceo'])->count() }}</h3>
                            <small class="text-muted">awaiting action</small>
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
                            <h6 class="text-muted mb-2">JOURNAL ENTRIES</h6>
                            <h3 class="mb-0 text-custom-primary">{{ \App\Models\JournalEntry::whereMonth('created_at', now()->month)->count() }}</h3>
                            <small class="text-muted">created this month</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial rounded bg-custom-primary">
                                <i class="bx bx-book fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
