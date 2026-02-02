<!-- Create/Edit Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="jobModalTitle">
                    <i class="bx bx-plus"></i> Create New Job Vacancy
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="jobForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" id="jobAction" value="create_job">
                    <input type="hidden" name="job_id" id="jobId">

                    <div class="mb-3">
                        <label class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" name="job_title" id="jobTitle" class="form-control" required maxlength="255">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institutional Position</label>
                            <select name="institutional_position_id" id="institutionalPositionId" class="form-select">
                                <option value="">Select Position (Optional)</option>
                                @foreach($institutionalPositions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->position_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Structure</label>
                            <select name="salary_structure_id" id="salaryStructureId" class="form-select">
                                <option value="">Select Structure (Auto-qualifications)</option>
                                @foreach($salaryStructures as $struct)
                                    <option value="{{ $struct->id }}">{{ $struct->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Description <span class="text-danger">*</span></label>
                        <textarea name="job_description" id="jobDescription" class="form-control" rows="5" required maxlength="2000"></textarea>
                        <small class="text-muted">Maximum 2000 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Qualifications & Requirements <span class="text-danger">*</span></label>
                        <textarea name="qualifications" id="qualifications" class="form-control" rows="5" required maxlength="2000"></textarea>
                        <small class="text-muted">Maximum 2000 characters</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Application Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="application_deadline" id="applicationDeadline" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Interview Mode <span class="text-danger">*</span></label>
                                <div class="border rounded p-3 bg-light">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Written" id="mode_written">
                                        <label class="form-check-label" for="mode_written">Written Test</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Oral" id="mode_oral">
                                        <label class="form-check-label" for="mode_oral">Oral Interview</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Practical" id="mode_practical">
                                        <label class="form-check-label" for="mode_practical">Practical Assessment</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Required Attachments</label>
                        <div id="attachmentsContainer">
                            <div class="input-group mb-2">
                                <input type="text" name="required_attachments[]" class="form-control" placeholder="e.g., Resume/CV">
                                <button type="button" class="btn btn-outline-danger" onclick="$(this).parent().remove()">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="$('#attachmentsContainer').append('<div class=\'input-group mb-2\'><input type=\'text\' name=\'required_attachments[]\' class=\'form-control\' placeholder=\'e.g., Cover Letter\'><button type=\'button\' class=\'btn btn-outline-danger\' onclick=\'$(this).parent().remove()\'><i class=\'bx bx-trash\'></i></button></div>')">
                            <i class="bx bx-plus"></i> Add Attachment
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="jobSubmitBtn">
                        <i class="bx bx-save"></i> Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
