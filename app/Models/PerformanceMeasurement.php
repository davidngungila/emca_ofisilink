<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PerformanceMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_type',
        'period_type',
        'period_start',
        'period_end',
        'year',
        'month',
        'quarter',
        'user_id',
        'department_id',
        'performance_criteria_id',
        'overall_score',
        'scores_by_criteria',
        'metrics',
        'summary',
        'recommendations',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'overall_score' => 'decimal:2',
        'scores_by_criteria' => 'array',
        'metrics' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user (for individual measurements)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department (for department/organization measurements)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the performance criteria used
     */
    public function performanceCriteria(): BelongsTo
    {
        return $this->belongsTo(PerformanceCriteria::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get performance issues for this measurement
     */
    public function issues(): HasMany
    {
        return $this->hasMany(PerformanceIssue::class, 'performance_measurement_id');
    }

    /**
     * Scope for individual measurements
     */
    public function scopeIndividual($query)
    {
        return $query->where('measurement_type', 'individual');
    }

    /**
     * Scope for department measurements
     */
    public function scopeDepartment($query)
    {
        return $query->where('measurement_type', 'department');
    }

    /**
     * Scope for organization measurements
     */
    public function scopeOrganization($query)
    {
        return $query->where('measurement_type', 'organization');
    }

    /**
     * Scope for period type
     */
    public function scopePeriodType($query, $type)
    {
        return $query->where('period_type', $type);
    }

    /**
     * Scope for year
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Get period label
     */
    public function getPeriodLabelAttribute(): string
    {
        switch ($this->period_type) {
            case 'monthly':
                return Carbon::create($this->year, $this->month, 1)->format('F Y');
            case 'quarterly':
                return "Q{$this->quarter} {$this->year}";
            case 'semi_annual':
                return $this->period_start->format('M') . ' - ' . $this->period_end->format('M Y');
            case 'annual':
                return $this->year;
            default:
                return $this->period_start->format('M d') . ' - ' . $this->period_end->format('M d, Y');
        }
    }
}
