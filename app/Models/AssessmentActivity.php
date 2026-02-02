<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'activity_name',
        'description',
        'targets',
        'task_activity_id',
        'target_start_date',
        'target_end_date',
        'reporting_frequency',
        'contribution_percentage',
    ];

    protected $casts = [
        'contribution_percentage' => 'decimal:2',
        'targets' => 'array',
        'target_start_date' => 'date',
        'target_end_date' => 'date',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function progressReports()
    {
        return $this->hasMany(AssessmentProgressReport::class, 'activity_id');
    }

    /**
     * Get the linked task activity
     */
    public function taskActivity()
    {
        return $this->belongsTo(TaskActivity::class, 'task_activity_id');
    }
}

