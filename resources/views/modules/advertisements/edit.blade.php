@extends('layouts.app')

@section('title', 'Edit Advertisement')

@section('breadcrumb')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-0">Edit Advertisement / Announcement</h4>
                <p class="text-muted">Update advertisement details</p>
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
                    <form id="advertisementForm" method="POST" action="{{ route('advertisements.update', $advertisement->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $advertisement->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Content <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control @error('content') is-invalid @enderror" 
                                          rows="8" required>{{ old('content', $advertisement->content) }}</textarea>
                                <small class="text-muted">HTML is supported</small>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            @if($advertisement->attachments && count($advertisement->attachments) > 0)
                            <div class="col-md-12">
                                <label class="form-label">Current Attachments</label>
                                <div class="row g-2">
                                    @foreach($advertisement->attachments as $index => $attachment)
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <small class="d-block text-truncate" style="max-width: 150px;" title="{{ $attachment['name'] ?? 'File' }}">
                                                            <i class="bx bx-file me-1"></i>{{ $attachment['name'] ?? 'File' }}
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Preview">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAttachment({{ $index }})" title="Delete">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="delete_attachments" id="deleteAttachments" value="">
                            </div>
                            @endif
                            
                            <div class="col-md-12">
                                <label class="form-label">Add New Attachments (Optional)</label>
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
                                    <option value="normal" {{ old('priority', $advertisement->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="important" {{ old('priority', $advertisement->priority) == 'important' ? 'selected' : '' }}>Important</option>
                                    <option value="urgent" {{ old('priority', $advertisement->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', $advertisement->start_date?->format('Y-m-d')) }}">
                                <small class="text-muted">Leave empty to start immediately</small>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       value="{{ old('expiry_date', $advertisement->expiry_date?->format('Y-m-d')) }}">
                                <small class="text-muted">Leave empty for no expiry</small>
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           {{ old('is_active', $advertisement->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_to_all" id="show_to_all" 
                                           {{ old('show_to_all', $advertisement->show_to_all) ? 'checked' : '' }} onchange="toggleTargetRoles()">
                                    <label class="form-check-label" for="show_to_all">Show to All Users</label>
                                </div>
                            </div>
                            
                            <div class="col-md-12" id="targetRolesSection" style="display: {{ $advertisement->show_to_all ? 'none' : 'block' }};">
                                <label class="form-label">Target Roles</label>
                                <select name="target_roles[]" class="form-select" multiple size="5">
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                            {{ in_array($role->id, old('target_roles', $advertisement->target_roles ?? [])) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple roles</small>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="require_acknowledgment" id="require_acknowledgment" 
                                           {{ old('require_acknowledgment', $advertisement->require_acknowledgment) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_acknowledgment">Require Acknowledgment</label>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_redisplay" id="allow_redisplay" 
                                           {{ old('allow_redisplay', $advertisement->allow_redisplay) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_redisplay">Allow Re-display if Updated</label>
                                    <small class="text-muted d-block">If checked, users will see the advertisement again if it's updated after they acknowledged it</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>Update Advertisement
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

