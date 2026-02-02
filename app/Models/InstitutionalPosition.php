<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstitutionalPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_title',
        'description',
        'department_id',
        'salary_structure_id',
        'required_count',
        'current_count',
        'shortage',
        'qualifications',
        'responsibilities',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'qualifications' => 'array',
        'responsibilities' => 'array',
    ];

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the salary structure
     */
    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    /**
     * Get current staff in this position
     */
    public function currentStaff(): HasMany
    {
        return $this->hasMany(Employee::class, 'institutional_position_id');
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate shortage automatically
     */
    public function calculateShortage(): int
    {
        $this->shortage = max(0, $this->required_count - $this->current_count);
        return $this->shortage;
    }

    /**
     * Update current count based on actual employees
     */
    public function updateCurrentCount(): void
    {
        $this->current_count = $this->currentStaff()->whereHas('user', function($q) {
            $q->where('is_active', true);
        })->count();
        $this->calculateShortage();
        $this->save();
    }

    /**
     * Scope for active positions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for positions with shortages
     */
    public function scopeWithShortage($query)
    {
        return $query->whereRaw('required_count > current_count');
    }
}
