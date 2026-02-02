@extends('layouts.app')

@section('title', 'Application Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<style>
    :root {
        --kanban-bg: #f5f6f8;
        --column-bg: #ebecf0;
        --card-bg: #ffffff;
    }
    
    .application-container {
        height: calc(100vh - 180px); /* Adjust based on header height */
        display: flex;
        flex-direction: column;
    }

    /* View Toggles */
    .view-toggle .btn {
        border: none;
        background: transparent;
        color: #697a8d;
    }
    .view-toggle .btn.active {
        background: white;
        color: #696cff; /* Primary color */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* List View Styles */
    .app-list-item {
        border-bottom: 1px solid #f0f2f4;
        transition: background 0.2s;
        cursor: pointer;
    }
    .app-list-item:hover {
        background-color: #f8f9fa;
    }
    .app-list-item:last-child {
        border-bottom: none;
    }
    
    /* Kanban View Styles */
    .kanban-board {
        display: flex;
        overflow-x: auto;
        height: 100%;
        gap: 1.5rem;
        padding-bottom: 1rem;
    }
    .kanban-column {
        min-width: 300px;
        width: 300px;
        background: var(--kanban-bg);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .kanban-header {
        padding: 1rem;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid transparent;
    }
    .kanban-header.status-applied { border-color: #696cff; color: #696cff; }
    .kanban-header.status-shortlisted { border-color: #03c3ec; color: #03c3ec; }
    .kanban-header.status-interviewing { border-color: #ffab00; color: #ffab00; }
    .kanban-header.status-offer { border-color: #71dd37; color: #71dd37; }
    .kanban-header.status-rejected { border-color: #ff3e1d; color: #ff3e1d; }

    .kanban-body {
        padding: 1rem;
        overflow-y: auto;
        flex-grow: 1;
    }
    .kanban-card {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid transparent;
    }
    .kanban-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #e0e0e0;
    }
    
    .avatar-initial {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* Modal Profile */
    .profile-header-bg {
        height: 120px;
        background: linear-gradient(135deg, #696cff 0%, #a2a5ff 100%);
        border-radius: 12px 12px 0 0;
    }
    .profile-avatar-xl {
        width: 100px;
        height: 100px;
        border: 4px solid white;
        background: white;
        position: absolute;
        bottom: -50px;
        left: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid flex-grow-1 container-p-y h-100 d-flex flex-column">
    
    <!-- Top Bar: Filters & Actions -->
    <div class="card shadow-sm border-0 mb-3 flex-shrink-0">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-hive me-2"></i>Applications</h5>
                    <div class="vr h-100 mx-2"></div>
                    
                    <!-- Search -->
                    <div class="input-group input-group-merge" style="max-width: 300px;">
                        <span class="input-group-text border-0 bg-light pl-2"><i class="bx bx-search small"></i></span>
                        <input type="text" class="form-control border-0 bg-light" id="searchInput" placeholder="Search candidates...">
                    </div>

                    <!-- Job Filter -->
                    <select id="jobFilter" class="form-select border-0 bg-light" style="max-width: 200px;">
                        <option value="">All Jobs</option>
                        <!-- Loaded via JS -->
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="bg-light rounded p-1 view-toggle d-flex">
                        <button class="btn btn-sm active" id="btnViewKanban" title="Kanban Board"><i class="bx bx-columns"></i></button>
                        <button class="btn btn-sm" id="btnViewList" title="List View"><i class="bx bx-list-ul"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow-1 overflow-hidden position-relative">
        <div id="loader" class="position-absolute top-50 start-50 translate-middle text-center" style="display: none;">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted small">Loading Candidates...</p>
        </div>

        <!-- Kanban View -->
        <div id="viewKanban" class="h-100 overflow-hidden" style="display: none;">
            <div class="kanban-board custom-scroll px-2">
                
                <!-- Applied Column -->
                <div class="kanban-column">
                    <div class="kanban-header status-applied bg-white shadow-sm">
                        <span>Applied</span>
                        <span class="badge bg-label-primary rounded-pill count-applied">0</span>
                    </div>
                    <div class="kanban-body custom-scroll" id="col-applied">
                        <!-- Items -->
                    </div>
                </div>

                <!-- Shortlisted Column -->
                <div class="kanban-column">
                    <div class="kanban-header status-shortlisted bg-white shadow-sm">
                        <span>Shortlisted</span>
                        <span class="badge bg-label-info rounded-pill count-shortlisted">0</span>
                    </div>
                    <div class="kanban-body custom-scroll" id="col-shortlisted">
                        <!-- Items -->
                    </div>
                </div>

                <!-- Interviewing Column -->
                <div class="kanban-column">
                    <div class="kanban-header status-interviewing bg-white shadow-sm">
                        <span>Interviewing</span>
                        <span class="badge bg-label-warning rounded-pill count-interviewing">0</span>
                    </div>
                    <div class="kanban-body custom-scroll" id="col-interviewing">
                        <!-- Items -->
                    </div>
                </div>

                <!-- Offer Extended Column -->
                <div class="kanban-column">
                    <div class="kanban-header status-offer bg-white shadow-sm">
                        <span>Offer Extended</span>
                        <span class="badge bg-label-success rounded-pill count-offer_extended">0</span>
                    </div>
                    <div class="kanban-body custom-scroll" id="col-offer_extended">
                        <!-- Items -->
                    </div>
                </div>

                <!-- Rejected Column -->
                <div class="kanban-column">
                    <div class="kanban-header status-rejected bg-white shadow-sm">
                         <span>Rejected</span>
                         <span class="badge bg-label-danger rounded-pill count-rejected">0</span>
                    </div>
                    <div class="kanban-body custom-scroll" id="col-rejected">
                        <!-- Items -->
                    </div>
                </div>

            </div>
        </div>

        <!-- List View -->
        <div id="viewList" class="card h-100 shadow-sm border-0" style="display: none;">
            <div class="table-responsive h-100 custom-scroll">
                <table class="table table-hover align-middle">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th class="ps-4">Candidate</th>
                            <th>Applied For</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Contacts</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="listTableBody">
                        <!-- Rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-body p-0">
                <div class="position-relative mb-5">
                    <div class="profile-header-bg">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="profile-avatar-xl shadow-sm">
                        <span id="modalAvatarInitials" class="text-primary fw-bold">JD</span>
                    </div>
                    <div class="position-absolute bottom-0 start-0 mb-0 ms-5 ps-5 pb-2">
                        <h3 class="mb-0 fw-bold ms-4" id="modalName">John Doe</h3>
                        <p class="text-white-50 mb-0 ms-4 small" id="modalJobTitle">Senior Accountant</p>
                    </div>
                    <div class="position-absolute bottom-0 end-0 m-3">
                         <div class="btn-group shadow-sm">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-edit me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="statusDropdown">
                                <li><a class="dropdown-item" href="#" data-status="Applied">Applied</a></li>
                                <li><a class="dropdown-item" href="#" data-status="Shortlisted">Shortlisted</a></li>
                                <li><a class="dropdown-item" href="#" data-status="Interviewing">Interviewing</a></li>
                                <li><a class="dropdown-item" href="#" data-status="Offer Extended">Offer Extended</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" data-status="Rejected">Reject</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row g-0 px-4 pb-4">
                    <!-- Left Sidebar -->
                    <div class="col-md-3 border-end pe-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Contact Info</h6>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Email</label>
                            <a href="#" class="text-body fw-medium" id="modalEmail">john@example.com</a>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Phone</label>
                            <span class="fw-medium" id="modalPhone">+255 123 456 789</span>
                        </div>
                        <div class="mb-4">
                             <label class="small text-muted d-block">Applied On</label>
                             <span class="fw-medium" id="modalDate">Jan 12, 2026</span>
                        </div>

                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Attachments</h6>
                        <div id="modalAttachments" class="d-flex flex-column gap-2">
                            <!-- Files -->
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-md-9 ps-4">
                        <h5 class="fw-bold text-primary mb-3"><i class="bx bx-file me-2"></i>Cover Letter</h5>
                        <div class="bg-light p-3 rounded mb-4 text-secondary" id="modalCoverLetter" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">
                            <!-- Cover Letter -->
                        </div>

                         <div class="card mb-4 border shadow-none">
                            <div class="card-header bg-white border-bottom fw-bold">
                                <i class="bx bx-history me-2"></i>History & Notes
                            </div>
                            <div class="card-body p-3 bg-light text-center text-muted">
                                <small>Activity log functionality coming soon...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="modalAppId">
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // --- State Management ---
        const state = {
            applications: [], // All loaded applications
            jobs: [],
            filters: {
                search: '',
                jobId: '', 
                status: ''
            },
            view: 'kanban' // 'kanban' or 'list'
        };

        const csrfToken = '{{ csrf_token() }}';
        const recruitmentUrl = '{{ route("recruitment.handle") }}';

        // --- Initialization ---
        
        // 1. Check URL Params
        const urlParams = new URLSearchParams(window.location.search);
        const urlJobId = urlParams.get('job_id');
        if(urlJobId) {
            state.filters.jobId = urlJobId;
        }

        // 2. Load Data
        init();

        function init() {
            loadJobs().then(() => {
                if(state.filters.jobId) {
                    $('#jobFilter').val(state.filters.jobId);
                }
            });
            loadApplications();
            
            // Set initial view
            toggleView('kanban');
        }

        // --- Event Listeners ---
        $('#btnViewKanban').click(() => toggleView('kanban'));
        $('#btnViewList').click(() => toggleView('list'));

        $('#searchInput').on('keyup', function() {
            state.filters.search = $(this).val().toLowerCase();
            render();
        });

        $('#jobFilter').on('change', function() {
            state.filters.jobId = $(this).val();
            // Update URL without reload
            const newUrl = new URL(window.location);
            if(state.filters.jobId) {
                newUrl.searchParams.set('job_id', state.filters.jobId);
            } else {
                newUrl.searchParams.delete('job_id');
            }
            window.history.pushState({}, '', newUrl);
            render();
        });

        // Modal Actions
        $('#statusDropdown .dropdown-item').click(function(e) {
            e.preventDefault();
            const newStatus = $(this).data('status');
            const appId = $('#modalAppId').val();
            updateStatus(appId, newStatus);
        });

        // --- Data Loading ---
        function loadJobs() {
            return $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { _token: csrfToken, action: 'get_all_jobs' },
                success: function(res) {
                    if(res.success) {
                        state.jobs = res.jobs;
                        const select = $('#jobFilter');
                        res.jobs.forEach(j => {
                            select.append(`<option value="${j.id}">${j.job_title}</option>`);
                        });
                    }
                }
            });
        }

        function loadApplications() {
            $('#loader').show();
            $.ajax({
                url: recruitmentUrl, type: 'POST',
                data: { _token: csrfToken, action: 'get_bulk_applications' },
                success: function(res) {
                    if(res.success) {
                        state.applications = res.applications || [];
                        render();
                    }
                },
                complete: () => $('#loader').hide()
            });
        }

        function updateStatus(id, status) {
            Swal.fire({
                title: 'Update Status?',
                text: `Move candidate to ${status}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: recruitmentUrl, type: 'POST',
                        data: { 
                            _token: csrfToken, 
                            action: 'update_application_status',
                            application_id: id,
                            status: status
                        },
                        success: function(res) {
                            if(res.success) {
                                // Update local state
                                const appIndex = state.applications.findIndex(a => a.id == id);
                                if(appIndex > -1) {
                                    state.applications[appIndex].status = status;
                                    // Update modal badge if open
                                    $('#modalAppId').closest('.modal').modal('hide');
                                    render();
                                    Swal.fire('Updated', 'Candidate status updated.', 'success');
                                }
                            }
                        }
                    });
                }
            });
        }


        // --- Rendering ---
        function render() {
            // Filter Data
            const filtered = state.applications.filter(app => {
                const matchesJob = !state.filters.jobId || app.job_id == state.filters.jobId;
                const matchesSearch = !state.filters.search || 
                    (app.first_name + ' ' + app.last_name).toLowerCase().includes(state.filters.search) ||
                    app.email.toLowerCase().includes(state.filters.search);
                return matchesJob && matchesSearch;
            });

            if(state.view === 'kanban') renderKanban(filtered);
            else renderList(filtered);
        }

        function renderKanban(data) {
            // Columns
            const cols = {
                'Applied': [], 'Shortlisted': [], 'Interviewing': [], 'Offer Extended': [], 'Hired': [], 'Rejected': []
            };
            
            // Group By Status
            data.forEach(app => {
                // Normalize status key
                let key = app.status; // Assume casing matches
                if(!cols[key]) key = 'Applied'; // Fallback
                cols[key].push(app);
            });

            // Render Columns
            for (const [status, items] of Object.entries(cols)) {
                // Determine DOM ID
                let colId = 'col-' + status.toLowerCase().replace(' ', '_');
                $(`#${colId}`).empty();
                $(`.count-${status.toLowerCase().replace(' ', '_')}`).text(items.length);

                items.forEach(app => {
                    $(`#${colId}`).append(createKanbanCard(app));
                });
            }
        }

        function createKanbanCard(app) {
           const initials = (app.first_name[0] + app.last_name[0]).toUpperCase();
           const jobTitle = app.job ? app.job.job_title : 'Unknown Job';
           
           return `
            <div class="kanban-card" onclick="openProfile(${app.id})">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar-initial bg-label-primary me-2">${initials}</div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-truncate">${app.first_name} ${app.last_name}</div>
                        <div class="small text-muted text-truncate" style="font-size: 0.75rem;">${jobTitle}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light">
                     <small class="text-muted"><i class="bx bx-calendar me-1"></i>${new Date(app.created_at).toLocaleDateString()}</small>
                     ${app.documents_count > 0 ? `<small class="text-muted"><i class="bx bx-paperclip"></i> ${app.documents_count}</small>` : ''}
                </div>
            </div>
           `;
        }

        function renderList(data) {
            const tbody = $('#listTableBody');
            tbody.empty();
            
            if(data.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center py-5">No applications found matching filters.</td></tr>');
                return;
            }

            data.forEach(app => {
                 const initials = (app.first_name[0] + app.last_name[0]).toUpperCase();
                 const jobTitle = app.job ? app.job.job_title : 'Unknown Job';
                 
                 tbody.append(`
                    <tr class="app-list-item" onclick="openProfile(${app.id})">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-initial bg-label-primary me-3">${initials}</div>
                                <div>
                                    <div class="fw-bold">${app.first_name} ${app.last_name}</div>
                                    <small class="text-muted">${app.email}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-label-secondary">${jobTitle}</span></td>
                        <td>${new Date(app.created_at).toLocaleDateString()}</td>
                        <td><span class="badge bg-label-${getStatusColor(app.status)}">${app.status}</span></td>
                        <td>${app.phone}</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-icon btn-outline-secondary"><i class="bx bx-show"></i></button>
                        </td>
                    </tr>
                 `);
            });
        }

        // --- UI View Logic ---
        function toggleView(viewName) {
            state.view = viewName;
            if(viewName === 'kanban') {
                $('#viewList').hide();
                $('#viewKanban').fadeIn(200);
                $('#btnViewList').removeClass('active');
                $('#btnViewKanban').addClass('active');
            } else {
                $('#viewKanban').hide();
                $('#viewList').fadeIn(200);
                $('#btnViewKanban').removeClass('active');
                $('#btnViewList').addClass('active');
            }
            render();
        }

        function getStatusColor(status) {
            const map = {
                'Applied': 'primary', 'Shortlisted': 'info', 'Interviewing': 'warning',
                'Offer Extended': 'success', 'Rejected': 'danger', 'Hired': 'success'
            };
            return map[status] || 'secondary';
        }

        // Global export for onclick events
        window.openProfile = function(id) {
            const app = state.applications.find(a => a.id == id);
            if(!app) return;

            $('#modalAppId').val(app.id);
            $('#modalName').text(`${app.first_name} ${app.last_name}`);
            $('#modalAvatarInitials').text((app.first_name[0] + app.last_name[0]).toUpperCase());
            $('#modalJobTitle').text(app.job ? app.job.job_title : '');
            $('#modalEmail').text(app.email).attr('href', `mailto:${app.email}`);
            $('#modalPhone').text(app.phone);
            $('#modalDate').text(new Date(app.created_at).toLocaleDateString());
            $('#modalCoverLetter').text(app.cover_letter || 'No cover letter provided.');

            // Attachments
            const attachContainer = $('#modalAttachments');
            attachContainer.empty();
            if(app.documents && app.documents.length) {
                app.documents.forEach(doc => {
                    attachContainer.append(`
                        <a href="/jobs/applications/${doc.id}/download" class="btn btn-sm btn-outline-secondary text-start text-truncate" target="_blank">
                            <i class="bx bx-file me-1"></i> ${doc.original_filename}
                        </a>
                    `);
                });
            } else {
                attachContainer.html('<small class="text-muted">No attachments</small>');
            }

            $('#profileModal').modal('show');
        };
    });
</script>
@endpush
