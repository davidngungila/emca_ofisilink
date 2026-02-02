<?php

namespace App\Http\Controllers;

use App\Models\PerformanceCriteria;
use App\Models\PerformanceMeasurement;
use App\Models\PerformanceIssue;
use App\Models\Assessment;
use App\Models\User;
use App\Models\Department;
use App\Services\TaskPerformanceSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PerformanceManagementController extends Controller
{
    protected $syncService;

    public function __construct()
    {
        $this->syncService = new TaskPerformanceSyncService();
    }

    /**
     * Display performance management dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $canManage = $user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD']);
        
        return view('modules.hr.performance-management', compact('canManage'));
    }

    /**
     * Handle AJAX requests
     */
    public function handleRequest(Request $request)
    {
        $action = $request->input('action');
        $user = Auth::user();

        try {
            DB::beginTransaction();

            switch ($action) {
                case 'get_criteria':
                    return $this->getCriteria($request, $user);
                case 'create_criteria':
                    return $this->createCriteria($request, $user);
                case 'update_criteria':
                    return $this->updateCriteria($request, $user);
                case 'calculate_organizational_performance':
                    return $this->calculateOrganizationalPerformance($request, $user);
                case 'get_measurements':
                    return $this->getMeasurements($request, $user);
                case 'create_issue':
                    return $this->createIssue($request, $user);
                case 'get_issues':
                    return $this->getIssues($request, $user);
                case 'update_issue':
                    return $this->updateIssue($request, $user);
                case 'sync_tasks':
                    return $this->syncTasks($request, $user);
                case 'get_performance_report':
                    return $this->getPerformanceReport($request, $user);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action requested.'
                    ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance criteria
     */
    private function getCriteria(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = PerformanceCriteria::with(['creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('active_only')) {
            $query->where('status', 'active');
        }

        $criteria = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'criteria' => $criteria
        ]);
    }

    /**
     * Create performance criteria (VIPAUmbele)
     */
    private function createCriteria(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:performance_criterias,code',
            'description' => 'nullable|string',
            'criteria' => 'required|array',
            'weighting' => 'nullable|array',
            'scoring_rules' => 'nullable|array',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // If setting as default, unset other defaults
        if ($request->is_default) {
            PerformanceCriteria::where('is_default', true)->update(['is_default' => false]);
        }

        $criteria = PerformanceCriteria::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'criteria' => $request->criteria,
            'weighting' => $request->weighting ?? [],
            'scoring_rules' => $request->scoring_rules ?? [],
            'status' => $request->status ?? 'active',
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'is_default' => $request->is_default ?? false,
            'created_by' => $user->id,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Performance criteria created successfully.',
            'criteria' => $criteria
        ]);
    }

    /**
     * Update performance criteria
     */
    private function updateCriteria(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $criteria = PerformanceCriteria::find($request->criteria_id);
        if (!$criteria) {
            return response()->json([
                'success' => false,
                'message' => 'Criteria not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:performance_criterias,code,' . $criteria->id,
            'description' => 'nullable|string',
            'criteria' => 'sometimes|required|array',
            'weighting' => 'nullable|array',
            'scoring_rules' => 'nullable|array',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // If setting as default, unset other defaults
        if ($request->is_default && !$criteria->is_default) {
            PerformanceCriteria::where('is_default', true)->update(['is_default' => false]);
        }

        $criteria->update(array_merge(
            $request->only([
                'name', 'code', 'description', 'criteria', 'weighting',
                'scoring_rules', 'status', 'effective_from', 'effective_to', 'is_default'
            ]),
            ['updated_by' => $user->id]
        ));

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Performance criteria updated successfully.',
            'criteria' => $criteria
        ]);
    }

    /**
     * Calculate organizational performance
     */
    private function calculateOrganizationalPerformance(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'period_type' => 'required|in:monthly,quarterly,semi_annual,annual,custom',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'measurement_type' => 'required|in:individual,department,organization',
            'performance_criteria_id' => 'nullable|exists:performance_criterias,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);
        $periodType = $request->period_type;
        $measurementType = $request->measurement_type;

        // Get default criteria if not specified
        $criteriaId = $request->performance_criteria_id;
        if (!$criteriaId) {
            $defaultCriteria = PerformanceCriteria::where('is_default', true)->first();
            $criteriaId = $defaultCriteria ? $defaultCriteria->id : null;
        }

        // Calculate performance based on type
        if ($measurementType === 'organization') {
            $result = $this->calculateOrganizationPerformance($periodStart, $periodEnd, $criteriaId);
        } elseif ($measurementType === 'department') {
            $result = $this->calculateDepartmentPerformance(
                $request->department_id,
                $periodStart,
                $periodEnd,
                $criteriaId
            );
        } else {
            $result = $this->calculateIndividualPerformance(
                $request->user_id,
                $periodStart,
                $periodEnd,
                $criteriaId
            );
        }

        // Create or update measurement record
        $measurement = PerformanceMeasurement::updateOrCreate(
            [
                'measurement_type' => $measurementType,
                'period_type' => $periodType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'user_id' => $request->user_id,
                'department_id' => $request->department_id,
            ],
            [
                'year' => $periodStart->year,
                'month' => $periodType === 'monthly' ? $periodStart->month : null,
                'quarter' => $periodType === 'quarterly' ? ceil($periodStart->month / 3) : null,
                'performance_criteria_id' => $criteriaId,
                'overall_score' => $result['overall_score'],
                'scores_by_criteria' => $result['scores_by_criteria'],
                'metrics' => $result['metrics'],
                'summary' => $result['summary'],
                'status' => 'draft',
                'created_by' => $user->id,
            ]
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Performance calculated successfully.',
            'measurement' => $measurement,
            'result' => $result
        ]);
    }

    /**
     * Calculate organization-wide performance
     */
    private function calculateOrganizationPerformance($periodStart, $periodEnd, $criteriaId = null)
    {
        // Get all approved assessments in the period
        $assessments = Assessment::where('status', 'approved')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with(['activities', 'activities.progressReports' => function($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('report_date', [$periodStart, $periodEnd])
                  ->where('status', 'approved');
            }])
            ->get();

        $totalScore = 0;
        $count = 0;
        $scoresByCriteria = [];
        $metrics = [
            'total_employees' => $assessments->count(),
            'total_assessments' => $assessments->count(),
            'total_activities' => 0,
            'completed_activities' => 0,
        ];

        foreach ($assessments as $assessment) {
            $assessmentScore = $this->calculateAssessmentScore($assessment, $periodStart, $periodEnd);
            $totalScore += $assessmentScore;
            $count++;

            $metrics['total_activities'] += $assessment->activities->count();
            $metrics['completed_activities'] += $assessment->activities->where('status', 'completed')->count();
        }

        $overallScore = $count > 0 ? round($totalScore / $count, 2) : 0;

        return [
            'overall_score' => $overallScore,
            'scores_by_criteria' => $scoresByCriteria,
            'metrics' => $metrics,
            'summary' => "Organization performance: {$overallScore}% based on {$count} assessments"
        ];
    }

    /**
     * Calculate department performance
     */
    private function calculateDepartmentPerformance($departmentId, $periodStart, $periodEnd, $criteriaId = null)
    {
        $assessments = Assessment::where('status', 'approved')
            ->whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with(['activities', 'activities.progressReports'])
            ->get();

        return $this->calculatePerformanceFromAssessments($assessments, $periodStart, $periodEnd);
    }

    /**
     * Calculate individual performance
     */
    private function calculateIndividualPerformance($userId, $periodStart, $periodEnd, $criteriaId = null)
    {
        $assessments = Assessment::where('employee_id', $userId)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with(['activities', 'activities.progressReports'])
            ->get();

        return $this->calculatePerformanceFromAssessments($assessments, $periodStart, $periodEnd);
    }

    /**
     * Calculate performance from assessments
     */
    private function calculatePerformanceFromAssessments($assessments, $periodStart, $periodEnd)
    {
        $totalScore = 0;
        $count = 0;
        $metrics = [
            'total_assessments' => $assessments->count(),
            'total_activities' => 0,
            'completed_activities' => 0,
        ];

        foreach ($assessments as $assessment) {
            $score = $this->calculateAssessmentScore($assessment, $periodStart, $periodEnd);
            $totalScore += $score;
            $count++;

            $metrics['total_activities'] += $assessment->activities->count();
        }

        $overallScore = $count > 0 ? round($totalScore / $count, 2) : 0;

        return [
            'overall_score' => $overallScore,
            'scores_by_criteria' => [],
            'metrics' => $metrics,
            'summary' => "Performance score: {$overallScore}%"
        ];
    }

    /**
     * Calculate assessment score
     */
    private function calculateAssessmentScore($assessment, $periodStart, $periodEnd)
    {
        $totalScore = 0;

        foreach ($assessment->activities as $activity) {
            $reports = $activity->progressReports()
                ->whereBetween('report_date', [$periodStart, $periodEnd])
                ->where('status', 'approved')
                ->get();

            if ($reports->isNotEmpty()) {
                $avgScore = $reports->avg('performance_score') ?? 0;
                $contribution = $activity->contribution_percentage ?? 0;
                $totalScore += ($avgScore * $contribution / 100);
            }
        }

        return round($totalScore, 2);
    }

    /**
     * Get performance measurements
     */
    private function getMeasurements(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = PerformanceMeasurement::with(['user', 'department', 'performanceCriteria']);

        if ($request->filled('measurement_type')) {
            $query->where('measurement_type', $request->measurement_type);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $measurements = $query->orderBy('period_start', 'desc')->get();

        return response()->json([
            'success' => true,
            'measurements' => $measurements
        ]);
    }

    /**
     * Create performance issue
     */
    private function createIssue(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'issue_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'assessment_id' => 'nullable|exists:assessments,id',
            'performance_measurement_id' => 'nullable|exists:performance_measurements,id',
            'target_resolution_date' => 'nullable|date',
            'action_plan' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $issue = PerformanceIssue::create([
            'issue_type' => $request->issue_type,
            'title' => $request->title,
            'description' => $request->description,
            'severity' => $request->severity,
            'user_id' => $request->user_id,
            'department_id' => $request->department_id,
            'assessment_id' => $request->assessment_id,
            'performance_measurement_id' => $request->performance_measurement_id,
            'identified_date' => now(),
            'target_resolution_date' => $request->target_resolution_date,
            'action_plan' => $request->action_plan ?? [],
            'status' => 'open',
            'identified_by' => $user->id,
            'assigned_to' => $request->assigned_to ?? $user->id,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Performance issue created successfully.',
            'issue' => $issue
        ]);
    }

    /**
     * Get performance issues
     */
    private function getIssues(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = PerformanceIssue::with(['user', 'department', 'assessment', 'assignedTo', 'identifiedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $issues = $query->orderBy('identified_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'issues' => $issues
        ]);
    }

    /**
     * Update performance issue
     */
    private function updateIssue(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $issue = PerformanceIssue::find($request->issue_id);
        if (!$issue) {
            return response()->json([
                'success' => false,
                'message' => 'Issue not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
            'action_plan' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $updateData = $request->only(['status', 'resolution_notes', 'action_plan', 'assigned_to']);

        if ($request->status === 'resolved' && !$issue->resolved_date) {
            $updateData['resolved_date'] = now();
        }

        $issue->update($updateData);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Performance issue updated successfully.',
            'issue' => $issue
        ]);
    }

    /**
     * Sync tasks to performance
     */
    private function syncTasks(Request $request, $user)
    {
        $userId = $request->user_id ?? $user->id;

        // Authorization: User can sync own tasks, but only HR/Admin can sync others
        if ($userId != $user->id && !$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $period = $request->filled('period_start') && $request->filled('period_end') ? [
            'start' => $request->period_start,
            'end' => $request->period_end,
        ] : null;

        $synced = $this->syncService->syncUserTasks($userId, $period);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Synced {$synced} task(s) to performance.",
            'synced_count' => $synced
        ]);
    }

    /**
     * Get performance report
     */
    private function getPerformanceReport(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'measurement_id' => 'required|exists:performance_measurements,id',
            'format' => 'nullable|in:json,pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $measurement = PerformanceMeasurement::with([
            'user', 'department', 'performanceCriteria', 'issues'
        ])->findOrFail($request->measurement_id);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('modules.hr.pdf.performance-report', compact('measurement'));
            $filename = 'Performance_Report_' . $measurement->id . '.pdf';
            return $pdf->download($filename);
        }

        return response()->json([
            'success' => true,
            'measurement' => $measurement
        ]);
    }
}
