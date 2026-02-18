<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\OrganizationalGoal;
use App\Models\Assessment;
use App\Models\AssessmentActivity;
use App\Models\AssessmentProgressReport;
use Carbon\Carbon;

class PerformanceDemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Organizational Goals
        $corpGoal = OrganizationalGoal::create([
            'title' => 'Increase Global Market Share by 15%',
            'description' => 'Expand operations into West African markets and optimize digital presence.',
            'start_date' => Carbon::now()->startOfYear(),
            'end_date' => Carbon::now()->endOfYear(),
            'level' => 'organization',
            'is_active' => true,
        ]);

        // 2. Lookup Users and Departments
        $admin = User::first(); // Grab the first user as admin/assigner
        $staff = User::skip(3)->first(); // Grab another user as staff
        $department = \App\Models\Department::first();
        
        if (!$admin || !$department) return;

        // 3. Create Departmental Goals (Children)
        $opsGoal = OrganizationalGoal::create([
            'parent_id' => $corpGoal->id,
            'title' => 'Operational Efficiency Enhancement',
            'description' => 'Digitize 100% of internal workflow processes.',
            'department_id' => $department->id,
            'start_date' => Carbon::now()->startOfYear(),
            'end_date' => Carbon::now()->endOfYear(),
            'level' => 'department',
            'is_active' => true,
        ]);

        // 4. Create Assessments and Activities for demo
        if ($staff) {
            $assessment = Assessment::create([
                'employee_id' => $staff->id,
                'organizational_goal_id' => $opsGoal->id,
                'main_responsibility' => 'System Digitalization Lead',
                'description' => 'Lead the transition of paper-based systems to digital platforms.',
                'contribution_percentage' => 40,
                'status' => 'approved',
            ]);

            $activity = AssessmentActivity::create([
                'assessment_id' => $assessment->id,
                'activity_name' => 'Develop Digital HR Portal',
                'description' => 'Build and test the new employee self-service portal.',
                'reporting_frequency' => 'daily',
                'contribution_percentage' => 25,
                'target_start_date' => Carbon::now()->subMonths(1),
                'target_end_date' => Carbon::now()->addMonths(2),
                'status' => 'in_progress',
                'assigned_by' => $admin->id,
            ]);

            // Add a progress report
            AssessmentProgressReport::create([
                'activity_id' => $activity->id,
                'report_date' => Carbon::now()->subDay(),
                'progress_text' => 'Module configuration completed. Frontend design started.',
                'progress_percentage' => 45,
                'status' => 'approved',
                'hod_approved_at' => Carbon::now(),
                'hod_approved_by' => $admin->id,
                'quality_rating' => 8,
                'hod_comments' => 'Great progress on the UI components.',
            ]);

            // Add a pending report for approval demonstration
            AssessmentProgressReport::create([
                'activity_id' => $activity->id,
                'report_date' => Carbon::now(),
                'progress_text' => 'Integrated authentication system and tested API endpoints.',
                'progress_percentage' => 60,
                'status' => 'pending_approval',
            ]);
        }
    }
}
