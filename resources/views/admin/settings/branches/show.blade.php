@extends('layouts.app')

@section('title', 'Branch Details - ' . $branch->name . ' - OfisiLink')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.branches.page') }}">Branches</a></li>
                    <li class="breadcrumb-item active">{{ $branch->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-map me-2"></i>{{ $branch->name }}
                @if($branch->code)
                    <small class="text-muted">({{ $branch->code }})</small>
                @endif
            </h1>
            <p class="text-muted mb-0">Branch Details & Information</p>
        </div>
        <div>
            <a href="{{ route('admin.settings.branches.page') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Branches
            </a>
            <button class="btn btn-primary" onclick="editBranch({{ $branch->id }})">
                <i class="bx bx-edit me-1"></i>Edit Branch
            </button>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-{{ $branch->is_active ? 'success' : 'secondary' }} d-flex justify-content-between align-items-center" role="alert">
                <div>
                    <i class="bx bx-{{ $branch->is_active ? 'check-circle' : 'x-circle' }} me-2"></i>
                    <strong>Status:</strong> {{ $branch->is_active ? 'Active' : 'Inactive' }}
                </div>
                <div>
                    <small class="text-muted">
                        Created: {{ \Carbon\Carbon::parse($branch->created_at)->format('M d, Y') }} | 
                        Updated: {{ \Carbon\Carbon::parse($branch->updated_at)->format('M d, Y') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Information -->
        <div class="col-lg-8">
            <!-- Branch Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-info-circle me-2"></i>Branch Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-tag me-2 text-primary"></i>Branch Code:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($branch->code)
                                <code class="bg-light px-2 py-1 rounded">{{ $branch->code }}</code>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-building me-2 text-primary"></i>Branch Name:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="h5 mb-0">{{ $branch->name }}</span>
                        </div>
                    </div>
                    <hr>
                    @if($branch->address)
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-map me-2 text-primary"></i>Address:</strong>
                        </div>
                        <div class="col-md-8">
                            <p class="mb-0">{{ $branch->address }}</p>
                        </div>
                    </div>
                    <hr>
                    @endif
                    @if($branch->phone)
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-phone me-2 text-primary"></i>Phone:</strong>
                        </div>
                        <div class="col-md-8">
                            <a href="tel:{{ $branch->phone }}">{{ $branch->phone }}</a>
                        </div>
                    </div>
                    <hr>
                    @endif
                    @if($branch->email)
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-envelope me-2 text-primary"></i>Email:</strong>
                        </div>
                        <div class="col-md-8">
                            <a href="mailto:{{ $branch->email }}">{{ $branch->email }}</a>
                        </div>
                    </div>
                    <hr>
                    @endif
                    @if($branch->notes)
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-file-blank me-2 text-primary"></i>Notes:</strong>
                        </div>
                        <div class="col-md-8">
                            <p class="mb-0">{{ $branch->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Branch Managers Card -->
            @if($branch->managers && $branch->managers->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-user-check me-2"></i>Branch Managers
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($branch->managers as $manager)
                    <div class="d-flex align-items-center mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                        <div class="avatar-circle me-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                            {{ substr($manager->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $manager->name }}</h5>
                            <p class="text-muted mb-1">
                                <i class="bx bx-envelope me-1"></i>{{ $manager->email }}
                            </p>
                            @if($manager->phone)
                            <p class="text-muted mb-1">
                                <i class="bx bx-phone me-1"></i>{{ $manager->phone }}
                            </p>
                            @endif
                            @if($manager->employee_id)
                            <p class="text-muted mb-0">
                                <i class="bx bx-id-card me-1"></i>Employee ID: {{ $manager->employee_id }}
                            </p>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $manager->is_active ? 'success' : 'secondary' }}">
                                {{ $manager->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card shadow mb-4 border-warning">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-user-x me-2"></i>Branch Managers
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No branch managers assigned</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Statistics & Quick Info -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-stats me-2"></i>Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-group me-2 text-primary"></i>Total Employees:</span>
                            <strong class="h5 mb-0">{{ $branch->users->count() }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ min(100, ($branch->users->count() / 50) * 100) }}%"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-check-circle me-2 text-success"></i>Active Employees:</span>
                            <strong class="h5 mb-0">{{ $branch->users->where('is_active', true)->count() }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-x-circle me-2 text-secondary"></i>Inactive Employees:</span>
                            <strong class="h5 mb-0">{{ $branch->users->where('is_active', false)->count() }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-user-check me-2 text-info"></i>Managers:</span>
                            <strong class="h5 mb-0">{{ $branch->managers->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('modules.hr.employees') }}?branch={{ $branch->id }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-group me-1"></i>Branch Employees ({{ $branch->users->count() }})
                        </a>
                        <button class="btn btn-outline-info btn-sm" onclick="editBranch({{ $branch->id }})">
                            <i class="bx bx-edit me-1"></i>Edit Branch
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees List Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-group me-2"></i>Branch Employees ({{ $branch->users->count() }})
                    </h6>
                    @if($branch->users->count() > 0)
                    <a href="{{ route('modules.hr.employees') }}?branch={{ $branch->id }}" class="btn btn-sm btn-light">
                        <i class="bx bx-link-external me-1"></i>View All
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($branch->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Phone</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branch->users->take(10) as $employee)
                                    <tr>
                                        <td>
                                            <code>{{ $employee->employee_id ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $employee->name }}</strong>
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                                        </td>
                                        <td>
                                            {{ $employee->primaryDepartment->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $employee->phone ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $employee->is_active ? 'success' : 'secondary' }}">
                                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($branch->users->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('modules.hr.employees') }}?branch={{ $branch->id }}" class="btn btn-primary">
                                View All {{ $branch->users->count() }} Employees
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-group text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">No Employees in This Branch</h5>
                            <p class="text-muted">Employees will appear here once they are assigned to this branch.</p>
                            <a href="{{ route('modules.hr.employees') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i>Add Employee
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
function editBranch(branchId) {
    window.location.href = '{{ route('admin.settings.branches.page') }}';
    // Trigger edit modal on the branches page
    setTimeout(() => {
        if (typeof editBranch === 'function') {
            editBranch(branchId);
        }
    }, 100);
}
</script>
@endpush
@endsection






