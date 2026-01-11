@extends('layouts.app')

@section('title', 'Report Progress - ' . $activity->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .activity-info-card {
        border-left: 4px solid #007bff;
    }
    .report-history-item {
        border-left: 3px solid #007bff;
        transition: all 0.3s ease;
    }
    .report-history-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

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
                                <i class="bx bx-upload me-2"></i>Report Progress
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Submit progress report for: <strong>{{ $activity->name }}</strong>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.tasks.show', $activity->mainTask->id) }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Task
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Activity Information -->
            <div class="card mb-4 activity-info-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Activity Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Activity Name:</strong><br>
                            <span class="h5 mb-0">{{ $activity->name }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Task:</strong><br>
                            <a href="{{ route('modules.tasks.show', $activity->mainTask->id) }}" class="text-primary">
                                {{ $activity->mainTask->name }}
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            <span class="badge bg-{{ $activity->status == 'Completed' ? 'success' : ($activity->status == 'In Progress' ? 'info' : 'warning') }}">
                                {{ $activity->status }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Due Date:</strong><br>
                            {{ $activity->end_date ? \Carbon\Carbon::parse($activity->end_date)->format('M d, Y') : 'No deadline' }}
                        </div>
                        @if($activity->timeframe)
                        <div class="col-md-6 mb-3">
                            <strong>Timeframe:</strong><br>
                            {{ $activity->timeframe }}
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <strong>Assigned To:</strong><br>
                            @if($activity->assignedUsers->count() > 0)
                                @foreach($activity->assignedUsers as $user)
                                    <span class="badge bg-info me-1">{{ $user->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Report Form -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="bx bx-file-blank me-2"></i>Submit Progress Report</h5>
                </div>
                <div class="card-body">
                    <form id="reportProgressForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="action" value="task_submit_report">
                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Report Date <span class="text-danger">*</span></label>
                                <input type="date" name="report_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Completion Status <span class="text-danger">*</span></label>
                                <select name="completion_status" id="completionStatus" class="form-select" required>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Delayed">Delayed</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Work Description <span class="text-danger">*</span></label>
                                <textarea name="work_description" rows="5" class="form-control" placeholder="Describe what you have completed, what work was done, achievements, etc. Be detailed and specific." required></textarea>
                                <small class="text-muted">Provide a detailed description of the work completed.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Next Activities</label>
                                <textarea name="next_activities" rows="3" class="form-control" placeholder="What activities are planned next? Who needs to act? What are the next steps?"></textarea>
                                <small class="text-muted">Optional: Describe upcoming activities or next steps.</small>
                            </div>
                            <div class="col-12" id="delayReasonWrap" style="display: none;">
                                <label class="form-label">Reason for Delay <span class="text-danger">*</span></label>
                                <textarea name="reason_if_delayed" rows="3" class="form-control" placeholder="Explain why the activity is delayed. What blockers or challenges are you facing? What support is needed?"></textarea>
                                <small class="text-muted">Required when status is "Delayed".</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Attachments</label>
                                <input type="file" name="attachments[]" id="reportAttachments" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.txt">
                                <small class="text-muted">Upload documents, images, or other files to support your report (multiple files allowed).</small>
                                <div id="attachmentPreview" class="mt-2"></div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Note:</strong> SMS alerts will be automatically sent to team leaders and approvers when you submit this report.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('modules.tasks.show', $activity->mainTask->id) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bx bx-check me-2"></i>Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Reports -->
            @if($recentReports->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-history me-2"></i>Recent Reports</h5>
                </div>
                <div class="card-body">
                    @foreach($recentReports as $report)
                        <div class="report-history-item mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $report->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $report->report_date->format('M d, Y') }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $report->status == 'Approved' ? 'success' : ($report->status == 'Rejected' ? 'danger' : 'warning') }}">
                                        {{ $report->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-{{ $report->completion_status == 'Completed' ? 'success' : ($report->completion_status == 'Delayed' ? 'danger' : 'info') }}">
                                    {{ $report->completion_status }}
                                </span>
                            </div>
                            <p class="mb-2 small">{{ Str::limit($report->work_description, 100) }}</p>
                            @if($report->attachments->count() > 0)
                                <small class="text-muted">
                                    <i class="bx bx-paperclip"></i> {{ $report->attachments->count() }} attachment(s)
                                </small>
                            @endif
                            @if($report->approver)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Approved by: {{ $report->approver->name }}<br>
                                        {{ $report->approved_at ? $report->approved_at->format('M d, Y H:i') : '' }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i>Report Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Total Reports:</strong>
                        <span class="float-end">{{ $activity->reports->count() }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Approved:</strong>
                        <span class="float-end text-success">{{ $activity->reports->where('status', 'Approved')->count() }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Pending:</strong>
                        <span class="float-end text-warning">{{ $activity->reports->where('status', 'Pending')->count() }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Rejected:</strong>
                        <span class="float-end text-danger">{{ $activity->reports->where('status', 'Rejected')->count() }}</span>
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
    const actionUrl = '{{ route("modules.tasks.action") }}';
    const csrfToken = '{{ csrf_token() }}';

    // Handle completion status change
    $('#completionStatus').on('change', function() {
        const status = $(this).val();
        if (status === 'Delayed') {
            $('#delayReasonWrap').show();
            $('textarea[name="reason_if_delayed"]').prop('required', true);
        } else {
            $('#delayReasonWrap').hide();
            $('textarea[name="reason_if_delayed"]').prop('required', false);
        }
    });

    // Preview selected files
    $('#reportAttachments').on('change', function() {
        const files = this.files;
        const preview = $('#attachmentPreview');
        preview.empty();
        
        if (files.length > 0) {
            preview.append('<small class="text-muted d-block mb-1">Selected files:</small><ul class="list-unstyled mb-0">');
            Array.from(files).forEach(function(file) {
                const size = (file.size / 1024).toFixed(2);
                preview.append(`<li><i class="bx bx-file"></i> ${file.name} <small class="text-muted">(${size} KB)</small></li>`);
            });
            preview.append('</ul>');
        }
    });

    // Submit form
    $('#reportProgressForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);

        // Show loading
        Swal.fire({
            title: 'Submitting Report...',
            text: 'Please wait while we process your report',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Submitted!',
                        text: response.message || 'Your progress report has been submitted successfully and is pending approval.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("modules.tasks.show", $activity->mainTask->id) }}';
                    });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to submit report', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error!', response?.message || 'Failed to submit report. Please try again.', 'error');
            }
        });
    });
});
</script>
@endpush


