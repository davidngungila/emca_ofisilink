@extends('layouts.app')

@section('title', 'Assessment Analytics - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bx bx-bar-chart-alt-2 me-2"></i>Assessment Analytics</h4>
                            <p class="mb-0 text-muted">Comprehensive analytics and insights</p>
                        </div>
                        <a href="{{ route('modules.hr.performance_management_module') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">Analytics Dashboard</h5>
                        <div class="d-flex gap-2">
                            <!-- Period Type Selector -->
                            <select class="form-select" id="analytics-period-type" style="width: auto;">
                                <option value="year">Yearly</option>
                                <option value="semi">Semi-Annual</option>
                                <option value="quarter">Quarterly</option>
                                <option value="month">Monthly</option>
                            </select>

                            <!-- Year Selector (Always visible) -->
                            <input type="number" class="form-control" id="analytics-year" value="{{ date('Y') }}" min="2000" max="2100" style="width: 100px;">

                            <!-- Semi-Annual Selector -->
                            <select class="form-select d-none" id="analytics-period-semi" style="width: auto;">
                                <option value="h1">H1 (Jan - Jun)</option>
                                <option value="h2">H2 (Jul - Dec)</option>
                            </select>

                            <!-- Quarterly Selector -->
                            <select class="form-select d-none" id="analytics-period-quarter" style="width: auto;">
                                <option value="q1">Q1 (Jan - Mar)</option>
                                <option value="q2">Q2 (Apr - Jun)</option>
                                <option value="q3">Q3 (Jul - Sep)</option>
                                <option value="q4">Q4 (Oct - Dec)</option>
                            </select>

                            <!-- Monthly Selector -->
                            <select class="form-select d-none" id="analytics-period-month" style="width: auto;">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>

                            <button class="btn btn-primary" id="btn-load-analytics">
                                <i class="bx bx-refresh"></i> Load
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="analytics-content">
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-bar-chart-alt-2" style="font-size: 3rem;"></i>
                            <p class="mt-2">Click "Load" to view comprehensive analytics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Toggle inputs based on period type
$('#analytics-period-type').on('change', function() {
    const type = $(this).val();
    $('#analytics-period-semi, #analytics-period-quarter, #analytics-period-month').addClass('d-none');
    
    if (type === 'semi') $('#analytics-period-semi').removeClass('d-none');
    if (type === 'quarter') $('#analytics-period-quarter').removeClass('d-none');
    if (type === 'month') $('#analytics-period-month').removeClass('d-none');
});

$('#btn-load-analytics').on('click', function() {
    const year = $('#analytics-year').val();
    const periodType = $('#analytics-period-type').val();
    let periodValue = '';

    if (periodType === 'semi') periodValue = $('#analytics-period-semi').val();
    if (periodType === 'quarter') periodValue = $('#analytics-period-quarter').val();
    if (periodType === 'month') periodValue = $('#analytics-period-month').val();

    const content = $('#analytics-content');
    content.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading analytics...</p></div>');
    
    const params = new URLSearchParams({
        year: year,
        period_type: periodType,
        period_value: periodValue
    });

    fetch('{{ route("performance_management_module.analytics.data") }}?' + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderAnalyticsCharts(data);
            } else {
                content.html('<div class="alert alert-danger">Failed to load analytics</div>');
            }
        })
        .catch(err => {
            content.html('<div class="alert alert-danger">Error: ' + err.message + '</div>');
        });
});

function renderAnalyticsCharts(data) {
    let html = '<div class="row g-4">';
    
    // Top Row: KPIs
    html += `
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Assessments</h6>
                    <h3 class="mb-0">${Object.values(data.status_distribution).reduce((a, b) => a + b, 0)}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Approval Rate</h6>
                    <h3 class="mb-0 text-success">${calculateRate(data.status_distribution.approved, Object.values(data.status_distribution).reduce((a, b) => a + b, 0))}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Active Goals</h6>
                    <h3 class="mb-0 text-info">${Object.keys(data.goal_distribution).length}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Reports Submitted</h6>
                    <h3 class="mb-0 text-warning">${Object.values(data.report_status_distribution).reduce((a, b) => a + b, 0)}</h3>
                </div>
            </div>
        </div>
    `;

    // Second Row: Distributions
    html += `
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Assessment Status</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Organizational Goal Alignment</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="goalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Report Status</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="reportStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Third Row: Trend & Department
    html += `
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Monthly Activity Trend (${data.year})</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Assessments by Department</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Fourth Row: Top Performers Table
    html += `
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Top Performing Employees</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Assessments</th>
                                    <th>Reports</th>
                                    <th>Performance Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.top_performers_list.length > 0 ? data.top_performers_list.map((p, i) => `
                                    <tr>
                                        <td>${i + 1}</td>
                                        <td class="fw-bold">${p.name}</td>
                                        <td>${p.department}</td>
                                        <td>${p.assessments_count}</td>
                                        <td>${p.reports_count}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${p.score}%"></div>
                                                </div>
                                                <span>${p.score}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('') : '<tr><td colspan="6" class="text-center py-3">No performance data available</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;

    html += '</div>';
    $('#analytics-content').html(html);
    
    setTimeout(() => {
        // Status Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending HOD', 'Rejected'],
                datasets: [{
                    data: [data.status_distribution.approved, data.status_distribution.pending_hod, data.status_distribution.rejected],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Goal Chart
        new Chart(document.getElementById('goalChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.goal_distribution),
                datasets: [{
                    data: Object.values(data.goal_distribution),
                    backgroundColor: ['#696cff', '#8592a3', '#71dd37', '#ff3e1d', '#03c3ec'],
                    borderWidth: 0
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Report Status Chart
        new Chart(document.getElementById('reportStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [data.report_status_distribution.approved, data.report_status_distribution.pending_approval, data.report_status_distribution.rejected],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // Trend Chart
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: data.monthly_trend.labels,
                datasets: [{
                    label: 'New Assessments',
                    data: data.monthly_trend.assessments,
                    borderColor: '#696cff',
                    backgroundColor: 'rgba(105, 108, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Progress Reports',
                    data: data.monthly_trend.reports,
                    borderColor: '#71dd37',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { 
                maintainAspectRatio: false, 
                scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } },
                plugins: { legend: { position: 'top', align: 'end' } }
            }
        });
        
        // Department Chart
        new Chart(document.getElementById('deptChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.department_distribution),
                datasets: [{
                    label: 'Assessments',
                    data: Object.values(data.department_distribution),
                    backgroundColor: '#03c3ec',
                    borderRadius: 4
                }]
            },
            options: { 
                maintainAspectRatio: false, 
                indexAxis: 'y',
                scales: { x: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    }, 100);
}

function calculateRate(part, total) {
    if(!total || total === 0) return 0;
    return Math.round((part / total) * 100);
}
</script>
@endpush

