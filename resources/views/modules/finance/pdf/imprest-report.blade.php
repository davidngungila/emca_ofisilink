<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprest Report - {{ $summary['period_label'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            background-color: #940000;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #940000;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .summary-label {
            font-weight: bold;
            color: #666;
        }
        
        .summary-value {
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #940000;
            color: white;
        }
        
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-approved {
            background-color: #28a745;
            color: white;
        }
        
        .status-assigned {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-paid {
            background-color: #007bff;
            color: white;
        }
        
        .status-completed {
            background-color: #6c757d;
            color: white;
        }
        
        .status-pending-hod {
            background-color: #ff9800;
            color: white;
        }
        
        .status-pending-ceo {
            background-color: #2196f3;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #940000;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .summary-by-status {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .summary-by-status h3 {
            background-color: #940000;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table th {
            background-color: #e0e0e0;
            color: #333;
            padding: 8px;
            text-align: left;
            font-size: 9px;
        }
        
        .summary-table td {
            padding: 6px 8px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Imprest Report</h1>
        <p>{{ $summary['period_label'] }}</p>
    </div>
    
    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Period:</span>
            <span class="summary-value">{{ $summary['period_label'] }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Date Range:</span>
            <span class="summary-value">{{ \Carbon\Carbon::parse($summary['date_from'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($summary['date_to'])->format('d M Y') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Requests:</span>
            <span class="summary-value">{{ number_format($summary['total_requests']) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Amount:</span>
            <span class="summary-value">{{ number_format($summary['total_amount'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Generated:</span>
            <span class="summary-value">{{ $generated_at }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Generated By:</span>
            <span class="summary-value">{{ $generated_by }}</span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Request No</th>
                <th>Date</th>
                <th>Purpose</th>
                <th class="text-right">Amount</th>
                <th>Status</th>
                <th>Accountant</th>
                <th>HOD Approved</th>
                <th>CEO Approved</th>
                <th>Assigned Staff</th>
                <th>Paid At</th>
                <th>Completed At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
            <tr>
                <td>{{ $request->request_no ?? 'N/A' }}</td>
                <td>{{ $request->created_at ? $request->created_at->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($request->purpose ?? 'N/A', 30) }}</td>
                <td class="text-right">{{ number_format($request->amount ?? 0, 2) }}</td>
                <td>
                    <span class="status-badge status-{{ str_replace('_', '-', $request->status ?? 'pending') }}">
                        {{ ucfirst(str_replace('_', ' ', $request->status ?? 'N/A')) }}
                    </span>
                </td>
                <td>{{ $request->accountant->name ?? 'N/A' }}</td>
                <td>{{ $request->hodApproval->name ?? 'N/A' }}</td>
                <td>{{ $request->ceoApproval->name ?? 'N/A' }}</td>
                <td>{{ $request->assignments->pluck('staff.name')->filter()->implode(', ') ?: 'N/A' }}</td>
                <td>{{ $request->paid_at ? $request->paid_at->format('Y-m-d H:i') : 'N/A' }}</td>
                <td>{{ $request->completed_at ? $request->completed_at->format('Y-m-d H:i') : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No imprest requests found for this period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="3" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($summary['total_amount'], 2) }}</td>
                <td colspan="7"></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="summary-by-status">
        <h3>Summary by Status</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['by_status'] as $status => $data)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                    <td class="text-right">{{ number_format($data['count']) }}</td>
                    <td class="text-right">{{ number_format($data['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>This report was generated on {{ $generated_at }} by {{ $generated_by }}</p>
        <p>OfisiLink System - Imprest Management</p>
    </div>
</body>
</html>

