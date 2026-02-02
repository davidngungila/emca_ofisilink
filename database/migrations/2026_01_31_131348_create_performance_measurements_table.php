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
        Schema::create('performance_measurements', function (Blueprint $table) {
            $table->id();
            $table->enum('measurement_type', ['individual', 'department', 'organization'])->default('individual');
            $table->enum('period_type', ['monthly', 'quarterly', 'semi_annual', 'annual', 'custom'])->default('monthly');
            $table->date('period_start');
            $table->date('period_end');
            $table->year('year');
            $table->integer('month')->nullable(); // For monthly/quarterly
            $table->integer('quarter')->nullable(); // For quarterly
            $table->unsignedBigInteger('user_id')->nullable(); // For individual
            $table->unsignedBigInteger('department_id')->nullable(); // For department
            $table->unsignedBigInteger('performance_criteria_id')->nullable(); // Which criteria framework used
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->json('scores_by_criteria')->nullable(); // Scores for each criterion
            $table->json('metrics')->nullable(); // Additional metrics
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['draft', 'finalized', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            //$table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            //$table->foreign('performance_criteria_id')->references('id')->on('performance_criterias')->onDelete('set null');
            //$table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            //$table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['measurement_type', 'period_type', 'year', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_measurements');
    }
};
