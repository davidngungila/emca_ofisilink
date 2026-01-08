@extends('layouts.app')

@section('title', 'Create Advertisement')

@section('breadcrumb')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-0">Create New Advertisement / Announcement</h4>
                <p class="text-muted">Create a new system-wide announcement</p>
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
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bullhorn me-2"></i>Advertisement Details
                    </h5>
                </div>
                <div class="card-body">
                    <form id="advertisementForm" method="POST" action="{{ route('advertisements.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Content <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control @error('content') is-invalid @enderror" 
                                          rows="8" required>{{ old('content') }}</textarea>
                                <small class="text-muted">HTML is supported</small>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Attachments (Optional)</label>
                                <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" 
                                       multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                <small class="text-muted">You can upload multiple files (PDF, Images, Documents). Max 10MB per file.</small>
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="important" {{ old('priority') == 'important' ? 'selected' : '' }}>Important</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date') }}">
                                <small class="text-muted">Leave empty to start immediately</small>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       value="{{ old('expiry_date') }}">
                                <small class="text-muted">Leave empty for no expiry</small>
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_to_all" id="show_to_all" value="1"
                                           {{ old('show_to_all', true) ? 'checked' : '' }} onchange="toggleTargetRoles()">
                                    <label class="form-check-label" for="show_to_all">Show to All Users</label>
                                </div>
                            </div>
                            
                            <div class="col-md-12" id="targetRolesSection" style="display: none;">
                                <label class="form-label">Target Roles</label>
                                <select name="target_roles[]" class="form-select" multiple size="5">
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                            {{ in_array($role->id, old('target_roles', [])) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple roles</small>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="require_acknowledgment" id="require_acknowledgment" value="1"
                                           {{ old('require_acknowledgment', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_acknowledgment">Require Acknowledgment</label>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_redisplay" id="allow_redisplay" value="1"
                                           {{ old('allow_redisplay', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_redisplay">Allow Re-display if Updated</label>
                                    <small class="text-muted d-block">If checked, users will see the advertisement again if it's updated after they acknowledged it</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>Create Advertisement
                            </button>
                            <a href="{{ route('advertisements.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
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
function toggleTargetRoles() {
    const showToAll = document.getElementById('show_to_all').checked;
    const targetRolesSection = document.getElementById('targetRolesSection');
    targetRolesSection.style.display = showToAll ? 'none' : 'block';
}
</script>
@endpush

