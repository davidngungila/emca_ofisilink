@extends('layouts.app')

@section('title', 'Training Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                                <i class="bx bx-bar-chart me-2"></i>Training Analytics
                            </h3>
                            <p class="mb-0 text-white-50">Comprehensive training statistics and insights</p>
                        </div>
                        <a href="{{ route('trainings.index') }}" class="btn btn-light">
                            <i class="bx bx-arrow-back me-1"></i>Back to Trainings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('trainings.analytics') }}" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Trainings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTrainings }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-book-open bx-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedTrainings }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-check-circle bx-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Participants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalParticipants }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-user bx-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Reports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalReports }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-clipboard bx-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Trainings by Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Trainings by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Rated Trainings -->
    @if($topRatedTrainings->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Top Rated Trainings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Training</th>
                                    <th>Average Rating</th>
                                    <th>Total Evaluations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topRatedTrainings as $training)
                                <tr>
                                    <td>
                                        <a href="{{ route('trainings.show', $training->id) }}">
                                            {{ $training->topic }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ number_format($training->evaluations_avg_overall_rating, 1) }} / 5.0
                                        </span>
                                    </td>
                                    <td>{{ $training->evaluations_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($trainingsByStatus->pluck('status')) !!},
            datasets: [{
                data: {!! json_encode($trainingsByStatus->pluck('count')) !!},
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
            }]
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($trainingsByCategory->pluck('category')) !!},
            datasets: [{
                label: 'Trainings',
                data: {!! json_encode($trainingsByCategory->pluck('count')) !!},
                backgroundColor: '#4e73df'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush




