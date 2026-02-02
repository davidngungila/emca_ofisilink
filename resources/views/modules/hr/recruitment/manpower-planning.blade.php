@extends('layouts.app')

@section('title', 'Manpower Planning')

@push('styles')
<style>
    .position-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    .position-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .shortage-badge {
        font-size: 0.85rem;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="text-white mb-1"><i class="bx bx-buildings me-2"></i>Manpower Planning</h3>
                            <p class="mb-0 text-white-50">Manage institutional establishment, define needs, and track shortages.</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            @if($canManagePlanning)
                            <button class="btn btn-light shadow-sm text-primary fw-bold" id="addPositionBtn">
                                <i class="bx bx-plus-circle me-1"></i> Add Institutional Position
                            </button>
                            <a href="{{ route('modules.hr.salary-structures.index') }}" class="btn btn-outline-light">
                                <i class="bx bx-money me-1"></i> Salary Structures
                            </a>
                            @endif
                            <a href="{{ route('jobs') }}" class="btn btn-outline-light">
                                <i class="bx bx-briefcase me-1"></i> Recruitment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-primary rounded p-2 me-3">
                        <i class="bx bx-list-ul fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Positions</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total_positions'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-success rounded p-2 me-3">
                        <i class="bx bx-user-check fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Filled Positions</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['filled_positions'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-5">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-danger rounded p-2 me-3">
                        <i class="bx bx-user-minus fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-danger mb-1 fw-bold">Total Shortage</h6>
                        <h3 class="mb-0 fw-bold text-danger">{{ $stats['total_shortage'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-warning rounded p-2 me-3">
                        <i class="bx bx-error fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Critical Needs</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['critical_shortages'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-light"><i class="bx bx-search"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="Search positions...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="deptFilter" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="">All Status</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-outline-secondary" id="refreshBtn">
                        <i class="bx bx-refresh me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="fw-bold text-secondary">Position Title</th>
                        <th class="fw-bold text-secondary">Department</th>
                        <th class="fw-bold text-secondary text-center">Required</th>
                        <th class="fw-bold text-secondary text-center">Current</th>
                        <th class="fw-bold text-secondary text-center">Shortage</th>
                        <th class="fw-bold text-secondary">Salary Structure</th>
                        <th class="fw-bold text-secondary text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="positionsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bx bx-loader-alt bx-spin bx-md text-muted"></i>
                            <p class="text-muted mt-2">Loading positions...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Position Modal -->
<div class="modal fade" id="positionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitle">Add Institutional Position</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="positionForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_institutional_position">
                    <input type="hidden" name="position_id" id="positionId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position Title *</label>
                            <input type="text" name="position_title" id="positionTitle" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="departmentId" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Required Count (Establishment) *</label>
                            <input type="number" name="required_count" id="requiredCount" class="form-control" min="1" required>
                            <small class="text-muted">Total number of staff required for this position.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Structure</label>
                            <select name="salary_structure_id" id="salaryStructureId" class="form-select">
                                <option value="">Select Salary Structure (Auto-qualifications)</option>
                                @foreach($salaryStructures as $struct)
                                <option value="{{ $struct->id }}">{{ $struct->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description / Scope</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Default Qualifications</label>
                        <textarea name="qualifications[]" id="defKeywords" class="form-control" rows="2" placeholder="These will be auto-filled from Salary Structure if selected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Position</button>
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
    const recruitmentUrl = '{{ route("recruitment.handle") }}';

    loadPositions();

    // Event Listeners
    $('#refreshBtn').click(loadPositions);
    $('#searchInput, #deptFilter, #statusFilter').on('input change', loadPositions);

    $('#addPositionBtn').click(function() {
        $('#positionForm')[0].reset();
        $('#modalTitle').text('Add Institutional Position');
        $('#formAction').val('create_institutional_position');
        $('#positionId').val('');
        $('#positionModal').modal('show');
    });

    // Auto-fill qualifications from Salary Structure
    $('#salaryStructureId').change(function() {
        const structId = $(this).val();
        if(structId) {
            $.ajax({
                url: recruitmentUrl,
                type: 'POST',
                data: { 
                    action: 'get_salary_structure_details', 
                    salary_structure_id: structId,
                    _token: csrfToken // Explicitly passing token here too just in case
                },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function(response) {
                    if(response.success && response.qualifications) {
                        let quals = response.qualifications;
                        if(Array.isArray(quals)) quals = quals.join('\n');
                        // Confirm before overwriting
                        if($('#defKeywords').val()) {
                            if(confirm('Overwrite existing qualifications with defaults from Salary Structure?')) {
                                $('#defKeywords').val(quals);
                            }
                        } else {
                            $('#defKeywords').val(quals);
                        }
                    }
                }
            });
        }
    });

    // Submit Form
    $('#positionForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serializeArray();
        
        // Add CSRF token to form data if not present (serialize includes it usually if input exists)
        // ensure action is correct
        
        $.ajax({
            url: recruitmentUrl,
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            beforeSend: function() {
                $('#saveBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');
            },
            success: function(response) {
                if(response.success) {
                    $('#positionModal').modal('hide');
                    Swal.fire('Success', response.message, 'success');
                    loadPositions();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(err) {
                Swal.fire('Error', 'Failed to save position.', 'error');
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false).text('Save Position');
            }
        });
    });

    // Load Positions Function
    function loadPositions() {
        const search = $('#searchInput').val();
        const dept = $('#deptFilter').val();
        const status = $('#statusFilter').val();

        $('#positionsTableBody').html('<tr><td colspan="7" class="text-center py-5"><i class="bx bx-loader-alt bx-spin bx-md text-primary"></i></td></tr>');

        $.ajax({
            url: recruitmentUrl,
            type: 'POST',
            data: { 
                action: 'get_institutional_positions',
                department_id: dept,
                status: status
            },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                if(response.success) {
                    let html = '';
                    const positions = response.positions.filter(p => {
                        return !search || p.position_title.toLowerCase().includes(search.toLowerCase());
                    });

                    if(positions.length === 0) {
                        html = '<tr><td colspan="7" class="text-center py-4 text-muted">No positions found.</td></tr>';
                    } else {
                        positions.forEach(p => {
                            const shortageClass = p.shortage > 0 ? 'text-danger fw-bold' : 'text-success';
                            const shortageBadge = p.shortage > 0 
                                ? `<span class="badge bg-danger shortage-badge">${p.shortage} Shortage</span>`
                                : '<span class="badge bg-success">Full</span>';
                            
                            html += `
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">${p.position_title}</div>
                                        <small class="text-muted"><i class="bx bx-time me-1"></i>Last updated: ${new Date(p.updated_at).toLocaleDateString()}</small>
                                    </td>
                                    <td>${p.department ? p.department.name : '-'}</td>
                                    <td class="text-center"><span class="badge bg-label-primary">${p.required_count}</span></td>
                                    <td class="text-center"><span class="badge bg-label-info">${p.current_count}</span></td>
                                    <td class="text-center">${shortageBadge}</td>
                                    <td>
                                        ${p.salary_structure ? '<i class="bx bx-check-circle text-success me-1"></i>' + p.salary_structure.name : '<span class="text-muted">Not Link</span>'}
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-icon btn-outline-primary edit-btn" data-data='${JSON.stringify(p)}'>
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        ${p.shortage > 0 ? `
                                        <button class="btn btn-sm btn-success recruit-btn" data-id="${p.id}" data-title="${p.position_title}" title="Recruit / Advertise">
                                            <i class="bx bx-user-plus"></i> Recruit
                                        </button>
                                        ` : ''}
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#positionsTableBody').html(html);
                }
            },
            error: function() {
                $('#positionsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load data.</td></tr>');
            }
        });
    }

    // Edit Button Click
    $(document).on('click', '.edit-btn', function() {
        const data = $(this).data('data');
        $('#modalTitle').text('Edit Position');
        $('#formAction').val('update_institutional_position');
        $('#positionId').val(data.id);
        $('#positionTitle').val(data.position_title);
        $('#departmentId').val(data.department_id);
        $('#requiredCount').val(data.required_count);
        $('#salaryStructureId').val(data.salary_structure_id);
        $('#description').val(data.description);
        
        let quals = data.qualifications;
        if(Array.isArray(quals)) quals = quals.join('\\n');
        $('#defKeywords').val(quals);
        
        $('#positionModal').modal('show');
    });

    // Recruit Button Click - Open Recruitment Modal or Redirect
    $(document).on('click', '.recruit-btn', function() {
        const posTitle = $(this).data('title');
        // We could redirect to Jobs page with pre-fill params, or handle it here
        // For now, redirect to jobs page and maybe pass a param? 
        // Or better, show a Swal asking to create job
        Swal.fire({
            title: `Recruit for ${posTitle}?`,
            text: "This will redirect you to create a new Job Vacancy for this position.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Create Job'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('jobs') }}?create=true&position_title=" + encodeURIComponent(posTitle);
            }
        });
    });
});
</script>
@endpush
