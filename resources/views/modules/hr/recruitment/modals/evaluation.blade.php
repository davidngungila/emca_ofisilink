<!-- Evaluation Modal -->
<div class="modal fade" id="evaluationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-task me-2"></i>Candidate Evaluation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="evaluationForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="store_evaluation">
                    <input type="hidden" name="application_id" id="evalAppId">

                    <div class="alert alert-info d-flex align-items-center" role="alert">
                         <i class="bx bx-info-circle me-2"></i>
                         <div>
                             Evaluate <strong><span id="evalCandidateName">Candidate</span></strong> based on interview performance.
                         </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Written Score (0-100)</label>
                            <input type="number" class="form-control" name="written_score" id="scoreWritten" min="0" max="100" step="0.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Practical Score (0-100)</label>
                            <input type="number" class="form-control" name="practical_score" id="scorePractical" min="0" max="100" step="0.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Oral Score (0-100)</label>
                            <input type="number" class="form-control" name="oral_score" id="scoreOral" min="0" max="100" step="0.5">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Interviewer Comments</label>
                        <textarea class="form-control" name="comments" id="evalComments" rows="4" placeholder="Detailed strengths, weaknesses, and observations..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Recommendation</label>
                        <select class="form-select" name="recommendation" id="evalRec">
                            <option value="">Select Recommendation...</option>
                            <option value="Hire" class="text-success fw-bold">Strongly Recommend (Hire)</option>
                            <option value="Hold" class="text-warning fw-bold">Wait / Hold</option>
                            <option value="Reject" class="text-danger fw-bold">Reject</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Save Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>
