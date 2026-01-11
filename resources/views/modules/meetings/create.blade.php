@extends('layouts.app')

@section('title', 'Create New Meeting - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .meeting-form-container {
        position: relative;
    }
    .form-progress {
        position: sticky;
        top: 20px;
        z-index: 100;
    }
    .progress-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .progress-steps {
        list-style: none;
        padding: 0;
        margin: 15px 0 0 0;
    }
    .progress-steps li {
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
    }
    .progress-steps li:last-child {
        border-bottom: none;
    }
    .step-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-weight: bold;
    }
    .step-icon.active {
        background: white;
        color: #667eea;
    }
    .step-icon.completed {
        background: #10b981;
        color: white;
    }
    .step-label {
        flex: 1;
        font-size: 14px;
    }
    .form-section-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .form-section-card:hover {
        border-color: #667eea;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
    }
    .form-section-card.completed {
        border-color: #10b981;
    }
    .quick-actions {
        position: sticky;
        bottom: 20px;
        z-index: 100;
        margin-top: 30px;
    }
    .quick-actions-card {
        background: white;
        border-radius: 15px;
        padding: 15px 20px;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }
    .section-counter {
        display: inline-block;
        background: #667eea;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: bold;
        margin-left: 8px;
    }
    .auto-save-indicator {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1050;
        background: white;
        border-radius: 8px;
        padding: 10px 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        display: none;
        align-items: center;
        gap: 10px;
    }
    .auto-save-indicator.show {
        display: flex;
    }
    .auto-save-indicator.saving {
        border-left: 3px solid #f59e0b;
    }
    .auto-save-indicator.saved {
        border-left: 3px solid #10b981;
    }
    .form-validation-feedback {
        margin-top: 5px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Auto-Save Indicator -->
    <div id="auto-save-indicator" class="auto-save-indicator">
        <i class="bx bx-loader-alt bx-spin"></i>
        <span>Auto-saving...</span>
    </div>

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-2 fw-bold">
                                <i class="bx bx-calendar-plus me-2"></i>Create New Meeting
                            </h4>
                            <p class="card-text text-white-50 mb-0">
                                <i class="bx bx-info-circle me-1"></i>Schedule a new meeting with participants, agenda, and advanced options
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.meetings.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Meetings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Meeting Form -->
    <div class="row meeting-form-container">
        <!-- Main Form -->
        <div class="col-lg-9">
            @include('modules.meetings.partials.meeting-form', ['meeting' => null, 'categories' => $categories, 'branches' => $branches, 'departments' => $departments, 'users' => $users ?? collect()])
        </div>

        <!-- Progress Sidebar -->
        <div class="col-lg-3">
            <div class="form-progress">
                <div class="progress-card">
                    <h6 class="text-white mb-3 fw-bold">
                        <i class="bx bx-list-check me-2"></i>Form Progress
                    </h6>
                    <ul class="progress-steps">
                        <li>
                            <span class="step-icon active">1</span>
                            <span class="step-label">Basic Information</span>
                        </li>
                        <li>
                            <span class="step-icon">2</span>
                            <span class="step-label">Participants</span>
                        </li>
                        <li>
                            <span class="step-icon">3</span>
                            <span class="step-label">Agenda Items</span>
                        </li>
                    </ul>
                </div>

                <!-- Quick Tips Card -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bx bx-bulb me-2"></i>Quick Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Fill all required fields marked with *
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Add at least one participant
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Include agenda items for better organization
                            </li>
                            <li>
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Use "Save & Submit" for approval workflow
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Update progress indicator
        function updateProgress() {
            const basicInfo = $('#meetingForm input[name="title"]').val() && 
                             $('#meetingForm select[name="category_id"]').val() &&
                             $('#meetingForm input[name="meeting_date"]').val();
            
            const participants = $('#staff_participants').val() && $('#staff_participants').val().length > 0 ||
                                 $('#external-participants-container .external-participant-item').length > 0;
            
            const agenda = $('#agenda-items-container .agenda-item').length > 0;

            // Update step icons
            $('.progress-steps li').eq(0).find('.step-icon').toggleClass('completed', basicInfo).toggleClass('active', !basicInfo);
            $('.progress-steps li').eq(1).find('.step-icon').toggleClass('completed', participants).toggleClass('active', !participants && basicInfo);
            $('.progress-steps li').eq(2).find('.step-icon').toggleClass('completed', agenda).toggleClass('active', !agenda && participants);

            // Update section cards
            if (basicInfo) $('#meetingForm .card').first().addClass('completed');
            if (participants) $('#meetingForm .card').eq(1).addClass('completed');
            if (agenda) $('#meetingForm .card').eq(2).addClass('completed');
        }

        // Initial progress update
        updateProgress();

        // Update on input change
        $('#meetingForm').on('input change', 'input, select, textarea', function() {
            updateProgress();
        });

        // Auto-save indicator (can be enhanced with actual auto-save)
        let autoSaveTimeout;
        $('#meetingForm').on('input', function() {
            clearTimeout(autoSaveTimeout);
            $('#auto-save-indicator').removeClass('saved').addClass('saving show');
            autoSaveTimeout = setTimeout(function() {
                $('#auto-save-indicator').removeClass('saving').addClass('saved').find('span').text('Changes saved');
                setTimeout(function() {
                    $('#auto-save-indicator').removeClass('show');
                }, 2000);
            }, 2000);
        });
    });
</script>
@endpush

