@extends('layouts.app')

@section('title', 'Meeting Resolutions - ' . $meeting->title)

@push('styles')
<style>
    .resolution-card {
        border-left: 4px solid #ffc107;
        transition: transform 0.2s;
    }
    .resolution-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .resolution-status {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-warning" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-dark p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-dark fw-bold">
                                <i class="bx bx-file-blank me-2"></i>Meeting Resolutions
                            </h3>
                            <p class="mb-0 text-dark-50 fs-6">
                                {{ $meeting->title }} - {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, F d, Y') }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-dark btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Meeting
                            </a>
                            <a href="{{ route('modules.meetings.resolutions.pdf', $meeting->id) }}" class="btn btn-danger btn-lg shadow-sm" target="_blank">
                                <i class="bx bx-file-pdf me-2"></i>Export PDF
                            </a>
                            <button onclick="window.print()" class="btn btn-dark btn-lg shadow-sm">
                                <i class="bx bx-printer me-2"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolutions List -->
    <div class="row">
        <div class="col-12">
            @if($resolutions->count() > 0)
                @foreach($resolutions as $resolution)
                <div class="card mb-4 resolution-card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <span class="badge bg-warning text-dark me-2">{{ $resolution->resolution_number ?? 'RES-' . ($loop->index + 1) }}</span>
                                {{ $resolution->title }}
                            </h5>
                        </div>
                        <span class="badge resolution-status bg-{{ 
                            $resolution->status == 'approved' ? 'success' : 
                            ($resolution->status == 'rejected' ? 'danger' : 
                            ($resolution->status == 'deferred' ? 'warning' : 
                            ($resolution->status == 'proposed' ? 'info' : 'secondary'))) 
                        }}">
                            {{ ucfirst($resolution->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if($resolution->description)
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Background/Context:</h6>
                            <p class="mb-0" style="text-align: justify; line-height: 1.8;">{{ $resolution->description }}</p>
                        </div>
                        @endif
                        
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Resolution Statement:</h6>
                            <p class="mb-0 fw-semibold" style="text-align: justify; line-height: 1.8; font-size: 1.05rem;">{{ $resolution->resolution_text }}</p>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                @if($resolution->proposer_name)
                                <p class="mb-1">
                                    <strong>Proposed By:</strong> {{ $resolution->proposer_name }}
                                </p>
                                @endif
                                @if($resolution->seconder_name)
                                <p class="mb-0">
                                    <strong>Seconded By:</strong> {{ $resolution->seconder_name }}
                                </p>
                                @endif
                            </div>
                            <div class="col-md-6 text-md-end">
                                @if($resolution->approved_at && $resolution->approver_name)
                                <p class="mb-1">
                                    <strong>Approved By:</strong> {{ $resolution->approver_name }}
                                </p>
                                <p class="mb-0 text-muted small">
                                    {{ \Carbon\Carbon::parse($resolution->approved_at)->format('M d, Y h:i A') }}
                                </p>
                                @endif
                            </div>
                        </div>
                        
                        @if($resolution->approval_notes)
                        <div class="mt-3 p-2 bg-info bg-opacity-10 rounded">
                            <strong>Approval Notes:</strong> {{ $resolution->approval_notes }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-file-blank fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No Resolutions Found</h5>
                        <p class="text-muted">No resolutions have been prepared for this meeting yet.</p>
                        <a href="{{ route('modules.meetings.edit', $meeting->id) }}" class="btn btn-warning">
                            <i class="bx bx-plus me-1"></i>Add Resolutions
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection





