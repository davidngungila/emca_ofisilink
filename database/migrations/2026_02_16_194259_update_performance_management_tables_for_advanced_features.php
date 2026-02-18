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
        // Hierarchy support in users
        if (!Schema::hasColumn('users', 'supervisor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('supervisor_id')->nullable()->after('primary_department_id')->constrained('users')->onDelete('set null');
            });
        }

        // Organizational Goals updates
        Schema::table('organizational_goals', function (Blueprint $table) {
            if (!Schema::hasColumn('organizational_goals', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('organizational_goals')->onDelete('cascade');
            }
            if (!Schema::hasColumn('organizational_goals', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('parent_id')->constrained('departments')->onDelete('set null');
            }
            if (!Schema::hasColumn('organizational_goals', 'level')) {
                $table->enum('level', ['organization', 'department', 'individual'])->default('organization')->after('department_id');
            }
        });

        // Assessment Activities updates
        Schema::table('assessment_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_activities', 'status')) {
                $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->after('contribution_percentage');
            }
            if (!Schema::hasColumn('assessment_activities', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            }
        });

        // Progress Reports updates
        Schema::table('assessment_progress_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_progress_reports', 'progress_percentage')) {
                $table->decimal('progress_percentage', 5, 2)->default(0)->after('progress_text');
            }
            if (!Schema::hasColumn('assessment_progress_reports', 'evidence_file')) {
                $table->string('evidence_file')->nullable()->after('progress_percentage');
            }
            if (!Schema::hasColumn('assessment_progress_reports', 'quality_rating')) {
                $table->integer('quality_rating')->nullable()->after('hod_comments'); // 1-100 or 1-5
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users
        if (Schema::hasColumn('users', 'supervisor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            });
        }

        // Organizational Goals
        Schema::table('organizational_goals', function (Blueprint $table) {
            if (Schema::hasColumn('organizational_goals', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            if (Schema::hasColumn('organizational_goals', 'department_id')) {
                $table->dropForeign(['department_id']);
            }
            $table->dropColumn(['parent_id', 'department_id', 'level']);
        });

        // Assessment Activities
        Schema::table('assessment_activities', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_activities', 'assigned_by')) {
                $table->dropForeign(['assigned_by']);
            }
            $table->dropColumn(['status', 'assigned_by']);
        });

        // Progress Reports
        Schema::table('assessment_progress_reports', function (Blueprint $table) {
            $table->dropColumn(['progress_percentage', 'evidence_file', 'quality_rating']);
        });
    }
};
