<!-- New Imprest Request Modal -->
<div class="modal fade" id="newImprestModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-plus-circle me-2"></i>Create New Imprest Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="newImprestForm">
        @csrf
        <div class="modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div class="mb-3">
            <label class="form-label fw-bold">
              Purpose <span class="text-danger">*</span>
            </label>
            <div class="position-relative">
              <input type="text" class="form-control form-control-lg" name="purpose" id="purpose" required placeholder="e.g., Training, Field Work, Conference">
              <div class="hover-info-tooltip">
                <i class="bx bx-info-circle"></i>
                <span class="hover-info-text">Provide a clear purpose for this imprest request</span>
              </div>
            </div>
          </div>
            <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Amount (TZS) <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-money text-primary"></i>
                </span>
                <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="1" required placeholder="Enter amount">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Expected Return Date</label>
            <div class="position-relative">
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-calendar text-primary"></i>
                </span>
                <input type="date" class="form-control" name="expected_return_date" id="expected_return_date" min="{{ date('Y-m-d') }}">
              </div>
              <div class="position-relative d-inline-block mt-1">
                <div class="hover-info-tooltip">
                  <i class="bx bx-info-circle"></i>
                  <span class="hover-info-text">Optional: When do you expect to return/receive this amount?</span>
                </div>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <div class="position-relative">
              <textarea class="form-control" name="description" id="description" rows="4" placeholder="Additional details about this imprest request..."></textarea>
              <div class="hover-info-tooltip" style="top: 10px; transform: none;">
                <i class="bx bx-info-circle"></i>
                <span class="hover-info-text">Provide any additional information that might be helpful</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-lg text-white" id="submitImprestBtn">
            <i class="bx bx-check-circle me-1"></i>Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#newImprestModal {
    z-index: 99999 !important;
}

#newImprestModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#newImprestModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
}

#newImprestModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#newImprestModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#newImprestModal .modal-header,
#newImprestModal .modal-footer {
    flex-shrink: 0;
}

#newImprestModal .form-control:focus,
#newImprestModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 991.98px) {
    #newImprestModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #newImprestModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}

/* Hover Info Tooltip Styles */
.hover-info-tooltip {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: help;
    z-index: 10;
}

.hover-info-tooltip i {
    font-size: 1.1rem;
    color: #940000;
    transition: color 0.2s;
}

.hover-info-tooltip:hover i {
    color: #7a0000;
}

.hover-info-text {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    bottom: 125%;
    right: 0;
    background-color: #940000;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    white-space: nowrap;
    font-size: 0.875rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: opacity 0.3s, visibility 0.3s;
    z-index: 1000;
    pointer-events: none;
    min-width: 200px;
}

.hover-info-text::after {
    content: "";
    position: absolute;
    top: 100%;
    right: 15px;
    border-width: 5px;
    border-style: solid;
    border-color: #940000 transparent transparent transparent;
}

.hover-info-tooltip:hover .hover-info-text {
    visibility: visible;
    opacity: 1;
}

/* For inline tooltips */
.position-relative.d-inline-block .hover-info-tooltip {
    position: relative;
    display: inline-block;
    top: auto;
    right: auto;
    transform: none;
    margin-left: 5px;
}

.position-relative.d-inline-block .hover-info-text {
    bottom: auto;
    top: 125%;
    right: auto;
    left: 0;
}

.position-relative.d-inline-block .hover-info-text::after {
    top: auto;
    bottom: 100%;
    right: auto;
    left: 15px;
    border-color: transparent transparent #940000 transparent;
}
</style>

<script>
// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('newImprestModal');
  if (modal) {
    modal.addEventListener('hidden.bs.modal', function() {
      const form = document.getElementById('newImprestForm');
      if (form) {
        form.reset();
        const submitBtn = form.querySelector('#submitImprestBtn');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bx bx-check-circle me-1"></i>Submit Request';
        }
      }
    });
  }
});
</script>

