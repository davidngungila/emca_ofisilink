@extends('layouts.app')

@section('title', 'Create New Assessment - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bx bx-plus-circle me-2"></i>Create New Assessment
                            </h4>
                            <p class="mb-0 text-muted">Define your main responsibility and activities</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.performance_management_module') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-file me-2"></i>Assessment Information</h5>
                </div>
                <div class="card-body">
                    <form id="create-assessment-form">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Main Responsibility Title <span class="text-danger">*</span></label>
                            <input type="text" name="main_responsibility" class="form-control" required placeholder="e.g., Customer Service Management">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide a detailed description of this responsibility..."></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Aligned Organizational Goal</label>
                            <select name="organizational_goal_id" class="form-select">
                                <option value="">-- Select Goal (Optional) --</option>
                                @foreach($goals as $goal)
                                    <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Link this assessment to a broader organizational objective.</small>
                        </div>
                        
                        <input type="hidden" name="contribution_percentage" value="100">

                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Sub Responsibilities / Activities</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="import-task-btn">
                                    <i class="bx bx-import me-1"></i> Import from Tasks
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="add-activity">
                                    <i class="bx bx-plus"></i> Add Activity
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="activities-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30%">Activity Name <span class="text-danger">*</span></th>
                                        <th style="width:45%">Description</th>
                                        <th style="width:20%">Frequency <span class="text-danger">*</span></th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <small class="text-muted">
                            <i class="bx bx-info-circle"></i> Contribution percentage will be automatically distributed equally among all activities.
                        </small>
                        
                        <div class="alert alert-danger d-none mt-3" id="create-error"></div>
                        <div class="alert alert-success d-none mt-3" id="create-success"></div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bx bx-check me-2"></i>Submit for HOD Approval
                            </button>
                            <a href="{{ route('modules.hr.performance_management_module') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Import Modal -->
<div class="modal fade" id="taskImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-task me-2"></i>Import Tasks as Activities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center" id="task-loader">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading your tasks...</p>
                </div>
                <div id="task-empty" class="d-none text-center py-4">
                    <i class="bx bx-task-x fs-1 text-muted"></i>
                    <p class="mt-2">No active tasks found in the Task Module.</p>
                </div>
                <div class="table-responsive d-none" id="task-list-container">
                    <table class="table table-hover table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%"><input type="checkbox" id="select-all-tasks" class="form-check-input"></th>
                                <th>Task Name</th>
                                <th>Project/Main Task</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="task-list-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirm-import-tasks">
                    Import Selected (<span id="selected-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const tableBody = document.querySelector('#activities-table tbody');
    const addBtn = document.getElementById('add-activity');
    const importBtn = document.getElementById('import-task-btn');
    const form = document.getElementById('create-assessment-form');
    const errBox = document.getElementById('create-error');
    const okBox = document.getElementById('create-success');
    
    // Modal elements
    const taskModal = new bootstrap.Modal(document.getElementById('taskImportModal'));
    const taskLoader = document.getElementById('task-loader');
    const taskEmpty = document.getElementById('task-empty');
    const taskContainer = document.getElementById('task-list-container');
    const taskBody = document.getElementById('task-list-body');
    const confirmImportBtn = document.getElementById('confirm-import-tasks');
    const selectAllCheck = document.getElementById('select-all-tasks');
    const selectedCountSpan = document.getElementById('selected-count');

    function addRow(data = {}){
        const tr = document.createElement('tr');
        const name = data.activity_name || '';
        const desc = data.description || '';
        const freq = data.reporting_frequency || 'monthly';
        const taskId = data.task_activity_id || '';
        
        tr.innerHTML = `
            <td>
                <input type="text" name="activities[][activity_name]" class="form-control form-control-sm" required placeholder="Activity name" value="${name}">
                <input type="hidden" name="activities[][task_activity_id]" value="${taskId}">
            </td>
            <td><input type="text" name="activities[][description]" class="form-control form-control-sm" placeholder="Description" value="${desc}"></td>
            <td>
                <select name="activities[][reporting_frequency]" class="form-select form-select-sm" required>
                    <option value="">-- Select --</option>
                    <option value="daily" ${freq==='daily'?'selected':''}>Daily</option>
                    <option value="weekly" ${freq==='weekly'?'selected':''}>Weekly</option>
                    <option value="monthly" ${freq==='monthly'?'selected':''}>Monthly</option>
                </select>
            </td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
        `;
        tableBody.appendChild(tr);
        tr.querySelector('.remove-row').addEventListener('click', function(){ tr.remove(); });
    }

    addBtn.addEventListener('click', () => addRow());

    importBtn.addEventListener('click', function() {
        taskModal.show();
        loadTasks();
    });

    function loadTasks() {
        taskLoader.classList.remove('d-none');
        taskEmpty.classList.add('d-none');
        taskContainer.classList.add('d-none');
        taskBody.innerHTML = '';
        confirmImportBtn.disabled = true;

        fetch("{{ route('modules.tasks.user-tasks') }}")
            .then(r => r.json())
            .then(res => {
                if(res.success && res.tasks.length > 0) {
                    renderTasks(res.tasks);
                } else {
                    taskLoader.classList.add('d-none');
                    taskEmpty.classList.remove('d-none');
                }
            })
            .catch(e => {
                console.error(e);
                taskLoader.classList.add('d-none');
                taskEmpty.innerHTML = '<p class="text-danger mt-3">Failed to load tasks.</p>';
                taskEmpty.classList.remove('d-none');
            });
    }

    function renderTasks(tasks) {
        taskLoader.classList.add('d-none');
        taskContainer.classList.remove('d-none');
        confirmImportBtn.disabled = false;

        tasks.forEach(task => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="checkbox" class="form-check-input task-check" value="${task.id}" data-name="${task.name}" data-main="${task.main_task ? task.main_task.name : ''}"></td>
                <td><span class="fw-semibold">${task.name}</span></td>
                <td><small class="text-muted">${task.main_task ? task.main_task.name : 'General'}</small></td>
                <td>${task.end_date || '-'}</td>
                <td><span class="badge bg-label-${getStatusColor(task.status)}">${task.status}</span></td>
            `;
            taskBody.appendChild(tr);
        });

        // Re-attach listeners for checkboxes to update count
        document.querySelectorAll('.task-check').forEach(cb => {
            cb.addEventListener('change', updateCount);
        });
    }

    function getStatusColor(status) {
        if(!status) return 'secondary';
        const s = status.toLowerCase();
        if(s.includes('completed')) return 'success';
        if(s.includes('progress')) return 'info';
        if(s.includes('hold')) return 'warning';
        return 'secondary';
    }

    function updateCount() {
        const count = document.querySelectorAll('.task-check:checked').length;
        selectedCountSpan.textContent = count;
    }

    selectAllCheck.addEventListener('change', function() {
        document.querySelectorAll('.task-check').forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    confirmImportBtn.addEventListener('click', function() {
        const selected = [];
        document.querySelectorAll('.task-check:checked').forEach(cb => {
            selected.push({
                activity_name: cb.dataset.name,
                description: 'Imported from Task Module: ' + cb.dataset.main,
                reporting_frequency: 'monthly', // Default
                task_activity_id: cb.value
            });
        });

        if(selected.length > 0) {
            selected.forEach(s => addRow(s));
            taskModal.hide();
        }
    });

    form.addEventListener('submit', function(e){
        e.preventDefault();
        errBox.classList.add('d-none'); errBox.textContent='';
        okBox.classList.add('d-none'); okBox.textContent='';

        const url = "{{ route('performance_management_module.store') }}";

        const rows = Array.from(tableBody.querySelectorAll('tr'));
        const activities = [];
        rows.forEach(function(tr){
            const name = tr.querySelector('input[name="activities[][activity_name]"]').value.trim();
            const desc = tr.querySelector('input[name="activities[][description]"]').value.trim();
            const freq = tr.querySelector('select[name="activities[][reporting_frequency]"]').value;
            const tid = tr.querySelector('input[name="activities[][task_activity_id]"]').value;
            
            if (name !== '') {
                activities.push({
                    activity_name: name,
                    description: desc || null,
                    reporting_frequency: freq,
                    task_activity_id: tid || null
                });
            }
        });

        if (activities.length === 0) {
            errBox.textContent = 'Add at least one activity and fill its name.';
            errBox.classList.remove('d-none');
            return;
        }

        const payload = {
            main_responsibility: form.querySelector('input[name="main_responsibility"]').value,
            description: form.querySelector('textarea[name="description"]').value,
            organizational_goal_id: form.querySelector('select[name="organizational_goal_id"]').value,
            contribution_percentage: form.querySelector('input[name="contribution_percentage"]').value,
            activities: activities
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

        fetch(url, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 
                'Accept': 'application/json', 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(payload)
        }).then(async (res)=>{
            const data = await res.json().catch(()=>({success:false,message:'Unexpected response'}));
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to submit');
            }
            okBox.textContent = data.message || 'Submitted successfully';
            okBox.classList.remove('d-none');
            setTimeout(()=>{ window.location.href = "{{ route('modules.hr.performance_management_module') }}"; }, 1500);
        }).catch((e)=>{
            errBox.textContent = e.message || 'Failed to submit';
            errBox.classList.remove('d-none');
        }).finally(()=>{
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endpush
