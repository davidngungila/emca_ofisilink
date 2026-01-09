<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeAcknowledgment extends Model
{
    use HasFactory;

    protected $table = 'advertisement_acknowledgments'; // Keep existing table name

    protected $fillable = [
        'advertisement_id',
        'user_id',
        'acknowledged_at',
        'notes',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the notice
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'advertisement_id');
    }

    /**
     * Get the user who acknowledged
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

