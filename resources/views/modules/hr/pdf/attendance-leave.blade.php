<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Report</title>
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
        .badge-approved { background-color: #28a745; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-rejected { background-color: #dc3545; }
        .text-center { text-align: center; }
        .employee-name { font-weight: bold; color: #500000; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-LEAVE-' . \Carbon\Carbon::parse($dateFrom)->format('Ym');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'LEAVE REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Leave Report</h1>
        
        <div class="summary-box">
            <h2 style="margin-top: 0; margin-bottom: 15px;">
                Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}
            </h2>
            <div style="text-align: center;">
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['total_requests'] }}</strong>
                    <span>Total Requests</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['total_leave_days'] }}</strong>
                    <span>Total Leave Days</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['approved'] }}</strong>
                    <span>Approved</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $reportData['summary']['pending'] }}</strong>
                    <span>Pending</span>
                </div>
            </div>
        </div>

        <!-- By Leave Type -->
        <h2>Summary by Leave Type</h2>
        @if($reportData['by_leave_type']->count() > 0)
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Leave Type</th>
                    <th style="width: 12%;" class="text-center">Total Requests</th>
                    <th style="width: 12%;" class="text-center">Total Days</th>
                    <th style="width: 12%;" class="text-center">Approved</th>
                    <th style="width: 12%;" class="text-center">Pending</th>
                    <th style="width: 12%;" class="text-center">Rejected</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['by_leave_type'] as $index => $type)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $type['leave_type'] }}</strong></td>
                    <td class="text-center">{{ $type['total_requests'] }}</td>
                    <td class="text-center">{{ $type['total_days'] }}</td>
                    <td class="text-center">{{ $type['approved'] }}</td>
                    <td class="text-center">{{ $type['pending'] }}</td>
                    <td class="text-center">{{ $type['rejected'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- By Employee -->
        <h2>Leave Usage by Employee</h2>
        @if($reportData['by_employee']->count() > 0)
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 18%;">Employee Name</th>
                    <th style="width: 10%;">Employee ID</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 10%;" class="text-center">Total Days</th>
                    <th style="width: 44%;">Leave Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['by_employee'] as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">{{ $employee['employee_name'] }}</td>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td>{{ $employee['department'] }}</td>
                    <td class="text-center"><strong>{{ $employee['total_leave_days'] }}</strong></td>
                    <td>
                        @foreach($employee['leave_requests'] as $leave)
                        <div style="margin-bottom: 5px; font-size: 8pt;">
                            <strong>{{ $leave['leave_type'] }}</strong>: 
                            {{ \Carbon\Carbon::parse($leave['start_date'])->format('d M') }} - 
                            {{ \Carbon\Carbon::parse($leave['end_date'])->format('d M') }} 
                            ({{ $leave['total_days'] }} days)
                            <span class="status-badge {{ $leave['status'] === 'approved' ? 'badge-approved' : ($leave['status'] === 'pending' ? 'badge-pending' : 'badge-rejected') }}">
                                {{ ucfirst($leave['status']) }}
                            </span>
                        </div>
                        @endforeach
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

