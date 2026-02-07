<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Department Attendance Report</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: "Helvetica", Arial, sans-serif; 
            font-size: 10pt; 
            color: #333; 
            line-height: 1.4; 
        }
        h1, h2, h3 { 
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
        .stat-card { 
            display: inline-block; 
            width: 22%; 
            text-align: center; 
            padding: 12px; 
            margin: 3px; 
            background-color: #f8f9fa; 
            border: 2px solid #940000; 
            border-radius: 5px; 
            vertical-align: top;
        }
        .stat-card strong { 
            display: block; 
            font-size: 20pt; 
            color: #940000; 
            margin-bottom: 5px; 
        }
        .stat-card span { 
            font-size: 9pt; 
            color: #6c757d; 
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .department-name { font-weight: bold; color: #500000; }
        .attendance-rate { font-weight: bold; }
        .rate-excellent { color: #28a745; }
        .rate-good { color: #17a2b8; }
        .rate-fair { color: #ffc107; }
        .rate-poor { color: #dc3545; }
        .progress-bar { 
            width: 100%; 
            background-color: #e9ecef; 
            border-radius: 5px; 
            height: 20px; 
            position: relative;
        }
        .progress-fill { 
            height: 100%; 
            background-color: #940000; 
            border-radius: 5px; 
            text-align: center; 
            color: white; 
            line-height: 20px; 
            font-weight: bold; 
            font-size: 9pt; 
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-DEPT-' . \Carbon\Carbon::parse($dateFrom)->format('Ym');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'DEPARTMENT ATTENDANCE REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Department Attendance Report</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">
                Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}
            </h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['total_departments'] }}</strong>
                    <span>Departments</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['total_staff'] }}</strong>
                    <span>Total Staff</span>
                </div>
                <div class="stat-card">
                    <strong>{{ number_format($reportData['summary']['average_attendance_rate'], 1) }}%</strong>
                    <span>Avg. Attendance</span>
                </div>
                <div class="stat-card">
                    <strong>{{ number_format($reportData['summary']['average_absentee_rate'], 1) }}%</strong>
                    <span>Avg. Absentee Rate</span>
                </div>
            </div>
        </div>

        <h2>Department Performance Comparison</h2>
        @if($reportData['departments']->count() > 0)
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Department</th>
                    <th style="width: 8%;">Code</th>
                    <th style="width: 8%;" class="text-center">Total Staff</th>
                    <th style="width: 8%;" class="text-center">Working Days</th>
                    <th style="width: 10%;" class="text-center">Present Days</th>
                    <th style="width: 10%;" class="text-center">Absent Days</th>
                    <th style="width: 12%;" class="text-center">Attendance %</th>
                    <th style="width: 12%;" class="text-center">Absentee Rate</th>
                    <th style="width: 5%;" class="text-center">Late</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['departments'] as $index => $dept)
                @php
                    $rateClass = match(true) {
                        $dept['attendance_percentage'] >= 95 => 'rate-excellent',
                        $dept['attendance_percentage'] >= 85 => 'rate-good',
                        $dept['attendance_percentage'] >= 70 => 'rate-fair',
                        default => 'rate-poor'
                    };
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="department-name">{{ $dept['department_name'] }}</td>
                    <td>{{ $dept['department_code'] }}</td>
                    <td class="text-center">{{ $dept['total_staff'] }}</td>
                    <td class="text-center">{{ $dept['total_working_days'] }}</td>
                    <td class="text-center">{{ number_format($dept['present_days']) }}</td>
                    <td class="text-center">{{ number_format($dept['absent_days']) }}</td>
                    <td class="text-center">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $dept['attendance_percentage'] }}%;">
                                {{ number_format($dept['attendance_percentage'], 1) }}%
                            </div>
                        </div>
                    </td>
                    <td class="text-center attendance-rate {{ $rateClass }}">
                        {{ number_format($dept['absentee_rate'], 1) }}%
                    </td>
                    <td class="text-center">{{ $dept['late_count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">No department data available for this period.</p>
        @endif

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
                        Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </main>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

