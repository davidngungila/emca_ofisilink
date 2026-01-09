<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    protected $table = 'advertisements'; // Keep existing table name

    protected $fillable = [
        'title',
        'content',
        'attachments',
        'priority',
        'start_date',
        'expiry_date',
        'is_active',
        'show_to_all',
        'target_roles',
        'require_acknowledgment',
        'allow_redisplay',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'show_to_all' => 'boolean',
        'target_roles' => 'array',
        'require_acknowledgment' => 'boolean',
        'allow_redisplay' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Get the user who created this notice
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this notice
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all acknowledgments for this notice
     */
    public function acknowledgments(): HasMany
    {
        return $this->hasMany(NoticeAcknowledgment::class, 'advertisement_id');
    }

    /**
     * Check if notice is currently active based on dates
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->expiry_date && $now->gt($this->expiry_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if notice should be shown to a specific user
     */
    public function shouldShowToUser(User $user): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        if ($this->show_to_all) {
            return true;
        }

        if (empty($this->target_roles)) {
            return false;
        }

        $userRoleIds = $user->roles->pluck('id')->toArray();
        $targetRoleIds = $this->target_roles;

        return !empty(array_intersect($userRoleIds, $targetRoleIds));
    }

    /**
     * Check if notice has been acknowledged by a user
     */
    public function isAcknowledgedBy(User $user): bool
    {
        return $this->acknowledgments()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Get status badge class for display
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'important' => 'warning',
            default => 'info',
        };
    }

    /**
     * Get attachment URLs for display
     */
    public function getAttachmentUrls(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        $formattedAttachments = [];
        foreach ($this->attachments as $attachment) {
            $url = \Illuminate\Support\Facades\Storage::url($attachment['path']);
            $formattedAttachments[] = [
                'name' => $attachment['name'],
                'url' => $url,
                'type' => $this->getFileType($attachment['name']),
                'size' => $attachment['size'] ?? null,
            ];
        }
        return $formattedAttachments;
    }

    /**
     * Get file type from filename
     */
    protected function getFileType(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return match (strtolower($extension)) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'pdf' => 'pdf',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv' => 'document',
            default => 'file',
        };
    }
}

