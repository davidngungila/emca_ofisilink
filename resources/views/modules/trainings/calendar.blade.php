@extends('layouts.app')

@section('title', 'Training Calendar')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calendar me-2"></i>Training Calendar
                            </h3>
                            <p class="mb-0 text-white-50">View all trainings in calendar format</p>
                        </div>
                        <a href="{{ route('trainings.index') }}" class="btn btn-light">
                            <i class="bx bx-arrow-back me-1"></i>Back to Trainings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                @foreach($trainings as $training)
                {
                    title: '{{ $training->topic }}',
                    start: '{{ $training->start_date ? $training->start_date->format('Y-m-d') : '' }}',
                    end: '{{ $training->end_date ? $training->end_date->format('Y-m-d') : '' }}',
                    url: '{{ route('trainings.show', $training->id) }}',
                    color: '{{ $training->status == 'completed' ? '#28a745' : ($training->status == 'ongoing' ? '#17a2b8' : '#6c757d') }}'
                },
                @endforeach
            ],
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        });
        calendar.render();
    });
</script>
@endpush




