@extends('layouts.app')

@section('title', 'Create Activity - ' . $task->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .section-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-success" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-plus me-2"></i>Create New Activity
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">Add a new activity to: <strong>{{ $task->name }}</strong></p>
                        </div>
                        <div>
                            <a href="{{ route('modules.tasks.show', $task->id) }}" class="btn btn-light btn-lg shadow-sm me-2">
                                <i class="bx bx-arrow-back me-2"></i>Back to Task
                            </a>
                            <a href="{{ route('modules.tasks.edit', $task->id) }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-edit me-2"></i>Edit Task
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Activity Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="createActivityForm" method="POST" action="{{ route('modules.tasks.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="task_add_activity">
                        <input type="hidden" name="main_task_id" value="{{ $task->id }}">

                        <!-- Basic Information -->
                        <div class="section-card">
                            <h4 class="mb-4"><i class="bx bx-info-circle me-2"></i>Activity Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Activity Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="Enter activity name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" id="startDate">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" id="endDate">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Not Started" selected>Not Started</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Delayed">Delayed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="Low">Low</option>
                                        <option value="Normal" selected>Normal</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Timeframe</label>
                                    <input type="text" name="timeframe" class="form-control" id="timeframe" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Depends On</label>
                                    <select name="depends_on_id" class="form-select">
                                        <option value="">None</option>
                                        @foreach($activities as $activity)
                                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Assign To</label>
                                    <select name="user_ids[]" class="form-select select2-users" multiple>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select team members to assign this activity to</small>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('modules.tasks.show', $task->id) }}" class="btn btn-secondary">
                                <i class="bx bx-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bx bx-save me-1"></i>Create Activity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const actionUrl = '{{ route("modules.tasks.action") }}';

    // Initialize Select2
    $('.select2-users').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select team members',
        allowClear: true
    });

    // Calculate timeframe
    function calculateTimeframe() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            let timeframe = '';
            if (diffDays < 7) {
                timeframe = diffDays + ' Day(s)';
            } else if (diffDays < 30) {
                const weeks = Math.floor(diffDays / 7);
                const days = diffDays % 7;
                timeframe = weeks + ' Week(s)';
                if (days > 0) timeframe += ' ' + days + ' Day(s)';
            } else if (diffDays < 365) {
                const months = Math.floor(diffDays / 30);
                const days = diffDays % 30;
                timeframe = months + ' Month(s)';
                if (days > 0) timeframe += ' ' + days + ' Day(s)';
            } else {
                const years = Math.floor(diffDays / 365);
                const months = Math.floor((diffDays % 365) / 30);
                timeframe = years + ' Year(s)';
                if (months > 0) timeframe += ' ' + months + ' Month(s)';
            }
            
            $('#timeframe').val(timeframe);
        }
    }

    $('#startDate, #endDate').on('change', calculateTimeframe);

    // Create activity form
    $('#createActivityForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();

        Swal.fire({
            title: 'Creating Activity...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message || 'Activity created successfully', 'success').then(() => {
                        window.location.href = '{{ route("modules.tasks.show", $task->id) }}';
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to create activity', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to create activity. Please try again.', 'error');
            }
        });
    });
});
</script>
@endpush


