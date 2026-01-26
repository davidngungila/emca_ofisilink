@extends('layouts.app')

@section('title', 'Request Refund')

@push('styles')
<style>
    .create-card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: none;
    }
    
    .form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
    
    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        background: #fff;
        transition: all 0.3s;
    }
    
    .file-upload-area:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .file-upload-area.dragover {
        border-color: #667eea;
        background: #f0f4ff;
    }
    
    .attachment-item {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .attachment-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-danger">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-money-withdraw me-2"></i>Request Refund
                    </h2>
                    <p class="mb-0 opacity-90">Submit a refund request for expenses paid from your pocket. Supporting documents are required.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('refunds.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>Back to Refunds
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm create-card">
        <div class="card-body p-4">
            <form id="refundCreateForm" method="POST" action="{{ route('refunds.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Expense Details Section -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="bx bx-target-lock me-2 text-primary"></i>Expense Details
                    </h5>
                    <div class="mb-3">
                        <label class="form-label">
                            Purpose/Description <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" name="purpose" id="purpose" required placeholder="e.g., Office supplies, Travel expenses, Conference fees">
                        <small class="text-muted">
                            <i class="bx bx-info-circle"></i> Briefly describe what the expense was for
                        </small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Amount (TZS) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-money text-primary"></i>
                                </span>
                                <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="0.01" required placeholder="Enter amount">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Expense Date <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-calendar text-primary"></i>
                                </span>
                                <input type="date" class="form-control" name="expense_date" id="expense_date" max="{{ date('Y-m-d') }}" required>
                            </div>
                            <small class="text-muted">
                                <i class="bx bx-info-circle"></i> Date when you incurred this expense
                            </small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Details</label>
                        <textarea class="form-control" name="description" id="description" rows="4" placeholder="Provide any additional information about this expense..."></textarea>
                        <small class="text-muted">
                            <i class="bx bx-info-circle"></i> Optional: Add more context about this expense
                        </small>
                    </div>
                </div>

                <!-- Supporting Documents Section -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="bx bx-paperclip me-2 text-success"></i>Supporting Documents
                        <span class="text-danger">*</span>
                    </h5>
                    <p class="text-muted mb-3">
                        <i class="bx bx-info-circle"></i> Upload receipts, invoices, or other supporting documents. At least one document is required.
                    </p>
                    
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="bx bx-cloud-upload fs-1 text-muted mb-3"></i>
                        <p class="mb-2 fw-bold">Drag & drop files here or click to browse</p>
                        <p class="text-muted small mb-0">Supported formats: PDF, JPG, PNG, DOC, DOCX (Max 5MB per file)</p>
                        <input type="file" name="attachments[]" id="attachments" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;" required>
                    </div>
                    
                    <div id="attachmentsList" class="mt-3"></div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('refunds.index') }}" class="btn btn-secondary btn-lg">
                        <i class="bx bx-x me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-danger btn-lg" id="submitBtn">
                        <i class="bx bx-check-circle me-1"></i>Submit Refund Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('refundCreateForm');
    const fileInput = document.getElementById('attachments');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const attachmentsList = document.getElementById('attachmentsList');
    let selectedFiles = [];
    
    // Click to upload
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Drag and drop
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });
    
    fileUploadArea.addEventListener('dragleave', function() {
        fileUploadArea.classList.remove('dragover');
    });
    
    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        handleFiles(files);
    });
    
    function handleFiles(files) {
        files.forEach(file => {
            // Validate file
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: `${file.name} exceeds 5MB limit. Please choose a smaller file.`
                });
                return;
            }
            
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: `${file.name} is not a supported file type.`
                });
                return;
            }
            
            // Add to selected files
            if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
                displayAttachment(file);
            }
        });
        
        updateFileInput();
    }
    
    function displayAttachment(file) {
        const item = document.createElement('div');
        item.className = 'attachment-item';
        item.dataset.fileName = file.name;
        item.dataset.fileSize = file.size;
        
        const fileSize = (file.size / 1024).toFixed(2) + ' KB';
        const fileIcon = file.type.startsWith('image/') ? 'bx-image' : (file.type === 'application/pdf' ? 'bx-file-blank' : 'bx-file');
        
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bx ${fileIcon} fs-4 text-primary me-2"></i>
                <div>
                    <div class="fw-bold">${file.name}</div>
                    <small class="text-muted">${fileSize}</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-attachment" data-file-name="${file.name}">
                <i class="bx bx-trash"></i> Remove
            </button>
        `;
        
        attachmentsList.appendChild(item);
        
        // Remove button
        item.querySelector('.remove-attachment').addEventListener('click', function() {
            selectedFiles = selectedFiles.filter(f => !(f.name === file.name && f.size === file.size));
            item.remove();
            updateFileInput();
        });
    }
    
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
        
        // Update required attribute
        if (selectedFiles.length > 0) {
            fileInput.removeAttribute('required');
        } else {
            fileInput.setAttribute('required', 'required');
        }
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Documents Required',
                text: 'Please upload at least one supporting document.'
            });
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
    });
});
</script>
@endpush





