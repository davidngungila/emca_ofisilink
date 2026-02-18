@extends('layouts.app')

@section('title', 'Report Details - ' . ($report->activity->name ?? 'N/A'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .info-card {
        border-left: 4px solid #007bff;
    }
    .attachment-item {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    .attachment-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 5px;
        margin-top: 10px;
    }
    /* Ensure SweetAlert2 appears on top of everything */
    .swal2-container {
        z-index: 99999 !important;
    }
    .swal2-popup-success {
        z-index: 99999 !important;
        font-size: 1.2rem;
    }
    .swal2-title-success {
        font-size: 1.5rem;
        font-weight: bold;
        color: #28a745;
    }
    .swal2-confirm-success {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        font-size: 1rem;
        padding: 10px 30px;
    }
    .swal2-popup-error {
        z-index: 99999 !important;
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
                                <i class="bx bx-file-blank me-2"></i>Report Details
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Progress report for: <strong>{{ $report->activity->name ?? 'N/A' }}</strong>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.tasks.reports') }}" class="btn btn-light btn-lg shadow-sm me-2">
                                <i class="bx bx-arrow-back me-2"></i>Back to Reports
                            </a>
                            @if($report->activity->mainTask)
                                <a href="{{ route('modules.tasks.show', $report->activity->mainTask->id) }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-task me-2"></i>View Task
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Report Information -->
            <div class="card mb-4 info-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Report Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="bx bx-calendar me-2 text-primary"></i>Report Date:</strong><br>
                            <span class="h5 mb-0">{{ $report->report_date->format('l, F d, Y') }}</span><br>
                            <small class="text-muted">Submitted {{ $report->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bx bx-user me-2 text-primary"></i>Reporter:</strong><br>
                            <div class="d-flex align-items-center mt-2">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-primary">{{ substr($report->user->name ?? 'N/A', 0, 1) }}</span>
                                </div>
                                <div>
                                    <strong>{{ $report->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $report->user->email ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bx bx-task me-2 text-primary"></i>Activity:</strong><br>
                            <span class="h6 mb-0">{{ $report->activity->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bx bx-list-check me-2 text-primary"></i>Task:</strong><br>
                            @if($report->activity->mainTask)
                                <a href="{{ route('modules.tasks.show', $report->activity->mainTask->id) }}" class="text-primary">
                                    <strong>{{ $report->activity->mainTask->name }}</strong>
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bx bx-check-circle me-2 text-primary"></i>Completion Status:</strong><br>
                            @php
                                $completionBadge = [
                                    'In Progress' => 'bg-info',
                                    'Completed' => 'bg-success',
                                    'Delayed' => 'bg-danger'
                                ];
                                $badgeClass = $completionBadge[$report->completion_status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} fs-6 mt-2">
                                {{ $report->completion_status }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bx bx-time me-2 text-primary"></i>Approval Status:</strong><br>
                            @php
                                $statusBadge = [
                                    'Pending' => 'bg-warning',
                                    'Approved' => 'bg-success',
                                    'Rejected' => 'bg-danger'
                                ];
                                $badgeClass = $statusBadge[$report->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} fs-6 mt-2">
                                {{ $report->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Description -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-file me-2"></i>Work Description</h5>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded">
                        {!! nl2br(e($report->work_description)) !!}
                    </div>
                </div>
            </div>

            <!-- Next Activities -->
            @if($report->next_activities)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-right-arrow-circle me-2"></i>Next Activities</h5>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded">
                        {!! nl2br(e($report->next_activities)) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Reason for Delay -->
            @if($report->reason_if_delayed)
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0 text-white"><i class="bx bx-error-circle me-2"></i>Reason for Delay</h5>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-danger bg-opacity-10 rounded text-danger">
                        {!! nl2br(e($report->reason_if_delayed)) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Attachments -->
            @if($report->attachments->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-paperclip me-2"></i>Attachments ({{ $report->attachments->count() }})</h5>
                </div>
                <div class="card-body">
                    @foreach($report->attachments as $attachment)
                        <div class="attachment-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bx bx-file fs-4 me-2 text-primary"></i>
                                        <div>
                                            <strong>{{ $attachment->file_name }}</strong><br>
                                            <small class="text-muted">
                                                {{ $attachment->file_type ?? 'Document' }} • 
                                                {{ $attachment->file_size ? number_format($attachment->file_size / 1024, 2) . ' KB' : 'N/A' }}
                                            </small>
                                        </div>
                                    </div>
                                    @php
                                        $isImage = in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp
                                    @if($isImage)
                                        <div>
                                            <img src="{{ route('storage.activity-reports', ['reportId' => $report->id, 'filename' => basename($attachment->file_path)]) }}" alt="{{ $attachment->file_name }}" class="image-preview">
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('storage.activity-reports', ['reportId' => $report->id, 'filename' => basename($attachment->file_path)]) }}" target="_blank" class="btn btn-sm btn-outline-primary" download>
                                        <i class="bx bx-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Approval Information -->
            @if($report->approver)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-check-circle me-2"></i>Approval Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Approved By:</strong><br>
                            <div class="d-flex align-items-center mt-2">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-success">{{ substr($report->approver->name ?? 'N/A', 0, 1) }}</span>
                                </div>
                                <div>
                                    <strong>{{ $report->approver->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $report->approver->email ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Approved At:</strong><br>
                            <span class="h6 mb-0">{{ $report->approved_at ? $report->approved_at->format('l, F d, Y H:i') : 'N/A' }}</span><br>
                            @if($report->approved_at)
                                <small class="text-muted">{{ $report->approved_at->diffForHumans() }}</small>
                            @endif
                        </div>
                        @if($report->approver_comments)
                        <div class="col-12">
                            <strong>Approver Comments:</strong><br>
                            <div class="p-3 bg-light rounded mt-2">
                                {!! nl2br(e($report->approver_comments)) !!}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons (for Managers) -->
            @if($canApprove || $canReject)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0 text-white"><i class="bx bx-check-double me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        @if($canApprove)
                            <button type="button" class="btn btn-success btn-lg" onclick="approveReport({{ $report->id }})">
                                <i class="bx bx-check me-2"></i>Approve Report
                            </button>
                        @endif
                        @if($canReject)
                            <button type="button" class="btn btn-danger btn-lg" onclick="rejectReport({{ $report->id }})">
                                <i class="bx bx-x me-2"></i>Reject Report
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Activity Information -->
            @if($report->activity)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Activity Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Activity Name:</strong><br>
                        {{ $report->activity->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $report->activity->status == 'Completed' ? 'success' : ($report->activity->status == 'In Progress' ? 'info' : 'warning') }}">
                            {{ $report->activity->status }}
                        </span>
                    </div>
                    @if($report->activity->end_date)
                    <div class="mb-3">
                        <strong>Due Date:</strong><br>
                        {{ \Carbon\Carbon::parse($report->activity->end_date)->format('M d, Y') }}
                    </div>
                    @endif
                    @if($report->activity->assignedUsers->count() > 0)
                    <div class="mb-3">
                        <strong>Assigned To:</strong><br>
                        @foreach($report->activity->assignedUsers as $user)
                            <span class="badge bg-info me-1">{{ $user->name }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Related Reports -->
            @if($relatedReports->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-history me-2"></i>Related Reports</h5>
                </div>
                <div class="card-body">
                    @foreach($relatedReports as $relatedReport)
                        <div class="mb-3 p-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $relatedReport->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $relatedReport->report_date->format('M d, Y') }}</small>
                                </div>
                                <span class="badge bg-{{ $relatedReport->status == 'Approved' ? 'success' : ($relatedReport->status == 'Rejected' ? 'danger' : 'warning') }}">
                                    {{ $relatedReport->status }}
                                </span>
                            </div>
                            <a href="{{ route('modules.tasks.reports.show', $relatedReport->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show"></i> View
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i>Report Statistics</h5>
                </div>
                <div class="card-body">
                    @if($report->activity)
                        <div class="mb-3">
                            <strong>Total Reports for Activity:</strong>
                            <span class="float-end">{{ $report->activity->reports->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Approved:</strong>
                            <span class="float-end text-success">{{ $report->activity->reports->where('status', 'Approved')->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Pending:</strong>
                            <span class="float-end text-warning">{{ $report->activity->reports->where('status', 'Pending')->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Rejected:</strong>
                            <span class="float-end text-danger">{{ $report->activity->reports->where('status', 'Rejected')->count() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Modal -->
<div class="modal fade" id="approveRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveRejectModalTitle">Approve Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveRejectForm">
                <div class="modal-body">
                    <input type="hidden" id="reportIdForAction" name="report_id">
                    <input type="hidden" id="actionType" name="action">
                    
                    <!-- Task Completion Check Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="bx bx-check-square me-2"></i>Task Completion Check</h6>
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                            <textarea name="comments" id="approverComments" class="form-control" rows="3" placeholder="Add any comments or feedback about task completion..."></textarea>
                        </div>
                    </div>

                    <!-- Quality Assessment Section (Only for Approval) -->
                    <div id="qualityAssessmentSection" style="display: none;">
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3"><i class="bx bx-star me-2 text-warning"></i>Quality Assessment (Performance Module)</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Quality Rating <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="btn-group" role="group" id="qualityRatingGroup">
                                        <input type="radio" class="btn-check" name="quality_rating" id="rating1" value="1" autocomplete="off">
                                        <label class="btn btn-outline-warning" for="rating1">⭐</label>
                                        
                                        <input type="radio" class="btn-check" name="quality_rating" id="rating2" value="2" autocomplete="off">
                                        <label class="btn btn-outline-warning" for="rating2">⭐⭐</label>
                                        
                                        <input type="radio" class="btn-check" name="quality_rating" id="rating3" value="3" autocomplete="off" checked>
                                        <label class="btn btn-outline-warning" for="rating3">⭐⭐⭐</label>
                                        
                                        <input type="radio" class="btn-check" name="quality_rating" id="rating4" value="4" autocomplete="off">
                                        <label class="btn btn-outline-warning" for="rating4">⭐⭐⭐⭐</label>
                                        
                                        <input type="radio" class="btn-check" name="quality_rating" id="rating5" value="5" autocomplete="off">
                                        <label class="btn btn-outline-warning" for="rating5">⭐⭐⭐⭐⭐</label>
                                    </div>
                                    <small class="text-muted">Rate the quality of work (1-5 stars)</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Complexity Tag</label>
                                <select name="complexity_tag" id="complexityTag" class="form-select">
                                    <option value="routine">Routine</option>
                                    <option value="standard" selected>Standard</option>
                                    <option value="complex">Complex</option>
                                </select>
                                <small class="text-muted">Tag the complexity level</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Initiative Bonus</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="initiative_bonus" id="initiativeBonus" value="1">
                                    <label class="form-check-label" for="initiativeBonus">
                                        Staff-initiated task (bonus points)
                                    </label>
                                </div>
                                <small class="text-muted">Check if this was a staff-initiated task</small>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Quality Comments</label>
                                <textarea name="quality_comments" id="qualityComments" class="form-control" rows="3" placeholder="Add quality assessment comments for improvement..."></textarea>
                                <small class="text-muted">Comments for performance tracking</small>
                            </div>
                        </div>

                        @php
                            $hasPerformanceLink = $report->isLinkedToPerformance();
                        @endphp
                        @if($hasPerformanceLink)
                            <div class="alert alert-info mt-3">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Performance Impact:</strong> This task is linked to a performance objective. 
                                Your assessment will contribute to performance scoring.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="submitActionBtn">
                        <span id="submitActionText">Approve</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const ajaxUrl = '{{ route("modules.tasks.action") }}';

function approveReport(reportId) {
    $('#reportIdForAction').val(reportId);
    $('#actionType').val('approve');
    $('#approveRejectModalTitle').text('Approve Report - Dual Assessment');
    $('#submitActionBtn').removeClass('btn-danger').addClass('btn-success');
    $('#submitActionText').text('Approve Report');
    $('#approverComments').val('');
    $('#qualityComments').val('');
    $('#qualityRatingGroup input[value="3"]').prop('checked', true);
    $('#complexityTag').val('standard');
    $('#initiativeBonus').prop('checked', false);
    $('#qualityAssessmentSection').show();
    $('#approveRejectModal').modal('show');
}

function rejectReport(reportId) {
    $('#reportIdForAction').val(reportId);
    $('#actionType').val('reject');
    $('#approveRejectModalTitle').text('Reject Report');
    $('#submitActionBtn').removeClass('btn-success').addClass('btn-danger');
    $('#submitActionText').text('Reject Report');
    $('#approverComments').val('');
    $('#qualityAssessmentSection').hide();
    $('#approveRejectModal').modal('show');
}

$('#approveRejectForm').on('submit', function(e) {
    e.preventDefault();
    const reportId = $('#reportIdForAction').val();
    const action = $('#actionType').val();
    const comments = $('#approverComments').val();
    
    // Disable submit button and show loading state immediately
    const submitBtn = $('#submitActionBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: {
            _token: csrfToken,
            action: action === 'approve' ? 'task_approve_report' : 'task_reject_report',
            report_id: reportId,
            comments: comments,
            quality_rating: action === 'approve' ? $('input[name="quality_rating"]:checked').val() : null,
            complexity_tag: action === 'approve' ? $('#complexityTag').val() : null,
            initiative_bonus: action === 'approve' ? $('#initiativeBonus').is(':checked') : false,
            quality_comments: action === 'approve' ? $('#qualityComments').val() : null
        },
        success: function(response) {
            // Re-enable button
            submitBtn.prop('disabled', false).html(originalText);
            
            if (response.success) {
                // Hide modal immediately
                $('#approveRejectModal').modal('hide');
                
                // Prepare success message - don't show delay-related text unless actually delayed
                let successMessage = action === 'approve' ? 'The report has been approved successfully.' : (response.message || 'Report rejected.');
                
                // Only show delay-related message if completion status is actually Delayed
                if (action === 'approve' && response.completion_status === 'Delayed') {
                    successMessage = 'Report approved. Note: This activity is marked as delayed.';
                }
                
                // Show success message immediately with high z-index
                Swal.fire({
                    icon: 'success',
                    title: action === 'approve' ? '✓ Report Approved Successfully!' : 'Report Rejected',
                    html: '<div style="font-size: 1.1rem; padding: 10px 0;">' + successMessage + '</div>',
                    timer: 2500,
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    position: 'top',
                    customClass: {
                        popup: 'swal2-popup-success',
                        title: 'swal2-title-success',
                        confirmButton: 'swal2-confirm-success'
                    },
                    didOpen: () => {
                        // Ensure it's on top of everything
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                            swalContainer.style.position = 'fixed';
                        }
                    }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to process request',
                    confirmButtonText: 'OK',
                    position: 'top',
                    customClass: {
                        popup: 'swal2-popup-error'
                    },
                    didOpen: () => {
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                            swalContainer.style.position = 'fixed';
                        }
                    }
                });
            }
        },
        error: function(xhr) {
            // Re-enable button on error
            submitBtn.prop('disabled', false).html(originalText);
            
            const message = xhr.responseJSON?.message || 'Failed to process request';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonText: 'OK',
                position: 'top',
                customClass: {
                    popup: 'swal2-popup-error'
                },
                didOpen: () => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.style.zIndex = '99999';
                        swalContainer.style.position = 'fixed';
                    }
                }
            });
        }
    });
});
</script>
@endpush

