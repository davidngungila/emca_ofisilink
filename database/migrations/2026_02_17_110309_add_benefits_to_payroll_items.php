<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('house_benefit_amount', 15, 2)->default(0)->after('allowance_amount');
            $table->decimal('hardship_benefit_amount', 15, 2)->default(0)->after('house_benefit_amount');
            $table->decimal('other_benefits_amount', 15, 2)->default(0)->after('hardship_benefit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['house_benefit_amount', 'hardship_benefit_amount', 'other_benefits_amount']);
        });
    }
};
