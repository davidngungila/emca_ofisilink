<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackup;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupOldBackups extends Command
{
    protected $signature = 'system:cleanup-backups {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean up old database backups based on retention policy';

    public function handle(): int
    {
        $retentionDays = SystemSetting::getValue('backup_retention_days', 30);
        $cutoffDate = now()->subDays($retentionDays);
        
        $this->info("Cleaning up backups older than {$retentionDays} days (before {$cutoffDate->format('Y-m-d H:i:s')})");
        
        // Find old backups
        $oldBackups = DatabaseBackup::where('created_at', '<', $cutoffDate)
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($oldBackups->isEmpty()) {
            $this->info('No old backups found to clean up.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$oldBackups->count()} backup(s) to clean up.");
        
        $deletedCount = 0;
        $failedCount = 0;
        $totalSizeFreed = 0;
        
        foreach ($oldBackups as $backup) {
            try {
                $filePath = $backup->file_path;
                $fullPath = storage_path('app/' . $filePath);
                
                // Get file size before deletion
                $fileSize = 0;
                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                } elseif (Storage::disk('local')->exists($filePath)) {
                    $fileSize = Storage::disk('local')->size($filePath);
                }
                
                if ($this->option('dry-run')) {
                    $this->line("  [DRY RUN] Would delete: {$backup->filename} (Size: " . $this->formatBytes($fileSize) . ", Created: {$backup->created_at->format('Y-m-d H:i:s')})");
                    $deletedCount++;
                    $totalSizeFreed += $fileSize;
                    continue;
                }
                
                // Delete file
                $fileDeleted = false;
                if (file_exists($fullPath)) {
                    $fileDeleted = @unlink($fullPath);
                } elseif (Storage::disk('local')->exists($filePath)) {
                    $fileDeleted = Storage::disk('local')->delete($filePath);
                }
                
                // Delete database record
                $backup->delete();
                
                if ($fileDeleted || !file_exists($fullPath)) {
                    $deletedCount++;
                    $totalSizeFreed += $fileSize;
                    $this->line("  ✓ Deleted: {$backup->filename} (Size: " . $this->formatBytes($fileSize) . ")");
                    Log::info('Old backup deleted', [
                        'backup_id' => $backup->id,
                        'filename' => $backup->filename,
                        'created_at' => $backup->created_at->toDateTimeString(),
                        'size' => $fileSize
                    ]);
                } else {
                    $failedCount++;
                    $this->error("  ✗ Failed to delete: {$backup->filename}");
                    Log::warning('Failed to delete backup file', [
                        'backup_id' => $backup->id,
                        'filename' => $backup->filename,
                        'file_path' => $filePath
                    ]);
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("  ✗ Error deleting {$backup->filename}: " . $e->getMessage());
                Log::error('Error deleting backup', [
                    'backup_id' => $backup->id,
                    'filename' => $backup->filename,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if ($this->option('dry-run')) {
            $this->info("\n[DRY RUN] Would delete {$deletedCount} backup(s), freeing " . $this->formatBytes($totalSizeFreed));
        } else {
            $this->info("\nCleanup completed:");
            $this->info("  - Deleted: {$deletedCount} backup(s)");
            if ($failedCount > 0) {
                $this->warn("  - Failed: {$failedCount} backup(s)");
            }
            $this->info("  - Space freed: " . $this->formatBytes($totalSizeFreed));
        }
        
        return self::SUCCESS;
    }
    
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

