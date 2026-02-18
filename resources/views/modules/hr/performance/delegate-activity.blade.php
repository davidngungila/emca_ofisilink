@extends('layouts.app')

@section('title', 'Delegate Performance Activity')

@section('content')
<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #940000 0%, #600000 100%) !important; }
    .text-primary { color: #940000 !important; }
    .btn-primary { background-color: #940000 !important; border-color: #940000 !important; }
    .btn-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; }
    .select2-container--bootstrap-5 .select2-selection { border-radius: 0.5rem; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-gradient-primary p-4">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('modules.performance_management_module') }}" class="btn btn-sm btn-glass me-3">
                            <i class="bx bx-left-arrow-alt"></i>
                        </a>
                        <h4 class="mb-0 text-white fw-bold">Delegate New Performance Activity</h4>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('modules.performance_management_module.action') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="delegate_activity">
                        
                        <div class="row g-4">
                            <!-- Link to Strategic Goal -->
                            <div class="col-12">
                                <label class="form-label fw-bold">Link to Strategic Goal</label>
                                <select name="organizational_goal_id" class="form-select form-select-lg rounded-3 select2-basic" required>
                                    <option value="">Select a goal this contributes to...</option>
                                    @foreach($orgGoals ?? [] as $goal)
                                        <optgroup label="Core Strategic: {{ $goal->title }}">
                                            @foreach($goal->children as $deptGoal)
                                            <option value="{{ $deptGoal->id }}">{{ $deptGoal->title }} ({{ $deptGoal->department->name ?? 'Dept' }})</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="form-text">Choose the departmental/team goal this activity supports.</div>
                            </div>

                            <!-- Staff Member -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Select Staff Member</label>
                                <select name="employee_id" class="form-select form-select-lg rounded-3 select2-basic" required>
                                    <option value="">Choose a team member to assign this to...</option>
                                    @foreach($teamMembers ?? [] as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->department->name ?? 'Staff' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Activity Name -->
                            <div class="col-12">
                                <label class="form-label fw-bold">Activity Name</label>
                                <input type="text" name="activity_name" class="form-control rounded-3" placeholder="e.g., Weekly Financial Reconciliation" required>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-bold">Activity Requirements & Description</label>
                                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Explain the deliverables and quality standards..."></textarea>
                            </div>

                            <!-- Frequency & Weight -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reporting Frequency</label>
                                <select name="reporting_frequency" class="form-select rounded-3">
                                    <option value="daily">Daily Submission</option>
                                    <option value="weekly">Weekly Submission</option>
                                    <option value="monthly">Monthly Submission</option>
                                    <option value="quarterly">Quarterly Submission</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Weight in Performance Score (%)</label>
                                <div class="input-group">
                                    <input type="number" name="contribution_percentage" class="form-control rounded-3" value="10" min="1" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="target_start_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Target/Deadline Date</label>
                                <input type="date" name="target_end_date" class="form-control rounded-3" required>
                            </div>

                            <!-- Submit -->
                            <div class="col-12 mt-5">
                                <hr class="my-4">
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="{{ route('modules.performance_management_module') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                                        <i class="bx bx-paper-plane me-2"></i>Delegate Activity
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2-basic').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }
    });
</script>
@endpush
