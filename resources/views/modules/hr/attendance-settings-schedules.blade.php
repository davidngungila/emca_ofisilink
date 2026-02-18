@extends('layouts.app')

@section('title', 'Work Schedules')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-time-five"></i> Work Schedules
                </h4>
                <p class="text-muted">Configure work schedules, shifts, and time policies</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
                <button type="button" class="btn btn-primary" onclick="openScheduleModal()">
                    <i class="bx bx-plus me-1"></i> Add Schedule
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .border-left-info { border-left: 4px solid #0dcaf0; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Schedules</h6>
                            <h3 class="mb-0" id="statTotalSchedules">{{ $stats['total_schedules'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-time-five fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Schedules</h6>
                            <h3 class="mb-0 text-success" id="statActiveSchedules">{{ $stats['active_schedules'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-list-ul me-1"></i> Schedules List
                    </h6>
                </div>
                <div class="card-body">
                    @include('modules.hr.attendance-settings.partials.schedules')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
@include('modules.hr.attendance-settings.modals.schedule-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const schedulesData = @json($schedules ?? []);

document.addEventListener('DOMContentLoaded', function() {
    loadSchedules();
});

function loadSchedules() {
    const tbody = document.getElementById('schedulesList');
    if (!tbody) return;

    if (!schedulesData || schedulesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No schedules found</p></td></tr>';
        updateStats(0, 0);
        return;
    }
    
    const activeCount = schedulesData.filter(s => s.is_active).length;
    updateStats(schedulesData.length, activeCount);
    
    tbody.innerHTML = schedulesData.map(schedule => {
        const startTime = schedule.start_time ? (typeof schedule.start_time === 'string' ? schedule.start_time : schedule.start_time.substring(0, 5)) : 'N/A';
        const endTime = schedule.end_time ? (typeof schedule.end_time === 'string' ? schedule.end_time : schedule.end_time.substring(0, 5)) : 'N/A';
        const scheduleName = (schedule.name || 'N/A').replace(/'/g, "\\'");
        
        return `
        <tr>
            <td><strong>${schedule.name || 'N/A'}</strong></td>
            <td><code>${schedule.code || 'N/A'}</code></td>
            <td>${startTime} - ${endTime}</td>
            <td>${schedule.work_hours || 0} hrs</td>
            <td>${schedule.location?.name || 'All'}</td>
            <td>${schedule.department?.name || 'All'}</td>
            <td>${schedule.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editSchedule(${schedule.id})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(${schedule.id}, '${scheduleName}')" title="Delete">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
        `;
    }).join('');
}

function updateStats(total, active) {
    const totalEl = document.getElementById('statTotalSchedules');
    const activeEl = document.getElementById('statActiveSchedules');
    if (totalEl) totalEl.textContent = total;
    if (activeEl) activeEl.textContent = active;
}

function openScheduleModal(scheduleId = null) {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const form = document.getElementById('scheduleForm');
    const title = document.getElementById('scheduleModalTitle');
    
    form.reset();
    document.getElementById('scheduleId').value = scheduleId || '';
    title.textContent = scheduleId ? 'Edit Schedule' : 'Add Schedule';
    
    if (scheduleId) {
        const schedule = schedulesData.find(s => s.id == scheduleId);
        if (schedule) {
            document.getElementById('scheduleName').value = schedule.name || '';
            document.getElementById('scheduleCode').value = schedule.code || '';
            document.getElementById('scheduleDescription').value = schedule.description || '';
            document.getElementById('scheduleLocationId').value = schedule.location_id || '';
            document.getElementById('scheduleDepartmentId').value = schedule.department_id || '';
            // Format times for time input (HH:mm format)
            const formatTime = (time) => {
                if (!time) return '';
                if (typeof time === 'string') {
                    // Handle H:i:s format
                    return time.substring(0, 5);
                }
                return '';
            };
            
            document.getElementById('scheduleStartTime').value = formatTime(schedule.start_time);
            document.getElementById('scheduleEndTime').value = formatTime(schedule.end_time);
            document.getElementById('scheduleWorkHours').value = schedule.work_hours || 8;
            document.getElementById('scheduleBreakDuration').value = schedule.break_duration_minutes || 60;
            document.getElementById('scheduleBreakStart').value = formatTime(schedule.break_start_time);
            document.getElementById('scheduleBreakEnd').value = formatTime(schedule.break_end_time);
            document.getElementById('scheduleLateTolerance').value = schedule.late_tolerance_minutes || 15;
            document.getElementById('scheduleEarlyLeaveTolerance').value = schedule.early_leave_tolerance_minutes || 15;
            document.getElementById('scheduleOvertimeThreshold').value = schedule.overtime_threshold_minutes || 30;
            document.getElementById('scheduleIsFlexible').checked = schedule.is_flexible || false;
            document.getElementById('scheduleIsActive').checked = schedule.is_active !== false;
            
            // Set working days checkboxes
            const workingDays = schedule.working_days || [];
            [1,2,3,4,5,6,7].forEach(day => {
                const checkbox = document.getElementById(`workingDay${day}`);
                if (checkbox) checkbox.checked = workingDays.includes(day);
            });
        }
    } else {
        // Default working days: Mon-Fri
        [1,2,3,4,5].forEach(day => {
            const checkbox = document.getElementById(`workingDay${day}`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    modal.show();
}

function editSchedule(id) {
    openScheduleModal(id);
}

function deleteSchedule(id, name) {
    Swal.fire({
        title: 'Delete Schedule',
        html: 'Are you sure you want to delete <strong>' + name + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('schedules.delete', '') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete schedule', 'error');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                Swal.fire('Error!', 'Failed to delete schedule', 'error');
            });
        }
    });
}
</script>
@endpush









