<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Accounts List</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: #999;
        }
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 5px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'BANK-ACC-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'BANK ACCOUNTS LIST',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    @php
        $totalBalance = $bankAccounts->sum('balance');
        $activeAccounts = $bankAccounts->where('is_active', true)->count();
        $primaryAccounts = $bankAccounts->where('is_primary', true)->count();
    @endphp

    <div class="summary">
        <strong>Summary:</strong>
        Total Accounts: {{ $bankAccounts->count() }} | 
        Active: {{ $activeAccounts }} | 
        Primary: {{ $primaryAccounts }} | 
        Total Balance: TZS {{ number_format($totalBalance, 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Bank Name</th>
                <th>Account Number</th>
                <th>Account Name</th>
                <th>Branch</th>
                <th>SWIFT Code</th>
                <th class="text-right">Balance</th>
                <th class="text-center">Status</th>
                <th>Owner</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bankAccounts as $index => $account)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $account->bank_name ?? 'N/A' }}</td>
                <td><code>{{ $account->account_number ?? 'N/A' }}</code></td>
                <td>{{ $account->account_name ?? 'N/A' }}</td>
                <td>{{ $account->branch_name ?? '-' }}</td>
                <td>{{ $account->swift_code ?? '-' }}</td>
                <td class="text-right {{ ($account->balance ?? 0) >= 0 ? 'status-active' : 'text-danger' }}">
                    TZS {{ number_format($account->balance ?? 0, 2) }}
                </td>
                <td class="text-center">
                    @if($account->is_active)
                        <span class="status-active">Active</span>
                    @else
                        <span class="status-inactive">Inactive</span>
                    @endif
                    @if($account->is_primary)
                        <br><small>(Primary)</small>
                    @endif
                </td>
                <td>{{ $account->user->name ?? 'Organization' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No bank accounts found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total Balance:</th>
                <th class="text-right">TZS {{ number_format($totalBalance, 2) }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 8px; color: #666; text-align: center;">
        Generated on {{ $generatedAt }} | {{ $companyName }}
    </div>
</body>
</html>

