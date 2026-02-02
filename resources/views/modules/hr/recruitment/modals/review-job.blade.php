<!-- Review Job Modal (for CEO approval) -->
<div class="modal fade" id="reviewJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bx bx-shield-quarter me-2"></i>Review Job Vacancy
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewJobModalBody">
                 <div class="text-center p-5">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div class="vr mx-2"></div>
                <button type="button" class="btn btn-outline-danger btn-reject-from-modal" data-id="">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-success btn-approve-from-modal" data-id="">
                    <i class="bx bx-check me-1"></i> Approve & Publish
                </button>
            </div>
        </div>
    </div>
</div>
