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
        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('institutional_position_id')->nullable()->after('job_title');
            $table->unsignedBigInteger('salary_structure_id')->nullable()->after('institutional_position_id');
            $table->enum('payroll_approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('status');
            $table->unsignedBigInteger('payroll_approved_by')->nullable()->after('payroll_approval_status');
            $table->timestamp('payroll_approved_at')->nullable()->after('payroll_approved_by');
            $table->text('payroll_approval_notes')->nullable()->after('payroll_approved_at');
            
            $table->foreign('institutional_position_id')->references('id')->on('institutional_positions')->onDelete('set null');
            $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('set null');
            $table->foreign('payroll_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->dropForeign(['institutional_position_id']);
            $table->dropForeign(['salary_structure_id']);
            $table->dropForeign(['payroll_approved_by']);
            $table->dropColumn([
                'institutional_position_id',
                'salary_structure_id',
                'payroll_approval_status',
                'payroll_approved_by',
                'payroll_approved_at',
                'payroll_approval_notes'
            ]);
        });
    }
};
