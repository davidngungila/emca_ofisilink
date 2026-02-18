<?php

namespace App\Http\Controllers;

use App\Services\FinancialYearService;
use App\Models\OrganizationSetting;
use App\Models\MainTask;
use App\Models\Assessment;
use App\Models\AssessmentProgressReport;
use App\Models\ActivityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialYearController extends Controller
{
    protected $fyService;

    public function __construct(FinancialYearService $fyService)
    {
        $this->fyService = $fyService;
    }

    /**
     * Show financial year management page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'CEO'])) {
            abort(403, 'Unauthorized access');
        }

        $orgSettings = OrganizationSetting::getSettings();
        $currentFY = $orgSettings->current_financial_year ?? date('Y');
        $currentFYDates = $this->fyService->getFinancialYearDates($currentFY);
        
        // Get previous financial year
        $previousFY = $currentFY - 1;
        $previousFYDates = $this->fyService->getFinancialYearDates($previousFY);

        // Get statistics for current FY
        $currentFYStats = $this->fyService->getPerformanceDataForFinancialYear($currentFY);
        
        // Get statistics for previous FY
        $previousFYStats = $this->fyService->getPerformanceDataForFinancialYear($previousFY);

        // Get incomplete tasks that may need to be carried forward
        $incompleteTasks = MainTask::where('financial_year', $currentFY)
            ->whereIn('status', ['planning', 'in_progress'])
            ->with(['organizationalGoal', 'assessment'])
            ->get();

        // Get quarterly breakdown
        $quarterlyData = $this->getQuarterlyBreakdown($currentFY);

        return view('admin.financial-year-management', compact(
            'currentFY',
            'currentFYDates',
            'previousFY',
            'previousFYDates',
            'currentFYStats',
            'previousFYStats',
            'incompleteTasks',
            'quarterlyData'
        ));
    }

    /**
     * Close current financial year
     */
    public function closeFinancialYear(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['System Admin', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only System Admin or CEO can close financial year.'
            ], 403);
        }

        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'carry_forward_tasks' => 'nullable|array',
            'carry_forward_tasks.*' => 'integer|exists:main_tasks,id',
        ]);

        $year = $request->integer('year');
        $orgSettings = OrganizationSetting::getSettings();
        $currentFY = $orgSettings->current_financial_year;

        if ($year != $currentFY) {
            return response()->json([
                'success' => false,
                'message' => "Can only close the current financial year ({$currentFY})"
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Lock all performance objectives for this FY
            // (This would need to be implemented based on your objectives structure)
            
            // 2. Archive completed tasks
            $archivedTasks = MainTask::where('financial_year', $year)
                ->where('status', 'completed')
                ->update(['status' => 'archived']);

            // 3. Handle incomplete tasks
            $carryForwardIds = $request->input('carry_forward_tasks', []);
            $incompleteTasks = MainTask::where('financial_year', $year)
                ->whereIn('status', ['planning', 'in_progress'])
                ->get();

            $carriedForward = 0;
            $closed = 0;

            foreach ($incompleteTasks as $task) {
                if (in_array($task->id, $carryForwardIds)) {
                    // Mark for carry forward - will be linked to new FY objectives
                    $task->update([
                        'status' => 'carried_forward',
                        'carried_forward_from_fy' => $year,
                    ]);
                    $carriedForward++;
                } else {
                    // Close the task
                    $task->update(['status' => 'closed']);
                    $closed++;
                }
            }

            // 4. Finalize performance scores
            // Generate final performance reports
            $this->generateFinalPerformanceReports($year);

            // 5. Update organization settings
            $orgSettings->update([
                'financial_year_locked' => true,
                'financial_year_history' => array_merge(
                    $orgSettings->financial_year_history ?? [],
                    [[
                        'year' => $year,
                        'closed_at' => now()->toDateTimeString(),
                        'closed_by' => $user->id,
                        'archived_tasks' => $archivedTasks,
                        'carried_forward_tasks' => $carriedForward,
                        'closed_tasks' => $closed,
                    ]]
                )
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Financial Year {$year} closed successfully",
                'data' => [
                    'archived_tasks' => $archivedTasks,
                    'carried_forward_tasks' => $carriedForward,
                    'closed_tasks' => $closed,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to close financial year: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to close financial year: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize new financial year
     */
    public function initializeFinancialYear(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['System Admin', 'CEO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only System Admin or CEO can initialize financial year.'
            ], 403);
        }

        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = $request->integer('year');
        $orgSettings = OrganizationSetting::getSettings();
        $currentFY = $orgSettings->current_financial_year;

        // Check if previous year is closed
        if (!$orgSettings->financial_year_locked && $year > $currentFY) {
            return response()->json([
                'success' => false,
                'message' => "Please close Financial Year {$currentFY} before initializing a new year."
            ], 400);
        }

        $result = $this->fyService->initializeFinancialYear($year);

        if ($result['success']) {
            // Unlock financial year
            $orgSettings->update(['financial_year_locked' => false]);
        }

        return response()->json($result);
    }

    /**
     * Get quarterly breakdown for a financial year
     */
    private function getQuarterlyBreakdown($year)
    {
        $quarters = [];
        
        for ($q = 1; $q <= 4; $q++) {
            $quarterData = $this->fyService->getQuarterlyPeriod(
                Carbon::create($year, 1, 1)->addMonths(($q - 1) * 3)
            );
            
            $quarterStart = $quarterData['start'];
            $quarterEnd = $quarterData['end'];

            $quarters[$q] = [
                'quarter' => $q,
                'label' => $quarterData['label'],
                'start' => $quarterStart,
                'end' => $quarterEnd,
                'tasks' => MainTask::where(function($query) use ($quarterStart, $quarterEnd) {
                    $query->whereBetween('start_date', [$quarterStart, $quarterEnd])
                          ->orWhereBetween('end_date', [$quarterStart, $quarterEnd]);
                })->count(),
                'completed_tasks' => MainTask::where(function($query) use ($quarterStart, $quarterEnd) {
                    $query->whereBetween('start_date', [$quarterStart, $quarterEnd])
                          ->orWhereBetween('end_date', [$quarterStart, $quarterEnd]);
                })->where('status', 'completed')->count(),
                'reports' => ActivityReport::whereBetween('report_date', [$quarterStart, $quarterEnd])->count(),
                'assessments' => Assessment::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
            ];
        }

        return $quarters;
    }

    /**
     * Generate final performance reports for a financial year
     */
    private function generateFinalPerformanceReports($year)
    {
        // This would generate comprehensive performance reports
        // Implementation depends on your reporting structure
        \Log::info("Generating final performance reports for FY {$year}");
        
        // You can add PDF generation, data export, etc. here
        return true;
    }

    /**
     * Get financial year comparison data
     */
    public function getComparison(Request $request)
    {
        $year1 = $request->integer('year1', date('Y') - 1);
        $year2 = $request->integer('year2', date('Y'));

        $data1 = $this->fyService->getPerformanceDataForFinancialYear($year1);
        $data2 = $this->fyService->getPerformanceDataForFinancialYear($year2);

        return response()->json([
            'success' => true,
            'comparison' => [
                'year1' => [
                    'year' => $year1,
                    'data' => $data1,
                ],
                'year2' => [
                    'year' => $year2,
                    'data' => $data2,
                ],
                'changes' => [
                    'tasks' => $data2['tasks'] - $data1['tasks'],
                    'reports' => $data2['reports'] - $data1['reports'],
                    'assessments' => $data2['assessments'] - $data1['assessments'],
                    'linked_tasks' => $data2['linked_tasks'] - $data1['linked_tasks'],
                ]
            ]
        ]);
    }
}





