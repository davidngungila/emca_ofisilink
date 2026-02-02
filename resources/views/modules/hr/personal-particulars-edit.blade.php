@extends('layouts.app')

@section('title', 'Edit Employee - ' . $employee->name . ' - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Edit Employee: {{ $employee->name }}</h4>
        <p class="text-muted">Update employee information step by step</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-edit me-2"></i>Edit Employee: {{ $employee->name }}
                            </h5>
                            <p class="text-white-50 mb-0">Update all employee information step by step</p>
                        </div>
<div>
                            <span class="badge bg-light text-primary" id="progressBadge">0% Complete</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Registration Progress</span>
                            <span class="text-muted" id="progressText">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 id="progressBar" 
                                 style="width: 0%;" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="progressPercentage">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Stage Navigation -->
                    <div class="mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap gap-2 align-items-center" id="stageIndicators">
                                    <span class="badge bg-primary stage-badge cursor-pointer" data-stage="personal" onclick="goToStep(0)" title="Click to jump to this section">
                                        <i class="bx bx-check me-1"></i>1. Personal Info
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="employment" onclick="goToStep(1)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>2. Employment
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="emergency" onclick="goToStep(2)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>3. Emergency Contact
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="family" onclick="goToStep(3)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>4. Family
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="next-of-kin" onclick="goToStep(4)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>5. Next of Kin
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="referees" onclick="goToStep(5)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>6. Referees
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="education" onclick="goToStep(6)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>7. Education
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="banking" onclick="goToStep(7)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>8. Banking
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="deductions" onclick="goToStep(8)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>9. Deductions
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="profile" onclick="goToStep(9)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>10. Profile
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="documents" onclick="goToStep(10)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>11. Documents
                                    </span>
                                    <span class="badge bg-secondary stage-badge cursor-pointer" data-stage="statutory" onclick="goToStep(11)" title="Click to jump to this section">
                                        <i class="bx bx-circle me-1"></i>12. Statutory Info
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Save Status Indicator -->
                    <div id="saveStatusIndicator" class="alert alert-info d-none mb-3">
                        <i class="bx bx-loader-alt bx-spin me-2"></i>
                        <span id="saveStatusText">Saving changes...</span>
                    </div>

                    <!-- Edit Form -->
                    <form id="employeeRegistrationForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="user_id" id="userId" value="{{ $employee->id }}">
                        <input type="hidden" name="stage" id="currentStage" value="personal">
                        <input type="hidden" name="save_as_draft" value="1">

                        <!-- Step 1: Personal Information -->
                        <div class="registration-step" id="step-personal" data-stage="personal">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-user me-2"></i>Step 1: Personal Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required value="{{ old('name', $employee->name) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required value="{{ old('email', $employee->email) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phone" class="form-control" placeholder="255XXXXXXXXX" value="{{ old('phone', $employee->phone ?? $employee->mobile) }}">
                                            @if(!$employee->phone && !$employee->mobile)
                                                <small class="text-warning d-block mt-1">
                                                    <i class="bx bx-info-circle me-1"></i>No phone number registered. Please contact administrator.
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Primary Department <span class="text-danger">*</span></label>
                                            <select name="primary_department_id" class="form-select" required>
                                                <option value="">Select Department</option>
                                                @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ old('primary_department_id', $employee->primary_department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                                            <select name="branch_id" class="form-select" required>
                                                <option value="">Select Branch</option>
                                                @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }} ({{ $branch->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Hire Date</label>
                                            <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Employee ID</label>
                                            <input type="text" class="form-control" value="{{ $employee->employee_id }}" readonly disabled>
                                            <small class="text-muted">Employee ID is auto-generated and cannot be edited</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Employment Details -->
                        <div class="registration-step d-none" id="step-employment" data-stage="employment">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-briefcase me-2"></i>Step 2: Employment Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Position/Job Title</label>
                                            <select name="position" class="form-select" id="position-select">
                                                <option value="">Select Position</option>
                                                @foreach($positions as $position)
                                                <option value="{{ $position->title }}" data-code="{{ $position->code }}" data-dept="{{ $position->department_id }}" {{ old('position', $employee->employee->position ?? '') == $position->title ? 'selected' : '' }}>
                                                    {{ $position->title }}@if($position->department) - {{ $position->department->name }}@endif
                                                </option>
                                                @endforeach
                                                <option value="__custom__" {{ !in_array(old('position', $employee->employee->position ?? ''), $positions->pluck('title')->toArray()) && old('position', $employee->employee->position ?? '') ? 'selected' : '' }}>-- Enter Custom Position --</option>
                                            </select>
                                            <input type="text" name="position_custom" id="position-custom" class="form-control mt-2 {{ !in_array(old('position', $employee->employee->position ?? ''), $positions->pluck('title')->toArray()) && old('position', $employee->employee->position ?? '') ? '' : 'd-none' }}" placeholder="Enter custom position" value="{{ !in_array(old('position', $employee->employee->position ?? ''), $positions->pluck('title')->toArray()) ? old('position', $employee->employee->position ?? '') : '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Employment Type</label>
                                            <select name="employment_type" class="form-select">
                                                <option value="">Select Type</option>
                                                <option value="permanent" {{ old('employment_type', $employee->employee->employment_type ?? '') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                                <option value="contract" {{ old('employment_type', $employee->employee->employment_type ?? '') == 'contract' ? 'selected' : '' }}>Contract</option>
                                                <option value="intern" {{ old('employment_type', $employee->employee->employment_type ?? '') == 'intern' ? 'selected' : '' }}>Intern</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Salary Structure</label>
                                            <select name="salary_structure_id" id="salary_structure_id" class="form-select">
                                                <option value="">Select Salary Structure</option>
                                                @foreach($salaryStructures as $structure)
                                                    <option value="{{ $structure->id }}" 
                                                            data-min-salary="{{ $structure->min_salary }}"
                                                            data-max-salary="{{ $structure->max_salary }}"
                                                            data-basic-salary="{{ $structure->basic_salary }}"
                                                            {{ old('salary_structure_id', $employee->employee->salary_structure_id ?? '') == $structure->id ? 'selected' : '' }}>
                                                        {{ $structure->name }} 
                                                        @if($structure->code) ({{ $structure->code }}) @endif
                                                        @if($structure->min_salary && $structure->max_salary)
                                                            - TZS {{ number_format($structure->min_salary, 2) }} - {{ number_format($structure->max_salary, 2) }}
                                                        @elseif($structure->basic_salary)
                                                            - TZS {{ number_format($structure->basic_salary, 2) }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Select a salary structure to auto-populate salary range</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Salary (TZS)</label>
                                            <input type="number" name="salary" id="salary" class="form-control" min="0" step="0.01" value="{{ old('salary', $employee->employee->salary ?? '') }}" placeholder="Enter salary amount">
                                            <small class="text-muted" id="salary-range-hint"></small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Assign Roles</label>
                                            <select name="roles[]" class="form-select" multiple>
                                                @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $employee->roles->contains($role->id) ? 'selected' : '' }}>{{ $role->display_name ?? $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Emergency Contact -->
                        <div class="registration-step d-none" id="step-emergency" data-stage="emergency">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-phone-call me-2"></i>Step 3: Emergency Contact
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addEmergencyContact()">
                                            <i class="bx bx-plus"></i> Add Contact
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 25%">Contact Name</th>
                                                    <th style="width: 20%">Phone</th>
                                                    <th style="width: 20%">Relationship</th>
                                                    <th style="width: 30%">Address</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="emergencyContactsListBody">
                                                @if($employee->employee && ($employee->employee->emergency_contact_name || $employee->employee->emergency_contact_phone))
                                                <tr class="emergency-contact-item">
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][name]" class="form-control form-control-sm" placeholder="Name" value="{{ old('emergency_contact_name', $employee->employee->emergency_contact_name ?? '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][phone]" class="form-control form-control-sm" placeholder="Phone" value="{{ old('emergency_contact_phone', $employee->employee->emergency_contact_phone ?? '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][relationship]" class="form-control form-control-sm" placeholder="Relationship" value="{{ old('emergency_contact_relationship', $employee->employee->emergency_contact_relationship ?? '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][address]" class="form-control form-control-sm" placeholder="Address" value="{{ old('emergency_contact_address', $employee->employee->emergency_contact_address ?? '') }}">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.emergency-contact-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @else
                                                <tr class="emergency-contact-item">
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][name]" class="form-control form-control-sm" placeholder="Name">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][phone]" class="form-control form-control-sm" placeholder="Phone">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][relationship]" class="form-control form-control-sm" placeholder="Relationship">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="emergency_contacts[0][address]" class="form-control form-control-sm" placeholder="Address">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.emergency-contact-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Family Information -->
                        <div class="registration-step d-none" id="step-family" data-stage="family">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-group me-2"></i>Step 4: Family Information
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addFamilyMember()">
                                            <i class="bx bx-plus"></i> Add Member
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 25%">Name / Relationship</th>
                                                    <th style="width: 20%">DOB / Gender</th>
                                                    <th style="width: 25%">Occupation / Phone</th>
                                                    <th style="width: 25%">Dependency</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="familyMembersBody">
                                                @if($employee->family && $employee->family->count() > 0)
                                                    @foreach($employee->family as $index => $member)
                                                    <tr class="family-member-item">
                                                        <td>
                                                            <input type="hidden" name="family[{{ $index }}][id]" value="{{ $member->id }}">
                                                            <input type="text" name="family[{{ $index }}][name]" class="form-control form-control-sm mb-1" placeholder="Name" value="{{ $member->name }}">
                                                            <input type="text" name="family[{{ $index }}][relationship]" class="form-control form-control-sm" placeholder="Relationship" value="{{ $member->relationship }}">
                                                        </td>
                                                        <td>
                                                            <input type="date" name="family[{{ $index }}][date_of_birth]" class="form-control form-control-sm mb-1" value="{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('Y-m-d') : '' }}">
                                                            <select name="family[{{ $index }}][gender]" class="form-select form-select-sm">
                                                                <option value="">Gender</option>
                                                                <option value="Male" {{ $member->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                                <option value="Female" {{ $member->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="family[{{ $index }}][occupation]" class="form-control form-control-sm mb-1" placeholder="Occupation" value="{{ $member->occupation }}">
                                                            <input type="text" name="family[{{ $index }}][phone]" class="form-control form-control-sm" placeholder="Phone" value="{{ $member->phone }}">
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="form-check d-inline-block">
                                                                <input class="form-check-input" type="checkbox" name="family[{{ $index }}][is_dependent]" value="1" id="dependent{{ $index }}" {{ $member->is_dependent ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="dependent{{ $index }}">Yes</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.family-member-item').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr class="family-member-item">
                                                    <td>
                                                        <input type="text" name="family[0][name]" class="form-control form-control-sm mb-1" placeholder="Name">
                                                        <input type="text" name="family[0][relationship]" class="form-control form-control-sm" placeholder="Relationship">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="family[0][date_of_birth]" class="form-control form-control-sm mb-1">
                                                        <select name="family[0][gender]" class="form-select form-select-sm">
                                                            <option value="">Gender</option>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="family[0][occupation]" class="form-control form-control-sm mb-1" placeholder="Occupation">
                                                        <input type="text" name="family[0][phone]" class="form-control form-control-sm" placeholder="Phone">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox" name="family[0][is_dependent]" value="1" id="dependent0">
                                                            <label class="form-check-label" for="dependent0">Yes</label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.family-member-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Next of Kin -->
                        <div class="registration-step d-none" id="step-next-of-kin" data-stage="next-of-kin">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-user-check me-2"></i>Step 5: Next of Kin
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addNextOfKin()">
                                            <i class="bx bx-plus"></i> Add Contact
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 25%">Name / Relationship</th>
                                                    <th style="width: 25%">Phone / Email</th>
                                                    <th style="width: 25%">Address / ID</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="nextOfKinMembersBody">
                                                @if($employee->nextOfKin && $employee->nextOfKin->count() > 0)
                                                    @foreach($employee->nextOfKin as $index => $kin)
                                                    <tr class="next-of-kin-item">
                                                        <td>
                                                            <input type="hidden" name="next_of_kin[{{ $index }}][id]" value="{{ $kin->id }}">
                                                            <input type="text" name="next_of_kin[{{ $index }}][name]" class="form-control form-control-sm mb-1" required placeholder="Name *" value="{{ $kin->name }}">
                                                            <input type="text" name="next_of_kin[{{ $index }}][relationship]" class="form-control form-control-sm" required placeholder="Relationship *" value="{{ $kin->relationship }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="next_of_kin[{{ $index }}][phone]" class="form-control form-control-sm mb-1" required placeholder="Phone *" value="{{ $kin->phone }}">
                                                            <input type="email" name="next_of_kin[{{ $index }}][email]" class="form-control form-control-sm" placeholder="Email" value="{{ $kin->email }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="next_of_kin[{{ $index }}][address]" class="form-control form-control-sm mb-1" required placeholder="Address *" value="{{ $kin->address }}">
                                                            <input type="text" name="next_of_kin[{{ $index }}][id_number]" class="form-control form-control-sm" placeholder="ID Number" value="{{ $kin->id_number }}">
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.next-of-kin-item').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr class="next-of-kin-item">
                                                    <td>
                                                        <input type="text" name="next_of_kin[0][name]" class="form-control form-control-sm mb-1" required placeholder="Name *">
                                                        <input type="text" name="next_of_kin[0][relationship]" class="form-control form-control-sm" required placeholder="Relationship *">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="next_of_kin[0][phone]" class="form-control form-control-sm mb-1" required placeholder="Phone *">
                                                        <input type="email" name="next_of_kin[0][email]" class="form-control form-control-sm" placeholder="Email">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="next_of_kin[0][address]" class="form-control form-control-sm mb-1" required placeholder="Address *">
                                                        <input type="text" name="next_of_kin[0][id_number]" class="form-control form-control-sm" placeholder="ID Number">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.next-of-kin-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Referees -->
                        <div class="registration-step d-none" id="step-referees" data-stage="referees">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-user-voice me-2"></i>Step 6: Referees
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addReferee()">
                                            <i class="bx bx-plus"></i> Add Referee
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 30%">Name / Relationship</th>
                                                    <th style="width: 30%">Position / Org</th>
                                                    <th style="width: 35%">Contact</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="refereesListBody">
                                                @if($employee->referees && $employee->referees->count() > 0)
                                                    @foreach($employee->referees as $index => $referee)
                                                    <tr class="referee-item">
                                                        <td>
                                                            <input type="hidden" name="referees[{{ $index }}][id]" value="{{ $referee->id }}">
                                                            <input type="text" name="referees[{{ $index }}][name]" class="form-control form-control-sm mb-1" required placeholder="Name *" value="{{ $referee->name }}">
                                                            <input type="text" name="referees[{{ $index }}][relationship]" class="form-control form-control-sm" placeholder="Relationship" value="{{ $referee->relationship }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="referees[{{ $index }}][position]" class="form-control form-control-sm mb-1" placeholder="Position" value="{{ $referee->position }}">
                                                            <input type="text" name="referees[{{ $index }}][organization]" class="form-control form-control-sm" placeholder="Organization" value="{{ $referee->organization }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="referees[{{ $index }}][phone]" class="form-control form-control-sm mb-1" required placeholder="Phone *" value="{{ $referee->phone }}">
                                                            <input type="email" name="referees[{{ $index }}][email]" class="form-control form-control-sm" placeholder="Email" value="{{ $referee->email }}">
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.referee-item').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr class="referee-item">
                                                    <td>
                                                        <input type="text" name="referees[0][name]" class="form-control form-control-sm mb-1" required placeholder="Name *">
                                                        <input type="text" name="referees[0][relationship]" class="form-control form-control-sm" placeholder="Relationship">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="referees[0][position]" class="form-control form-control-sm mb-1" placeholder="Position">
                                                        <input type="text" name="referees[0][organization]" class="form-control form-control-sm" placeholder="Organization">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="referees[0][phone]" class="form-control form-control-sm mb-1" required placeholder="Phone *">
                                                        <input type="email" name="referees[0][email]" class="form-control form-control-sm" placeholder="Email">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.referee-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 7: Education -->
                        <div class="registration-step d-none" id="step-education" data-stage="education">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-book me-2"></i>Step 7: Education Background
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addEducation()">
                                            <i class="bx bx-plus"></i> Add Education
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 30%">Institution / Qual</th>
                                                    <th style="width: 30%">Field / Grade</th>
                                                    <th style="width: 35%">Years (Start - End)</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="educationListBody">
                                                @if($employee->educations && $employee->educations->count() > 0)
                                                    @foreach($employee->educations as $index => $education)
                                                    <tr class="education-item">
                                                        <td>
                                                            <input type="hidden" name="educations[{{ $index }}][id]" value="{{ $education->id }}">
                                                            <input type="text" name="educations[{{ $index }}][institution_name]" class="form-control form-control-sm mb-1" required placeholder="Institution *" value="{{ $education->institution_name }}">
                                                            <input type="text" name="educations[{{ $index }}][qualification]" class="form-control form-control-sm" required placeholder="Qualification *" value="{{ $education->qualification }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="educations[{{ $index }}][field_of_study]" class="form-control form-control-sm mb-1" placeholder="Field of Study" value="{{ $education->field_of_study }}">
                                                            <input type="text" name="educations[{{ $index }}][grade]" class="form-control form-control-sm" placeholder="Grade" value="{{ $education->grade }}">
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" name="educations[{{ $index }}][start_year]" class="form-control" placeholder="Start" min="1900" max="{{ date('Y') }}" value="{{ $education->start_year }}">
                                                                <span class="input-group-text">-</span>
                                                                <input type="number" name="educations[{{ $index }}][end_year]" class="form-control" placeholder="End" min="1900" max="{{ date('Y') }}" value="{{ $education->end_year }}">
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.education-item').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr class="education-item">
                                                    <td>
                                                        <input type="text" name="educations[0][institution_name]" class="form-control form-control-sm mb-1" required placeholder="Institution *">
                                                        <input type="text" name="educations[0][qualification]" class="form-control form-control-sm" required placeholder="Qualification *">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="educations[0][field_of_study]" class="form-control form-control-sm mb-1" placeholder="Field of Study">
                                                        <input type="text" name="educations[0][grade]" class="form-control form-control-sm" placeholder="Grade">
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" name="educations[0][start_year]" class="form-control" placeholder="Start" min="1900" max="{{ date('Y') }}">
                                                            <span class="input-group-text">-</span>
                                                            <input type="number" name="educations[0][end_year]" class="form-control" placeholder="End" min="1900" max="{{ date('Y') }}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.education-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 8: Banking Information -->
                        <div class="registration-step d-none" id="step-banking" data-stage="banking">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-credit-card me-2"></i>Step 8: Banking Information
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addBankAccount()">
                                            <i class="bx bx-plus"></i> Add Account
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 30%">Bank / Account No</th>
                                                    <th style="width: 30%">Account Name / Branch</th>
                                                    <th style="width: 15%">Swift Code</th>
                                                    <th style="width: 20%">Options</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="bankAccountsListBody">
                                                @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                                                    @foreach($employee->bankAccounts as $index => $account)
                                                    <tr class="bank-account-item">
                                                        <td>
                                                            <input type="hidden" name="bank_accounts[{{ $index }}][id]" value="{{ $account->id }}">
                                                            <input type="text" name="bank_accounts[{{ $index }}][bank_name]" class="form-control form-control-sm mb-1" required placeholder="Bank Name *" value="{{ $account->bank_name }}">
                                                            <input type="text" name="bank_accounts[{{ $index }}][account_number]" class="form-control form-control-sm" required placeholder="Acc Number *" value="{{ $account->account_number }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="bank_accounts[{{ $index }}][account_name]" class="form-control form-control-sm mb-1" placeholder="Acc Name" value="{{ $account->account_name }}">
                                                            <input type="text" name="bank_accounts[{{ $index }}][branch_name]" class="form-control form-control-sm" placeholder="Branch" value="{{ $account->branch_name }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="bank_accounts[{{ $index }}][swift_code]" class="form-control form-control-sm" placeholder="SWIFT" value="{{ $account->swift_code }}">
                                                        </td>
                                                        <td class="align-middle">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="bank_accounts[{{ $index }}][is_primary]" value="1" id="primaryBank{{ $index }}" {{ $account->is_primary ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="primaryBank{{ $index }}">Primary</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.bank-account-item').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr class="bank-account-item">
                                                    <td>
                                                        <input type="text" name="bank_accounts[0][bank_name]" class="form-control form-control-sm mb-1" required placeholder="Bank Name *">
                                                        <input type="text" name="bank_accounts[0][account_number]" class="form-control form-control-sm" required placeholder="Acc Number *">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="bank_accounts[0][account_name]" class="form-control form-control-sm mb-1" placeholder="Acc Name">
                                                        <input type="text" name="bank_accounts[0][branch_name]" class="form-control form-control-sm" placeholder="Branch">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="bank_accounts[0][swift_code]" class="form-control form-control-sm" placeholder="SWIFT">
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="bank_accounts[0][is_primary]" value="1" id="primaryBank0">
                                                            <label class="form-check-label small" for="primaryBank0">Primary</label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.bank-account-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 9: Deductions -->
                        <div class="registration-step d-none" id="step-deductions" data-stage="deductions">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-money me-2"></i>Step 9: Salary Deductions
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addDeduction()">
                                            <i class="bx bx-plus"></i> Add Deduction
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 20%">Type / Amount</th>
                                                    <th style="width: 15%">Frequency</th>
                                                    <th style="width: 20%">Dates</th>
                                                    <th style="width: 30%">Description / Notes</th>
                                                    <th style="width: 10%">Status</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="deductionsListBody">
                                                @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                                                    @foreach($employee->salaryDeductions as $index => $deduction)
                                                    <tr class="deduction-item">
                                                        <td>
                                                            <input type="hidden" name="deductions[{{ $index }}][id]" value="{{ $deduction->id }}">
                                                            <div class="mb-2">
                                                                <label class="form-label small text-muted">Type</label>
                                                                <input type="text" name="deductions[{{ $index }}][deduction_type]" class="form-control form-control-sm" required placeholder="Type" value="{{ $deduction->deduction_type }}">
                                                            </div>
                                                            <div>
                                                                <label class="form-label small text-muted">Amount</label>
                                                                <input type="number" name="deductions[{{ $index }}][amount]" class="form-control form-control-sm" required min="0" step="0.01" value="{{ $deduction->amount }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <label class="form-label small text-muted">Freq</label>
                                                            <select name="deductions[{{ $index }}][frequency]" class="form-select form-select-sm">
                                                                <option value="monthly" {{ $deduction->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                                <option value="one-time" {{ $deduction->frequency == 'one-time' ? 'selected' : '' }}>One-Time</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="mb-2">
                                                                <label class="form-label small text-muted">Start</label>
                                                                <input type="date" name="deductions[{{ $index }}][start_date]" class="form-control form-control-sm" required value="{{ $deduction->start_date ? \Carbon\Carbon::parse($deduction->start_date)->format('Y-m-d') : '' }}">
                                                            </div>
                                                            <div>
                                                                <label class="form-label small text-muted">End</label>
                                                                <input type="date" name="deductions[{{ $index }}][end_date]" class="form-control form-control-sm" value="{{ $deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mb-2">
                                                                <textarea name="deductions[{{ $index }}][description]" class="form-control form-control-sm" rows="2" placeholder="Description">{{ $deduction->description }}</textarea>
                                                            </div>
                                                            <div>
                                                                <input type="text" name="deductions[{{ $index }}][notes]" class="form-control form-control-sm" placeholder="Notes" value="{{ $deduction->notes }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check text-center mt-2">
                                                                <input class="form-check-input float-none" type="checkbox" name="deductions[{{ $index }}][is_active]" value="1" id="activeDeduction{{ $index }}" {{ $deduction->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label d-block small" for="activeDeduction{{ $index }}">Active</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.deduction-item').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr class="deduction-item">
                                                    <td>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-muted">Type</label>
                                                            <input type="text" name="deductions[0][deduction_type]" class="form-control form-control-sm" required placeholder="Type">
                                                        </div>
                                                        <div>
                                                            <label class="form-label small text-muted">Amount</label>
                                                            <input type="number" name="deductions[0][amount]" class="form-control form-control-sm" required min="0" step="0.01">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label class="form-label small text-muted">Freq</label>
                                                        <select name="deductions[0][frequency]" class="form-select form-select-sm">
                                                            <option value="monthly">Monthly</option>
                                                            <option value="one-time">One-Time</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-muted">Start</label>
                                                            <input type="date" name="deductions[0][start_date]" class="form-control form-control-sm" required>
                                                        </div>
                                                        <div>
                                                            <label class="form-label small text-muted">End</label>
                                                            <input type="date" name="deductions[0][end_date]" class="form-control form-control-sm">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="mb-2">
                                                            <textarea name="deductions[0][description]" class="form-control form-control-sm" rows="2" placeholder="Description"></textarea>
                                                        </div>
                                                        <div>
                                                            <input type="text" name="deductions[0][notes]" class="form-control form-control-sm" placeholder="Notes">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check text-center mt-2">
                                                            <input class="form-check-input float-none" type="checkbox" name="deductions[0][is_active]" value="1" id="activeDeduction0" checked>
                                                            <label class="form-check-label d-block small" for="activeDeduction0">Active</label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.deduction-item').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 10: Profile Information -->
                        <div class="registration-step d-none" id="step-profile" data-stage="profile">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-user-circle me-2"></i>Step 10: Profile Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12 text-center mb-4">
                                            <div class="mb-3">
                                                @if(isset($employee) && $employee->photo)
                                                <img src="{{ asset('storage/photos/' . $employee->photo) }}" alt="Profile Photo" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                                @else
                                                <div class="avatar avatar-xl mx-auto">
                                                    <span class="avatar-initial rounded-circle bg-label-primary" style="width: 150px; height: 150px; font-size: 60px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bx bx-user"></i>
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            <label class="form-label">Profile Photo</label>
                                            <input type="file" name="photo" class="form-control" accept="image/*">
                                            <small class="text-muted">Max size: 5MB. Formats: JPEG, PNG, JPG, GIF, WEBP</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">Select Gender</option>
                                                <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                                <option value="Other" {{ old('gender', $employee->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Marital Status</label>
                                            <select name="marital_status" class="form-select">
                                                <option value="">Select Status</option>
                                                <option value="Single" {{ old('marital_status', $employee->marital_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                                <option value="Married" {{ old('marital_status', $employee->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                                <option value="Divorced" {{ old('marital_status', $employee->marital_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                                <option value="Widowed" {{ old('marital_status', $employee->marital_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nationality</label>
                                            <input type="text" name="nationality" class="form-control" placeholder="e.g., Tanzanian" value="{{ old('nationality', $employee->nationality) }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Address</label>
                                            <textarea name="address" class="form-control" rows="3" placeholder="Full residential address">{{ old('address', $employee->address) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 11: Documents -->
                        <div class="registration-step d-none" id="step-documents" data-stage="documents">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bx bx-file-blank me-2"></i>Step 11: Documents
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addDocument()">
                                            <i class="bx bx-plus"></i> Add Document
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Documents are optional. You can add documents now or later. Click "Add Document" to add a new document to the list.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 20%">Type / Name</th>
                                                    <th style="width: 20%">Number / Issued By</th>
                                                    <th style="width: 15%">Dates</th>
                                                    <th style="width: 25%">File</th>
                                                    <th style="width: 15%">Description</th>
                                                    <th style="width: 5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="documentsListBody">
                                                @if($employee->documents && $employee->documents->count() > 0)
                                                    @foreach($employee->documents as $index => $document)
                                                    <tr class="document-item" data-index="{{ $index }}">
                                                        <td>
                                                            <input type="hidden" name="documents[{{ $index }}][id]" value="{{ $document->id }}">
                                                            <select name="documents[{{ $index }}][document_type]" class="form-select form-select-sm mb-2">
                                                                <option value="">Select Type</option>
                                                                <option value="ID Card" {{ $document->document_type == 'ID Card' ? 'selected' : '' }}>ID Card</option>
                                                                <option value="Passport" {{ $document->document_type == 'Passport' ? 'selected' : '' }}>Passport</option>
                                                                <option value="Birth Certificate" {{ $document->document_type == 'Birth Certificate' ? 'selected' : '' }}>Birth Certificate</option>
                                                                <option value="Academic Certificate" {{ $document->document_type == 'Academic Certificate' ? 'selected' : '' }}>Academic Certificate</option>
                                                                <option value="Professional License" {{ $document->document_type == 'Professional License' ? 'selected' : '' }}>Professional License</option>
                                                                <option value="Contract" {{ $document->document_type == 'Contract' ? 'selected' : '' }}>Contract</option>
                                                                <option value="Other" {{ $document->document_type == 'Other' ? 'selected' : '' }}>Other</option>
                                                            </select>
                                                            <input type="text" name="documents[{{ $index }}][document_name]" class="form-control form-control-sm" placeholder="Doc Name" value="{{ $document->document_name }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="documents[{{ $index }}][document_number]" class="form-control form-control-sm mb-2" placeholder="Doc Number" value="{{ $document->document_number }}">
                                                            <input type="text" name="documents[{{ $index }}][issued_by]" class="form-control form-control-sm" placeholder="Issued By" value="{{ $document->issued_by }}">
                                                        </td>
                                                        <td>
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Issue:</small>
                                                                <input type="date" name="documents[{{ $index }}][issue_date]" class="form-control form-control-sm" value="{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('Y-m-d') : '' }}">
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block">Expiry:</small>
                                                                <input type="date" name="documents[{{ $index }}][expiry_date]" class="form-control form-control-sm" value="{{ $document->expiry_date ? \Carbon\Carbon::parse($document->expiry_date)->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($document->file_path)
                                                            <div class="mb-2 d-flex align-items-center">
                                                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="previewDocument('{{ asset('storage/documents/' . $document->file_path) }}', '{{ $document->document_name }}')">
                                                                    <i class="bx bx-show me-1"></i> Preview
                                                                </button>
                                                                <small class="text-success"><i class="bx bx-check"></i> Uploaded</small>
                                                            </div>
                                                            @endif
                                                            <input type="file" name="documents[{{ $index }}][file]" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                            <small class="text-muted" style="font-size: 10px;">PDF, Images (Max 10MB)</small>
                                                        </td>
                                                        <td>
                                                            <textarea name="documents[{{ $index }}][description]" class="form-control form-control-sm" rows="3" placeholder="Desc">{{ $document->description }}</textarea>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeDocument($(this))">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr id="noDocumentsMessage">
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="bx bx-file-blank" style="font-size: 32px; opacity: 0.3;"></i>
                                                        <p class="mt-2 mb-0">No documents added yet. Click "Add Document".</p>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 12: Statutory Information -->
                        <div class="registration-step d-none" id="step-statutory" data-stage="statutory">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bx bx-id-card me-2"></i>Step 12: Statutory Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">NIDA Number</label>
                                            <input type="text" name="nida_number" class="form-control" placeholder="National ID Number" value="{{ old('nida_number', $employee->employee->nida_number ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">TIN Number</label>
                                            <input type="text" name="tin_number" class="form-control" placeholder="Tax Identification Number" value="{{ old('tin_number', $employee->employee->tin_number ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">NSSF Number</label>
                                            <input type="text" name="nssf_number" class="form-control" placeholder="National Social Security Fund Number" value="{{ old('nssf_number', $employee->employee->nssf_number ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">NHIF Number</label>
                                            <input type="text" name="nhif_number" class="form-control" placeholder="National Health Insurance Fund Number" value="{{ old('nhif_number', $employee->employee->nhif_number ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">HESLB Number</label>
                                            <input type="text" name="heslb_number" class="form-control" placeholder="Higher Education Students' Loans Board Number" value="{{ old('heslb_number', $employee->employee->heslb_number ?? '') }}">
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="has_student_loan" value="1" id="has_student_loan" {{ old('has_student_loan', $employee->employee->has_student_loan ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="has_student_loan">
                                                    Has Student Loan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="previousStep()" style="display: none;">
                                <i class="bx bx-chevron-left me-1"></i> Previous
                            </button>
                            <div class="ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="saveStepBtn" onclick="saveCurrentStep()">
                                    <i class="bx bx-save me-1"></i> Save This Step
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                                    Next <i class="bx bx-chevron-right ms-1"></i>
                                </button>
                                <button type="button" class="btn btn-success" id="saveAllBtn" onclick="saveAllChanges()">
                                    <i class="bx bx-save me-1"></i> Save All Changes
                                </button>
                                <a href="{{ route('modules.hr.personal-particulars') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Employees
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="previewFrame" src="" style="width:100%; height:100%; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStepIndex = 0;
const steps = ['personal', 'employment', 'emergency', 'family', 'next-of-kin', 'referees', 'education', 'banking', 'deductions', 'profile', 'documents', 'statutory'];
// Initialize indices based on existing data counts
let emergencyContactIndex = {{ ($employee->employee && ($employee->employee->emergency_contact_name || $employee->employee->emergency_contact_phone)) ? 1 : 0 }};
let familyIndex = {{ $employee->family && $employee->family->count() > 0 ? $employee->family->count() : 0 }};
let nextOfKinIndex = {{ $employee->nextOfKin && $employee->nextOfKin->count() > 0 ? $employee->nextOfKin->count() : 0 }};
let refereeIndex = {{ $employee->referees && $employee->referees->count() > 0 ? $employee->referees->count() : 1 }};
let educationIndex = {{ $employee->educations && $employee->educations->count() > 0 ? $employee->educations->count() : 1 }};
let bankAccountIndex = {{ $employee->bankAccounts && $employee->bankAccounts->count() > 0 ? $employee->bankAccounts->count() : 1 }};
let deductionIndex = {{ $employee->salaryDeductions && $employee->salaryDeductions->count() > 0 ? $employee->salaryDeductions->count() : 1 }};
let documentIndex = {{ $employee->documents && $employee->documents->count() > 0 ? $employee->documents->count() : 1 }};
let userId = {{ $employee->id }};

$(document).ready(function() {
    updateProgress();
    updateStageIndicators();
    
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Handle Salary Structure selection
    $('#salary_structure_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const minSalary = selectedOption.data('min-salary');
        const maxSalary = selectedOption.data('max-salary');
        const basicSalary = selectedOption.data('basic-salary');
        const salaryInput = $('#salary');
        const hintElement = $('#salary-range-hint');
        
        if (selectedOption.val()) {
            if (basicSalary) {
                salaryInput.val(basicSalary);
                hintElement.text('Basic salary from structure: TZS ' + parseFloat(basicSalary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                hintElement.removeClass('text-danger').addClass('text-success');
            } else if (minSalary && maxSalary) {
                salaryInput.val('');
                hintElement.text('Salary range: TZS ' + parseFloat(minSalary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' - ' + parseFloat(maxSalary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                hintElement.removeClass('text-danger').addClass('text-info');
                salaryInput.attr('min', minSalary);
                salaryInput.attr('max', maxSalary);
            } else {
                hintElement.text('Please enter salary manually');
                hintElement.removeClass('text-danger text-success').addClass('text-muted');
            }
        } else {
            salaryInput.val('');
            hintElement.text('');
            salaryInput.removeAttr('min max');
        }
    });
    
    // Validate salary against structure range
    $('#salary').on('blur', function() {
        const salaryValue = parseFloat($(this).val());
        const selectedOption = $('#salary_structure_id').find('option:selected');
        const minSalary = selectedOption.data('min-salary');
        const maxSalary = selectedOption.data('max-salary');
        const hintElement = $('#salary-range-hint');
        
        if (selectedOption.val() && minSalary && maxSalary && salaryValue) {
            if (salaryValue < minSalary || salaryValue > maxSalary) {
                hintElement.text('⚠ Salary is outside the structure range!');
                hintElement.removeClass('text-success text-info').addClass('text-danger');
            } else {
                hintElement.text('✓ Salary is within the structure range');
                hintElement.removeClass('text-danger text-info').addClass('text-success');
            }
        }
    });
    
    // Trigger change on page load if structure is already selected
    if ($('#salary_structure_id').val()) {
        $('#salary_structure_id').trigger('change');
    }
    
    // Handle position dropdown custom option
    $('#position-select').on('change', function() {
        if ($(this).val() === '__custom__') {
            $('#position-custom').removeClass('d-none').focus();
        } else {
            $('#position-custom').addClass('d-none').val('');
        }
    });
    
    // Handle position custom input - update select when typing
    $('#position-custom').on('input', function() {
        if ($(this).val().trim()) {
            $('#position-select').val('__custom__');
        }
    });
});

function updateProgress() {
    const progress = ((currentStepIndex + 1) / steps.length) * 100;
    $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress);
    $('#progressPercentage').text(Math.round(progress) + '%');
    $('#progressText').text(Math.round(progress) + '%');
    $('#progressBadge').text(Math.round(progress) + '% Complete');
}

function goToStep(index) {
    if (index >= 0 && index < steps.length) {
        // Save current step before navigating
        saveCurrentStep(false, function() {
            currentStepIndex = index;
            showStep(currentStepIndex);
            // Scroll to top of form
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }
}

function updateStageIndicators() {
    $('.stage-badge').each(function(index) {
        const stage = $(this).data('stage');
        if (index < currentStepIndex) {
            $(this).removeClass('bg-secondary bg-primary').addClass('bg-success');
            $(this).find('i').removeClass('bx-circle bx-check').addClass('bx-check');
        } else if (index === currentStepIndex) {
            $(this).removeClass('bg-secondary bg-success').addClass('bg-primary');
            $(this).find('i').removeClass('bx-circle').addClass('bx-check');
        } else {
            $(this).removeClass('bg-primary bg-success').addClass('bg-secondary');
            $(this).find('i').removeClass('bx-check').addClass('bx-circle');
        }
    });
}

function showStep(index) {
    $('.registration-step').addClass('d-none');
    $(`#step-${steps[index]}`).removeClass('d-none');
    $('#currentStage').val(steps[index]);
    
    // Update navigation buttons
    $('#prevBtn').toggle(index > 0);
    
    // Show review/complete/submit buttons on last step
    if (index === steps.length - 1) {
        if (userId) {
            $('#completeBtn').removeClass('d-none').html('<i class="bx bx-check me-1"></i> Review & Complete');
            $('#submitBtn').removeClass('d-none');
            $('#nextBtn').addClass('d-none');
            $('#saveStepBtn').removeClass('d-none');
        } else {
            $('#completeBtn').addClass('d-none');
            $('#submitBtn').addClass('d-none');
            $('#nextBtn').removeClass('d-none');
        }
    } else {
        $('#completeBtn').addClass('d-none');
        $('#submitBtn').addClass('d-none');
        $('#nextBtn').removeClass('d-none');
        $('#saveStepBtn').removeClass('d-none');
    }
    
    updateProgress();
    updateStageIndicators();
}

function nextStep() {
    if (currentStepIndex < steps.length - 1) {
        // Save current step before moving
        saveCurrentStep(false, function() {
            currentStepIndex++;
            showStep(currentStepIndex);
        });
    }
}

function previousStep() {
    if (currentStepIndex > 0) {
        currentStepIndex--;
        showStep(currentStepIndex);
    }
}

function saveCurrentStep(showMessage = true, callback = null) {
    const formData = new FormData($('#employeeRegistrationForm')[0]);
    // Map stage names to section names for controller compatibility
    const stageToSection = {
        'personal': 'personal',
        'employment': 'employment',
        'emergency': 'emergency',
        'family': 'family',
        'next-of-kin': 'next-of-kin',
        'referees': 'referees',
        'education': 'education',
        'banking': 'bank',  // Map 'banking' to 'bank' for controller
        'deductions': 'deductions',
        'profile': 'profile',
        'documents': 'documents',
        'statutory': 'statutory',
    };
    const currentStage = steps[currentStepIndex];
    const section = stageToSection[currentStage] || currentStage;
    
    // Log for debugging
    console.log('Saving step:', {
        'stepIndex': currentStepIndex,
        'stage': currentStage,
        'section': section,
        'stepName': steps[currentStepIndex]
    });
    
    formData.append('section', section);
    formData.append('stage', currentStage); // Keep for backward compatibility
    formData.append('save_as_draft', '1');
    
    // Always set password to welcome123 in background (remove any password fields from form)
    formData.delete('password');
    formData.delete('password_confirmation');
    
    // Handle position - if custom is selected, use custom value
    if ($('#position-select').val() === '__custom__' && $('#position-custom').val().trim()) {
        formData.set('position', $('#position-custom').val().trim());
        formData.delete('position_custom');
    } else if ($('#position-select').val() && $('#position-select').val() !== '__custom__') {
        formData.set('position', $('#position-select').val());
        formData.delete('position_custom');
    }
    
    formData.append('_method', 'PUT');
    
    // Show save status
    $('#saveStatusIndicator').removeClass('d-none alert-success alert-danger').addClass('alert-info');
    $('#saveStatusText').html('<i class="bx bx-loader-alt bx-spin me-2"></i>Saving changes...');
    
    $.ajax({
        url: '{{ route("personal-particulars.update", $employee->id) }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Hide save status after a delay
            setTimeout(function() {
                $('#saveStatusIndicator').addClass('d-none');
            }, 2000);
            
            if (response.success) {
                if (response.user_id) {
                    userId = response.user_id;
                    $('#userId').val(userId);
                }
                
                // If can review, show review button
                if (response.can_review && currentStepIndex === steps.length - 1) {
                    $('#completeBtn').html('<i class="bx bx-check me-1"></i> Review & Complete').removeClass('d-none');
                }
                
                if (showMessage) {
                    showToast('success', response.message || 'Step saved successfully!');
                    // Show success status
                    $('#saveStatusIndicator').removeClass('alert-info alert-danger').addClass('alert-success');
                    $('#saveStatusText').html('<i class="bx bx-check-circle me-2"></i>Changes saved successfully!');
                    setTimeout(function() {
                        $('#saveStatusIndicator').addClass('d-none');
                    }, 3000);
                }
                
                if (callback) callback();
            } else {
                showToast('error', response.message || 'Failed to save step');
                // Show error status
                $('#saveStatusIndicator').removeClass('alert-info alert-success').addClass('alert-danger');
                $('#saveStatusText').html('<i class="bx bx-error-circle me-2"></i>' + (response.message || 'Failed to save step'));
            }
        },
        error: function(xhr) {
            // Hide save status and show error
            $('#saveStatusIndicator').removeClass('alert-info alert-success').addClass('alert-danger');
            const errors = xhr.responseJSON?.errors || {};
            let errorMsg = xhr.responseJSON?.message || 'Failed to save step';
            
            // Get section name for better error message
            const sectionNames = {
                'personal': 'Personal Information',
                'employment': 'Employment Information',
                'emergency': 'Emergency Contact',
                'family': 'Family Information',
                'next-of-kin': 'Next of Kin',
                'referees': 'Referees',
                'bank': 'Banking Information',
                'banking': 'Banking Information',
                'education': 'Education',
                'deductions': 'Deductions',
                'profile': 'Profile',
                'documents': 'Documents',
                'statutory': 'Statutory Information'
            };
            const sectionName = sectionNames[section] || section;
            
            if (Object.keys(errors).length > 0) {
                errorMsg = `Error saving ${sectionName}:\n` + Object.values(errors).flat().join('\n');
            } else {
                errorMsg = `Error saving ${sectionName}: ${errorMsg}`;
            }
            
            console.error('Save error:', {
                'section': section,
                'sectionName': sectionName,
                'error': errorMsg,
                'response': xhr.responseJSON
            });
            
            $('#saveStatusText').html('<i class="bx bx-error-circle me-2"></i>' + errorMsg);
            showToast('error', errorMsg);
        }
    });
}

function saveAllChanges() {
    Swal.fire({
        title: 'Save All Changes?',
        text: 'This will save all changes made to the employee information.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Save All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Save current step first
            saveCurrentStep(false, function() {
                showToast('success', 'All changes saved successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("modules.hr.personal-particulars") }}';
                }, 1500);
            });
        }
    });
}


// Dynamic form additions
function addEmergencyContact() {
    const html = `
        <tr class="emergency-contact-item">
            <td>
                <input type="text" name="emergency_contacts[${emergencyContactIndex}][name]" class="form-control form-control-sm" placeholder="Name">
            </td>
            <td>
                <input type="text" name="emergency_contacts[${emergencyContactIndex}][phone]" class="form-control form-control-sm" placeholder="Phone">
            </td>
            <td>
                <input type="text" name="emergency_contacts[${emergencyContactIndex}][relationship]" class="form-control form-control-sm" placeholder="Relationship">
            </td>
            <td>
                <input type="text" name="emergency_contacts[${emergencyContactIndex}][address]" class="form-control form-control-sm" placeholder="Address">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.emergency-contact-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#emergencyContactsListBody').append(html);
    emergencyContactIndex++;
}

function addFamilyMember() {
    const html = `
        <tr class="family-member-item">
            <td>
                <input type="text" name="family[${familyIndex}][name]" class="form-control form-control-sm mb-1" placeholder="Name">
                <input type="text" name="family[${familyIndex}][relationship]" class="form-control form-control-sm" placeholder="Relationship">
            </td>
            <td>
                <input type="date" name="family[${familyIndex}][date_of_birth]" class="form-control form-control-sm mb-1">
                <select name="family[${familyIndex}][gender]" class="form-select form-select-sm">
                    <option value="">Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </td>
            <td>
                <input type="text" name="family[${familyIndex}][occupation]" class="form-control form-control-sm mb-1" placeholder="Occupation">
                <input type="text" name="family[${familyIndex}][phone]" class="form-control form-control-sm" placeholder="Phone">
            </td>
            <td class="text-center align-middle">
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" name="family[${familyIndex}][is_dependent]" value="1" id="dependent${familyIndex}">
                    <label class="form-check-label" for="dependent${familyIndex}">Yes</label>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.family-member-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#familyMembersBody').append(html);
    familyIndex++;
}

function addNextOfKin() {
    const html = `
        <tr class="next-of-kin-item">
            <td>
                <input type="text" name="next_of_kin[${nextOfKinIndex}][name]" class="form-control form-control-sm mb-1" required placeholder="Name *">
                <input type="text" name="next_of_kin[${nextOfKinIndex}][relationship]" class="form-control form-control-sm" required placeholder="Relationship *">
            </td>
            <td>
                <input type="text" name="next_of_kin[${nextOfKinIndex}][phone]" class="form-control form-control-sm mb-1" required placeholder="Phone *">
                <input type="email" name="next_of_kin[${nextOfKinIndex}][email]" class="form-control form-control-sm" placeholder="Email">
            </td>
            <td>
                <input type="text" name="next_of_kin[${nextOfKinIndex}][address]" class="form-control form-control-sm mb-1" required placeholder="Address *">
                <input type="text" name="next_of_kin[${nextOfKinIndex}][id_number]" class="form-control form-control-sm" placeholder="ID Number">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.next-of-kin-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#nextOfKinMembersBody').append(html);
    nextOfKinIndex++;
}

function addReferee() {
    const html = `
        <tr class="referee-item">
            <td>
                <input type="text" name="referees[${refereeIndex}][name]" class="form-control form-control-sm mb-1" required placeholder="Name *">
                <input type="text" name="referees[${refereeIndex}][relationship]" class="form-control form-control-sm" placeholder="Relationship">
            </td>
            <td>
                <input type="text" name="referees[${refereeIndex}][position]" class="form-control form-control-sm mb-1" placeholder="Position">
                <input type="text" name="referees[${refereeIndex}][organization]" class="form-control form-control-sm" placeholder="Organization">
            </td>
            <td>
                <input type="text" name="referees[${refereeIndex}][phone]" class="form-control form-control-sm mb-1" required placeholder="Phone *">
                <input type="email" name="referees[${refereeIndex}][email]" class="form-control form-control-sm" placeholder="Email">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.referee-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#refereesListBody').append(html);
    refereeIndex++;
}

function addEducation() {
    const html = `
        <tr class="education-item">
            <td>
                <input type="text" name="educations[${educationIndex}][institution_name]" class="form-control form-control-sm mb-1" required placeholder="Institution *">
                <input type="text" name="educations[${educationIndex}][qualification]" class="form-control form-control-sm" required placeholder="Qualification *">
            </td>
            <td>
                <input type="text" name="educations[${educationIndex}][field_of_study]" class="form-control form-control-sm mb-1" placeholder="Field of Study">
                <input type="text" name="educations[${educationIndex}][grade]" class="form-control form-control-sm" placeholder="Grade">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" name="educations[${educationIndex}][start_year]" class="form-control" placeholder="Start" min="1900" max="{{ date('Y') }}">
                    <span class="input-group-text">-</span>
                    <input type="number" name="educations[${educationIndex}][end_year]" class="form-control" placeholder="End" min="1900" max="{{ date('Y') }}">
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.education-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#educationListBody').append(html);
    educationIndex++;
}

function addBankAccount() {
    const html = `
        <tr class="bank-account-item">
            <td>
                <input type="text" name="bank_accounts[${bankAccountIndex}][bank_name]" class="form-control form-control-sm mb-1" required placeholder="Bank Name *">
                <input type="text" name="bank_accounts[${bankAccountIndex}][account_number]" class="form-control form-control-sm" required placeholder="Acc Number *">
            </td>
            <td>
                <input type="text" name="bank_accounts[${bankAccountIndex}][account_name]" class="form-control form-control-sm mb-1" placeholder="Acc Name">
                <input type="text" name="bank_accounts[${bankAccountIndex}][branch_name]" class="form-control form-control-sm" placeholder="Branch">
            </td>
            <td>
                <input type="text" name="bank_accounts[${bankAccountIndex}][swift_code]" class="form-control form-control-sm" placeholder="SWIFT">
            </td>
            <td class="align-middle">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="bank_accounts[${bankAccountIndex}][is_primary]" value="1" id="primaryBank${bankAccountIndex}">
                    <label class="form-check-label small" for="primaryBank${bankAccountIndex}">Primary</label>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.bank-account-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#bankAccountsListBody').append(html);
    bankAccountIndex++;
}

function addDeduction() {
    const html = `
        <tr class="deduction-item">
            <td>
                <div class="mb-2">
                    <label class="form-label small text-muted">Type</label>
                    <input type="text" name="deductions[${deductionIndex}][deduction_type]" class="form-control form-control-sm" required placeholder="Type">
                </div>
                <div>
                    <label class="form-label small text-muted">Amount</label>
                    <input type="number" name="deductions[${deductionIndex}][amount]" class="form-control form-control-sm" required min="0" step="0.01">
                </div>
            </td>
            <td>
                <label class="form-label small text-muted">Freq</label>
                <select name="deductions[${deductionIndex}][frequency]" class="form-select form-select-sm">
                    <option value="monthly">Monthly</option>
                    <option value="one-time">One-Time</option>
                </select>
            </td>
            <td>
                <div class="mb-2">
                    <label class="form-label small text-muted">Start</label>
                    <input type="date" name="deductions[${deductionIndex}][start_date]" class="form-control form-control-sm" required>
                </div>
                <div>
                    <label class="form-label small text-muted">End</label>
                    <input type="date" name="deductions[${deductionIndex}][end_date]" class="form-control form-control-sm">
                </div>
            </td>
            <td>
                <div class="mb-2">
                    <textarea name="deductions[${deductionIndex}][description]" class="form-control form-control-sm" rows="2" placeholder="Description"></textarea>
                </div>
                <div>
                    <input type="text" name="deductions[${deductionIndex}][notes]" class="form-control form-control-sm" placeholder="Notes">
                </div>
            </td>
            <td>
                 <div class="form-check text-center mt-2">
                    <input class="form-check-input float-none" type="checkbox" name="deductions[${deductionIndex}][is_active]" value="1" id="activeDeduction${deductionIndex}" checked>
                    <label class="form-check-label d-block small" for="activeDeduction${deductionIndex}">Active</label>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.deduction-item').remove()">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#deductionsListBody').append(html);
    deductionIndex++;
}

function addDocument() {
    // Hide "no documents" message (using table row ID now)
    $('#noDocumentsMessage').hide();
    
    const html = `
        <tr class="document-item" data-index="${documentIndex}">
            <td>
                <select name="documents[${documentIndex}][document_type]" class="form-select form-select-sm mb-2">
                    <option value="">Select Type</option>
                    <option value="ID Card">ID Card</option>
                    <option value="Passport">Passport</option>
                    <option value="Birth Certificate">Birth Certificate</option>
                    <option value="Academic Certificate">Academic Certificate</option>
                    <option value="Professional License">Professional License</option>
                    <option value="Contract">Contract</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" name="documents[${documentIndex}][document_name]" class="form-control form-control-sm" placeholder="Doc Name">
            </td>
            <td>
                <input type="text" name="documents[${documentIndex}][document_number]" class="form-control form-control-sm mb-2" placeholder="Doc Number">
                <input type="text" name="documents[${documentIndex}][issued_by]" class="form-control form-control-sm" placeholder="Issued By">
            </td>
            <td>
                <div class="mb-2">
                    <small class="text-muted d-block">Issue:</small>
                    <input type="date" name="documents[${documentIndex}][issue_date]" class="form-control form-control-sm">
                </div>
                <div>
                    <small class="text-muted d-block">Expiry:</small>
                    <input type="date" name="documents[${documentIndex}][expiry_date]" class="form-control form-control-sm">
                </div>
            </td>
            <td>
                <input type="file" name="documents[${documentIndex}][file]" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small class="text-muted" style="font-size: 10px;">PDF, Images (Max 10MB)</small>
            </td>
            <td>
                <textarea name="documents[${documentIndex}][description]" class="form-control form-control-sm" rows="3" placeholder="Desc"></textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDocument($(this))">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#documentsListBody').append(html);
    documentIndex++;
}

function removeDocument(button) {
    button.closest('.document-item').remove();
    // Show "no documents" message if list is empty
    if ($('#documentsListBody .document-item').length === 0) {
        $('#noDocumentsMessage').show();
    }
}

function previewDocument(url, title) {
    $('#previewFrame').attr('src', url);
    $('#documentPreviewModal .modal-title').text('Preview: ' + title);
    $('#documentPreviewModal').modal('show');
}

function showToast(type, message) {
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-warning';
    const toast = $(`
        <div class="alert ${bgClass} alert-dismissible fade show position-fixed top-0 end-0 m-3 text-white" 
             style="z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    `);
    $('body').append(toast);
    setTimeout(() => toast.alert('close'), 5000);
}
</script>

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
    transition: all 0.2s ease;
}

.cursor-pointer:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.stage-badge {
    transition: all 0.3s ease;
}

#saveStatusIndicator {
    position: sticky;
    top: 20px;
    z-index: 1000;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.registration-step {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
@endpush
@endpush