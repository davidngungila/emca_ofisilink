<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceReportController extends Controller
{
    /**
     * Display attendance reports page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $canViewAll = $user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD']);
        
        return view('modules.hr.attendance-reports', compact('canViewAll'));
    }

    /**
     * Generate individual attendance report (for employee)
     */
    public function myReport(Request $request)
    {
        $user = Auth::user();
        
        $validator = \Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'nullable|in:json,pdf,excel',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->orderBy('attendance_date', 'desc')
            ->get();

        $report = $this->generateIndividualReport($attendances, $user, $dateFrom, $dateTo);

        if ($request->format === 'pdf') {
            return $this->exportIndividualPdf($report, $user, $dateFrom, $dateTo);
        } elseif ($request->format === 'excel') {
            return $this->exportIndividualExcel($report, $user, $dateFrom, $dateTo);
        }

        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Generate general attendance report (for HR/Admin)
     */
    public function generalReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = \Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'format' => 'nullable|in:json,pdf,excel',
            'report_type' => 'nullable|in:summary,detailed,late_analysis,absenteeism,overtime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $reportType = $request->report_type ?? 'summary';

        $query = Attendance::with(['user', 'user.employee', 'user.primaryDepartment'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('primary_department_id', $request->department_id);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $report = $this->generateGeneralReport($attendances, $dateFrom, $dateTo, $reportType);

        if ($request->format === 'pdf') {
            return $this->exportGeneralPdf($report, $dateFrom, $dateTo, $reportType);
        } elseif ($request->format === 'excel') {
            return $this->exportGeneralExcel($report, $dateFrom, $dateTo, $reportType);
        }

        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Generate individual report data
     */
    private function generateIndividualReport($attendances, $user, $dateFrom, $dateTo)
    {
        $totalDays = $dateFrom->diffInDays($dateTo) + 1;
        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $totalDays - $presentDays;
        $lateCount = $attendances->where('is_late', true)->count();
        $earlyLeaveCount = $attendances->where('is_early_leave', true)->count();
        $overtimeCount = $attendances->where('is_overtime', true)->count();
        
        $totalHours = $attendances->sum('total_hours') / 60; // Convert minutes to hours
        
        return [
            'employee' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
            ],
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'total_days' => $totalDays,
            ],
            'summary' => [
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_count' => $lateCount,
                'early_leave_count' => $earlyLeaveCount,
                'overtime_count' => $overtimeCount,
                'total_hours' => round($totalHours, 2),
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
            ],
            'details' => $attendances->map(function($attendance) {
                return [
                    'date' => $attendance->attendance_date->format('Y-m-d'),
                    'time_in' => $attendance->time_in ? Carbon::parse($attendance->time_in)->format('H:i') : null,
                    'time_out' => $attendance->time_out ? Carbon::parse($attendance->time_out)->format('H:i') : null,
                    'status' => $attendance->status,
                    'is_late' => $attendance->is_late,
                    'is_early_leave' => $attendance->is_early_leave,
                    'is_overtime' => $attendance->is_overtime,
                    'total_hours' => $attendance->total_hours ? round($attendance->total_hours / 60, 2) : 0,
                ];
            }),
        ];
    }

    /**
     * Generate general report data
     */
    private function generateGeneralReport($attendances, $dateFrom, $dateTo, $reportType)
    {
        $totalDays = $dateFrom->diffInDays($dateTo) + 1;
        $uniqueUsers = $attendances->pluck('user_id')->unique();
        
        $summary = [
            'total_employees' => $uniqueUsers->count(),
            'total_records' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'absent_days' => $totalDays * $uniqueUsers->count() - $attendances->where('status', 'present')->count(),
            'late_count' => $attendances->where('is_late', true)->count(),
            'early_leave_count' => $attendances->where('is_early_leave', true)->count(),
            'overtime_count' => $attendances->where('is_overtime', true)->count(),
        ];

        $byEmployee = $attendances->groupBy('user_id')->map(function($userAttendances, $userId) use ($totalDays) {
            $user = $userAttendances->first()->user;
            $presentDays = $userAttendances->where('status', 'present')->count();
            
            return [
                'employee_id' => $userId,
                'employee_name' => $user->name ?? 'Unknown',
                'employee_number' => $user->employee_id ?? 'N/A',
                'department' => $user->primaryDepartment->name ?? 'N/A',
                'present_days' => $presentDays,
                'absent_days' => max(0, $totalDays - $presentDays),
                'late_count' => $userAttendances->where('is_late', true)->count(),
                'early_leave_count' => $userAttendances->where('is_early_leave', true)->count(),
                'overtime_count' => $userAttendances->where('is_overtime', true)->count(),
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
            ];
        })->values();

        $byDepartment = $attendances->groupBy(function($attendance) {
            return $attendance->user->primaryDepartment->id ?? 'unknown';
        })->map(function($deptAttendances, $deptId) use ($totalDays) {
            $dept = $deptAttendances->first()->user->primaryDepartment ?? null;
            $uniqueUsers = $deptAttendances->pluck('user_id')->unique()->count();
            $presentDays = $deptAttendances->where('status', 'present')->count();
            
            return [
                'department_id' => $deptId === 'unknown' ? null : $deptId,
                'department_name' => $dept->name ?? 'Unknown',
                'total_employees' => $uniqueUsers,
                'present_days' => $presentDays,
                'absent_days' => max(0, ($totalDays * $uniqueUsers) - $presentDays),
                'attendance_rate' => ($totalDays * $uniqueUsers) > 0 
                    ? round(($presentDays / ($totalDays * $uniqueUsers)) * 100, 2) 
                    : 0,
            ];
        })->values();

        $report = [
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'total_days' => $totalDays,
            ],
            'summary' => $summary,
            'by_employee' => $byEmployee,
            'by_department' => $byDepartment,
        ];

        if ($reportType === 'late_analysis') {
            $report['late_analysis'] = $this->generateLateAnalysis($attendances);
        }

        if ($reportType === 'absenteeism') {
            $report['absenteeism'] = $this->generateAbsenteeismAnalysis($attendances, $dateFrom, $dateTo);
        }

        if ($reportType === 'overtime') {
            $report['overtime'] = $this->generateOvertimeAnalysis($attendances);
        }

        return $report;
    }

    /**
     * Generate late analysis
     */
    private function generateLateAnalysis($attendances)
    {
        $lateRecords = $attendances->where('is_late', true);
        
        return [
            'total_late' => $lateRecords->count(),
            'by_employee' => $lateRecords->groupBy('user_id')->map(function($records, $userId) {
                $user = $records->first()->user;
                return [
                    'employee_id' => $userId,
                    'employee_name' => $user->name ?? 'Unknown',
                    'late_count' => $records->count(),
                ];
            })->values()->sortByDesc('late_count')->take(10),
        ];
    }

    /**
     * Generate absenteeism analysis
     */
    private function generateAbsenteeismAnalysis($attendances, $dateFrom, $dateTo)
    {
        $totalDays = $dateFrom->diffInDays($dateTo) + 1;
        $allUsers = User::whereHas('employee')->pluck('id');
        
        $absenteeism = [];
        foreach ($allUsers as $userId) {
            $userAttendances = $attendances->where('user_id', $userId);
            $presentDays = $userAttendances->where('status', 'present')->count();
            $absentDays = $totalDays - $presentDays;
            
            if ($absentDays > 0) {
                $user = User::find($userId);
                $absenteeism[] = [
                    'employee_id' => $userId,
                    'employee_name' => $user->name ?? 'Unknown',
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'absenteeism_rate' => round(($absentDays / $totalDays) * 100, 2),
                ];
            }
        }
        
        return collect($absenteeism)->sortByDesc('absent_days')->values()->take(20);
    }

    /**
     * Generate overtime analysis
     */
    private function generateOvertimeAnalysis($attendances)
    {
        $overtimeRecords = $attendances->where('is_overtime', true);
        
        return [
            'total_overtime' => $overtimeRecords->count(),
            'total_overtime_hours' => round($overtimeRecords->sum('total_hours') / 60, 2),
            'by_employee' => $overtimeRecords->groupBy('user_id')->map(function($records, $userId) {
                $user = $records->first()->user;
                $totalHours = round($records->sum('total_hours') / 60, 2);
                return [
                    'employee_id' => $userId,
                    'employee_name' => $user->name ?? 'Unknown',
                    'overtime_count' => $records->count(),
                    'total_overtime_hours' => $totalHours,
                ];
            })->values()->sortByDesc('total_overtime_hours')->take(10),
        ];
    }

    /**
     * Export individual PDF
     */
    private function exportIndividualPdf($report, $user, $dateFrom, $dateTo)
    {
        $pdf = Pdf::loadView('modules.hr.pdf.attendance-individual', compact('report', 'user', 'dateFrom', 'dateTo'));
        $filename = 'Attendance_Report_' . $user->employee_id . '_' . $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export individual Excel
     */
    private function exportIndividualExcel($report, $user, $dateFrom, $dateTo)
    {
        // Implementation for Excel export
        // This would typically use Laravel Excel package
        return response()->json(['message' => 'Excel export not yet implemented']);
    }

    /**
     * Export general PDF
     */
    private function exportGeneralPdf($report, $dateFrom, $dateTo, $reportType)
    {
        $pdf = Pdf::loadView('modules.hr.pdf.attendance-general', compact('report', 'dateFrom', 'dateTo', 'reportType'));
        $filename = 'Attendance_Report_' . $reportType . '_' . $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export general Excel
     */
    private function exportGeneralExcel($report, $dateFrom, $dateTo, $reportType)
    {
        // Implementation for Excel export
        return response()->json(['message' => 'Excel export not yet implemented']);
    }

    /**
     * Generate PDF report for staff timing (early, late, early leave)
     */
    public function timingReportPdf(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            abort(403, 'Unauthorized');
        }

        $validator = \Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::today()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::today();

        $query = Attendance::with(['user', 'user.employee', 'user.primaryDepartment', 'workSchedule'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotNull('time_in');

        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('primary_department_id', $request->department_id);
            });
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        // Separate into early, late, and early leave
        $earlyArrivals = [];
        $lateArrivals = [];
        $earlyLeaves = [];

        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            $schedule = $attendance->workSchedule;
            
            // Get expected times
            $expectedStartTime = '09:00:00'; // Default
            $expectedEndTime = '17:00:00'; // Default
            
            if ($schedule && $schedule->start_time) {
                $expectedStartTime = Carbon::parse($schedule->start_time)->format('H:i:s');
            }
            if ($schedule && $schedule->end_time) {
                $expectedEndTime = Carbon::parse($schedule->end_time)->format('H:i:s');
            }

            // Get actual times
            $timeIn = $attendance->time_in;
            if ($timeIn instanceof Carbon) {
                $actualTimeIn = $timeIn->format('H:i:s');
            } else {
                $actualTimeIn = Carbon::parse($timeIn)->format('H:i:s');
            }

            $timeOut = $attendance->time_out;
            $actualTimeOut = null;
            if ($timeOut) {
                if ($timeOut instanceof Carbon) {
                    $actualTimeOut = $timeOut->format('H:i:s');
                } else {
                    $actualTimeOut = Carbon::parse($timeOut)->format('H:i:s');
                }
            }

            // Calculate minutes difference
            $expectedStart = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $expectedStartTime);
            $actualStart = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $actualTimeIn);
            $minutesDiff = $actualStart->diffInMinutes($expectedStart, false); // false = signed difference

            $record = [
                'date' => $attendance->attendance_date->format('Y-m-d'),
                'employee_name' => $user->name ?? 'Unknown',
                'employee_id' => $user->employee_id ?? 'N/A',
                'department' => $user->primaryDepartment->name ?? 'N/A',
                'expected_time_in' => $expectedStartTime,
                'actual_time_in' => $actualTimeIn,
                'expected_time_out' => $expectedEndTime,
                'actual_time_out' => $actualTimeOut ?? 'N/A',
                'minutes_diff' => $minutesDiff,
            ];

            // Check for early arrival (arrived before expected time) - prioritize this
            if ($minutesDiff < 0) {
                $record['minutes_early'] = abs($minutesDiff);
                $earlyArrivals[] = $record;
            } elseif ($attendance->is_late || $minutesDiff > 0) {
                // Check for late arrival (only if not early)
                $record['minutes_late'] = $minutesDiff > 0 ? $minutesDiff : 0;
                $lateArrivals[] = $record;
            }

            // Check for early leave
            if ($attendance->is_early_leave && $actualTimeOut) {
                $expectedEnd = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $expectedEndTime);
                $actualEnd = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $actualTimeOut);
                $leaveMinutesDiff = $expectedEnd->diffInMinutes($actualEnd, false);
                
                if ($leaveMinutesDiff > 0) {
                    $earlyLeaveRecord = $record;
                    $earlyLeaveRecord['minutes_early'] = $leaveMinutesDiff;
                    $earlyLeaves[] = $earlyLeaveRecord;
                }
            }
        }

        // Sort by date and time
        usort($earlyArrivals, function($a, $b) {
            return strcmp($b['date'], $a['date']) ?: strcmp($a['actual_time_in'], $b['actual_time_in']);
        });
        usort($lateArrivals, function($a, $b) {
            return strcmp($b['date'], $a['date']) ?: ($b['minutes_late'] ?? 0) <=> ($a['minutes_late'] ?? 0);
        });
        usort($earlyLeaves, function($a, $b) {
            return strcmp($b['date'], $a['date']) ?: ($b['minutes_early'] ?? 0) <=> ($a['minutes_early'] ?? 0);
        });

        $reportData = [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'early_arrivals' => $earlyArrivals,
            'late_arrivals' => $lateArrivals,
            'early_leaves' => $earlyLeaves,
            'total_early' => count($earlyArrivals),
            'total_late' => count($lateArrivals),
            'total_early_leaves' => count($earlyLeaves),
        ];

        $pdf = Pdf::loadView('modules.hr.pdf.attendance-timing', compact('reportData', 'dateFrom', 'dateTo'));
        $filename = 'Attendance_Timing_Report_' . $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Advanced Attendance Reports - Main Entry Point
     */
    public function advancedReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            abort(403, 'Unauthorized');
        }

        $reportType = $request->get('report_type', 'daily');
        $format = $request->get('format', 'pdf');
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dateFrom = $request->get('date_from', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
        $departmentId = $request->get('department_id');

        switch ($reportType) {
            case 'daily':
                return $this->generateDailyReport($date, $departmentId, $format);
            case 'monthly':
                return $this->generateMonthlySummaryReport($dateFrom, $dateTo, $departmentId, $format);
            case 'late':
                return $this->generateLateComingReport($dateFrom, $dateTo, $departmentId, $format);
            case 'absenteeism':
                return $this->generateAbsenteeismReport($dateFrom, $dateTo, $departmentId, $format);
            case 'overtime':
                return $this->generateOvertimeReport($dateFrom, $dateTo, $departmentId, $format);
            case 'leave':
                return $this->generateLeaveReport($dateFrom, $dateTo, $departmentId, $format);
            case 'department':
                return $this->generateDepartmentReport($dateFrom, $dateTo, $format);
            case 'exception':
                return $this->generateExceptionReport($dateFrom, $dateTo, $departmentId, $format);
            default:
                abort(404, 'Report type not found');
        }
    }

    /**
     * 1. Daily Attendance Report
     */
    private function generateDailyReport($date, $departmentId = null, $format = 'pdf')
    {
        $reportDate = Carbon::parse($date);
        $query = Attendance::with(['user', 'user.primaryDepartment', 'workSchedule'])
            ->whereDate('attendance_date', $reportDate);

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $attendances = $query->orderBy('time_in')->get();
        
        // Get all active users to show absentees
        $allUsersQuery = User::where('is_active', true)->whereHas('employee');
        if ($departmentId) {
            $allUsersQuery->where('primary_department_id', $departmentId);
        }
        $allUsers = $allUsersQuery->get();
        
        $presentUserIds = $attendances->pluck('user_id')->unique();
        $absentUsers = $allUsers->whereNotIn('id', $presentUserIds);
        
        // Get users on leave
        $onLeaveUsers = LeaveRequest::whereDate('start_date', '<=', $reportDate)
            ->whereDate('end_date', '>=', $reportDate)
            ->whereIn('status', ['approved_pending_docs', 'on_leave'])
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        $reportData = [
            'date' => $reportDate->format('Y-m-d'),
            'present' => $attendances->map(function($att) {
                return [
                    'employee_name' => $att->user->name ?? 'Unknown',
                    'employee_id' => $att->user->employee_id ?? 'N/A',
                    'department' => $att->user->primaryDepartment->name ?? 'N/A',
                    'check_in' => $att->time_in ? Carbon::parse($att->time_in)->format('H:i') : 'N/A',
                    'check_out' => $att->time_out ? Carbon::parse($att->time_out)->format('H:i') : 'N/A',
                    'status' => $att->status,
                    'is_late' => $att->is_late,
                    'total_hours' => $att->total_hours ? round($att->total_hours / 60, 2) : 0,
                ];
            }),
            'absent' => $absentUsers->whereNotIn('id', $onLeaveUsers->pluck('id'))->map(function($user) {
                return [
                    'employee_name' => $user->name,
                    'employee_id' => $user->employee_id ?? 'N/A',
                    'department' => $user->primaryDepartment->name ?? 'N/A',
                ];
            }),
            'on_leave' => $onLeaveUsers->map(function($user) {
                $leave = LeaveRequest::where('employee_id', $user->id)
                    ->whereDate('start_date', '<=', Carbon::parse($date))
                    ->whereDate('end_date', '>=', Carbon::parse($date))
                    ->whereIn('status', ['approved_pending_docs', 'on_leave'])
                    ->with('leaveType')
                    ->first();
                return [
                    'employee_name' => $user->name,
                    'employee_id' => $user->employee_id ?? 'N/A',
                    'department' => $user->primaryDepartment->name ?? 'N/A',
                    'leave_type' => $leave->leaveType->name ?? 'N/A',
                ];
            }),
            'summary' => [
                'total_employees' => $allUsers->count(),
                'present' => $attendances->count(),
                'absent' => $absentUsers->whereNotIn('id', $onLeaveUsers->pluck('id'))->count(),
                'on_leave' => $onLeaveUsers->count(),
                'late' => $attendances->where('is_late', true)->count(),
            ],
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-daily', compact('reportData', 'reportDate'));
            $filename = 'Daily_Attendance_Report_' . $reportDate->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * 2. Monthly Attendance Summary
     */
    private function generateMonthlySummaryReport($dateFrom, $dateTo, $departmentId = null, $format = 'pdf')
    {
        $query = Attendance::with(['user', 'user.primaryDepartment'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $attendances = $query->get();
        $totalDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        
        $allUsersQuery = User::where('is_active', true)->whereHas('employee');
        if ($departmentId) {
            $allUsersQuery->where('primary_department_id', $departmentId);
        }
        $allUsers = $allUsersQuery->get();

        $reportData = $allUsers->map(function($user) use ($attendances, $totalDays, $dateFrom, $dateTo) {
            $userAttendances = $attendances->where('user_id', $user->id);
            $presentDays = $userAttendances->where('status', 'present')->count();
            $lateDays = $userAttendances->where('is_late', true)->count();
            
            // Get leave days
            $leaveDays = LeaveRequest::where('employee_id', $user->id)
                ->where(function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('start_date', [$dateFrom, $dateTo])
                      ->orWhereBetween('end_date', [$dateFrom, $dateTo])
                      ->orWhere(function($q2) use ($dateFrom, $dateTo) {
                          $q2->where('start_date', '<=', $dateFrom)
                             ->where('end_date', '>=', $dateTo);
                      });
                })
                ->whereIn('status', ['approved_pending_docs', 'on_leave', 'completed'])
                ->sum('total_days');

            $absentDays = max(0, $totalDays - $presentDays - $leaveDays);

            return [
                'employee_name' => $user->name,
                'employee_id' => $user->employee_id ?? 'N/A',
                'department' => $user->primaryDepartment->name ?? 'N/A',
                'total_working_days' => $totalDays,
                'days_present' => $presentDays,
                'days_absent' => $absentDays,
                'late_days' => $lateDays,
                'leave_days' => $leaveDays,
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
            ];
        })->sortByDesc('days_absent')->values();

        $summary = [
            'total_employees' => $allUsers->count(),
            'total_working_days' => $totalDays,
            'total_present_days' => $reportData->sum('days_present'),
            'total_absent_days' => $reportData->sum('days_absent'),
            'total_late_days' => $reportData->sum('late_days'),
            'total_leave_days' => $reportData->sum('leave_days'),
            'average_attendance_rate' => $reportData->avg('attendance_rate') ?? 0,
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-monthly', compact('reportData', 'summary', 'dateFrom', 'dateTo'));
            $filename = 'Monthly_Attendance_Summary_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => ['summary' => $summary, 'employees' => $reportData]]);
    }

    /**
     * 3. Late Coming Report (Enhanced version of timing report)
     */
    private function generateLateComingReport($dateFrom, $dateTo, $departmentId = null, $format = 'pdf')
    {
        // This uses the existing timing report logic but focuses only on late arrivals
        $query = Attendance::with(['user', 'user.primaryDepartment', 'workSchedule'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->where('is_late', true)
            ->whereNotNull('time_in');

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $lateRecords = [];
        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            $schedule = $attendance->workSchedule;
            
            $expectedStartTime = '09:00:00';
            if ($schedule && $schedule->start_time) {
                $expectedStartTime = Carbon::parse($schedule->start_time)->format('H:i:s');
            }

            $timeIn = $attendance->time_in;
            $actualTimeIn = $timeIn instanceof Carbon ? $timeIn->format('H:i:s') : Carbon::parse($timeIn)->format('H:i:s');

            $expectedStart = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $expectedStartTime);
            $actualStart = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $actualTimeIn);
            $minutesLate = $actualStart->diffInMinutes($expectedStart);

            $lateRecords[] = [
                'date' => $attendance->attendance_date->format('Y-m-d'),
                'employee_name' => $user->name ?? 'Unknown',
                'employee_id' => $user->employee_id ?? 'N/A',
                'department' => $user->primaryDepartment->name ?? 'N/A',
                'expected_time' => $expectedStartTime,
                'actual_time' => $actualTimeIn,
                'minutes_late' => $minutesLate,
            ];
        }

        // Group by employee
        $byEmployee = collect($lateRecords)->groupBy('employee_id')->map(function($records, $empId) {
            return [
                'employee_name' => $records->first()['employee_name'],
                'employee_id' => $empId,
                'department' => $records->first()['department'],
                'total_late_count' => $records->count(),
                'total_minutes_late' => $records->sum('minutes_late'),
                'average_minutes_late' => round($records->avg('minutes_late'), 1),
                'records' => $records->sortByDesc('date')->values(),
            ];
        })->sortByDesc('total_late_count')->values();

        $reportData = [
            'date_from' => Carbon::parse($dateFrom)->format('Y-m-d'),
            'date_to' => Carbon::parse($dateTo)->format('Y-m-d'),
            'total_late_occurrences' => count($lateRecords),
            'unique_employees' => $byEmployee->count(),
            'by_employee' => $byEmployee,
            'all_records' => collect($lateRecords)->sortByDesc('date')->values(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-late', compact('reportData', 'dateFrom', 'dateTo'));
            $filename = 'Late_Coming_Report_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * 4. Absenteeism Report
     */
    private function generateAbsenteeismReport($dateFrom, $dateTo, $departmentId = null, $format = 'pdf')
    {
        $totalDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        
        $query = Attendance::with(['user', 'user.primaryDepartment'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $attendances = $query->get();
        
        $allUsersQuery = User::where('is_active', true)->whereHas('employee');
        if ($departmentId) {
            $allUsersQuery->where('primary_department_id', $departmentId);
        }
        $allUsers = $allUsersQuery->get();

        $absenteeismData = [];
        foreach ($allUsers as $user) {
            $userAttendances = $attendances->where('user_id', $user->id);
            $presentDays = $userAttendances->where('status', 'present')->count();
            
            // Get leave days
            $leaveDays = LeaveRequest::where('employee_id', $user->id)
                ->where(function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('start_date', [$dateFrom, $dateTo])
                      ->orWhereBetween('end_date', [$dateFrom, $dateTo])
                      ->orWhere(function($q2) use ($dateFrom, $dateTo) {
                          $q2->where('start_date', '<=', $dateFrom)
                             ->where('end_date', '>=', $dateTo);
                      });
                })
                ->whereIn('status', ['approved_pending_docs', 'on_leave', 'completed'])
                ->get();

            $absentDays = max(0, $totalDays - $presentDays - $leaveDays->sum('total_days'));
            
            if ($absentDays > 0) {
                $absentDates = [];
                $currentDate = Carbon::parse($dateFrom);
                while ($currentDate <= Carbon::parse($dateTo)) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $hasAttendance = $userAttendances->where('attendance_date', $dateStr)->where('status', 'present')->count() > 0;
                    $onLeave = $leaveDays->filter(function($leave) use ($dateStr) {
                        return Carbon::parse($dateStr)->between($leave->start_date, $leave->end_date);
                    })->count() > 0;
                    
                    if (!$hasAttendance && !$onLeave) {
                        $absentDates[] = $dateStr;
                    }
                    $currentDate->addDay();
                }

                $absenteeismData[] = [
                    'employee_name' => $user->name,
                    'employee_id' => $user->employee_id ?? 'N/A',
                    'department' => $user->primaryDepartment->name ?? 'N/A',
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'absent_dates' => $absentDates,
                    'absenteeism_rate' => round(($absentDays / $totalDays) * 100, 2),
                    'reason' => 'Not provided', // Could be enhanced to check for absence requests
                ];
            }
        }

        $reportData = collect($absenteeismData)->sortByDesc('absent_days')->values();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-absenteeism', compact('reportData', 'dateFrom', 'dateTo', 'totalDays'));
            $filename = 'Absenteeism_Report_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * 5. Overtime Report
     */
    private function generateOvertimeReport($dateFrom, $dateTo, $departmentId = null, $format = 'pdf')
    {
        $query = Attendance::with(['user', 'user.primaryDepartment', 'workSchedule'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->where('is_overtime', true);

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $attendances = $query->get();

        $overtimeData = $attendances->map(function($att) {
            $normalHours = 8; // Default 8 hours
            $totalHours = $att->total_hours ? round($att->total_hours / 60, 2) : 0;
            $overtimeHours = max(0, $totalHours - $normalHours);

            return [
                'date' => $att->attendance_date->format('Y-m-d'),
                'employee_name' => $att->user->name ?? 'Unknown',
                'employee_id' => $att->user->employee_id ?? 'N/A',
                'department' => $att->user->primaryDepartment->name ?? 'N/A',
                'normal_hours' => $normalHours,
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'time_in' => $att->time_in ? Carbon::parse($att->time_in)->format('H:i') : 'N/A',
                'time_out' => $att->time_out ? Carbon::parse($att->time_out)->format('H:i') : 'N/A',
                'approval_status' => $att->verification_status ?? 'pending',
            ];
        });

        $byEmployee = $overtimeData->groupBy('employee_id')->map(function($records, $empId) {
            return [
                'employee_name' => $records->first()['employee_name'],
                'employee_id' => $empId,
                'department' => $records->first()['department'],
                'total_overtime_days' => $records->count(),
                'total_overtime_hours' => round($records->sum('overtime_hours'), 2),
                'average_overtime_hours' => round($records->avg('overtime_hours'), 2),
                'records' => $records->sortByDesc('date')->values(),
            ];
        })->sortByDesc('total_overtime_hours')->values();

        $summary = [
            'total_overtime_records' => $overtimeData->count(),
            'total_overtime_hours' => round($overtimeData->sum('overtime_hours'), 2),
            'unique_employees' => $byEmployee->count(),
            'average_overtime_per_employee' => $byEmployee->count() > 0 ? round($byEmployee->avg('total_overtime_hours'), 2) : 0,
        ];

        $reportData = [
            'date_from' => Carbon::parse($dateFrom)->format('Y-m-d'),
            'date_to' => Carbon::parse($dateTo)->format('Y-m-d'),
            'summary' => $summary,
            'by_employee' => $byEmployee,
            'all_records' => $overtimeData->sortByDesc('date')->values(),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-overtime', compact('reportData', 'dateFrom', 'dateTo'));
            $filename = 'Overtime_Report_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * 6. Leave Report
     */
    private function generateLeaveReport($dateFrom, $dateTo, $departmentId = null, $format = 'pdf')
    {
        $query = LeaveRequest::with(['user', 'leaveType', 'user.primaryDepartment'])
            ->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('start_date', [$dateFrom, $dateTo])
                  ->orWhereBetween('end_date', [$dateFrom, $dateTo])
                  ->orWhere(function($q2) use ($dateFrom, $dateTo) {
                      $q2->where('start_date', '<=', $dateFrom)
                         ->where('end_date', '>=', $dateTo);
                  });
            });

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $leaveRequests = $query->orderBy('start_date', 'desc')->get();

        $byLeaveType = $leaveRequests->groupBy('leave_type_id')->map(function($leaves, $typeId) {
            $type = $leaves->first()->leaveType;
            return [
                'leave_type' => $type->name ?? 'Unknown',
                'total_requests' => $leaves->count(),
                'total_days' => $leaves->sum('total_days'),
                'approved' => $leaves->whereIn('status', ['approved_pending_docs', 'on_leave', 'completed'])->count(),
                'pending' => $leaves->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval'])->count(),
                'rejected' => $leaves->whereIn('status', ['rejected', 'rejected_for_edit'])->count(),
            ];
        })->values();

        $byEmployee = $leaveRequests->groupBy('employee_id')->map(function($leaves, $empId) {
            $user = $leaves->first()->user;
            return [
                'employee_name' => $user->name ?? 'Unknown',
                'employee_id' => $user->employee_id ?? 'N/A',
                'department' => $user->primaryDepartment->name ?? 'N/A',
                'total_leave_days' => $leaves->sum('total_days'),
                'leave_requests' => $leaves->map(function($leave) {
                    return [
                        'leave_type' => $leave->leaveType->name ?? 'N/A',
                        'start_date' => $leave->start_date->format('Y-m-d'),
                        'end_date' => $leave->end_date->format('Y-m-d'),
                        'total_days' => $leave->total_days,
                        'status' => $leave->status,
                    ];
                })->values(),
            ];
        })->sortByDesc('total_leave_days')->values();

        // Get leave balances
        $leaveBalances = [];
        foreach ($leaveRequests->pluck('employee_id')->unique() as $empId) {
            $user = User::find($empId);
            if ($user) {
                $balances = $user->leaveBalances()->with('leaveType')->get();
                if ($balances->count() > 0) {
                    $leaveBalances[$empId] = $balances->map(function($balance) {
                        return [
                            'leave_type' => $balance->leaveType->name ?? 'N/A',
                            'balance' => $balance->remaining_days ?? 0,
                        ];
                    });
                }
            }
        }

        $summary = [
            'total_requests' => $leaveRequests->count(),
            'total_leave_days' => $leaveRequests->sum('total_days'),
            'approved' => $leaveRequests->whereIn('status', ['approved_pending_docs', 'on_leave', 'completed'])->count(),
            'pending' => $leaveRequests->whereIn('status', ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval'])->count(),
            'rejected' => $leaveRequests->whereIn('status', ['rejected', 'rejected_for_edit'])->count(),
        ];

        $reportData = [
            'date_from' => Carbon::parse($dateFrom)->format('Y-m-d'),
            'date_to' => Carbon::parse($dateTo)->format('Y-m-d'),
            'summary' => $summary,
            'by_leave_type' => $byLeaveType,
            'by_employee' => $byEmployee,
            'leave_balances' => $leaveBalances,
            'all_requests' => $leaveRequests->map(function($leave) {
                return [
                    'employee_name' => $leave->user->name ?? 'Unknown',
                    'employee_id' => $leave->user->employee_id ?? 'N/A',
                    'department' => $leave->user->primaryDepartment->name ?? 'N/A',
                    'leave_type' => $leave->leaveType->name ?? 'N/A',
                    'start_date' => $leave->start_date->format('Y-m-d'),
                    'end_date' => $leave->end_date->format('Y-m-d'),
                    'total_days' => $leave->total_days,
                    'status' => $leave->status,
                ];
            }),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-leave', compact('reportData', 'dateFrom', 'dateTo'));
            $filename = 'Leave_Report_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * 7. Department Attendance Report
     */
    private function generateDepartmentReport($dateFrom, $dateTo, $format = 'pdf')
    {
        $totalDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        
        $departments = Department::where('is_active', true)->with('primaryUsers')->get();
        
        $attendances = Attendance::with(['user', 'user.primaryDepartment'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->get();

        $departmentData = $departments->map(function($dept) use ($attendances, $totalDays) {
            $deptUsers = $dept->primaryUsers->where('is_active', true);
            $totalStaff = $deptUsers->count();
            
            if ($totalStaff == 0) {
                return null;
            }

            $deptAttendances = $attendances->filter(function($att) use ($dept) {
                return $att->user->primary_department_id == $dept->id;
            });

            $presentDays = $deptAttendances->where('status', 'present')->count();
            $expectedDays = $totalDays * $totalStaff;
            $attendanceRate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 2) : 0;
            
            $absentDays = max(0, $expectedDays - $presentDays);
            $absenteeRate = $expectedDays > 0 ? round(($absentDays / $expectedDays) * 100, 2) : 0;

            $lateCount = $deptAttendances->where('is_late', true)->count();
            $earlyLeaveCount = $deptAttendances->where('is_early_leave', true)->count();

            return [
                'department_name' => $dept->name,
                'department_code' => $dept->code ?? 'N/A',
                'total_staff' => $totalStaff,
                'total_working_days' => $totalDays,
                'expected_attendance_days' => $expectedDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'attendance_percentage' => $attendanceRate,
                'absentee_rate' => $absenteeRate,
                'late_count' => $lateCount,
                'early_leave_count' => $earlyLeaveCount,
            ];
        })->filter()->sortByDesc('absentee_rate')->values();

        $summary = [
            'total_departments' => $departmentData->count(),
            'total_staff' => $departmentData->sum('total_staff'),
            'average_attendance_rate' => round($departmentData->avg('attendance_percentage'), 2),
            'average_absentee_rate' => round($departmentData->avg('absentee_rate'), 2),
        ];

        $reportData = [
            'date_from' => Carbon::parse($dateFrom)->format('Y-m-d'),
            'date_to' => Carbon::parse($dateTo)->format('Y-m-d'),
            'summary' => $summary,
            'departments' => $departmentData,
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-department', compact('reportData', 'dateFrom', 'dateTo'));
            $filename = 'Department_Attendance_Report_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }

    /**
     * 8. Attendance Exception Report
     */
    private function generateExceptionReport($dateFrom, $dateTo, $departmentId = null, $format = 'pdf')
    {
        $query = Attendance::with(['user', 'user.primaryDepartment'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        $attendances = $query->get();

        $exceptions = [
            'missing_check_ins' => [],
            'missing_check_outs' => [],
            'duplicate_entries' => [],
            'unauthorized_absences' => [],
        ];

        foreach ($attendances as $attendance) {
            // Missing check-in
            if (!$attendance->time_in && $attendance->status !== 'on_leave') {
                $exceptions['missing_check_ins'][] = [
                    'date' => $attendance->attendance_date->format('Y-m-d'),
                    'employee_name' => $attendance->user->name ?? 'Unknown',
                    'employee_id' => $attendance->user->employee_id ?? 'N/A',
                    'department' => $attendance->user->primaryDepartment->name ?? 'N/A',
                    'has_check_out' => !is_null($attendance->time_out),
                ];
            }

            // Missing check-out
            if ($attendance->time_in && !$attendance->time_out && $attendance->status !== 'on_leave') {
                $exceptions['missing_check_outs'][] = [
                    'date' => $attendance->attendance_date->format('Y-m-d'),
                    'employee_name' => $attendance->user->name ?? 'Unknown',
                    'employee_id' => $attendance->user->employee_id ?? 'N/A',
                    'department' => $attendance->user->primaryDepartment->name ?? 'N/A',
                    'check_in_time' => $attendance->time_in ? Carbon::parse($attendance->time_in)->format('H:i') : 'N/A',
                ];
            }
        }

        // Check for duplicate entries (same user, same date, multiple records)
        $duplicates = $attendances->groupBy(function($att) {
            return $att->user_id . '_' . $att->attendance_date->format('Y-m-d');
        })->filter(function($group) {
            return $group->count() > 1;
        })->map(function($group) {
            $first = $group->first();
            return [
                'date' => $first->attendance_date->format('Y-m-d'),
                'employee_name' => $first->user->name ?? 'Unknown',
                'employee_id' => $first->user->employee_id ?? 'N/A',
                'department' => $first->user->primaryDepartment->name ?? 'N/A',
                'duplicate_count' => $group->count(),
                'records' => $group->map(function($att) {
                    return [
                        'time_in' => $att->time_in ? Carbon::parse($att->time_in)->format('H:i') : 'N/A',
                        'time_out' => $att->time_out ? Carbon::parse($att->time_out)->format('H:i') : 'N/A',
                        'device' => $att->device_ip ?? 'N/A',
                    ];
                })->values(),
            ];
        })->values();

        $exceptions['duplicate_entries'] = $duplicates;

        // Unauthorized absences (absent without leave)
        $allUsersQuery = User::where('is_active', true)->whereHas('employee');
        if ($departmentId) {
            $allUsersQuery->where('primary_department_id', $departmentId);
        }
        $allUsers = $allUsersQuery->get();

        $presentUserIds = $attendances->where('status', 'present')->pluck('user_id')->unique();
        $onLeaveUserIds = LeaveRequest::whereBetween('start_date', [$dateFrom, $dateTo])
            ->orWhereBetween('end_date', [$dateFrom, $dateTo])
            ->whereIn('status', ['approved_pending_docs', 'on_leave'])
            ->pluck('employee_id')
            ->unique();

        $unauthorizedAbsences = [];
        foreach ($allUsers as $user) {
            $userAttendances = $attendances->where('user_id', $user->id);
            $currentDate = Carbon::parse($dateFrom);
            
            while ($currentDate <= Carbon::parse($dateTo)) {
                $dateStr = $currentDate->format('Y-m-d');
                $hasAttendance = $userAttendances->where('attendance_date', $dateStr)->where('status', 'present')->count() > 0;
                $onLeave = $onLeaveUserIds->contains($user->id) && 
                    LeaveRequest::where('employee_id', $user->id)
                        ->whereDate('start_date', '<=', $dateStr)
                        ->whereDate('end_date', '>=', $dateStr)
                        ->whereIn('status', ['approved_pending_docs', 'on_leave'])
                        ->exists();
                
                if (!$hasAttendance && !$onLeave && !$currentDate->isWeekend()) {
                    $unauthorizedAbsences[] = [
                        'date' => $dateStr,
                        'employee_name' => $user->name,
                        'employee_id' => $user->employee_id ?? 'N/A',
                        'department' => $user->primaryDepartment->name ?? 'N/A',
                    ];
                }
                $currentDate->addDay();
            }
        }

        $exceptions['unauthorized_absences'] = collect($unauthorizedAbsences)->groupBy('employee_id')->map(function($absences, $empId) {
            return [
                'employee_name' => $absences->first()['employee_name'],
                'employee_id' => $empId,
                'department' => $absences->first()['department'],
                'absence_count' => $absences->count(),
                'absence_dates' => $absences->pluck('date')->sort()->values(),
            ];
        })->sortByDesc('absence_count')->values();

        $summary = [
            'missing_check_ins' => count($exceptions['missing_check_ins']),
            'missing_check_outs' => count($exceptions['missing_check_outs']),
            'duplicate_entries' => count($exceptions['duplicate_entries']),
            'unauthorized_absences' => count($exceptions['unauthorized_absences']),
        ];

        $reportData = [
            'date_from' => Carbon::parse($dateFrom)->format('Y-m-d'),
            'date_to' => Carbon::parse($dateTo)->format('Y-m-d'),
            'summary' => $summary,
            'exceptions' => $exceptions,
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.attendance-exception', compact('reportData', 'dateFrom', 'dateTo'));
            $filename = 'Attendance_Exception_Report_' . Carbon::parse($dateFrom)->format('Y-m-d') . '_to_' . Carbon::parse($dateTo)->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json(['success' => true, 'data' => $reportData]);
    }
}
