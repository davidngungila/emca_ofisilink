<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Exception Report</title>
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
        .status-badge { 
            display: inline-block; 
            padding: 3px 8px; 
            border-radius: 10px; 
            color: white; 
            font-weight: bold; 
            font-size: 8pt; 
        }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .text-center { text-align: center; }
        .employee-name { font-weight: bold; color: #500000; }
        .exception-dates { font-size: 8pt; color: #6c757d; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-EXCEPTION-' . Carbon::parse($dateFrom)->format('Ymd');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ATTENDANCE EXCEPTION REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Attendance Exception Report</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">
                Period: {{ Carbon::parse($dateFrom)->format('d M, Y') }} to {{ Carbon::parse($dateTo)->format('d M, Y') }}
            </h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['missing_check_ins'] }}</strong>
                    <span>Missing Check-Ins</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['missing_check_outs'] }}</strong>
                    <span>Missing Check-Outs</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['duplicate_entries'] }}</strong>
                    <span>Duplicate Entries</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['unauthorized_absences'] }}</strong>
                    <span>Unauthorized Absences</span>
                </div>
            </div>
        </div>

        <!-- Missing Check-Ins -->
        @if(count($reportData['exceptions']['missing_check_ins']) > 0)
        <h2>⚠ Missing Check-Ins</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 20%;">Employee Name</th>
                    <th style="width: 12%;">Employee ID</th>
                    <th style="width: 18%;">Department</th>
                    <th style="width: 35%;">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['exceptions']['missing_check_ins'] as $index => $exception)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($exception['date'])->format('d M Y') }}</td>
                    <td class="employee-name">{{ $exception['employee_name'] }}</td>
                    <td>{{ $exception['employee_id'] }}</td>
                    <td>{{ $exception['department'] }}</td>
                    <td>
                        <span class="status-badge badge-warning">Missing Check-In</span>
                        @if($exception['has_check_out'])
                            <br><small>Has check-out but no check-in</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Missing Check-Outs -->
        @if(count($reportData['exceptions']['missing_check_outs']) > 0)
        <h2>⚠ Missing Check-Outs</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 20%;">Employee Name</th>
                    <th style="width: 12%;">Employee ID</th>
                    <th style="width: 18%;">Department</th>
                    <th style="width: 12%;">Check-In Time</th>
                    <th style="width: 23%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['exceptions']['missing_check_outs'] as $index => $exception)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($exception['date'])->format('d M Y') }}</td>
                    <td class="employee-name">{{ $exception['employee_name'] }}</td>
                    <td>{{ $exception['employee_id'] }}</td>
                    <td>{{ $exception['department'] }}</td>
                    <td>{{ $exception['check_in_time'] }}</td>
                    <td>
                        <span class="status-badge badge-warning">Missing Check-Out</span>
                        <br><small>Checked in but no check-out recorded</small>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Duplicate Entries -->
        @if(count($reportData['exceptions']['duplicate_entries']) > 0)
        <h2>⚠ Duplicate Entries</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 18%;">Employee Name</th>
                    <th style="width: 10%;">Employee ID</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 10%;" class="text-center">Count</th>
                    <th style="width: 32%;">Records</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['exceptions']['duplicate_entries'] as $index => $duplicate)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($duplicate['date'])->format('d M Y') }}</td>
                    <td class="employee-name">{{ $duplicate['employee_name'] }}</td>
                    <td>{{ $duplicate['employee_id'] }}</td>
                    <td>{{ $duplicate['department'] }}</td>
                    <td class="text-center">
                        <span class="status-badge badge-danger">{{ $duplicate['duplicate_count'] }}</span>
                    </td>
                    <td class="exception-dates">
                        @foreach($duplicate['records'] as $record)
                        <div style="margin-bottom: 3px;">
                            In: {{ $record['time_in'] }} | Out: {{ $record['time_out'] }}
                            @if($record['device'] !== 'N/A')
                                <br><small>Device: {{ $record['device'] }}</small>
                            @endif
                        </div>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Unauthorized Absences -->
        @if($reportData['exceptions']['unauthorized_absences']->count() > 0)
        <h2>⚠ Unauthorized Absences</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 18%;">Employee Name</th>
                    <th style="width: 10%;">Employee ID</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 8%;" class="text-center">Absence Count</th>
                    <th style="width: 44%;">Absence Dates</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['exceptions']['unauthorized_absences'] as $index => $absence)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $absence['employee_name'] }}</td>
                    <td>{{ $absence['employee_id'] }}</td>
                    <td>{{ $absence['department'] }}</td>
                    <td class="text-center">
                        <span class="status-badge badge-danger">{{ $absence['absence_count'] }}</span>
                    </td>
                    <td class="exception-dates">
                        {{ implode(', ', array_slice($absence['absence_dates']->toArray(), 0, 10)) }}
                        @if($absence['absence_dates']->count() > 10)
                            <br>... and {{ $absence['absence_dates']->count() - 10 }} more dates
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(count($reportData['exceptions']['missing_check_ins']) == 0 && 
            count($reportData['exceptions']['missing_check_outs']) == 0 && 
            count($reportData['exceptions']['duplicate_entries']) == 0 && 
            $reportData['exceptions']['unauthorized_absences']->count() == 0)
        <p style="text-align: center; color: #28a745; font-weight: bold; padding: 20px; background-color: #d4edda; border-radius: 5px;">
            ✓ No exceptions found for this period. All attendance records are clean.
        </p>
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

