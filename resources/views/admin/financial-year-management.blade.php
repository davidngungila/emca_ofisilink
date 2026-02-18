@extends('layouts.app')

@section('title', 'Financial Year Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calendar me-2"></i>Financial Year Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage financial year transitions, archival, and performance tracking
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Financial Year Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-calendar-check fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Current Financial Year</h6>
                            <h3 class="mb-0 fw-bold text-primary">FY {{ $currentFY }}</h3>
                            <small class="text-muted">
                                {{ $currentFYDates['start']->format('M d, Y') }} - {{ $currentFYDates['end']->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-info">
                            <i class="bx bx-task fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Tasks (Current FY)</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ $currentFYStats['tasks'] }}</h3>
                            <small class="text-muted">{{ $currentFYStats['linked_tasks'] }} linked to performance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success">
                            <i class="bx bx-file fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Reports (Current FY)</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $currentFYStats['reports'] }}</h3>
                            <small class="text-muted">Daily progress reports</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-target-lock fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Assessments (Current FY)</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $currentFYStats['assessments'] }}</h3>
                            <small class="text-muted">Performance assessments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarterly Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Quarterly Breakdown - FY {{ $currentFY }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($quarterlyData as $quarter)
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <h4 class="text-primary mb-2">{{ $quarter['label'] }}</h4>
                                    <p class="text-muted small mb-2">
                                        {{ $quarter['start']->format('M d') }} - {{ $quarter['end']->format('M d, Y') }}
                                    </p>
                                    <div class="row g-2 mt-3">
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded">
                                                <strong class="d-block">{{ $quarter['tasks'] }}</strong>
                                                <small class="text-muted">Tasks</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded">
                                                <strong class="d-block">{{ $quarter['completed_tasks'] }}</strong>
                                                <small class="text-muted">Completed</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded">
                                                <strong class="d-block">{{ $quarter['reports'] }}</strong>
                                                <small class="text-muted">Reports</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded">
                                                <strong class="d-block">{{ $quarter['assessments'] }}</strong>
                                                <small class="text-muted">Assessments</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incomplete Tasks (Carry Forward) -->
    @if($incompleteTasks->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-transfer me-2"></i>Incomplete Tasks - Carry Forward Options
                    </h5>
                    <small class="text-white-50">{{ $incompleteTasks->count() }} task(s) not completed in FY {{ $currentFY }}</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllTasks" class="form-check-input">
                                    </th>
                                    <th>Task Name</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Linked To</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incompleteTasks as $task)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="carry_forward_tasks[]" value="{{ $task->id }}" class="form-check-input task-checkbox">
                                    </td>
                                    <td>
                                        <strong>{{ $task->name }}</strong>
                                        @if($task->description)
                                        <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status == 'in_progress' ? 'info' : 'warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 100px;">
                                            <div class="progress-bar" style="width: {{ $task->progress_percentage ?? 0 }}%">
                                                {{ $task->progress_percentage ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->organizationalGoal)
                                            <span class="badge bg-primary">Org Goal</span>
                                        @endif
                                        @if($task->assessment)
                                            <span class="badge bg-info">Assessment</span>
                                        @endif
                                        @if(!$task->organizationalGoal && !$task->assessment)
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->end_date ? $task->end_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Financial Year Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Financial Year Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bx bx-lock me-2"></i>Close Current Financial Year</h6>
                                    <p class="text-muted small mb-3">
                                        Archive completed tasks, finalize performance scores, and prepare for new FY.
                                    </p>
                                    <button type="button" class="btn btn-primary" id="btnCloseFY" data-year="{{ $currentFY }}">
                                        <i class="bx bx-lock me-2"></i>Close FY {{ $currentFY }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bx bx-calendar-plus me-2"></i>Initialize New Financial Year</h6>
                                    <p class="text-muted small mb-3">
                                        Set up a new financial year period for performance tracking.
                                    </p>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="newFYYear" value="{{ $currentFY + 1 }}" min="{{ $currentFY + 1 }}" max="2100">
                                        <button type="button" class="btn btn-success" id="btnInitializeFY">
                                            <i class="bx bx-calendar-plus me-2"></i>Initialize
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Select all tasks checkbox
    $('#selectAllTasks').on('change', function() {
        $('.task-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Close Financial Year
    $('#btnCloseFY').on('click', function() {
        const year = $(this).data('year');
        const carryForwardTasks = $('.task-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        Swal.fire({
            title: 'Close Financial Year?',
            html: `
                <p>Are you sure you want to close Financial Year <strong>${year}</strong>?</p>
                <p class="text-warning"><strong>Warning:</strong> This will:</p>
                <ul class="text-start">
                    <li>Archive all completed tasks</li>
                    <li>Finalize performance scores</li>
                    <li>Lock the financial year</li>
                    <li>${carryForwardTasks.length > 0 ? `Carry forward ${carryForwardTasks.length} task(s)` : 'Close incomplete tasks'}</li>
                </ul>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Close FY',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Closing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ route('financial-year.close') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        year: year,
                        carry_forward_tasks: carryForwardTasks
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Financial Year Closed!',
                                html: `
                                    <p>FY ${year} has been closed successfully.</p>
                                    <ul class="text-start">
                                        <li>Archived Tasks: ${response.data.archived_tasks}</li>
                                        <li>Carried Forward: ${response.data.carried_forward_tasks}</li>
                                        <li>Closed Tasks: ${response.data.closed_tasks}</li>
                                    </ul>
                                `,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to close financial year';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });

    // Initialize New Financial Year
    $('#btnInitializeFY').on('click', function() {
        const newYear = $('#newFYYear').val();
        
        if (!newYear || newYear < 2000 || newYear > 2100) {
            Swal.fire('Error', 'Please enter a valid year (2000-2100)', 'error');
            return;
        }

        Swal.fire({
            title: 'Initialize New Financial Year?',
            html: `<p>Initialize Financial Year <strong>${newYear}</strong>?</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Initialize',
            confirmButtonColor: '#28a745',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Initializing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ route('financial-year.initialize') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        year: newYear
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Financial Year Initialized!',
                                html: `
                                    <p>FY ${newYear} has been initialized successfully.</p>
                                    <p><strong>Period:</strong> ${response.dates.start} to ${response.dates.end}</p>
                                `,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to initialize financial year';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

