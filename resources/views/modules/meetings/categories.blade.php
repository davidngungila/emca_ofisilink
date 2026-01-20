@extends('layouts.app')

@section('title', 'Meeting Categories - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-category me-2"></i>Meeting Categories
                            </h4>
                            <p class="card-text text-white-50 mb-0">Manage meeting categories and types</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.meetings.index') }}" class="btn btn-light me-2">
                                <i class="bx bx-arrow-back me-1"></i>Back to Meetings
                            </a>
                            <button class="btn btn-light" id="add-category-btn">
                                <i class="bx bx-plus me-1"></i>Add Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-list-ul me-2"></i>All Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Meetings</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Categories loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    @csrf
                    <input type="hidden" name="category_id" id="category_id">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-category-btn">Save Category</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const ajaxUrl = '{{ route("modules.meetings.ajax") }}';

// Load categories
function loadCategories() {
    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: { _token: csrfToken, action: 'get_categories' },
        success: function(response) {
            if (response.success && response.categories) {
                renderCategories(response.categories);
            }
        }
    });
}

// Render categories
function renderCategories(categories) {
    const tbody = $('#categoriesTable tbody');
    tbody.empty();
    
    if (categories.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center text-muted">No categories found</td></tr>');
        return;
    }
    
    categories.forEach(cat => {
        tbody.append(`
            <tr>
                <td><strong>${escapeHtml(cat.name)}</strong></td>
                <td>${escapeHtml(cat.description || '-')}</td>
                <td><span class="badge bg-info">${cat.meetings_count || 0}</span></td>
                <td><span class="badge bg-${cat.is_active ? 'success' : 'secondary'}">${cat.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-warning edit-category-btn" data-id="${cat.id}" data-name="${escapeHtml(cat.name)}" data-description="${escapeHtml(cat.description || '')}" data-active="${cat.is_active}">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-category-btn" data-id="${cat.id}">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

// Event listeners
$('#add-category-btn').on('click', function() {
    $('#categoryModalTitle').text('Add Category');
    $('#categoryForm')[0].reset();
    $('#category_id').val('');
    $('#categoryModal').modal('show');
});

$(document).on('click', '.edit-category-btn', function() {
    const btn = $(this);
    $('#categoryModalTitle').text('Edit Category');
    $('#category_id').val(btn.data('id'));
    $('input[name="name"]').val(btn.data('name'));
    $('textarea[name="description"]').val(btn.data('description'));
    $('#is_active').prop('checked', btn.data('active') == 1);
    $('#categoryModal').modal('show');
});

$('#save-category-btn').on('click', function() {
    const formData = $('#categoryForm').serialize();
    const categoryId = $('#category_id').val();
    const action = categoryId ? 'update_category' : 'create_category';
    
    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: {
            _token: csrfToken,
            action: action,
            category_id: categoryId,
            ...Object.fromEntries(new URLSearchParams(formData))
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', categoryId ? 'Category updated' : 'Category created', 'success');
                $('#categoryModal').modal('hide');
                loadCategories();
            } else {
                Swal.fire('Error', response.message || 'Failed to save category', 'error');
            }
        }
    });
});

$(document).on('click', '.delete-category-btn', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Delete Category?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: { _token: csrfToken, action: 'delete_category', category_id: id },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Category deleted', 'success');
                        loadCategories();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
});

// Initial load
loadCategories();
</script>
@endpush






