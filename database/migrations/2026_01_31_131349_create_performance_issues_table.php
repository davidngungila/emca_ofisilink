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
        Schema::create('performance_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id')->nullable(); // Link to individual assessment
            $table->unsignedBigInteger('performance_measurement_id')->nullable(); // Link to measurement
            $table->unsignedBigInteger('user_id')->nullable(); // Employee with issue
            $table->unsignedBigInteger('department_id')->nullable(); // Department issue
            $table->string('issue_type'); // e.g., 'underperformance', 'attendance', 'quality', 'deadline'
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->date('identified_date');
            $table->date('target_resolution_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->json('action_plan')->nullable(); // Steps to resolve
            $table->unsignedBigInteger('assigned_to')->nullable(); // Who is handling it
            $table->unsignedBigInteger('identified_by')->nullable();
            $table->timestamps();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('performance_measurement_id')->references('id')->on('performance_measurements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('identified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'severity', 'identified_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_issues');
    }
};
