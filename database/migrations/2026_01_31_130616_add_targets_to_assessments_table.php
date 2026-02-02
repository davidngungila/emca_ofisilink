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
        Schema::table('assessments', function (Blueprint $table) {
            $table->json('targets')->nullable()->after('description'); // Performance targets
            $table->unsignedBigInteger('task_activity_id')->nullable()->after('targets'); // Link to Task Management
            $table->date('target_start_date')->nullable()->after('task_activity_id');
            $table->date('target_end_date')->nullable()->after('target_start_date');
            $table->enum('target_type', ['quantitative', 'qualitative', 'mixed'])->nullable()->after('target_end_date');
            
            $table->foreign('task_activity_id')->references('id')->on('task_activities')->onDelete('set null');
        });
        
        Schema::table('assessment_activities', function (Blueprint $table) {
            $table->json('targets')->nullable()->after('description'); // Activity-specific targets
            $table->unsignedBigInteger('task_activity_id')->nullable()->after('targets'); // Link to Task Activity
            $table->date('target_start_date')->nullable()->after('task_activity_id');
            $table->date('target_end_date')->nullable()->after('target_start_date');
            
            $table->foreign('task_activity_id')->references('id')->on('task_activities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['task_activity_id']);
            $table->dropColumn(['targets', 'task_activity_id', 'target_start_date', 'target_end_date', 'target_type']);
        });
        
        Schema::table('assessment_activities', function (Blueprint $table) {
            $table->dropForeign(['task_activity_id']);
            $table->dropColumn(['targets', 'task_activity_id', 'target_start_date', 'target_end_date']);
        });
    }
};
