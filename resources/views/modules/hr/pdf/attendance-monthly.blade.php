<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance Summary - {{ Carbon::parse($dateFrom)->format('M Y') }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: "Helvetica", Arial, sans-serif; 
            font-size: 9pt; 
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
            padding: 6px; 
            text-align: left; 
            vertical-align: top; 
            font-size: 9pt;
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
            width: 18%; 
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
        .employee-name { font-weight: bold; color: #500000; }
        .attendance-rate { font-weight: bold; }
        .rate-excellent { color: #28a745; }
        .rate-good { color: #17a2b8; }
        .rate-fair { color: #ffc107; }
        .rate-poor { color: #dc3545; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-MONTHLY-' . Carbon::parse($dateFrom)->format('Ym');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'MONTHLY ATTENDANCE SUMMARY',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Monthly Attendance Summary</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">
                Period: {{ Carbon::parse($dateFrom)->format('d M, Y') }} to {{ Carbon::parse($dateTo)->format('d M, Y') }}
            </h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $summary['total_employees'] }}</strong>
                    <span>Employees</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $summary['total_working_days'] }}</strong>
                    <span>Working Days</span>
                </div>
                <div class="stat-card">
                    <strong>{{ number_format($summary['total_present_days']) }}</strong>
                    <span>Present Days</span>
                </div>
                <div class="stat-card">
                    <strong>{{ number_format($summary['total_absent_days']) }}</strong>
                    <span>Absent Days</span>
                </div>
                <div class="stat-card">
                    <strong>{{ number_format($summary['average_attendance_rate'], 1) }}%</strong>
                    <span>Avg. Rate</span>
                </div>
            </div>
        </div>

        <h2>Employee Attendance Details</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 18%;">Employee Name</th>
                    <th style="width: 10%;">Employee ID</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 8%;" class="text-center">Working Days</th>
                    <th style="width: 8%;" class="text-center">Present</th>
                    <th style="width: 8%;" class="text-center">Absent</th>
                    <th style="width: 8%;" class="text-center">Late Days</th>
                    <th style="width: 8%;" class="text-center">Leave Days</th>
                    <th style="width: 14%;" class="text-center">Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $employee)
                @php
                    $rateClass = match(true) {
                        $employee['attendance_rate'] >= 95 => 'rate-excellent',
                        $employee['attendance_rate'] >= 85 => 'rate-good',
                        $employee['attendance_rate'] >= 70 => 'rate-fair',
                        default => 'rate-poor'
                    };
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $employee['employee_name'] }}</td>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td>{{ $employee['department'] }}</td>
                    <td class="text-center">{{ $employee['total_working_days'] }}</td>
                    <td class="text-center">{{ $employee['days_present'] }}</td>
                    <td class="text-center">{{ $employee['days_absent'] }}</td>
                    <td class="text-center">{{ $employee['late_days'] }}</td>
                    <td class="text-center">{{ $employee['leave_days'] }}</td>
                    <td class="text-center attendance-rate {{ $rateClass }}">
                        {{ number_format($employee['attendance_rate'], 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

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
                        Period: {{ Carbon::parse($dateFrom)->format('d M Y') }} - {{ Carbon::parse($dateTo)->format('d M Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </main>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

