<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'performance_measurement_id',
        'user_id',
        'department_id',
        'issue_type',
        'title',
        'description',
        'severity',
        'status',
        'identified_date',
        'target_resolution_date',
        'resolved_date',
        'resolution_notes',
        'action_plan',
        'assigned_to',
        'identified_by',
    ];

    protected $casts = [
        'identified_date' => 'date',
        'target_resolution_date' => 'date',
        'resolved_date' => 'date',
        'action_plan' => 'array',
    ];

    /**
     * Get the assessment
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Get the performance measurement
     */
    public function performanceMeasurement(): BelongsTo
    {
        return $this->belongsTo(PerformanceMeasurement::class);
    }

    /**
     * Get the user (employee)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get who is assigned to resolve this
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get who identified the issue
     */
    public function identifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identified_by');
    }

    /**
     * Scope for open issues
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope for resolved issues
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope by severity
     */
    public function scopeSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Check if issue is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->target_resolution_date || $this->status === 'resolved') {
            return false;
        }

        return now()->gt($this->target_resolution_date);
    }
}
