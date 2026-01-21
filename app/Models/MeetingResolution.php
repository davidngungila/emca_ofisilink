<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingResolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'resolution_number',
        'title',
        'description',
        'resolution_text',
        'proposed_by',
        'seconded_by',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'order_index',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function proposer()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function seconder()
    {
        return $this->belongsTo(User::class, 'seconded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
