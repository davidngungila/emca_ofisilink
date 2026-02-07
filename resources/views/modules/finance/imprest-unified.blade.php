@extends('layouts.app')

@section('title', 'Imprest Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Imprest Management</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Imprest Requests</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    :root {
        --custom-primary: #940000;
    }
    
    .page-header {
        background: linear-gradient(135deg, var(--custom-primary) 0%, #7a0000 100%);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(148, 0, 0, 0.2);
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }
    
    .stat-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        height: 100%;
        position: relative;
        overflow: hidden;
        background: white;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .stat-card.border-primary::before { background: var(--custom-primary); }
    .stat-card.border-warning::before { background: #ffc107; }
    .stat-card.border-success::before { background: #28a745; }
    .stat-card.border-info::before { background: #17a2b8; }
    .stat-card.border-danger::before { background: #dc3545; }
    .stat-card.border-dark::before { background: #343a40; }
    
    .stat-card:hover::before {
        width: 100%;
        opacity: 0.1;
    }
    
    .filter-btn {
        width: 100%;
        margin-bottom: 8px;
        text-align: left;
        padding: 12px 15px;
        border-radius: 8px;
        transition: all 0.3s;
        border: 2px solid #e9ecef;
        background: white;
        color: #495057;
        font-weight: 500;
    }
    
    .filter-btn:hover {
        background: #f8f9fa;
        border-color: var(--custom-primary);
        transform: translateX(5px);
    }
    
    .filter-btn.active {
        background: var(--custom-primary);
        color: white;
        border-color: var(--custom-primary);
    }
    
    .filter-btn .badge {
        float: right;
        margin-top: 2px;
    }
    
    .export-card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: none;
        overflow: hidden;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        margin-bottom: 2rem;
    }
    
    .export-card .card-header {
        background: linear-gradient(135deg, var(--custom-primary) 0%, #7a0000 100%);
        color: white;
        padding: 1rem 1.5rem;
        border: none;
    }
    
    .export-period-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        height: 100%;
    }
    
    .export-period-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(148, 0, 0, 0.15);
        border-color: var(--custom-primary);
    }
    
    .swal2-container { z-index: 200000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-2 text-white">
                    <i class="bx bx-wallet me-2"></i>Imprest Management
                </h2>
                <p class="mb-0 opacity-90">Complete workflow: Request → Approval → Assignment → Payment → Receipts → Verification</p>
            </div>
            <div class="mt-3 mt-md-0">
                @php
                    $isAccountant = auth()->user()->hasRole('Accountant') || auth()->user()->hasRole('System Admin');
                @endphp
                @if($isAccountant)
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#newImprestModal">
                    <i class="bx bx-plus-circle me-1"></i>New Request
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-primary">
                <div class="card-body p-3">
                    <div class="text-xs text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">My Action</div>
                    <div class="h4 mb-0 mt-2" style="font-weight: 700; color: var(--custom-primary);">{{ $counts['my_action'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-warning">
                <div class="card-body p-3">
                    <div class="text-xs text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Pending</div>
                    <div class="h4 mb-0 mt-2" style="font-weight: 700; color: #ffc107;">{{ ($counts['pending_hod'] ?? 0) + ($counts['pending_ceo'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-info">
                <div class="card-body p-3">
                    <div class="text-xs text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">In Progress</div>
                    <div class="h4 mb-0 mt-2" style="font-weight: 700; color: #17a2b8;">{{ ($counts['approved'] ?? 0) + ($counts['assigned'] ?? 0) + ($counts['paid'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-success">
                <div class="card-body p-3">
                    <div class="text-xs text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">Completed</div>
                    <div class="h4 mb-0 mt-2" style="font-weight: 700; color: #28a745;">{{ $counts['completed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Reports Section -->
    <div class="card export-card">
        <div class="card-header">
            <h5 class="mb-0" style="color: white; font-weight: 600;">
                <i class="bx bx-download me-2"></i>Export Reports
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="export-period-card">
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--custom-primary); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-calendar me-2"></i>Quarter Report
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('imprest.export.report', ['period' => 'quarter', 'format' => 'pdf']) }}" 
                               class="btn btn-sm flex-fill" 
                               style="border-radius: 8px; border: 2px solid #dc3545; color: #dc3545;">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                            <a href="{{ route('imprest.export.report', ['period' => 'quarter', 'format' => 'excel']) }}" 
                               class="btn btn-sm flex-fill" 
                               style="border-radius: 8px; border: 2px solid #28a745; color: #28a745;">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="export-period-card">
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--custom-primary); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-calendar-check me-2"></i>6 Months Report
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('imprest.export.report', ['period' => '6month', 'format' => 'pdf']) }}" 
                               class="btn btn-sm flex-fill" 
                               style="border-radius: 8px; border: 2px solid #dc3545; color: #dc3545;">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                            <a href="{{ route('imprest.export.report', ['period' => '6month', 'format' => 'excel']) }}" 
                               class="btn btn-sm flex-fill" 
                               style="border-radius: 8px; border: 2px solid #28a745; color: #28a745;">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="export-period-card">
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--custom-primary); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-calendar-event me-2"></i>Year Report
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('imprest.export.report', ['period' => 'year', 'format' => 'pdf']) }}" 
                               class="btn btn-sm flex-fill" 
                               style="border-radius: 8px; border: 2px solid #dc3545; color: #dc3545;">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                            <a href="{{ route('imprest.export.report', ['period' => 'year', 'format' => 'excel']) }}" 
                               class="btn btn-sm flex-fill" 
                               style="border-radius: 8px; border: 2px solid #28a745; color: #28a745;">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Sidebar - Filters -->
        <div class="col-xl-3 col-lg-4 mb-4">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid var(--custom-primary);">
                    <h5 class="mb-0" style="color: var(--custom-primary); font-weight: 600;">
                        <i class="bx bx-filter me-2"></i>Filters
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div style="padding: 1rem;">
                        @php
                            $user = Auth::user();
                            $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
                            $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
                            $isCEO = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
                            $currentFilter = request('filter', 'my_action');
                        @endphp

                        <button class="btn filter-btn {{ $currentFilter === 'my_action' ? 'active' : '' }}" 
                                onclick="loadRequests('my_action')">
                            <i class="bx bx-time me-2"></i>My Action
                            <span class="badge bg-danger">{{ $counts['my_action'] ?? 0 }}</span>
                        </button>

                        @if($isHOD || $isAccountant)
                        <button class="btn filter-btn {{ $currentFilter === 'pending_hod' ? 'active' : '' }}" 
                                onclick="loadRequests('pending_hod')">
                            <i class="bx bx-user-check me-2"></i>Pending HOD
                            <span class="badge bg-warning">{{ $counts['pending_hod'] ?? 0 }}</span>
                        </button>
                        @endif

                        @if($isCEO || $isAccountant || $isHOD)
                        <button class="btn filter-btn {{ $currentFilter === 'pending_ceo' ? 'active' : '' }}" 
                                onclick="loadRequests('pending_ceo')">
                            <i class="bx bx-user-circle me-2"></i>Pending CEO
                            <span class="badge bg-info">{{ $counts['pending_ceo'] ?? 0 }}</span>
                        </button>
                        @endif

                        @if($isAccountant)
                        <button class="btn filter-btn {{ $currentFilter === 'approved' ? 'active' : '' }}" 
                                onclick="loadRequests('approved')">
                            <i class="bx bx-check-circle me-2"></i>Approved
                            <span class="badge bg-success">{{ $counts['approved'] ?? 0 }}</span>
                        </button>

                        <button class="btn filter-btn {{ $currentFilter === 'assigned' ? 'active' : '' }}" 
                                onclick="loadRequests('assigned')">
                            <i class="bx bx-credit-card me-2"></i>Assigned
                            <span class="badge bg-primary">{{ $counts['assigned'] ?? 0 }}</span>
                        </button>

                        <button class="btn filter-btn {{ $currentFilter === 'paid' ? 'active' : '' }}" 
                                onclick="loadRequests('paid')">
                            <i class="bx bx-money me-2"></i>Paid
                            <span class="badge bg-info">{{ $counts['paid'] ?? 0 }}</span>
                        </button>

                        <button class="btn filter-btn {{ $currentFilter === 'pending_verification' ? 'active' : '' }}" 
                                onclick="loadRequests('pending_verification')">
                            <i class="bx bx-check-double me-2"></i>Pending Verification
                            <span class="badge bg-warning">{{ $counts['pending_receipt_verification'] ?? 0 }}</span>
                        </button>
                        @endif

                        <hr>

                        <button class="btn filter-btn {{ $currentFilter === 'completed' ? 'active' : '' }}" 
                                onclick="loadRequests('completed')">
                            <i class="bx bx-check-square me-2"></i>Completed
                            <span class="badge bg-success">{{ $counts['completed'] ?? 0 }}</span>
                        </button>

                        <button class="btn filter-btn {{ $currentFilter === 'all' ? 'active' : '' }}" 
                                onclick="loadRequests('all')">
                            <i class="bx bx-list-ul me-2"></i>All Requests
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <div class="card-body p-0">
                    <div id="requests-container">
                        @include('modules.finance.imprest-partials.table', [
                            'requests' => $requests,
                            'showActions' => true
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('modules.finance.imprest-partials.modals')
@include('modules.finance.imprest-partials.scripts')

@push('scripts')
<script>
function loadRequests(filter) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('.filter-btn').classList.add('active');
    
    // Show loading
    document.getElementById('requests-container').innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
    
    // Load requests
    fetch(`{{ route('imprest.index') }}?filter=${filter}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('requests-container').innerHTML = data.html;
            // Update URL without reload
            window.history.pushState({}, '', `{{ route('imprest.index') }}?filter=${filter}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('requests-container').innerHTML = '<div class="alert alert-danger">Error loading requests. Please refresh the page.</div>';
    });
}
</script>
@endpush
@endsection

