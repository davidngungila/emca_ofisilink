@extends('layouts.app')

@section('title', 'Refund Request Details')

@push('styles')
<style>
    .status-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .status-timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    
    .status-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .status-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 0.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #dee2e6;
        border: 2px solid #fff;
    }
    
    .status-item.completed::before {
        background: #28a745;
    }
    
    .status-item.pending::before {
        background: #ffc107;
    }
    
    .status-item.rejected::before {
        background: #dc3545;
    }
    
    .attachment-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s;
    }
    
    .attachment-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('refunds.index') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back to Refunds
        </a>
    </div>

    @php
        $user = auth()->user();
        $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
        $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
        $isCEO = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
        
        $canHodApprove = $isHOD && $refundRequest->status === 'pending_hod';
        $canAccountantVerify = $isAccountant && $refundRequest->status === 'pending_accountant';
        $canCeoApprove = $isCEO && $refundRequest->status === 'pending_ceo';
        $canMarkPaid = $isAccountant && $refundRequest->status === 'approved';
    @endphp

    <!-- Action Buttons -->
    @if($canHodApprove || $canAccountantVerify || $canCeoApprove || $canMarkPaid)
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <h5 class="text-white mb-3"><i class="bx bx-cog me-2"></i>Available Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                @if($canHodApprove)
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('hod', 'approve')">
                    <i class="bx bx-check me-1"></i>Approve (HOD)
                </button>
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('hod', 'reject')">
                    <i class="bx bx-x me-1"></i>Reject
                </button>
                @endif

                @if($canAccountantVerify)
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('accountant', 'approve')">
                    <i class="bx bx-check me-1"></i>Verify (Accountant)
                </button>
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('accountant', 'reject')">
                    <i class="bx bx-x me-1"></i>Reject
                </button>
                @endif

                @if($canCeoApprove)
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('ceo', 'approve')">
                    <i class="bx bx-check-double me-1"></i>Final Approval (CEO)
                </button>
                <button class="btn btn-light btn-lg" onclick="openApprovalModal('ceo', 'reject')">
                    <i class="bx bx-x me-1"></i>Reject
                </button>
                @endif

                @if($canMarkPaid)
                <button class="btn btn-light btn-lg" onclick="openPaymentModal()">
                    <i class="bx bx-money me-1"></i>Mark as Paid
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8 mb-4">
            <!-- Request Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-info-circle me-2"></i>Request Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Request Number:</strong><br>
                            <span class="text-primary fw-bold">{{ $refundRequest->request_no }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
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
                            <span class="badge bg-{{ $statusClasses[$refundRequest->status] ?? 'secondary' }} fs-6">
                                {{ $statusLabels[$refundRequest->status] ?? ucfirst($refundRequest->status) }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Staff Member:</strong><br>
                            {{ $refundRequest->staff->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Purpose:</strong><br>
                            {{ $refundRequest->purpose }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Amount:</strong><br>
                            <span class="fs-4 fw-bold text-success">{{ number_format($refundRequest->amount, 2) }} TZS</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Expense Date:</strong><br>
                            {{ $refundRequest->expense_date->format('F d, Y') }}
                        </div>
                    </div>
                    @if($refundRequest->description)
                    <div class="mb-3">
                        <strong>Description:</strong><br>
                        <p class="text-muted">{{ $refundRequest->description }}</p>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Created:</strong><br>
                            {{ $refundRequest->created_at->format('F d, Y h:i A') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Last Updated:</strong><br>
                            {{ $refundRequest->updated_at->format('F d, Y h:i A') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-paperclip me-2"></i>Supporting Documents
                        <span class="badge bg-primary">{{ $refundRequest->attachments->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($refundRequest->attachments->count() > 0)
                    @foreach($refundRequest->attachments as $attachment)
                    <div class="attachment-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                @php
                                    $icon = 'bx-file-blank';
                                    if (str_contains($attachment->file_type, 'image')) {
                                        $icon = 'bx-image';
                                    } elseif (str_contains($attachment->file_type, 'pdf')) {
                                        $icon = 'bx-file-blank';
                                    } else {
                                        $icon = 'bx-file';
                                    }
                                @endphp
                                <i class="bx {{ $icon }} fs-3 text-primary me-3"></i>
                                <div>
                                    <div class="fw-bold">{{ $attachment->file_name }}</div>
                                    @if($attachment->description)
                                    <small class="text-muted">{{ $attachment->description }}</small><br>
                                    @endif
                                    <small class="text-muted">
                                        {{ number_format($attachment->file_size / 1024, 2) }} KB
                                        • Uploaded {{ $attachment->created_at->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                            <a href="{{ route('refunds.attachment.download', [$refundRequest->id, $attachment->id]) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-download"></i> Download
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted text-center py-3">No attachments found</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-time me-2"></i>Status Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="status-timeline">
                        <div class="status-item {{ $refundRequest->status !== 'rejected' ? 'completed' : '' }}">
                            <strong>Request Submitted</strong>
                            <div class="text-muted small">{{ $refundRequest->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        
                        @if($refundRequest->hod_approved_at)
                        <div class="status-item completed">
                            <strong>HOD Approved</strong>
                            <div class="text-muted small">{{ $refundRequest->hod_approved_at->format('M d, Y h:i A') }}</div>
                            @if($refundRequest->hodApproval)
                            <div class="text-muted small">By: {{ $refundRequest->hodApproval->name }}</div>
                            @endif
                            @if($refundRequest->hod_comments)
                            <div class="text-muted small mt-1"><em>"{{ $refundRequest->hod_comments }}"</em></div>
                            @endif
                        </div>
                        @elseif($refundRequest->status === 'pending_hod')
                        <div class="status-item pending">
                            <strong>Pending HOD Approval</strong>
                        </div>
                        @endif
                        
                        @if($refundRequest->accountant_verified_at)
                        <div class="status-item completed">
                            <strong>Accountant Verified</strong>
                            <div class="text-muted small">{{ $refundRequest->accountant_verified_at->format('M d, Y h:i A') }}</div>
                            @if($refundRequest->accountantVerification)
                            <div class="text-muted small">By: {{ $refundRequest->accountantVerification->name }}</div>
                            @endif
                            @if($refundRequest->accountant_comments)
                            <div class="text-muted small mt-1"><em>"{{ $refundRequest->accountant_comments }}"</em></div>
                            @endif
                        </div>
                        @elseif($refundRequest->status === 'pending_accountant')
                        <div class="status-item pending">
                            <strong>Pending Accountant Verification</strong>
                        </div>
                        @endif
                        
                        @if($refundRequest->ceo_approved_at)
                        <div class="status-item completed">
                            <strong>CEO Approved</strong>
                            <div class="text-muted small">{{ $refundRequest->ceo_approved_at->format('M d, Y h:i A') }}</div>
                            @if($refundRequest->ceoApproval)
                            <div class="text-muted small">By: {{ $refundRequest->ceoApproval->name }}</div>
                            @endif
                            @if($refundRequest->ceo_comments)
                            <div class="text-muted small mt-1"><em>"{{ $refundRequest->ceo_comments }}"</em></div>
                            @endif
                        </div>
                        @elseif($refundRequest->status === 'pending_ceo')
                        <div class="status-item pending">
                            <strong>Pending CEO Approval</strong>
                        </div>
                        @endif
                        
                        @if($refundRequest->paid_at)
                        <div class="status-item completed">
                            <strong>Paid</strong>
                            <div class="text-muted small">{{ $refundRequest->paid_at->format('M d, Y h:i A') }}</div>
                            @if($refundRequest->paidBy)
                            <div class="text-muted small">By: {{ $refundRequest->paidBy->name }}</div>
                            @endif
                            @if($refundRequest->payment_method)
                            <div class="text-muted small">Method: {{ $refundRequest->payment_method }}</div>
                            @endif
                        </div>
                        @elseif($refundRequest->status === 'approved')
                        <div class="status-item pending">
                            <strong>Approved - Awaiting Payment</strong>
                        </div>
                        @endif
                        
                        @if($refundRequest->status === 'rejected')
                        <div class="status-item rejected">
                            <strong>Rejected</strong>
                            @if($refundRequest->rejected_at)
                            <div class="text-muted small">{{ $refundRequest->rejected_at->format('M d, Y h:i A') }}</div>
                            @endif
                            @if($refundRequest->rejectedBy)
                            <div class="text-muted small">By: {{ $refundRequest->rejectedBy->name }}</div>
                            @endif
                            @if($refundRequest->rejection_reason)
                            <div class="text-danger small mt-1"><strong>Reason:</strong> {{ $refundRequest->rejection_reason }}</div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            @if($refundRequest->status === 'paid' && $refundRequest->paid_at)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white border-bottom">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-check-circle me-2"></i>Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Payment Date:</strong><br>
                        {{ $refundRequest->paid_at->format('F d, Y h:i A') }}
                    </div>
                    @if($refundRequest->payment_method)
                    <div class="mb-2">
                        <strong>Payment Method:</strong><br>
                        {{ $refundRequest->payment_method }}
                    </div>
                    @endif
                    @if($refundRequest->payment_reference)
                    <div class="mb-2">
                        <strong>Reference:</strong><br>
                        {{ $refundRequest->payment_reference }}
                    </div>
                    @endif
                    @if($refundRequest->payment_notes)
                    <div class="mb-2">
                        <strong>Notes:</strong><br>
                        <p class="text-muted mb-0">{{ $refundRequest->payment_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approvalForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" name="comments" rows="3" placeholder="Optional comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="approvalSubmitBtn"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST" action="{{ route('refunds.mark-paid', $refundRequest->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Reference</label>
                        <input type="text" class="form-control" name="payment_reference" placeholder="Transaction reference, cheque number, etc.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Notes</label>
                        <textarea class="form-control" name="payment_notes" rows="3" placeholder="Additional payment details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
let currentAction = '';
let currentType = '';

function openApprovalModal(type, action) {
    currentType = type;
    currentAction = action;
    
    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    const title = document.getElementById('approvalModalTitle');
    const submitBtn = document.getElementById('approvalSubmitBtn');
    
    if (action === 'approve') {
        title.textContent = type === 'hod' ? 'Approve (HOD)' : (type === 'accountant' ? 'Verify (Accountant)' : 'Final Approval (CEO)');
        submitBtn.textContent = type === 'hod' ? 'Approve' : (type === 'accountant' ? 'Verify' : 'Approve');
        submitBtn.className = 'btn btn-success';
    } else {
        title.textContent = 'Reject Request';
        submitBtn.textContent = 'Reject';
        submitBtn.className = 'btn btn-danger';
    }
    
    modal.show();
}

function openPaymentModal() {
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

document.getElementById('approvalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', currentAction);
    
    let url = '';
    if (currentType === 'hod') {
        url = '{{ route("refunds.hod-approve", $refundRequest->id) }}';
    } else if (currentType === 'accountant') {
        url = '{{ route("refunds.accountant-verify", $refundRequest->id) }}';
    } else if (currentType === 'ceo') {
        url = '{{ route("refunds.ceo-approve", $refundRequest->id) }}';
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || !data.errors) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Action completed successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to process action'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred. Please try again.'
        });
    });
});
</script>
@endpush





