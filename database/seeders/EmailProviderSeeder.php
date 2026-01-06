<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationProvider;

class EmailProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update primary email provider with Gmail SMTP configuration
        // Email: david.ngungila@emca.tech
        // App Password: mvrv zdxd iqqx xvkv (stored without spaces)
        
        $emailProvider = NotificationProvider::updateOrCreate(
            [
                'type' => 'email',
                'mail_from_address' => 'david.ngungila@emca.tech',
            ],
            [
                'name' => 'Primary Gmail SMTP (EmCa Tech)',
                'type' => 'email',
                'is_active' => true,
                'is_primary' => true,
                'priority' => 100,
                'mailer_type' => 'smtp',
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => 587, // Gmail SMTP port (TLS)
                'mail_username' => 'david.ngungila@emca.tech',
                'mail_password' => 'mvrvzdxdiqqxxvkv', // App password without spaces
                'mail_encryption' => 'tls',
                'mail_from_address' => 'david.ngungila@emca.tech',
                'mail_from_name' => 'OfisiLink System',
                'description' => 'Primary Gmail SMTP provider for system emails (EmCa Tech)',
                'last_test_status' => null,
                'last_tested_at' => null,
            ]
        );

        // Ensure this is set as primary (unset others)
        $emailProvider->setAsPrimary();

        $this->command->info('✅ Email provider seeded successfully!');
        $this->command->info('   Email: david.ngungila@emca.tech');
        $this->command->info('   Host: smtp.gmail.com');
        $this->command->info('   Port: 587 (TLS)');
        $this->command->info('   Encryption: TLS');
        $this->command->info('   Status: Active & Primary');
    }
}

