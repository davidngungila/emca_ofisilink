@extends('layouts.app')

@section('title', 'Refund Requests')

@push('styles')
<style>
    .refund-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .refund-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-danger">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-money-withdraw me-2"></i>Refund Requests
                    </h2>
                    <p class="mb-0 opacity-90">Manage your refund requests for expenses paid from your pocket</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('refunds.create') }}" class="btn btn-light btn-lg">
                        <i class="bx bx-plus me-2"></i>Request Refund
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if($isManager)
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm refund-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">All Requests</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['all'] }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bx bx-list-ul fs-2 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm refund-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending HOD</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending_hod'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bx bx-time fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm refund-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Accountant</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ $stats['pending_accountant'] }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bx bx-check-circle fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm refund-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Paid</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['paid'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bx bx-check-double fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('refunds.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending_hod" {{ request('status') == 'pending_hod' ? 'selected' : '' }}>Pending HOD</option>
                        <option value="pending_accountant" {{ request('status') == 'pending_accountant' ? 'selected' : '' }}>Pending Accountant</option>
                        <option value="pending_ceo" {{ request('status') == 'pending_ceo' ? 'selected' : '' }}>Pending CEO</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Request No, Purpose..." value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('refunds.index') }}" class="btn btn-secondary">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Refund Requests List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($refundRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Request No</th>
                            @if($isManager)
                            <th>Staff</th>
                            @endif
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Expense Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($refundRequests as $refund)
                        <tr>
                            <td>
                                <strong>{{ $refund->request_no }}</strong>
                            </td>
                            @if($isManager)
                            <td>{{ $refund->staff->name }}</td>
                            @endif
                            <td>{{ Str::limit($refund->purpose, 40) }}</td>
                            <td><strong>{{ number_format($refund->amount, 2) }} TZS</strong></td>
                            <td>{{ $refund->expense_date->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending_hod' => 'warning',
                                        'pending_accountant' => 'info',
                                        'pending_ceo' => 'primary',
                                        'approved' => 'success',
                                        'paid' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'pending_hod' => 'Pending HOD',
                                        'pending_accountant' => 'Pending Accountant',
                                        'pending_ceo' => 'Pending CEO',
                                        'approved' => 'Approved',
                                        'paid' => 'Paid',
                                        'rejected' => 'Rejected'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusClasses[$refund->status] ?? 'secondary' }} status-badge">
                                    {{ $statusLabels[$refund->status] ?? ucfirst($refund->status) }}
                                </span>
                            </td>
                            <td>{{ $refund->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('refunds.show', $refund->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-show"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $refundRequests->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bx bx-money-withdraw fs-1 text-muted mb-3"></i>
                <p class="text-muted">No refund requests found.</p>
                <a href="{{ route('refunds.create') }}" class="btn btn-danger">
                    <i class="bx bx-plus me-1"></i>Create Your First Refund Request
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection





