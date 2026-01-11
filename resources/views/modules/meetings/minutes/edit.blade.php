@extends('layouts.app')

@section('title', 'Edit Meeting Minutes - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-edit me-2"></i>Edit Meeting Minutes
                            </h4>
                            <p class="card-text text-white-50 mb-0">{{ $meeting->title }}</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Meeting
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Meeting Information</h5>
                    <div class="row">
                        <div class="col-md-3"><strong>Date:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, F d, Y') }}</div>
                        <div class="col-md-3"><strong>Time:</strong> {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }}</div>
                        <div class="col-md-3"><strong>Venue:</strong> {{ $meeting->venue ?? 'TBD' }}</div>
                        <div class="col-md-3"><strong>Category:</strong> {{ $meeting->category_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minutes Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Minutes editing form will be loaded here. This will use the existing minutes modal functionality from the main meetings page.</p>
                    <p class="text-info">Note: The minutes editing interface will be integrated from the existing meeting management system.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Minutes editing will use the existing AJAX functionality
const csrfToken = '{{ csrf_token() }}';
const ajaxUrl = '{{ route("modules.meetings.ajax") }}';
const meetingId = {{ $meeting->id }};
</script>
@endpush


