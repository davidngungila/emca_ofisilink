<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Journal - {{ $payroll->pay_period ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page { 
            margin: 40px 50px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #940000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 22px;
            color: #940000;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            color: #940000;
            font-weight: bold;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .info-box {
            width: 48%;
        }
        .journal-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .journal-table th {
            background: #940000;
            color: #fff;
            padding: 10px;
            text-align: left;
            text-transform: uppercase;
            font-size: 12px;
            border: 1px solid #610000;
        }
        .journal-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
        }
        .journal-table tr:nth-child(even) {
            background: #f1f5f9;
        }
        .amount-col {
            text-align: right;
            width: 120px;
        }
        .day-col {
            text-align: center;
            width: 60px;
        }
        .total-row {
            background: #e2e8f0 !important;
            font-weight: bold;
        }
        .label-debit {
            color: #940000;
            font-weight: bold;
            padding-top: 15px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .label-credit {
            color: #dc2626;
            font-weight: bold;
            padding-top: 15px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .indent {
            padding-left: 30px !important;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33.33%;
            padding: 20px 10px;
            vertical-align: top;
            border: none;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            margin-bottom: 5px;
            width: 90%;
        }
        .signature-label {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .signature-date {
            font-size: 9px;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Salary Journal</h1>
        <p>Period: {{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</p>
    </div>

    <div class="info-section">
        <div class="info-box">
            <strong>Reference:</strong> SAL-JRNL-{{ $payroll->id }}-{{ date('Ymd') }}<br>
            <strong>Date:</strong> {{ date('d M Y') }}<br>
            <strong>Branch:</strong> {{ $payroll->branch->name ?? 'Head Office' }}
        </div>
        <div class="info-box" style="text-align: right;">
            <strong>Status:</strong> <span style="color: {{ $payroll->status === 'paid' ? '#059669' : '#f59e0b' }}">{{ strtoupper($payroll->status) }}</span><br>
            <strong>Currency:</strong> TZS
        </div>
    </div>

    <table class="journal-table">
        <thead>
            <tr>
                <th class="day-col">Day</th>
                <th>Details</th>
                <th class="amount-col">Debit</th>
                <th class="amount-col">Credit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $day = \Carbon\Carbon::parse($payroll->pay_date ?? now())->day;
                $payrollTotals = $totals ?? [];
            @endphp
            
            <!-- DEBIT SECTION -->
            <tr>
                <td colspan="4" class="label-debit">Debit (Expenses)</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Basic Salary</td>
                <td class="amount-col">{{ number_format($payrollTotals['basic_salary'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Allowances</td>
                <td class="amount-col">{{ number_format($payrollTotals['allowance_amount'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">House Benefit</td>
                <td class="amount-col">{{ number_format($payrollTotals['house_benefit_amount'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Hardship Benefit</td>
                <td class="amount-col">{{ number_format($payrollTotals['hardship_benefit_amount'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Other Benefits</td>
                <td class="amount-col">{{ number_format($payrollTotals['other_benefits_amount'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            @if(($payrollTotals['overtime_amount'] ?? 0) > 0)
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Overtime Pay</td>
                <td class="amount-col">{{ number_format($payrollTotals['overtime_amount'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            @endif
            @if(($payrollTotals['bonus_amount'] ?? 0) > 0)
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Bonus & Incentives</td>
                <td class="amount-col">{{ number_format($payrollTotals['bonus_amount'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            @endif
            @if(($payrollTotals['employer_nssf'] ?? 0) > 0)
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">NSSF Employer Contribution</td>
                <td class="amount-col">{{ number_format($payrollTotals['employer_nssf'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            @endif
            @if(($payrollTotals['employer_wcf'] ?? 0) > 0)
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">WCF Employer Contribution</td>
                <td class="amount-col">{{ number_format($payrollTotals['employer_wcf'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            @endif
            @if(($payrollTotals['employer_sdl'] ?? 0) > 0)
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">SDL Employer Contribution</td>
                <td class="amount-col">{{ number_format($payrollTotals['employer_sdl'] ?? 0, 0) }}</td>
                <td class="amount-col"></td>
            </tr>
            @endif

            <!-- CREDIT SECTION -->
            <tr>
                <td colspan="4" class="label-credit">Credit (Liabilities & Cash)</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">NSSF Payable (Employee & Employer)</td>
                <td class="amount-col"></td>
                <td class="amount-col">{{ number_format(($payrollTotals['nssf_amount'] ?? 0) + ($payrollTotals['employer_nssf'] ?? 0), 0) }}</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">NHIF Payable (Employee)</td>
                <td class="amount-col"></td>
                <td class="amount-col">{{ number_format($payrollTotals['nhif_amount'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">SDL Payable</td>
                <td class="amount-col"></td>
                <td class="amount-col">{{ number_format($payrollTotals['employer_sdl'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Net Salaries Payable (Cash/Bank)</td>
                <td class="amount-col"></td>
                <td class="amount-col">{{ number_format($payrollTotals['net_salary'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Other Statutory (HESLB, WCF, SDL, PAYE)</td>
                <td class="amount-col"></td>
                <td class="amount-col">{{ number_format(($payrollTotals['paye_amount'] ?? 0) + ($payrollTotals['heslb_amount'] ?? 0) + ($payrollTotals['wcf_amount'] ?? 0) + ($payrollTotals['sdl_amount'] ?? 0), 0) }}</td>
            </tr>
            <tr>
                <td class="day-col">{{ $day }}</td>
                <td class="indent">Other Deductions (Staff Loans, etc.)</td>
                <td class="amount-col"></td>
                <td class="amount-col">{{ number_format(($payrollTotals['deduction_amount'] ?? 0) + ($payrollTotals['other_deductions'] ?? 0), 0) }}</td>
            </tr>

            <!-- TOTALS -->
            <tr class="total-row">
                <td colspan="2">TOTAL JOURNAL ENTRY</td>
                @php
                    $totalDebit = ($payrollTotals['basic_salary'] ?? 0) + 
                                  ($payrollTotals['overtime_amount'] ?? 0) + 
                                  ($payrollTotals['bonus_amount'] ?? 0) + 
                                  ($payrollTotals['allowance_amount'] ?? 0) + 
                                  ($payrollTotals['house_benefit_amount'] ?? 0) + 
                                  ($payrollTotals['hardship_benefit_amount'] ?? 0) + 
                                  ($payrollTotals['other_benefits_amount'] ?? 0) + 
                                  ($payrollTotals['employer_nssf'] ?? 0) +
                                  ($payrollTotals['employer_wcf'] ?? 0) +
                                  ($payrollTotals['employer_sdl'] ?? 0);
                    
                    $totalCredit = ($payrollTotals['nssf_amount'] ?? 0) + 
                                   ($payrollTotals['employer_nssf'] ?? 0) +
                                   ($payrollTotals['nhif_amount'] ?? 0) +
                                   ($payrollTotals['net_salary'] ?? 0) +
                                   ($payrollTotals['paye_amount'] ?? 0) + 
                                   ($payrollTotals['heslb_amount'] ?? 0) + 
                                   ($payrollTotals['wcf_amount'] ?? 0) + 
                                   ($payrollTotals['sdl_amount'] ?? 0) +
                                   ($payrollTotals['deduction_amount'] ?? 0) +
                                   ($payrollTotals['other_deductions'] ?? 0);
                @endphp
                <td class="amount-col">{{ number_format($totalDebit, 0) }}</td>
                <td class="amount-col">{{ number_format($totalCredit, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-label">PREPARED BY</div>
                    <div class="signature-line"></div>
                    <div class="signature-date">SIGN & DATE</div>
                </td>
                <td>
                    <div class="signature-label">CHECKED BY</div>
                    <div class="signature-line"></div>
                    <div class="signature-date">SIGN & DATE</div>
                </td>
                <td>
                    <div class="signature-label">APPROVED BY</div>
                    <div class="signature-line"></div>
                    <div class="signature-date">SIGN & DATE</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated by OfisiLink Payroll System &bull; {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
