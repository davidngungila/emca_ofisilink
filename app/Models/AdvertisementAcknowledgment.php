<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementAcknowledgment extends Model
{
    use HasFactory;

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
     * Get the advertisement
     */
    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    /**
     * Get the user who acknowledged
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
