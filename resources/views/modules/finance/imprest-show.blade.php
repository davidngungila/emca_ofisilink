@extends('layouts.app')

@section('title', 'Imprest Request Details')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Imprest Request Details</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $imprestRequest->request_no }}</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .action-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('imprest.index') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back
        </a>
    </div>

    <!-- Action Buttons (if needed) -->
    @php
        $isSystemAdmin = auth()->user()->hasRole('System Admin');
        $user = auth()->user();
        
        // System Admin can approve at any level (HOD and CEO) regardless of status
        // But only if the action hasn't been performed yet
        // HOD can only approve when status is pending_hod
        $canHodApprove = false;
        // Check if HOD approval has already been performed
        $hodAlreadyApproved = !is_null($imprestRequest->hod_approved_at);
        
        if (!$hodAlreadyApproved) {
            if ($isSystemAdmin) {
                // System Admin can approve HOD level at any status (except completed)
                if (!in_array($imprestRequest->status, ['completed'])) {
                    $canHodApprove = true;
                }
            } elseif ($isHOD) {
                // Regular HOD can approve when status is pending_hod
                if ($imprestRequest->status === 'pending_hod') {
                    $canHodApprove = true;
                }
            } elseif ($isAccountant) {
                // Accountant can approve HOD level if status hasn't been approved yet
                if (in_array($imprestRequest->status, ['pending_hod', 'pending_ceo'])) {
                    $canHodApprove = true;
                }
            }
        }
        
        // CEO can only approve when status is pending_ceo (unless System Admin override)
        // But only if the action hasn't been performed yet
        $canCeoApprove = false;
        // Check if CEO approval has already been performed
        $ceoAlreadyApproved = !is_null($imprestRequest->ceo_approved_at);
        
        if (!$ceoAlreadyApproved) {
            if ($isSystemAdmin) {
                // System Admin can approve CEO level at any status (except completed)
                if (!in_array($imprestRequest->status, ['completed'])) {
                    $canCeoApprove = true;
                }
            } elseif ($isCEO) {
                // Regular CEO can approve when status is pending_ceo
                if ($imprestRequest->status === 'pending_ceo') {
                    $canCeoApprove = true;
                }
            }
        }
        
        // Accountant can only assign staff when status is approved and no assignments exist
        $canAssignStaff = false;
        $hasAssignments = $imprestRequest->assignments->count() > 0;
        
        if (!$hasAssignments) {
            if ($isAccountant) {
                if ($imprestRequest->status === 'approved') {
                    $canAssignStaff = true;
                } elseif ($isSystemAdmin && in_array($imprestRequest->status, ['approved', 'assigned', 'paid'])) {
                    // System Admin can also assign
                    $canAssignStaff = true;
                }
            }
        }
        
        // Accountant can only process payment when status is assigned
        // But only if payment hasn't been processed yet
        $canProcessPayment = false;
        $paymentAlreadyProcessed = !is_null($imprestRequest->paid_at);
        
        if (!$paymentAlreadyProcessed) {
            if ($isAccountant) {
                if ($imprestRequest->status === 'assigned') {
                    $canProcessPayment = true;
                } elseif ($isSystemAdmin && in_array($imprestRequest->status, ['assigned', 'paid'])) {
                    // System Admin can also process payment
                    $canProcessPayment = true;
                }
            }
        }
        
        // Accountant can only verify receipts when status is pending_receipt_verification
        // Check if there are unverified receipts
        $canVerifyReceipts = false;
        $hasUnverifiedReceipts = false;
        if ($imprestRequest->assignments) {
            foreach ($imprestRequest->assignments as $assignment) {
                if ($assignment->receipts) {
                    foreach ($assignment->receipts as $receipt) {
                        if (!$receipt->is_verified) {
                            $hasUnverifiedReceipts = true;
                            break 2;
                        }
                    }
                }
            }
        }
        
        if ($hasUnverifiedReceipts || $imprestRequest->status === 'pending_receipt_verification') {
            if ($isAccountant) {
                if ($imprestRequest->status === 'pending_receipt_verification') {
                    $canVerifyReceipts = true;
                } elseif ($isSystemAdmin && in_array($imprestRequest->status, ['paid', 'pending_receipt_verification'])) {
                    // System Admin can also verify receipts
                    $canVerifyReceipts = true;
                }
            }
        }
        
        $showActions = $canHodApprove || $canCeoApprove || $canAssignStaff || $canProcessPayment || $canVerifyReceipts;
    @endphp

    @if($showActions)
    <div class="card border-0 shadow-sm mb-4 action-card bg-primary">
        <div class="card-body text-white">
            <h5 class="text-white mb-3"><i class="bx bx-cog me-2"></i>Available Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                @if($canHodApprove)
                <button class="btn btn-light btn-lg" onclick="hodApprove({{ $imprestRequest->id }})">
                    <i class="bx bx-check me-1"></i>Approve (HOD)
                </button>
                @endif

                @if($canCeoApprove)
                <button class="btn btn-light btn-lg" onclick="ceoApprove({{ $imprestRequest->id }})">
                    <i class="bx bx-check-double me-1"></i>Final Approval (CEO)
                </button>
                @endif

                @if($canAssignStaff)
                <button class="btn btn-light btn-lg" onclick="openAssignStaff({{ $imprestRequest->id }})">
                    <i class="bx bx-user-plus me-1"></i>Assign Staff
                </button>
                @endif

                @if($canProcessPayment)
                <button class="btn btn-light btn-lg" onclick="openPayment({{ $imprestRequest->id }})">
                    <i class="bx bx-money me-1"></i>Process Payment
                </button>
                @endif

                @if($canVerifyReceipts)
                <button class="btn btn-light btn-lg" onclick="viewReceiptsForVerification({{ $imprestRequest->id }})">
                    <i class="bx bx-check-circle me-1"></i>Verify Receipts
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- PDF Download (Available to all authorized users) -->
    @php
        $hasAssignment = false;
        if ($isStaff && $imprestRequest->assignments) {
            $hasAssignment = $imprestRequest->assignments->contains(function($assignment) {
                return $assignment && $assignment->staff_id == auth()->id();
            });
        }
    @endphp
    @if($isSystemAdmin || $isAccountant || $isHOD || $isCEO || ($isStaff && $hasAssignment))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <a href="{{ route('imprest.pdf', $imprestRequest->id) }}" class="btn btn-outline-primary btn-lg" target="_blank">
                <i class="bx bx-file-blank me-1"></i>Download PDF
            </a>
        </div>
    </div>
    @endif

    <!-- Details Content -->
    @include('modules.finance.imprest-details', ['imprestRequest' => $imprestRequest])
</div>

@include('modules.finance.imprest-partials.modals')
@include('modules.finance.imprest-partials.scripts')
@endsection

