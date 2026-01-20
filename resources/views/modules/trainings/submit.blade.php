@extends('layouts.app')

@section('title', 'Submit Training Form')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bx bx-check me-2"></i>Submit Training Form</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('trainings.submit', $training->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Please fill in the training details and upload supportive documents.
                        </div>

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

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Supportive Documents</label>
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
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-check me-1"></i>Submit Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




