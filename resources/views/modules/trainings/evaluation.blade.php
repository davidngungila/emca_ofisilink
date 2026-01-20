@extends('layouts.app')

@section('title', 'Training Evaluation')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="bx bx-star me-2"></i>Training Evaluation: {{ $training->topic }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('trainings.evaluation.store', $training->id) }}" method="POST">
                        @csrf
                        
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Please provide your honest feedback about this training. Your evaluation helps us improve future trainings.
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Overall Rating <span class="text-danger">*</span></label>
                                <select class="form-select @error('overall_rating') is-invalid @enderror" name="overall_rating" required>
                                    <option value="">Select rating</option>
                                    <option value="5" {{ old('overall_rating', $evaluation->overall_rating ?? '') == 5 ? 'selected' : '' }}>5 - Excellent</option>
                                    <option value="4" {{ old('overall_rating', $evaluation->overall_rating ?? '') == 4 ? 'selected' : '' }}>4 - Very Good</option>
                                    <option value="3" {{ old('overall_rating', $evaluation->overall_rating ?? '') == 3 ? 'selected' : '' }}>3 - Good</option>
                                    <option value="2" {{ old('overall_rating', $evaluation->overall_rating ?? '') == 2 ? 'selected' : '' }}>2 - Fair</option>
                                    <option value="1" {{ old('overall_rating', $evaluation->overall_rating ?? '') == 1 ? 'selected' : '' }}>1 - Poor</option>
                                </select>
                                @error('overall_rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Content Rating</label>
                                <select class="form-select @error('content_rating') is-invalid @enderror" name="content_rating">
                                    <option value="">Select rating</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ old('content_rating', $evaluation->content_rating ?? '') == $i ? 'selected' : '' }}>
                                            {{ $i }} - {{ $i == 5 ? 'Excellent' : ($i == 4 ? 'Very Good' : ($i == 3 ? 'Good' : ($i == 2 ? 'Fair' : 'Poor'))) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('content_rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instructor Rating</label>
                                <select class="form-select @error('instructor_rating') is-invalid @enderror" name="instructor_rating">
                                    <option value="">Select rating</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ old('instructor_rating', $evaluation->instructor_rating ?? '') == $i ? 'selected' : '' }}>
                                            {{ $i }} - {{ $i == 5 ? 'Excellent' : ($i == 4 ? 'Very Good' : ($i == 3 ? 'Good' : ($i == 2 ? 'Fair' : 'Poor'))) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('instructor_rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Venue Rating</label>
                                <select class="form-select @error('venue_rating') is-invalid @enderror" name="venue_rating">
                                    <option value="">Select rating</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ old('venue_rating', $evaluation->venue_rating ?? '') == $i ? 'selected' : '' }}>
                                            {{ $i }} - {{ $i == 5 ? 'Excellent' : ($i == 4 ? 'Very Good' : ($i == 3 ? 'Good' : ($i == 2 ? 'Fair' : 'Poor'))) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('venue_rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">What did you like most about this training?</label>
                            <textarea class="form-control @error('what_you_liked') is-invalid @enderror" 
                                      name="what_you_liked" rows="4">{{ old('what_you_liked', $evaluation->what_you_liked ?? '') }}</textarea>
                            @error('what_you_liked')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">What can be improved?</label>
                            <textarea class="form-control @error('what_can_be_improved') is-invalid @enderror" 
                                      name="what_can_be_improved" rows="4">{{ old('what_can_be_improved', $evaluation->what_can_be_improved ?? '') }}</textarea>
                            @error('what_can_be_improved')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Comments</label>
                            <textarea class="form-control @error('additional_comments') is-invalid @enderror" 
                                      name="additional_comments" rows="4">{{ old('additional_comments', $evaluation->additional_comments ?? '') }}</textarea>
                            @error('additional_comments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="would_recommend" 
                                       value="1" id="would_recommend" 
                                       {{ old('would_recommend', $evaluation->would_recommend ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="would_recommend">
                                    I would recommend this training to others
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('trainings.show', $training->id) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="bx bx-save me-1"></i>Submit Evaluation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




