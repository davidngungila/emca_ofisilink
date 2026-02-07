@extends('layouts.app')

@section('title', 'Attendance Reports')

@push('styles')
<style>
    .report-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(148, 0, 0, 0.15);
        border-color: #940000;
    }
    .report-card-header {
        background: linear-gradient(135deg, #940000 0%, #500000 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    .report-card-body {
        padding: 20px;
    }
    .report-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }
    .report-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 15px;
        min-height: 40px;
    }
    .btn-report {
        background-color: #940000;
        border-color: #940000;
        color: white;
        width: 100%;
    }
    .btn-report:hover {
        background-color: #500000;
        border-color: #500000;
        color: white;
    }
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-file-blank me-2"></i>Advanced Attendance Reports
                            </h4>
                            <p class="card-text text-white-50 mb-0">Generate comprehensive attendance reports in PDF format</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.attendance') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Attendance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-section">
                <h5 class="mb-3"><i class="bx bx-filter me-2"></i>Report Filters</h5>
                <form id="reportFilterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date (for Daily Report)</label>
                            <input type="date" class="form-control" id="reportDate" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="dateFrom" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="dateTo" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department (Optional)</label>
                            <select class="form-select" id="departmentId">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row g-4">
        <!-- 1. Daily Attendance Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-calendar-check"></i>
                    </div>
                    <h5 class="mb-0">Daily Attendance Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Know who was present today. Includes employee name/ID, department, check-in/out times, and status.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('daily')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Monthly Attendance Summary -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-bar-chart-alt-2"></i>
                    </div>
                    <h5 class="mb-0">Monthly Attendance Summary</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Overall attendance performance. Includes total working days, days present/absent, late days, and leave days.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('monthly')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Late Coming Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <h5 class="mb-0">Late Coming Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Track punctuality. Includes employee name, date, expected vs actual time, and minutes late.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('late')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 4. Absenteeism Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-user-x"></i>
                    </div>
                    <h5 class="mb-0">Absenteeism Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Identify frequent absences. Includes employee name, dates absent, reason, and number of absence days.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('absenteeism')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. Overtime Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-time"></i>
                    </div>
                    <h5 class="mb-0">Overtime Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Track extra working hours. Includes employee name, normal hours, overtime hours, and approval status.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('overtime')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 6. Leave Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-calendar"></i>
                    </div>
                    <h5 class="mb-0">Leave Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Monitor leave usage. Includes leave type, leave balance, and approved leave dates.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('leave')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. Department Attendance Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-buildings"></i>
                    </div>
                    <h5 class="mb-0">Department Attendance Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Compare departments. Includes department name, total staff, attendance percentage, and absentee rate.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('department')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- 8. Attendance Exception Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bx bx-error-circle"></i>
                    </div>
                    <h5 class="mb-0">Attendance Exception Report</h5>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Highlight irregularities. Includes missing check-ins/outs, duplicate entries, and unauthorized absences.
                    </p>
                    <button class="btn btn-report" onclick="generateReport('exception')">
                        <i class="bx bx-file-blank me-1"></i>Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generateReport(reportType) {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const reportDate = document.getElementById('reportDate').value;
    const departmentId = document.getElementById('departmentId').value;
    
    if (!dateFrom || !dateTo) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please select date range (Date From and Date To)'
        });
        return;
    }
    
    // For daily report, use the specific date
    let url = '{{ route("modules.hr.attendance.reports.advanced") }}?report_type=' + reportType + '&format=pdf';
    
    if (reportType === 'daily') {
        if (!reportDate) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a date for the daily report'
            });
            return;
        }
        url += '&date=' + reportDate;
    } else {
        url += '&date_from=' + dateFrom + '&date_to=' + dateTo;
    }
    
    if (departmentId) {
        url += '&department_id=' + departmentId;
    }
    
    // Open in new tab
    window.open(url, '_blank');
}

// Initialize tooltips if needed
document.addEventListener('DOMContentLoaded', function() {
    // Any initialization code
});
</script>
@endpush
@endsection

