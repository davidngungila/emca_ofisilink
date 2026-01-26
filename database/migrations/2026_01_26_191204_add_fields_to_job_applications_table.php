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
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'current_address')) {
                $table->text('current_address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('job_applications', 'cover_letter')) {
                $table->text('cover_letter')->nullable()->after('current_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (Schema::hasColumn('job_applications', 'current_address')) {
                $table->dropColumn('current_address');
            }
            if (Schema::hasColumn('job_applications', 'cover_letter')) {
                $table->dropColumn('cover_letter');
            }
        });
    }
};
