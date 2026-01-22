@extends('layouts.app')

@section('title', 'My Documents - Digital Files')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .upload-area {
        border: 2px dashed #11998e;
        border-radius: 12px;
        transition: all 0.3s;
    }
    .upload-area:hover {
        border-color: #38ef7d;
        background-color: rgba(17, 153, 142, 0.05);
    }
    .upload-area.dragover {
        border-color: #38ef7d;
        background-color: rgba(17, 153, 142, 0.1);
    }
    .badge-expiring {
        background-color: #ffc107;
        color: #000;
    }
    .badge-expired {
        background-color: #dc3545;
        color: #fff;
    }
    /* Ensure SweetAlert2 appears on top of everything */
    .swal2-container {
        z-index: 99999 !important;
    }
    .swal2-popup {
        z-index: 99999 !important;
    }
    .swal2-backdrop-show {
        z-index: 99998 !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-gradient-success text-white" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-folder me-2"></i>My Documents
                            </h3>
                            <p class="mb-0 text-white-50">View and manage all your documents</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                                <i class="bx bx-folder-plus me-1"></i>Create Folder
                            </button>
                            <button class="btn btn-light btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                <i class="bx bx-upload me-1"></i>Upload Document
                            </button>
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Files
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #11998e;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success bg-opacity-10">
                            <i class="bx bx-file fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Documents</h6>
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #38ef7d;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success bg-opacity-10">
                            <i class="bx bx-data fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Size</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_size'] / 1024 / 1024, 2) }} MB</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #11998e;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success bg-opacity-10">
                            <i class="bx bx-folder fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">My Folders</h6>
                            <h4 class="mb-0 fw-bold">{{ $myFolders->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #38ef7d;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success bg-opacity-10">
                            <i class="bx bx-user fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Employee Docs</h6>
                            <h4 class="mb-0 fw-bold">{{ $stats['employee_documents'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Folders Section -->
    @if($myFolders->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-folder me-2 text-success"></i>My Folders
                        <span class="badge bg-success ms-2">{{ $myFolders->count() }}</span>
                    </h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="bx bx-folder-plus me-1"></i>Create Folder
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($myFolders as $folder)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #11998e;">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="avatar avatar-lg me-3 bg-success bg-opacity-10">
                                            <i class="bx bx-folder fs-2 text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold" title="{{ $folder->name }}">
                                                {{ \Illuminate\Support\Str::limit($folder->name, 20) }}
                                            </h6>
                                            @if($folder->description)
                                                <small class="text-muted d-block">
                                                    {{ \Illuminate\Support\Str::limit($folder->description, 30) }}
                                                </small>
                                            @endif
                                            <small class="text-muted d-block">
                                                <i class="bx bx-file me-1"></i>{{ $folder->files_count ?? 0 }} files
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="bx bx-calendar me-1"></i>{{ $folder->created_at->format('M d, Y') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 mt-2">
                                        <a href="{{ route('modules.files.digital.folder.detail', $folder->id) }}" class="btn btn-sm btn-success flex-fill">
                                            <i class="bx bx-show me-1"></i>View
                                        </a>
                                        <button class="btn btn-sm btn-outline-success" onclick="uploadToFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}')" title="Upload to this folder">
                                            <i class="bx bx-upload"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Assigned Documents Section -->
    @if(isset($assignedDocuments) && $assignedDocuments->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #17a2b8;">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-user-check me-2 text-info"></i>Assigned Documents
                        <span class="badge bg-info ms-2">{{ $assignedDocuments->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>Document Name</th>
                                    <th>Assigned By</th>
                                    <th>Assigned Date</th>
                                    <th>Expiry Date</th>
                                    <th>File Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedDocuments as $document)
                                <tr>
                                    <td><strong>{{ $document['document_type'] }}</strong></td>
                                    <td>{{ $document['document_name'] }}</td>
                                    <td>{{ $document['assigned_by'] ?? 'System' }}</td>
                                    <td>{{ $document['assigned_at'] ? \Carbon\Carbon::parse($document['assigned_at'])->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        @if($document['expiry_date'])
                                            @php
                                                $expiryDate = \Carbon\Carbon::parse($document['expiry_date']);
                                                $isExpired = $expiryDate->isPast();
                                                $isExpiringSoon = $expiryDate->isFuture() && $expiryDate->diffInDays(now()) <= 30;
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
                                            <span class="text-muted">No expiry</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($document['file_size'] / 1024, 2) }} KB</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @php
                                                $previewUrl = route('modules.files.digital.preview', $document['id']);
                                                $downloadUrl = Storage::url($document['file_path']);
                                            @endphp
                                            <button class="btn btn-sm btn-info" onclick="previewDocument('{{ $previewUrl }}', '{{ addslashes($document['document_name']) }}', 'assigned_file', '{{ $downloadUrl }}', '{{ $document['file_path'] ?? '' }}')" title="Preview">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <a href="{{ $downloadUrl }}" download class="btn btn-sm btn-outline-info" title="Download">
                                                <i class="bx bx-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- My Working Documents (Uploaded by Employee) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745;">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-briefcase me-2 text-success"></i>My Working Documents
                        <span class="badge bg-success ms-2">{{ $paginatedWorking->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="searchWorkingDocuments" class="form-control form-control-sm" placeholder="Search documents..." style="width: 250px;">
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                            <i class="bx bx-upload me-1"></i>Upload
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($paginatedWorking->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Document Name</th>
                                        <th>Folder</th>
                                        <th>Upload Date</th>
                                        <th>File Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="workingDocumentsTableBody">
                                    @foreach($paginatedWorking as $document)
                                    <tr class="working-document-row" data-name="{{ strtolower($document['document_name']) }}" data-type="{{ strtolower($document['document_type']) }}">
                                        <td><strong>{{ $document['document_type'] }}</strong></td>
                                        <td>{{ $document['document_name'] }}</td>
                                        <td><span class="badge bg-secondary">{{ $document['folder_name'] }}</span></td>
                                        <td>{{ $document['created_at'] ? \Carbon\Carbon::parse($document['created_at'])->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ number_format($document['file_size'] / 1024, 2) }} KB</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @php
                                                    $previewUrl = route('modules.files.digital.preview', $document['id']);
                                                    $downloadUrl = Storage::url($document['file_path']);
                                                @endphp
                                                <button class="btn btn-sm btn-success" onclick="previewDocument('{{ $previewUrl }}', '{{ addslashes($document['document_name']) }}', 'digital_file', '{{ $downloadUrl }}', '{{ $document['file_path'] ?? '' }}')" title="Preview">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ $downloadUrl }}" download class="btn btn-sm btn-outline-success" title="Download">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info" onclick="openAssignModal({{ $document['id'] }}, '{{ addslashes($document['document_name']) }}')" title="Assign to Staff">
                                                    <i class="bx bx-user-plus"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="viewFileDetails({{ $document['id'] }})" title="View Details">
                                                    <i class="bx bx-info-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $paginatedWorking->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-briefcase fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No working documents found. Upload your first document to get started.</p>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                <i class="bx bx-upload me-1"></i>Upload Document
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- My Employee Documents (Uploaded by HR/Admin) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107;">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-id-card me-2 text-warning"></i>My Employee Documents
                        <span class="badge bg-warning text-dark ms-2">{{ $paginatedEmployee->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="searchEmployeeDocuments" class="form-control form-control-sm" placeholder="Search documents..." style="width: 250px;">
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> These documents are uploaded by HR/Administration. You cannot upload employee documents yourself.
                    </div>
                    @if($paginatedEmployee->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
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
                                <tbody id="employeeDocumentsTableBody">
                                    @foreach($paginatedEmployee as $document)
                                    <tr class="employee-document-row" data-name="{{ strtolower($document['document_name']) }}" data-type="{{ strtolower($document['document_type']) }}">
                                        <td><strong>{{ $document['document_type'] }}</strong></td>
                                        <td>{{ $document['document_name'] }}</td>
                                        <td><code>{{ $document['document_number'] }}</code></td>
                                        <td>{{ $document['issue_date'] ? \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') : 'N/A' }}</td>
                                        <td>
                                            @if($document['expiry_date'])
                                                @php
                                                    $expiryDate = \Carbon\Carbon::parse($document['expiry_date']);
                                                    $isExpired = $expiryDate->isPast();
                                                    $isExpiringSoon = $expiryDate->isFuture() && $expiryDate->diffInDays(now()) <= 30;
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
                                        <td>{{ $document['issued_by'] }}</td>
                                        <td>{{ number_format($document['file_size'] / 1024, 2) }} KB</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @php
                                                    $docUrl = $document['file_url'] ?? asset('storage/documents/' . $document['file_path']);
                                                    $previewUrl = $docUrl;
                                                    $downloadUrl = $docUrl;
                                                @endphp
                                                <button class="btn btn-sm btn-warning" onclick="previewDocument('{{ $previewUrl }}', '{{ addslashes($document['document_name']) }}', 'employee_document', '{{ $downloadUrl }}', '{{ $document['file_path'] ?? '' }}')" title="Preview">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <a href="{{ $downloadUrl }}" download class="btn btn-sm btn-outline-warning" title="Download">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $paginatedEmployee->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-id-card fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No employee documents found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-success text-white" style="border-radius: 12px 12px 0 0; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bx bx-folder-plus me-2"></i>Create New Folder
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFolderForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Folder Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" name="folder_name" required placeholder="Enter folder name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter folder description (optional)"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Parent Folder</label>
                        <select class="form-select" name="parent_id">
                            <option value="0">Root (No Parent)</option>
                            @foreach($myFolders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Access Level <span class="text-danger">*</span></label>
                            <select class="form-select" name="access_level" id="folderAccessLevel" required>
                                <option value="private" selected>Private (Only me)</option>
                                <option value="department">Department (My Department)</option>
                                <option value="public">Public (All users)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="departmentFieldContainer" style="display: none;">
                            <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                            <select class="form-select" name="department_id" id="folderDepartmentId">
                                <option value="">-- Select Department --</option>
                                @php
                                    $userDeptId = Auth::user()->department_id ?? null;
                                    $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
                                @endphp
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $userDeptId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="folderCodeContainer">
                            <label class="form-label fw-bold">Folder Code</label>
                            <input type="text" class="form-control" name="folder_code" placeholder="Optional folder code">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-folder-plus me-1"></i>Create Folder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-success text-white" style="border-radius: 12px 12px 0 0; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bx bx-upload me-2"></i>Upload Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadDocumentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Folder <span class="text-muted">(Optional)</span></label>
                        <select class="form-select form-select-lg" name="folder_id" id="uploadFolderId">
                            <option value="">-- Select Folder (Optional) --</option>
                            @if($myFolders->count() > 0)
                            <optgroup label="My Folders">
                                @foreach($myFolders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }} ({{ $folder->files_count ?? 0 }} files)</option>
                                @endforeach
                            </optgroup>
                            @endif
                            @if($availableFolders->count() > 0)
                            <optgroup label="Available Folders">
                                @foreach($availableFolders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }} 
                                        @if($folder->department)
                                            ({{ $folder->department->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        <small class="text-muted">Select a folder to organize your document (optional)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Document Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter a description for this document (optional)"></textarea>
                    </div>
                    
                    <div class="upload-area p-5 text-center" id="uploadDropzone">
                        <i class="bx bx-cloud-upload fs-1 text-success mb-3"></i>
                        <h5>Drag & Drop Files Here</h5>
                        <p class="text-muted">or click to browse</p>
                        <input type="file" id="documentFileInput" name="file" class="d-none" required>
                        <button type="button" class="btn btn-success" onclick="document.getElementById('documentFileInput').click()">
                            <i class="bx bx-folder-open me-2"></i>Select File
                        </button>
                        <div id="selectedFileInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bx bx-file me-2"></i>
                                <span id="selectedFileName"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearSelectedFile()">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Access Level <span class="text-danger">*</span></label>
                            <select class="form-select" name="access_level" id="uploadAccessLevel" required>
                                <option value="private" selected>Private (Only assigned users)</option>
                                <option value="department">Department (My Department)</option>
                                <option value="public">Public (All users)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Confidential Level <span class="text-danger">*</span></label>
                            <select class="form-select" name="confidential_level" required>
                                <option value="normal" selected>Normal</option>
                                <option value="confidential">Confidential</option>
                                <option value="strictly_confidential">Strictly Confidential</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-upload me-1"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Document Modal -->
<div class="modal fade" id="assignDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bx bx-user-plus me-2"></i>Assign Document to Staff
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignDocumentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Document:</label>
                        <input type="text" id="assignDocumentName" class="form-control" readonly>
                        <input type="hidden" id="assignDocumentId" name="file_id">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Staff Members <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <input type="text" id="searchStaff" class="form-control" placeholder="Search staff by name or email...">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearStaffSearch()">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;" id="staffListContainer">
                            <div class="text-center py-3">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Select one or more staff members to assign this document to</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Selected Staff:</label>
                        <div id="selectedStaffList" class="d-flex flex-wrap gap-2">
                            <span class="text-muted">No staff selected</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="bx bx-user-plus me-1"></i>Assign Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- File Details Modal -->
<div class="modal fade" id="fileDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bx bx-info-circle me-2"></i>File Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="fileDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-success text-white" style="border-radius: 12px 12px 0 0; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <h5 class="modal-title text-white fw-bold" id="previewModalTitle">Document Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body preview-modal-content p-0" id="previewModalContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <a href="#" id="previewDownloadLink" class="btn btn-success" download>
                    <i class="bx bx-download me-1"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Search functionality
// Search Working Documents
$('#searchWorkingDocuments').on('input', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.working-document-row').each(function() {
        const name = $(this).data('name') || '';
        const type = $(this).data('type') || '';
        if (name.includes(searchTerm) || type.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Search Employee Documents
$('#searchEmployeeDocuments').on('input', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.employee-document-row').each(function() {
        const name = $(this).data('name') || '';
        const type = $(this).data('type') || '';
        if (name.includes(searchTerm) || type.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Legacy search (for backward compatibility)
$('#searchDocuments').on('input', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.document-row').each(function() {
        const name = $(this).data('name') || '';
        const type = $(this).data('type') || '';
        if (name.includes(searchTerm) || type.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Upload to specific folder
function uploadToFolder(folderId, folderName) {
    // Set the folder in the upload modal
    $('#uploadFolderId').val(folderId);
    // Show the upload modal
    const uploadModal = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
    uploadModal.show();
    
    // Show a brief message that folder is selected
    Swal.fire({
        icon: 'info',
        title: 'Folder Selected',
        text: `Files will be uploaded to: ${folderName}`,
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        customClass: {
            container: 'swal2-container-high-zindex',
            popup: 'swal2-popup-high-zindex'
        }
    });
}

// Create Folder Form
$('#createFolderForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const submitBtn = $(this).find('button[type="submit"]');
    const originalHtml = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: formData + '&action=create_folder',
        headers: {
            'X-CSRF-TOKEN': token
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Folder created successfully',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        container: 'swal2-container-high-zindex',
                        popup: 'swal2-popup-high-zindex'
                    },
                    didOpen: () => {
                        // Ensure it's on top of all elements
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                        }
                        const swalPopup = document.querySelector('.swal2-popup');
                        if (swalPopup) {
                            swalPopup.style.zIndex = '100000';
                        }
                    }
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to create folder',
                    customClass: {
                        container: 'swal2-container-high-zindex',
                        popup: 'swal2-popup-high-zindex'
                    },
                    didOpen: () => {
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                        }
                    }
                });
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response?.message || 'An error occurred while creating folder',
                customClass: {
                    container: 'swal2-container-high-zindex',
                    popup: 'swal2-popup-high-zindex'
                },
                didOpen: () => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.style.zIndex = '99999';
                    }
                }
            });
            submitBtn.prop('disabled', false).html(originalHtml);
        }
    });
});

// File upload handling
const dropzone = $('#uploadDropzone');
const fileInput = $('#documentFileInput');
const selectedFileInfo = $('#selectedFileInfo');
const selectedFileName = $('#selectedFileName');

dropzone.on('dragover', function(e) {
    e.preventDefault();
    $(this).addClass('dragover');
});

dropzone.on('dragleave', function(e) {
    e.preventDefault();
    $(this).removeClass('dragover');
});

dropzone.on('drop', function(e) {
    e.preventDefault();
    $(this).removeClass('dragover');
    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
        fileInput[0].files = files;
        handleFileSelect(files[0]);
    }
});

fileInput.on('change', function() {
    if (this.files.length > 0) {
        handleFileSelect(this.files[0]);
    }
});

function handleFileSelect(file) {
    const maxSize = 20 * 1024 * 1024; // 20MB
    if (file.size > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'File Too Large',
            text: 'Maximum file size is 20MB. Please select a smaller file.'
        });
        clearSelectedFile();
        return;
    }
    selectedFileName.text(file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
    selectedFileInfo.show();
}

function clearSelectedFile() {
    fileInput.val('');
    selectedFileInfo.hide();
}

// Form submission
$('#uploadDocumentForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!fileInput[0].files.length) {
        Swal.fire({
            icon: 'warning',
            title: 'No File Selected',
            text: 'Please select a file to upload.'
        });
        return;
    }
    
    const formData = new FormData(this);
    formData.append('action', 'upload_file');
    const submitBtn = $(this).find('button[type="submit"]');
    const originalHtml = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Uploading...');
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': token
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Document uploaded successfully',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        container: 'swal2-container-high-zindex',
                        popup: 'swal2-popup-high-zindex'
                    },
                    didOpen: () => {
                        // Ensure it's on top of all elements
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                        }
                        const swalPopup = document.querySelector('.swal2-popup');
                        if (swalPopup) {
                            swalPopup.style.zIndex = '100000';
                        }
                    }
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: response.message || 'Failed to upload document',
                    customClass: {
                        container: 'swal2-container-high-zindex',
                        popup: 'swal2-popup-high-zindex'
                    },
                    didOpen: () => {
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                        }
                    }
                });
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response?.message || 'An error occurred while uploading',
                customClass: {
                    container: 'swal2-container-high-zindex',
                    popup: 'swal2-popup-high-zindex'
                },
                didOpen: () => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.style.zIndex = '99999';
                    }
                }
            });
            submitBtn.prop('disabled', false).html(originalHtml);
        }
    });
});

// Preview document
function previewDocument(fileUrl, fileName, docType, downloadUrl, filePath) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const title = document.getElementById('previewModalTitle');
    const content = $('#previewModalContent'); // Use jQuery selector
    const downloadLink = document.getElementById('previewDownloadLink');
    
    title.textContent = fileName;
    content.html('<div class="text-center py-5"><div class="spinner-border text-success"></div></div>');
    modal.show();
    
    // Set download link
    downloadLink.href = downloadUrl || fileUrl;
    downloadLink.download = fileName;
    
    // Determine file type for preview - check extension, URL, and file path
    const ext = fileName ? fileName.split('.').pop().toLowerCase() : '';
    const fileUrlLower = fileUrl ? fileUrl.toLowerCase() : '';
    const filePathLower = filePath ? filePath.toLowerCase() : '';
    
    // Check if it's a PDF (by extension, URL, or file path)
    const isPdf = ext === 'pdf' || 
                  fileUrlLower.includes('.pdf') || 
                  fileUrlLower.includes('/pdf') ||
                  filePathLower.includes('.pdf') ||
                  filePathLower.endsWith('pdf');
    
    // Check if it's an image
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    const isImage = imageExts.includes(ext) || 
                    fileUrlLower.match(/\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i) ||
                    filePathLower.match(/\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i);
    
    if (isImage) {
        // Image preview
        content.html(`<img src="${fileUrl}" class="img-fluid" alt="${fileName}" style="max-height: 70vh; width: auto; display: block; margin: 0 auto;">`);
    } else if (isPdf) {
        // PDF preview using iframe
        // Use embed tag as fallback for better browser compatibility
        content.html(`
            <div style="width: 100%; height: 70vh; overflow: auto;">
                <embed 
                    src="${fileUrl}" 
                    type="application/pdf" 
                    style="width: 100%; height: 100%; border: none;"
                    title="PDF Preview">
                <iframe 
                    src="${fileUrl}" 
                    style="width: 100%; height: 100%; border: none; display: none;" 
                    onload="this.style.display='block'; this.previousElementSibling.style.display='none';"
                    title="PDF Preview">
                </iframe>
            </div>
        `);
    } else {
        // For other file types, show download option
        content.html(`
            <div class="text-center py-5">
                <i class="bx bx-file fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Preview Not Available</h5>
                <p class="text-muted">Preview is not available for this file type. Please download to view.</p>
                <a href="${downloadUrl || fileUrl}" download="${fileName}" class="btn btn-success mt-3">
                    <i class="bx bx-download me-1"></i>Download Document
                </a>
            </div>
        `);
    }
}

// Assign Document Functions
let allStaff = [];
let selectedStaffIds = [];

function openAssignModal(fileId, fileName) {
    $('#assignDocumentId').val(fileId);
    $('#assignDocumentName').val(fileName);
    selectedStaffIds = [];
    $('#selectedStaffList').html('<span class="text-muted">No staff selected</span>');
    
    // Load staff list
    loadStaffList();
    
    // Show modal
    const assignModal = new bootstrap.Modal(document.getElementById('assignDocumentModal'));
    assignModal.show();
}

function loadStaffList() {
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'get_users_for_assignment',
            _token: token
        },
        success: function(response) {
            if (response.success && response.users) {
                allStaff = response.users;
                renderStaffList();
            } else {
                $('#staffListContainer').html('<div class="alert alert-warning">No staff members found</div>');
            }
        },
        error: function() {
            $('#staffListContainer').html('<div class="alert alert-danger">Error loading staff list</div>');
        }
    });
}

function renderStaffList(searchTerm = '') {
    const container = $('#staffListContainer');
    const term = searchTerm.toLowerCase();
    
    const filteredStaff = allStaff.filter(staff => {
        const name = (staff.name || '').toLowerCase();
        const email = (staff.email || '').toLowerCase();
        return name.includes(term) || email.includes(term);
    });
    
    if (filteredStaff.length === 0) {
        container.html('<div class="alert alert-info">No staff members found</div>');
        return;
    }
    
    let html = '<div class="list-group">';
    filteredStaff.forEach(staff => {
        const isSelected = selectedStaffIds.includes(staff.id);
        html += `
            <label class="list-group-item d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-3" value="${staff.id}" 
                       ${isSelected ? 'checked' : ''} 
                       onchange="toggleStaffSelection(${staff.id}, this.checked)">
                <div class="flex-grow-1">
                    <strong>${staff.name || 'N/A'}</strong>
                    <br>
                    <small class="text-muted">${staff.email || 'N/A'}</small>
                    ${staff.department ? `<br><small class="text-muted"><i class="bx bx-building"></i> ${staff.department.name || ''}</small>` : ''}
                </div>
            </label>
        `;
    });
    html += '</div>';
    container.html(html);
}

function toggleStaffSelection(userId, isChecked) {
    if (isChecked) {
        if (!selectedStaffIds.includes(userId)) {
            selectedStaffIds.push(userId);
        }
    } else {
        selectedStaffIds = selectedStaffIds.filter(id => id !== userId);
    }
    updateSelectedStaffList();
}

function updateSelectedStaffList() {
    const container = $('#selectedStaffList');
    if (selectedStaffIds.length === 0) {
        container.html('<span class="text-muted">No staff selected</span>');
        return;
    }
    
    let html = '';
    selectedStaffIds.forEach(userId => {
        const staff = allStaff.find(s => s.id === userId);
        if (staff) {
            html += `
                <span class="badge bg-info p-2">
                    ${staff.name || 'N/A'}
                    <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.7em;" onclick="removeStaff(${userId})"></button>
                </span>
            `;
        }
    });
    container.html(html);
}

function removeStaff(userId) {
    selectedStaffIds = selectedStaffIds.filter(id => id !== userId);
    updateSelectedStaffList();
    // Uncheck checkbox
    $(`input[type="checkbox"][value="${userId}"]`).prop('checked', false);
}

function clearStaffSearch() {
    $('#searchStaff').val('');
    renderStaffList();
}

// Search staff
$('#searchStaff').on('input', function() {
    renderStaffList($(this).val());
});

// Assign Document Form Submission
$('#assignDocumentForm').on('submit', function(e) {
    e.preventDefault();
    
    const fileId = $('#assignDocumentId').val();
    
    if (selectedStaffIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Staff Selected',
            text: 'Please select at least one staff member to assign this document to.',
            customClass: {
                container: 'swal2-container-high-zindex',
                popup: 'swal2-popup-high-zindex'
            }
        });
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalHtml = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'assign_document_to_staff',
            file_id: fileId,
            user_ids: selectedStaffIds,
            _token: token
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Document assigned successfully',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        container: 'swal2-container-high-zindex',
                        popup: 'swal2-popup-high-zindex'
                    },
                    didOpen: () => {
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '99999';
                        }
                        const swalPopup = document.querySelector('.swal2-popup');
                        if (swalPopup) {
                            swalPopup.style.zIndex = '100000';
                        }
                    }
                }).then(() => {
                    const assignModal = bootstrap.Modal.getInstance(document.getElementById('assignDocumentModal'));
                    assignModal.hide();
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Assignment Failed',
                    text: response.message || 'Failed to assign document',
                    customClass: {
                        container: 'swal2-container-high-zindex',
                        popup: 'swal2-popup-high-zindex'
                    }
                });
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response?.message || 'An error occurred while assigning document',
                customClass: {
                    container: 'swal2-container-high-zindex',
                    popup: 'swal2-popup-high-zindex'
                }
            });
            submitBtn.prop('disabled', false).html(originalHtml);
        }
    });
});

// View File Details
function viewFileDetails(fileId) {
    const modal = new bootstrap.Modal(document.getElementById('fileDetailsModal'));
    const content = $('#fileDetailsContent');
    
    content.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading file details...</p></div>');
    modal.show();
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'get_file_details_for_my_documents',
            file_id: fileId,
            _token: token
        },
        success: function(response) {
            if (response.success && response.file) {
                const file = response.file;
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bx bx-file me-2"></i>File Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold" style="width: 40%;">File Name:</td>
                                            <td>${file.original_name || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">File Size:</td>
                                            <td>${file.file_size || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">File Type:</td>
                                            <td>${file.mime_type || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Description:</td>
                                            <td>${file.description || 'No description'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Access Level:</td>
                                            <td><span class="badge bg-info">${file.access_level || 'N/A'}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Confidential Level:</td>
                                            <td><span class="badge bg-warning">${file.confidential_level || 'Normal'}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Uploaded By:</td>
                                            <td>${file.uploaded_by || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Folder:</td>
                                            <td>${file.folder || 'No folder'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Created:</td>
                                            <td>${file.created_at || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Downloads:</td>
                                            <td><span class="badge bg-success">${file.download_count || 0}</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bx bx-user-check me-2"></i>Assigned Staff (${file.assigned_users ? file.assigned_users.length : 0})</h6>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                `;
                
                if (file.assigned_users && file.assigned_users.length > 0) {
                    html += '<div class="list-group">';
                    file.assigned_users.forEach(function(assignment) {
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">${assignment.name || 'N/A'}</h6>
                                        <small class="text-muted">${assignment.email || 'N/A'}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Assigned: ${assignment.assigned_at || 'N/A'}</small>
                                        ${assignment.expiry_date && assignment.expiry_date !== 'No expiry' ? `<small class="text-muted">Expires: ${assignment.expiry_date}</small>` : '<small class="text-success">No expiry</small>'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-info mb-0">No staff assigned to this document</div>';
                }
                
                html += `
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bx bx-history me-2"></i>Access History</h6>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                `;
                
                if (file.access_history && file.access_history.length > 0) {
                    html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Staff Member</th><th>Action</th><th>Accessed At</th><th>Time Ago</th></tr></thead><tbody>';
                    file.access_history.forEach(function(access) {
                        html += `
                            <tr>
                                <td>
                                    <strong>${access.user_name || 'Unknown'}</strong><br>
                                    <small class="text-muted">${access.user_email || ''}</small>
                                </td>
                                <td><span class="badge bg-primary">${access.action || 'N/A'}</span></td>
                                <td>${access.accessed_at || 'N/A'}</td>
                                <td><small class="text-muted">${access.time_ago || 'N/A'}</small></td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class="alert alert-info mb-0">No access history available</div>';
                }
                
                html += `
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                content.html(html);
            } else {
                content.html('<div class="alert alert-danger">Failed to load file details</div>');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            content.html(`<div class="alert alert-danger">Error: ${response?.message || 'Failed to load file details'}</div>`);
        }
    });
}
</script>
@endpush
