@extends('layouts.app')

@section('title', 'Salary Structures - OfisiLink')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-money me-2"></i>Salary Structures
            </h1>
            <p class="text-muted mb-0">Define salary bands, basic pay, and default qualifications for positions.</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" id="new-structure-btn">
                <i class="bx bx-plus-circle me-1"></i>New Structure
            </button>
        </div>
    </div>

    <!-- Structures Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white border-bottom-0">
             <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">All Salary Structures</h6>
                </div>
                <div class="col-md-6 text-end">
                    <input type="text" id="searchInput" class="form-control form-control-sm d-inline-block w-auto" placeholder="Search structures...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Name / Band</th>
                            <th>Code</th>
                            <th class="text-end">Basic Salary</th>
                            <th class="text-end">Range (Min - Max)</th>
                            <th>Department</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="structuresTableBody">
                        @forelse($salaryStructures as $structure)
                        <tr class="structure-row">
                            <td class="ps-4 fw-bold text-primary">{{ $structure->name }}</td>
                            <td><code>{{ $structure->code ?? '-' }}</code></td>
                            <td class="text-end fw-bold">{{ number_format($structure->basic_salary, 2) }}</td>
                            <td class="text-end text-muted small">
                                {{ number_format($structure->min_salary, 2) }} - {{ number_format($structure->max_salary, 2) }}
                            </td>
                            <td>
                                @if($structure->department)
                                    <span class="badge bg-label-info">{{ $structure->department->name }}</span>
                                @else
                                    <span class="text-muted text-xs">Global</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($structure->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-icon btn-outline-primary edit-btn" 
                                    data-id="{{ $structure->id }}"
                                    title="Edit">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-outline-danger delete-btn" 
                                    data-id="{{ $structure->id }}"
                                    title="Delete">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bx bx-ghost fs-1 mb-2"></i><br>
                                No salary structures defined yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="structureModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitle">New Salary Structure</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="structureForm">
                <div class="modal-body">
                    <input type="hidden" id="structureId" name="id">
                    
                    <!-- Basic Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Structure Name / Grade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" required placeholder="e.g. Grade A1 - Senior Management">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control" name="code" id="code" placeholder="e.g. GS-A1">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="2"></textarea>
                        </div>
                    </div>

                    <hr class="text-muted">
                    
                    <!-- Financials -->
                    <h6 class="fw-bold text-primary mb-3"><i class="bx bx-coin-stack me-2"></i>Salary Details (TZS)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="basic_salary" id="basicSalary" required min="0" step="0.01">
                            <div class="form-text">Standard base pay for this grade.</div>
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Min Salary (Range) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="min_salary" id="minSalary" required min="0" step="0.01">
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Max Salary (Range) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_salary" id="maxSalary" required min="0" step="0.01">
                        </div>
                    </div>

                    <hr class="text-muted">

                    <!-- Requirements -->
                    <h6 class="fw-bold text-primary mb-3"><i class="bx bx-award me-2"></i>Requirements & Context</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Department Scope (Optional)</label>
                            <select class="form-select" name="department_id" id="departmentId">
                                <option value="">Global / All Departments</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                             <label class="form-label">Linked Position (Optional)</label>
                             <select class="form-select" name="position_id" id="positionId">
                                <option value="">No Specific Position</option>
                                @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Qualifications (Auto-fill for Recruitment)</label>
                        <div id="qualificationsList">
                            <div class="input-group mb-2 qual-item">
                                <input type="text" class="form-control" name="qualifications[]" placeholder="e.g. Bachelor's Degree in Finance">
                                <button type="button" class="btn btn-outline-danger remove-qual"><i class="bx bx-x"></i></button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="addQualBtn">
                            <i class="bx bx-plus"></i> Add Qualification
                        </button>
                    </div>

                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Salary Structure is Active</label>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Search
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $("#structuresTableBody tr.structure-row").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Add Qualification Field
    $('#addQualBtn').click(function() {
        $('#qualificationsList').append(`
            <div class="input-group mb-2 qual-item">
                <input type="text" class="form-control" name="qualifications[]" placeholder="Qualification requirement">
                <button type="button" class="btn btn-outline-danger remove-qual"><i class="bx bx-x"></i></button>
            </div>
        `);
    });

    // Remove Qualification Field
    $(document).on('click', '.remove-qual', function() {
        $(this).closest('.qual-item').remove();
    });

    // New Structure Button
    $('#new-structure-btn').click(function() {
        $('#structureForm')[0].reset();
        $('#structureId').val('');
        $('#modalTitle').text('New Salary Structure');
        $('#qualificationsList').html(`
            <div class="input-group mb-2 qual-item">
                <input type="text" class="form-control" name="qualifications[]" placeholder="e.g. Bachelor's Degree in Finance">
                <button type="button" class="btn btn-outline-danger remove-qual"><i class="bx bx-x"></i></button>
            </div>
        `);
        $('#structureModal').modal('show');
    });

    // Edit Button
    $('.edit-btn').click(function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/modules/hr/salary-structures/${id}`,
            method: 'GET',
            dataType: 'json', // Explicitly expect JSON
            headers: {
                'Accept': 'application/json' // Explicitly ask for JSON
            },
            success: function(response) {
                if(response.success) {
                    const data = response.data;
                    $('#structureId').val(data.id);
                    $('#name').val(data.name);
                    $('#code').val(data.code);
                    $('#description').val(data.description);
                    $('#basicSalary').val(data.basic_salary);
                    $('#minSalary').val(data.min_salary);
                    $('#maxSalary').val(data.max_salary);
                    $('#departmentId').val(data.department_id);
                    $('#positionId').val(data.position_id);
                    $('#isActive').prop('checked', data.is_active);

                    // Populate qualifications
                    $('#qualificationsList').empty();
                    if(data.qualifications && Array.isArray(data.qualifications)) {
                        data.qualifications.forEach(q => {
                             $('#qualificationsList').append(`
                                <div class="input-group mb-2 qual-item">
                                    <input type="text" class="form-control" name="qualifications[]" value="${q}">
                                    <button type="button" class="btn btn-outline-danger remove-qual"><i class="bx bx-x"></i></button>
                                </div>
                            `);
                        });
                    }
                    if($('#qualificationsList').children().length === 0) {
                        $('#addQualBtn').click();
                    }

                    $('#modalTitle').text('Edit Salary Structure');
                    $('#structureModal').modal('show');
                } else {
                    Swal.fire('Error', 'Failed to load details.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Server error while loading details.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Delete Button
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
         Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/modules/hr/salary-structures/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire('Deleted!', 'Structure has been deleted.', 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete.', 'error');
                    }
                });
            }
        });
    });

    // Save Form
    $('#structureForm').submit(function(e) {
        e.preventDefault();
        
        // Simple client-side validation logic for ranges
        const min = parseFloat($('#minSalary').val());
        const max = parseFloat($('#maxSalary').val());
        const basic = parseFloat($('#basicSalary').val());

        if (max < min) {
            Swal.fire('Validation Error', 'Max Salary cannot be less than Min Salary.', 'warning');
            return;
        }
        if (basic < min || basic > max) {
            Swal.fire('Validation Warning', 'Basic Salary is normally within Min-Max range. Proceed anyway?', 'question')
            .then((r) => {
                if(r.isConfirmed) submitData();
            });
            return;
        }

        submitData();
    });

    function submitData() {
        const id = $('#structureId').val();
         const formData = {
            name: $('#name').val(),
            code: $('#code').val(),
            description: $('#description').val(),
            basic_salary: $('#basicSalary').val(),
            min_salary: $('#minSalary').val(),
            max_salary: $('#maxSalary').val(),
            department_id: $('#departmentId').val(),
            position_id: $('#positionId').val(),
            is_active: $('#isActive').is(':checked'),
            qualifications: $('input[name="qualifications[]"]').map(function(){return $(this).val();}).get().filter(v=>v)
        };

        const url = id ? `/modules/hr/salary-structures/${id}` : '/modules/hr/salary-structures';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            beforeSend: function() {
                $('#saveBtn').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                if(response.success) {
                    $('#structureModal').modal('hide');
                    Swal.fire('Success', response.message, 'success')
                    .then(() => location.reload());
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to save.';
                // Handle validation errors list
                if(xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errs = Object.values(xhr.responseJSON.errors).flat().join('\n');
                     Swal.fire('Validation Error', errs, 'error');
                } else {
                    Swal.fire('Error', msg, 'error');
                }
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false).text('Save Structure');
            }
        });
    }
});
</script>
@endpush
