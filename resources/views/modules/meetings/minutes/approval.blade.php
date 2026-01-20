@extends('layouts.app')

@section('title', 'Approve Meeting Minutes - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .minutes-preview {
        background: white;
        padding: 30px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #ffc107 0%, #ffb300 50%, #ffc107 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-check-circle me-2"></i>Approve Meeting Minutes
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Review and approve meeting minutes for {{ $meeting->title }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Meeting
                            </a>
                            <a href="{{ route('modules.meetings.minutes.preview', $meeting->id) }}" class="btn btn-light btn-lg shadow-sm" target="_blank">
                                <i class="bx bx-show me-2"></i>Preview
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-info-circle me-2"></i>Approval Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Meeting:</strong> {{ $meeting->title }}</p>
                            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, d F Y') }}</p>
                            <p><strong>Prepared By:</strong> {{ $preparedBy->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge bg-warning">Pending Approval</span></p>
                            <p><strong>Submitted:</strong> {{ $minutes->created_at ? \Carbon\Carbon::parse($minutes->created_at)->format('d M Y, h:i A') : 'N/A' }}</p>
                            <p><strong>Your Action Required:</strong> Review and approve or reject these minutes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minutes Preview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-file me-2"></i>Meeting Minutes Preview</h5>
                </div>
                <div class="card-body">
                    @include('modules.meetings.partials.minutes-preview')
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-check-circle me-2"></i>Take Action</h5>
                </div>
                <div class="card-body">
                    <form id="approvalForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" id="approval-comments" rows="3" placeholder="Add any comments or notes..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-lg" id="approve-btn">
                                <i class="bx bx-check me-2"></i>Approve Minutes
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" id="reject-btn">
                                <i class="bx bx-x me-2"></i>Reject Minutes
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
<script>
const meetingId = {{ $meeting->id }};
const ajaxUrl = '{{ route('modules.meetings.ajax') }}';
const csrfToken = '{{ csrf_token() }}';

$(document).ready(function() {
    // Approve button
    $('#approve-btn').on('click', function() {
        Swal.fire({
            title: 'Approve Minutes?',
            text: 'Are you sure you want to approve these meeting minutes?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'approve_minutes',
                        meeting_id: meetingId,
                        comments: $('#approval-comments').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                text: response.message || 'Minutes have been approved successfully',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route('modules.meetings.show', $meeting->id) }}';
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to approve minutes', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to approve minutes. Please try again.', 'error');
                    }
                });
            }
        });
    });

    // Reject button
    $('#reject-btn').on('click', function() {
        Swal.fire({
            title: 'Reject Minutes?',
            text: 'Please provide a reason for rejection',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            inputAttributes: {
                'aria-label': 'Enter rejection reason'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to provide a reason!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'reject_minutes',
                        meeting_id: meetingId,
                        reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected!',
                                text: response.message || 'Minutes have been rejected',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route('modules.meetings.show', $meeting->id) }}';
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to reject minutes', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to reject minutes. Please try again.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush






