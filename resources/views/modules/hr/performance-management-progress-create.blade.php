@extends('layouts.app')

@section('title', 'Submit Progress Report - OfisiLink')

@section('content')
<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #940000 0%, #600000 100%) !important; }
    .text-primary { color: #940000 !important; }
    .btn-primary { background-color: #940000 !important; border-color: #940000 !important; }
    .btn-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-gradient-primary p-4 text-white">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('modules.performance_management_module.daily-report') }}" class="btn btn-sm btn-glass me-3">
                            <i class="bx bx-left-arrow-alt"></i>
                        </a>
                        <div>
                            <h4 class="mb-0 text-white fw-bold">Submit Progress Report</h4>
                            <small class="opacity-75">{{ $activity->activity_name }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('modules.performance_management_module.action') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="action" value="submit_progress_report">
                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Recent Progress Achievements</label>
                            <textarea name="progress_text" class="form-control rounded-3" rows="4" placeholder="Describe what you achieved today/this period..." required></textarea>
                            <div class="form-text">Provide specific details of completed tasks and milestones.</div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Current Completion Percentage (%)</label>
                                <div class="p-3 bg-light rounded-3">
                                    <input type="range" name="progress_percentage" class="form-range" style="accent-color: #940000;" min="0" max="100" value="{{ $activity->current_progress ?? 0 }}" oninput="this.nextElementSibling.value = this.value">
                                    <output class="fw-bold text-primary float-end">{{ $activity->current_progress ?? 0 }}</output>
                                    <span class="small text-muted">Slide to update status</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Upload Evidence (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bx bx-file"></i></span>
                                    <input type="file" name="evidence_file" class="form-control rounded-3 border-start-0">
                                </div>
                                <div class="form-text">PDF, DOCX, or Images of work done.</div>
                            </div>
                        </div>

                        <div class="alert alert-soft-info border-0 rounded-3 d-flex align-items-center">
                            <i class="bx bx-info-circle fs-4 me-2"></i>
                            <div>
                                This report will be sent to your supervisor for approval and quality rating.
                            </div>
                        </div>

                        <div class="mt-5">
                            <hr class="my-4">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('modules.performance_management_module.daily-report') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5 fw-bold">
                                    <i class="bx bx-check-double me-1"></i>Submit Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-glass { background-color: rgba(255, 255, 255, 0.1); color: white; border: 1px solid rgba(255, 255, 255, 0.2); }
    .btn-glass:hover { background-color: rgba(255, 255, 255, 0.2); color: white; }
    .alert-soft-info { background-color: rgba(148, 0, 0, 0.05); color: #940000; }
</style>
@endsection
