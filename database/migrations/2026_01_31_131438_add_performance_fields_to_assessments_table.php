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
            $table->unsignedBigInteger('performance_criteria_id')->nullable()->after('branch_id');
            
            $table->foreign('performance_criteria_id')->references('id')->on('performance_criterias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['performance_criteria_id']);
            $table->dropColumn('performance_criteria_id');
        });
    }
};
