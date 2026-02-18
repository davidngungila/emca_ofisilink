@extends('layouts.app')

@section('title', 'Set Strategic Goal')

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
                <div class="card-header bg-gradient-primary p-4">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('modules.performance_management_module') }}" class="btn btn-sm btn-glass me-3">
                            <i class="bx bx-left-arrow-alt"></i>
                        </a>
                        <h4 class="mb-0 text-white fw-bold">Set Strategic Organizational Goal</h4>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('modules.performance_management_module.action') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="create_organizational_goal">
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Strategic Objective Title</label>
                                <input type="text" name="title" class="form-control form-control-lg rounded-3 border-primary-focus" placeholder="e.g., Increase Overall Efficiency by 20%" required>
                                <div class="form-text text-muted">This will be at the root of the goal cascade tree.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Detailed Description</label>
                                <textarea name="description" class="form-control rounded-3" rows="5" placeholder="Outline the key outcomes and expectations for this strategic goal..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                    <input type="date" name="start_date" class="form-control rounded-3" value="{{ date('Y-01-01') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">End/Deadline Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                    <input type="date" name="end_date" class="form-control rounded-3" value="{{ date('Y-12-31') }}" required>
                                </div>
                            </div>
                            <div class="col-12 mt-5">
                                <hr class="my-4">
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="{{ route('modules.performance_management_module') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                                        <i class="bx bx-check-circle me-2"></i>Publish Strategic Goal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
