<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalTitle">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm">
            <div class="modal-body">
                    <input type="hidden" id="scheduleId" name="id">
                    
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="scheduleName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="scheduleCode" name="code" required placeholder="e.g., STD-001">
                            <small class="text-muted">Unique code for this schedule</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="scheduleDescription" name="description" rows="2"></textarea>
                        </div>
                        
                        <!-- Location & Department -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bx bx-map me-2"></i>Location & Department</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select class="form-select" id="scheduleLocationId" name="location_id">
                                <option value="">All Locations</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="scheduleDepartmentId" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Work Hours -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bx bx-time-five me-2"></i>Work Hours</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="scheduleStartTime" name="start_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="scheduleEndTime" name="end_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Work Hours <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="scheduleWorkHours" name="work_hours" min="1" max="24" value="8" required>
                            <small class="text-muted">Total working hours per day</small>
                        </div>
                        
                        <!-- Break Time -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bx bx-coffee me-2"></i>Break Time</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Break Duration (minutes)</label>
                            <input type="number" class="form-control" id="scheduleBreakDuration" name="break_duration_minutes" min="0" max="480" value="60">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Break Start Time</label>
                            <input type="time" class="form-control" id="scheduleBreakStart" name="break_start_time">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Break End Time</label>
                            <input type="time" class="form-control" id="scheduleBreakEnd" name="break_end_time">
                        </div>
                        
                        <!-- Tolerances -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bx bx-timer me-2"></i>Tolerances</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Late Tolerance (minutes)</label>
                            <input type="number" class="form-control" id="scheduleLateTolerance" name="late_tolerance_minutes" min="0" max="120" value="15">
                            <small class="text-muted">Minutes allowed before marked late</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Early Leave Tolerance (minutes)</label>
                            <input type="number" class="form-control" id="scheduleEarlyLeaveTolerance" name="early_leave_tolerance_minutes" min="0" max="120" value="15">
                            <small class="text-muted">Minutes allowed before marked early leave</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Overtime Threshold (minutes)</label>
                            <input type="number" class="form-control" id="scheduleOvertimeThreshold" name="overtime_threshold_minutes" min="0" max="120" value="30">
                            <small class="text-muted">Minutes after end time to count as overtime</small>
                        </div>
                        
                        <!-- Working Days -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bx bx-calendar me-2"></i>Working Days</h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay1" name="working_days[]" value="1">
                                        <label class="form-check-label" for="workingDay1">Monday</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay2" name="working_days[]" value="2">
                                        <label class="form-check-label" for="workingDay2">Tuesday</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay3" name="working_days[]" value="3">
                                        <label class="form-check-label" for="workingDay3">Wednesday</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay4" name="working_days[]" value="4">
                                        <label class="form-check-label" for="workingDay4">Thursday</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay5" name="working_days[]" value="5">
                                        <label class="form-check-label" for="workingDay5">Friday</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay6" name="working_days[]" value="6">
                                        <label class="form-check-label" for="workingDay6">Saturday</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="workingDay7" name="working_days[]" value="7">
                                        <label class="form-check-label" for="workingDay7">Sunday</label>
            </div>
        </div>
    </div>
</div>

                        <!-- Options -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bx bx-cog me-2"></i>Options</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="scheduleIsFlexible" name="is_flexible">
                                <label class="form-check-label" for="scheduleIsFlexible">Flexible Working Hours</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="scheduleIsActive" name="is_active" checked>
                                <label class="form-check-label" for="scheduleIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Schedule
                    </button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
// Schedule Form Submission
document.getElementById('scheduleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const scheduleId = document.getElementById('scheduleId').value;
    const url = scheduleId 
        ? `{{ route('schedules.update', '') }}/${scheduleId}`
        : '{{ route('schedules.store') }}';
    const method = scheduleId ? 'PUT' : 'POST';
    
    // Collect working days
    const workingDays = [];
    document.querySelectorAll('input[name="working_days[]"]:checked').forEach(checkbox => {
        workingDays.push(parseInt(checkbox.value));
    });
    
    // Build data object
    const data = {
        name: formData.get('name'),
        code: formData.get('code'),
        description: formData.get('description'),
        location_id: formData.get('location_id') || null,
        department_id: formData.get('department_id') || null,
        start_time: formData.get('start_time'),
        end_time: formData.get('end_time'),
        work_hours: parseInt(formData.get('work_hours')),
        break_duration_minutes: formData.get('break_duration_minutes') ? parseInt(formData.get('break_duration_minutes')) : null,
        break_start_time: formData.get('break_start_time') || null,
        break_end_time: formData.get('break_end_time') || null,
        late_tolerance_minutes: formData.get('late_tolerance_minutes') ? parseInt(formData.get('late_tolerance_minutes')) : null,
        early_leave_tolerance_minutes: formData.get('early_leave_tolerance_minutes') ? parseInt(formData.get('early_leave_tolerance_minutes')) : null,
        overtime_threshold_minutes: formData.get('overtime_threshold_minutes') ? parseInt(formData.get('overtime_threshold_minutes')) : null,
        working_days: workingDays.length > 0 ? workingDays : [1,2,3,4,5],
        is_flexible: document.getElementById('scheduleIsFlexible').checked,
        is_active: document.getElementById('scheduleIsActive').checked,
    };
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message || 'Schedule saved successfully',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                location.reload();
            });
        } else {
            let errorMessage = result.message || 'Failed to save schedule';
            if (result.errors) {
                const errorList = Object.values(result.errors).flat().join('<br>');
                errorMessage = errorList;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMessage
            });
        }
    } catch (error) {
        console.error('Save error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save schedule. Please try again.'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
