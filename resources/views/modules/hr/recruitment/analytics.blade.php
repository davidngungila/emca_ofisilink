@extends('layouts.app')

@section('title', 'Recruitment Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.css">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s;
        height: 100%;
        border: 1px solid #e5e7eb;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .chart-box {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        height: 100%;
        min-height: 400px;
    }

    .insight-card {
        background: #f8fafc;
        border-left: 4px solid #6366f1;
        padding: 1rem;
        border-radius: 0 8px 8px 0;
        margin-bottom: 1rem;
    }

    .insight-warning { border-color: #f59e0b; background: #fffbeb; }
    .insight-success { border-color: #10b981; background: #f0fdf4; }
    .insight-info { border-color: #3b82f6; background: #eff6ff; }
</style>
@endpush

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-0 text-primary">
                <i class="bx bx-bar-chart-alt-2 me-2"></i>Recruitment Intelligence
            </h4>
            <p class="text-muted mb-0">Performance metrics and hiring insights.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print
            </button>
            <button class="btn btn-primary" id="refreshBtn">
                <i class="bx bx-refresh me-1"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background: var(--primary-gradient);">
                    <i class="bx bx-briefcase"></i>
                </div>
                <h3 class="mb-1 fw-bold" id="totalJobs">0</h3>
                <div class="text-muted small">Total Job Listings</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background: var(--secondary-gradient);">
                    <i class="bx bx-user-voice"></i>
                </div>
                <h3 class="mb-1 fw-bold" id="totalApps">0</h3>
                <div class="text-muted small">Total Applications</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background: var(--success-gradient);">
                    <i class="bx bx-stopwatch"></i>
                </div>
                <h3 class="mb-1 fw-bold" id="avgTime">0 <small class="fs-6 fw-normal">days</small></h3>
                <div class="text-muted small">Avg. Time to Hire</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background: var(--warning-gradient);">
                    <i class="bx bx-target-lock"></i>
                </div>
                <h3 class="mb-1 fw-bold" id="hireRate">0%</h3>
                <div class="text-muted small">Hiring Success Rate</div>
            </div>
        </div>
    </div>

    <!-- Main Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="chart-box">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Application Trends</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">Last 6 Months</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last Year</a></li>
                        </ul>
                    </div>
                </div>
                <div id="applicationTrendChart" style="min-height: 350px;"></div>
            </div>
        </div>
        <div class="col-lg-4">
             <div class="chart-box">
                <h5 class="mb-4 fw-bold">Pipeline Distribution</h5>
                <div id="pipelineChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Secondary Charts & Insights -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="chart-box">
                 <h5 class="mb-4 fw-bold">Top Performing Jobs</h5>
                 <div id="topJobsChart" style="min-height: 300px;"></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bx bx-bulb me-2 text-warning"></i>AI Insights</h5>
                </div>
                <div class="card-body p-4" id="insightsContainer">
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Analyzing data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const recruitmentUrl = '{{ route("recruitment.handle") }}';

        // Load Data
        loadAnalytics();

        $('#refreshBtn').click(loadAnalytics);

        function loadAnalytics() {
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { _token: csrfToken, action: 'get_analytics' },
                success: function(res) {
                    if(res.success) {
                        const data = res.analytics;
                        updateStats(data);
                        renderPipelineChart(data.applications_by_status);
                        renderTrendChart(data.applications_over_time);
                        renderTopJobs(data.top_jobs);
                        renderInsights(data.insights);
                    }
                }
            });
        }

        function updateStats(data) {
            $('#totalJobs').text(data.total_jobs);
            $('#totalApps').text(data.total_applications);
            $('#avgTime').html(`${data.avg_time_to_hire} <small class="fs-6 fw-normal">days</small>`);
            $('#hireRate').text(data.hire_rate + '%');
        }

        function renderPipelineChart(data) {
            const labels = data.map(i => i.status);
            const series = data.map(i => i.count);

            var options = {
                series: series,
                labels: labels,
                chart: { type: 'donut', height: 350 },
                colors: ['#696cff', '#03c3ec', '#ffab00', '#71dd37', '#ff3e1d', '#8592a3'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                }
            };
            
            const chartEl = document.querySelector("#pipelineChart");
            chartEl.innerHTML = '';
            new ApexCharts(chartEl, options).render();
        }

        function renderTrendChart(data) {
            const categories = data.map(i => i.date);
            const counts = data.map(i => i.count);

            var options = {
                series: [{ name: "Applications", data: counts }],
                chart: { type: 'area', height: 350, toolbar: { show: false } },
                colors: ['#696cff'],
                fill: {
                    type: "gradient",
                    gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.9, stops: [0, 90, 100] }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: categories, type: 'datetime' },
                tooltip: { x: { format: 'dd MMM yyyy' } }
            };

            const chartEl = document.querySelector("#applicationTrendChart");
            chartEl.innerHTML = '';
            new ApexCharts(chartEl, options).render();
        }

        function renderTopJobs(data) {
            const categories = data.map(i => i.job_title);
            const counts = data.map(i => i.applications_count);

            var options = {
                series: [{ name: "Applications", data: counts }],
                chart: { type: 'bar', height: 300, toolbar: { show: false } },
                plotOptions: {
                    bar: { borderRadius: 4, horizontal: true, barHeight: '50%' }
                },
                colors: ['#696cff'],
                xaxis: { categories: categories }
            };

            const chartEl = document.querySelector("#topJobsChart");
            chartEl.innerHTML = '';
            new ApexCharts(chartEl, options).render();
        }

        function renderInsights(insights) {
            const container = $('#insightsContainer');
            container.empty();

            if(!insights || insights.length === 0) {
                container.html('<p class="text-muted text-center">No sufficient data for insights yet.</p>');
                return;
            }

            insights.forEach(i => {
                const typeClass = `insight-${i.type}`; // warning, success, info
                container.append(`
                    <div class="insight-card ${typeClass}">
                        <h6 class="fw-bold mb-1">${i.title}</h6>
                        <p class="mb-0 small text-muted">${i.description}</p>
                    </div>
                `);
            });
        }
    });
</script>
@endpush
