<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Timing Report</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #333;
        }
        h1 { 
            font-size: 18px; 
            margin-bottom: 5px; 
            color: #0d6efd;
        }
        h2 { 
            font-size: 14px; 
            margin: 15px 0 8px; 
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 12px;
            margin: 10px 0 5px;
            color: #6c757d;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0d6efd;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item strong {
            display: block;
            font-size: 16px;
            color: #0d6efd;
        }
        .summary-item span {
            font-size: 10px;
            color: #6c757d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        table thead {
            background: #0d6efd;
            color: #fff;
        }
        table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0a58ca;
        }
        table td {
            padding: 6px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table tbody tr:hover {
            background: #e9ecef;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: #fff;
        }
        .badge-warning {
            background: #ffc107;
            color: #000;
        }
        .badge-danger {
            background: #dc3545;
            color: #fff;
        }
        .badge-info {
            background: #17a2b8;
            color: #fff;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .muted {
            color: #6c757d;
            font-size: 10px;
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
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            padding: 10px;
            border-top: 1px solid #dee2e6;
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

    <!-- Header -->
    <div class="header">
        <h1>ATTENDANCE TIMING REPORT</h1>
        <p class="muted">Report Period: {{ \Carbon\Carbon::parse($reportData['date_from'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($reportData['date_to'])->format('d M Y') }}</p>
        <p class="muted">Generated on: {{ $documentDate }} | Ref: {{ $documentRef }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-item">
            <strong>{{ $reportData['total_early'] }}</strong>
            <span>Early Arrivals</span>
        </div>
        <div class="summary-item">
            <strong>{{ $reportData['total_late'] }}</strong>
            <span>Late Arrivals</span>
        </div>
        <div class="summary-item">
            <strong>{{ $reportData['total_early_leaves'] }}</strong>
            <span>Early Leaves</span>
        </div>
    </div>

    <!-- Early Arrivals Section -->
    <div class="section">
        <h2><span style="color: #28a745;">✓</span> Staff Coming Early</h2>
        @if(count($reportData['early_arrivals']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Expected Time</th>
                    <th>Actual Time</th>
                    <th>Minutes Early</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['early_arrivals'] as $record)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                    <td><strong>{{ $record['employee_name'] }}</strong></td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td>{{ $record['expected_time_in'] }}</td>
                    <td><span class="badge badge-success">{{ $record['actual_time_in'] }}</span></td>
                    <td class="text-center"><strong>{{ $record['minutes_early'] }}</strong> min</td>
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
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Expected Time</th>
                    <th>Actual Time</th>
                    <th>Minutes Late</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['late_arrivals'] as $record)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                    <td><strong>{{ $record['employee_name'] }}</strong></td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td>{{ $record['expected_time_in'] }}</td>
                    <td><span class="badge badge-danger">{{ $record['actual_time_in'] }}</span></td>
                    <td class="text-center"><strong>{{ $record['minutes_late'] ?? 0 }}</strong> min</td>
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
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Expected Time Out</th>
                    <th>Actual Time Out</th>
                    <th>Minutes Early</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['early_leaves'] as $record)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                    <td><strong>{{ $record['employee_name'] }}</strong></td>
                    <td>{{ $record['employee_id'] }}</td>
                    <td>{{ $record['department'] }}</td>
                    <td>{{ $record['expected_time_out'] }}</td>
                    <td><span class="badge badge-warning">{{ $record['actual_time_out'] }}</span></td>
                    <td class="text-center"><strong>{{ $record['minutes_early'] ?? 0 }}</strong> min</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">No early leaves recorded for this period.</div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated automatically by OfisiLink HR System | Page <span id="pageNum"></span></p>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 30;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>

