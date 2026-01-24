@extends('layouts.app')

@section('title', $meeting->title . ' - Meeting Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .timeline-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: -20px;
        width: 2px;
        background: #e0e0e0;
    }
    .timeline-item:last-child:before {
        display: none;
    }
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e0e0;
    }
    .timeline-item.approved .timeline-marker {
        background: #28a745;
        box-shadow: 0 0 0 2px #28a745;
    }
    .timeline-item.rejected .timeline-marker {
        background: #dc3545;
        box-shadow: 0 0 0 2px #dc3545;
    }
    .timeline-item.submitted .timeline-marker {
        background: #ffc107;
        box-shadow: 0 0 0 2px #ffc107;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calendar-event me-2"></i>{{ $meeting->title }}
                            </h3>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $meeting->status)) }}
                                </span>
                                @if($meeting->branch_name)
                                    <span class="text-white-50">
                                        <i class="bx bx-map me-1"></i>{{ $meeting->branch_name }}
                                    </span>
                                @endif
                                @if($meeting->category_name)
                                    <span class="text-white-50">
                                        <i class="bx bx-category me-1"></i>{{ $meeting->category_name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('modules.meetings.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Meetings
                            </a>
                            @if($canEdit)
                                <a href="{{ route('modules.meetings.edit', $meeting->id) }}" class="btn btn-warning btn-lg shadow-sm">
                                    <i class="bx bx-edit me-1"></i>Edit Meeting
                                </a>
                            @endif
                            @if($minutes)
                                <a href="{{ route('modules.meetings.minutes.preview', $meeting->id) }}" class="btn btn-info btn-lg shadow-sm">
                                    <i class="bx bx-file me-1"></i>View Minutes
                                </a>
                            @endif
                            <a href="{{ route('modules.meetings.resolutions', $meeting->id) }}" class="btn btn-warning btn-lg shadow-sm">
                                <i class="bx bx-file-blank me-1"></i>View Resolutions
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
            <div class="card border-0 shadow-sm h-100 stat-card border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-user fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Participants</h6>
                            <h4 class="mb-0 fw-bold">{{ $stats['total_participants'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 stat-card border-success" style="border-left: 4px solid var(--bs-success) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success">
                            <i class="bx bx-list-check fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Agenda Items</h6>
                            <h4 class="mb-0 fw-bold">{{ $stats['total_agendas'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 stat-card border-info" style="border-left: 4px solid var(--bs-info) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-info">
                            <i class="bx bx-file fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Documents</h6>
                            <h4 class="mb-0 fw-bold">{{ $stats['total_documents'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 stat-card border-warning" style="border-left: 4px solid var(--bs-warning) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-check-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Confirmed</h6>
                            <h4 class="mb-0 fw-bold">{{ $stats['confirmed_attendees'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Progress Tracking -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-task me-2 text-primary"></i>Progress Tracking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: {{ $meeting->status === 'draft' ? '#e9ecef' : '#fff3cd' }}; border: 2px solid {{ $meeting->status === 'draft' ? '#6c757d' : '#ffc107' }};">
                                <i class="bx bx-edit fs-2 mb-2" style="color: {{ $meeting->status === 'draft' ? '#6c757d' : '#ffc107' }};"></i>
                                <h6 class="mb-1 fw-bold">Draft</h6>
                                <small class="text-muted">Meeting Created</small>
                                @if($meeting->status === 'draft')
                                    <div class="mt-2">
                                        <span class="badge bg-dark">Current</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: {{ $meeting->status === 'pending_approval' ? '#fff3cd' : ($meeting->status === 'approved' || $meeting->status === 'completed' ? '#d4edda' : '#e9ecef') }}; border: 2px solid {{ $meeting->status === 'pending_approval' ? '#ffc107' : ($meeting->status === 'approved' || $meeting->status === 'completed' ? '#28a745' : '#dee2e6') }};">
                                <i class="bx bx-time-five fs-2 mb-2" style="color: {{ $meeting->status === 'pending_approval' ? '#ffc107' : ($meeting->status === 'approved' || $meeting->status === 'completed' ? '#28a745' : '#6c757d') }};"></i>
                                <h6 class="mb-1 fw-bold">Pending Approval</h6>
                                <small class="text-muted">Awaiting Review</small>
                                @if($meeting->status === 'pending_approval')
                                    <div class="mt-2">
                                        <span class="badge bg-warning">Current</span>
                                    </div>
                                @elseif($meeting->status === 'approved' || $meeting->status === 'completed')
                                    <div class="mt-2">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: {{ $meeting->status === 'approved' || $meeting->status === 'completed' ? '#d4edda' : '#e9ecef' }}; border: 2px solid {{ $meeting->status === 'approved' || $meeting->status === 'completed' ? '#28a745' : '#dee2e6' }};">
                                <i class="bx bx-check-circle fs-2 mb-2" style="color: {{ $meeting->status === 'approved' || $meeting->status === 'completed' ? '#28a745' : '#6c757d' }};"></i>
                                <h6 class="mb-1 fw-bold">Approved</h6>
                                <small class="text-muted">Meeting Approved</small>
                                @if($meeting->status === 'approved' || $meeting->status === 'completed')
                                    <div class="mt-2">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: {{ $minutes && property_exists($minutes, 'status') && $minutes->status === 'approved' ? '#d4edda' : ($minutes ? '#fff3cd' : '#e9ecef') }}; border: 2px solid {{ $minutes && property_exists($minutes, 'status') && $minutes->status === 'approved' ? '#28a745' : ($minutes ? '#ffc107' : '#dee2e6') }};">
                                <i class="bx bx-file fs-2 mb-2" style="color: {{ $minutes && property_exists($minutes, 'status') && $minutes->status === 'approved' ? '#28a745' : ($minutes ? '#ffc107' : '#6c757d') }};"></i>
                                <h6 class="mb-1 fw-bold">Minutes</h6>
                                <small class="text-muted">{{ $minutes ? 'Created' : 'Not Created' }}</small>
                                @if($minutes && property_exists($minutes, 'status') && $minutes->status === 'approved')
                                    <div class="mt-2">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </div>
                                @elseif($minutes)
                                    <div class="mt-2">
                                        <span class="badge bg-warning">{{ ucfirst($minutes->status ?? 'Draft') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if(count($approvalHistory) > 0)
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Approval History</h6>
                        <div class="timeline-item {{ $meeting->status === 'approved' ? 'approved' : ($meeting->status === 'rejected' ? 'rejected' : 'submitted') }}">
                            @foreach($approvalHistory as $history)
                            <div class="timeline-item {{ $history['type'] }}">
                                <div class="timeline-marker"></div>
                                <div class="ms-4">
                                    <h6 class="mb-1 fw-semibold">{{ $history['action'] }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="bx bx-user me-1"></i>{{ $history['user'] }}
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($history['date'])->format('M d, Y h:i A') }}
                                    </p>
                                    @if(isset($history['reason']) && $history['reason'])
                                        <p class="mb-0 mt-2 text-muted small">
                                            <strong>Reason:</strong> {{ $history['reason'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Meeting Information -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-info-circle me-2 text-primary"></i>Meeting Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-calendar text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Meeting Date</h6>
                                    <p class="mb-0 fw-semibold">{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-time text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Time</h6>
                                    <p class="mb-0 fw-semibold">
                                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }} - 
                                        {{ \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-map text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Venue/Location</h6>
                                    <p class="mb-0 fw-semibold">{{ $meeting->venue ?? $meeting->location ?? 'TBD' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-video text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Meeting Type</h6>
                                    <p class="mb-0">
                                        @php
                                            $meetingType = $meeting->meeting_type ?? (isset($meeting->meeting_mode) ? ($meeting->meeting_mode == 'in_person' ? 'physical' : $meeting->meeting_mode) : 'physical');
                                        @endphp
                                        <span class="badge bg-info">{{ ucfirst($meetingType) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-category text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Category</h6>
                                    <p class="mb-0 fw-semibold">{{ $meeting->category_name ?? 'Uncategorized' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-user text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Created By</h6>
                                    <p class="mb-0 fw-semibold">{{ $meeting->creator_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        @if(isset($meeting->description) && $meeting->description)
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-file text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-2 small">Description / Objectives</h6>
                                    <p class="mb-0">{{ nl2br(e($meeting->description)) }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($updatedBy)
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-edit text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Last Updated</h6>
                                    <p class="mb-0 fw-semibold">{{ $updatedBy->name ?? 'N/A' }}</p>
                                    @if(isset($meeting->updated_at) && $meeting->updated_at)
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($meeting->updated_at)->format('M d, Y h:i A') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10">
                                    <i class="bx bx-calendar-check text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small">Created</h6>
                                    <p class="mb-0 fw-semibold">
                                        @if(isset($meeting->created_at) && $meeting->created_at)
                                            {{ \Carbon\Carbon::parse($meeting->created_at)->format('M d, Y h:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agenda Items -->
            @if($agendas && $agendas->count() > 0)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-list-check me-2 text-primary"></i>Agenda Items ({{ $agendas->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="agendaAccordion">
                        @foreach($agendas as $index => $agenda)
                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                    <div class="d-flex align-items-center w-100">
                                        <span class="badge bg-primary me-3">{{ $index + 1 }}</span>
                                        <div class="flex-grow-1 text-start">
                                            <strong>{{ $agenda->title }}</strong>
                                            @if($agenda->duration || (isset($agenda->duration_minutes) && $agenda->duration_minutes))
                                                <span class="badge bg-secondary ms-2">
                                                    {{ $agenda->duration ?? ($agenda->duration_minutes . ' min') }}
                                                </span>
                                            @endif
                                            @if($agenda->presenter_name)
                                                <br><small class="text-muted"><i class="bx bx-user me-1"></i>Presenter: {{ $agenda->presenter_name }}</small>
                                            @endif
                                        </div>
                                        @if($agenda->documents && $agenda->documents->count() > 0)
                                            <span class="badge bg-info me-2">{{ $agenda->documents->count() }} Docs</span>
                                        @endif
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#agendaAccordion">
                                <div class="accordion-body">
                                    @if($agenda->description)
                                        <div class="mb-3">
                                            <strong>Description:</strong>
                                            <p class="mb-0 mt-1">{{ nl2br(e($agenda->description)) }}</p>
                                        </div>
                                    @endif
                                    @if($agenda->documents && $agenda->documents->count() > 0)
                                        <div class="mt-3">
                                            <strong><i class="bx bx-paperclip me-2"></i>Documents ({{ $agenda->documents->count() }}):</strong>
                                            <div class="list-group mt-2">
                                                @foreach($agenda->documents as $doc)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bx bx-file me-2"></i>
                                                        <span>{{ $doc->original_name }}</span>
                                                        <small class="text-muted ms-2">({{ number_format($doc->file_size / 1024, 2) }} KB)</small>
                                                    </div>
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-download"></i> Download
                                                    </a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($agenda->status) && $agenda->status)
                                        <div class="mt-2">
                                            <span class="badge bg-{{ $agenda->status == 'done' ? 'success' : ($agenda->status == 'in_progress' ? 'info' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $agenda->status)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Approval History -->
            @if(count($approvalHistory) > 0)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-history me-2 text-primary"></i>Approval History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($approvalHistory as $history)
                        <div class="timeline-item {{ $history['type'] }}">
                            <div class="timeline-marker"></div>
                            <div>
                                <h6 class="mb-1">{{ $history['action'] }}</h6>
                                <p class="mb-1 text-muted small">
                                    <i class="bx bx-user me-1"></i>{{ $history['user'] }}
                                    <span class="ms-2">
                                        <i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($history['date'])->format('M d, Y h:i A') }}
                                    </span>
                                </p>
                                @if(isset($history['reason']))
                                    <p class="mb-0 text-muted small"><strong>Reason:</strong> {{ $history['reason'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Meeting Minutes -->
            @if($minutes)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-file me-2 text-primary"></i>Meeting Minutes
                    </h5>
                    <div>
                        <a href="{{ route('modules.meetings.minutes.preview', $meeting->id) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bx bx-show"></i> View
                        </a>
                        <a href="{{ route('modules.meetings.minutes.pdf', $meeting->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                            <i class="bx bx-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Prepared By:</strong>
                            <p class="mb-0">{{ (property_exists($minutes, 'prepared_by_name') && $minutes->prepared_by_name) ? $minutes->prepared_by_name : 'N/A' }}</p>
                        </div>
                        @if(property_exists($minutes, 'approved_by_name') && $minutes->approved_by_name)
                        <div class="col-md-6">
                            <strong>Approved By:</strong>
                            <p class="mb-0">{{ $minutes->approved_by_name }}</p>
                            @if(property_exists($minutes, 'approved_at') && $minutes->approved_at)
                                <small class="text-muted">{{ \Carbon\Carbon::parse($minutes->approved_at)->format('M d, Y h:i A') }}</small>
                            @endif
                        </div>
                        @endif
                        @if(property_exists($minutes, 'status') && $minutes->status)
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p class="mb-0">
                                <span class="badge bg-{{ $minutes->status == 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($minutes->status) }}
                                </span>
                            </p>
                        </div>
                        @endif
                        @if(property_exists($minutes, 'summary') && $minutes->summary)
                        <div class="col-12">
                            <strong>Summary:</strong>
                            <p class="mb-0 mt-1">{{ nl2br(e($minutes->summary)) }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Approval Section for Pending Approval -->
                    @if($canApprove && property_exists($minutes, 'status') && $minutes->status === 'pending_approval')
                    <div class="mt-4 pt-4 border-top">
                        <div class="alert alert-warning mb-3">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Action Required:</strong> These minutes are pending your approval.
                        </div>
                        <form id="minutesApprovalForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Comments (Optional)</label>
                                <textarea class="form-control" id="approval-comments" rows="3" placeholder="Add any comments or notes..."></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-lg" id="approve-minutes-btn">
                                    <i class="bx bx-check me-2"></i>Approve Minutes
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" id="reject-minutes-btn">
                                    <i class="bx bx-x me-2"></i>Reject Minutes
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @elseif($meeting->status === 'approved' || $meeting->status === 'completed')
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-file me-2 text-primary"></i>Meeting Minutes
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="bx bx-file-blank" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No minutes have been created yet.</p>
                    <a href="{{ route('modules.meetings.minutes.create', $meeting->id) }}" class="btn btn-success btn-lg">
                        <i class="bx bx-plus me-1"></i> Create Minutes
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-cog me-2 text-primary"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($canApprove && $meeting->status === 'pending_approval')
                            <button type="button" class="btn btn-success btn-lg" id="approve-meeting-btn">
                                <i class="bx bx-check me-1"></i> Approve Meeting
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" id="reject-meeting-btn">
                                <i class="bx bx-x me-1"></i> Reject Meeting
                            </button>
                        @endif
                        @if($canEdit)
                            <a href="{{ route('modules.meetings.edit', $meeting->id) }}" class="btn btn-warning btn-lg">
                                <i class="bx bx-edit me-1"></i> Edit Meeting
                            </a>
                        @endif
                        @if(!$minutes && ($meeting->status === 'approved' || $meeting->status === 'completed'))
                            <a href="{{ route('modules.meetings.minutes.create', $meeting->id) }}" class="btn btn-primary btn-lg">
                                <i class="bx bx-file me-1"></i> Create Minutes
                            </a>
                        @endif
                        @if($minutes)
                            <a href="{{ route('modules.meetings.minutes.preview', $meeting->id) }}" class="btn btn-info btn-lg">
                                <i class="bx bx-show me-1"></i> View Minutes
                            </a>
                            <a href="{{ route('modules.meetings.minutes.pdf', $meeting->id) }}" class="btn btn-danger btn-lg" target="_blank">
                                <i class="bx bx-file-pdf me-1"></i> Download PDF
                            </a>
                        @endif
                        <a href="{{ route('modules.meetings.resolutions', $meeting->id) }}" class="btn btn-warning btn-lg">
                            <i class="bx bx-file-blank me-1"></i> View Resolutions
                        </a>
                        <a href="{{ route('modules.meetings.resolutions.pdf', $meeting->id) }}" class="btn btn-warning btn-lg" target="_blank" style="background-color: #ff9800; border-color: #ff9800;">
                            <i class="bx bx-file-pdf me-1"></i> Resolutions PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Participants Summary -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-user me-2 text-primary"></i>Participants Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 fw-bold text-primary">{{ $stats['staff_participants'] }}</h4>
                                <small class="text-muted">Staff</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 fw-bold text-info">{{ $stats['external_participants'] }}</h4>
                                <small class="text-muted">External</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participants Details Table -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-list-ul me-2 text-primary"></i>All Participants
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 20%;">Email</th>
                                    <th style="width: 15%;">Phone</th>
                                    <th style="width: 20%;">Institution/Organization</th>
                                    <th style="width: 10%;">Type</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $index = 1;
                                @endphp
                                
                                <!-- Staff Participants -->
                                @forelse($staffParticipants as $participant)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2 bg-primary bg-opacity-10">
                                                <i class="bx bx-user text-primary"></i>
                                            </div>
                                            <strong>{{ $participant->user_name ?? $participant->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if($participant->user_email)
                                            <small>{{ $participant->user_email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->phone)
                                            <small>{{ $participant->phone }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->institution)
                                            <small>{{ $participant->institution }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Staff</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $participant->attendance_status == 'confirmed' || $participant->attendance_status == 'attended' ? 'success' : ($participant->attendance_status == 'declined' ? 'danger' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $participant->attendance_status ?? 'invited')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                @endforelse

                                <!-- External Participants -->
                                @forelse($externalParticipants as $participant)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2 bg-info bg-opacity-10">
                                                <i class="bx bx-user-circle text-info"></i>
                                            </div>
                                            <strong>{{ $participant->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if($participant->email)
                                            <small>{{ $participant->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->phone)
                                            <small>{{ $participant->phone }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->institution)
                                            <small>{{ $participant->institution }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">External</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $participant->attendance_status == 'confirmed' || $participant->attendance_status == 'attended' ? 'success' : ($participant->attendance_status == 'declined' ? 'danger' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $participant->attendance_status ?? 'invited')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                @endforelse

                                @if($staffParticipants->count() == 0 && $externalParticipants->count() == 0)
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bx bx-info-circle me-2"></i>No participants found
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Meeting Status & Information -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-info-circle me-2 text-primary"></i>Status & Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <div class="mt-1">
                            <span class="badge bg-{{ $meeting->status == 'approved' ? 'success' : ($meeting->status == 'pending_approval' ? 'warning' : ($meeting->status == 'rejected' ? 'danger' : ($meeting->status == 'completed' ? 'info' : 'secondary'))) }} fs-6 px-3 py-2">
                                {{ ucfirst(str_replace('_', ' ', $meeting->status)) }}
                            </span>
                        </div>
                    </div>
                    @if(isset($meeting->reference_code) && $meeting->reference_code)
                    <div class="mb-3">
                        <strong>Reference Code:</strong>
                        <p class="mb-0">{{ $meeting->reference_code }}</p>
                    </div>
                    @endif
                    @if(isset($meeting->virtual_link) && $meeting->virtual_link)
                    <div class="mb-3">
                        <strong>Virtual Link:</strong>
                        <p class="mb-0">
                            <a href="{{ $meeting->virtual_link }}" target="_blank" class="text-primary">
                                {{ $meeting->virtual_link }}
                                <i class="bx bx-link-external ms-1"></i>
                            </a>
                        </p>
                    </div>
                    @endif
                    <div class="mb-0">
                        <strong>Created:</strong>
                        <p class="mb-0 small text-muted">
                            @if(isset($meeting->created_at) && $meeting->created_at)
                                {{ \Carbon\Carbon::parse($meeting->created_at)->format('M d, Y h:i A') }}
                            @else
                                N/A
                            @endif
                        </p>
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
const csrfToken = '{{ csrf_token() }}';
const meetingId = {{ $meeting->id }};
const ajaxUrl = '{{ route("modules.meetings.ajax") }}';

@if($canApprove && $meeting->status === 'pending_approval')
// Approve Meeting
$('#approve-meeting-btn').on('click', function() {
    Swal.fire({
        title: 'Approve Meeting?',
        html: 'This will approve the meeting and send SMS notifications to all participants.<br><br><textarea id="approval-message" class="form-control" rows="3" placeholder="Custom message for SMS (optional)"></textarea>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve & Send SMS',
        confirmButtonColor: '#28a745',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            const message = $('#approval-message').val();
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    action: 'approve_meeting',
                    meeting_id: meetingId,
                    custom_message: message
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Approved!',
                            text: 'Meeting approved and SMS sent to participants',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', response.message || 'Failed to approve meeting', 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to approve meeting. Please try again.';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});

// Reject Meeting
$('#reject-meeting-btn').on('click', function() {
    Swal.fire({
        title: 'Reject Meeting?',
        input: 'textarea',
        inputPlaceholder: 'Reason for rejection...',
        inputAttributes: { required: true },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Reject Meeting',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    action: 'reject_meeting',
                    meeting_id: meetingId,
                    reason: result.value
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Rejected',
                            text: 'Meeting has been rejected',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', response.message || 'Failed to reject meeting', 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to reject meeting. Please try again.';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});
@endif

@if($minutes && $canApprove && property_exists($minutes, 'status') && $minutes->status === 'pending_approval')
// Approve Minutes
$('#approve-minutes-btn').on('click', function() {
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
                    comments: $('#approval-comments').val() || ''
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
                            text: response.message || 'Minutes have been approved successfully',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to approve minutes', 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to approve minutes. Please try again.';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});

// Reject Minutes
$('#reject-minutes-btn').on('click', function() {
    Swal.fire({
        title: 'Reject Minutes?',
        input: 'textarea',
        inputLabel: 'Reason for rejection',
        inputPlaceholder: 'Please provide a reason for rejecting these minutes...',
        inputAttributes: { required: true },
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
        if (result.isConfirmed && result.value) {
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
                            icon: 'info',
                            title: 'Rejected',
                            text: response.message || 'Minutes have been rejected',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to reject minutes', 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to reject minutes. Please try again.';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});
@endif
</script>
@endpush
