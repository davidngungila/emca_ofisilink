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
        Schema::create('institutional_positions', function (Blueprint $table) {
            $table->id();
            $table->string('position_title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('salary_structure_id')->nullable(); // Link to salary structure
            $table->integer('required_count')->default(1); // Number of positions needed
            $table->integer('current_count')->default(0); // Current staff in this position
            $table->integer('shortage')->default(0); // Calculated: required_count - current_count
            $table->json('qualifications')->nullable(); // Required qualifications
            $table->json('responsibilities')->nullable(); // Key responsibilities
            $table->enum('status', ['active', 'inactive', 'frozen'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutional_positions');
    }
};
