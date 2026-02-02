<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'min_salary',
        'max_salary',
        'basic_salary',
        'allowances',
        'deductions',
        'qualifications',
        'department_id',
        'position_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'allowances' => 'array',
        'deductions' => 'array',
        'qualifications' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get institutional positions using this salary structure
     */
    public function institutionalPositions(): HasMany
    {
        return $this->hasMany(InstitutionalPosition::class);
    }

    /**
     * Get employees using this salary structure
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'salary_structure_id');
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active salary structures
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
