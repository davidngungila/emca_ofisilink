<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentJob extends Model
{
    protected $table = 'recruitment_jobs';

    protected $fillable = [
        'job_title',
        'institutional_position_id',
        'salary_structure_id',
        'job_description',
        'qualifications',
        'application_deadline',
        'required_attachments',
        'interview_mode',
        'status',
        'payroll_approval_status',
        'payroll_approved_by',
        'payroll_approved_at',
        'payroll_approval_notes',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'approved_at' => 'datetime',
        'payroll_approved_at' => 'datetime',
        'required_attachments' => 'array',
        'interview_mode' => 'array',
    ];

    /**
     * Get the user who created this job
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this job
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all applications for this job
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    /**
     * Scope for active jobs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope for pending approval jobs
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'Pending Approval');
    }

    /**
     * Check if job deadline has passed
     */
    public function isDeadlinePassed(): bool
    {
        return $this->application_deadline < now()->startOfDay();
    }

    /**
     * Get the institutional position
     */
    public function institutionalPosition(): BelongsTo
    {
        return $this->belongsTo(InstitutionalPosition::class);
    }

    /**
     * Get the salary structure
     */
    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    /**
     * Get the payroll approver
     */
    public function payrollApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payroll_approved_by');
    }
}

