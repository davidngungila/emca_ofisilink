<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceCriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'criteria',
        'weighting',
        'scoring_rules',
        'status',
        'effective_from',
        'effective_to',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'criteria' => 'array',
        'weighting' => 'array',
        'scoring_rules' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_default' => 'boolean',
    ];

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get assessments using this criteria
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'performance_criteria_id');
    }

    /**
     * Get performance measurements using this criteria
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(PerformanceMeasurement::class, 'performance_criteria_id');
    }

    /**
     * Scope for active criteria
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for default criteria
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Check if criteria is currently effective
     */
    public function isEffective(): bool
    {
        $now = now();
        
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->effective_from && $now->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_to && $now->gt($this->effective_to)) {
            return false;
        }

        return true;
    }
}
