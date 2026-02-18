@extends('layouts.app')

@section('title', 'Allowance Management - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-info" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-money me-2"></i>Allowance Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage monthly allowances for employees
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Payroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-info" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-info">
                            <i class="bx bx-money fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total (Allowances + Benefits)</h6>
                            <h3 class="mb-0 fw-bold text-info">TZS {{ number_format($totalAmount ?? 0, 0) }}</h3>
                            <small class="text-muted">TZS {{ number_format($totalAllowances ?? 0, 0) }} Allowances</small>
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
                            <i class="bx bx-group fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Beneficiaries</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $employeeCount ?? 0 }}</h3>
                            <small class="text-muted">{{ $beneficiaryCount ?? 0 }} With Auto Benefits</small>
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
                            <i class="bx bx-bar-chart fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Avg Allowance</h6>
                            <h3 class="mb-0 fw-bold text-warning">TZS {{ number_format($avgAmount ?? 0, 0) }}</h3>
                            <small class="text-muted">Per Employee</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Month Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <form method="GET" action="{{ route('payroll.allowance.index') }}" class="d-flex gap-3 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label">Select Month</label>
                            <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" required>
                        </div>
                        <button type="submit" class="btn btn-info">
                            <i class="bx bx-search me-1"></i>Load Month
                        </button>
                        <button type="button" class="btn btn-primary" onclick="showAddAllowanceModal()">
                            <i class="bx bx-plus me-1"></i>Add Allowance
                        </button>
                        <button type="button" class="btn btn-success" onclick="showBulkAllowanceModal()">
                            <i class="bx bx-layer me-1"></i>Bulk Create
                        </button>
                        <button type="button" class="btn btn-warning" onclick="downloadAllowanceTemplate()">
                            <i class="bx bx-download me-1"></i>Download Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Allowance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="bx bx-table me-2"></i>Allowance Records - {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th class="text-end">Allowances</th>
                                    <th class="text-end">Auto Benefits</th>
                                    <th class="text-end">Total Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    @php
                                        $allowance = $allowances[$employee->id] ?? null;
                                        $allowanceAmount = $allowance ? $allowance->amount : 0;
                                        
                                        $totalBenefits = 0;
                                        $benefitDetails = [];
                                        if ($employee->benefits) {
                                            foreach($employee->benefits as $benefit) {
                                                $bAmount = 0;
                                                if ($benefit->amount > 0) {
                                                    $bAmount = $benefit->amount;
                                                } elseif ($benefit->percentage > 0 && ($employee->employee->salary ?? 0) > 0) {
                                                    $bAmount = (($employee->employee->salary ?? 0) * $benefit->percentage) / 100;
                                                }
                                                $totalBenefits += $bAmount;
                                                if ($bAmount > 0) {
                                                    $benefitDetails[] = $benefit->benefit_name . ': ' . number_format($bAmount, 0);
                                                }
                                            }
                                        }
                                        $totalRow = $allowanceAmount + $totalBenefits;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-info">{{ substr($employee->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $employee->name }}</div>
                                                    <small class="text-muted">{{ $employee->employee->employee_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            @if($allowance)
                                                <div class="fw-bold text-info">TZS {{ number_format($allowance->amount, 0) }}</div>
                                                <small class="text-muted">{{ $allowance->allowance_type ?? 'Allowance' }}</small>
                                            @else
                                                <span class="text-muted">TZS 0</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($totalBenefits > 0)
                                                <div class="fw-bold text-success">TZS {{ number_format($totalBenefits, 0) }}</div>
                                                <small class="text-muted" title="{{ implode(', ', $benefitDetails) }}">
                                                    {{ count($benefitDetails) }} Active Benefits
                                                </small>
                                            @else
                                                <span class="text-muted">TZS 0</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-bold {{ $totalRow > 0 ? 'text-primary' : 'text-muted' }}">
                                                TZS {{ number_format($totalRow, 0) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-primary" onclick="viewEmployeePayDetails({{ $employee->id }}, '{{ addslashes($employee->name) }}', '{{ $employee->employee->employee_id ?? 'N/A' }}', {{ $allowanceAmount }}, '{{ addslashes($allowance->allowance_type ?? 'N/A') }}', '{{ addslashes($allowance->description ?? 'N/A') }}', {{ $totalBenefits }}, '{{ addslashes(implode('|', $benefitDetails)) }}')" title="View Details">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                @if($allowance)
                                                    <button class="btn btn-sm btn-info" onclick="editAllowance({{ $allowance->id }}, {{ $employee->id }}, '{{ $employee->name }}', {{ $allowance->amount }}, '{{ $allowance->allowance_type ?? '' }}', '{{ $allowance->description ?? '' }}')" title="Edit Allowance">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteAllowance({{ $allowance->id }})" title="Delete Allowance">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-success" onclick="addAllowance({{ $employee->id }}, '{{ $employee->name }}')" title="Add Allowance">
                                                        <i class="bx bx-plus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No employees found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Allowance Modal -->
<div class="modal fade" id="allowanceModal" tabindex="-1" style="z-index: 10090 !important;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="allowanceModalTitle">Add Allowance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="allowanceForm">
                <div class="modal-body">
                    <input type="hidden" id="allowance_id" name="allowance_id">
                    <input type="hidden" id="employee_id" name="employee_id">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" id="employee_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control" step="1000" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allowance Type</label>
                        <select name="allowance_type" id="allowance_type" class="form-select">
                            <option value="">Select Type</option>
                            <option value="Transport">Transport</option>
                            <option value="Housing">Housing</option>
                            <option value="Medical">Medical</option>
                            <option value="Meal">Meal</option>
                            <option value="Communication">Communication</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" style="z-index: 10090 !important;">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-detail me-2"></i>Payment Details Breakdown</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl mx-auto mb-2">
                        <span class="avatar-initial rounded-circle bg-label-primary shadow-sm" style="font-size: 2rem;" id="view_avatar"></span>
                    </div>
                    <h5 class="mb-0 fw-bold" id="view_employee_name"></h5>
                    <small class="text-muted" id="view_employee_id"></small>
                </div>

                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Manual Allowance</span>
                            <span class="fw-bold text-info" id="view_allowance_amount"></span>
                        </div>
                        <div class="ps-3 border-start border-info border-3">
                            <p class="mb-1 small"><strong>Type:</strong> <span id="view_allowance_type"></span></p>
                            <p class="mb-0 small text-muted italic" id="view_allowance_desc"></p>
                        </div>
                    </div>
                </div>

                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Automatic Benefits</span>
                            <span class="fw-bold text-success" id="view_benefits_total"></span>
                        </div>
                        <div id="view_benefits_list" class="small">
                            <!-- Benefits will be inserted here -->
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center p-3 bg-primary bg-opacity-10 rounded">
                    <h5 class="mb-0 fw-bold">Grand Total</h5>
                    <h4 class="mb-0 fw-bold text-primary" id="view_grand_total"></h4>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Bulk Create Allowance Modal -->
<div class="modal fade" id="bulkAllowanceModal" tabindex="-1" style="z-index: 10090 !important;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-layer me-2"></i>Bulk Create Allowance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAllowanceForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Bulk Create Allowance</strong><br>
                        Upload a CSV or Excel file to create multiple allowance records at once. CSV format is recommended.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="bulk_allowance_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                        <small class="text-muted">Accepted formats: CSV, XLSX, XLS</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Format</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <small class="text-muted">
                                    <strong>Template includes all active employees with their details:</strong><br>
                                    - employee_code: Employee Code like EMP006 (pre-filled, primary identifier)<br>
                                    - employee_id: System Employee ID (pre-filled, for reference)<br>
                                    - employee_name: Full Name (pre-filled)<br>
                                    - department: Department Name (pre-filled)<br>
                                    - basic_salary: Current Basic Salary (pre-filled)<br>
                                    <br>
                                    <strong>You only need to fill:</strong><br>
                                    - amount: Allowance amount (numeric, required)<br>
                                    - allowance_type (optional): Transport, Housing, Medical, Meal, Communication, Other<br>
                                    - description (optional): Description of allowance
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bx bx-upload me-1"></i>Upload & Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure modals appear in front of all elements */
    .modal {
        z-index: 10090 !important;
    }
    .modal-backdrop {
        z-index: 10089 !important;
    }
    .modal.show {
        z-index: 10090 !important;
    }
    
    /* SweetAlert2 z-index */
    .swal2-container {
        z-index: 100000 !important;
    }
    .swal2-popup {
        z-index: 100001 !important;
    }
    .swal2-backdrop-show {
        z-index: 99999 !important;
    }
</style>
@endpush

@push('scripts')
<script>
let allowanceModal = new bootstrap.Modal(document.getElementById('allowanceModal'));
let bulkAllowanceModal = new bootstrap.Modal(document.getElementById('bulkAllowanceModal'));
let viewDetailsModal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));

function viewEmployeePayDetails(id, name, empCode, allowanceAmt, allowanceType, allowanceDesc, benefitsTotal, benefitsListStr) {
    document.getElementById('view_employee_name').textContent = name;
    document.getElementById('view_employee_id').textContent = empCode;
    document.getElementById('view_avatar').textContent = name.charAt(0);
    
    document.getElementById('view_allowance_amount').textContent = 'TZS ' + allowanceAmt.toLocaleString();
    document.getElementById('view_allowance_type').textContent = allowanceType;
    document.getElementById('view_allowance_desc').textContent = allowanceDesc;
    
    document.getElementById('view_benefits_total').textContent = 'TZS ' + benefitsTotal.toLocaleString();
    
    const benefitsList = document.getElementById('view_benefits_list');
    benefitsList.innerHTML = '';
    
    if (benefitsListStr) {
        const benefits = benefitsListStr.split('|');
        benefits.forEach(benefit => {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between mb-1 text-muted';
            const parts = benefit.split(':');
            div.innerHTML = `<span>${parts[0]}</span> <span class="fw-medium">${parts[1]}</span>`;
            benefitsList.appendChild(div);
        });
    } else {
        benefitsList.innerHTML = '<div class="text-muted italic">No active benefits found</div>';
    }
    
    const grandTotal = allowanceAmt + benefitsTotal;
    document.getElementById('view_grand_total').textContent = 'TZS ' + grandTotal.toLocaleString();
    
    viewDetailsModal.show();
}

function showBulkAllowanceModal() {
    document.getElementById('bulkAllowanceForm').reset();
    bulkAllowanceModal.show();
}

function downloadAllowanceTemplate() {
    window.location.href = '/payroll/allowance/template';
}

function showAddAllowanceModal() {
    document.getElementById('allowanceModalTitle').textContent = 'Add Allowance';
    document.getElementById('allowanceForm').reset();
    document.getElementById('allowance_id').value = '';
    document.getElementById('employee_id').value = '';
    document.getElementById('employee_name').value = '';
    allowanceModal.show();
}

function addAllowance(employeeId, employeeName) {
    document.getElementById('allowanceModalTitle').textContent = 'Add Allowance';
    document.getElementById('allowanceForm').reset();
    document.getElementById('allowance_id').value = '';
    document.getElementById('employee_id').value = employeeId;
    document.getElementById('employee_name').value = employeeName;
    allowanceModal.show();
}

function editAllowance(id, employeeId, employeeName, amount, allowanceType, description) {
    document.getElementById('allowanceModalTitle').textContent = 'Edit Allowance';
    document.getElementById('allowance_id').value = id;
    document.getElementById('employee_id').value = employeeId;
    document.getElementById('employee_name').value = employeeName;
    document.getElementById('amount').value = amount;
    document.getElementById('allowance_type').value = allowanceType || '';
    document.getElementById('description').value = description || '';
    allowanceModal.show();
}

function deleteAllowance(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will deactivate the allowance record.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/payroll/allowance/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deactivated!', response.message, 'success').then(() => location.reload());
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to deactivate', 'error');
                }
            });
        }
    });
}

$('#allowanceForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const allowanceId = $('#allowance_id').val();
    const url = allowanceId ? `/payroll/allowance/${allowanceId}` : '/payroll/allowance';
    const method = allowanceId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to save', 'error');
        }
    });
});

$('#bulkAllowanceForm').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Processing...',
        text: 'Uploading and processing file...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/payroll/allowance/bulk',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let message = `Successfully processed ${response.created || 0} new and ${response.updated || 0} updated records.`;
                if (response.errors && response.errors.length > 0) {
                    message += '\n\nErrors:\n' + response.errors.join('\n');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Bulk Upload Complete',
                    html: message.replace(/\n/g, '<br>'),
                    width: '600px'
                }).then(() => location.reload());
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to process bulk upload', 'error');
        }
    });
});
</script>
@endpush
@endsection

