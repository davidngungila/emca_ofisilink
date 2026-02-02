<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskPerformanceSync extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_activity_id',
        'assessment_activity_id',
        'performance_score',
        'synced_at',
        'synced_by',
        'sync_notes',
    ];

    protected $casts = [
        'performance_score' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the task activity
     */
    public function taskActivity(): BelongsTo
    {
        return $this->belongsTo(TaskActivity::class);
    }

    /**
     * Get the assessment activity
     */
    public function assessmentActivity(): BelongsTo
    {
        return $this->belongsTo(AssessmentActivity::class);
    }

    /**
     * Get who synced this
     */
    public function syncedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'synced_by');
    }
}
