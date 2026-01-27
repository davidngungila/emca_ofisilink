@extends('layouts.app')

@section('title', 'Recruitment Analytics - Recruitment')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Professional Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-success" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-bar-chart me-2"></i>Recruitment Analytics & Reports
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive analytics, insights, and reports for recruitment performance tracking
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <button class="btn btn-light btn-lg shadow-sm" id="export-pdf-btn">
                                <i class="bx bx-file me-2"></i>Export PDF
                            </button>
                            <button class="btn btn-light btn-lg shadow-sm" id="export-excel-btn">
                                <i class="bx bx-spreadsheet me-2"></i>Export Excel
                            </button>
                            <a href="{{ route('jobs.list') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-briefcase me-2"></i>Job Vacancies
                            </a>
                            <a href="{{ route('jobs.applications') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-user-check me-2"></i>Applications
                            </a>
                            <a href="{{ route('jobs.interviews') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-calendar me-2"></i>Interviews
                            </a>
                            <a href="{{ route('jobs') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-briefcase fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Jobs</h6>
                            <h3 class="mb-0 fw-bold text-primary" id="totalJobs">0</h3>
                            <small class="text-info">
                                <i class="bx bx-trending-up me-1"></i>All Vacancies
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bx bx-user fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Applications</h6>
                            <h3 class="mb-0 fw-bold text-success" id="totalApplications">0</h3>
                            <small class="text-success">
                                <i class="bx bx-trending-up me-1"></i>All Candidates
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="bx bx-trending-up fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Hire Rate</h6>
                            <h3 class="mb-0 fw-bold text-warning" id="hireRate">0%</h3>
                            <small class="text-warning">
                                <i class="bx bx-percent me-1"></i>Success Rate
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class="bx bx-time fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Avg. Time to Hire</h6>
                            <h3 class="mb-0 fw-bold text-primary" id="avgTimeToHire">0 days</h3>
                            <small class="text-primary">
                                <i class="bx bx-calendar me-1"></i>Average
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-pie-chart me-2"></i> Applications by Status</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; margin-top: 20px;">
                        <canvas id="applicationsStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i> Applications Over Time</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; margin-top: 20px;">
                        <canvas id="applicationsOverTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-bar-chart-alt me-2"></i> Jobs by Status</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; margin-top: 20px;">
                        <canvas id="jobsStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i> Interview Completion Rate</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; margin-top: 20px;">
                        <canvas id="interviewRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-bulb me-2"></i> Key Insights</h5>
                </div>
                <div class="card-body">
                    <div id="insightsContainer">
                        <!-- Insights will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i> Top Performing Jobs</h5>
                </div>
                <div class="card-body">
                    <div id="topJobsContainer">
                        <!-- Top jobs will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<style>
    .metric-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #007bff;
    }
</style>
@endpush

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
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        // Applications by Status Chart
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
