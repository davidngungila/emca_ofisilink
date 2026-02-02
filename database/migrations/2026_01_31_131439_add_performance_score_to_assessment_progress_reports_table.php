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
        Schema::table('assessment_progress_reports', function (Blueprint $table) {
            $table->decimal('performance_score', 5, 2)->nullable()->after('progress_text');
            $table->string('source')->default('manual')->after('performance_score'); // manual, task_sync, auto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_progress_reports', function (Blueprint $table) {
            $table->dropColumn(['performance_score', 'source']);
        });
    }
};
