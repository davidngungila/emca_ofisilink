<!-- Report Approval Modal -->
<div class="modal fade" id="approveReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('modules.performance_management_module.action') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="approve_report">
            <input type="hidden" name="report_id" id="approveReportId">
            
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Review Progress Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Qualitative Quality Rating (1-10)</label>
                            <input type="range" name="quality_rating" class="form-range" style="accent-color: #940000;" min="1" max="10" step="1" value="5" oninput="this.nextElementSibling.value = this.value">
                            <output class="fw-bold" style="color: #940000;">5</output>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Supervisor Comments</label>
                            <textarea name="comments" class="form-control rounded-3" rows="3" placeholder="Provide feedback to the employee..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #940000;">Approve & Rate</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Reject Modal -->
<div class="modal fade" id="rejectReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('modules.performance_management_module.action') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="reject_report">
            <input type="hidden" name="report_id" id="rejectReportId">
            
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Reject Progress Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-soft-danger d-flex align-items-center mb-3">
                        <i class="bx bx-error-circle me-2"></i>
                        <small>Are you sure you want to reject this report? This will notify the employee.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Reason for Rejection</label>
                        <textarea name="comments" class="form-control rounded-3" rows="3" placeholder="Provide a reason..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Reject Report</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Request Changes Modal -->
<div class="modal fade" id="requestChangesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('modules.performance_management_module.action') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="request_changes">
            <input type="hidden" name="report_id" id="requestChangesReportId">
            
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Request Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-12">
                        <label class="form-label fw-bold">What should be improved?</label>
                        <textarea name="comments" class="form-control rounded-3" rows="3" placeholder="Describe the required changes..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info px-4 fw-bold text-white">Send Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .alert-soft-info { background-color: rgba(148, 0, 0, 0.05); color: #940000; border: none; }
    .alert-soft-danger { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
    .btn-soft-secondary { background-color: rgba(107, 114, 128, 0.1); color: #6b7280; border: none; }
    .btn-soft-secondary:hover { background-color: #6b7280; color: white; }
    .btn-soft-danger { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
    .btn-soft-danger:hover { background-color: #ef4444; color: white; }
    .form-range::-webkit-slider-thumb { background: #940000; }
    .form-range::-moz-range-thumb { background: #940000; }
    .form-range::-ms-thumb { background: #940000; }
</style>
