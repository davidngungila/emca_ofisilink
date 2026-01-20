@extends('layouts.app')

@section('title', 'Edit Training')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bx bx-edit me-2"></i>Edit Training</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('trainings.update', $training->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Topic <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('topic') is-invalid @enderror" 
                                       name="topic" value="{{ old('topic', $training->topic) }}" required>
                                @error('topic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Content</label>
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          name="content" rows="4">{{ old('content', $training->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">What Learn</label>
                                <textarea class="form-control @error('what_learn') is-invalid @enderror" 
                                          name="what_learn" rows="3">{{ old('what_learn', $training->what_learn) }}</textarea>
                                @error('what_learn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Who Teach</label>
                                <input type="text" class="form-control @error('who_teach') is-invalid @enderror" 
                                       name="who_teach" value="{{ old('who_teach', $training->who_teach) }}">
                                @error('who_teach')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       name="location" value="{{ old('location', $training->location) }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       name="start_date" value="{{ old('start_date', $training->start_date?->format('Y-m-d')) }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       name="end_date" value="{{ old('end_date', $training->end_date?->format('Y-m-d')) }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Suggestion to Our Saccos</label>
                                <textarea class="form-control @error('suggestion_to_saccos') is-invalid @enderror" 
                                          name="suggestion_to_saccos" rows="3">{{ old('suggestion_to_saccos', $training->suggestion_to_saccos) }}</textarea>
                                @error('suggestion_to_saccos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Training Timetable</label>
                                <textarea class="form-control @error('training_timetable') is-invalid @enderror" 
                                          name="training_timetable" rows="4">{{ old('training_timetable', is_array($training->training_timetable) ? json_encode($training->training_timetable, JSON_PRETTY_PRINT) : $training->training_timetable) }}</textarea>
                                @error('training_timetable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status">
                                    <option value="draft" {{ old('status', $training->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $training->status) == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="ongoing" {{ old('status', $training->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                    <option value="completed" {{ old('status', $training->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $training->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Participants</label>
                                <select class="form-select select2 @error('participants') is-invalid @enderror" 
                                        name="participants[]" multiple>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ in_array($user->id, old('participants', $training->participants->pluck('user_id')->toArray())) ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Add More Documents</label>
                                <input type="file" class="form-control @error('documents.*') is-invalid @enderror" 
                                       name="documents[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <small class="text-muted">You can upload multiple files (Max 10MB each)</small>
                                @error('documents.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('trainings.show', $training->id) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>Update Training
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select participants',
            allowClear: true
        });
    });
</script>
@endpush




