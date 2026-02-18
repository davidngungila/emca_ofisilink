<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationalGoal extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'parent_id',
        'department_id',
        'level',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(OrganizationalGoal::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrganizationalGoal::class, 'parent_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
