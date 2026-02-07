<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Late Coming Report</title>
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
        .badge-danger { background-color: #dc3545; }
        .text-center { text-align: center; }
        .employee-name { font-weight: bold; color: #500000; }
        .minutes-late { font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-LATE-' . Carbon::parse($dateFrom)->format('Ymd');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'LATE COMING REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Late Coming Report</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">
                Period: {{ Carbon::parse($dateFrom)->format('d M, Y') }} to {{ Carbon::parse($dateTo)->format('d M, Y') }}
            </h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData['total_late_occurrences'] }}</strong>
                    <span>Total Late Occurrences</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['unique_employees'] }}</strong>
                    <span>Employees with Late</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['by_employee']->sum('total_minutes_late') }}</strong>
                    <span>Total Minutes Late</span>
                </div>
            </div>
        </div>

        <!-- Summary by Employee -->
        <h2>Summary by Employee</h2>
        @if($reportData['by_employee']->count() > 0)
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Employee Name</th>
                    <th style="width: 12%;">Employee ID</th>
                    <th style="width: 18%;">Department</th>
                    <th style="width: 12%;" class="text-center">Late Count</th>
                    <th style="width: 16%;" class="text-center">Total Minutes</th>
                    <th style="width: 17%;" class="text-center">Average Minutes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['by_employee'] as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $employee['employee_name'] }}</td>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td>{{ $employee['department'] }}</td>
                    <td class="text-center"><strong>{{ $employee['total_late_count'] }}</strong></td>
                    <td class="text-center minutes-late">{{ $employee['total_minutes_late'] }} min</td>
                    <td class="text-center">{{ $employee['average_minutes_late'] }} min</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">No late arrivals recorded for this period.</p>
        @endif

        <!-- Detailed Records -->
        @if($reportData['all_records']->count() > 0)
        <h2>Detailed Late Arrival Records</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Date</th>
                    <th style="width: 20%;">Employee Name</th>
                    <th style="width: 12%;">Employee ID</th>
                    <th style="width: 18%;">Department</th>
                    <th style="width: 12%;">Expected Time</th>
                    <th style="width: 12%;">Actual Time</th>
                    <th style="width: 18%;" class="text-center">Minutes Late</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['all_records'] as $record)
                <tr>
                    <td>{{ Carbon::parse($record['date'])->format('d M Y') }}</td>
                    <td class="employee-name">{{ $record['employee_name'] }}</td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td>{{ $record['expected_time'] }}</td>
                    <td>{{ $record['actual_time'] }}</td>
                    <td class="text-center minutes-late">
                        <span class="status-badge badge-danger">{{ $record['minutes_late'] }} min</span>
                    </td>
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

