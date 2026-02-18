@extends('layouts.app')

@section('title', 'Payslip - ' . ($payrollItem->employee->name ?? 'Employee') . ' - ' . ($payrollItem->payroll->pay_period ?? '') . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #940000 0%, #c00000 100%); border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="avatar avatar-lg me-3" style="width: 70px; height: 70px;">
                                <span class="avatar-initial rounded-circle bg-white" style="font-size: 1.8rem; color: #940000;">
                                    <i class="bx bx-receipt"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-1 text-white fw-bold">Employee Payslip</h3>
                                <div class="d-flex align-items-center flex-wrap gap-2 text-white-50">
                                    <span class="fs-5 fw-semibold">{{ $payrollItem->employee->name ?? 'N/A' }}</span>
                                    <span class="badge bg-white px-3" style="color: #940000;">{{ \Carbon\Carbon::parse($payrollItem->payroll->pay_period . '-01')->format('F Y') ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light btn-lg px-4 shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                            <a href="{{ route('payroll.payslip.pdf', $payrollItem->id) }}" class="btn btn-warning btn-lg px-4 shadow-sm" target="_blank">
                                <i class="bx bx-download me-2"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    @php
        $payslipData = [
            'id' => $payrollItem->id,
            'employee_id' => $payrollItem->employee_id,
            'basic_salary' => (float)($payrollItem->basic_salary ?? 0),
            'overtime_amount' => (float)($payrollItem->overtime_amount ?? 0),
            'overtime_hours' => (float)($payrollItem->overtime_hours ?? 0),
            'bonus_amount' => (float)($payrollItem->bonus_amount ?? 0),
            'allowance_amount' => (float)($payrollItem->allowance_amount ?? 0),
            'nssf_amount' => (float)($payrollItem->nssf_amount ?? 0),
            'nhif_amount' => (float)($payrollItem->nhif_amount ?? 0),
            'heslb_amount' => (float)($payrollItem->heslb_amount ?? 0),
            'paye_amount' => (float)($payrollItem->paye_amount ?? 0),
            'wcf_amount' => (float)($payrollItem->wcf_amount ?? 0),
            'sdl_amount' => (float)($payrollItem->sdl_amount ?? 0),
            'deduction_amount' => (float)($payrollItem->deduction_amount ?? 0),
            'other_deductions' => (float)($payrollItem->other_deductions ?? 0),
            'net_salary' => (float)($payrollItem->net_salary ?? 0),
            'status' => $payrollItem->status ?? 'processed',
            'payroll' => [
                'id' => $payrollItem->payroll->id ?? null,
                'pay_period' => $payrollItem->payroll->pay_period ?? '',
                'pay_date' => $payrollItem->payroll->pay_date ? $payrollItem->payroll->pay_date->format('Y-m-d') : null,
                'status' => $payrollItem->payroll->status ?? 'processed',
                'processed_by' => ($payrollItem->payroll->processor ?? null) ? $payrollItem->payroll->processor->name : 'N/A',
                'reviewed_by' => ($payrollItem->payroll->reviewer ?? null) ? $payrollItem->payroll->reviewer->name : null,
                'approved_by' => ($payrollItem->payroll->approver ?? null) ? $payrollItem->payroll->approver->name : null,
                'paid_by' => ($payrollItem->payroll->payer ?? null) ? $payrollItem->payroll->payer->name : null,
            ],
            'employee' => [
                'id' => $payrollItem->employee->id ?? null,
                'name' => $payrollItem->employee->name ?? 'N/A',
                'employee_id' => $payrollItem->employee->employee->employee_id ?? $payrollItem->employee->id ?? 'N/A',
                'department' => ($payrollItem->employee->primaryDepartment ?? null) ? $payrollItem->employee->primaryDepartment->name : 'N/A',
                'position' => ($payrollItem->employee->employee ?? null) ? $payrollItem->employee->employee->position : 'N/A',
            ]
        ];
        $grossSalary = $payslipData['basic_salary'] + $payslipData['overtime_amount'] + $payslipData['bonus_amount'] + $payslipData['allowance_amount'];
        $totalDeductions = $payslipData['paye_amount'] + $payslipData['nssf_amount'] + $payslipData['nhif_amount'] + $payslipData['heslb_amount'] + $payslipData['wcf_amount'] + $payslipData['sdl_amount'] + $payslipData['deduction_amount'] + $payslipData['other_deductions'];
        $calculatedNetPay = $grossSalary - $totalDeductions;
        $netPay = $payslipData['net_salary'] > 0 ? $payslipData['net_salary'] : $calculatedNetPay;
        
        // Calculate percentages
        $payePercent = $grossSalary > 0 ? ($payslipData['paye_amount'] / $grossSalary) * 100 : 0;
        $nssfPercent = $grossSalary > 0 ? ($payslipData['nssf_amount'] / $grossSalary) * 100 : 0;
        $deductionPercent = $grossSalary > 0 ? ($totalDeductions / $grossSalary) * 100 : 0;
        $netPercent = $grossSalary > 0 ? ($netPay / $grossSalary) * 100 : 0;
    @endphp


    <!-- Payslip Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header text-white" style="background-color: #940000;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-white">
                                <i class="bx bx-receipt me-2"></i>Payslip
                            </h5>
                            <small class="text-white-50">
                                {{ \Carbon\Carbon::parse($payslipData['payroll']['pay_period'] . '-01')->format('F Y') ?? 'N/A' }}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                {{ ucfirst($payslipData['payroll']['status']) }}
                            </span>
                        </div>
                    </div>
                </div>
                <br>
                <div class="card-body">
                    <!-- Employee & Processing Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="card border-0 shadow-sm h-100" style="background: rgba(148, 0, 0, 0.05); border-radius: 12px; border-left: 5px solid #940000 !important;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="avatar rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background-color: #940000;">
                                            <i class="bx bx-user text-white fs-4"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold" style="color: #940000;">Employee Profile</h5>
                                    </div>
                                    <div class="info-list">
                                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                            <span class="text-muted fw-medium">Full Name</span>
                                            <span class="fw-bold text-dark">{{ $payslipData['employee']['name'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                            <span class="text-muted fw-medium">Employee ID</span>
                                            <span class="badge px-3" style="background-color: rgba(148, 0, 0, 0.1); color: #940000;">#{{ $payslipData['employee']['employee_id'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                            <span class="text-muted fw-medium">Department</span>
                                            <span class="fw-bold text-dark">{{ $payslipData['employee']['department'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted fw-medium">Job Position</span>
                                            <span class="fw-bold text-dark">{{ $payslipData['employee']['position'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100" style="background: rgba(23, 162, 184, 0.05); border-radius: 12px; border-left: 5px solid #17a2b8 !important;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="avatar avatar bg-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="bx bx-calendar text-white fs-4"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-info">Period Summary</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-sm-6">
                                            <label class="text-muted small d-block mb-1 fw-medium">PAY PERIOD</label>
                                            <span class="fw-bold text-dark fs-6">{{ \Carbon\Carbon::parse($payslipData['payroll']['pay_period'] . '-01')->format('F Y') }}</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="text-muted small d-block mb-1 fw-medium">PAY DATE</label>
                                            <span class="fw-bold text-dark fs-6">{{ $payslipData['payroll']['pay_date'] ? \Carbon\Carbon::parse($payslipData['payroll']['pay_date'])->format('M d, Y') : 'Processing' }}</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="text-muted small d-block mb-1 fw-medium">STATUS</label>
                                            <span class="badge bg-{{ $payslipData['payroll']['status'] === 'paid' ? 'success' : 'warning' }} shadow-sm px-3">
                                                <i class="bx {{ $payslipData['payroll']['status'] === 'paid' ? 'bx-check-double' : 'bx-time' }} me-1"></i>{{ strtoupper($payslipData['payroll']['status']) }}
                                            </span>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="text-muted small d-block mb-1 fw-medium">PROCESSED BY</label>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs bg-label-secondary me-2 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                                    {{ substr($payslipData['payroll']['processed_by'], 0, 1) }}
                                                </div>
                                                <span class="text-dark fw-medium small">{{ $payslipData['payroll']['processed_by'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Income and Deductions Modern Layout -->
                    <div class="row mb-5">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                                <div class="card-header bg-success bg-opacity-10 border-0 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bx bx-trending-up text-white fs-4"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-success">EARNINGS BREAKDOWN</h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-3 py-3">DESCRIPTION</th>
                                                    <th class="text-end pe-3 py-3">AMOUNT (TZS)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">Basic Salary</div>
                                                        <small class="text-muted small">Standard monthly base pay</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-dark">{{ number_format($payslipData['basic_salary'], 2) }}</td>
                                                </tr>
                                                @if($payslipData['overtime_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">Overtime Pay</div>
                                                        <small class="text-muted small">{{ number_format($payslipData['overtime_hours'], 1) }} hours worked</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-success">+{{ number_format($payslipData['overtime_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['bonus_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">Bonus & Incentives</div>
                                                        <small class="text-muted small">Performance/Referral awards</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-success">+{{ number_format($payslipData['bonus_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['allowance_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">Allowances</div>
                                                        <small class="text-muted small">House, Meal, Travel, etc.</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-success">+{{ number_format($payslipData['allowance_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                            <tfoot class="bg-success bg-opacity-10 border-0">
                                                <tr>
                                                    <td class="ps-3 py-3 fw-bold text-success uppercase small">TOTAL GROSS EARNINGS</td>
                                                    <td class="text-end pe-3 py-3">
                                                        <span class="fs-5 fw-bold text-success">TZS {{ number_format($grossSalary, 2) }}</span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                                <div class="card-header bg-danger bg-opacity-10 border-0 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bx bx-trending-down text-white fs-4"></i>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-danger">DEDUCTIONS BREAKDOWN</h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-3 py-3">CONTRIBUTION/TAX</th>
                                                    <th class="text-end pe-3 py-3">AMOUNT (TZS)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($payslipData['paye_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">P.A.Y.E</div>
                                                        <small class="text-muted small">Pay As You Earn Tax</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-danger">-{{ number_format($payslipData['paye_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['nssf_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">N.S.S.F</div>
                                                        <small class="text-muted small">Social Security Fund</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-danger">-{{ number_format($payslipData['nssf_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['nhif_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">N.H.I.F</div>
                                                        <small class="text-muted small">Health Insurance Fund</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-danger">-{{ number_format($payslipData['nhif_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['heslb_amount'] > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">H.E.S.L.B</div>
                                                        <small class="text-muted small">Education Loan Board</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-danger">-{{ number_format($payslipData['heslb_amount'], 2) }}</td>
                                                </tr>
                                                @endif
                                                @if(($payslipData['deduction_amount'] + $payslipData['other_deductions']) > 0)
                                                <tr>
                                                    <td class="ps-3 py-3">
                                                        <div class="fw-semibold text-dark">Other Deductions</div>
                                                        <small class="text-muted small">Additional/Manual adjustments</small>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-danger">-{{ number_format($payslipData['deduction_amount'] + $payslipData['other_deductions'], 2) }}</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                            <tfoot class="bg-danger bg-opacity-10 border-0">
                                                <tr>
                                                    <td class="ps-3 py-3 fw-bold text-danger uppercase small">TOTAL DEDUCTIONS</td>
                                                    <td class="text-end pe-3 py-3">
                                                        <span class="fs-5 fw-bold text-danger">TZS {{ number_format($totalDeductions, 2) }}</span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Pay Calculation Summary -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #940000 0%, #610000 100%); border-radius: 15px; overflow: hidden;">
                                <div class="card-body p-5 text-white">
                                    <div class="row align-items-center">
                                        <div class="col-lg-7 mb-4 mb-lg-0">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                                    <i class="bx bx-wallet text-white fs-3"></i>
                                                </div>
                                                <h4 class="mb-0 fw-bold text-white">NET TAKE-HOME PAY</h4>
                                            </div>
                                            <div class="calculation-box bg-white p-4 rounded-3 shadow-sm">
                                                <div class="list-group list-group-flush bg-transparent">
                                                    <div class="list-group-item bg-transparent px-0 border-light d-flex justify-content-between align-items-center">
                                                        <span class="text-muted fw-medium">Total Gross Earnings</span>
                                                        <span class="fs-5 fw-bold" style="color: #940000;">TZS {{ number_format($grossSalary, 2) }}</span>
                                                    </div>
                                                    <div class="list-group-item bg-transparent px-0 border-light d-flex justify-content-between align-items-center">
                                                        <span class="text-muted fw-medium">Total Deductions</span>
                                                        <span class="fs-5 fw-bold" style="color: #940000;">- TZS {{ number_format($totalDeductions, 2) }}</span>
                                                    </div>
                                                    <div class="list-group-item bg-transparent px-0 border-0 pt-3 d-flex justify-content-between align-items-center">
                                                        <span class="fs-4 fw-bold" style="color: #940000;">NET SALARY</span>
                                                        <span class="text-end">
                                                            <div class="fs-1 fw-bold mb-0" style="color: #940000;">TZS {{ number_format($netPay, 2) }}</div>
                                                            <small class="fw-medium" style="color: #940000; opacity: 0.8;">{{ number_format($netPercent, 1) }}% of gross remains</small>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="text-center p-4 rounded-4" style="background: rgba(255,255,255,0.15); border: 2px dashed rgba(255,255,255,0.4);">
                                                <div class="mb-3">
                                                    <h6 class="text-white opacity-75 small mb-1">TOTAL PAYABLE FOR</h6>
                                                    <h5 class="text-white fw-bold">{{ \Carbon\Carbon::parse($payslipData['payroll']['pay_period'] . '-01')->format('F Y') }}</h5>
                                                </div>
                                                <div class="display-3 fw-bold mb-3 text-white" style="letter-spacing: -2px;">
                                                    TZS {{ number_format($netPay, 0) }}
                                                </div>
                                                <div class="d-inline-block bg-white fw-bold px-4 py-2 rounded-pill shadow-sm" style="color: #940000;">
                                                    {{ strtoupper($payslipData['payroll']['status']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Summary Statistics -->
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-radius: 12px; border-bottom: 4px solid #28a745 !important;">
                                <div class="avatar bg-success rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bx bx-trending-up text-white fs-4"></i>
                                </div>
                                <h6 class="text-muted small fw-bold mb-1">GROSS EARNINGS</h6>
                                <h4 class="text-dark fw-bold mb-0">TZS {{ number_format($grossSalary, 2) }}</h4>
                                <div class="mt-2">
                                    <div class="progress shadow-none" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">100% of income</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-radius: 12px; border-bottom: 4px solid #dc3545 !important;">
                                <div class="avatar bg-danger rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bx bx-trending-down text-white fs-4"></i>
                                </div>
                                <h6 class="text-muted small fw-bold mb-1">TOTAL DEDUCTIONS</h6>
                                <h4 class="text-dark fw-bold mb-0">TZS {{ number_format($totalDeductions, 2) }}</h4>
                                <div class="mt-2">
                                    <div class="progress shadow-none" style="height: 6px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $deductionPercent }}%"></div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">{{ number_format($deductionPercent, 1) }}% of earnings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-radius: 12px; border-bottom: 4px solid #940000 !important;">
                                <div class="avatar rounded-circle mx-auto mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background-color: #940000;">
                                    <i class="bx bx-check-shield text-white fs-4"></i>
                                </div>
                                <h6 class="text-muted small fw-bold mb-1">NET TAKE-HOME</h6>
                                <h4 class="text-dark fw-bold mb-0">TZS {{ number_format($netPay, 2) }}</h4>
                                <div class="mt-2">
                                    <div class="progress shadow-none" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $netPercent }}%; background-color: #940000;"></div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">{{ number_format($netPercent, 1) }}% of earnings</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

