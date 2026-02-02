<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Assessment;
use App\Models\RecruitmentJob;
use App\Models\JobApplication;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $canViewAll = $user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD']);
        
        return view('modules.reports.index', compact('canViewAll'));
    }

    /**
     * Generate report based on type
     */
    public function generate(Request $request)
    {
        $user = Auth::user();
        
        $validator = \Validator::make($request->all(), [
            'report_type' => 'required|string|in:attendance,leave,payroll,performance,recruitment,employee',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'nullable|in:json,pdf,excel',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $reportType = $request->report_type;
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $format = $request->format ?? 'json';

        switch ($reportType) {
            case 'attendance':
                return $this->generateAttendanceReport($dateFrom, $dateTo, $format, $request->filters ?? []);
            case 'leave':
                return $this->generateLeaveReport($dateFrom, $dateTo, $format, $request->filters ?? []);
            case 'payroll':
                return $this->generatePayrollReport($dateFrom, $dateTo, $format, $request->filters ?? []);
            case 'performance':
                return $this->generatePerformanceReport($dateFrom, $dateTo, $format, $request->filters ?? []);
            case 'recruitment':
                return $this->generateRecruitmentReport($dateFrom, $dateTo, $format, $request->filters ?? []);
            case 'employee':
                return $this->generateEmployeeReport($dateFrom, $dateTo, $format, $request->filters ?? []);
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid report type'
                ], 400);
        }
    }

    /**
     * Generate attendance report
     */
    private function generateAttendanceReport($dateFrom, $dateTo, $format, $filters)
    {
        $query = Attendance::with(['user', 'user.employee', 'user.primaryDepartment'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        if (isset($filters['department_id'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('primary_department_id', $filters['department_id']);
            });
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $attendances = $query->get();

        $report = [
            'type' => 'attendance',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'total_records' => $attendances->count(),
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('is_late', true)->count(),
                'overtime' => $attendances->where('is_overtime', true)->count(),
            ],
            'data' => $attendances,
        ];

        return $this->formatReport($report, $format, 'attendance');
    }

    /**
     * Generate leave report
     */
    private function generateLeaveReport($dateFrom, $dateTo, $format, $filters)
    {
        $query = LeaveRequest::with(['user', 'leaveType'])
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->orWhereBetween('end_date', [$dateFrom, $dateTo]);

        if (isset($filters['department_id'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('primary_department_id', $filters['department_id']);
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $leaves = $query->get();

        $report = [
            'type' => 'leave',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'total_requests' => $leaves->count(),
                'approved' => $leaves->where('status', 'approved')->count(),
                'pending' => $leaves->where('status', 'pending')->count(),
                'rejected' => $leaves->where('status', 'rejected')->count(),
            ],
            'data' => $leaves,
        ];

        return $this->formatReport($report, $format, 'leave');
    }

    /**
     * Generate payroll report
     */
    private function generatePayrollReport($dateFrom, $dateTo, $format, $filters)
    {
        $query = Payroll::with(['user', 'user.employee'])
            ->whereBetween('pay_date', [$dateFrom, $dateTo]);

        if (isset($filters['department_id'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('primary_department_id', $filters['department_id']);
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $payrolls = $query->get();

        $report = [
            'type' => 'payroll',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'total_payrolls' => $payrolls->count(),
                'total_amount' => $payrolls->sum('total_amount'),
                'paid' => $payrolls->where('status', 'paid')->count(),
                'pending' => $payrolls->where('status', 'pending')->count(),
            ],
            'data' => $payrolls,
        ];

        return $this->formatReport($report, $format, 'payroll');
    }

    /**
     * Generate performance report
     */
    private function generatePerformanceReport($dateFrom, $dateTo, $format, $filters)
    {
        $year = $dateFrom->year;
        
        $query = Assessment::with(['employee', 'activities', 'activities.progressReports'])
            ->where('status', 'approved')
            ->whereYear('created_at', $year);

        if (isset($filters['user_id'])) {
            $query->where('employee_id', $filters['user_id']);
        }

        $assessments = $query->get();

        $report = [
            'type' => 'performance',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'year' => $year,
            ],
            'summary' => [
                'total_assessments' => $assessments->count(),
                'total_employees' => $assessments->pluck('employee_id')->unique()->count(),
            ],
            'data' => $assessments,
        ];

        return $this->formatReport($report, $format, 'performance');
    }

    /**
     * Generate recruitment report
     */
    private function generateRecruitmentReport($dateFrom, $dateTo, $format, $filters)
    {
        $query = RecruitmentJob::with(['creator', 'applications'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $jobs = $query->get();

        $report = [
            'type' => 'recruitment',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'total_jobs' => $jobs->count(),
                'active_jobs' => $jobs->where('status', 'Active')->count(),
                'total_applications' => $jobs->sum(function($job) {
                    return $job->applications->count();
                }),
                'hired' => JobApplication::whereIn('job_id', $jobs->pluck('id'))
                    ->where('status', 'Hired')
                    ->count(),
            ],
            'data' => $jobs,
        ];

        return $this->formatReport($report, $format, 'recruitment');
    }

    /**
     * Generate employee report
     */
    private function generateEmployeeReport($dateFrom, $dateTo, $format, $filters)
    {
        $query = User::with(['employee', 'primaryDepartment', 'roles'])
            ->whereHas('employee');

        if (isset($filters['department_id'])) {
            $query->where('primary_department_id', $filters['department_id']);
        }

        if (isset($filters['employment_status'])) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('employment_status', $filters['employment_status']);
            });
        }

        $employees = $query->get();

        $report = [
            'type' => 'employee',
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
            'summary' => [
                'total_employees' => $employees->count(),
                'active' => $employees->where('is_active', true)->count(),
                'inactive' => $employees->where('is_active', false)->count(),
            ],
            'data' => $employees,
        ];

        return $this->formatReport($report, $format, 'employee');
    }

    /**
     * Format report based on requested format
     */
    private function formatReport($report, $format, $type)
    {
        switch ($format) {
            case 'pdf':
                return $this->exportPdf($report, $type);
            case 'excel':
                return $this->exportExcel($report, $type);
            default:
                return response()->json([
                    'success' => true,
                    'report' => $report
                ]);
        }
    }

    /**
     * Export PDF
     */
    private function exportPdf($report, $type)
    {
        $pdf = Pdf::loadView('modules.reports.pdf.' . $type, compact('report'));
        $filename = ucfirst($type) . '_Report_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Excel
     */
    private function exportExcel($report, $type)
    {
        // Implementation for Excel export
        return response()->json(['message' => 'Excel export not yet implemented']);
    }
}
