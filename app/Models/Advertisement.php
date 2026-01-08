<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    use HasFactory;

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
        'attachments' => 'array',
        'require_acknowledgment' => 'boolean',
        'allow_redisplay' => 'boolean',
    ];

    /**
     * Get all acknowledgments for this advertisement
     */
    public function acknowledgments(): HasMany
    {
        return $this->hasMany(AdvertisementAcknowledgment::class);
    }

    /**
     * Get the user who created this advertisement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this advertisement
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if advertisement is currently active
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
     * Check if user has acknowledged this advertisement
     */
    public function isAcknowledgedBy(int $userId): bool
    {
        return $this->acknowledgments()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Check if advertisement should be shown to user
     */
    public function shouldShowToUser(User $user): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // If show to all, check if user has acknowledged (unless allow_redisplay and updated)
        if ($this->show_to_all) {
            if ($this->require_acknowledgment && $this->isAcknowledgedBy($user->id)) {
                // Check if allow_redisplay and advertisement was updated after acknowledgment
                if ($this->allow_redisplay) {
                    $acknowledgment = $this->acknowledgments()
                        ->where('user_id', $user->id)
                        ->first();
                    
                    if ($acknowledgment && $this->updated_at->gt($acknowledgment->acknowledged_at)) {
                        return true; // Show again if updated after acknowledgment
                    }
                }
                return false; // Already acknowledged
            }
            return true;
        }

        // Check if user has required role
        if ($this->target_roles && is_array($this->target_roles)) {
            $userRoleIds = $user->roles->pluck('id')->toArray();
            $hasRequiredRole = !empty(array_intersect($this->target_roles, $userRoleIds));
            
            if (!$hasRequiredRole) {
                return false;
            }
        }

        // Check acknowledgment
        if ($this->require_acknowledgment && $this->isAcknowledgedBy($user->id)) {
            if ($this->allow_redisplay) {
                $acknowledgment = $this->acknowledgments()
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($acknowledgment && $this->updated_at->gt($acknowledgment->acknowledged_at)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-danger',
            'important' => 'bg-warning',
            default => 'bg-info',
        };
    }

    /**
     * Get attachment URLs
     */
    public function getAttachmentUrls(): array
    {
        if (!$this->attachments || !is_array($this->attachments)) {
            return [];
        }

        $urls = [];
        foreach ($this->attachments as $attachment) {
            if (isset($attachment['path'])) {
                $urls[] = [
                    'name' => $attachment['name'] ?? basename($attachment['path']),
                    'url' => asset('storage/' . $attachment['path']),
                    'type' => $attachment['type'] ?? $this->getFileType($attachment['path']),
                    'size' => $attachment['size'] ?? null,
                ];
            }
        }
        return $urls;
    }

    /**
     * Get file type from extension
     */
    protected function getFileType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $pdfTypes = ['pdf'];
        $docTypes = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        
        if (in_array($extension, $imageTypes)) {
            return 'image';
        } elseif (in_array($extension, $pdfTypes)) {
            return 'pdf';
        } elseif (in_array($extension, $docTypes)) {
            return 'document';
        }
        
        return 'file';
    }
}
