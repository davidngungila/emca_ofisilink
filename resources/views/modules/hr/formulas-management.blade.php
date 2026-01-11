@extends('layouts.app')

@section('title', 'Payroll Formulas Management - OfisiLink')

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
                                <i class="bx bx-calculator me-2"></i>Payroll Formulas Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage and configure all payroll calculation formulas with advanced settings and OTP protection
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Payroll
                            </a>
                            <button type="button" class="btn btn-light btn-lg shadow-sm" onclick="loadFormulas()">
                                <i class="bx bx-refresh me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-calculator fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Formulas</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalFormulas ?? 0 }}</h3>
                            <small class="text-muted">Active Formulas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success">
                            <i class="bx bx-lock-open fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Unlocked</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $unlockedFormulas ?? 0 }}</h3>
                            <small class="text-muted">Available for Edit</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-lock fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Locked</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $lockedFormulas ?? 0 }}</h3>
                            <small class="text-muted">Protected with OTP</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Formula Management Guidelines</h6>
                        <ul class="mb-0 ps-3">
                            <li>All payroll calculations reference formulas from this centralized location</li>
                            <li>Lock formulas to prevent unauthorized changes - OTP verification required to unlock</li>
                            <li>Each formula has its own detailed page for advanced configuration</li>
                            <li>Changes to formulas affect all future payroll calculations</li>
                            <li>Review formula explanations and parameters before making changes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulas Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="bx bx-list-ul me-2"></i>All Payroll Formulas
                        </h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-white">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchFormulas" placeholder="Search formulas...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="formulasContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading formulas...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading formulas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.formula-card {
    transition: all 0.3s ease;
    border-left: 4px solid var(--bs-primary) !important;
}
.formula-card.locked {
    border-left-color: #f59e0b !important;
}
.formula-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
let allFormulas = [];
let filteredFormulas = [];

// Load formulas on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFormulas();
    
    // Search functionality
    document.getElementById('searchFormulas')?.addEventListener('input', function() {
        filterFormulas();
    });
});

function loadFormulas() {
    const container = document.getElementById('formulasContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading formulas...</span>
            </div>
            <p class="mt-3 text-muted">Loading formulas...</p>
        </div>
    `;
    
    fetch('{{ route("payroll.formulas.api") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allFormulas = data.formulas || [];
            filteredFormulas = allFormulas;
            renderFormulas(filteredFormulas);
        } else {
            container.innerHTML = '<div class="alert alert-danger">Failed to load formulas</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<div class="alert alert-danger">Error loading formulas</div>';
    });
}

function filterFormulas() {
    const searchTerm = document.getElementById('searchFormulas')?.value.toLowerCase() || '';
    
    filteredFormulas = allFormulas.filter(formula => {
        return formula.name.toLowerCase().includes(searchTerm) || 
               formula.formula_type.toLowerCase().includes(searchTerm) ||
               formula.explanation.toLowerCase().includes(searchTerm);
    });
    
    renderFormulas(filteredFormulas);
}

function renderFormulas(formulas) {
    const container = document.getElementById('formulasContainer');
    if (!container) return;
    
    if (!formulas || formulas.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center">
                <i class="bx bx-info-circle fs-1 mb-3"></i>
                <p class="mb-0">No formulas found. Please initialize formulas first.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="row g-4">
            ${formulas.map(formula => {
                const isLocked = formula.is_locked || false;
                const parameters = formula.parameters ? JSON.stringify(formula.parameters, null, 2) : null;
                
                return `
                    <div class="col-lg-6 col-md-6">
                        <div class="card formula-card ${isLocked ? 'locked' : ''} h-100 hover-lift">
                            <div class="card-header ${isLocked ? 'bg-warning' : 'bg-primary'} text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-white fw-bold">
                                            <i class="bx bx-${isLocked ? 'lock' : 'calculator'} me-2"></i>
                                            ${escapeHtml(formula.name)}
                                        </h6>
                                        <small class="text-white-50">${escapeHtml(formula.formula_type)}</small>
                                    </div>
                                    <div>
                                        ${isLocked ? 
                                            `<span class="badge bg-light text-dark">
                                                <i class="bx bx-lock me-1"></i>Locked
                                            </span>` : 
                                            `<span class="badge bg-light text-success">
                                                <i class="bx bx-lock-open me-1"></i>Unlocked
                                            </span>`
                                        }
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold text-primary mb-2">
                                        <i class="bx bx-code-alt me-1"></i>Formula:
                                    </h6>
                                    <div class="bg-light p-3 rounded border">
                                        <code class="text-primary">${escapeHtml(formula.formula)}</code>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold text-info mb-2">
                                        <i class="bx bx-info-circle me-1"></i>Explanation:
                                    </h6>
                                    <p class="text-muted mb-0 small">${escapeHtml(formula.explanation)}</p>
                                </div>
                                
                                ${parameters && parameters !== '{}' ? `
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-secondary mb-2">
                                            <i class="bx bx-cog me-1"></i>Parameters:
                                        </h6>
                                        <pre class="bg-light p-2 rounded border small mb-0" style="max-height: 150px; overflow-y: auto;"><code>${escapeHtml(parameters)}</code></pre>
                                    </div>
                                ` : ''}
                                
                                <div class="d-flex gap-2 mt-3">
                                    <a href="{{ url('payroll/formulas') }}/${formula.id}" class="btn btn-primary btn-sm flex-fill">
                                        <i class="bx bx-show me-1"></i>View Details
                                    </a>
                                    ${isLocked ? 
                                        `<button type="button" class="btn btn-warning btn-sm" onclick="unlockFormula(${formula.id})">
                                            <i class="bx bx-lock-open me-1"></i>Unlock
                                        </button>` :
                                        `<button type="button" class="btn btn-success btn-sm" onclick="lockFormula(${formula.id})">
                                            <i class="bx bx-lock me-1"></i>Lock
                                        </button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

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
                    });
                    loadFormulas();
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
                    });
                    loadFormulas();
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

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection


