<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialYearService
{
    protected $orgSettings;

    public function __construct()
    {
        $this->orgSettings = OrganizationSetting::getSettings();
    }

    /**
     * Get current financial year
     */
    public function getCurrentFinancialYear(): int
    {
        return $this->orgSettings->current_financial_year ?? date('Y');
    }

    /**
     * Get financial year dates
     */
    public function getFinancialYearDates($year = null)
    {
        return $this->orgSettings->getFinancialYearDates($year);
    }

    /**
     * Get financial year for a given date
     */
    public function getFinancialYearForDate($date)
    {
        return $this->orgSettings->getFinancialYearForDate($date);
    }

    /**
     * Check if date is in current financial year
     */
    public function isInCurrentFinancialYear($date): bool
    {
        return $this->orgSettings->isInCurrentFinancialYear($date);
    }

    /**
     * Validate task creation against financial year boundaries
     */
    public function validateTaskCreation($startDate, $endDate = null, $linkToObjective = null)
    {
        $currentFY = $this->getCurrentFinancialYear();
        $currentFYDates = $this->getFinancialYearDates($currentFY);
        
        $start = Carbon::parse($startDate);
        $end = $endDate ? Carbon::parse($endDate) : $start;
        
        $startFY = $this->getFinancialYearForDate($start);
        $endFY = $this->getFinancialYearForDate($end);

        // Check if linking to past FY objective
        if ($linkToObjective) {
            // This would need to check the objective's financial year
            // For now, we'll assume objectives are checked separately
        }

        // Allow tasks in current or future FY
        if ($startFY < $currentFY) {
            return [
                'valid' => false,
                'message' => "Cannot create tasks for past financial years. Task start date falls in FY {$startFY}, but current FY is {$currentFY}."
            ];
        }

        // Warn if task spans multiple FYs
        if ($startFY !== $endFY) {
            return [
                'valid' => true,
                'warning' => "Task spans multiple financial years (FY {$startFY} to FY {$endFY}). Progress will be tracked per FY.",
                'financial_year' => $startFY // Use start FY as primary
            ];
        }

        return [
            'valid' => true,
            'financial_year' => $startFY
        ];
    }

    /**
     * Get quarterly period for a date
     */
    public function getQuarterlyPeriod($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $fyDates = $this->getFinancialYearDates();
        
        $quarter = 1;
        $monthsSinceStart = $date->diffInMonths($fyDates['start']);
        
        if ($monthsSinceStart >= 9) {
            $quarter = 4;
        } elseif ($monthsSinceStart >= 6) {
            $quarter = 3;
        } elseif ($monthsSinceStart >= 3) {
            $quarter = 2;
        }

        $quarterStart = $fyDates['start']->copy()->addMonths(($quarter - 1) * 3);
        $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay();

        return [
            'quarter' => $quarter,
            'start' => $quarterStart,
            'end' => $quarterEnd,
            'label' => "Q{$quarter} " . $fyDates['year']
        ];
    }

    /**
     * Close financial year and archive data
     */
    public function closeFinancialYear($year)
    {
        try {
            DB::beginTransaction();

            // 1. Lock all performance objectives for this FY
            // This would need to be implemented based on your objectives structure
            
            // 2. Archive completed tasks
            $completedTasks = \App\Models\MainTask::where('financial_year', $year)
                ->where('status', 'completed')
                ->update(['status' => 'archived']);

            // 3. Mark incomplete tasks as "Carried Forward"
            $incompleteTasks = \App\Models\MainTask::where('financial_year', $year)
                ->whereIn('status', ['planning', 'in_progress'])
                ->get();

            foreach ($incompleteTasks as $task) {
                // Create a new task entry for next FY or mark as carried forward
                // This depends on your business logic
            }

            // 4. Finalize performance scores
            // Generate final performance reports for the year

            // 5. Archive daily reports (keep for historical reference)
            // Reports are already tagged with financial_year, so they're preserved

            DB::commit();

            return [
                'success' => true,
                'message' => "Financial Year {$year} closed successfully",
                'archived_tasks' => $completedTasks,
                'carried_forward_tasks' => $incompleteTasks->count()
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to close financial year: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to close financial year: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initialize new financial year
     */
    public function initializeFinancialYear($year)
    {
        try {
            $dates = $this->orgSettings->getFinancialYearDates($year);
            
            $this->orgSettings->update([
                'current_financial_year' => $year,
                'financial_year_start_date' => $dates['start'],
                'financial_year_end_date' => $dates['end'],
            ]);

            return [
                'success' => true,
                'message' => "Financial Year {$year} initialized successfully",
                'dates' => [
                    'start' => $dates['start']->format('Y-m-d'),
                    'end' => $dates['end']->format('Y-m-d')
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to initialize financial year: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to initialize financial year: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get tasks for a specific financial year
     */
    public function getTasksForFinancialYear($year = null)
    {
        $year = $year ?? $this->getCurrentFinancialYear();
        
        return \App\Models\MainTask::where('financial_year', $year)
            ->orWhere(function($q) use ($year) {
                $fyDates = $this->getFinancialYearDates($year);
                $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
                  ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
            })
            ->get();
    }

    /**
     * Get performance data for a financial year
     */
    public function getPerformanceDataForFinancialYear($year = null)
    {
        $year = $year ?? $this->getCurrentFinancialYear();
        $fyDates = $this->getFinancialYearDates($year);

        return [
            'assessments' => \App\Models\Assessment::whereBetween('created_at', [$fyDates['start'], $fyDates['end']])->count(),
            'tasks' => $this->getTasksForFinancialYear($year)->count(),
            'reports' => \App\Models\ActivityReport::whereBetween('report_date', [$fyDates['start'], $fyDates['end']])->count(),
            'linked_tasks' => \App\Models\MainTask::where('financial_year', $year)
                ->where(function($q) {
                    $q->whereNotNull('organizational_goal_id')
                      ->orWhereNotNull('assessment_id')
                      ->orWhere('link_type', '!=', 'none');
                })
                ->count(),
        ];
    }
}





