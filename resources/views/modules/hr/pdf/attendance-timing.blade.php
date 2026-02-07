<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Timing Report</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: "Helvetica", Arial, sans-serif; 
            font-size: 10pt; 
            color: #333; 
            line-height: 1.4; 
        }
        h1, h2, h3, h4 { 
            color: #500000; 
            margin: 0 0 10px 0; 
            font-weight: bold; 
        }
        h1 { 
            font-size: 22pt; 
            border-bottom: 2px solid #940000; 
            padding-bottom: 10px; 
            margin-bottom: 25px; 
            text-align: center; 
        }
        h2 { 
            font-size: 16pt; 
            background-color: #fceeee; 
            padding: 12px; 
            margin-top: 25px; 
            border-left: 4px solid #940000; 
        }
        h3 { 
            font-size: 13pt; 
            border-bottom: 1px solid #f0d0d0; 
            padding-bottom: 5px; 
            margin-top: 20px; 
        }
        h4 { 
            font-size: 11pt; 
            color: #6c757d; 
            margin-top: 15px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            vertical-align: top; 
        }
        .bordered-table th, .bordered-table td { 
            border: 1px solid #ddd; 
        }
        .bordered-table th { 
            background-color: #f9f9f9; 
            font-weight: bold; 
            color: #500000;
        }
        .summary-box { 
            background-color: #fff9f9; 
            border: 1px solid #f0d0d0; 
            padding: 20px; 
            margin-bottom: 25px; 
            border-radius: 5px; 
        }
        .summary-table { 
            width: 100%; 
            border: none; 
        }
        .summary-table td { 
            padding: 10px; 
            border: 1px solid #f0d0d0; 
        }
        .summary-table td.label { 
            font-weight: bold; 
            color: #500000; 
            width: 20%; 
            background-color: #fceeee;
        }
        .stat-card { 
            display: inline-block; 
            width: 30%; 
            text-align: center; 
            padding: 15px; 
            margin: 5px; 
            background-color: #f8f9fa; 
            border: 2px solid #940000; 
            border-radius: 5px; 
            vertical-align: top;
        }
        .stat-card strong { 
            display: block; 
            font-size: 24pt; 
            color: #940000; 
            margin-bottom: 5px; 
        }
        .stat-card span { 
            font-size: 10pt; 
            color: #6c757d; 
        }
        .status-badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 10px; 
            color: white; 
            font-weight: bold; 
            font-size: 9pt; 
        }
        .badge-success { 
            background-color: #28a745; 
        }
        .badge-warning { 
            background-color: #ffc107; 
            color: #000; 
        }
        .badge-danger { 
            background-color: #dc3545; 
        }
        .badge-info { 
            background-color: #17a2b8; 
        }
        .text-center { 
            text-align: center; 
        }
        .text-right { 
            text-align: right; 
        }
        .section { 
            margin-bottom: 25px; 
            page-break-inside: avoid; 
        }
        .no-data { 
            text-align: center; 
            padding: 20px; 
            color: #6c757d; 
            font-style: italic; 
            background-color: #f8f9fa; 
            border: 1px dashed #dee2e6; 
            border-radius: 5px; 
        }
        .report-period { 
            background-color: #f8f9fa; 
            padding: 15px; 
            border-left: 4px solid #940000; 
            margin-bottom: 20px; 
        }
        .report-period strong { 
            color: #500000; 
        }
        .employee-name { 
            font-weight: bold; 
            color: #500000; 
        }
        .time-badge { 
            display: inline-block; 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 9pt; 
        }
        .minutes-indicator { 
            font-weight: bold; 
            color: #500000; 
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-TIMING-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ATTENDANCE TIMING REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Attendance Timing Report</h1>
        
        <!-- Report Period -->
        <div class="report-period">
            <strong>Report Period:</strong> 
            {{ \Carbon\Carbon::parse($reportData['date_from'])->format('d M, Y') }} to 
            {{ \Carbon\Carbon::parse($reportData['date_to'])->format('d M, Y') }}
        </div>

        <!-- Summary Statistics -->
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">Summary Statistics</h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData['total_early'] }}</strong>
                    <span>Early Arrivals</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['total_late'] }}</strong>
                    <span>Late Arrivals</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['total_early_leaves'] }}</strong>
                    <span>Early Leaves</span>
                </div>
            </div>
        </div>

        <!-- Early Arrivals Section -->
        <div class="section">
            <h2><span style="color: #28a745;">✓</span> Staff Coming Early</h2>
            @if(count($reportData['early_arrivals']) > 0)
            <table class="bordered-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 20%;">Employee Name</th>
                        <th style="width: 12%;">Employee ID</th>
                        <th style="width: 18%;">Department</th>
                        <th style="width: 12%;">Expected Time</th>
                        <th style="width: 12%;">Actual Time</th>
                        <th style="width: 14%;" class="text-center">Minutes Early</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['early_arrivals'] as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                        <td class="employee-name">{{ $record['employee_name'] }}</td>
                        <td>{{ $record['employee_id'] }}</td>
                        <td>{{ $record['department'] }}</td>
                        <td>{{ $record['expected_time_in'] }}</td>
                        <td>
                            <span class="time-badge" style="background-color: #d4edda; color: #155724;">
                                {{ $record['actual_time_in'] }}
                            </span>
                        </td>
                        <td class="text-center minutes-indicator">
                            <span class="status-badge badge-success">{{ $record['minutes_early'] }} min</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data">No early arrivals recorded for this period.</div>
            @endif
        </div>

        <!-- Late Arrivals Section -->
        <div class="section">
            <h2><span style="color: #dc3545;">✗</span> Staff Coming Late</h2>
            @if(count($reportData['late_arrivals']) > 0)
            <table class="bordered-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 20%;">Employee Name</th>
                        <th style="width: 12%;">Employee ID</th>
                        <th style="width: 18%;">Department</th>
                        <th style="width: 12%;">Expected Time</th>
                        <th style="width: 12%;">Actual Time</th>
                        <th style="width: 14%;" class="text-center">Minutes Late</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['late_arrivals'] as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                        <td class="employee-name">{{ $record['employee_name'] }}</td>
                        <td>{{ $record['employee_id'] }}</td>
                        <td>{{ $record['department'] }}</td>
                        <td>{{ $record['expected_time_in'] }}</td>
                        <td>
                            <span class="time-badge" style="background-color: #f8d7da; color: #721c24;">
                                {{ $record['actual_time_in'] }}
                            </span>
                        </td>
                        <td class="text-center minutes-indicator">
                            <span class="status-badge badge-danger">{{ $record['minutes_late'] ?? 0 }} min</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data">No late arrivals recorded for this period.</div>
            @endif
        </div>

        <!-- Early Leaves Section -->
        <div class="section">
            <h2><span style="color: #ffc107;">⚠</span> Staff Leaving Early (Mapema)</h2>
            @if(count($reportData['early_leaves']) > 0)
            <table class="bordered-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 20%;">Employee Name</th>
                        <th style="width: 12%;">Employee ID</th>
                        <th style="width: 18%;">Department</th>
                        <th style="width: 12%;">Expected Time Out</th>
                        <th style="width: 12%;">Actual Time Out</th>
                        <th style="width: 14%;" class="text-center">Minutes Early</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['early_leaves'] as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                        <td class="employee-name">{{ $record['employee_name'] }}</td>
                        <td>{{ $record['employee_id'] }}</td>
                        <td>{{ $record['department'] }}</td>
                        <td>{{ $record['expected_time_out'] }}</td>
                        <td>
                            <span class="time-badge" style="background-color: #fff3cd; color: #856404;">
                                {{ $record['actual_time_out'] }}
                            </span>
                        </td>
                        <td class="text-center minutes-indicator">
                            <span class="status-badge badge-warning">{{ $record['minutes_early'] ?? 0 }} min</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data">No early leaves recorded for this period.</div>
            @endif
        </div>

        <!-- Report Footer Information -->
        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #f0d0d0;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%; text-align: left;">
                        <strong style="color: #500000;">Report Generated:</strong><br>
                        {{ now()->format('d M, Y, h:i A') }}<br>
                        OfisiLink HR System
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <strong style="color: #500000;">Report Reference:</strong><br>
                        {{ $documentRef }}<br>
                        Period: {{ \Carbon\Carbon::parse($reportData['date_from'])->format('d M Y') }} - 
                        {{ \Carbon\Carbon::parse($reportData['date_to'])->format('d M Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </main>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
