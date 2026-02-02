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
        Schema::create('task_performance_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_activity_id');
            $table->unsignedBigInteger('assessment_activity_id');
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->timestamp('synced_at');
            $table->unsignedBigInteger('synced_by')->nullable();
            $table->text('sync_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('task_activity_id')->references('id')->on('task_activities')->onDelete('cascade');
            $table->foreign('assessment_activity_id')->references('id')->on('assessment_activities')->onDelete('cascade');
            $table->foreign('synced_by')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['task_activity_id', 'assessment_activity_id'], 'unique_task_assessment_sync');
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_performance_syncs');
    }
};
