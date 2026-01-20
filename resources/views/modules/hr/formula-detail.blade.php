@extends('layouts.app')

@section('title', 'Formula Details - ' . $formula->name . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Professional Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-gradient-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-{{ $formula->is_locked ? 'lock' : 'calculator' }} me-2"></i>{{ $formula->name }}
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                {{ $formula->formula_type }} - Advanced Formula Configuration
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('payroll.formulas.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Formulas
                            </a>
                            @if($formula->is_locked)
                            <button type="button" class="btn btn-warning btn-lg shadow-sm" onclick="unlockFormula({{ $formula->id }})">
                                <i class="bx bx-lock-open me-1"></i>Unlock to Edit
                            </button>
                            @else
                            <button type="button" class="btn btn-success btn-lg shadow-sm" onclick="lockFormula({{ $formula->id }})">
                                <i class="bx bx-lock me-1"></i>Lock Formula
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($formula->is_locked)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="bx bx-lock fs-4 me-3"></i>
                    <div>
                        <strong>Formula is Locked</strong> - This formula is protected and requires OTP verification to edit.
                        @if($formula->locked_by)
                        <br><small class="text-muted">Locked by: {{ $formula->lockedByUser->name ?? 'N/A' }} on {{ $formula->locked_at ? $formula->locked_at->format('M d, Y H:i') : 'N/A' }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Formula Details -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="bx bx-code-alt me-2"></i>Formula Configuration
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="formulaForm">
                        <input type="hidden" id="formula_id" value="{{ $formula->id }}">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bx bx-code me-1"></i>Formula Code <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control font-monospace" 
                                id="formula_code" 
                                rows="4" 
                                required
                                {{ $formula->is_locked ? 'readonly' : '' }}
                                placeholder="Enter formula (e.g., min(gross_salary, 2000000) * 0.05)"
                            >{{ $formula->formula }}</textarea>
                            <small class="text-muted">Enter the calculation formula. Use variables like: gross_salary, basic_salary, etc.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bx bx-info-circle me-1"></i>Detailed Explanation <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="formula_explanation" 
                                rows="6" 
                                required
                                {{ $formula->is_locked ? 'readonly' : '' }}
                                placeholder="Explain how this formula works, what it calculates, and any important notes..."
                            >{{ $formula->explanation }}</textarea>
                            <small class="text-muted">Provide a comprehensive explanation of the formula, including calculation method, rates, ceilings, and any special conditions.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bx bx-cog me-1"></i>Parameters (JSON)
                            </label>
                            <textarea 
                                class="form-control font-monospace" 
                                id="formula_parameters" 
                                rows="8"
                                {{ $formula->is_locked ? 'readonly' : '' }}
                                placeholder='{"rate": 0.05, "ceiling": 2000000}'
                            >{{ $formula->parameters ? json_encode($formula->parameters, JSON_PRETTY_PRINT) : '' }}</textarea>
                            <small class="text-muted">Enter parameters as JSON format (e.g., rates, ceilings, brackets, etc.)</small>
                        </div>

                        @if($formula->is_locked)
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bx bx-key me-1"></i>OTP Code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="formula_otp" 
                                    maxlength="6" 
                                    placeholder="Enter 6-digit OTP"
                                    required
                                >
                                <button type="button" class="btn btn-outline-primary" onclick="requestUnlockOtp({{ $formula->id }})">
                                    <i class="bx bx-refresh me-1"></i>Request OTP
                                </button>
                            </div>
                            <small class="text-muted">Enter the OTP code to unlock and edit this formula</small>
                        </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" {{ $formula->is_locked ? 'disabled' : '' }}>
                                <i class="bx bx-save me-1"></i>Save Changes
                            </button>
                            <a href="{{ route('payroll.formulas.index') }}" class="btn btn-secondary btn-lg">
                                <i class="bx bx-x me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Formula Information Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-info-circle me-2"></i>Formula Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Formula Type</small>
                        <strong class="text-primary">{{ $formula->formula_type }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        @if($formula->is_locked)
                        <span class="badge bg-warning">
                            <i class="bx bx-lock me-1"></i>Locked
                        </span>
                        @else
                        <span class="badge bg-success">
                            <i class="bx bx-lock-open me-1"></i>Unlocked
                        </span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Created</small>
                        <strong>{{ $formula->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <strong>{{ $formula->updated_at->format('M d, Y H:i') }}</strong>
                    </div>
                    @if($formula->locked_by)
                    <div class="mb-3">
                        <small class="text-muted d-block">Locked By</small>
                        <strong>{{ $formula->lockedByUser->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Locked At</small>
                        <strong>{{ $formula->locked_at ? $formula->locked_at->format('M d, Y H:i') : 'N/A' }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-bolt-circle me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($formula->is_locked)
                        <button type="button" class="btn btn-warning" onclick="unlockFormula({{ $formula->id }})">
                            <i class="bx bx-lock-open me-1"></i>Unlock Formula
                        </button>
                        @else
                        <button type="button" class="btn btn-success" onclick="lockFormula({{ $formula->id }})">
                            <i class="bx bx-lock me-1"></i>Lock Formula
                        </button>
                        @endif
                        <a href="{{ route('payroll.formulas.index') }}" class="btn btn-outline-primary">
                            <i class="bx bx-arrow-back me-1"></i>Back to All Formulas
                        </a>
                        <a href="{{ route('modules.hr.payroll') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-calculator me-1"></i>Payroll Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Parameters Preview -->
            @if($formula->parameters)
            <div class="card border-0 shadow-lg mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-cog me-2"></i>Parameters Preview
                    </h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($formula->parameters, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.font-monospace {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('formulaForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formulaId = document.getElementById('formula_id').value;
    const formula = document.getElementById('formula_code').value;
    const explanation = document.getElementById('formula_explanation').value;
    const parametersText = document.getElementById('formula_parameters').value;
    const otp = document.getElementById('formula_otp')?.value || null;
    
    if (!formula || !explanation) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please fill in all required fields',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
        return;
    }
    
    let parameters = null;
    if (parametersText.trim()) {
        try {
            parameters = JSON.parse(parametersText);
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid JSON',
                text: 'Parameters must be valid JSON format',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
            return;
        }
    }
    
    const data = {
        formula: formula,
        explanation: explanation,
        parameters: parameters,
        otp: otp
    };
    
    Swal.fire({
        title: 'Saving...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        customClass: {
            container: 'swal2-container',
            popup: 'swal2-popup'
        }
    });
    
    fetch(`{{ url('payroll/formulas') }}/${formulaId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Formula updated successfully',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to update formula',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while updating the formula',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
    });
});

function lockFormula(formulaId) {
    Swal.fire({
        title: 'Lock Formula?',
        text: 'This will prevent unauthorized changes. You will need OTP to unlock it.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Lock it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        customClass: {
            container: 'swal2-container',
            popup: 'swal2-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('payroll/formulas') }}/${formulaId}/lock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Formula locked successfully',
                        customClass: {
                            container: 'swal2-container',
                            popup: 'swal2-popup'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to lock formula',
                        customClass: {
                            container: 'swal2-container',
                            popup: 'swal2-popup'
                        }
                    });
                }
            });
        }
    });
}

function unlockFormula(formulaId) {
    Swal.fire({
        title: 'Unlock Formula',
        html: `
            <p>To unlock this formula, you need to enter the OTP code.</p>
            <input type="text" class="form-control mt-3" id="unlock_otp" maxlength="6" placeholder="Enter 6-digit OTP">
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="requestUnlockOtp(${formulaId})">
                <i class="bx bx-refresh me-1"></i>Request New OTP
            </button>
        `,
        showCancelButton: true,
        confirmButtonText: 'Unlock',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        customClass: {
            container: 'swal2-container',
            popup: 'swal2-popup'
        },
        preConfirm: () => {
            const otp = document.getElementById('unlock_otp').value;
            if (!otp || otp.length !== 6) {
                Swal.showValidationMessage('Please enter a valid 6-digit OTP');
                return false;
            }
            return { otp: otp };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            fetch(`{{ url('payroll/formulas') }}/${formulaId}/unlock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Formula unlocked successfully',
                        customClass: {
                            container: 'swal2-container',
                            popup: 'swal2-popup'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to unlock formula. Invalid or expired OTP.',
                        customClass: {
                            container: 'swal2-container',
                            popup: 'swal2-popup'
                        }
                    });
                }
            });
        }
    });
}

function requestUnlockOtp(formulaId) {
    Swal.fire({
        title: 'Requesting OTP...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        customClass: {
            container: 'swal2-container',
            popup: 'swal2-popup'
        }
    });
    
    fetch(`{{ url('payroll/formulas') }}/${formulaId}/unlock-otp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'OTP Generated',
                html: `
                    <p>Your OTP code is: <strong>${data.otp}</strong></p>
                    <p class="text-muted small">Valid for 10 minutes. Expires at: ${data.expires_at}</p>
                    <p class="text-warning small">⚠️ In production, this will be sent via email/SMS</p>
                `,
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to generate OTP',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while generating OTP',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
    });
}
</script>
@endpush
@endsection






