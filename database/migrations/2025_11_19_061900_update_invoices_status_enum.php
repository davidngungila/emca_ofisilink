<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only modify if table exists (table is created in 2025_12_03_000010_create_invoices_table)
        if (Schema::hasTable('invoices')) {
            // For MySQL, we need to modify the ENUM column
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Pending for Approval', 'Pending CEO Approval', 'Approved', 'Rejected', 'Sent', 'Partially Paid', 'Paid', 'Cancelled', 'Overdue') DEFAULT 'Draft'");
        }
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Draft', 'Sent', 'Partially Paid', 'Paid', 'Cancelled', 'Overdue') DEFAULT 'Draft'");
    }
};



