<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Services\EmailService;
use App\Models\User;
use App\Models\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SystemDatabaseBackup extends Command
{
    protected $signature = 'system:backup-db {--sleep-30 : Sleep 30 seconds before starting} {--return : Return path instead of printing}';

    protected $description = 'Dump full database to SQL file with password protection, append current year to filename, and notify admins';

    public function handle(NotificationService $notifier): int
    {
        if ($this->option('sleep-30')) {
            sleep(30);
        }

        $disk = Storage::disk('local');
        $backupDir = 'backups';
        
        // Ensure backup directory exists - try multiple methods
        if (!$disk->exists($backupDir)) {
            $disk->makeDirectory($backupDir);
        }
        
        // Also ensure directory exists on filesystem directly
        $backupDirPath = storage_path('app/' . $backupDir);
        if (!is_dir($backupDirPath)) {
            @mkdir($backupDirPath, 0755, true);
        }

        $now = now();
        $year = $now->year;
        $timestamp = $now->format('Ymd_His');
        $baseName = "ofisilink_backup_{$timestamp}_{$year}";

        // Use forward slash for storage path (Laravel convention)
        $sqlPath = $backupDir . "/{$baseName}.sql";
        // Use DIRECTORY_SEPARATOR for filesystem path
        $fullSqlPath = storage_path('app' . DIRECTORY_SEPARATOR . $backupDir . DIRECTORY_SEPARATOR . $baseName . '.sql');

        $backupSuccess = false;
        $errorMessage = null;
        $zipPath = null;
        $fullZipPath = null;
        
        // Create backup record in database
        $backupRecord = DatabaseBackup::create([
            'filename' => $baseName . '.sql',
            'file_path' => $sqlPath,
            'file_size' => 0,
            'status' => 'in_progress',
            'error_message' => null,
            'created_by' => null, // Can be set if called from web interface
        ]);

        try {
            // Build mysqldump command from DB config
            $db = config('database.connections.' . config('database.default'));
            $driver = $db['driver'] ?? '';
            
            if ($driver !== 'mysql') {
                throw new \Exception('This command currently supports only MySQL.');
            }

            $host = $db['host'] ?? '127.0.0.1';
            $port = $db['port'] ?? 3306;
            $database = $db['database'] ?? '';
            $username = $db['username'] ?? '';
            $password = $db['password'] ?? '';

            // Ensure the directory exists before attempting backup
            $backupDirPath = dirname($fullSqlPath);
            if (!is_dir($backupDirPath)) {
                if (!mkdir($backupDirPath, 0755, true)) {
                    throw new \Exception('Failed to create backup directory: ' . $backupDirPath);
                }
            }
            
            // Try to find mysqldump in common locations
            $mysqlDump = $this->findMysqldump();
            
            if (!$mysqlDump) {
                // Fallback: Use Laravel DB connection to export data
                // Only show info if not using --return flag (to avoid polluting output)
                if (!$this->option('return')) {
                    $this->info('mysqldump not found. Using Laravel DB connection for backup...');
                }
                \Log::info('Using Laravel DB connection method for backup', ['output_path' => $fullSqlPath]);
                $backupSuccess = $this->backupUsingLaravel($fullSqlPath, $database);
            } else {
                // Use mysqldump command
                \Log::info('Using mysqldump for backup', ['mysqldump' => $mysqlDump, 'output_path' => $fullSqlPath]);
                $cmdDump = $this->buildMysqldumpCommand($mysqlDump, $host, $port, $username, $password, $database, $fullSqlPath);
                $exit1 = $this->runShell($cmdDump);
                
                // Wait a moment for file to be written
                if ($exit1 === 0) {
                    sleep(1); // Give filesystem time to write
                }
                
                if ($exit1 === 0 && file_exists($fullSqlPath) && filesize($fullSqlPath) > 0) {
                    $backupSuccess = true;
                    \Log::info('mysqldump backup completed successfully', [
                        'file' => $fullSqlPath,
                        'size' => filesize($fullSqlPath)
                    ]);
                } else {
                    // If mysqldump fails, try Laravel method
                    \Log::warning('mysqldump failed or produced empty file', [
                        'exit_code' => $exit1,
                        'file_exists' => file_exists($fullSqlPath),
                        'file_size' => file_exists($fullSqlPath) ? filesize($fullSqlPath) : 0
                    ]);
                    // Only show info if not using --return flag
                    if (!$this->option('return')) {
                        $this->info('mysqldump failed. Trying Laravel DB connection method...');
                    }
                    if (file_exists($fullSqlPath)) {
                        @unlink($fullSqlPath);
                    }
                    $backupSuccess = $this->backupUsingLaravel($fullSqlPath, $database);
                }
            }

            if (!$backupSuccess || !file_exists($fullSqlPath) || filesize($fullSqlPath) === 0) {
                throw new \Exception('Database backup failed. Could not create SQL dump.');
            }

            // Verify SQL file was created and has content
            if (!file_exists($fullSqlPath)) {
                throw new \Exception('Backup SQL file was not created.');
            }
            
            // Verify file size
            $fileSize = filesize($fullSqlPath);
            if ($fileSize === 0) {
                throw new \Exception('Backup SQL file is empty.');
            }
            
            // Log successful backup creation
            \Log::info('Backup SQL file created successfully', [
                'path' => $fullSqlPath,
                'size' => $fileSize,
                'size_human' => $this->formatBytes($fileSize),
                'sql_path' => $sqlPath
            ]);
            
            // Create password-protected ZIP file
            $zipPath = $backupDir . "/{$baseName}.zip";
            $fullZipPath = storage_path('app' . DIRECTORY_SEPARATOR . $backupDir . DIRECTORY_SEPARATOR . $baseName . '.zip');
            $password = 'Ofisilink';
            
            // Prepare storage directory path to include in backup
            $storagePath = storage_path('app');
            
            // Try to create password-protected ZIP with database and storage files
            $zipCreated = false;
            if (class_exists(\ZipArchive::class)) {
                $zipCreated = $this->zipWithPasswordAndStorage($fullSqlPath, $fullZipPath, $password, $storagePath, $backupDir);
            }
            
            // Note: Don't update backup record here - it will be updated later after verification
            if ($zipCreated && file_exists($fullZipPath)) {
                \Log::info('Password-protected ZIP created', [
                    'zip_path' => $fullZipPath,
                    'zip_size' => filesize($fullZipPath)
                ]);
            } else {
                // If ZIP creation fails, keep SQL file but log warning
                $zipPath = null;
                $fullZipPath = null;
                \Log::warning('Failed to create password-protected ZIP, keeping SQL file', [
                    'sql_path' => $fullSqlPath
                ]);
            }

        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $this->error('Backup failed: ' . $errorMessage);
            \Log::error('Backup failed: ' . $errorMessage, ['trace' => $e->getTraceAsString()]);
        }

        // Update backup record in database
        if (isset($backupRecord)) {
            // Check if ZIP file exists (password-protected), otherwise use SQL file
            $finalPath = isset($fullZipPath) && file_exists($fullZipPath) ? $fullZipPath : (isset($fullSqlPath) && file_exists($fullSqlPath) ? $fullSqlPath : null);
            $finalStoragePath = isset($fullZipPath) && file_exists($fullZipPath) ? $zipPath : $sqlPath;
            $finalFilename = isset($fullZipPath) && file_exists($fullZipPath) ? $baseName . '.zip' : $baseName . '.sql';
            
            if ($backupSuccess && $finalPath && file_exists($finalPath)) {
                // Ensure file_path is correct (use forward slash for Laravel storage convention)
                $fileSize = filesize($finalPath);
                $backupRecord->update([
                    'status' => 'completed',
                    'filename' => $finalFilename,
                    'file_path' => $finalStoragePath, // Use ZIP if created, otherwise SQL
                    'file_size' => $fileSize,
                    'completed_at' => now(),
                    'error_message' => null,
                ]);
                
                \Log::info('Backup record updated successfully', [
                    'backup_id' => $backupRecord->id,
                    'filename' => $backupRecord->filename,
                    'file_path' => $backupRecord->file_path,
                    'file_size' => $fileSize,
                    'full_path' => $finalPath,
                    'is_zip' => isset($fullZipPath) && file_exists($fullZipPath)
                ]);
            } else {
                $backupRecord->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'completed_at' => now(),
                ]);
                
                \Log::error('Backup record marked as failed', [
                    'backup_id' => $backupRecord->id,
                    'error' => $errorMessage
                ]);
            }
        }

        // Store backup off-system (cloud storage)
        $offSystemStorageInfo = null;
        if ($backupSuccess) {
            $finalFile = isset($fullZipPath) && file_exists($fullZipPath) ? $fullZipPath : (isset($fullSqlPath) && file_exists($fullSqlPath) ? $fullSqlPath : null);
            if ($finalFile) {
                $offSystemStorageInfo = $this->storeBackupOffSystem($finalFile, $baseName);
            }
        }

        // Always send notifications (success or failure) with ZIP file attachment
        // Email will be sent automatically to all administrators with the backup ZIP file attached
        // Use ZIP path if available, otherwise SQL path
        $notificationPath = isset($zipPath) && isset($fullZipPath) && file_exists($fullZipPath) ? $zipPath : ($sqlPath ?? null);
        $finalFilePath = isset($fullZipPath) && file_exists($fullZipPath) ? $fullZipPath : (isset($fullSqlPath) && file_exists($fullSqlPath) ? $fullSqlPath : null);
        
        \Log::info('Sending backup notification emails with attachment to all administrators', [
            'backup_success' => $backupSuccess,
            'file_path' => $finalFilePath,
            'file_size' => $finalFilePath && file_exists($finalFilePath) ? filesize($finalFilePath) : 0
        ]);
        
        $this->sendNotifications($notifier, $now, $backupSuccess, $notificationPath, $errorMessage, $finalFilePath, $offSystemStorageInfo);

        // Check final file (ZIP or SQL)
        $finalFile = isset($fullZipPath) && file_exists($fullZipPath) ? $fullZipPath : (isset($fullSqlPath) && file_exists($fullSqlPath) ? $fullSqlPath : null);
        if ($backupSuccess && $finalFile) {
            $outputPath = isset($zipPath) && isset($fullZipPath) && file_exists($fullZipPath) ? $zipPath : $sqlPath;
            if ($this->option('return')) {
                // Only output the file path if successful - no other output
                $this->line($outputPath);
            } else {
                $fileType = isset($zipPath) && isset($fullZipPath) && file_exists($fullZipPath) ? 'ZIP (password-protected)' : 'SQL';
                $this->info('Backup completed: ' . $outputPath . ' (' . $fileType . ')');
            }
            return self::SUCCESS;
        } else {
            // On failure, don't output anything when --return is used
            // This prevents error messages from being treated as file paths
            if (!$this->option('return')) {
                $this->error('Backup failed. Check logs for details.');
            }
            return self::FAILURE;
        }
    }

    /**
     * Find mysqldump executable in common locations
     */
    protected function findMysqldump(): ?string
    {
        // Check if mysqldump is in PATH
        $paths = ['mysqldump'];
        
        // Common Windows locations
        if (stripos(PHP_OS, 'WIN') === 0) {
            $commonPaths = [
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\wamp\\bin\\mysql\\mysql' . $this->getMysqlVersion() . '\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
            ];
            $paths = array_merge($paths, $commonPaths);
        } else {
            // Linux/Mac locations
            $paths = array_merge($paths, [
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/opt/mysql/bin/mysqldump',
            ]);
        }

        foreach ($paths as $path) {
            if ($this->commandExists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check if command exists
     */
    protected function commandExists(string $command): bool
    {
        // Check if exec() function is available
        if (!function_exists('exec')) {
            // exec() is disabled - check if command exists as file
            if (file_exists($command)) {
                return true;
            }
            // Can't check via exec, assume it doesn't exist
            // Will fall back to Laravel DB connection method
            return false;
        }
        
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows: check if file exists
            if (file_exists($command)) {
                return true;
            }
            // Try 'where' command
            $output = [];
            $return = 0;
            @exec('where ' . escapeshellarg($command) . ' 2>nul', $output, $return);
            return $return === 0 && !empty($output);
        } else {
            // Unix: use 'which' command
            $output = [];
            $return = 0;
            @exec('which ' . escapeshellarg($command) . ' 2>/dev/null', $output, $return);
            return $return === 0 && !empty($output);
        }
    }

    /**
     * Get MySQL version from config (for WAMP path)
     */
    protected function getMysqlVersion(): string
    {
        // Try to detect MySQL version, default to 8.0
        return '8.0';
    }

    /**
     * Build mysqldump command
     */
    protected function buildMysqldumpCommand(string $mysqlDump, string $host, string $port, string $username, string $password, string $database, string $outputPath): string
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows command
            return sprintf('"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > "%s"',
                $mysqlDump,
                escapeshellarg($host),
                escapeshellarg((string)$port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                $outputPath
            );
        } else {
            // Unix command
            return sprintf('%s --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($mysqlDump),
                escapeshellarg($host),
                escapeshellarg((string)$port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($outputPath)
            );
        }
    }

    /**
     * Backup using Laravel DB connection (fallback method)
     */
    protected function backupUsingLaravel(string $outputPath, string $database): bool
    {
        $handle = null;
        try {
            // Ensure directory exists
            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    \Log::error('Failed to create backup directory: ' . $dir);
                    return false;
                }
            }
            
            $handle = fopen($outputPath, 'w');
            if (!$handle) {
                \Log::error('Failed to open backup file for writing: ' . $outputPath);
                return false;
            }

            // Write header with password protection information
            fwrite($handle, "-- OfisiLink Database Backup\n");
            fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
            fwrite($handle, "-- Database: {$database}\n");
            fwrite($handle, "-- Password Protection: Ofisilink (case-sensitive)\n");
            fwrite($handle, "-- This backup requires database password: Ofisilink\n");
            fwrite($handle, "-- IMPORTANT: Keep this password secure and confidential\n\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
            fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
            fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
            fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
            fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n\n");

            // Test database connection
            try {
                \DB::connection()->getPdo();
            } catch (\Exception $e) {
                \Log::error('Database connection failed during backup: ' . $e->getMessage());
                fclose($handle);
                @unlink($outputPath);
                return false;
            }

            // Get all tables
            $tables = \DB::select("SHOW TABLES");
            if (empty($tables)) {
                \Log::warning('No tables found in database: ' . $database);
                fclose($handle);
                return file_exists($outputPath) && filesize($outputPath) > 0;
            }
            
            $tableKey = 'Tables_in_' . $database;

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                try {
                    // Get table structure
                    fwrite($handle, "\n-- Table structure for table `{$tableName}`\n");
                    fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                    
                    $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
                    if (!empty($createTable)) {
                        $createKey = 'Create Table';
                        fwrite($handle, $createTable[0]->$createKey . ";\n\n");
                    }

                    // Get table data
                    fwrite($handle, "-- Dumping data for table `{$tableName}`\n");
                    $rows = \DB::table($tableName)->get();
                    
                    if ($rows->count() > 0) {
                        fwrite($handle, "INSERT INTO `{$tableName}` VALUES\n");
                        $values = [];
                        foreach ($rows as $row) {
                            $rowArray = (array)$row;
                            $rowValues = [];
                            foreach ($rowArray as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } elseif (is_numeric($value)) {
                                    $rowValues[] = $value;
                                } else {
                                    // Properly escape SQL strings
                                    $rowValues[] = "'" . str_replace(['\\', "'", "\n", "\r"], ['\\\\', "\\'", "\\n", "\\r"], $value) . "'";
                                }
                            }
                            $values[] = '(' . implode(',', $rowValues) . ')';
                        }
                        fwrite($handle, implode(",\n", $values) . ";\n\n");
                    }
                } catch (\Exception $tableError) {
                    \Log::warning("Error backing up table {$tableName}: " . $tableError->getMessage());
                    // Continue with next table
                    continue;
                }
            }

            // Write footer
            fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
            fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
            fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

            fclose($handle);
            $handle = null;
            
            // Verify file was created and has content
            if (!file_exists($outputPath)) {
                \Log::error('Backup file was not created: ' . $outputPath);
                return false;
            }
            
            $fileSize = filesize($outputPath);
            if ($fileSize === 0) {
                \Log::error('Backup file is empty: ' . $outputPath);
                @unlink($outputPath);
                return false;
            }
            
            return true;
        } catch (\Throwable $e) {
            \Log::error('Laravel backup method failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($handle && is_resource($handle)) {
                fclose($handle);
            }
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            return false;
        }
    }

    /**
     * Send notifications (SMS and Email) - always called
     */
    protected function sendNotifications(NotificationService $notifier, $now, bool $success, ?string $sqlPath, ?string $errorMessage, ?string $fullFilePath = null, ?array $offSystemStorageInfo = null): void
    {
        try {
            $admins = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'System Admin');
            })->where('is_active', true)->get();

            if ($success && $sqlPath) {
                $downloadUrl = route('admin.system.backup.download', ['file' => basename($sqlPath)], false);
                $isZip = str_ends_with($sqlPath, '.zip');
                $passwordNote = $isZip ? 'Password-protected ZIP (Password: Ofisilink)' : 'SQL file (Database Password: Ofisilink)';
                $message = 'Backup for OfisiLink System completed at ' . $now->toDateTimeString() . '. ' . $passwordNote . '. Download: ' . url($downloadUrl);
                $subject = 'OfisiLink System Backup Completed - ' . $now->format('Y-m-d H:i:s');
                // Use the provided full file path, or fallback to constructing it
                $fullPath = $fullFilePath ?? storage_path('app/backups/' . basename($sqlPath));
            } else {
                $message = 'Backup for OfisiLink System FAILED at ' . $now->toDateTimeString() . '. Error: ' . ($errorMessage ?? 'Unknown error');
                $subject = 'OfisiLink System Backup FAILED - ' . $now->format('Y-m-d H:i:s');
                $downloadUrl = null;
                $fullPath = null;
            }

            // Send SMS notification to all admins
            foreach ($admins as $admin) {
                if ($admin->mobile || $admin->phone) {
                    try {
                        $notifier->sendSMS($admin->mobile ?? $admin->phone, $message);
                    } catch (\Throwable $smsError) {
                        \Log::error('Backup SMS failed for admin ' . $admin->id . ': ' . $smsError->getMessage());
                    }
                }
            }

            // Initialize EmailService
            $emailService = new EmailService();
            
            // Collect all email recipients
            $emailRecipients = [];
            foreach ($admins as $admin) {
                if ($admin->email) {
                    $emailRecipients[] = $admin->email;
                }
            }
            // Always include davidngungila@gmail.com
            $emailRecipients[] = 'davidngungila@gmail.com';
            $emailRecipients = array_unique($emailRecipients);
            
            // Prepare email content
            if ($success && $fullPath && file_exists($fullPath)) {
                // Success: Prepare success email
                $isZip = str_ends_with($sqlPath, '.zip');
                $emailBody = View::make('emails.backup-completed', [
                    'admin' => (object)['name' => 'Administrator'],
                    'backup_file' => basename($sqlPath),
                    'completed_at' => $now->toDateTimeString(),
                    'download_url' => $downloadUrl ? url($downloadUrl) : null,
                    'password' => 'Ofisilink',
                    'is_zip' => $isZip,
                    'file_size' => $this->formatBytes(filesize($fullPath)),
                    'off_system_storage' => $offSystemStorageInfo,
                ])->render();
                
                // Send to all recipients with ZIP file attached
                foreach ($emailRecipients as $recipient) {
                    try {
                        // Attach the backup file (ZIP or SQL) to the email
                        $attachmentPath = $fullPath && file_exists($fullPath) ? $fullPath : null;
                        $attachmentName = basename($sqlPath);
                        
                        $emailService->send(
                            $recipient,
                            $subject,
                            $emailBody,
                            $attachmentPath, // Attach the ZIP/SQL file
                            $attachmentName
                        );
                        \Log::info('Backup success email with attachment sent to: ' . $recipient, [
                            'attachment' => $attachmentPath,
                            'file_size' => $attachmentPath ? filesize($attachmentPath) : 0
                        ]);
                    } catch (\Throwable $emailError) {
                        \Log::error('Backup email failed for ' . $recipient . ': ' . $emailError->getMessage());
                    }
                }
            } else {
                // Failure: Prepare failure email
                $emailBody = View::make('emails.backup-failed', [
                    'admin' => (object)['name' => 'Administrator'],
                    'error_message' => $errorMessage ?? 'Unknown error occurred',
                    'failed_at' => $now->toDateTimeString(),
                ])->render();
                
                // Send to all recipients
                foreach ($emailRecipients as $recipient) {
                    try {
                        $emailService->send(
                            $recipient,
                            $subject,
                            $emailBody
                        );
                        \Log::info('Backup failure email sent to: ' . $recipient);
                    } catch (\Throwable $emailError) {
                        \Log::error('Backup failure email failed for ' . $recipient . ': ' . $emailError->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Backup notification process failed: ' . $e->getMessage());
        }
    }

    protected function runShell(string $command): int
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            $proc = proc_open(['cmd', '/C', $command], [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        } else {
            $proc = proc_open($command, [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        }
        if (!\is_resource($proc)) {
            return 1;
        }
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        foreach ($pipes as $p) { fclose($p); }
        return proc_close($proc);
    }

    protected function zipWithPassword(string $sourceFile, string $zipFile, string $password): bool
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            $name = basename($sourceFile);
            if (!$zip->addFile($sourceFile, $name)) {
                $zip->close();
                return false;
            }
            if (defined('ZipArchive::EM_AES_256')) {
                $zip->setPassword($password);
                $zip->setEncryptionName($name, \ZipArchive::EM_AES_256);
            } else {
                // Fallback: try external 7z/zip
                $zip->close();
                return $this->zipExternal($sourceFile, $zipFile, $password);
            }
            $zip->close();
            return true;
        }
        return $this->zipExternal($sourceFile, $zipFile, $password);
    }

    /**
     * Create password-protected ZIP with database backup and storage files
     */
    protected function zipWithPasswordAndStorage(string $sourceFile, string $zipFile, string $password, string $storagePath, string $excludeBackupDir): bool
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                \Log::error('Failed to open ZIP file for writing: ' . $zipFile);
                return false;
            }

            // Add database backup file
            $dbName = basename($sourceFile);
            if (!$zip->addFile($sourceFile, 'database/' . $dbName)) {
                \Log::error('Failed to add database file to ZIP');
                $zip->close();
                return false;
            }

            // Add storage files (excluding backups directory to avoid circular issues)
            $this->addStorageFilesToZip($zip, $storagePath, $excludeBackupDir);

            // Set password encryption for all files
            $fileCount = $zip->numFiles;
            if (defined('ZipArchive::EM_AES_256')) {
                $zip->setPassword($password);
                // Encrypt all files in the ZIP
                for ($i = 0; $i < $fileCount; $i++) {
                    $zip->setEncryptionIndex($i, \ZipArchive::EM_AES_256);
                }
            } else {
                // Fallback: try external 7z/zip
                $zip->close();
                return $this->zipExternalWithStorage($sourceFile, $zipFile, $password, $storagePath, $excludeBackupDir);
            }

            $zip->close();
            \Log::info('ZIP created with database and storage files', [
                'zip_file' => $zipFile,
                'files_count' => $fileCount
            ]);
            return true;
        }
        return $this->zipExternalWithStorage($sourceFile, $zipFile, $password, $storagePath, $excludeBackupDir);
    }

    /**
     * Recursively add storage files to ZIP (excluding backups directory)
     */
    protected function addStorageFilesToZip(\ZipArchive $zip, string $storagePath, string $excludeBackupDir): void
    {
        $excludePath = $storagePath . DIRECTORY_SEPARATOR . $excludeBackupDir;
        
        if (!is_dir($storagePath)) {
            \Log::warning('Storage path does not exist: ' . $storagePath);
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $addedCount = 0;
        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            
            // Skip backups directory to avoid including backup files in backup
            if (strpos($filePath, $excludePath) === 0) {
                continue;
            }

            // Skip if it's a directory
            if ($file->isDir()) {
                continue;
            }

            // Get relative path for ZIP entry
            $relativePath = 'storage/' . str_replace($storagePath . DIRECTORY_SEPARATOR, '', $filePath);
            $relativePath = str_replace('\\', '/', $relativePath); // Normalize path separators

            // Add file to ZIP
            if ($zip->addFile($filePath, $relativePath)) {
                $addedCount++;
            } else {
                \Log::warning('Failed to add file to ZIP: ' . $filePath);
            }
        }

        \Log::info('Added storage files to ZIP', ['count' => $addedCount]);
    }

    /**
     * External ZIP creation with storage files (fallback method)
     */
    protected function zipExternalWithStorage(string $sourceFile, string $zipFile, string $password, string $storagePath, string $excludeBackupDir): bool
    {
        // Create a temporary directory to organize files
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_' . uniqid();
        @mkdir($tempDir, 0755, true);
        
        // Copy database file
        $dbName = basename($sourceFile);
        $tempDbPath = $tempDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $dbName;
        @mkdir(dirname($tempDbPath), 0755, true);
        copy($sourceFile, $tempDbPath);

        // Copy storage files (excluding backups)
        $excludePath = $storagePath . DIRECTORY_SEPARATOR . $excludeBackupDir;
        $tempStoragePath = $tempDir . DIRECTORY_SEPARATOR . 'storage';
        $this->copyStorageFiles($storagePath, $tempStoragePath, $excludePath);

        // Try 7z first
        $cmd7z = sprintf('7z a -tzip -p%s -mem=AES256 -r %s %s',
            escapeshellarg($password),
            escapeshellarg($zipFile),
            escapeshellarg($tempDir . DIRECTORY_SEPARATOR . '*')
        );
        $exit = $this->runShell($cmd7z);
        
        if ($exit === 0 && file_exists($zipFile)) {
            // Cleanup temp directory
            $this->deleteDirectory($tempDir);
            return true;
        }

        // Try zip (Info-ZIP) with password
        $cmdZip = sprintf('zip -r -P %s %s %s',
            escapeshellarg($password),
            escapeshellarg($zipFile),
            escapeshellarg($tempDir)
        );
        $exit = $this->runShell($cmdZip);
        
        // Cleanup temp directory
        $this->deleteDirectory($tempDir);
        
        return $exit === 0 && file_exists($zipFile);
    }

    /**
     * Copy storage files to temporary directory (excluding backups)
     */
    protected function copyStorageFiles(string $source, string $dest, string $excludePath): void
    {
        if (!is_dir($source)) {
            return;
        }

        @mkdir($dest, 0755, true);
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            
            // Skip backups directory
            if (strpos($filePath, $excludePath) === 0) {
                continue;
            }

            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $filePath);
            $destPath = $dest . DIRECTORY_SEPARATOR . $relativePath;

            if ($file->isDir()) {
                @mkdir($destPath, 0755, true);
            } else {
                @mkdir(dirname($destPath), 0755, true);
                @copy($filePath, $destPath);
            }
        }
    }

    /**
     * Recursively delete directory
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    protected function zipExternal(string $sourceFile, string $zipFile, string $password): bool
    {
        // Try 7z first
        $cmd7z = sprintf('7z a -tzip -p%s -mem=AES256 %s %s',
            escapeshellarg($password),
            escapeshellarg($zipFile),
            escapeshellarg($sourceFile)
        );
        $exit = $this->runShell($cmd7z);
        if ($exit === 0 && file_exists($zipFile)) {
            return true;
        }

        // Try zip (Info-ZIP) with password (legacy encryption)
        $cmdZip = sprintf('zip -j -P %s %s %s',
            escapeshellarg($password),
            escapeshellarg($zipFile),
            escapeshellarg($sourceFile)
        );
        $exit = $this->runShell($cmdZip);
        return $exit === 0 && file_exists($zipFile);
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

    /**
     * Store backup file off-system (cloud storage)
     * Supports: S3, FTP, SFTP, Google Drive (via API)
     * 
     * @param string $filePath Full path to backup file
     * @param string $baseName Base name of the backup file
     * @return array|null Storage information or null if failed
     */
    protected function storeBackupOffSystem(string $filePath, string $baseName): ?array
    {
        $storageInfo = [];
        $storedLocations = [];

        // 1. Try AWS S3 storage
        if ($this->storeToS3($filePath, $baseName, $storageInfo)) {
            $storedLocations[] = 'S3';
        }

        // 2. Try FTP storage
        if ($this->storeToFTP($filePath, $baseName, $storageInfo)) {
            $storedLocations[] = 'FTP';
        }

        // 3. Try SFTP storage
        if ($this->storeToSFTP($filePath, $baseName, $storageInfo)) {
            $storedLocations[] = 'SFTP';
        }

        // 4. Try Google Drive (if configured)
        if ($this->storeToGoogleDrive($filePath, $baseName, $storageInfo)) {
            $storedLocations[] = 'Google Drive';
        }

        if (empty($storedLocations)) {
            \Log::warning('Backup file not stored off-system. All storage methods failed or not configured.', [
                'file' => $filePath
            ]);
            return null;
        }

        \Log::info('Backup stored off-system successfully', [
            'locations' => $storedLocations,
            'file' => $baseName
        ]);

        return [
            'locations' => $storedLocations,
            'details' => $storageInfo,
            'stored_at' => now()->toDateTimeString()
        ];
    }

    /**
     * Store backup to AWS S3
     */
    protected function storeToS3(string $filePath, string $baseName, array &$storageInfo): bool
    {
        try {
            // Check if S3 is configured
            if (!env('AWS_ACCESS_KEY_ID') || !env('AWS_SECRET_ACCESS_KEY') || !env('AWS_BUCKET')) {
                return false;
            }

            $s3Disk = Storage::disk('s3');
            $remotePath = 'backups/' . $baseName . (str_ends_with($filePath, '.zip') ? '.zip' : '.sql');

            // Read file content
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                throw new \Exception('Failed to read file for S3 upload');
            }

            // Upload file to S3
            $uploaded = $s3Disk->put($remotePath, $fileContent);
            
            if (!$uploaded) {
                throw new \Exception('S3 upload returned false');
            }

            // Get file URL
            $fileUrl = null;
            try {
                $fileUrl = $s3Disk->url($remotePath);
            } catch (\Throwable $e) {
                // URL generation might fail, but upload was successful
                \Log::debug('Could not generate S3 URL: ' . $e->getMessage());
            }

            $storageInfo['s3'] = [
                'bucket' => env('AWS_BUCKET'),
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'path' => $remotePath,
                'url' => $fileUrl,
                'key' => $remotePath
            ];

            \Log::info('Backup stored to S3 successfully', [
                'path' => $remotePath,
                'bucket' => env('AWS_BUCKET')
            ]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('Failed to store backup to S3: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store backup to FTP server
     */
    protected function storeToFTP(string $filePath, string $baseName, array &$storageInfo): bool
    {
        try {
            // Check if FTP extension is available
            if (!function_exists('ftp_connect')) {
                \Log::debug('FTP extension not available. FTP storage skipped.');
                return false;
            }

            // Check if FTP is configured
            $ftpHost = env('BACKUP_FTP_HOST');
            $ftpUser = env('BACKUP_FTP_USERNAME');
            $ftpPass = env('BACKUP_FTP_PASSWORD');
            $ftpPort = env('BACKUP_FTP_PORT', 21);
            $ftpPath = env('BACKUP_FTP_PATH', '/backups');

            if (!$ftpHost || !$ftpUser || !$ftpPass) {
                return false;
            }

            // Connect to FTP
            $connection = ftp_connect($ftpHost, $ftpPort);
            if (!$connection) {
                throw new \Exception('Failed to connect to FTP server');
            }

            if (!ftp_login($connection, $ftpUser, $ftpPass)) {
                ftp_close($connection);
                throw new \Exception('Failed to login to FTP server');
            }

            // Enable passive mode
            ftp_pasv($connection, true);

            // Create remote directory if it doesn't exist
            $remotePath = rtrim($ftpPath, '/') . '/' . $baseName . (str_ends_with($filePath, '.zip') ? '.zip' : '.sql');

            // Upload file
            if (!ftp_put($connection, $remotePath, $filePath, FTP_BINARY)) {
                ftp_close($connection);
                throw new \Exception('Failed to upload file to FTP');
            }

            ftp_close($connection);

            $storageInfo['ftp'] = [
                'host' => $ftpHost,
                'path' => $remotePath
            ];

            \Log::info('Backup stored to FTP', ['path' => $remotePath]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('Failed to store backup to FTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store backup to SFTP server
     */
    protected function storeToSFTP(string $filePath, string $baseName, array &$storageInfo): bool
    {
        try {
            // Check if SSH2 extension is available
            if (!extension_loaded('ssh2')) {
                \Log::debug('SSH2 extension not available. SFTP storage skipped.');
                return false;
            }

            // Check if SFTP is configured
            $sftpHost = env('BACKUP_SFTP_HOST');
            $sftpUser = env('BACKUP_SFTP_USERNAME');
            $sftpPass = env('BACKUP_SFTP_PASSWORD');
            $sftpPort = env('BACKUP_SFTP_PORT', 22);
            $sftpPath = env('BACKUP_SFTP_PATH', '/backups');
            $sftpKey = env('BACKUP_SFTP_KEY'); // Optional: SSH key path

            if (!$sftpHost || !$sftpUser) {
                return false;
            }

            // Connect to SFTP
            if ($sftpKey && file_exists($sftpKey)) {
                // Use SSH key authentication
                $connection = ssh2_connect($sftpHost, $sftpPort);
                if (!$connection) {
                    throw new \Exception('Failed to connect to SFTP server');
                }
                if (!ssh2_auth_pubkey_file($connection, $sftpUser, $sftpKey . '.pub', $sftpKey, $sftpPass)) {
                    throw new \Exception('Failed to authenticate with SSH key');
                }
            } else {
                // Use password authentication
                $connection = ssh2_connect($sftpHost, $sftpPort);
                if (!$connection) {
                    throw new \Exception('Failed to connect to SFTP server');
                }
                if (!ssh2_auth_password($connection, $sftpUser, $sftpPass)) {
                    throw new \Exception('Failed to authenticate with password');
                }
            }

            $sftp = ssh2_sftp($connection);
            if (!$sftp) {
                throw new \Exception('Failed to initialize SFTP');
            }

            // Create remote directory if it doesn't exist
            $remoteDir = rtrim($sftpPath, '/');
            $remotePath = $remoteDir . '/' . $baseName . (str_ends_with($filePath, '.zip') ? '.zip' : '.sql');

            // Upload file
            $stream = fopen("ssh2.sftp://{$sftp}{$remotePath}", 'w');
            if (!$stream) {
                throw new \Exception('Failed to open remote file for writing');
            }

            $localStream = fopen($filePath, 'r');
            if (!$localStream) {
                fclose($stream);
                throw new \Exception('Failed to open local file for reading');
            }

            stream_copy_to_stream($localStream, $stream);
            fclose($stream);
            fclose($localStream);

            $storageInfo['sftp'] = [
                'host' => $sftpHost,
                'path' => $remotePath
            ];

            \Log::info('Backup stored to SFTP', ['path' => $remotePath]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('Failed to store backup to SFTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store backup to Google Drive (via API)
     * Uses Google Drive API v3 with OAuth2 authentication
     */
    protected function storeToGoogleDrive(string $filePath, string $baseName, array &$storageInfo): bool
    {
        try {
            // Check if Google Drive is configured
            $googleClientId = env('GOOGLE_DRIVE_CLIENT_ID');
            $googleClientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');
            $googleRefreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');
            $googleFolderId = env('GOOGLE_DRIVE_FOLDER_ID', 'root');

            if (!$googleClientId || !$googleClientSecret || !$googleRefreshToken) {
                return false;
            }

            // Get access token using refresh token
            $accessToken = $this->getGoogleDriveAccessToken($googleClientId, $googleClientSecret, $googleRefreshToken);
            if (!$accessToken) {
                throw new \Exception('Failed to obtain Google Drive access token');
            }

            // Upload file to Google Drive
            $fileName = $baseName . (str_ends_with($filePath, '.zip') ? '.zip' : '.sql');
            $fileId = $this->uploadToGoogleDrive($accessToken, $filePath, $fileName, $googleFolderId);

            if (!$fileId) {
                throw new \Exception('Failed to upload file to Google Drive');
            }

            // Get file info
            $fileInfo = $this->getGoogleDriveFileInfo($accessToken, $fileId);
            
            $storageInfo['google_drive'] = [
                'file_id' => $fileId,
                'file_name' => $fileName,
                'folder_id' => $googleFolderId,
                'web_view_link' => $fileInfo['webViewLink'] ?? null,
                'web_content_link' => $fileInfo['webContentLink'] ?? null,
            ];

            \Log::info('Backup stored to Google Drive', [
                'file_id' => $fileId,
                'file_name' => $fileName
            ]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('Failed to store backup to Google Drive: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Google Drive access token using refresh token
     */
    protected function getGoogleDriveAccessToken(string $clientId, string $clientSecret, string $refreshToken): ?string
    {
        try {
            $url = 'https://oauth2.googleapis.com/token';
            
            $data = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token'
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                \Log::error('Google Drive token refresh failed', [
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return null;
            }

            $tokenData = json_decode($response, true);
            return $tokenData['access_token'] ?? null;
        } catch (\Throwable $e) {
            \Log::error('Error getting Google Drive access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload file to Google Drive
     */
    protected function uploadToGoogleDrive(string $accessToken, string $filePath, string $fileName, string $folderId): ?string
    {
        try {
            // First, get file metadata
            $mimeType = mime_content_type($filePath);
            if (!$mimeType) {
                $mimeType = str_ends_with($filePath, '.zip') ? 'application/zip' : 'application/sql';
            }

            // Create file metadata
            $metadata = [
                'name' => $fileName,
                'parents' => [$folderId]
            ];

            // Step 1: Create file metadata
            $url = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';
            
            $boundary = uniqid();
            $delimiter = '-------------' . $boundary;

            // Read file content
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                throw new \Exception('Failed to read file: ' . $filePath);
            }

            // Build multipart request body
            $body = '';
            $body .= '--' . $delimiter . "\r\n";
            $body .= 'Content-Type: application/json; charset=UTF-8' . "\r\n\r\n";
            $body .= json_encode($metadata) . "\r\n";
            $body .= '--' . $delimiter . "\r\n";
            $body .= 'Content-Type: ' . $mimeType . "\r\n\r\n";
            $body .= $fileContent . "\r\n";
            $body .= '--' . $delimiter . '--';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: multipart/related; boundary=' . $delimiter,
                'Content-Length: ' . strlen($body)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new \Exception('cURL error: ' . $error);
            }

            if ($httpCode !== 200) {
                \Log::error('Google Drive upload failed', [
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return null;
            }

            $fileData = json_decode($response, true);
            return $fileData['id'] ?? null;
        } catch (\Throwable $e) {
            \Log::error('Error uploading to Google Drive: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Google Drive file information
     */
    protected function getGoogleDriveFileInfo(string $accessToken, string $fileId): array
    {
        try {
            $url = 'https://www.googleapis.com/drive/v3/files/' . $fileId . '?fields=id,name,webViewLink,webContentLink,size,createdTime';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return json_decode($response, true) ?? [];
            }

            return [];
        } catch (\Throwable $e) {
            \Log::error('Error getting Google Drive file info: ' . $e->getMessage());
            return [];
        }
    }
}







