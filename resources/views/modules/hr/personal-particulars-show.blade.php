@extends('layouts.app')

@section('title', 'Employee Details - ' . $employee->name . ' - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Employee Details</h4>
        <p class="text-muted">Complete employee information and profile</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Profile Completion</h6>
                            <h3 class="mb-0">{{ number_format($completionPercentage, 1) }}%</h3>
                        </div>
                        <div>
                            <i class="bx bx-check-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.3);">
                        <div class="progress-bar bg-white" style="width: {{ $completionPercentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Documents</h6>
                            <h3 class="mb-0">{{ $employee->documents ? $employee->documents->count() : 0 }}</h3>
                        </div>
                        <div>
                            <i class="bx bx-file-blank fs-1 opacity-50"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-2 text-white-50 small">Total uploaded</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Years of Service</h6>
                            <h3 class="mb-0">
                                @if($employee->hire_date)
                                    {{ round(\Carbon\Carbon::parse($employee->hire_date)->diffInYears(now())) }}
                                @else
                                    N/A
                                @endif
                            </h3>
                        </div>
                        <div>
                            <i class="bx bx-calendar fs-1 opacity-50"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-2 text-white-50 small">
                        @if($employee->hire_date)
                            Since {{ \Carbon\Carbon::parse($employee->hire_date)->format('M Y') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-{{ $employee->is_active ? 'success' : 'secondary' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Status</h6>
                            <h3 class="mb-0">{{ $employee->is_active ? 'Active' : 'Inactive' }}</h3>
                        </div>
                        <div>
                            <i class="bx bx-{{ $employee->is_active ? 'check-circle' : 'x-circle' }} fs-1 opacity-50"></i>
                        </div>
                    </div>
                    <p class="mb-0 mt-2 text-white-50 small">Employee status</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
        <div>
                            <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-user me-2"></i>Personal Particulars - {{ $employee->name }}
                            </h5>
                            <p class="text-white-50 mb-0">Employee ID: {{ $employee->employee_id ?? 'N/A' }}</p>
        </div>
        <div>
                            <a href="{{ route('modules.hr.personal-particulars') }}" class="btn btn-light btn-sm">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
                            @if($canEdit)
                            <a href="{{ route('personal-particulars.edit', $employee->id) }}" class="btn btn-light btn-sm">
                                <i class="bx bx-edit me-1"></i>Edit
                            </a>
                            @endif
                            <a href="{{ route('modules.hr.personal-particulars.registration-pdf', $employee->id) }}" class="btn btn-light btn-sm" target="_blank">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </a>
                        </div>
        </div>
    </div>
                <div class="card-body">
                    <!-- Navigation Tabs -->
                    <div class="nav-tabs-wrapper mb-4">
                        <ul class="nav nav-tabs nav-tabs-scrollable" id="particularsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                    <i class="bx bx-user me-1"></i>Personal Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                                    <i class="bx bx-briefcase me-1"></i>Employment
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab">
                                    <i class="bx bx-phone me-1"></i>Contacts
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">
                                    <i class="bx bx-book me-1"></i>Education
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="banking-tab" data-bs-toggle="tab" data-bs-target="#banking" type="button" role="tab">
                                    <i class="bx bx-credit-card me-1"></i>Banking
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                                    <i class="bx bx-file-blank me-1"></i>Documents
                                    @if($employee->documents && $employee->documents->count() > 0)
                                        <span class="badge bg-primary ms-1">{{ $employee->documents->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="statutory-tab" data-bs-toggle="tab" data-bs-target="#statutory" type="button" role="tab">
                                    <i class="bx bx-file me-1"></i>Statutory
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="particularsTabContent">
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-user me-2"></i>Personal Information</h5>
                                </div>
                                <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    @if($employee->photo)
                                    <img src="{{ asset('storage/photos/' . $employee->photo) }}" alt="Photo" class="img-thumbnail" style="max-width: 150px;">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; margin: 0 auto;">
                                        <i class="bx bx-user" style="font-size: 60px; color: #ccc;"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-borderless">
                                        <tr><th width="200">Full Name:</th><td>{{ $employee->name }}</td></tr>
                                        <tr><th>Email:</th><td><a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a></td></tr>
                                        <tr><th>Phone:</th><td><a href="tel:{{ $employee->phone }}">{{ $employee->phone ?? 'N/A' }}</a></td></tr>
                                        <tr><th>Employee ID:</th><td><code>{{ $employee->employee_id ?? 'N/A' }}</code></td></tr>
                                        <tr><th>Department:</th><td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td></tr>
                                        <tr><th>Branch:</th><td>{{ $employee->branch->name ?? 'N/A' }}@if($employee->branch && $employee->branch->code) ({{ $employee->branch->code }})@endif</td></tr>
                                        <tr><th>Date of Birth:</th><td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</td></tr>
                                        <tr><th>Gender:</th><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
                                        <tr><th>Marital Status:</th><td>{{ $employee->marital_status ?? 'N/A' }}</td></tr>
                                        <tr><th>Nationality:</th><td>{{ $employee->nationality ?? 'N/A' }}</td></tr>
                                        <tr><th>Address:</th><td>{{ $employee->address ?? 'N/A' }}</td></tr>
                                        <tr><th>Hire Date:</th><td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d M Y') : 'N/A' }}</td></tr>
                                        @if($employee->roles && $employee->roles->count() > 0)
                                        <tr><th>Roles:</th><td>
                                            @foreach($employee->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->display_name ?? $role->name }}</span>
                                            @endforeach
                                        </td></tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- Employment Tab -->
                        <div class="tab-pane fade" id="employment" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-briefcase me-2"></i>Employment Details</h5>
                                </div>
                                <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">Position:</th><td>{{ $employee->employee->position ?? 'N/A' }}</td></tr>
                                <tr><th>Employment Type:</th><td>
                                    <span class="badge bg-{{ $employee->employee->employment_type == 'permanent' ? 'success' : ($employee->employee->employment_type == 'contract' ? 'warning' : 'info') }}">
                                        {{ ucfirst($employee->employee->employment_type ?? 'N/A') }}
                                    </span>
                                </td></tr>
                                <tr><th>Hire Date:</th><td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d M Y') : 'N/A' }}</td></tr>
                                <tr><th>Salary:</th><td>{{ $employee->employee->salary ? 'TZS ' . number_format($employee->employee->salary, 2) : 'N/A' }}</td></tr>
                                <tr><th>Years of Service:</th><td>
                                    @if($employee->hire_date)
                                        {{ round(\Carbon\Carbon::parse($employee->hire_date)->diffInYears(now())) }} years
                        @else
                                        N/A
                        @endif
                                </td></tr>
                            </table>
                        </div>
                    </div>
                        </div>

                        <!-- Contacts Tab -->
                        <div class="tab-pane fade" id="contacts" role="tabpanel">
                            <!-- Emergency Contact -->
                            <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-phone-call me-2"></i>3. Emergency Contact</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="200">Contact Name:</th><td>{{ $employee->employee->emergency_contact_name ?? 'N/A' }}</td></tr>
                                <tr><th>Contact Phone:</th><td>
                                    @if($employee->employee->emergency_contact_phone)
                                        <a href="tel:{{ $employee->employee->emergency_contact_phone }}">{{ $employee->employee->emergency_contact_phone }}</a>
                                    @else
                                        N/A
                    @endif
                                </td></tr>
                                <tr><th>Relationship:</th><td>{{ $employee->employee->emergency_contact_relationship ?? 'N/A' }}</td></tr>
                                <tr><th>Address:</th><td>{{ $employee->employee->emergency_contact_address ?? 'N/A' }}</td></tr>
                            </table>
                </div>
            </div>

                            </div>

                            <!-- Family Information -->
                            @if($employee->family && $employee->family->count() > 0)
                            <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-group me-2"></i>4. Family Information</h5>
                </div>
                <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Date of Birth</th>
                                            <th>Gender</th>
                                            <th>Occupation</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Dependent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->family as $member)
                                        <tr>
                                            <td><strong>{{ $member->name }}</strong></td>
                                            <td>{{ $member->relationship }}</td>
                                            <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $member->gender ?? 'N/A' }}</td>
                                            <td>{{ $member->occupation ?? 'N/A' }}</td>
                                            <td>{{ $member->phone ?? 'N/A' }}</td>
                                            <td>{{ $member->email ?? 'N/A' }}</td>
                                            <td><span class="badge bg-{{ $member->is_dependent ? 'success' : 'secondary' }}">{{ $member->is_dependent ? 'Yes' : 'No' }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-group me-2"></i>4. Family Information</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No family information recorded</p>
                        </div>
                    </div>
                    @endif

                            <!-- Next of Kin -->
                            @if($employee->nextOfKin && $employee->nextOfKin->count() > 0)
                            <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>5. Next of Kin</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>ID Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->nextOfKin as $kin)
                                        <tr>
                                            <td><strong>{{ $kin->name }}</strong></td>
                                            <td>{{ $kin->relationship }}</td>
                                            <td><a href="tel:{{ $kin->phone }}">{{ $kin->phone }}</a></td>
                                            <td>{{ $kin->email ?? 'N/A' }}</td>
                                            <td>{{ $kin->address }}</td>
                                            <td>{{ $kin->id_number ?? 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>5. Next of Kin</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-3">No next of kin information recorded</p>
                        </div>
                    </div>
                    @endif

                            <!-- Referees -->
                            @if($employee->referees && $employee->referees->count() > 0)
                            <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-voice me-2"></i>6. Referees</h5>
                </div>
                <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Organization</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Relationship</th>
                                            <th>Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->referees as $referee)
                                        <tr>
                                            <td><strong>{{ $referee->name }}</strong></td>
                                            <td>{{ $referee->position ?? 'N/A' }}</td>
                                            <td>{{ $referee->organization ?? 'N/A' }}</td>
                                            <td><a href="tel:{{ $referee->phone }}">{{ $referee->phone }}</a></td>
                                            <td>{{ $referee->email ?? 'N/A' }}</td>
                                            <td>{{ $referee->relationship ?? 'N/A' }}</td>
                                            <td>{{ $referee->address ?? 'N/A' }}</td>
                                        </tr>
                    @endforeach
                                    </tbody>
                                </table>
                </div>
            </div>
        </div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-user-voice me-2"></i>6. Referees</h5>
                </div>
                <div class="card-body">
                            <p class="text-muted text-center py-3">No referees information recorded</p>
                        </div>
                            </div>
                    @endif
                        </div>

                        <!-- Education Tab -->
                        <div class="tab-pane fade" id="education" role="tabpanel">
                            @if($employee->educations && $employee->educations->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-book me-2"></i>Education Background</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Institution</th>
                                                    <th>Qualification</th>
                                                    <th>Field of Study</th>
                                                    <th>Start Year</th>
                                                    <th>End Year</th>
                                                    <th>Grade</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employee->educations as $education)
                                                <tr>
                                                    <td><strong>{{ $education->institution_name }}</strong></td>
                                                    <td>{{ $education->qualification }}</td>
                                                    <td>{{ $education->field_of_study ?? 'N/A' }}</td>
                                                    <td>{{ $education->start_year ?? 'N/A' }}</td>
                                                    <td>{{ $education->end_year ?? 'N/A' }}</td>
                                                    <td>{{ $education->grade ?? 'N/A' }}</td>
                                                    <td>{{ $education->description ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-book me-2"></i>Education Background</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted text-center py-3">No education information recorded</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Banking Tab -->
                        <div class="tab-pane fade" id="banking" role="tabpanel">
                            @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>Banking Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Bank Name</th>
                                                    <th>Account Number</th>
                                                    <th>Account Name</th>
                                            <th>Branch Name</th>
                                            <th>SWIFT Code</th>
                                            <th>Primary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->bankAccounts as $account)
                                        <tr class="{{ $account->is_primary ? 'table-success' : '' }}">
                                            <td><strong>{{ $account->bank_name }}</strong></td>
                                            <td><code>{{ $account->account_number }}</code></td>
                                            <td>{{ $account->account_name ?? 'N/A' }}</td>
                                            <td>{{ $account->branch_name ?? 'N/A' }}</td>
                                            <td>{{ $account->swift_code ?? 'N/A' }}</td>
                                            <td>
                                                @if($account->is_primary)
                                                    <span class="badge bg-success">Primary</span>
                                                @else
                                                    <span class="badge bg-secondary">Secondary</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                            @else
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>Banking Information</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted text-center py-3">No banking information recorded</p>
                                </div>
                            </div>
                            @endif

                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            @if($employee->documents && $employee->documents->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bx bx-file-blank me-2"></i>Documents ({{ $employee->documents->count() }})</h5>
                                    <div>
                                        <input type="text" id="documentSearch" class="form-control form-control-sm" placeholder="Search documents..." style="width: 250px; display: inline-block;">
                                        <select id="documentTypeFilter" class="form-select form-select-sm" style="width: 150px; display: inline-block;">
                                            <option value="">All Types</option>
                                            @foreach($employee->documents->pluck('document_type')->unique() as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="documentsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Document Name</th>
                                            <th>Document Number</th>
                                            <th>Issue Date</th>
                                            <th>Expiry Date</th>
                                            <th>Issued By</th>
                                            <th>File Size</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->documents as $document)
                                        <tr>
                                            <td><strong>{{ $document->document_type }}</strong></td>
                                            <td>{{ $document->document_name }}</td>
                                            <td><code>{{ $document->document_number ?? 'N/A' }}</code></td>
                                            <td>{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                @if($document->expiry_date)
                                                    @php
                                                        $expiryDate = \Carbon\Carbon::parse($document->expiry_date);
                                                        $isExpired = $expiryDate->isPast();
                                                        $isExpiringSoon = $expiryDate->diffInDays(now()) <= 30;
                                                    @endphp
                                                    <span class="{{ $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : '') }}">
                                                        {{ $expiryDate->format('d M Y') }}
                                                        @if($isExpired)
                                                            <span class="badge bg-danger">Expired</span>
                                                        @elseif($isExpiringSoon)
                                                            <span class="badge bg-warning">Expiring Soon</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $document->issued_by ?? 'N/A' }}</td>
                                            <td>
                                                @if($document->file_size)
                                                    {{ number_format($document->file_size / 1024, 2) }} KB
                                                @else
                                                    N/A
                        @endif
                                            </td>
                                            <td>
                                                @if($document->file_path)
                                                    @php
                                                        $fileUrl = asset('storage/documents/' . basename($document->file_path));
                                                        $downloadUrl = route('personal-particulars.documents.download', ['employee' => $employee->id, 'document' => $document->id]);
                                                        $fileName = $document->file_name ?? basename($document->file_path);
                                                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                    @endphp
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            onclick="previewDocument('{{ $fileUrl }}', '{{ $fileName }}', '{{ $fileExt }}', '{{ $downloadUrl }}', {{ $document->id }})">
                                                        <i class="bx bx-show"></i> Preview
                                                    </button>
                                                    <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-success" download>
                                                        <i class="bx bx-download"></i> Download
                                                    </a>
                                                @else
                                                    <span class="text-muted">No file</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                            @else
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-file-blank me-2"></i>Documents</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted text-center py-3">No documents uploaded</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Statutory Tab -->
                        <div class="tab-pane fade" id="statutory" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bx bx-file me-2"></i>Statutory Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr><th width="200">NIDA Number:</th><td><code>{{ $employee->employee->nida_number ?? 'N/A' }}</code></td></tr>
                                                <tr><th width="200">TIN Number:</th><td><code>{{ $employee->employee->tin_number ?? 'N/A' }}</code></td></tr>
                                                <tr><th>NSSF Number:</th><td><code>{{ $employee->employee->nssf_number ?? 'N/A' }}</code></td></tr>
                                                <tr><th>NHIF Number:</th><td><code>{{ $employee->employee->nhif_number ?? 'N/A' }}</code></td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr><th width="200">HESLB Number:</th><td><code>{{ $employee->employee->heslb_number ?? 'N/A' }}</code></td></tr>
                                                <tr><th>Has Student Loan:</th><td>
                                                    <span class="badge bg-{{ $employee->employee->has_student_loan ? 'warning' : 'success' }}">
                                                        {{ $employee->employee->has_student_loan ? 'Yes' : 'No' }}
                                                    </span>
                                                </td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('modules.hr.personal-particulars') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back to Employees
                        </a>
                        <div>
                            <a href="{{ route('modules.hr.personal-particulars.registration-pdf', $employee->id) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bx bx-file-blank me-1"></i>Generate PDF
                            </a>
                            @if($canEdit)
                            <a href="{{ route('personal-particulars.edit', $employee->id) }}" class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i>Edit Employee
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="documentPreviewModalLabel">
                        <i class="bx bx-file-blank me-2"></i>Document Preview
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="documentPreviewContent" style="min-height: 500px; max-height: 80vh; overflow: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Close
                    </button>
                    <a href="#" id="documentPreviewDownload" class="btn btn-primary" download>
                        <i class="bx bx-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn btn-info" onclick="window.open(document.getElementById('documentPreviewDownload').href, '_blank')">
                        <i class="bx bx-link-external me-1"></i>Open in New Tab
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editEmployee(id) {
    window.location.href = '{{ route("personal-particulars.edit", ":id") }}'.replace(':id', id);
}

function uploadPhoto(id) {
    window.location.href = '{{ route("modules.hr.personal-particulars") }}?upload=' + id;
}

// Document Preview Function
function previewDocument(fileUrl, fileName, fileExt, downloadUrl, documentId) {
    const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
    const title = document.getElementById('documentPreviewModalLabel');
    const content = document.getElementById('documentPreviewContent');
    const downloadLink = document.getElementById('documentPreviewDownload');
    
    // Update modal title
    title.innerHTML = '<i class="bx bx-file-blank me-2"></i>' + (fileName || 'Document Preview');
    
    // Set download link
    downloadLink.href = downloadUrl || fileUrl;
    downloadLink.download = fileName;
    
    // Show loading
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    // Determine file type
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
    const isImage = imageExts.includes(fileExt.toLowerCase());
    const isPdf = fileExt.toLowerCase() === 'pdf';
    
    // Load preview based on file type
    if (isImage) {
        // Image preview
        const img = new Image();
        img.onload = function() {
            content.innerHTML = `
                <div class="text-center p-3" style="background: #f8f9fa;">
                    <img src="${fileUrl}" class="img-fluid" alt="${fileName}" style="max-height: 75vh; width: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                </div>
            `;
        };
        img.onerror = function() {
            content.innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error loading image.</strong> Please try downloading the file.
                </div>
            `;
        };
        img.src = fileUrl;
    } else if (isPdf) {
        // PDF preview using iframe with fallback
        content.innerHTML = `
            <div style="width: 100%; height: 75vh; position: relative;">
                <iframe 
                    src="${fileUrl}#toolbar=1&navpanes=1&scrollbar=1" 
                    style="width: 100%; height: 100%; border: none;"
                    title="PDF Preview"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                </iframe>
                <embed 
                    src="${fileUrl}" 
                    type="application/pdf" 
                    style="width: 100%; height: 100%; border: none; display: none;"
                    onerror="this.style.display='none'; this.previousElementSibling.style.display='block';">
                </embed>
                <div class="alert alert-info m-3" style="display: none;">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>PDF Preview Unavailable</strong><br>
                    Your browser may not support PDF preview. Please download the file to view it.
                </div>
            </div>
        `;
    } else {
        // Other file types - show download option
        const fileIcon = getFileIcon(fileExt);
        content.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="${fileIcon}" style="font-size: 80px; color: #6c757d;"></i>
                </div>
                <h5 class="text-muted mb-3">Preview Not Available</h5>
                <p class="text-muted mb-4">Preview is not available for <strong>${fileExt.toUpperCase()}</strong> files.<br>Please download the file to view it.</p>
                <a href="${downloadUrl || fileUrl}" download="${fileName}" class="btn btn-primary btn-lg">
                    <i class="bx bx-download me-2"></i>Download Document
                </a>
            </div>
        `;
    }
}

// Get file icon based on extension
function getFileIcon(ext) {
    const icons = {
        'doc': 'bx bx-file-blank',
        'docx': 'bx bx-file-blank',
        'xls': 'bx bx-file',
        'xlsx': 'bx bx-file',
        'ppt': 'bx bx-file-blank',
        'pptx': 'bx bx-file-blank',
        'txt': 'bx bx-file',
        'zip': 'bx bx-archive',
        'rar': 'bx bx-archive',
    };
    return icons[ext.toLowerCase()] || 'bx bx-file';
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // ESC to close modal
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('documentPreviewModal'));
        if (modal) {
            modal.hide();
        }
    }
});

// Document search and filter functionality
$(document).ready(function() {
    const $searchInput = $('#documentSearch');
    const $typeFilter = $('#documentTypeFilter');
    const $table = $('#documentsTable tbody');
    
    function filterDocuments() {
        const searchTerm = $searchInput.val().toLowerCase();
        const typeFilter = $typeFilter.val().toLowerCase();
        
        $table.find('tr').each(function() {
            const $row = $(this);
            const documentType = $row.find('td:first').text().toLowerCase();
            const documentName = $row.find('td:nth-child(2)').text().toLowerCase();
            const documentNumber = $row.find('td:nth-child(3)').text().toLowerCase();
            
            const matchesSearch = !searchTerm || 
                documentType.includes(searchTerm) || 
                documentName.includes(searchTerm) || 
                documentNumber.includes(searchTerm);
            
            const matchesType = !typeFilter || documentType.includes(typeFilter);
            
            if (matchesSearch && matchesType) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }
    
    $searchInput.on('input', filterDocuments);
    $typeFilter.on('change', filterDocuments);
    
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    .card-header {
        border-bottom: 2px solid #dee2e6;
    }
    .table th {
        font-weight: 600;
        color: #495057;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.35em 0.65em;
    }
    code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
    .nav-tabs-wrapper {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 1.5rem;
    }
    .nav-tabs-scrollable {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
    }
    .nav-tabs-scrollable::-webkit-scrollbar {
        height: 6px;
    }
    .nav-tabs-scrollable::-webkit-scrollbar-track {
        background: transparent;
    }
    .nav-tabs-scrollable::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 3px;
    }
    .nav-tabs-scrollable::-webkit-scrollbar-thumb:hover {
        background-color: #adb5bd;
    }
    .nav-tabs .nav-item {
        flex-shrink: 0;
        white-space: nowrap;
    }
    .nav-tabs .nav-link {
        color: #495057;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.25rem;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    .nav-tabs .nav-link:hover {
        border-bottom-color: #dee2e6;
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        font-weight: 600;
        background-color: rgba(13, 110, 253, 0.1);
    }
    @media (max-width: 768px) {
        .nav-tabs-scrollable {
            overflow-x: auto;
        }
        .nav-tabs .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }
    #documentPreviewContent img {
        max-width: 100%;
        height: auto;
    }
    #documentPreviewContent iframe,
    #documentPreviewContent embed {
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .quick-stats-card {
        transition: transform 0.2s;
    }
    .quick-stats-card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

