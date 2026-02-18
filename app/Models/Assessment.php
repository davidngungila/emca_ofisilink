<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'performance_criteria_id',
        'main_responsibility',
        'description',
        'targets',
        'task_activity_id',
        'target_start_date',
        'target_end_date',
        'target_type',
        'contribution_percentage',
        'organizational_goal_id',
        'status',
        'hod_approved_at',
        'hod_approved_by',
        'hod_comments',
    ];

    public function organizationalGoal()
    {
        return $this->belongsTo(OrganizationalGoal::class);
    }

    protected $casts = [
        'contribution_percentage' => 'decimal:2',
        'hod_approved_at' => 'datetime',
        'targets' => 'array',
        'target_start_date' => 'date',
        'target_end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function hodApprover()
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function activities()
    {
        return $this->hasMany(AssessmentActivity::class);
    }

    public function progressReports()
    {
        // through: assessment_activities.assessment_id -> assessment.id
        // final uses assessment_progress_reports.activity_id -> assessment_activities.id
        return $this->hasManyThrough(
            AssessmentProgressReport::class,
            AssessmentActivity::class,
            'assessment_id', // Foreign key on assessment_activities
            'activity_id',   // Foreign key on assessment_progress_reports
            'id',            // Local key on assessments
            'id'             // Local key on assessment_activities
        );
    }

    /**
     * Get the linked task activity
     */
    public function taskActivity()
    {
        return $this->belongsTo(TaskActivity::class, 'task_activity_id');
    }

    /**
     * Get the performance criteria
     */
    public function performanceCriteria()
    {
        return $this->belongsTo(PerformanceCriteria::class);
    }

    /**
     * Get performance issues for this assessment
     */
    public function issues()
    {
        return $this->hasMany(PerformanceIssue::class, 'assessment_id');
    }
}

