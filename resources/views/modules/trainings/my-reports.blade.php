@extends('layouts.app')

@section('title', 'My Training Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bx bx-clipboard me-2"></i>My Training Reports
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Training Permissions Section -->
                    @if($trainingPermissions->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bx bx-calendar-check me-2"></i>My Approved Training Permissions
                            </h5>
                            <div class="row">
                                @foreach($trainingPermissions as $permission)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="bx bx-book me-2"></i>
                                                    {{ $permission->training->topic ?? 'Training' }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-2">
                                                    <strong>Permission ID:</strong> {{ $permission->request_id }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>Dates:</strong> 
                                                    {{ $permission->start_datetime->format('M d, Y') }} - 
                                                    {{ $permission->end_datetime->format('M d, Y') }}
                                                </p>
                                                @php
                                                    $permissionDates = $permission->requested_dates;
                                                    $reportedDates = $permission->training->reports
                                                        ->where('created_by', auth()->id())
                                                        ->pluck('report_date')
                                                        ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                                                        ->toArray();
                                                    $reportedCount = count(array_intersect($permissionDates, $reportedDates));
                                                    $totalDates = count($permissionDates);
                                                @endphp
                                                <p class="mb-2">
                                                    <strong>Reporting Progress:</strong> 
                                                    <span class="badge bg-{{ $reportedCount == $totalDates ? 'success' : 'warning' }}">
                                                        {{ $reportedCount }} / {{ $totalDates }} days reported
                                                    </span>
                                                </p>
                                                <a href="{{ route('trainings.report', $permission->training_id) }}?permission_request_id={{ $permission->id }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bx bx-edit me-1"></i>Submit Report
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- My Reports Section -->
                    <div>
                        <h5 class="mb-3">
                            <i class="bx bx-file me-2"></i>All My Training Reports
                        </h5>
                        @if($myReports->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Training</th>
                                            <th>Report Date</th>
                                            <th>Activities Completed</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($myReports as $report)
                                            <tr>
                                                <td>
                                                    <strong>{{ $report->training->topic ?? 'N/A' }}</strong>
                                                    @if($report->permissionRequest)
                                                        <br><small class="text-muted">
                                                            Permission: {{ $report->permissionRequest->request_id }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($report->report_date)->format('M d, Y') }}</td>
                                                <td>
                                                    {{ \Illuminate\Support\Str::limit($report->activities_completed ?? 'N/A', 50) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Submitted</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('trainings.show', $report->training_id) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View Training">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                You haven't submitted any training reports yet.
                                @if($trainingPermissions->count() > 0)
                                    <a href="{{ route('trainings.report', $trainingPermissions->first()->training_id) }}?permission_request_id={{ $trainingPermissions->first()->id }}" 
                                       class="alert-link">Submit your first report</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

