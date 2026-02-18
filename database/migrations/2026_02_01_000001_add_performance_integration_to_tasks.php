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
        // Add financial year and performance linking to main_tasks
        Schema::table('main_tasks', function (Blueprint $table) {
            $table->integer('financial_year')->nullable()->after('created_by');
            $table->unsignedBigInteger('organizational_goal_id')->nullable()->after('financial_year');
            $table->unsignedBigInteger('assessment_id')->nullable()->after('organizational_goal_id');
            $table->enum('link_type', ['direct', 'supporting', 'operational', 'none'])->default('none')->after('assessment_id');
            $table->decimal('performance_weight', 5, 2)->nullable()->after('link_type')->comment('Weight percentage for performance contribution');
            
            $table->foreign('organizational_goal_id')->references('id')->on('organizational_goals')->onDelete('set null');
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('set null');
        });

        // Add financial year to task_activities
        Schema::table('task_activities', function (Blueprint $table) {
            $table->integer('financial_year')->nullable()->after('depends_on_id');
            $table->unsignedBigInteger('assessment_activity_id')->nullable()->after('financial_year');
            
            $table->foreign('assessment_activity_id')->references('id')->on('assessment_activities')->onDelete('set null');
        });

        // Add financial year and performance sync info to activity_reports
        Schema::table('activity_reports', function (Blueprint $table) {
            $table->integer('financial_year')->nullable()->after('status');
            $table->boolean('synced_to_performance')->default(false)->after('financial_year');
            $table->timestamp('synced_at')->nullable()->after('synced_to_performance');
            $table->unsignedBigInteger('assessment_progress_report_id')->nullable()->after('synced_at');
            
            $table->foreign('assessment_progress_report_id')->references('id')->on('assessment_progress_reports')->onDelete('set null');
        });

        // Create task_performance_links table for many-to-many relationships
        Schema::create('task_performance_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_activity_id');
            $table->unsignedBigInteger('assessment_activity_id');
            $table->enum('link_type', ['direct', 'supporting', 'operational'])->default('direct');
            $table->decimal('weight', 5, 2)->default(0)->comment('Weight percentage');
            $table->integer('financial_year');
            $table->boolean('is_active')->default(true);
            $table->timestamp('linked_at')->useCurrent();
            $table->unsignedBigInteger('linked_by')->nullable();
            
            $table->foreign('task_activity_id')->references('id')->on('task_activities')->onDelete('cascade');
            $table->foreign('assessment_activity_id')->references('id')->on('assessment_activities')->onDelete('cascade');
            $table->foreign('linked_by')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['task_activity_id', 'assessment_activity_id', 'financial_year'], 'unique_task_perf_link');
            $table->index('financial_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_performance_links');
        
        Schema::table('activity_reports', function (Blueprint $table) {
            $table->dropForeign(['assessment_progress_report_id']);
            $table->dropColumn(['financial_year', 'synced_to_performance', 'synced_at', 'assessment_progress_report_id']);
        });
        
        Schema::table('task_activities', function (Blueprint $table) {
            $table->dropForeign(['assessment_activity_id']);
            $table->dropColumn(['financial_year', 'assessment_activity_id']);
        });
        
        Schema::table('main_tasks', function (Blueprint $table) {
            $table->dropForeign(['organizational_goal_id']);
            $table->dropForeign(['assessment_id']);
            $table->dropColumn(['financial_year', 'organizational_goal_id', 'assessment_id', 'link_type', 'performance_weight']);
        });
    }
};





