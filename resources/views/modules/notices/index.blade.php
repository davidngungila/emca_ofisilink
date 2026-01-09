@extends('layouts.app')

@section('title', 'notices / Announcements')

@section('breadcrumb')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-0">notices / Announcements</h4>
                <p class="text-muted">Manage system-wide announcements and notices</p>
            </div>
        </div>
    </div>
</div>
<br>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bullhorn me-2"></i>All notices
                    </h5>
                    <a href="{{ route('notices.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i>Create New notice
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="noticesTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Target</th>
                                    <th>Start Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Acknowledged</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notices as $ad)
                                <tr>
                                    <td>
                                        <strong class="d-block" title="{{ $ad->title }}" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ \Illuminate\Support\Str::limit($ad->title, 40) }}
                                        </strong>
                                        <small class="text-muted d-block" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ strip_tags($ad->content) }}">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($ad->content), 50) }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $ad->getPriorityBadgeClass() }}">
                                            {{ ucfirst($ad->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ad->show_to_all)
                                            <span class="badge bg-info">All Users</span>
                                        @else
                                            <span class="badge bg-secondary">Selected Roles</span>
                                        @endif
                                    </td>
                                    <td>{{ $ad->start_date ? $ad->start_date->format('Y-m-d') : 'N/A' }}</td>
                                    <td>{{ $ad->expiry_date ? $ad->expiry_date->format('Y-m-d') : 'No Expiry' }}</td>
                                    <td>
                                        @if($ad->isCurrentlyActive())
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $ad->acknowledgments->count() }} users</span>
                                        <button class="btn btn-sm btn-outline-info ms-1" onclick="viewAcknowledgmentStats({{ $ad->id }})" title="View Details">
                                            <i class="bx bx-info-circle"></i>
                                        </button>
                                    </td>
                                    <td>{{ $ad->creator->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('notices.show', $ad->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('notices.edit', $ad->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deletenotice({{ $ad->id }})" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bx bx-info-circle"></i> No notices found. 
                                        <a href="{{ route('notices.create') }}">Create one now</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $notices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acknowledgment Stats Modal -->
<div class="modal fade" id="acknowledgmentStatsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Acknowledgment Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="acknowledgmentStatsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
function deletenotice(id) {
    Swal.fire({
        title: 'Delete notice?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteUrl = '{{ url("/modules/notices") }}/' + id;
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'notice has been deleted.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete notice', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An error occurred', 'error');
            });
        }
    });
}

function viewAcknowledgmentStats(id) {
    const modal = new bootstrap.Modal(document.getElementById('acknowledgmentStatsModal'));
    const content = document.getElementById('acknowledgmentStatsContent');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();
    
    const statsUrl = '{{ url("/notices") }}/' + id + '/acknowledgment-stats';
    fetch(statsUrl, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = `
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary">${data.total_users}</h3>
                                <p class="mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success">${data.acknowledged_count}</h3>
                                <p class="mb-0">Acknowledged</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning">${data.pending_count}</h3>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (data.acknowledgments && data.acknowledgments.length > 0) {
                html += `
                    <h6>Users Who Acknowledged:</h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Acknowledged At</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                data.acknowledgments.forEach(ack => {
                    html += `
                        <tr>
                            <td>${ack.user_name}</td>
                            <td>${ack.user_email}</td>
                            <td>${ack.acknowledged_at}</td>
                            <td>${ack.notes || '-'}</td>
                        </tr>
                    `;
                });
                html += `</tbody></table></div>`;
            } else {
                html += '<p class="text-muted text-center">No acknowledgments yet</p>';
            }
            
            content.innerHTML = html;
        } else {
            content.innerHTML = '<p class="text-danger">Error loading statistics</p>';
        }
    })
    .catch(error => {
        content.innerHTML = '<p class="text-danger">Error loading statistics</p>';
    });
}
</script>
@endpush

