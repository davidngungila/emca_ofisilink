@extends('layouts.app')

@section('title', 'Meeting Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    .meeting-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }
    .meeting-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .swal2-container { z-index: 200000 !important; }
    .select2-container { z-index: 200001 !important; }
    .flatpickr-calendar { z-index: 200002 !important; }
    .flatpickr-calendar:not(.open) {
        display: none !important;
    }
    .flatpickr-calendar.open {
        z-index: 9999;
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
                                <i class="bx bx-calendar-event me-2"></i>Meeting Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive meeting management system with agendas, participants, and minutes tracking
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <!-- Branch Filter -->
                            <div class="me-3">
                                <label class="text-white-50 small mb-1 d-block">Filter by Branch</label>
                                <form method="GET" action="{{ route('modules.meetings.index') }}" class="d-inline">
                                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 200px;">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }} ({{ $branch->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                            @if($canManageMeetings)
                                <a href="{{ route('modules.meetings.create') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-plus me-2"></i>Create Meeting
                                </a>
                            @endif
                            @if($canApproveMeetings)
                                <a href="{{ route('modules.meetings.pending-approval') }}" class="btn btn-light btn-lg shadow-sm position-relative">
                                    <i class="bx bx-time me-2"></i>Pending Approvals
                                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="pending-count-badge">0</span>
                                </a>
                            @endif
                            <a href="{{ route('modules.meetings.analytics') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-bar-chart me-2"></i>Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-calendar fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Meetings</h6>
                            <h3 class="mb-0 fw-bold text-primary" id="stat-total-meetings">{{ $stats['total_meetings'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-trending-up me-1"></i>All Time
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bx bx-calendar-check fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Upcoming Today</h6>
                            <h3 class="mb-0 fw-bold text-success" id="stat-upcoming">{{ $stats['upcoming'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-check-circle me-1"></i>Scheduled
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="bx bx-time-five fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Pending Approval</h6>
                            <h3 class="mb-0 fw-bold text-warning" id="stat-pending">{{ $stats['pending_approval'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bx bx-time me-1"></i>Awaiting Review
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="bx bx-file fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Minutes Pending</h6>
                            <h3 class="mb-0 fw-bold text-danger" id="stat-minutes-pending">{{ $stats['minutes_pending'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="bx bx-edit me-1"></i>Need Minutes
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-bottom">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-bolt-circle me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($canManageMeetings)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.create') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                        <i class="bx bx-calendar-plus fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Create Meeting</h6>
                                    <small class="text-muted">Schedule new meeting</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        @if($canApproveMeetings)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.pending-approval') }}" class="card border-warning h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <i class="bx bx-time fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Pending Approvals</h6>
                                    <small class="text-muted"><span id="pending-count-badge-2">0</span> awaiting</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.analytics') }}" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                        <i class="bx bx-bar-chart fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Analytics</h6>
                                    <small class="text-muted">View statistics</small>
                                </div>
                            </a>
                        </div>
                        
                        @if($canManageMeetings)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.categories') }}" class="card border-secondary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-secondary">
                                        <i class="bx bx-category fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Categories</h6>
                                    <small class="text-muted">Manage categories</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.index') }}?status=approved" class="card border-success h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <i class="bx bx-check-circle fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Approved Meetings</h6>
                                    <small class="text-muted">View approved</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.index') }}?status=completed" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                        <i class="bx bx-check-double fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Completed Meetings</h6>
                                    <small class="text-muted">View completed</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.meetings.index') }}?status=draft" class="card border-secondary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-secondary">
                                        <i class="bx bx-file fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Draft Meetings</h6>
                                    <small class="text-muted">View drafts</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meetings List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-list-ul me-2"></i>Meetings
                        </h5>
                        <div class="d-flex gap-2">
                            <!-- Filters -->
                            <div class="input-group" style="max-width: 200px;">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="filter-search" placeholder="Search...">
                            </div>
                            <select class="form-select form-select-sm" id="filter-status" style="max-width: 150px;">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="pending_approval">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select class="form-select form-select-sm" id="filter-category" style="max-width: 150px;">
                                <option value="">All Categories</option>
                            </select>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary active" id="view-list">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                                <button class="btn btn-sm btn-outline-primary" id="view-grid">
                                    <i class="bx bx-grid-alt"></i> Grid
                                </button>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-btn">
                                <i class="bx bx-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="meetings-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-3">Loading meetings...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Meeting Modal (Multi-Step Wizard) -->
<div class="modal fade" id="meetingWizardModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-calendar-plus"></i> <span id="wizard-title">Create Meeting</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Wizard Steps -->
                    <div class="col-md-3">
                        <div class="wizard-step active" data-step="1">
                            <span class="step-number">1</span> Basic Information
                        </div>
                        <div class="wizard-step" data-step="2">
                            <span class="step-number">2</span> Participants
                        </div>
                        <div class="wizard-step" data-step="3">
                            <span class="step-number">3</span> Agenda Items
                        </div>
                        <div class="wizard-step" data-step="4">
                            <span class="step-number">4</span> Review & Submit
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div class="col-md-9">
                        <form id="meetingForm">
                            @csrf
                            <input type="hidden" name="meeting_id" id="meeting_id">

                            <!-- Step 1: Basic Information -->
                            <div class="wizard-content" id="step-1">
                                <h5 class="mb-4"><i class="bx bx-info-circle"></i> Basic Information</h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Meeting Title *</label>
                                            <input type="text" name="title" class="form-control" required placeholder="Enter meeting title">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Category *</label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Date *</label>
                                            <input type="text" name="meeting_date" class="form-control datepicker" required placeholder="Select date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Start Time *</label>
                                            <input type="text" name="start_time" class="form-control timepicker" required placeholder="Select time">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">End Time *</label>
                                            <input type="text" name="end_time" class="form-control timepicker" required placeholder="Select time">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Venue/Location *</label>
                                            <input type="text" name="venue" class="form-control" required placeholder="Enter venue or meeting link">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Meeting Type</label>
                                            <select name="meeting_type" class="form-select">
                                                <option value="physical">Physical</option>
                                                <option value="virtual">Virtual</option>
                                                <option value="hybrid">Hybrid</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description/Objectives</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Enter meeting description or objectives"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-success save-step-btn" data-step="1">
                                        <i class="bx bx-save"></i> Save Basic Info
                                    </button>
                                </div>
                            </div>

                            <!-- Step 2: Participants -->
                            <div class="wizard-content d-none" id="step-2">
                                <h5 class="mb-4"><i class="bx bx-user-plus"></i> Participants</h5>
                                
                                <!-- Staff Participants -->
                                <div class="mb-4">
                                    <h6><i class="bx bx-user"></i> Internal Staff</h6>
                                    <div class="mb-3">
                                        <select id="staff-select" class="form-select" multiple>
                                            <!-- Staff loaded dynamically -->
                                        </select>
                                    </div>
                                    <div id="selected-staff-list" class="row">
                                        <!-- Selected staff shown here -->
                                    </div>
                                </div>

                                <hr>

                                <!-- External Participants -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="bx bx-user-voice"></i> External Participants</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-external-btn">
                                            <i class="bx bx-plus"></i> Add External
                                        </button>
                                    </div>
                                    <div id="external-participants-list">
                                        <!-- External participants added here -->
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary prev-step-btn" data-step="2">
                                        <i class="bx bx-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-success save-step-btn" data-step="2">
                                        <i class="bx bx-save"></i> Save Participants
                                    </button>
                                </div>
                            </div>

                            <!-- Step 3: Agenda Items -->
                            <div class="wizard-content d-none" id="step-3">
                                <h5 class="mb-4"><i class="bx bx-list-check"></i> Agenda Items</h5>
                                <div id="agenda-items-list">
                                    <!-- Agenda items added here -->
                                </div>
                                <div class="text-end mb-3">
                                    <button type="button" class="btn btn-outline-primary" id="add-agenda-item-btn">
                                        <i class="bx bx-plus"></i> Add Agenda Item
                                    </button>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary prev-step-btn" data-step="3">
                                        <i class="bx bx-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-success save-step-btn" data-step="3">
                                        <i class="bx bx-save"></i> Save Agenda
                                    </button>
                                </div>
                            </div>

                            <!-- Step 4: Review & Submit -->
                            <div class="wizard-content d-none" id="step-4">
                                <h5 class="mb-4"><i class="bx bx-check-circle"></i> Review & Submit</h5>
                                <div id="review-content">
                                    <!-- Review content loaded here -->
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary prev-step-btn" data-step="4">
                                        <i class="bx bx-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-primary" id="submit-meeting-btn">
                                        <i class="bx bx-check"></i> Submit for Approval
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Set AJAX URL for meetings
window.meetingAjaxUrl = '{{ route("modules.meetings.ajax") }}';
</script>
<script src="{{ asset('js/meetings.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize date picker only if element exists
    if (document.getElementById('filter-date')) {
        flatpickr('#filter-date', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            appendTo: document.body,
            static: false,
            clickOpens: true
        });
    }
    
    // Update pending count badge
    updatePendingCountBadge();
    
    // Refresh meetings on page load
    refreshAll();
});
</script>
@endpush
