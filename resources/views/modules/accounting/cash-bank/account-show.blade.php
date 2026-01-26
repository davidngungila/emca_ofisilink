@extends('layouts.app')

@section('title', 'Bank Account - ' . ($bankAccount->name ?? $bankAccount->account_name ?? 'N/A'))

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Bank Account Details</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.index') }}">Accounting</a></li>
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.cash-bank.accounts') }}">Bank Accounts</a></li>
            <li class="breadcrumb-item active">{{ $bankAccount->bank_name ?? 'Account' }} - {{ $bankAccount->account_number ?? 'N/A' }}</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .account-header {
        border-left: 4px solid #0d6efd;
        background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
    }
    
    .info-card {
        border-left: 4px solid #0d6efd;
        background: #f8f9fa;
        transition: all 0.3s;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        border-left: 4px solid #28a745;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Action Buttons -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="{{ route('modules.accounting.cash-bank.accounts') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>Back to Accounts
            </a>
            <button onclick="editAccount({{ $bankAccount->id }})" class="btn btn-warning btn-sm ms-2">
                <i class="bx bx-edit me-1"></i>Edit Account
            </button>
        </div>
        <div>
            <button onclick="deleteAccount({{ $bankAccount->id }}, '{{ $bankAccount->bank_name }}', '{{ $bankAccount->account_number }}')" class="btn btn-danger btn-sm">
                <i class="bx bx-trash me-1"></i>Delete Account
            </button>
        </div>
    </div>

    <!-- Account Header -->
    <div class="card border-0 shadow-sm mb-4 account-header">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-credit-card me-2"></i>{{ $bankAccount->bank_name ?? 'Bank Account' }}
                    </h2>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span class="badge bg-light text-dark">
                            <i class="bx bx-hash me-1"></i>{{ $bankAccount->account_number ?? 'N/A' }}
                        </span>
                        @if($bankAccount->is_primary)
                        <span class="badge bg-warning text-dark">
                            <i class="bx bx-star me-1"></i>Primary Account
                        </span>
                        @endif
                        <span class="badge {{ $bankAccount->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $bankAccount->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="mb-2">
                        <small class="text-white-50 d-block">Current Balance</small>
                        <h3 class="mb-0 text-white">TZS {{ number_format($bankAccount->balance ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Transactions</h6>
                            <h4 class="mb-0">{{ $transactionSummary['total_transactions'] ?? 0 }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-list-ul fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Debits</h6>
                            <h4 class="mb-0 text-danger">TZS {{ number_format($transactionSummary['total_debits'] ?? 0, 2) }}</h4>
                        </div>
                        <div class="text-danger">
                            <i class="bx bx-down-arrow-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Credits</h6>
                            <h4 class="mb-0 text-success">TZS {{ number_format($transactionSummary['total_credits'] ?? 0, 2) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-up-arrow-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Current Balance</h6>
                            <h4 class="mb-0 {{ ($bankAccount->balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($bankAccount->balance ?? 0, 2) }}
                            </h4>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-wallet fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Account Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Bank Name</label>
                            <div class="fw-semibold">{{ $bankAccount->bank_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Account Number</label>
                            <div class="fw-semibold"><code>{{ $bankAccount->account_number ?? 'N/A' }}</code></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Account Name</label>
                            <div class="fw-semibold">{{ $bankAccount->account_name ?? 'N/A' }}</div>
                        </div>
                        @if($bankAccount->branch_name)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Branch Name</label>
                            <div class="fw-semibold">{{ $bankAccount->branch_name }}</div>
                        </div>
                        @endif
                        @if($bankAccount->swift_code)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">SWIFT Code</label>
                            <div class="fw-semibold"><code>{{ $bankAccount->swift_code }}</code></div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Account Status</label>
                            <div>
                                <span class="badge {{ $bankAccount->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                    {{ $bankAccount->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($bankAccount->is_primary)
                                    <span class="badge bg-warning ms-2 px-3 py-2">Primary</span>
                                @endif
                            </div>
                        </div>
                        @if($bankAccount->user)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Account Owner</label>
                            <div class="fw-semibold">{{ $bankAccount->user->name ?? 'N/A' }}</div>
                            @if($bankAccount->user->email)
                                <small class="text-muted">{{ $bankAccount->user->email }}</small>
                            @endif
                        </div>
                        @endif
                        @if($chartAccount)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-1">Chart of Account</label>
                            <div class="fw-semibold">
                                {{ $chartAccount->code ?? 'N/A' }} - {{ $chartAccount->name ?? 'N/A' }}
                            </div>
                        </div>
                        @endif
                        @if($bankAccount->notes)
                        <div class="col-12 mb-3">
                            <label class="text-muted small d-block mb-1">Notes</label>
                            <div class="text-muted">{{ $bankAccount->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            @if(count($activityLogs) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-time-five me-2"></i>Activity Timeline</h5>
                </div>
                <div class="card-body">
                    @foreach($activityLogs as $log)
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bx bx-time"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">{{ $log->description ?? 'Activity' }}</h6>
                            @if($log->user)
                            <small class="text-muted">by {{ $log->user->name }}</small>
                            @endif
                            <br>
                            <small class="text-muted">{{ $log->created_at ? $log->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Account Details -->
            <div class="card shadow-sm mb-4 info-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small d-block mb-1">Account Number</label>
                        <div class="fw-semibold"><code>{{ $bankAccount->account_number ?? 'N/A' }}</code></div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small d-block mb-1">Balance</label>
                        <div class="fw-bold fs-5 {{ ($bankAccount->balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format($bankAccount->balance ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small d-block mb-1">Status</label>
                        <div>
                            <span class="badge {{ $bankAccount->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                {{ $bankAccount->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($bankAccount->is_primary)
                                <span class="badge bg-warning ms-2 px-3 py-2">Primary</span>
                            @endif
                        </div>
                    </div>
                    @if($bankAccount->created_at)
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Created</label>
                        <div class="fw-semibold mb-1">{{ $bankAccount->created_at->format('M d, Y') }}</div>
                        <small class="text-muted"><i class="bx bx-time me-1"></i>{{ $bankAccount->created_at->format('H:i') }}</small>
                    </div>
                    @endif
                    @if($bankAccount->updated_at)
                    <div>
                        <label class="text-muted small d-block mb-1">Last Updated</label>
                        <div class="fw-semibold mb-1">{{ $bankAccount->updated_at->format('M d, Y') }}</div>
                        <small class="text-muted"><i class="bx bx-time me-1"></i>{{ $bankAccount->updated_at->format('H:i') }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Transaction Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Total Transactions</span>
                            <strong>{{ $transactionSummary['total_transactions'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Total Debits</span>
                            <strong class="text-danger">TZS {{ number_format($transactionSummary['total_debits'] ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    <div class="mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Total Credits</span>
                            <strong class="text-success">TZS {{ number_format($transactionSummary['total_credits'] ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Current Balance</span>
                        <strong class="fs-5 {{ ($bankAccount->balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format($bankAccount->balance ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button onclick="editAccount({{ $bankAccount->id }})" class="btn btn-outline-warning btn-sm">
                            <i class="bx bx-edit me-1"></i>Edit Account
                        </button>
                        <a href="{{ route('modules.accounting.cash-bank.reconciliation', ['account_id' => $bankAccount->id]) }}" class="btn btn-outline-info btn-sm">
                            <i class="bx bx-check-square me-1"></i>Reconciliation
                        </a>
                        <button onclick="deleteAccount({{ $bankAccount->id }}, '{{ $bankAccount->bank_name }}', '{{ $bankAccount->account_number }}')" class="btn btn-outline-danger btn-sm">
                            <i class="bx bx-trash me-1"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editAccount(id) {
        // Navigate back to accounts page and trigger edit
        window.location.href = `{{ route('modules.accounting.cash-bank.accounts') }}?edit=${id}`;
    }
    
    function deleteAccount(id, bankName, accountNumber) {
        if (!confirm(`Are you sure you want to delete this bank account?\n\nBank: ${bankName}\nAccount: ${accountNumber}\n\nThis action cannot be undone!`)) {
            return;
        }
        
        fetch(`{{ route('modules.accounting.cash-bank.accounts.destroy', ['id' => ':id']) }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const result = await response.json();
            
            if(response.ok && result.success) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Bank account deleted successfully!', { duration: 5000 });
                } else {
                    alert('Bank account deleted successfully!');
                }
                
                // Redirect to accounts list
                window.location.href = '{{ route('modules.accounting.cash-bank.accounts') }}';
            } else {
                const errorMsg = result.message || 'Failed to delete bank account';
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || 'Network error occurred';
            if(typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
            } else {
                alert('Error: ' + errorMsg);
            }
        });
    }
</script>
@endpush
@endsection





