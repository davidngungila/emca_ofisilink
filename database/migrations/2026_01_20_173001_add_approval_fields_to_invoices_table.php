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
        Schema::table('invoices', function (Blueprint $table) {
            // HOD Approval tracking
            $table->timestamp('hod_approved_at')->nullable()->after('status');
            $table->unsignedBigInteger('hod_approved_by')->nullable()->after('hod_approved_at');
            $table->text('hod_comments')->nullable()->after('hod_approved_by');
            
            // CEO Approval tracking
            $table->timestamp('ceo_approved_at')->nullable()->after('hod_comments');
            $table->unsignedBigInteger('ceo_approved_by')->nullable()->after('ceo_approved_at');
            $table->text('ceo_comments')->nullable()->after('ceo_approved_by');
            
            // Foreign keys
            $table->foreign('hod_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('ceo_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['hod_approved_by']);
            $table->dropForeign(['ceo_approved_by']);
            $table->dropColumn([
                'hod_approved_at',
                'hod_approved_by',
                'hod_comments',
                'ceo_approved_at',
                'ceo_approved_by',
                'ceo_comments'
            ]);
        });
    }
};
