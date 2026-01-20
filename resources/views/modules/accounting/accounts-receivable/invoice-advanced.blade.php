@extends('layouts.app')

@section('title', 'Advanced View - Invoice ' . $invoice->invoice_no)

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Advanced Invoice View</h4>
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
        background: #940000;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #940000;
    }
    
    .timeline-item:last-child {
        border-left: none;
    }
    
    .approval-info {
        border-left: 4px solid #17a2b8;
        background-color: #d1ecf1;
    }
    
    .alert-approval {
        border-left: 4px solid #ffc107;
        background-color: #fff3cd;
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
            <a href="{{ route('modules.accounting.ar.invoices.show', $invoice->id) }}" class="btn btn-outline-info btn-sm ms-2">
                <i class="bx bx-show me-1"></i>Quick View
            </a>
            <a href="{{ route('modules.accounting.ar.invoices.payment', $invoice->id) }}" class="btn btn-success btn-sm ms-2">
                <i class="bx bx-money me-1"></i>Record Payment
            </a>
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
                                {{ $daysOutstanding > 0 ? '+' : '' }}{{ $daysOutstanding }} Days
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
                        @if($invoice->customer->tax_id)
                        <div class="col-md-6 mb-3">
                            <strong>Tax ID:</strong>
                            <p class="mb-0">{{ $invoice->customer->tax_id }}</p>
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
                                    <th>Account</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Tax %</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>
                                        @if($item->account)
                                            <small class="text-muted">{{ $item->account->code }} - {{ $item->account->name }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->tax_rate, 2) }}%</td>
                                    <td class="text-end"><strong>TZS {{ number_format($item->line_total, 2) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($invoice->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($invoice->tax_amount, 2) }}</strong></td>
                                </tr>
                                @endif
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                                    <td class="text-end"><strong>-TZS {{ number_format($invoice->discount_amount, 2) }}</strong></td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bx bx-history me-2"></i>Payment History</h5>
                    <span class="badge bg-primary">{{ $paymentCount }} Payment(s)</span>
                </div>
                <div class="card-body">
                    @forelse($invoice->payments as $payment)
                    <div class="payment-history-card p-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1">
                                    <i class="bx bx-money me-1 text-success"></i>{{ $payment->payment_no }}
                                </h6>
                                <small class="text-muted">
                                    <i class="bx bx-calendar me-1"></i>{{ $payment->payment_date->format('M d, Y') }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-success">TZS {{ number_format($payment->amount, 2) }}</strong>
                                <div>
                                    <small class="text-muted">{{ ucfirst($payment->payment_method) }}</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if($payment->bankAccount)
                                    <small class="text-muted d-block">Bank Account:</small>
                                    <small>{{ $payment->bankAccount->name }}</small>
                                @endif
                                @if($payment->reference_no)
                                    <div class="mt-1">
                                        <small class="text-muted d-block">Ref: {{ $payment->reference_no }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-2 text-md-end">
                                <a href="{{ route('modules.accounting.ar.payments.show', $payment->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                @if($payment->createdBy)
                                <div class="mt-1">
                                    <small class="text-muted">by {{ $payment->createdBy->name }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @if($payment->notes)
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted"><i class="bx bx-note me-1"></i>{{ $payment->notes }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                        <p class="mb-0">No payments recorded yet</p>
                        <a href="{{ route('modules.accounting.ar.invoices.payment', $invoice->id) }}" class="btn btn-sm btn-success mt-2">
                            <i class="bx bx-plus me-1"></i>Record Payment
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Notes & Terms -->
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
                        <strong>Terms & Conditions:</strong>
                        <p class="mb-0 text-muted">{{ $invoice->terms }}</p>
                    </div>
                    @endif
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
                    <div class="mb-3">
                        <strong>Invoice Date:</strong>
                        <p class="mb-0">{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Due Date:</strong>
                        <p class="mb-0 {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                            @if($invoice->isOverdue())
                                <span class="badge bg-danger ms-2">Overdue</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <p class="mb-0">
                            <span class="badge bg-{{ $invoice->status === 'Paid' ? 'success' : ($invoice->status === 'Overdue' ? 'danger' : ($invoice->status === 'Partially Paid' ? 'warning' : 'info')) }}">
                                {{ $invoice->status }}
                            </span>
                        </p>
                    </div>
                    @if($invoice->creator)
                    <div class="mb-3">
                        <strong>Created By:</strong>
                        <p class="mb-0">{{ $invoice->creator->name ?? 'Unknown' }}</p>
                        <small class="text-muted">{{ $invoice->created_at ? $invoice->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                    </div>
                    @endif
                    @if($invoice->updater)
                    <div class="mb-3">
                        <strong>Last Updated By:</strong>
                        <p class="mb-0">{{ $invoice->updater->name ?? 'Unknown' }}</p>
                        <small class="text-muted">{{ $invoice->updated_at ? $invoice->updated_at->format('M d, Y H:i') : 'N/A' }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approval Status -->
            @if($invoice->hod_approved_at || $invoice->ceo_approved_at)
            <div class="card shadow-sm mb-4 approval-info">
                <div class="card-header bg-info text-white">
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
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>TZS {{ number_format($invoice->subtotal, 2) }}</strong>
                    </div>
                    @if($invoice->tax_amount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <strong>TZS {{ number_format($invoice->tax_amount, 2) }}</strong>
                    </div>
                    @endif
                    @if($invoice->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <strong class="text-danger">-TZS {{ number_format($invoice->discount_amount, 2) }}</strong>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Total:</strong></span>
                        <strong class="text-primary">TZS {{ number_format($invoice->total_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid:</span>
                        <strong class="text-success">TZS {{ number_format($invoice->paid_amount, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Balance:</strong></span>
                        <strong class="{{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            TZS {{ number_format($invoice->balance, 2) }}
                        </strong>
                    </div>
                    @if($invoice->total_amount > 0)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">Payment Progress</small>
                        <div class="progress" style="height: 20px;">
                            @php
                                $progress = ($invoice->paid_amount / $invoice->total_amount) * 100;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%">
                                {{ number_format($progress, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endif
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
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $log->description ?? 'Activity' }}</h6>
                                @if($log->causer)
                                <small class="text-muted">by {{ $log->causer->name }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $log->created_at->format('M d, Y H:i') }}</small>
                        </div>
                    </div>
                    @endforeach
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
                        <a href="{{ route('modules.accounting.ar.invoices.payment', $invoice->id) }}" class="btn btn-success">
                            <i class="bx bx-money me-1"></i>Record Payment
                        </a>
                        <a href="{{ route('modules.accounting.ar.invoices.pdf', $invoice->id) }}" class="btn btn-danger" target="_blank">
                            <i class="bx bx-file-blank me-1"></i>Download PDF
                        </a>
                        @if($invoice->status !== 'Paid' && $invoice->status !== 'Cancelled')
                        <a href="{{ route('modules.accounting.ar.invoices.show', $invoice->id) }}" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i>Edit Invoice
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

