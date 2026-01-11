@extends('layouts.app')

@section('title', 'Edit Meeting - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-edit me-2"></i>Edit Meeting
                            </h4>
                            <p class="card-text text-white-50 mb-0">{{ $meeting->title }}</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-light me-2">
                                <i class="bx bx-arrow-back me-1"></i>Back to Meeting
                            </a>
                            <a href="{{ route('modules.meetings.index') }}" class="btn btn-light">
                                <i class="bx bx-list-ul me-1"></i>All Meetings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Meeting Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('modules.meetings.partials.meeting-form', ['meeting' => $meeting, 'categories' => $categories, 'branches' => $branches, 'departments' => $departments, 'participants' => $participants, 'agendas' => $agendas])
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
const csrfToken = '{{ csrf_token() }}';
const ajaxUrl = '{{ route("modules.meetings.ajax") }}';
const meetingId = {{ $meeting->id }};
</script>
<script src="{{ asset('js/meetings-form.js') }}"></script>
@endpush


