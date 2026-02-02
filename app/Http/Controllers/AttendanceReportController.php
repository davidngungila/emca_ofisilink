<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
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
}
