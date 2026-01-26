@extends('layouts.app')

@section('title', 'Recruitment Analytics - Recruitment')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-bar-chart"></i> Recruitment Analytics & Reports
                </h4>
                <p class="text-muted">Comprehensive analytics, insights, and reports for recruitment performance tracking</p>
            </div>
            <div class="btn-group" role="group">
                <button class="btn btn-outline-primary" id="export-pdf-btn">
                    <i class="bx bx-file"></i> Export PDF
                </button>
                <button class="btn btn-outline-success" id="export-excel-btn">
                    <i class="bx bx-spreadsheet"></i> Export Excel
                </button>
                <button class="btn btn-outline-secondary" id="refresh-btn">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
                <a href="{{ route('modules.hr.recruitment') }}" class="btn btn-outline-dark">
                    <i class="bx bx-arrow-back"></i> Back to Recruitment
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border-left: 4px solid;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
    .stat-card.primary { border-left-color: #007bff; }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.info { border-left-color: #17a2b8; }
    .stat-card.danger { border-left-color: #dc3545; }
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .chart-wrapper {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }
    .insight-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
    }
    .metric-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #007bff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card primary">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Jobs</h6>
                        <h3 class="mb-0 text-primary" id="totalJobs">0</h3>
                        <small class="text-info">
                            <i class="bx bx-briefcase"></i> All vacancies
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-briefcase"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card success">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Applications</h6>
                        <h3 class="mb-0 text-success" id="totalApplications">0</h3>
                        <small class="text-success">
                            <i class="bx bx-user"></i> All candidates
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-user"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card warning">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Hire Rate</h6>
                        <h3 class="mb-0 text-warning" id="hireRate">0%</h3>
                        <small class="text-warning">
                            <i class="bx bx-trending-up"></i> Success rate
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-trending-up"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card info">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Avg. Time to Hire</h6>
                        <h3 class="mb-0 text-info" id="avgTimeToHire">0 days</h3>
                        <small class="text-info">
                            <i class="bx bx-time"></i> Average
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-info rounded">
                            <i class="bx bx-time"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bx bx-pie-chart"></i> Applications by Status</h5>
                <div class="chart-wrapper">
                    <canvas id="applicationsStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bx bx-bar-chart"></i> Applications Over Time</h5>
                <div class="chart-wrapper">
                    <canvas id="applicationsOverTimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bx bx-bar-chart-alt"></i> Jobs by Status</h5>
                <div class="chart-wrapper">
                    <canvas id="jobsStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bx bx-line-chart"></i> Interview Completion Rate</h5>
                <div class="chart-wrapper">
                    <canvas id="interviewRateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bx bx-bulb"></i> Key Insights</h5>
                <div id="insightsContainer">
                    <!-- Insights will be loaded here -->
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bx bx-list-ul"></i> Top Performing Jobs</h5>
                <div id="topJobsContainer">
                    <!-- Top jobs will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const recruitmentUrl = '{{ route("recruitment.handle") }}';
    
    let applicationsStatusChart = null;
    let applicationsOverTimeChart = null;
    let jobsStatusChart = null;
    let interviewRateChart = null;
    
    // Initialize
    loadAnalytics();
    
    // Refresh Button
    $('#refresh-btn').on('click', function() {
        loadAnalytics();
    });
    
    // Export Buttons
    $('#export-pdf-btn').on('click', function() {
        window.location.href = recruitmentUrl + '?action=export_jobs_pdf&export_type=analytics';
    });
    
    $('#export-excel-btn').on('click', function() {
        window.location.href = recruitmentUrl + '?action=export_applications_excel&export_type=analytics';
    });
    
    // Load Analytics
    function loadAnalytics() {
        $.ajax({
            url: recruitmentUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                action: 'get_analytics'
            },
            success: function(response) {
                if (response.success) {
                    const analytics = response.analytics || {};
                    updateMetrics(analytics);
                    renderCharts(analytics);
                    renderInsights(analytics);
                    renderTopJobs(analytics);
                }
            }
        });
    }
    
    // Update Metrics
    function updateMetrics(analytics) {
        $('#totalJobs').text(analytics.total_jobs || 0);
        $('#totalApplications').text(analytics.total_applications || 0);
        
        const hireRate = analytics.hire_rate || 0;
        $('#hireRate').text(hireRate.toFixed(1) + '%');
        
        const avgTime = analytics.avg_time_to_hire || 0;
        $('#avgTimeToHire').text(avgTime + ' days');
    }
    
    // Render Charts
    function renderCharts(analytics) {
        // Applications by Status Chart
        if (typeof Chart !== 'undefined') {
            const ctx1 = document.getElementById('applicationsStatusChart');
            if (ctx1) {
                if (applicationsStatusChart) applicationsStatusChart.destroy();
                applicationsStatusChart = new Chart(ctx1.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: analytics.applications_by_status?.map(s => s.status) || [],
                        datasets: [{
                            data: analytics.applications_by_status?.map(s => s.count) || [],
                            backgroundColor: ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc949']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
            
            // Applications Over Time Chart
            const ctx2 = document.getElementById('applicationsOverTimeChart');
            if (ctx2) {
                if (applicationsOverTimeChart) applicationsOverTimeChart.destroy();
                applicationsOverTimeChart = new Chart(ctx2.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: analytics.applications_over_time?.map(t => t.date) || [],
                        datasets: [{
                            label: 'Applications',
                            data: analytics.applications_over_time?.map(t => t.count) || [],
                            borderColor: '#4e79a7',
                            backgroundColor: 'rgba(78,121,167,0.2)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
            
            // Jobs by Status Chart
            const ctx3 = document.getElementById('jobsStatusChart');
            if (ctx3) {
                if (jobsStatusChart) jobsStatusChart.destroy();
                jobsStatusChart = new Chart(ctx3.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: analytics.jobs_by_status?.map(j => j.status) || [],
                        datasets: [{
                            label: 'Jobs',
                            data: analytics.jobs_by_status?.map(j => j.count) || [],
                            backgroundColor: '#59a14f'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
            
            // Interview Rate Chart
            const ctx4 = document.getElementById('interviewRateChart');
            if (ctx4) {
                if (interviewRateChart) interviewRateChart.destroy();
                interviewRateChart = new Chart(ctx4.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: analytics.interview_stats?.map(i => i.month) || [],
                        datasets: [{
                            label: 'Completed',
                            data: analytics.interview_stats?.map(i => i.completed) || [],
                            backgroundColor: '#28a745'
                        }, {
                            label: 'Scheduled',
                            data: analytics.interview_stats?.map(i => i.scheduled) || [],
                            backgroundColor: '#17a2b8'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        }
    }
    
    // Render Insights
    function renderInsights(analytics) {
        const container = $('#insightsContainer');
        container.empty();
        
        const insights = analytics.insights || [];
        if (insights.length === 0) {
            container.html('<p class="text-muted">No insights available</p>');
            return;
        }
        
        insights.forEach(insight => {
            container.append(`
                <div class="metric-item">
                    <h6>${escapeHtml(insight.title)}</h6>
                    <p class="mb-0">${escapeHtml(insight.description)}</p>
                </div>
            `);
        });
    }
    
    // Render Top Jobs
    function renderTopJobs(analytics) {
        const container = $('#topJobsContainer');
        container.empty();
        
        const topJobs = analytics.top_jobs || [];
        if (topJobs.length === 0) {
            container.html('<p class="text-muted">No data available</p>');
            return;
        }
        
        topJobs.forEach((job, index) => {
            container.append(`
                <div class="metric-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${index + 1}. ${escapeHtml(job.job_title)}</h6>
                            <small class="text-muted">${job.applications_count || 0} applications</small>
                        </div>
                        <span class="badge bg-primary">${job.applications_count || 0}</span>
                    </div>
                </div>
            `);
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
});
</script>
@endpush

