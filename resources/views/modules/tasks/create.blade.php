@extends('layouts.app')

@section('title', 'Create Task')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-plus-circle"></i> Create Task
                </h4>
                <p class="text-muted">Comprehensive task creation form with full details and activity setup</p>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('modules.tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Tasks
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    :root {
        --task-primary: #2563eb;
        --task-success: #16a34a;
        --task-warning: #d97706;
        --task-danger: #dc2626;
        --task-info: #4f46e5;
        --task-border: #e5e7eb;
        --task-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --task-shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .section-card {
        background: #ffffff;
        border: 1px solid var(--task-border);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--task-shadow);
    }

    .section-card-header {
        border-bottom: 2px solid var(--task-border);
        padding-bottom: 16px;
        margin-bottom: 24px;
    }

    .section-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--task-border);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border: 1px solid var(--task-border);
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--task-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .activity-item {
        background: #f9fafb;
        border: 1px solid var(--task-border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
    }

    .activity-item-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 16px;
    }

    .activity-item-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: var(--task-primary);
        color: white;
        border-radius: 50%;
        font-weight: 600;
        margin-right: 12px;
    }

    .btn-remove-activity {
        position: absolute;
        top: 16px;
        right: 16px;
        background: var(--task-danger);
        color: white;
        border: none;
        border-radius: 6px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-remove-activity:hover {
        background: #b91c1c;
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <form id="createTaskForm" method="POST" action="{{ route('modules.tasks.action') }}">
        @csrf
        <input type="hidden" name="action" value="task_create_main">
        
        <!-- Basic Information Section -->
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bx bx-info-circle me-2"></i>
                    Basic Information
                </h3>
            </div>
            
            <div class="form-section-title">Task Details</div>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Task Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter task name">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control" placeholder="Describe the task objectives, outcomes, and constraints"></textarea>
                </div>
            </div>

            <div class="form-section-title">Classification</div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            </div>

            <div class="form-section-title">Ownership & Timeline</div>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Team Leader <span class="text-danger">*</span></label>
                    <select name="team_leader_id" class="form-select" required>
                        <option value="">Select Team Leader</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" id="start_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" id="end_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Timeframe</label>
                    <input type="text" name="timeframe" class="form-control" id="timeframe" placeholder="Auto-calculated" readonly>
                </div>
            </div>

            <div class="form-section-title">Additional Information</div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Budget</label>
                    <input type="text" name="budget" class="form-control" placeholder="Enter budget if applicable">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="Comma-separated tags (e.g., urgent, q1, marketing)">
                </div>
            </div>

            @php
                $orgSettings = \App\Models\OrganizationSetting::getSettings();
                $currentFY = $orgSettings->current_financial_year ?? date('Y');
                $organizationalGoals = \App\Models\OrganizationalGoal::where('is_active', true)
                    ->where(function($q) use ($currentFY, $orgSettings) {
                        $fyDates = $orgSettings->getFinancialYearDates($currentFY);
                        $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
                          ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']])
                          ->orWhere(function($q2) use ($fyDates) {
                              $q2->where('start_date', '<=', $fyDates['start'])
                                 ->where('end_date', '>=', $fyDates['end']);
                          });
                    })
                    ->get();
                $myAssessments = auth()->user()->hasAnyRole(['HR Officer', 'System Admin', 'HOD']) 
                    ? \App\Models\Assessment::where('status', 'approved')->with('employee')->get()
                    : \App\Models\Assessment::where('employee_id', auth()->id())->where('status', 'approved')->get();
            @endphp

            <div class="form-section-title">
                <i class="bx bx-target-lock me-2"></i>Performance Management Integration
                <small class="text-muted ms-2">(Optional - Link this task to performance objectives)</small>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Financial Year:</strong> {{ $currentFY }} 
                        ({{ $orgSettings->getFinancialYearDates($currentFY)['start']->format('M d, Y') }} - {{ $orgSettings->getFinancialYearDates($currentFY)['end']->format('M d, Y') }})
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link Type</label>
                    <select name="link_type" class="form-select" id="link_type">
                        <option value="none">No Link (Operational Task)</option>
                        <option value="direct">Direct Strategic Link</option>
                        <option value="supporting">Supporting Link</option>
                        <option value="operational">Operational Link</option>
                    </select>
                    <small class="text-muted">How this task contributes to performance objectives</small>
                </div>
                <div class="col-md-6" id="performance_weight_field" style="display: none;">
                    <label class="form-label">Performance Weight (%)</label>
                    <input type="number" name="performance_weight" class="form-control" min="0" max="100" step="0.01" placeholder="e.g., 15.5">
                    <small class="text-muted">Weight percentage for performance contribution</small>
                </div>
                <div class="col-md-6" id="organizational_goal_field" style="display: none;">
                    <label class="form-label">Organizational Goal</label>
                    <select name="organizational_goal_id" class="form-select" id="organizational_goal_id">
                        <option value="">Select Organizational Goal</option>
                        @foreach($organizationalGoals as $goal)
                            <option value="{{ $goal->id }}">
                                {{ $goal->title }} 
                                ({{ $goal->start_date->format('M Y') }} - {{ $goal->end_date->format('M Y') }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Link to organizational-level objective</small>
                </div>
                <div class="col-md-6" id="assessment_field" style="display: none;">
                    <label class="form-label">Performance Assessment</label>
                    <select name="assessment_id" class="form-select" id="assessment_id">
                        <option value="">Select Assessment</option>
                        @foreach($myAssessments as $assessment)
                            <option value="{{ $assessment->id }}">
                                {{ $assessment->main_responsibility }} 
                                @if($assessment->employee)
                                    - {{ $assessment->employee->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Link to individual performance assessment</small>
                </div>
            </div>
            <input type="hidden" name="financial_year" value="{{ $currentFY }}">
        </div>

        <!-- Activities Section -->
        <div class="section-card">
            <div class="section-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="section-card-title">
                        <i class="bx bx-task me-2"></i>
                        Activities
                    </h3>
                    <button type="button" class="btn btn-primary btn-sm" id="addActivityBtn">
                        <i class="bx bx-plus"></i> Add Activity
                    </button>
                </div>
            </div>
            <p class="text-muted mb-4">Define the activities that make up this task. You can add multiple activities and assign team members to each.</p>
            
            <div id="activitiesContainer">
                <!-- Activities will be added here dynamically -->
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('modules.tasks.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-x"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bx bx-check"></i> Create Task
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let activityCounter = 0;
    
    // Initialize Select2 for team leader
    $('select[name="team_leader_id"]').select2({
        placeholder: 'Select Team Leader',
        allowClear: true
    });

    // Initialize Select2 for performance fields
    $('#organizational_goal_id, #assessment_id').select2({
        placeholder: 'Select option',
        allowClear: true
    });

    // Handle link type changes
    $('#link_type').on('change', function() {
        const linkType = $(this).val();
        const showFields = linkType !== 'none';
        
        $('#performance_weight_field, #organizational_goal_field, #assessment_field').toggle(showFields);
        
        if (linkType === 'direct') {
            $('#organizational_goal_field').show();
            $('#assessment_field').show();
        } else if (linkType === 'supporting' || linkType === 'operational') {
            $('#organizational_goal_field').show();
            $('#assessment_field').hide();
        }
    });
    
    // Calculate timeframe when dates change
    $('#start_date, #end_date').on('change', function() {
        calculateTimeframe();
    });
    
    function calculateTimeframe() {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const diffTime = Math.abs(endDate - startDate);
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
    
    // Add activity
    $('#addActivityBtn').on('click', function() {
        addActivityItem();
    });
    
    function addActivityItem() {
        activityCounter++;
        const activityHtml = `
            <div class="activity-item" data-activity-index="${activityCounter}">
                <button type="button" class="btn-remove-activity" onclick="removeActivity(this)">
                    <i class="bx bx-x"></i>
                </button>
                <div class="activity-item-header">
                    <span class="activity-item-number">${activityCounter}</span>
                    <strong>Activity ${activityCounter}</strong>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="activities[${activityCounter}][name]" class="form-control" required placeholder="Enter activity name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="activities[${activityCounter}][start_date]" class="form-control activity-start-date">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assign To</label>
                        <select name="activities[${activityCounter}][users][]" class="form-select select2-activity-users" multiple>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">
                            <i class="bx bx-target-lock me-1"></i>Link to Performance Activity (Optional)
                        </label>
                        <select name="activities[${activityCounter}][assessment_activity_id]" class="form-select select2-performance-activity">
                            <option value="">No Link</option>
                            @php
                                $assessmentActivities = \App\Models\AssessmentActivity::whereHas('assessment', function($q) {
                                    $q->where('status', 'approved');
                                })->with('assessment.employee')->get();
                            @endphp
                            @foreach($assessmentActivities as $act)
                                <option value="{{ $act->id }}">
                                    {{ $act->activity_name }} 
                                    @if($act->assessment && $act->assessment->employee)
                                        ({{ $act->assessment->employee->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Link this task activity to a performance assessment activity</small>
                    </div>
                </div>
            </div>
        `;
        
        $('#activitiesContainer').append(activityHtml);
        
        // Initialize Select2 for the new activity users
        $(`.activity-item[data-activity-index="${activityCounter}"] .select2-activity-users`).select2({
            placeholder: 'Select team members',
            allowClear: true
        });
        
        // Initialize Select2 for performance activity
        $(`.activity-item[data-activity-index="${activityCounter}"] .select2-performance-activity`).select2({
            placeholder: 'Select performance activity',
            allowClear: true
        });
    }
    
    // Remove activity
    window.removeActivity = function(btn) {
        $(btn).closest('.activity-item').remove();
        updateActivityNumbers();
    };
    
    function updateActivityNumbers() {
        $('.activity-item').each(function(index) {
            const newIndex = index + 1;
            $(this).attr('data-activity-index', newIndex);
            $(this).find('.activity-item-number').text(newIndex);
            $(this).find('strong').text(`Activity ${newIndex}`);
            
            // Update input names
            $(this).find('input, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/activities\[\d+\]/, `activities[${newIndex}]`));
                }
            });
        });
        activityCounter = $('.activity-item').length;
    }
    
    // Form submission
    $('#createTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading
        Swal.fire({
            title: 'Creating Task...',
            text: 'Please wait while we create your task',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Task created successfully',
                        confirmButtonText: 'Go to Tasks'
                    }).then(() => {
                        window.location.href = '{{ route("modules.tasks.index") }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to create task'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.message || 'An error occurred while creating the task'
                });
            }
        });
    });
});
</script>
@endpush

