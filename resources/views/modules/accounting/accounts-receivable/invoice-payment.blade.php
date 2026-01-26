@extends('layouts.app')

@section('title', 'Record Payment - ' . $invoice->invoice_no)

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Record Invoice Payment</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.ar.invoices') }}">Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('modules.accounting.ar.invoices.show', $invoice->id) }}">{{ $invoice->invoice_no }}</a></li>
            <li class="breadcrumb-item active">Record Payment</li>
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
    
    .payment-history-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    
    .payment-history-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .amount-input-group {
        position: relative;
    }
    
    .amount-input-group::before {
        content: 'TZS';
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: bold;
        color: #6c757d;
        z-index: 10;
    }
    
    .amount-input-group .form-control {
        padding-left: 80px;
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .alert-approval {
        border-left: 4px solid #ffc107;
        background-color: #fff3cd;
    }
    
    .approval-info {
        border-left: 4px solid #17a2b8;
        background-color: #d1ecf1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('modules.accounting.ar.invoices') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back to Invoices
        </a>
        <a href="{{ route('modules.accounting.ar.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm ms-2">
            <i class="bx bx-show me-1"></i>View Invoice Details
        </a>
    </div>

    <!-- Invoice Header -->
    <div class="card border-0 shadow-sm mb-4 invoice-header">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-receipt me-2"></i>{{ $invoice->invoice_no }}
                    </h2>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Customer:</strong> {{ $invoice->customer->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Total Amount:</strong> <strong>TZS {{ number_format($invoice->total_amount, 2) }}</strong></p>
                            <p class="mb-1"><strong>Paid Amount:</strong> <strong class="text-success">TZS {{ number_format($invoice->paid_amount, 2) }}</strong></p>
                            <p class="mb-0"><strong>Balance:</strong> <strong class="text-warning">TZS {{ number_format($invoice->balance, 2) }}</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge status-badge 
                        @if($invoice->status === 'Approved' || $invoice->status === 'Sent') bg-success
                        @elseif($invoice->status === 'Partially Paid') bg-warning
                        @elseif($invoice->status === 'Overdue') bg-danger
                        @elseif($invoice->status === 'Paid') bg-success
                        @elseif($invoice->status === 'Pending for Approval' || $invoice->status === 'Pending CEO Approval') bg-warning
                        @elseif($invoice->status === 'Rejected') bg-danger
                        @else bg-secondary
                        @endif">
                        {{ $invoice->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Status Alert -->
    @if(!in_array($invoice->status, ['Approved', 'Sent', 'Partially Paid', 'Overdue']))
    <div class="alert alert-warning alert-approval mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bx bx-info-circle fs-4 me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Invoice Not Approved</h6>
                <p class="mb-0">This invoice must be approved before payment can be recorded. Current status: <strong>{{ $invoice->status }}</strong></p>
                @if($invoice->status === 'Pending for Approval')
                    <small class="text-muted">Waiting for HOD approval</small>
                @elseif($invoice->status === 'Pending CEO Approval')
                    <small class="text-muted">Waiting for CEO approval</small>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Approval Information -->
    @if($invoice->hod_approved_at || $invoice->ceo_approved_at)
    <div class="card border-0 shadow-sm mb-4 approval-info">
        <div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="bx bx-check-circle me-2"></i>Approval Information</h6>
            <div class="row">
                @if($invoice->hod_approved_at)
                <div class="col-md-6">
                    <p class="mb-1"><strong>HOD Approved:</strong> 
                        <span class="text-success">Yes</span>
                        @if($invoice->hodApprover)
                            - {{ $invoice->hodApprover->name }}
                        @endif
                    </p>
                    <p class="mb-1"><small class="text-muted">Date: {{ $invoice->hod_approved_at->format('d M Y H:i') }}</small></p>
                    @if($invoice->hod_comments)
                        <p class="mb-0"><small><strong>Comments:</strong> {{ $invoice->hod_comments }}</small></p>
                    @endif
                </div>
                @endif
                @if($invoice->ceo_approved_at)
                <div class="col-md-6">
                    <p class="mb-1"><strong>CEO Approved:</strong> 
                        <span class="text-success">Yes</span>
                        @if($invoice->ceoApprover)
                            - {{ $invoice->ceoApprover->name }}
                        @endif
                    </p>
                    <p class="mb-1"><small class="text-muted">Date: {{ $invoice->ceo_approved_at->format('d M Y H:i') }}</small></p>
                    @if($invoice->ceo_comments)
                        <p class="mb-0"><small><strong>Comments:</strong> {{ $invoice->ceo_comments }}</small></p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Left Column: Invoice Details & Items -->
        <div class="col-lg-8">
            <!-- Customer Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bx bx-user me-2"></i>Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Name:</strong> {{ $invoice->customer->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $invoice->customer->email ?? '-' }}</p>
                            <p class="mb-0"><strong>Phone:</strong> {{ $invoice->customer->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Address:</strong> {{ $invoice->customer->address ?? '-' }}</p>
                            <p class="mb-1"><strong>City:</strong> {{ $invoice->customer->city ?? '-' }}</p>
                            @if($invoice->customer->tax_id)
                                <p class="mb-0"><strong>Tax ID:</strong> {{ $invoice->customer->tax_id }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bx bx-list-ul me-2"></i>Invoice Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Tax %</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->tax_rate, 2) }}%</td>
                                    <td class="text-end"><strong>TZS {{ number_format($item->line_total, 2) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($invoice->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-danger"><strong>Discount:</strong></td>
                                    <td class="text-end text-danger"><strong>- TZS {{ number_format($invoice->discount_amount, 2) }}</strong></td>
                                </tr>
                                @endif
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($invoice->tax_amount, 2) }}</strong></td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end text-success"><strong>Paid Amount:</strong></td>
                                    <td class="text-end text-success"><strong>TZS {{ number_format($invoice->paid_amount, 2) }}</strong></td>
                                </tr>
                                <tr class="table-warning">
                                    <td colspan="4" class="text-end"><strong>Balance:</strong></td>
                                    <td class="text-end"><strong class="text-danger">TZS {{ number_format($invoice->balance, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bx bx-history me-2"></i>Payment History</h6>
                </div>
                <div class="card-body">
                    @forelse($invoice->payments as $payment)
                    <div class="payment-history-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1"><code class="text-primary">{{ $payment->payment_no }}</code></h6>
                                <p class="mb-1 text-muted small">{{ $payment->payment_date->format('d M Y H:i') }}</p>
                                <p class="mb-0"><strong class="text-success">TZS {{ number_format($payment->amount, 2) }}</strong></p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info">{{ $payment->payment_method }}</span>
                                @if($payment->bankAccount)
                                    <p class="mb-0 mt-1 small text-muted">{{ $payment->bankAccount->name }}</p>
                                @endif
                            </div>
                        </div>
                        @if($payment->reference_no)
                            <p class="mb-1 small"><strong>Reference:</strong> {{ $payment->reference_no }}</p>
                        @endif
                        @if($payment->notes)
                            <p class="mb-0 small text-muted">{{ $payment->notes }}</p>
                        @endif
                        @if($payment->createdBy)
                            <p class="mb-0 mt-2 small text-muted"><i class="bx bx-user"></i> Recorded by: {{ $payment->createdBy->name }}</p>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-inbox fs-1"></i>
                        <p class="mt-2 mb-0">No payments recorded yet</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Payment Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0 text-white fw-semibold"><i class="bx bx-money me-2"></i>Record Payment</h6>
                </div>
                <div class="card-body">
                    <form id="paymentForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="amount-input-group">
                                <input type="number" step="0.01" class="form-control" id="paymentAmount" name="amount" value="{{ $invoice->balance }}" max="{{ $invoice->balance }}" required>
                            </div>
                            <small class="text-muted">Maximum: TZS {{ number_format($invoice->balance, 2) }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference No</label>
                            <input type="text" class="form-control" id="paymentReference" name="reference_no" placeholder="Payment reference number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bank Account</label>
                            <select class="form-select" id="paymentBankAccount" name="bank_account_id">
                                <option value="">Select Bank Account</option>
                                @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }} - {{ $bank->account_number }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="paymentNotes" name="notes" rows="3" placeholder="Additional notes about this payment"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="submitBtn" @if(!in_array($invoice->status, ['Approved', 'Sent', 'Partially Paid', 'Overdue'])) disabled @endif>
                                <i class="bx bx-save me-1"></i>Record Payment
                            </button>
                            @if(!in_array($invoice->status, ['Approved', 'Sent', 'Partially Paid', 'Overdue']))
                            <small class="text-danger text-center">Invoice must be approved before payment</small>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = '{{ csrf_token() }}';
const invoiceId = {{ $invoice->id }};
const invoiceBalance = {{ $invoice->balance }};

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
    
    const formData = {
        payment_date: document.getElementById('paymentDate').value,
        amount: parseFloat(document.getElementById('paymentAmount').value),
        payment_method: document.getElementById('paymentMethod').value,
        reference_no: document.getElementById('paymentReference').value || null,
        bank_account_id: document.getElementById('paymentBankAccount').value || null,
        notes: document.getElementById('paymentNotes').value || null
    };
    
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.invoices.payment.store', $invoice->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Payment recorded successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Payment recorded successfully');
            }
            
            // Reload page to show updated payment history
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            let errorMessage = data.message || 'Error recording payment';
            
            if (data.errors) {
                const errorMessages = [];
                Object.keys(data.errors).forEach(key => {
                    const fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    if (Array.isArray(data.errors[key])) {
                        data.errors[key].forEach(err => {
                            errorMessages.push(`${fieldName}: ${err}`);
                        });
                    } else {
                        errorMessages.push(`${fieldName}: ${data.errors[key]}`);
                    }
                });
                if (errorMessages.length > 0) {
                    errorMessage = errorMessages.join('<br>');
                }
            }
            
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', errorMessage, { duration: 10000, sound: true });
            } else {
                alert(errorMessage);
            }
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Network error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Network error: ' + error.message);
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Validate amount doesn't exceed balance
document.getElementById('paymentAmount').addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    if (amount > invoiceBalance) {
        this.setCustomValidity('Payment amount cannot exceed invoice balance');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});
</script>
@endpush





