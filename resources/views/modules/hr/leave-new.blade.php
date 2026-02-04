@extends('layouts.app')

@section('title', 'New Leave Request - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background-color: #940000; color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-plus-circle me-2"></i>NEW LEAVE REQUEST
                            </h4>
                            <p class="card-text text-white-50 mb-0">submit a new leave request</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Leave Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Leave Request Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="requestLeaveForm" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="action" id="request_action" value="request_leave">
                        <input type="hidden" name="request_id" id="request_id" value="">
                        
                        <h6>EMPLOYEE DETAILS</h6>
                        <div class="row bg-light p-3 rounded mb-4">
                            <div class="col-md-4"><strong>Name:</strong> {{ $user->name }}</div>
                            <div class="col-md-4"><strong>Department:</strong> {{ $user->primaryDepartment->name ?? 'N/A' }}</div>
                            <div class="col-md-4"><strong>Position:</strong> {{ $user->employee->position ?? 'N/A' }}</div>
                        </div>
                        
                        <h6>LEAVE DETAILS</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Leave Type *</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" data-max-days="{{ $type->max_days_per_year ?? $type->max_days ?? 0 }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div class="position-relative d-inline-block mt-1" id="leave-type-info" style="display: none;">
                                    <div class="hover-info-tooltip">
                                        <i class="bx bx-info-circle text-custom-primary"></i>
                                        <span class="hover-info-text"><span id="max-days-display">0</span> days available for this leave type</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Date * <small class="text-muted">(Auto-calculated)</small></label>
                                <div class="position-relative">
                                    <input type="date" name="end_date" id="end_date" class="form-control" required min="{{ date('Y-m-d') }}" readonly>
                                    <div class="hover-info-tooltip">
                                        <i class="bx bx-info-circle text-custom-primary"></i>
                                        <span class="hover-info-text">End date is automatically calculated based on leave type</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Days <small class="text-muted">(Auto-calculated)</small></label>
                                <div class="position-relative">
                                    <input type="text" name="total_days" id="total_days" class="form-control" readonly>
                                    <div class="hover-info-tooltip">
                                        <i class="bx bx-info-circle text-custom-primary"></i>
                                        <span class="hover-info-text">Automatically calculated from dates</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Location During Leave *</label>
                                <input type="text" name="leave_location" id="leave_location" class="form-control" placeholder="e.g., Arusha, Tanzania" value="{{ $user->place_of_domicile ?? '' }}" required readonly>
                                <div class="position-relative d-inline-block mt-1">
                                    <div class="hover-info-tooltip">
                                        <i class="bx bx-info-circle text-custom-primary"></i>
                                        <span class="hover-info-text">Auto-filled from your place of domicile in personal particulars</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reason for Leave *</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Please provide a detailed reason for your leave..." required></textarea>
                        </div>
                        
                        <h6 class="mt-4">DEPENDENTS (IF APPLICABLE FOR FARE/NAULI)</h6>
                        <div id="dependents-container" class="mb-3"></div>
                        <button type="button" class="btn btn-sm btn-outline-custom" id="add-dependent-btn">
                            <i class="bx bx-plus me-1"></i>Add Dependent
                        </button>
                        
                        <div class="mt-4">
                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-outline-custom">
                                <i class="bx bx-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-custom-primary">
                                <i class="bx bx-check me-1"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .recommendation-card {
        border-left: 4px solid #940000;
        transition: transform 0.2s;
    }
    .recommendation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .optimal-period-card {
        border-left: 4px solid #940000;
    }
    .bg-custom-primary { background-color: #940000 !important; color: white !important; }
    .btn-custom-primary { background-color: #940000 !important; border-color: #940000 !important; color: white !important; }
    .btn-custom-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; color: white !important; }
    .btn-outline-custom { border-color: #940000 !important; color: #940000 !important; background-color: white !important; }
    .btn-outline-custom:hover { background-color: #940000 !important; color: white !important; }
    .text-custom-primary { color: #940000 !important; }
    .border-left-custom { border-left: 4px solid #940000 !important; }
    .use-recommendation-btn {
        white-space: nowrap;
    }
    .dependent-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
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
    
    /* For leave type info that's inline */
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

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Fallback for SweetAlert2
if (typeof window.Swal === 'undefined') {
    window.Swal = {
        fire: function(opts) {
            alert(opts.title + (opts.text ? '\n' + opts.text : ''));
            return Promise.resolve({ isConfirmed: true });
        }
    };
}

const csrfToken = $('meta[name="csrf-token"]').attr('content');
let dependentCount = 0;
let selectedLeaveTypeMaxDays = 0;

// Removed: Load leave recommendations function - recommendations section removed

// Add dependent
$('#add-dependent-btn').on('click', function() {
    dependentCount++;
    const dependentHtml = `
        <div class="dependent-item" id="dependent-${dependentCount}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Dependent Name *</label>
                    <input type="text" name="dependents[${dependentCount}][name]" class="form-control form-control-sm" required placeholder="Full name">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Relationship *</label>
                    <select name="dependents[${dependentCount}][relationship]" class="form-select form-select-sm" required>
                        <option value="">-- Select --</option>
                        <option value="spouse">Spouse</option>
                        <option value="child">Child</option>
                        <option value="parent">Parent</option>
                        <option value="sibling">Sibling</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Fare Amount (TZS)</label>
                    <input type="number" name="dependents[${dependentCount}][fare_amount]" class="form-control form-control-sm" 
                           placeholder="0.00" step="0.01" min="0" value="0" readonly>
                    <small class="text-muted"><i class="bx bx-info-circle me-1"></i>HR will assign fare amount during review</small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-custom-primary w-100" onclick="removeDependent(${dependentCount})">
                        <i class="bx bx-trash"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#dependents-container').append(dependentHtml);
});

// Remove dependent
function removeDependent(id) {
    $(`#dependent-${id}`).remove();
}

// Store leave types data
const leaveTypesData = {
    @foreach($leaveTypes as $type)
    {{ $type->id }}: {
        maxDays: {{ $type->max_days_per_year ?? $type->max_days ?? 0 }},
        name: "{{ $type->name }}"
    },
    @endforeach
};

// Handle Leave Type selection
$('#leave_type_id').on('change', function() {
    const leaveTypeId = $(this).val();
    const selectedOption = $(this).find('option:selected');
    
    if (leaveTypeId && leaveTypeId !== '') {
        selectedLeaveTypeMaxDays = parseInt(selectedOption.data('max-days')) || 0;
        
        // Show max days info
        if (selectedLeaveTypeMaxDays > 0) {
            $('#leave-type-info').show();
            $('#max-days-display').text(selectedLeaveTypeMaxDays);
        } else {
            $('#leave-type-info').hide();
        }
        
        // If start date is already selected, auto-calculate end date
        const startDate = $('#start_date').val();
        if (startDate && selectedLeaveTypeMaxDays > 0) {
            calculateEndDate(startDate, selectedLeaveTypeMaxDays);
        }
    } else {
        selectedLeaveTypeMaxDays = 0;
        $('#leave-type-info').hide();
        $('#end_date').val('');
        $('#total_days').val('');
    }
});

// Auto-calculate End Date when Start Date is selected/changed
$('#start_date').on('change', function() {
    const startDate = $(this).val();
    
    if (!startDate) {
        $('#end_date').val('');
        $('#total_days').val('');
        return;
    }
    
    // Check if leave type is selected
    const leaveTypeId = $('#leave_type_id').val();
    if (!leaveTypeId || leaveTypeId === '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Select Leave Type First',
                text: 'Please select a leave type before choosing start date.',
                timer: 3000,
                showConfirmButton: false
            });
        }
        $(this).val('');
        return;
    }
    
    // Get max days from selected option
    const selectedOption = $('#leave_type_id').find('option:selected');
    selectedLeaveTypeMaxDays = parseInt(selectedOption.data('max-days')) || 0;
    
    if (selectedLeaveTypeMaxDays > 0) {
        calculateEndDate(startDate, selectedLeaveTypeMaxDays);
    } else {
        $('#end_date').val('');
        $('#total_days').val('');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Leave Type',
                text: 'Selected leave type has no maximum days configured.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    }
});

// Function to calculate and set End Date
function calculateEndDate(startDate, maxDays) {
    if (!startDate || maxDays <= 0) {
        return;
    }
    
    const start = new Date(startDate);
    if (isNaN(start.getTime())) {
        return;
    }
    
    // Calculate end date: start date + (maxDays - 1) days
    // Example: If maxDays is 7 and start is Jan 1, end should be Jan 7 (7 days total)
    const end = new Date(start);
    end.setDate(end.getDate() + (maxDays - 1));
    
    // Format as YYYY-MM-DD
    const endDateStr = end.toISOString().split('T')[0];
    
    // Set end date
    $('#end_date').val(endDateStr);
    
    // Calculate and set total days
    const days = maxDays;
    $('#total_days').val(days);
    
    // Show success message
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'End Date Calculated',
            text: `End date set to ${endDateStr} (${days} days)`,
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

// Allow manual override of end date (optional - remove readonly if user wants to adjust)
// Uncomment below if you want users to be able to manually adjust end date
// $('#end_date').on('focus', function() {
//     $(this).prop('readonly', false);
// });

// Calculate total days when end date is manually changed (if readonly is removed)
$('#end_date').on('change', function() {
    const start = $('#start_date').val();
    const end = $(this).val();
    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        if (endDate >= startDate) {
            const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            $('#total_days').val(days);
        } else {
            $('#total_days').val('');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Dates',
                    text: 'End date must be after or equal to start date.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
    }
});

// Form submission
$('#requestLeaveForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Disable submit button
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');
    
        $.ajax({
            url: '{{ route("leave.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Leave request submitted successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("modules.hr.leave") }}';
                    });
                } else {
                    alert('Leave request submitted successfully!');
                    window.location.href = '{{ route("modules.hr.leave") }}';
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to submit leave request.'
                    });
                } else {
                    alert('Error: ' + (response.message || 'Failed to submit leave request.'));
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to submit leave request. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg
                });
            } else {
                alert('Error: ' + errorMsg);
            }
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

// Load data on page load
$(document).ready(function() {
    // Page ready
});
</script>
@endpush

