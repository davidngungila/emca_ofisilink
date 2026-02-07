<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Attendance Report - {{ $reportDate->format('d M Y') }}</title>
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
        .badge-present { background-color: #28a745; }
        .badge-absent { background-color: #dc3545; }
        .badge-late { background-color: #ffc107; color: #000; }
        .badge-on-leave { background-color: #17a2b8; }
        .text-center { text-align: center; }
        .employee-name { font-weight: bold; color: #500000; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-DAILY-' . $reportDate->format('Ymd');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'DAILY ATTENDANCE REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Daily Attendance Report</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">Report Date: {{ $reportDate->format('d M, Y') }}</h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['total_employees'] }}</strong>
                    <span>Total Employees</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['present'] }}</strong>
                    <span>Present</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['absent'] }}</strong>
                    <span>Absent</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['on_leave'] }}</strong>
                    <span>On Leave</span>
                </div>
            </div>
        </div>

        <!-- Present Employees -->
        <h2>✓ Present Employees</h2>
        @if($reportData['present']->count() > 0)
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Employee Name</th>
                    <th style="width: 12%;">Employee ID</th>
                    <th style="width: 18%;">Department</th>
                    <th style="width: 12%;">Check-In</th>
                    <th style="width: 12%;">Check-Out</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 11%;" class="text-center">Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['present'] as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $record['employee_name'] }}</td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td>{{ $record['check_in'] }}</td>
                    <td>{{ $record['check_out'] }}</td>
                    <td>
                        <span class="status-badge badge-present">Present</span>
                        @if($record['is_late'])
                            <br><small style="color: #ffc107;">Late</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $record['total_hours'] }}h</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">No present employees recorded for this date.</p>
        @endif

        <!-- Absent Employees -->
        @if($reportData['absent']->count() > 0)
        <h2>✗ Absent Employees</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Employee Name</th>
                    <th style="width: 15%;">Employee ID</th>
                    <th style="width: 25%;">Department</th>
                    <th style="width: 25%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['absent'] as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $record['employee_name'] }}</td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td><span class="status-badge badge-absent">Absent</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Employees on Leave -->
        @if($reportData['on_leave']->count() > 0)
        <h2>📅 Employees on Leave</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Employee Name</th>
                    <th style="width: 15%;">Employee ID</th>
                    <th style="width: 20%;">Department</th>
                    <th style="width: 35%;">Leave Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['on_leave'] as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $record['employee_name'] }}</td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td><span class="status-badge badge-on-leave">{{ $record['leave_type'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
                        Date: {{ $reportDate->format('d M Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </main>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

