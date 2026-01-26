@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_no)

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Invoice Details</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.index') }}">Accounting</a></li>
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.ar.invoices') }}">Invoices</a></li>
            <li class="breadcrumb-item active">{{ $invoice->invoice_no }}</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .invoice-header {
        border-left: 4px solid #940000;
        background: linear-gradient(135deg, #940000 0%, #c40101 100%);
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
    
    .payment-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    
    .payment-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(148, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Action Buttons -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="{{ route('modules.accounting.ar.invoices') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>Back to Invoices
            </a>
            <a href="{{ route('modules.accounting.ar.invoices.advanced', $invoice->id) }}" class="btn btn-outline-primary btn-sm ms-2">
                <i class="bx bx-show me-1"></i>Advanced View
            </a>
            @if($invoice->balance > 0 && !in_array($invoice->status, ['Cancelled', 'Paid', 'Draft', 'Pending for Approval', 'Pending CEO Approval', 'Rejected']))
            <a href="{{ route('modules.accounting.ar.invoices.payment', $invoice->id) }}" class="btn btn-success btn-sm ms-2">
                <i class="bx bx-money me-1"></i>Record Payment
            </a>
            @endif
        </div>
        <div>
            <a href="{{ route('modules.accounting.ar.invoices.pdf', $invoice->id) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="bx bx-file-blank me-1"></i>Download PDF
            </a>
        </div>
    </div>

    <!-- Invoice Header -->
    <div class="card border-0 shadow-sm mb-4 invoice-header">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-file-blank me-2"></i>Invoice #{{ $invoice->invoice_no }}
                    </h2>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span class="badge bg-light text-dark status-badge">
                            <i class="bx bx-info-circle me-1"></i>{{ $invoice->status }}
                        </span>
                        @if($invoice->reference_no)
                        <span class="text-white-50">
                            <i class="bx bx-hash me-1"></i>Ref: {{ $invoice->reference_no }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="mb-2">
                        <small class="text-white-50 d-block">Total Amount</small>
                        <h3 class="mb-0 text-white">TZS {{ number_format($invoice->total_amount, 2) }}</h3>
                    </div>
                    <div>
                        <small class="text-white-50 d-block">Balance</small>
                        <h4 class="mb-0 text-white">TZS {{ number_format($invoice->balance, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $user = auth()->user();
        $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
        $isCEO = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
        $isSystemAdmin = $user->hasRole('System Admin');
        
        $canHodApprove = $isHOD && !$isCEO && $invoice->status === 'Pending for Approval';
        $canCeoApprove = $isCEO && $invoice->status === 'Pending CEO Approval';
        $canSystemAdminApprove = $isSystemAdmin && $invoice->status === 'Pending for Approval';
        $canReject = ($isHOD || $isCEO || $isSystemAdmin) && in_array($invoice->status, ['Pending for Approval', 'Pending CEO Approval']);
    @endphp

    <!-- Approval Actions -->
    @if($canHodApprove || $canCeoApprove || $canSystemAdminApprove || $canReject)
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <h5 class="text-white mb-3"><i class="bx bx-cog me-2"></i>Available Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                @if($canHodApprove || $canSystemAdminApprove)
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('approve')">
                    <i class="bx bx-check me-1"></i>Approve @if($canHodApprove)(HOD)@else(System Admin)@endif
                </button>
                @endif

                @if($canCeoApprove)
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('approve')">
                    <i class="bx bx-check-double me-1"></i>Final Approval (CEO)
                </button>
                @endif

                @if($canReject)
                <button class="btn btn-light btn-lg" onclick="openRejectionModal()">
                    <i class="bx bx-x me-1"></i>Reject
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Subtotal</h6>
                            <h4 class="mb-0">TZS {{ number_format($invoice->subtotal, 2) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-receipt fs-1 opacity-50"></i>
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
                            <h6 class="text-muted mb-1">Paid Amount</h6>
                            <h4 class="mb-0 text-success">TZS {{ number_format($invoice->paid_amount, 2) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1 opacity-50"></i>
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
                            <h6 class="text-muted mb-1">Payment Count</h6>
                            <h4 class="mb-0">{{ $paymentCount }} Payment(s)</h4>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-credit-card fs-1 opacity-50"></i>
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
                            <h6 class="text-muted mb-1">Days Outstanding</h6>
                            <h4 class="mb-0 {{ $daysOutstanding > 0 ? 'text-danger' : 'text-success' }}">
                                @if($daysOutstanding > 0)
                                    +{{ $daysOutstanding }} Days
                                @elseif($daysOutstanding < 0)
                                    {{ abs($daysOutstanding) }} Days Left
                                @else
                                    0 Days
                                @endif
                            </h4>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-calendar fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Customer Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-user me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    @if($invoice->customer)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Customer Name:</strong>
                            <p class="mb-0">{{ $invoice->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Customer Code:</strong>
                            <p class="mb-0">{{ $invoice->customer->customer_code ?? 'N/A' }}</p>
                        </div>
                        @if($invoice->customer->email)
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong>
                            <p class="mb-0">
                                <a href="mailto:{{ $invoice->customer->email }}">{{ $invoice->customer->email }}</a>
                            </p>
                        </div>
                        @endif
                        @if($invoice->customer->phone || $invoice->customer->mobile)
                        <div class="col-md-6 mb-3">
                            <strong>Phone:</strong>
                            <p class="mb-0">
                                {{ $invoice->customer->phone ?? $invoice->customer->mobile }}
                            </p>
                        </div>
                        @endif
                        @if($invoice->customer->address)
                        <div class="col-12 mb-3">
                            <strong>Address:</strong>
                            <p class="mb-0">{{ $invoice->customer->address }}</p>
                            @if($invoice->customer->city)
                                <p class="mb-0">{{ $invoice->customer->city }}{{ $invoice->customer->country ? ', ' . $invoice->customer->country : '' }}</p>
                            @endif
                        </div>
                        @endif
                        @if($invoice->customer && $invoice->customer->id)
                        <div class="col-12 mt-2">
                            <a href="{{ route('modules.accounting.ar.customers.show', $invoice->customer->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show me-1"></i>View Customer Details
                            </a>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>Customer information is not available for this invoice.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Invoice Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Tax Rate</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->tax_rate, 2) }}%</td>
                                    <td class="text-end">TZS {{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($item->discount_amount, 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="7" class="text-end">Subtotal:</th>
                                    <th class="text-end">TZS {{ number_format($invoice->subtotal, 2) }}</th>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <th colspan="7" class="text-end">Tax Amount:</th>
                                    <th class="text-end">TZS {{ number_format($invoice->tax_amount, 2) }}</th>
                                </tr>
                                @endif
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <th colspan="7" class="text-end">Discount:</th>
                                    <th class="text-end">TZS {{ number_format($invoice->discount_amount, 2) }}</th>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="7" class="text-end">Total Amount:</th>
                                    <th class="text-end">TZS {{ number_format($invoice->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            @if($invoicePayments && $invoicePayments->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-history me-2"></i>Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment #</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoicePayments as $payment)
                                <tr>
                                    <td>
                                        <strong>{{ $payment->payment_no }}</strong>
                                    </td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payment_method === 'Bank Transfer' ? 'primary' : ($payment->payment_method === 'Cash' ? 'success' : 'info') }}">
                                            {{ $payment->payment_method }}
                                        </span>
                                    </td>
                                    <td class="text-end"><strong>TZS {{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>{{ $payment->reference_no ?? '-' }}</td>
                                    <td>{{ $payment->creator->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('modules.accounting.ar.payments.advanced', $payment->id) }}" class="btn btn-sm btn-outline-primary" title="View Advanced">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total Payments:</th>
                                    <th class="text-end">TZS {{ number_format($totalPayments, 2) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <i class="bx bx-info-circle fs-1 text-muted mb-3"></i>
                    <p class="text-muted mb-0">No payments recorded for this invoice yet.</p>
                    @if($invoice->balance > 0 && !in_array($invoice->status, ['Cancelled', 'Paid', 'Draft', 'Pending for Approval', 'Pending CEO Approval', 'Rejected']))
                    <a href="{{ route('modules.accounting.ar.invoices.payment', $invoice->id) }}" class="btn btn-success btn-sm mt-3">
                        <i class="bx bx-money me-1"></i>Record First Payment
                    </a>
                    @endif
                </div>
            </div>
            @endif

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
            <!-- Invoice Details -->
            <div class="card shadow-sm mb-4 info-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Invoice Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="text-muted small d-block mb-1">Invoice Date</label>
                            <div class="fw-semibold">{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="text-muted small d-block mb-1">Due Date</label>
                            <div class="fw-semibold {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                                {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                                @if($invoice->isOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="text-muted small d-block mb-1">Days Outstanding</label>
                            <div class="fw-bold {{ $daysOutstanding > 0 ? 'text-danger' : ($daysOutstanding < 0 ? 'text-success' : '') }}">
                                @if($daysOutstanding > 0)
                                    <i class="bx bx-time-five me-1"></i>+{{ abs($daysOutstanding) }} Days
                                @elseif($daysOutstanding < 0)
                                    <i class="bx bx-check me-1"></i>{{ abs($daysOutstanding) }} Days Remaining
                                @else
                                    <i class="bx bx-calendar-check me-1"></i>Due Today
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="text-muted small d-block mb-1">Status</label>
                            <div>
                                <span class="badge bg-{{ $invoice->status === 'Paid' ? 'success' : ($invoice->status === 'Overdue' ? 'danger' : ($invoice->status === 'Partially Paid' ? 'warning' : 'info')) }} px-3 py-2">
                                    {{ $invoice->status ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($invoice->creator)
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-12">
                            <label class="text-muted small d-block mb-1">Created By</label>
                            <div class="fw-semibold mb-1">{{ $invoice->creator->name ?? 'Unknown' }}</div>
                            <small class="text-muted"><i class="bx bx-time me-1"></i>{{ $invoice->created_at ? $invoice->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                        </div>
                    </div>
                    @endif
                    @if($invoice->updater)
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="text-muted small d-block mb-1">Last Updated By</label>
                            <div class="fw-semibold mb-1">{{ $invoice->updater->name ?? 'Unknown' }}</div>
                            <small class="text-muted"><i class="bx bx-time me-1"></i>{{ $invoice->updated_at ? $invoice->updated_at->format('M d, Y H:i') : 'N/A' }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approval Status -->
            @if($invoice->hod_approved_at || $invoice->ceo_approved_at)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-check-circle me-2"></i>Approval Status</h5>
                </div>
                <div class="card-body">
                    @if($invoice->hod_approved_at)
                    <div class="mb-3">
                        <strong>HOD Approval:</strong>
                        <div class="d-flex align-items-center mt-1">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            <div>
                                <p class="mb-0">
                                    @if($invoice->hodApprover)
                                        {{ $invoice->hodApprover->name }}
                                    @else
                                        Approved
                                    @endif
                                </p>
                                <small class="text-muted">{{ $invoice->hod_approved_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($invoice->hod_comments)
                        <div class="mt-2">
                            <small class="text-muted"><strong>Comments:</strong> {{ $invoice->hod_comments }}</small>
                        </div>
                        @endif
                    </div>
                    @endif
                    @if($invoice->ceo_approved_at)
                    <div>
                        <strong>CEO Approval:</strong>
                        <div class="d-flex align-items-center mt-1">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            <div>
                                <p class="mb-0">
                                    @if($invoice->ceoApprover)
                                        {{ $invoice->ceoApprover->name }}
                                    @else
                                        Approved
                                    @endif
                                </p>
                                <small class="text-muted">{{ $invoice->ceo_approved_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($invoice->ceo_comments)
                        <div class="mt-2">
                            <small class="text-muted"><strong>Comments:</strong> {{ $invoice->ceo_comments }}</small>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Financial Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Subtotal</span>
                            <strong>TZS {{ number_format($invoice->subtotal, 2) }}</strong>
                        </div>
                        @if($invoice->tax_amount > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Tax</span>
                            <strong>TZS {{ number_format($invoice->tax_amount, 2) }}</strong>
                        </div>
                        @endif
                        @if($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Discount</span>
                            <strong class="text-success">-TZS {{ number_format($invoice->discount_amount, 2) }}</strong>
                        </div>
                        @endif
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Total</span>
                            <strong class="fs-5">TZS {{ number_format($invoice->total_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Paid</span>
                            <strong class="text-success">TZS {{ number_format($invoice->paid_amount, 2) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Balance</span>
                        <strong class="fs-5 {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            TZS {{ number_format($invoice->balance, 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Payment Progress -->
            @if($invoice->total_amount > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>Payment Progress</h5>
                </div>
                <div class="card-body">
                    @php
                        $paymentPercentage = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0;
                    @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Progress</span>
                            <span class="small fw-semibold">{{ number_format($paymentPercentage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $paymentPercentage >= 100 ? 'bg-success' : ($paymentPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                 role="progressbar" 
                                 style="width: {{ min($paymentPercentage, 100) }}%" 
                                 aria-valuenow="{{ $paymentPercentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            TZS {{ number_format($invoice->paid_amount, 2) }} of TZS {{ number_format($invoice->total_amount, 2) }}
                        </small>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes and Terms -->
            @if($invoice->notes || $invoice->terms)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-note me-2"></i>Notes & Terms</h5>
                </div>
                <div class="card-body">
                    @if($invoice->notes)
                    <div class="mb-3">
                        <strong>Notes:</strong>
                        <p class="mb-0 text-muted">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                    @if($invoice->terms)
                    <div>
                        <strong>Terms:</strong>
                        <p class="mb-0 text-muted">{{ $invoice->terms }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('modules.accounting.ar.invoices.advanced', $invoice->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-show me-1"></i>Advanced View
                        </a>
                        <a href="{{ route('modules.accounting.ar.invoices.pdf', $invoice->id) }}" class="btn btn-outline-danger btn-sm" target="_blank">
                            <i class="bx bx-file-blank me-1"></i>Download PDF
                        </a>
                        @if($invoice->balance > 0 && !in_array($invoice->status, ['Cancelled', 'Paid', 'Draft', 'Pending for Approval', 'Pending CEO Approval', 'Rejected']))
                        <a href="{{ route('modules.accounting.ar.invoices.payment', $invoice->id) }}" class="btn btn-outline-success btn-sm">
                            <i class="bx bx-money me-1"></i>Record Payment
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Approve Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approvalForm" method="POST" action="{{ route('modules.accounting.ar.invoices.approve', $invoice->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approval_comments" class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" id="approval_comments" name="comments" rows="3" placeholder="Enter approval comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i>Approve Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectionModalLabel">Reject Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectionForm" method="POST" action="{{ route('modules.accounting.ar.invoices.reject', $invoice->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-1"></i>
                        <strong>Warning:</strong> This action cannot be undone. Please provide a reason for rejection.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Enter reason for rejection..."></textarea>
                        <div class="form-text">This reason will be added to the invoice notes.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-x me-1"></i>Reject Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openApprovalModal() {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        modal.show();
    }

    function openRejectionModal() {
        const modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
        modal.show();
    }

    // Handle approval form submission
    document.getElementById('approvalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to approve invoice'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while approving the invoice'
            });
        });
    });

    // Handle rejection form submission
    document.getElementById('rejectionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        if (!formData.get('rejection_reason').trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Field',
                text: 'Please provide a rejection reason'
            });
            return;
        }
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectionModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to reject invoice'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while rejecting the invoice'
            });
        });
    });
</script>
@endpush

@endsection

