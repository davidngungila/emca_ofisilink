@extends('layouts.app')

@section('title', 'View Advertisement')

@section('breadcrumb')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-0">Advertisement Details</h4>
                <p class="text-muted">View advertisement information and statistics</p>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bullhorn me-2"></i>{{ $advertisement->title }}
                    </h5>
                    <div>
                        <a href="{{ route('advertisements.edit', $advertisement->id) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('advertisements.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Details</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Priority:</th>
                                    <td><span class="badge {{ $advertisement->getPriorityBadgeClass() }}">{{ ucfirst($advertisement->priority) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($advertisement->isCurrentlyActive())
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Target:</th>
                                    <td>
                                        @if($advertisement->show_to_all)
                                            <span class="badge bg-info">All Users</span>
                                        @else
                                            <span class="badge bg-secondary">Selected Roles</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Start Date:</th>
                                    <td>{{ $advertisement->start_date ? $advertisement->start_date->format('Y-m-d') : 'Immediate' }}</td>
                                </tr>
                                <tr>
                                    <th>Expiry Date:</th>
                                    <td>{{ $advertisement->expiry_date ? $advertisement->expiry_date->format('Y-m-d') : 'No Expiry' }}</td>
                                </tr>
                                <tr>
                                    <th>Require Acknowledgment:</th>
                                    <td>{{ $advertisement->require_acknowledgment ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Allow Re-display:</th>
                                    <td>{{ $advertisement->allow_redisplay ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $advertisement->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $advertisement->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $advertisement->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Content</h6>
                            <div class="border rounded p-3" style="min-height: 200px; max-height: 400px; overflow-y: auto;">
                                {!! $advertisement->content !!}
                            </div>
                            
                            @if($advertisement->attachments && count($advertisement->attachments) > 0)
                            <div class="mt-3">
                                <h6>Attachments</h6>
                                <div class="row g-2">
                                    @foreach($advertisement->getAttachmentUrls() as $attachment)
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-center">
                                                    @if($attachment['type'] == 'image')
                                                        <i class="bx bx-image fs-4 me-2 text-primary"></i>
                                                    @elseif($attachment['type'] == 'pdf')
                                                        <i class="bx bx-file-blank fs-4 me-2 text-danger"></i>
                                                    @else
                                                        <i class="bx bx-file fs-4 me-2 text-secondary"></i>
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <small class="d-block text-truncate" style="max-width: 200px;" title="{{ $attachment['name'] }}">
                                                            {{ $attachment['name'] }}
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="previewFile('{{ $attachment['url'] }}', '{{ $attachment['type'] }}', '{{ $attachment['name'] }}')" title="Preview">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                        <a href="{{ $attachment['url'] }}" download class="btn btn-sm btn-outline-success" title="Download">
                                                            <i class="bx bx-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Acknowledgment Statistics</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-primary" id="totalUsers">-</h3>
                                            <p class="mb-0">Total Users</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-success" id="acknowledgedCount">{{ $advertisement->acknowledgments->count() }}</h3>
                                            <p class="mb-0">Acknowledged</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-warning" id="pendingCount">-</h3>
                                            <p class="mb-0">Pending</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-sm btn-info" onclick="loadAcknowledgmentDetails()">
                                    <i class="bx bx-refresh me-1"></i>Refresh Statistics
                                </button>
                            </div>
                            
                            <div class="mt-3" id="acknowledgmentDetails" style="display: none;">
                                <h6>Users Who Acknowledged</h6>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm" id="acknowledgmentTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Acknowledged At</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePreviewTitle">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="filePreviewContent" style="min-height: 500px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="fileDownloadLink" class="btn btn-primary" download>
                    <i class="bx bx-download me-1"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFile(url, type, name) {
    const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    const title = document.getElementById('filePreviewTitle');
    const content = document.getElementById('filePreviewContent');
    const downloadLink = document.getElementById('fileDownloadLink');
    
    title.textContent = name;
    downloadLink.href = url;
    downloadLink.download = name;
    
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    modal.show();
    
    if (type === 'image') {
        content.innerHTML = `<img src="${url}" class="img-fluid" alt="${name}" style="max-height: 70vh; width: auto; display: block; margin: 0 auto;">`;
    } else if (type === 'pdf') {
        content.innerHTML = `<iframe src="${url}" style="width: 100%; height: 70vh; border: none;"></iframe>`;
    } else {
        content.innerHTML = `
            <div class="text-center py-5">
                <i class="bx bx-file fs-1 text-muted"></i>
                <p class="mt-3">Preview not available for this file type.</p>
                <a href="${url}" download class="btn btn-primary">
                    <i class="bx bx-download me-1"></i>Download to View
                </a>
            </div>
        `;
    }
}

function loadAcknowledgmentDetails() {
    fetch('{{ route("advertisements.acknowledgment-stats", $advertisement->id) }}', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalUsers').textContent = data.total_users;
            document.getElementById('acknowledgedCount').textContent = data.acknowledged_count;
            document.getElementById('pendingCount').textContent = data.pending_count;
            
            const tbody = document.querySelector('#acknowledgmentTable tbody');
            tbody.innerHTML = '';
            
            if (data.acknowledgments && data.acknowledgments.length > 0) {
                data.acknowledgments.forEach(ack => {
                    const row = tbody.insertRow();
                    row.insertCell(0).textContent = ack.user_name;
                    row.insertCell(1).textContent = ack.user_email;
                    row.insertCell(2).textContent = ack.acknowledged_at;
                    row.insertCell(3).textContent = ack.notes || '-';
                });
                document.getElementById('acknowledgmentDetails').style.display = 'block';
            } else {
                document.getElementById('acknowledgmentDetails').style.display = 'none';
            }
        }
    })
    .catch(error => {
        console.error('Error loading acknowledgment details:', error);
    });
}

// Load stats on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAcknowledgmentDetails();
});
</script>
@endpush

