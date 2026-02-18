<?php

namespace App\Services;

use App\Models\TaskActivity;
use App\Models\AssessmentActivity;
use App\Models\Assessment;
use App\Models\AssessmentProgressReport;
use App\Models\TaskPerformanceSync;
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskPerformanceSyncService
{
    /**
     * Sync task activity completion to performance assessment
     */
    public function syncTaskToPerformance(TaskActivity $taskActivity, $userId = null)
    {
        try {
            DB::beginTransaction();

            // Get current financial year
            $orgSettings = OrganizationSetting::getSettings();
            $currentFY = $orgSettings->current_financial_year ?? date('Y');
            
            // Check if task activity is in current financial year
            $taskFY = $taskActivity->financial_year ?? $orgSettings->getFinancialYearForDate($taskActivity->start_date ?? now());
            if ($taskFY != $currentFY) {
                DB::rollBack();
                return false; // Only sync tasks from current financial year
            }

            // Find assessment activities linked to this task activity
            $assessmentActivities = AssessmentActivity::where('task_activity_id', $taskActivity->id)->get();
            
            // Also check task_performance_links table for many-to-many relationships
            $linkedActivities = DB::table('task_performance_links')
                ->where('task_activity_id', $taskActivity->id)
                ->where('financial_year', $currentFY)
                ->where('is_active', true)
                ->pluck('assessment_activity_id');
            
            if ($linkedActivities->isNotEmpty()) {
                $additionalActivities = AssessmentActivity::whereIn('id', $linkedActivities)->get();
                $assessmentActivities = $assessmentActivities->merge($additionalActivities);
            }

            if ($assessmentActivities->isEmpty()) {
                DB::rollBack();
                return false;
            }

            foreach ($assessmentActivities as $assessmentActivity) {
                $assessment = $assessmentActivity->assessment;
                
                // Only sync if assessment is approved
                if ($assessment->status !== 'approved') {
                    continue;
                }
                
                // Verify assessment is in current financial year
                $assessmentFY = $orgSettings->getFinancialYearForDate($assessment->created_at ?? now());
                if ($assessmentFY != $currentFY) {
                    continue; // Skip assessments from other financial years
                }

                // Calculate performance score based on task completion
                $performanceScore = $this->calculateTaskPerformanceScore($taskActivity, $assessmentActivity);

                // Create or update progress report
                $this->createOrUpdateProgressReport($assessmentActivity, $taskActivity, $performanceScore);

                // Record sync
                TaskPerformanceSync::updateOrCreate(
                    [
                        'task_activity_id' => $taskActivity->id,
                        'assessment_activity_id' => $assessmentActivity->id,
                    ],
                    [
                        'performance_score' => $performanceScore,
                        'synced_at' => now(),
                        'synced_by' => $userId,
                    ]
                );
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task performance sync failed: ' . $e->getMessage(), [
                'task_activity_id' => $taskActivity->id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Calculate performance score based on task completion
     */
    private function calculateTaskPerformanceScore(TaskActivity $taskActivity, AssessmentActivity $assessmentActivity): float
    {
        $baseScore = 0;

        // Check if task is completed
        if ($taskActivity->status === 'Completed') {
            $baseScore = 100;
            
            // Check if completed on time
            if ($taskActivity->actual_end_date && $taskActivity->end_date) {
                $actualEnd = Carbon::parse($taskActivity->actual_end_date);
                $expectedEnd = Carbon::parse($taskActivity->end_date);
                
                if ($actualEnd->greaterThan($expectedEnd)) {
                    // Late completion - reduce score
                    $daysLate = $actualEnd->diffInDays($expectedEnd);
                    $baseScore = max(50, 100 - ($daysLate * 5)); // Reduce by 5 points per day late, minimum 50
                }
            }
        } elseif ($taskActivity->status === 'In Progress') {
            // Calculate progress based on reports or time elapsed
            $progress = $this->calculateTaskProgress($taskActivity);
            $baseScore = $progress;
        } else {
            $baseScore = 0;
        }

        // Apply assessment activity contribution percentage
        $contribution = $assessmentActivity->contribution_percentage ?? 0;
        $finalScore = ($baseScore * $contribution) / 100;

        return round($finalScore, 2);
    }

    /**
     * Calculate task progress percentage
     */
    private function calculateTaskProgress(TaskActivity $taskActivity): float
    {
        // Check if there are approved reports
        $reports = $taskActivity->reports()->where('status', 'approved')->get();
        
        if ($reports->isNotEmpty()) {
            // Use the latest report's completion percentage if available
            $latestReport = $reports->first();
            if (isset($latestReport->completion_percentage)) {
                return (float) $latestReport->completion_percentage;
            }
        }

        // Calculate based on time elapsed
        if ($taskActivity->start_date && $taskActivity->end_date) {
            $startDate = Carbon::parse($taskActivity->start_date);
            $endDate = Carbon::parse($taskActivity->end_date);
            $today = Carbon::today();

            if ($today->greaterThanOrEqualTo($endDate)) {
                return 100; // Should be completed
            }

            $totalDays = $startDate->diffInDays($endDate);
            $elapsedDays = $startDate->diffInDays($today);

            if ($totalDays > 0) {
                return min(90, ($elapsedDays / $totalDays) * 100); // Max 90% until completed
            }
        }

        return 0;
    }

    /**
     * Create or update progress report from task activity
     */
    private function createOrUpdateProgressReport(
        AssessmentActivity $assessmentActivity,
        TaskActivity $taskActivity,
        float $performanceScore
    ) {
        $reportDate = $taskActivity->actual_end_date ?? $taskActivity->end_date ?? now();

        // Check if report already exists for this date
        $existingReport = AssessmentProgressReport::where('activity_id', $assessmentActivity->id)
            ->whereDate('report_date', Carbon::parse($reportDate)->format('Y-m-d'))
            ->where('source', 'task_sync')
            ->first();

        if ($existingReport) {
            $existingReport->update([
                'progress_text' => $this->generateProgressText($taskActivity),
                'performance_score' => $performanceScore,
            ]);
        } else {
            AssessmentProgressReport::create([
                'activity_id' => $assessmentActivity->id,
                'report_date' => $reportDate,
                'progress_text' => $this->generateProgressText($taskActivity),
                'status' => 'approved', // Auto-approved when synced from tasks
                'performance_score' => $performanceScore,
                'source' => 'task_sync',
                'hod_approved_at' => now(),
                'hod_approved_by' => null, // System approved
            ]);
        }
    }

    /**
     * Generate progress text from task activity
     */
    private function generateProgressText(TaskActivity $taskActivity): string
    {
        $status = $taskActivity->status;
        $name = $taskActivity->name;
        
        if ($status === 'Completed') {
            $text = "Task activity '{$name}' has been completed";
            if ($taskActivity->actual_end_date) {
                $text .= " on " . Carbon::parse($taskActivity->actual_end_date)->format('Y-m-d');
            }
        } elseif ($status === 'In Progress') {
            $text = "Task activity '{$name}' is in progress";
        } else {
            $text = "Task activity '{$name}' status: {$status}";
        }

        return $text;
    }

    /**
     * Sync all pending task activities for a user
     */
    public function syncUserTasks($userId, $period = null)
    {
        // Get current financial year
        $orgSettings = OrganizationSetting::getSettings();
        $currentFY = $orgSettings->current_financial_year ?? date('Y');
        $fyDates = $orgSettings->getFinancialYearDates($currentFY);
        
        $query = TaskActivity::whereHas('assignedUsers', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereIn('status', ['Completed', 'In Progress'])
        ->where(function($q) use ($currentFY, $fyDates) {
            // Filter by financial year
            $q->where('financial_year', $currentFY)
              ->orWhere(function($q2) use ($fyDates) {
                  $q2->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
                     ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
              });
        });

        if ($period) {
            $startDate = Carbon::parse($period['start']);
            $endDate = Carbon::parse($period['end']);
            $query->whereBetween('end_date', [$startDate, $endDate]);
        } else {
            // Default to current financial year period
            $query->whereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
        }

        $taskActivities = $query->get();
        $synced = 0;

        foreach ($taskActivities as $taskActivity) {
            if ($this->syncTaskToPerformance($taskActivity, $userId)) {
                $synced++;
            }
        }

        return $synced;
    }
}

