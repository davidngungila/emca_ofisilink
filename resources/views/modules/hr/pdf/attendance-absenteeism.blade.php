<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Absenteeism Report</title>
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
        .text-center { text-align: center; }
        .employee-name { font-weight: bold; color: #500000; }
        .absenteeism-rate { font-weight: bold; }
        .rate-high { color: #dc3545; }
        .rate-medium { color: #ffc107; }
        .rate-low { color: #28a745; }
        .absence-dates { font-size: 8pt; color: #6c757d; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-ABSENT-' . Carbon::parse($dateFrom)->format('Ymd');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ABSENTEEISM REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Absenteeism Report</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">
                Period: {{ Carbon::parse($dateFrom)->format('d M, Y') }} to {{ Carbon::parse($dateTo)->format('d M, Y') }}
                ({{ $totalDays }} days)
            </h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData->count() }}</strong>
                    <span>Employees with Absences</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData->sum('absent_days') }}</strong>
                    <span>Total Absent Days</span>
                </div>
                <div class="stat-card">
                    <strong>{{ number_format($reportData->avg('absenteeism_rate'), 1) }}%</strong>
                    <span>Average Absenteeism Rate</span>
                </div>
            </div>
        </div>

        <h2>Absenteeism Details by Employee</h2>
        @if($reportData->count() > 0)
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 18%;">Employee Name</th>
                    <th style="width: 10%;">Employee ID</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 8%;" class="text-center">Total Days</th>
                    <th style="width: 8%;" class="text-center">Present</th>
                    <th style="width: 8%;" class="text-center">Absent</th>
                    <th style="width: 12%;" class="text-center">Absenteeism Rate</th>
                    <th style="width: 18%;">Absent Dates</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $employee)
                @php
                    $rateClass = match(true) {
                        $employee['absenteeism_rate'] >= 20 => 'rate-high',
                        $employee['absenteeism_rate'] >= 10 => 'rate-medium',
                        default => 'rate-low'
                    };
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $employee['employee_name'] }}</td>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td>{{ $employee['department'] }}</td>
                    <td class="text-center">{{ $employee['total_days'] }}</td>
                    <td class="text-center">{{ $employee['present_days'] }}</td>
                    <td class="text-center"><strong>{{ $employee['absent_days'] }}</strong></td>
                    <td class="text-center absenteeism-rate {{ $rateClass }}">
                        {{ number_format($employee['absenteeism_rate'], 1) }}%
                    </td>
                    <td class="absence-dates">
                        @if(count($employee['absent_dates']) > 0)
                            {{ implode(', ', array_slice($employee['absent_dates'], 0, 5)) }}
                            @if(count($employee['absent_dates']) > 5)
                                <br>... and {{ count($employee['absent_dates']) - 5 }} more
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">No absenteeism recorded for this period.</p>
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

