@extends('layouts.app')

@section('title', 'Advanced View - Payment ' . $payment->payment_no)

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Advanced Payment View</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.index') }}">Accounting</a></li>
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.ar.payments') }}">Payments</a></li>
            <li class="breadcrumb-item active">{{ $payment->payment_no }}</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .payment-header {
        border-left: 4px solid #28a745;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .info-card {
        border-left: 4px solid #0d6efd;
        background: #f8f9fa;
        transition: all 0.3s;
        height: 100%;
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
    
    .payment-history-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    
    .payment-history-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .timeline-item {
        border-left: 3px solid #dee2e6;
        padding-left: 20px;
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 5px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #28a745;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #28a745;
    }
    
    .timeline-item:last-child {
        border-left: none;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(40, 167, 69, 0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Action Buttons -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="{{ route('modules.accounting.ar.payments') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i>Back to Payments
            </a>
            <a href="{{ route('modules.accounting.ar.invoices.show', $payment->invoice_id) }}" class="btn btn-outline-info btn-sm ms-2">
                <i class="bx bx-file-blank me-1"></i>View Invoice
            </a>
            <a href="{{ route('modules.accounting.ar.invoices.advanced', $payment->invoice_id) }}" class="btn btn-outline-primary btn-sm ms-2">
                <i class="bx bx-show me-1"></i>Invoice Advanced View
            </a>
        </div>
        <div>
            <a href="{{ route('modules.accounting.ar.payments.pdf', $payment->id) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="bx bx-file-blank me-1"></i>Download PDF
            </a>
        </div>
    </div>

    <!-- Payment Header -->
    <div class="card border-0 shadow-sm mb-4 payment-header">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-money me-2"></i>Payment #{{ $payment->payment_no }}
                    </h2>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span class="badge bg-light text-dark status-badge">
                            <i class="bx bx-info-circle me-1"></i>{{ $payment->payment_method }}
                        </span>
                        @if($payment->reference_no)
                        <span class="text-white-50">
                            <i class="bx bx-hash me-1"></i>Ref: {{ $payment->reference_no }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="mb-2">
                        <small class="text-white-50 d-block">Payment Amount</small>
                        <h3 class="mb-0 text-white">TZS {{ number_format($payment->amount, 2) }}</h3>
                    </div>
                    <div>
                        <small class="text-white-50 d-block">Payment Date</small>
                        <h5 class="mb-0 text-white">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Days Since Payment</h6>
                            <h4 class="mb-0">{{ $daysSincePayment }} Days</h4>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-calendar fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Invoice Payments</h6>
                            <h4 class="mb-0">{{ $invoicePayments->count() }} Payment(s)</h4>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-credit-card fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Invoice Balance</h6>
                            <h4 class="mb-0 {{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($payment->invoice->balance, 2) }}
                            </h4>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-receipt fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Invoice Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-file-blank me-2"></i>Invoice Information</h5>
                </div>
                <div class="card-body">
                    @if($payment->invoice)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Invoice Number:</strong>
                            <p class="mb-0">
                                <a href="{{ route('modules.accounting.ar.invoices.show', $payment->invoice->id) }}">
                                    {{ $payment->invoice->invoice_no }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Invoice Date:</strong>
                            <p class="mb-0">{{ $payment->invoice->invoice_date ? $payment->invoice->invoice_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Due Date:</strong>
                            <p class="mb-0 {{ $payment->invoice->isOverdue() ? 'text-danger' : '' }}">
                                {{ $payment->invoice->due_date ? $payment->invoice->due_date->format('M d, Y') : 'N/A' }}
                                @if($payment->invoice->isOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Invoice Status:</strong>
                            <p class="mb-0">
                                <span class="badge bg-{{ $payment->invoice->status === 'Paid' ? 'success' : ($payment->invoice->status === 'Overdue' ? 'danger' : ($payment->invoice->status === 'Partially Paid' ? 'warning' : 'info')) }}">
                                    {{ $payment->invoice->status ?? 'N/A' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Total Amount:</strong>
                            <p class="mb-0">TZS {{ number_format($payment->invoice->total_amount, 2) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Paid Amount:</strong>
                            <p class="mb-0 text-success">TZS {{ number_format($payment->invoice->paid_amount, 2) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Balance:</strong>
                            <p class="mb-0 {{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($payment->invoice->balance, 2) }}
                            </p>
                        </div>
                        @if($payment->invoice->customer)
                        <div class="col-md-6 mb-3">
                            <strong>Customer:</strong>
                            <p class="mb-0">{{ $payment->invoice->customer->name ?? 'N/A' }}</p>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>Invoice information is not available for this payment.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-money me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Payment Number:</strong>
                            <p class="mb-0">{{ $payment->payment_no }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Payment Date:</strong>
                            <p class="mb-0">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Payment Method:</strong>
                            <p class="mb-0">
                                <span class="badge bg-{{ $payment->payment_method === 'Bank Transfer' ? 'primary' : ($payment->payment_method === 'Cash' ? 'success' : 'info') }}">
                                    {{ $payment->payment_method }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Amount:</strong>
                            <p class="mb-0 text-success fw-bold">TZS {{ number_format($payment->amount, 2) }}</p>
                        </div>
                        @if($payment->reference_no)
                        <div class="col-md-6 mb-3">
                            <strong>Reference Number:</strong>
                            <p class="mb-0">{{ $payment->reference_no }}</p>
                        </div>
                        @endif
                        @if($payment->bankAccount)
                        <div class="col-md-6 mb-3">
                            <strong>Bank Account:</strong>
                            <p class="mb-0">{{ $payment->bankAccount->name ?? 'N/A' }}</p>
                            @if($payment->bankAccount->account_number)
                                <small class="text-muted">Account: {{ $payment->bankAccount->account_number }}</small>
                            @endif
                        </div>
                        @endif
                        @if($payment->notes)
                        <div class="col-12 mb-3">
                            <strong>Notes:</strong>
                            <p class="mb-0">{{ $payment->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment History for Invoice -->
            @if($invoicePayments && $invoicePayments->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-history me-2"></i>All Payments for Invoice #{{ $payment->invoice->invoice_no }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment #</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoicePayments as $invPayment)
                                <tr class="{{ $invPayment->id === $payment->id ? 'table-success' : '' }}">
                                    <td>
                                        <strong>{{ $invPayment->payment_no }}</strong>
                                        @if($invPayment->id === $payment->id)
                                            <span class="badge bg-success ms-2">Current</span>
                                        @endif
                                    </td>
                                    <td>{{ $invPayment->payment_date ? $invPayment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $invPayment->payment_method === 'Bank Transfer' ? 'primary' : ($invPayment->payment_method === 'Cash' ? 'success' : 'info') }}">
                                            {{ $invPayment->payment_method }}
                                        </span>
                                    </td>
                                    <td><strong>TZS {{ number_format($invPayment->amount, 2) }}</strong></td>
                                    <td>{{ $invPayment->reference_no ?? '-' }}</td>
                                    <td>{{ $invPayment->creator->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('modules.accounting.ar.payments.advanced', $invPayment->id) }}" class="btn btn-sm btn-outline-primary" title="View Advanced">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>TZS {{ number_format($invoicePayments->sum('amount'), 2) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $log->description ?? 'Activity' }}</h6>
                                @if($log->user)
                                <small class="text-muted">by {{ $log->user->name }}</small>
                                @endif
                            </div>
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
            <!-- Payment Details -->
            <div class="card shadow-sm mb-4 info-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Payment Date:</strong>
                        <p class="mb-0">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Payment Method:</strong>
                        <p class="mb-0">
                            <span class="badge bg-{{ $payment->payment_method === 'Bank Transfer' ? 'primary' : ($payment->payment_method === 'Cash' ? 'success' : 'info') }}">
                                {{ $payment->payment_method }}
                            </span>
                        </p>
                    </div>
                    @if($payment->creator)
                    <div class="mb-3">
                        <strong>Recorded By:</strong>
                        <p class="mb-0">{{ $payment->creator->name ?? 'Unknown' }}</p>
                        <small class="text-muted">{{ $payment->created_at ? $payment->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Summary -->
            @if($payment->invoice)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-receipt me-2"></i>Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Invoice Number</small>
                        <p class="mb-0">
                            <a href="{{ route('modules.accounting.ar.invoices.show', $payment->invoice->id) }}">
                                {{ $payment->invoice->invoice_no }}
                            </a>
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Total Amount</small>
                        <p class="mb-0 fw-bold">TZS {{ number_format($payment->invoice->total_amount, 2) }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Paid Amount</small>
                        <p class="mb-0 text-success">TZS {{ number_format($payment->invoice->paid_amount, 2) }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Balance</small>
                        <p class="mb-0 {{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            <strong>TZS {{ number_format($payment->invoice->balance, 2) }}</strong>
                        </p>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Status</small>
                        <p class="mb-0">
                            <span class="badge bg-{{ $payment->invoice->status === 'Paid' ? 'success' : ($payment->invoice->status === 'Overdue' ? 'danger' : ($payment->invoice->status === 'Partially Paid' ? 'warning' : 'info')) }}">
                                {{ $payment->invoice->status ?? 'N/A' }}
                            </span>
                        </p>
                    </div>
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
                        <a href="{{ route('modules.accounting.ar.invoices.show', $payment->invoice_id) }}" class="btn btn-outline-info btn-sm">
                            <i class="bx bx-file-blank me-1"></i>View Invoice
                        </a>
                        <a href="{{ route('modules.accounting.ar.invoices.advanced', $payment->invoice_id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-show me-1"></i>Invoice Advanced View
                        </a>
                        <a href="{{ route('modules.accounting.ar.payments.pdf', $payment->id) }}" class="btn btn-outline-danger btn-sm" target="_blank">
                            <i class="bx bx-file-blank me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





