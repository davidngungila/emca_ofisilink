<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'user_id',
        'report_date',
        'work_description',
        'next_activities',
        'attachment_path',
        'completion_status',
        'reason_if_delayed',
        'status',
        'approved_by',
        'approved_at',
        'approver_comments',
        'quality_rating',
        'complexity_tag',
        'initiative_bonus',
        'quality_comments',
        'performance_score',
        'synced_to_performance',
        'synced_at',
        'financial_year',
        'assessment_progress_report_id',
    ];

    protected $casts = [
        'report_date' => 'date',
        'approved_at' => 'datetime',
        'synced_at' => 'datetime',
        'performance_score' => 'decimal:2',
        'initiative_bonus' => 'boolean',
        'synced_to_performance' => 'boolean',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class, 'activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(ActivityReportAttachment::class, 'report_id');
    }

    public function assessmentProgressReport(): BelongsTo
    {
        return $this->belongsTo(AssessmentProgressReport::class, 'assessment_progress_report_id');
    }

    /**
     * Check if this report is linked to a performance activity
     */
    public function isLinkedToPerformance(): bool
    {
        return $this->activity && $this->activity->assessment_activity_id !== null;
    }

    /**
     * Get performance impact information
     */
    public function getPerformanceImpactAttribute()
    {
        if (!$this->isLinkedToPerformance()) {
            return null;
        }

        $assessmentActivity = $this->activity->assessmentActivity;
        if (!$assessmentActivity) {
            return null;
        }

        return [
            'activity_name' => $assessmentActivity->activity_name,
            'contribution_percentage' => $assessmentActivity->contribution_percentage,
            'assessment' => $assessmentActivity->assessment->main_responsibility ?? null,
        ];
    }
}
