@extends('layouts.app')

@section('title', 'All Petty Cash Vouchers')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">All Petty Cash Vouchers</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('petty-cash.index') }}">Petty Cash Dashboard</a></li>
            <li class="breadcrumb-item active">All Vouchers</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    :root {
        --custom-primary: #940000;
        --custom-primary-light: rgba(148, 0, 0, 0.1);
        --custom-primary-dark: #7a0000;
    }
    
    .page-header-card {
        background: linear-gradient(135deg, var(--custom-primary) 0%, var(--custom-primary-dark) 100%);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(148, 0, 0, 0.2);
        border: none;
        overflow: hidden;
        position: relative;
    }
    
    .page-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }
    
    .count-badge {
        font-size: 3rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
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
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0.5rem 0;
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .filter-card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: none;
        overflow: hidden;
    }
    
    .filter-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid var(--custom-primary);
        padding: 1rem 1.5rem;
    }
    
    .filter-card .card-header h5 {
        color: var(--custom-primary);
        font-weight: 600;
        margin: 0;
    }
    
    .export-card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: none;
        overflow: hidden;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    }
    
    .export-card .card-header {
        background: linear-gradient(135deg, var(--custom-primary) 0%, var(--custom-primary-dark) 100%);
        color: white;
        padding: 1rem 1.5rem;
        border: none;
    }
    
    .export-card .card-header h5 {
        color: white;
        font-weight: 600;
        margin: 0;
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
    
    .export-period-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--custom-primary);
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-export {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        border-width: 2px;
    }
    
    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-export-pdf {
        border-color: #dc3545;
        color: #dc3545;
    }
    
    .btn-export-pdf:hover {
        background: #dc3545;
        color: white;
    }
    
    .btn-export-excel {
        border-color: #28a745;
        color: #28a745;
    }
    
    .btn-export-excel:hover {
        background: #28a745;
        color: white;
    }
    
    .table-card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: none;
        overflow: hidden;
    }
    
    .badge-active-filters {
        background: var(--custom-primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--custom-primary);
        box-shadow: 0 0 0 0.2rem var(--custom-primary-light);
    }
    
    .btn-primary {
        background: var(--custom-primary);
        border-color: var(--custom-primary);
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: var(--custom-primary-dark);
        border-color: var(--custom-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.3);
    }
    
    .btn-secondary {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
    }
    
    @media (max-width: 768px) {
        .count-badge {
            font-size: 2rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 page-header-card">
        <div class="card-body text-white position-relative">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-list-ul me-2"></i>All Petty Cash Vouchers
                    </h2>
                    <p class="mb-0 opacity-90">View and filter all petty cash vouchers with advanced search options</p>
                </div>
                <div class="mt-3 mt-md-0 text-center">
                    <span class="count-badge d-block">{{ $count }}</span>
                    <p class="mb-0 small opacity-90">Total Results</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card stat-card border-primary">
                <div class="card-body p-3">
                    <div class="stat-label">All</div>
                    <div class="stat-number">{{ $stats['all'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card stat-card border-warning">
                <div class="card-body p-3">
                    <div class="stat-label">Pending</div>
                    <div class="stat-number">{{ ($stats['pending_accountant'] ?? 0) + ($stats['pending_hod'] ?? 0) + ($stats['pending_ceo'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card stat-card border-success">
                <div class="card-body p-3">
                    <div class="stat-label">Approved</div>
                    <div class="stat-number">{{ $stats['approved_for_payment'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card stat-card border-info">
                <div class="card-body p-3">
                    <div class="stat-label">Paid</div>
                    <div class="stat-number">{{ $stats['paid'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card stat-card border-warning">
                <div class="card-body p-3">
                    <div class="stat-label">Retirement</div>
                    <div class="stat-number">{{ $stats['pending_retirement_review'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card stat-card border-dark">
                <div class="card-body p-3">
                    <div class="stat-label">Retired</div>
                    <div class="stat-number">{{ $stats['retired'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Reports Section -->
    <div class="card export-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bx bx-download me-2"></i>Export Reports
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="export-period-card">
                        <div class="export-period-title">
                            <i class="bx bx-calendar me-2"></i>Quarter Report
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('petty-cash.export.report', ['period' => 'quarter', 'format' => 'pdf']) }}" class="btn btn-sm btn-export btn-export-pdf flex-fill">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                            <a href="{{ route('petty-cash.export.report', ['period' => 'quarter', 'format' => 'excel']) }}" class="btn btn-sm btn-export btn-export-excel flex-fill">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="export-period-card">
                        <div class="export-period-title">
                            <i class="bx bx-calendar-check me-2"></i>6 Months Report
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('petty-cash.export.report', ['period' => '6month', 'format' => 'pdf']) }}" class="btn btn-sm btn-export btn-export-pdf flex-fill">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                            <a href="{{ route('petty-cash.export.report', ['period' => '6month', 'format' => 'excel']) }}" class="btn btn-sm btn-export btn-export-excel flex-fill">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="export-period-card">
                        <div class="export-period-title">
                            <i class="bx bx-calendar-event me-2"></i>Year Report
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('petty-cash.export.report', ['period' => 'year', 'format' => 'pdf']) }}" class="btn btn-sm btn-export btn-export-pdf flex-fill">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                            <a href="{{ route('petty-cash.export.report', ['period' => 'year', 'format' => 'excel']) }}" class="btn btn-sm btn-export btn-export-excel flex-fill">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card border-0 shadow-sm mb-4 filter-card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bx bx-filter me-2"></i>Advanced Filters
                <button class="btn btn-sm btn-outline-secondary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
                    <i class="bx bx-chevron-down"></i> Toggle Filters
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="{{ route('petty-cash.all') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending_accountant" {{ request('status') === 'pending_accountant' ? 'selected' : '' }}>Pending Accountant</option>
                            <option value="pending_hod" {{ request('status') === 'pending_hod' ? 'selected' : '' }}>Pending HOD</option>
                            <option value="pending_ceo" {{ request('status') === 'pending_ceo' ? 'selected' : '' }}>Pending CEO</option>
                            <option value="approved_for_payment" {{ request('status') === 'approved_for_payment' ? 'selected' : '' }}>Approved for Payment</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending_retirement_review" {{ request('status') === 'pending_retirement_review' ? 'selected' : '' }}>Pending Retirement</option>
                            <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Voucher #, Purpose, Payee..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created From</label>
                        <input type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created To</label>
                        <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount Min</label>
                        <input type="number" name="amount_min" class="form-control" step="0.01" placeholder="0.00" value="{{ request('amount_min') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount Max</label>
                        <input type="number" name="amount_max" class="form-control" step="0.01" placeholder="0.00" value="{{ request('amount_max') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Creator</label>
                        <select name="creator_id" class="form-select">
                            <option value="">All Creators</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}" {{ request('creator_id') == $creator->id ? 'selected' : '' }}>
                                    {{ $creator->name }} ({{ $creator->employee_id ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Accountant</label>
                        <select name="accountant_id" class="form-select">
                            <option value="">All Accountants</option>
                            @foreach($accountants as $accountant)
                                <option value="{{ $accountant->id }}" {{ request('accountant_id') == $accountant->id ? 'selected' : '' }}>
                                    {{ $accountant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Type</label>
                        <select name="is_direct" class="form-select">
                            <option value="">All Types</option>
                            <option value="yes" {{ request('is_direct') === 'yes' ? 'selected' : '' }}>Direct Vouchers</option>
                            <option value="no" {{ request('is_direct') === 'no' ? 'selected' : '' }}>Regular Vouchers</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order By</label>
                        <select name="order_by" class="form-select">
                            <option value="created_at" {{ request('order_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="date" {{ request('order_by') === 'date' ? 'selected' : '' }}>Voucher Date</option>
                            <option value="amount" {{ request('order_by') === 'amount' ? 'selected' : '' }}>Amount</option>
                            <option value="voucher_no" {{ request('order_by') === 'voucher_no' ? 'selected' : '' }}>Voucher Number</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order Direction</label>
                        <select name="order_dir" class="form-select">
                            <option value="desc" {{ request('order_dir') === 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ request('order_dir') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Per Page</label>
                        <select name="per_page" class="form-select">
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-filter me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('petty-cash.all') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh me-1"></i>Reset
                        </a>
                        @if(request()->hasAny(['status', 'search', 'date_from', 'date_to', 'created_from', 'created_to', 'amount_min', 'amount_max', 'creator_id', 'accountant_id', 'is_direct']))
                        <span class="badge badge-active-filters ms-2">
                            <i class="bx bx-info-circle"></i> Filters Active
                        </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="card table-card">
        <div class="card-body p-0">
            @include('modules.finance.petty-cash-partials.table', [
                'vouchers' => $vouchers,
                'showActions' => true
            ])
        </div>
    </div>
</div>

@include('modules.finance.petty-cash-partials.modals')
@include('modules.finance.petty-cash-partials.scripts')
@endsection
