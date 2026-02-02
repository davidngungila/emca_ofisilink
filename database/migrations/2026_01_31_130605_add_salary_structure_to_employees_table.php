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
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('salary_structure_id')->nullable()->after('salary');
            $table->unsignedBigInteger('institutional_position_id')->nullable()->after('position');
            
            $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('set null');
            $table->foreign('institutional_position_id')->references('id')->on('institutional_positions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['salary_structure_id']);
            $table->dropForeign(['institutional_position_id']);
            $table->dropColumn(['salary_structure_id', 'institutional_position_id']);
        });
    }
};
