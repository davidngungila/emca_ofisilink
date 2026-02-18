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
        Schema::table('activity_reports', function (Blueprint $table) {
            // Quality assessment fields (for HOD review)
            // Only add if they don't exist
            if (!Schema::hasColumn('activity_reports', 'quality_rating')) {
                $table->tinyInteger('quality_rating')->nullable()->after('approver_comments')->comment('1-5 star rating');
            }
        });
        
        Schema::table('activity_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_reports', 'complexity_tag')) {
                $table->enum('complexity_tag', ['routine', 'standard', 'complex'])->nullable()->after('quality_rating');
            }
        });
        
        Schema::table('activity_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_reports', 'initiative_bonus')) {
                $table->boolean('initiative_bonus')->default(false)->after('complexity_tag')->comment('Staff-initiated task bonus');
            }
        });
        
        Schema::table('activity_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_reports', 'quality_comments')) {
                $table->text('quality_comments')->nullable()->after('initiative_bonus')->comment('HOD quality assessment comments');
            }
        });
        
        Schema::table('activity_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_reports', 'performance_score')) {
                $table->decimal('performance_score', 5, 2)->nullable()->after('quality_comments')->comment('Calculated performance score');
            }
        });
        
        // Note: synced_to_performance and synced_at are already added in the first migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_reports', function (Blueprint $table) {
            $table->dropColumn([
                'quality_rating',
                'complexity_tag',
                'initiative_bonus',
                'quality_comments',
                'performance_score',
                'synced_to_performance',
                'synced_at'
            ]);
        });
    }
};

