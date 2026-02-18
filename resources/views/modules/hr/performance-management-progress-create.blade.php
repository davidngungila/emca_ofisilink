@extends('layouts.app')

@section('title', 'Submit Progress Report')

@section('content')
<style>
    :root {
        --pm-primary: #940000;
        --pm-bg-soft: rgba(148, 0, 0, 0.05);
    }
    .card-pm { border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); border-radius: 1rem; }
    .btn-pm-primary { background-color: var(--pm-primary); border-color: var(--pm-primary); color: #fff; }
    .btn-pm-primary:hover { background-color: #7a0000; color: #fff; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-pm overflow-hidden">
                <div class="card-header bg-white border-bottom py-4 px-4">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('modules.performance_management_module') }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="bx bx-left-arrow-alt"></i>
                        </a>
                        <div>
                            <h4 class="mb-0 fw-bold">Submit Progress Report</h4>
                            <p class="text-muted mb-0 small">{{ $activity->activity_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('modules.performance_management_module.action') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="action" value="submit_progress_report">
                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Recent Achievements</label>
                            <textarea name="progress_text" class="form-control" rows="5" placeholder="What specific tasks did you complete?" required></textarea>
                            <div class="form-text">Describe your progress clearly for supervisor review.</div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Overall Progress (%)</label>
                                <div class="px-3 py-2 bg-light rounded-3">
                                    <input type="range" name="progress_percentage" class="form-range" style="accent-color: #940000;" min="0" max="100" value="{{ $activity->current_progress ?? 0 }}" oninput="this.nextElementSibling.value = this.value">
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Completion</small>
                                        <output class="fw-bold text-pm-primary">{{ $activity->current_progress ?? 0 }}</output>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Evidence (Optional)</label>
                                <input type="file" name="evidence_file" class="form-control">
                                <div class="form-text">Upload documents or images.</div>
                            </div>
                        </div>

                        <div class="alert bg-pm-soft text-pm-primary border-0 rounded-3 small">
                            <i class="bx bx-info-circle me-1"></i>
                            Your report will be reviewed and rated by your supervisor.
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('modules.performance_management_module') }}" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-pm-primary px-4 fw-bold">
                                Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
