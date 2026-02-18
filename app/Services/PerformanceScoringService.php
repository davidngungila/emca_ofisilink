<?php

namespace App\Services;

use App\Models\ActivityReport;
use App\Models\TaskActivity;
use App\Models\MainTask;
use App\Models\Assessment;
use App\Models\AssessmentActivity;
use App\Models\AssessmentProgressReport;
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceScoringService
{
    /**
     * Calculate individual performance score for a financial year
     * Formula: (Task Completion × 0.4) + (Quality × 0.3) + (Strategic × 0.2) + (Initiative × 0.1)
     */
    public function calculateIndividualPerformance($userId, $financialYear = null)
    {
        $orgSettings = OrganizationSetting::getSettings();
        $fy = $financialYear ?? $orgSettings->current_financial_year ?? date('Y');
        $fyDates = $orgSettings->getFinancialYearDates($fy);

        // 1. Task Completion Rate (40%)
        $taskCompletionRate = $this->calculateTaskCompletionRate($userId, $fyDates);
        
        // 2. Task Quality Score (30%)
        $qualityScore = $this->calculateQualityScore($userId, $fyDates);
        
        // 3. Strategic Task Completion (20%)
        $strategicScore = $this->calculateStrategicTaskCompletion($userId, $fyDates);
        
        // 4. Initiative & Innovation (10%)
        $initiativeScore = $this->calculateInitiativeScore($userId, $fyDates);

        // Calculate final score
        $finalScore = ($taskCompletionRate * 0.4) + 
                     ($qualityScore * 0.3) + 
                     ($strategicScore * 0.2) + 
                     ($initiativeScore * 0.1);

        return [
            'final_score' => round($finalScore, 2),
            'components' => [
                'task_completion' => [
                    'score' => $taskCompletionRate,
                    'weight' => 40,
                    'contribution' => round($taskCompletionRate * 0.4, 2)
                ],
                'quality' => [
                    'score' => $qualityScore,
                    'weight' => 30,
                    'contribution' => round($qualityScore * 0.3, 2)
                ],
                'strategic' => [
                    'score' => $strategicScore,
                    'weight' => 20,
                    'contribution' => round($strategicScore * 0.2, 2)
                ],
                'initiative' => [
                    'score' => $initiativeScore,
                    'weight' => 10,
                    'contribution' => round($initiativeScore * 0.1, 2)
                ]
            ],
            'financial_year' => $fy,
            'period' => [
                'start' => $fyDates['start']->format('Y-m-d'),
                'end' => $fyDates['end']->format('Y-m-d')
            ]
        ];
    }

    /**
     * Calculate task completion rate (40% weight)
     * Formula: (Completed Tasks / Assigned Tasks) × 100
     */
    private function calculateTaskCompletionRate($userId, $fyDates)
    {
        $assignedTasks = TaskActivity::whereHas('assignedUsers', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where(function($q) use ($fyDates) {
            $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
              ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
        })
        ->count();

        if ($assignedTasks === 0) {
            return 0;
        }

        $completedTasks = TaskActivity::whereHas('assignedUsers', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', 'Completed')
        ->where(function($q) use ($fyDates) {
            $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
              ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
        })
        ->count();

        return round(($completedTasks / $assignedTasks) * 100, 2);
    }

    /**
     * Calculate quality score (30% weight)
     * Formula: Average of all task quality ratings (1-5 stars converted to 0-100)
     */
    private function calculateQualityScore($userId, $fyDates)
    {
        $reports = ActivityReport::where('user_id', $userId)
            ->where('status', 'Approved')
            ->whereNotNull('quality_rating')
            ->whereBetween('report_date', [$fyDates['start'], $fyDates['end']])
            ->get();

        if ($reports->isEmpty()) {
            return 0;
        }

        // Convert 1-5 star rating to 0-100 scale
        $totalScore = $reports->sum(function($report) {
            return ($report->quality_rating / 5) * 100;
        });

        return round($totalScore / $reports->count(), 2);
    }

    /**
     * Calculate strategic task completion (20% weight)
     * Formula: (Completed Strategic Tasks / Total Strategic Tasks) × 100
     */
    private function calculateStrategicTaskCompletion($userId, $fyDates)
    {
        // Get tasks linked to performance objectives
        $strategicTasks = TaskActivity::whereHas('assignedUsers', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotNull('assessment_activity_id')
        ->where(function($q) use ($fyDates) {
            $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
              ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
        })
        ->get();

        if ($strategicTasks->isEmpty()) {
            return 0;
        }

        $completedStrategic = $strategicTasks->where('status', 'Completed')->count();
        
        // Bonus for early completion
        $earlyCompletionBonus = 0;
        foreach ($strategicTasks->where('status', 'Completed') as $task) {
            if ($task->actual_end_date && $task->end_date) {
                $daysEarly = Carbon::parse($task->end_date)->diffInDays(Carbon::parse($task->actual_end_date), false);
                if ($daysEarly > 0) {
                    $earlyCompletionBonus += min(5, $daysEarly); // Max 5 bonus points per task
                }
            }
        }

        $baseScore = ($completedStrategic / $strategicTasks->count()) * 100;
        $bonusScore = min(10, $earlyCompletionBonus); // Max 10 bonus points

        return round(min(100, $baseScore + $bonusScore), 2);
    }

    /**
     * Calculate initiative score (10% weight)
     * Based on staff-created tasks that exceed expectations
     */
    private function calculateInitiativeScore($userId, $fyDates)
    {
        // Count tasks created by user (staff-initiated)
        $staffCreatedTasks = MainTask::where('created_by', $userId)
            ->where(function($q) use ($fyDates) {
                $q->whereBetween('start_date', [$fyDates['start'], $fyDates['end']])
                  ->orWhereBetween('end_date', [$fyDates['start'], $fyDates['end']]);
            })
            ->count();

        // Count reports with initiative bonus
        $initiativeReports = ActivityReport::where('user_id', $userId)
            ->where('initiative_bonus', true)
            ->where('status', 'Approved')
            ->whereBetween('report_date', [$fyDates['start'], $fyDates['end']])
            ->count();

        // Calculate score (each initiative task/report = 10 points, max 100)
        $initiativeCount = $staffCreatedTasks + $initiativeReports;
        $score = min(100, $initiativeCount * 10);

        return round($score, 2);
    }

    /**
     * Calculate performance score for a single activity report
     */
    public function calculateReportPerformanceScore(ActivityReport $report)
    {
        $baseScore = 0;

        // Base score from quality rating (if available)
        if ($report->quality_rating) {
            $baseScore = ($report->quality_rating / 5) * 100;
        } else {
            // Default score based on completion status
            $baseScore = $report->status === 'Approved' ? 80 : 0;
        }

        // Complexity multiplier
        $complexityMultiplier = match($report->complexity_tag) {
            'complex' => 1.2,
            'standard' => 1.0,
            'routine' => 0.9,
            default => 1.0
        };

        // Initiative bonus
        $initiativeBonus = $report->initiative_bonus ? 10 : 0;

        $finalScore = min(100, ($baseScore * $complexityMultiplier) + $initiativeBonus);

        return round($finalScore, 2);
    }

    /**
     * Sync approved activity report to performance module
     */
    public function syncReportToPerformance(ActivityReport $report)
    {
        if ($report->synced_to_performance) {
            return false; // Already synced
        }

        $taskActivity = $report->activity;
        if (!$taskActivity || !$taskActivity->assessment_activity_id) {
            return false; // Not linked to performance
        }

        try {
            DB::beginTransaction();

            $assessmentActivity = $taskActivity->assessmentActivity;
            if (!$assessmentActivity) {
                DB::rollBack();
                return false;
            }

            // Calculate performance score
            $performanceScore = $this->calculateReportPerformanceScore($report);

            // Create or update assessment progress report
            $progressReport = AssessmentProgressReport::updateOrCreate(
                [
                    'activity_id' => $assessmentActivity->id,
                    'report_date' => $report->report_date,
                    'source' => 'task_report_sync'
                ],
                [
                    'progress_text' => $this->generateProgressText($report),
                    'performance_score' => $performanceScore,
                    'status' => 'approved',
                    'hod_approved_at' => $report->approved_at,
                    'hod_approved_by' => $report->approved_by,
                    'hod_comments' => $report->quality_comments ?? $report->approver_comments,
                ]
            );

            // Update activity report with sync info
            $report->update([
                'synced_to_performance' => true,
                'synced_at' => now(),
                'performance_score' => $performanceScore,
                'assessment_progress_report_id' => $progressReport->id,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to sync report to performance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate progress text from activity report
     */
    private function generateProgressText(ActivityReport $report): string
    {
        $text = $report->work_description ?? 'Task progress reported';
        
        if ($report->completion_status) {
            $text .= "\n\nCompletion Status: " . ucfirst($report->completion_status);
        }

        if ($report->quality_comments) {
            $text .= "\n\nQuality Assessment: " . $report->quality_comments;
        }

        return $text;
    }
}





