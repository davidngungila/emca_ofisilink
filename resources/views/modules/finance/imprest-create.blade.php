@extends('layouts.app')

@section('title', 'Create New Imprest Request')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Create New Imprest Request</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item active">Create New Request</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .create-card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: none;
    }
    
    .form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
    
    .priority-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .priority-normal { background: #e3f2fd; color: #1976d2; }
    .priority-high { background: #fff3e0; color: #f57c00; }
    .priority-urgent { background: #ffebee; color: #d32f2f; }
    
    /* Hover Info Tooltip Styles */
    .hover-info-tooltip {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: help;
        z-index: 10;
    }
    
    .hover-info-tooltip i {
        font-size: 1.1rem;
        color: #940000;
        transition: color 0.2s;
    }
    
    .hover-info-tooltip:hover i {
        color: #7a0000;
    }
    
    .hover-info-text {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        bottom: 125%;
        right: 0;
        background-color: #940000;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        white-space: nowrap;
        font-size: 0.875rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: opacity 0.3s, visibility 0.3s;
        z-index: 1000;
        pointer-events: none;
        min-width: 200px;
    }
    
    .hover-info-text::after {
        content: "";
        position: absolute;
        top: 100%;
        right: 15px;
        border-width: 5px;
        border-style: solid;
        border-color: #940000 transparent transparent transparent;
    }
    
    .hover-info-tooltip:hover .hover-info-text {
        visibility: visible;
        opacity: 1;
    }
    
    /* For inline tooltips */
    .position-relative.d-inline-block .hover-info-tooltip {
        position: relative;
        display: inline-block;
        top: auto;
        right: auto;
        transform: none;
        margin-left: 5px;
    }
    
    .position-relative.d-inline-block .hover-info-text {
        bottom: auto;
        top: 125%;
        right: auto;
        left: 0;
    }
    
    .position-relative.d-inline-block .hover-info-text::after {
        top: auto;
        bottom: 100%;
        right: auto;
        left: 15px;
        border-color: transparent transparent #940000 transparent;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-plus-circle me-2"></i>Create New Imprest Request
                    </h2>
                    <p class="mb-0 opacity-90">Fill in the details below to create a new imprest request</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('imprest.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm create-card">
        <div class="card-body p-4">
            <form id="imprestCreateForm">
                @csrf
                
                <!-- Purpose Section -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="bx bx-target-lock me-2 text-primary"></i>Purpose & Details
                    </h5>
                    <div class="mb-3">
                        <label class="form-label">
                            Purpose <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-lg" name="purpose" id="purpose" required placeholder="e.g., Training, Field Work, Conference">
                            <div class="hover-info-tooltip">
                                <i class="bx bx-info-circle"></i>
                                <span class="hover-info-text">Provide a clear purpose for this imprest request</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <div class="position-relative">
                            <textarea class="form-control" name="description" id="description" rows="4" placeholder="Additional details about this imprest request..."></textarea>
                            <div class="hover-info-tooltip" style="top: 10px; transform: none;">
                                <i class="bx bx-info-circle"></i>
                                <span class="hover-info-text">Provide any additional information that might be helpful</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amount Section -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="bx bx-money me-2 text-success"></i>Amount
                    </h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Amount (TZS) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-money text-primary"></i>
                                </span>
                                <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="1" required placeholder="Enter amount">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expected Return Date Section -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="bx bx-calendar me-2 text-info"></i>Expected Return Date
                    </h5>
                    <div class="mb-3">
                        <label class="form-label">Expected Return Date</label>
                        <div class="position-relative">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-calendar text-primary"></i>
                                </span>
                                <input type="date" class="form-control" name="expected_return_date" id="expected_return_date" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="position-relative d-inline-block mt-1">
                                <div class="hover-info-tooltip">
                                    <i class="bx bx-info-circle"></i>
                                    <span class="hover-info-text">Optional: When do you expect to return/receive this amount?</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('imprest.index') }}" class="btn btn-secondary btn-lg">
                        <i class="bx bx-x me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="bx bx-check-circle me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('imprestCreateForm');
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
        
        const formData = new FormData(form);
        
        fetch('{{ route("imprest.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const result = await response.json();
            
            if (response.ok && result.success) {
                // Show success message
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Imprest request created successfully!', { duration: 5000 });
                } else {
                    alert('Imprest request created successfully!');
                }
                
                // Redirect to dashboard after 1 second
                setTimeout(() => {
                    window.location.href = '{{ route("imprest.index") }}';
                }, 1000);
            } else {
                // Show error message
                let errorMsg = result.message || 'Failed to create imprest request';
                
                if (result.errors) {
                    const errorMessages = [];
                    Object.keys(result.errors).forEach(key => {
                        if (Array.isArray(result.errors[key])) {
                            result.errors[key].forEach(err => {
                                errorMessages.push(`${key}: ${err}`);
                            });
                        } else {
                            errorMessages.push(`${key}: ${result.errors[key]}`);
                        }
                    });
                    if (errorMessages.length > 0) {
                        errorMsg = errorMessages.join('\n');
                    }
                }
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || 'Network error occurred';
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
            } else {
                alert('Error: ' + errorMsg);
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endpush

