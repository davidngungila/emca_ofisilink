<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationProvider;
use App\Services\EmailService;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email=davidngungila@gmail.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testEmail = $this->argument('email');
        
        $this->info("Testing email sending to: {$testEmail}");
        $this->newLine();
        
        try {
            // Get primary email provider
            $emailProvider = NotificationProvider::getPrimary('email');
            
            if (!$emailProvider) {
                $this->error('❌ No primary email provider found!');
                $this->info('Please run: php artisan db:seed --class=EmailProviderSeeder');
                return 1;
            }
            
            $this->info("Using provider: {$emailProvider->name}");
            $this->info("Host: {$emailProvider->mail_host}");
            $this->info("Port: {$emailProvider->mail_port}");
            $this->info("Username: {$emailProvider->mail_username}");
            $this->info("Encryption: {$emailProvider->mail_encryption}");
            $this->newLine();
            
            $this->info("Sending test email...");
            
            // Test email using provider
            $testResult = $emailProvider->testEmail($testEmail);
            
            if ($testResult['success']) {
                $this->info("✅ SUCCESS! Test email sent successfully to {$testEmail}");
                $this->info("Message: {$testResult['message']}");
                return 0;
            } else {
                $this->error("❌ FAILED! Could not send test email");
                $this->error("Error: {$testResult['message']}");
                if (isset($testResult['error'])) {
                    $this->error("Details: {$testResult['error']}");
                }
                if (isset($testResult['suggestion'])) {
                    $this->warn("Suggestion: {$testResult['suggestion']}");
                }
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ EXCEPTION: {$e->getMessage()}");
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}


