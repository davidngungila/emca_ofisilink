<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>@yield('title', 'OfisiLink - Office Management System')</title>

    <meta name="description" content="OfisiLink - Comprehensive Office Management System" />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <!-- DataTables (CDN) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" />
    <!-- jQuery UI for sortable (CDN) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

    <!-- Vite CSS -->
    @vite(['resources/css/app.css'])

    <!-- Page CSS -->
    @stack('styles')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!-- Template customizer & Theme config files -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- Custom OfisiLink Styles -->
    <style>
      :root {
        --bs-primary: #940000 !important;
        --bs-primary-rgb: 148, 0, 0 !important;
        --bs-primary-hover: #a80000 !important;
      }
      
      /* Primary Color Overrides */
      .btn-primary, .bg-primary { 
        background-color: #940000 !important; 
        border-color: #940000 !important; 
      }
      .btn-primary:hover, .bg-primary:hover { 
        background-color: #a80000 !important; 
        border-color: #a80000 !important; 
      }
      .text-primary { color: #940000 !important; }
      .badge-primary { background-color: #940000 !important; }
      .progress-bar { background-color: #940000 !important; }
      
      /* Menu and Header */
      .menu-vertical .menu-item.active > .menu-link {
        background-color: rgba(148, 0, 0, 0.1) !important;
        color: #940000 !important;
      }
      .menu-vertical .menu-link:hover {
        color: #940000 !important;
      }
      
      /* App Brand */
      .app-brand-logo svg use {
        fill: #940000 !important;
      }
      
      /* Navbar */
      .navbar-nav .nav-link.active {
        color: #940000 !important;
      }
      
      /* Cards */
      .card-header {
        border-bottom-color: rgba(148, 0, 0, 0.1) !important;
      }
      
      /* Dropdown hover support */
      .dropdown-hover .dropdown-menu {
        margin-top: 0;
      }
      
      /* Sidebar menu hover */
      .menu-item:has(.menu-sub):hover .menu-sub {
        display: block !important;
      }
      .menu-item:has(.menu-sub):hover {
        background-color: rgba(148, 0, 0, 0.05);
      }
      
      /* Tables */
      .table-primary {
        background-color: rgba(148, 0, 0, 0.1) !important;
      }
      
      /* Form Controls */
      .form-control:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25) !important;
      }
      
      /* Custom Components */
      .ofisi-primary {
        background-color: #940000 !important;
        color: white !important;
      }
      
      .ofisi-primary:hover {
        background-color: #a80000 !important;
        color: white !important;
      }
      
      /* Status Badges */
      .badge-pending { background-color: #ffc107 !important; }
      .badge-approved { background-color: #28a745 !important; }
      .badge-rejected { background-color: #dc3545 !important; }
      .badge-paid { background-color: #17a2b8 !important; }
      .badge-retired { background-color: #6c757d !important; }
      
      @media print {
        .btn-primary { 
          background-color: #940000 !important; 
          -webkit-print-color-adjust: exact; 
          print-color-adjust: exact; 
        }
      }
    </style>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{ route('dashboard') }}" class="app-brand-link" style="display:flex;justify-content:center;align-items:center;width:100%;">
              <span class="app-brand-logo demo">
                <x-logo width="72" alt="OfisiLink" />
              </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          @include('partials.sidebar')
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <!-- Branch Name Center -->
              <div class="d-flex align-items-center justify-content-center flex-grow-1">
                @if(auth()->check() && auth()->user()->branch)
                  <div class="text-center">
                    <span class="text-muted small d-block" style="font-size: 0.75rem;">Your working in branch</span>
                    <span class="fw-semibold text-primary">{{ auth()->user()->branch->name }}</span>
                  </div>
                @endif
              </div>

              <ul class="navbar-nav flex-row align-items-center">
                <!-- Notifications -->
                <li class="nav-item dropdown me-3 dropdown-hover" id="notifContainer">
                  <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notifBell">
                    <i class="bx bx-bell fs-4"></i>
                    <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="display:none" id="notifCount">0</span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 280px;max-width: 320px;" id="notifMenu">
                    <li class="dropdown-header p-2" style="font-size:0.875rem;font-weight:600;">Notifications</li>
                    <li>
                      <div style="max-height: 280px; overflow:auto" id="notifList">
                        <div class="p-3 text-muted text-center" style="font-size:0.875rem;">Loading...</div>
                      </div>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li><a class="dropdown-item small py-1 px-2" href="#" onclick="loadNotifDropdown(true); return false;" style="font-size:0.8rem;">Refresh</a></li>
                  </ul>
                </li>



                <!-- Quick Settings -->
              {{--   <li class="nav-item me-3 d-none d-md-block">
                  <a class="nav-link" href="{{ route('account.settings.index') }}" title="Account Settings">
                    <i class="bx bx-cog fs-4"></i>
                  </a>
                </li> --}}



                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown dropdown-hover">
                  <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online me-2">
                      @if(auth()->user()->photo)
                        @php
                          $photoUrl = route('storage.photos', ['filename' => auth()->user()->photo]);
                        @endphp
                        <img src="{{ $photoUrl }}?t={{ time() }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle user-profile-avatar" data-profile-image="true" style="object-fit: cover;" />
                      @else
                        <span class="avatar-initial rounded-circle bg-label-primary user-profile-avatar" data-profile-image="true">{{ substr(auth()->user()->name, 0, 1) }}</span>
                      @endif
                    </div>
                    <div class="d-none d-md-flex flex-column align-items-start" style="line-height: 1.2;">
                      <span class="fw-semibold" style="font-size: 0.875rem;">{{ auth()->user()->name }}</span>
                      <small class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->roles->first()->display_name ?? 'User' }}</small>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="{{ route('account.settings.index') }}">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              @if(auth()->user()->photo)
                                @php
                                  $photoUrl = route('storage.photos', ['filename' => auth()->user()->photo]);
                                @endphp
                                <img src="{{ $photoUrl }}?t={{ time() }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle user-profile-avatar" data-profile-image="true" style="object-fit: cover;" />
                              @else
                                <span class="avatar-initial rounded-circle bg-label-primary user-profile-avatar" data-profile-image="true">{{ substr(auth()->user()->name, 0, 1) }}</span>
                              @endif
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ auth()->user()->roles->first()->display_name ?? auth()->user()->roles->first()->name ?? 'User' }}</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('account.settings.index') }}">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('account.settings.index') }}">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item">
                          <i class="bx bx-power-off me-2"></i>
                          <span class="align-middle">Log Out</span>
                        </button>
                      </form>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              @yield('breadcrumb')
              @yield('content')
            </div>
            <!-- / Content -->

          

 <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme" style="border-top: 2px solid #940000;">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-3 flex-md-row flex-column align-items-center">
                <div class="mb-2 mb-md-0">
                  <span class="text-muted">Version: 1.0.0</span>
                  <span class="text-muted mx-2">|</span>
                  <span class="text-muted">© 2025 OfisiLink</span>
                </div>
                <div class="text-md-end">
                  <span class="text-muted">All rights reserved.</span>
                  <span class="text-muted mx-2">|</span>
                  <span class="text-muted">Powered By EmCa Techonologies</span>
                </div>
              </div>
            </footer>
            <!-- / Footer -->








            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    @stack('modals')

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <!-- jQuery UI (for sortable) -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <!-- DataTables (CDN) -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <!-- Advanced Toast Notifications -->
    <script src="{{ asset('assets/js/advanced-toast.js') }}"></script>

    <!-- Vite JS -->
    @vite(['resources/js/app.js'])

    <!-- Advertisement Pop-up Modal -->
    <div class="modal fade" id="advertisementModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border: none; border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 pb-0" id="advertisementModalHeader" style="padding: 1.5rem 1.5rem 0.5rem;">
                    <div class="d-flex align-items-center justify-content-center w-100 position-relative">
                        <div class="text-center">
                            <h4 class="modal-title mb-1 fw-bold text-white" id="advertisementModalTitle" style="font-size: 1.25rem;">
                                <i class="bx bx-bullhorn me-2"></i>Announcement
                            </h4>
                            <small class="text-white-50" id="advertisementDate" style="opacity: 0.9;"></small>
                        </div>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0" data-bs-dismiss="modal" aria-label="Close" id="modalCloseBtn" style="display: none;"></button>
                    </div>
                </div>
                <div class="modal-body" id="advertisementModalBody" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                    <div id="advertisementContent" class="mb-4" style="
                        background: #f8f9fa;
                        border-radius: 8px;
                        padding: 1.5rem;
                        border: 1px solid #e9ecef;
                    ">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attachments Section -->
                    <div id="advertisementAttachments" class="mb-3" style="display: none;">
                        <h6 class="mb-2 fw-semibold">
                            <i class="bx bx-paperclip me-1"></i>Attachments
                        </h6>
                        <div id="attachmentsList" class="row g-2"></div>
                    </div>
                    
                    <!-- Comment Section -->
                    <div id="advertisementCommentSection" class="mt-4 pt-3 border-top">
                        <label for="advertisementComment" class="form-label fw-semibold mb-2">
                            <i class="bx bx-comment-dots me-1 text-primary"></i>Your Feedback (Optional)
                        </label>
                        <textarea class="form-control" id="advertisementComment" rows="4" 
                                  placeholder="Share your thoughts, questions, or feedback about this announcement..."></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>Your comments help us improve our communications
                            </small>
                            <small class="text-muted" id="commentCharCount">0 / 500</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" id="advertisementModalFooter" style="padding: 0 1.5rem 1.5rem;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="closeBtn" style="display: none;">
                        <i class="bx bx-x me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary btn-lg px-4" id="acknowledgeBtn" onclick="acknowledgeAdvertisement()" style="display: none;">
                        <i class="bx bx-check-circle me-2"></i>Mark as Read
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <!-- Advertisement Pop-up Script -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
    let currentAdvertisementId = null;
    let advertisementQueue = [];
    let isProcessingAdvertisements = false;

    // Check for unacknowledged advertisements on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for page to fully load
        setTimeout(() => {
            checkForAdvertisements();
        }, 1000);
    });

    function checkForAdvertisements() {
        if (isProcessingAdvertisements) return;
        
        isProcessingAdvertisements = true;
        
        fetch('{{ route("advertisements.unacknowledged") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            isProcessingAdvertisements = false;
            
            if (data.success && data.advertisements && data.advertisements.length > 0) {
                advertisementQueue = data.advertisements;
                showNextAdvertisement();
            }
        })
        .catch(error => {
            isProcessingAdvertisements = false;
            console.error('Error checking advertisements:', error);
        });
    }

    function showNextAdvertisement() {
        if (advertisementQueue.length === 0) return;
        
        const advertisement = advertisementQueue.shift();
        currentAdvertisementId = advertisement.id;
        
        // Set modal header color based on priority
        const header = document.getElementById('advertisementModalHeader');
        const priorityClass = advertisement.priority === 'urgent' ? 'bg-danger text-white' : 
                             advertisement.priority === 'important' ? 'bg-warning text-dark' : 
                             'bg-info text-white';
        header.className = 'modal-header ' + priorityClass;
        
        // Set title
        document.getElementById('advertisementModalTitle').innerHTML = 
            `<i class="bx bx-bullhorn me-2"></i>${advertisement.title}`;
        
        // Set advertisement content in the content div with proper formatting
        const contentDiv = document.getElementById('advertisementContent');
        if (contentDiv) {
            // Format content with proper paragraph breaks and advanced styling
            let formattedContent = advertisement.content || '';
            
            // If content doesn't have HTML tags, format it properly
            if (!formattedContent.includes('<p>') && !formattedContent.includes('<div>') && !formattedContent.includes('<h')) {
                // Split by double line breaks for paragraphs
                let paragraphs = formattedContent.split(/\n\n+/).filter(p => p.trim().length > 0);
                
                // If no double breaks, try splitting by single breaks but group related lines
                if (paragraphs.length <= 1) {
                    const lines = formattedContent.split(/\n/).filter(p => p.trim().length > 0);
                    paragraphs = [];
                    let currentPara = '';
                    
                    lines.forEach((line, index) => {
                        line = line.trim();
                        // Check if line is a heading indicator
                        if (line.match(/^[📖🔹•▪▫]\s/) || line.match(/^[A-Z][^.!?]*:$/)) {
                            if (currentPara) {
                                paragraphs.push(currentPara);
                                currentPara = '';
                            }
                            paragraphs.push(line);
                        } else if (line.length > 0) {
                            if (currentPara) {
                                currentPara += ' ' + line;
                            } else {
                                currentPara = line;
                            }
                            // Start new paragraph if line ends with period or is long
                            if (line.match(/[.!?]$/) || index === lines.length - 1) {
                                paragraphs.push(currentPara);
                                currentPara = '';
                            }
                        }
                    });
                    if (currentPara) paragraphs.push(currentPara);
                }
                
                // Wrap each paragraph with proper formatting
                formattedContent = paragraphs.map(p => {
                    p = p.trim();
                    if (!p) return '';
                    
                    // Handle special markers
                    if (p.match(/^[📖🔹•▪▫]\s/)) {
                        const text = p.replace(/^[📖🔹•▪▫]\s*/, '');
                        return `<p class="fw-semibold text-primary mb-2"><i class="bx bx-info-circle me-1"></i>${text}</p>`;
                    }
                    // Handle headings (short lines or lines ending with colon)
                    if (p.match(/^[A-Z][^.!?]*:$/) && p.length < 100) {
                        return `<h5 class="fw-bold text-dark mb-3 mt-4">${p.replace(':', '')}</h5>`;
                    }
                    // Regular paragraph
                    return `<p class="mb-3">${p}</p>`;
                }).filter(p => p).join('');
            } else {
                // Content already has HTML, ensure proper spacing
                formattedContent = formattedContent
                    .replace(/(<\/p>)\s*(<p>)/g, '$1$2')
                    .replace(/(<\/h[1-6]>)\s*(<p>)/g, '$1$2');
            }
            
            contentDiv.innerHTML = `
                <div class="advertisement-content">
                    ${formattedContent}
                </div>
                <style>
                    #advertisementContent .advertisement-content {
                        line-height: 1.9;
                        font-size: 1.05rem;
                        color: #495057;
                    }
                    #advertisementContent .advertisement-content p {
                        margin-bottom: 1.2rem;
                        text-align: justify;
                        word-wrap: break-word;
                        text-indent: 0;
                        line-height: 1.9;
                    }
                    #advertisementContent .advertisement-content p:first-child {
                        margin-top: 0;
                    }
                    #advertisementContent .advertisement-content p:last-child {
                        margin-bottom: 0;
                    }
                    #advertisementContent .advertisement-content p.fw-semibold {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 0.75rem 1rem;
                        border-radius: 6px;
                        margin: 1rem 0;
                        text-align: left;
                    }
                    #advertisementContent .advertisement-content h1, 
                    #advertisementContent .advertisement-content h2, 
                    #advertisementContent .advertisement-content h3, 
                    #advertisementContent .advertisement-content h4, 
                    #advertisementContent .advertisement-content h5, 
                    #advertisementContent .advertisement-content h6 {
                        margin-top: 1.5rem;
                        margin-bottom: 1rem;
                        font-weight: 600;
                        color: #212529;
                        line-height: 1.4;
                    }
                    #advertisementContent .advertisement-content h1:first-child,
                    #advertisementContent .advertisement-content h2:first-child,
                    #advertisementContent .advertisement-content h3:first-child,
                    #advertisementContent .advertisement-content h4:first-child,
                    #advertisementContent .advertisement-content h5:first-child {
                        margin-top: 0;
                    }
                    #advertisementContent .advertisement-content ul, 
                    #advertisementContent .advertisement-content ol {
                        margin-bottom: 1.2rem;
                        padding-left: 2rem;
                        margin-top: 0.5rem;
                    }
                    #advertisementContent .advertisement-content li {
                        margin-bottom: 0.6rem;
                        line-height: 1.7;
                    }
                    #advertisementContent .advertisement-content strong {
                        font-weight: 600;
                        color: #212529;
                    }
                    #advertisementContent .advertisement-content em {
                        font-style: italic;
                    }
                    #advertisementContent .advertisement-content blockquote {
                        border-left: 4px solid #0d6efd;
                        padding: 1rem 1rem 1rem 1.5rem;
                        margin: 1.5rem 0;
                        font-style: italic;
                        color: #6c757d;
                        background: #f8f9fa;
                        border-radius: 4px;
                    }
                    #advertisementContent .advertisement-content img {
                        max-width: 100%;
                        height: auto;
                        border-radius: 8px;
                        margin: 1rem 0;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }
                    #advertisementContent .advertisement-content a {
                        color: #0d6efd;
                        text-decoration: none;
                        font-weight: 500;
                    }
                    #advertisementContent .advertisement-content a:hover {
                        text-decoration: underline;
                    }
                </style>
            `;
        }
        
        // Handle attachments
        const attachmentsSection = document.getElementById('advertisementAttachments');
        const attachmentsList = document.getElementById('attachmentsList');
        if (advertisement.attachments && advertisement.attachments.length > 0) {
            attachmentsList.innerHTML = '';
            advertisement.attachments.forEach((att, index) => {
                const fileType = att.type || 'file';
                const icon = fileType.includes('image') ? 'bx-image' : 
                           fileType.includes('pdf') ? 'bx-file-blank' : 'bx-file';
                const badgeColor = fileType.includes('image') ? 'bg-primary' : 
                                 fileType.includes('pdf') ? 'bg-danger' : 'bg-secondary';
                
                attachmentsList.innerHTML += `
                    <div class="col-md-6">
                        <div class="card border h-100" style="transition: all 0.3s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="badge ${badgeColor} rounded-circle p-2">
                                            <i class="bx ${icon} fs-5"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-truncate" style="max-width: 200px;" title="${att.name}">
                                            ${att.name}
                                        </h6>
                                        <small class="text-muted">${fileType}</small>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <a href="${att.url || '#'}" target="_blank" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            attachmentsSection.style.display = 'block';
        } else {
            attachmentsSection.style.display = 'none';
        }
        
        // Show comment section and clear previous comment
        const commentSection = document.getElementById('advertisementCommentSection');
        const commentField = document.getElementById('advertisementComment');
        if (commentSection) {
            commentSection.style.display = 'block';
        }
        if (commentField) {
            commentField.value = ''; // Clear previous comments
            updateCommentCharCount();
            // Add character counter event listener if not already added
            commentField.removeEventListener('input', updateCommentCharCount);
            commentField.addEventListener('input', updateCommentCharCount);
            commentField.setAttribute('maxlength', '500');
        }
        
        // Show/hide acknowledge button and close button
        const acknowledgeBtn = document.getElementById('acknowledgeBtn');
        const closeBtn = document.getElementById('closeBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        
        if (advertisement.require_acknowledgment) {
            acknowledgeBtn.style.display = 'inline-block';
            if (closeBtn) closeBtn.style.display = 'none';
            if (modalCloseBtn) modalCloseBtn.style.display = 'none'; // Prevent closing without acknowledgment
        } else {
            acknowledgeBtn.style.display = 'none';
            if (closeBtn) closeBtn.style.display = 'inline-block';
            if (modalCloseBtn) modalCloseBtn.style.display = 'block';
        }
        
        // Update date if available
        const dateElement = document.getElementById('advertisementDate');
        if (dateElement) {
            dateElement.textContent = new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('advertisementModal'));
        modal.show();
        
        // Handle modal close - show next if available
        const modalElement = document.getElementById('advertisementModal');
        modalElement.addEventListener('hidden.bs.modal', function onModalHidden() {
            modalElement.removeEventListener('hidden.bs.modal', onModalHidden);
            // Show next advertisement if available
            if (advertisementQueue.length > 0) {
                setTimeout(() => showNextAdvertisement(), 500);
            }
        }, { once: true });
    }

    function updateCommentCharCount() {
        const commentField = document.getElementById('advertisementComment');
        const charCount = document.getElementById('commentCharCount');
        if (commentField && charCount) {
            const length = commentField.value.length;
            charCount.textContent = length + ' / 500';
            if (length > 450) {
                charCount.classList.add('text-warning');
                charCount.classList.remove('text-muted');
            } else {
                charCount.classList.remove('text-warning');
                charCount.classList.add('text-muted');
            }
        }
    }
    
    function previewAttachment(url, type, name) {
        if (type.includes('image')) {
            window.open(url, '_blank');
        } else if (type.includes('pdf')) {
            window.open(url, '_blank');
        } else {
            window.open(url, '_blank');
        }
    }
    
    function acknowledgeAdvertisement() {
        if (!currentAdvertisementId) return;
        
        const btn = document.getElementById('acknowledgeBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader bx-spin me-2"></i>Processing...';
        
        // Get comment if provided
        const commentField = document.getElementById('advertisementComment');
        const comment = commentField ? commentField.value.trim() : '';
        
        // Build the acknowledge URL
        const acknowledgeUrl = '{{ url("/advertisements") }}/' + currentAdvertisementId + '/acknowledge';
        fetch(acknowledgeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                notes: comment || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('advertisementModal'));
                modal.hide();
                
                // Show next advertisement if available
                if (advertisementQueue.length > 0) {
                    setTimeout(() => showNextAdvertisement(), 500);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to acknowledge advertisement',
                    confirmButtonColor: '#d33'
                });
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            console.error('Error acknowledging advertisement:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing your acknowledgment. Please try again.',
                confirmButtonColor: '#d33'
            });
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }
    </script>
    <script>
    (function(){
      const notifCount = document.getElementById('notifCount');
      const notifList = document.getElementById('notifList');
      async function loadNotifDropdown(force){
        try{
          const res = await fetch('{{ route('notifications.dropdown') }}', { headers: {'X-Requested-With':'XMLHttpRequest'} });
          if(!res.ok) return;
          const data = await res.json();
          if(data.success){
            notifCount.style.display = data.count_unread>0 ? 'inline-block':'none';
            notifCount.textContent = data.count_unread;
            if(force || notifList.innerHTML.trim()==='' || notifList.innerText.includes('Loading')){
              notifList.innerHTML = (data.items||[]).map(function(n){
                const readClass = n.is_read ? '' : 'fw-bold';
                const href = n.link ? n.link : '#';
                const shortMsg = truncate(String(n.message||'').trim(), 45);
                // Add click handler to mark as read and handle navigation properly
                const onClick = href !== '#' ? `onclick="handleNotificationClick(event, ${n.id}, '${href}')"` : '';
                return `<a class="dropdown-item ${readClass} py-2 px-2" href="${href}" ${onClick} style="font-size:0.875rem;line-height:1.3;cursor:pointer;"><div>${escapeHtml(shortMsg)}</div></a>`;
              }).join('') || '<div class="p-3 text-muted text-center" style="font-size:0.875rem;">No notifications</div>';
            }
          }
        }catch(e){/* silent */}
      }
      function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

      // Advanced Toast for notifications with clickable action
      function showNotificationToast(notification){
        if(!notification || !notification.message) return;
        
        // Use AdvancedToast if available, otherwise fallback to simple alert
        if(typeof window.AdvancedToast !== 'undefined' && window.AdvancedToast){
          const actions = [];
          
          // Add "View" action button if link is available
          if(notification.link && notification.link !== '#'){
            actions.push({
              label: 'View',
              name: 'view',
              class: 'primary',
              callback: function(){
                // Mark as shown in session (frontend)
                if(notification.id){
                  markNotificationAsShown(notification.id);
                  // Also mark as shown in backend session
                  fetch('{{ route('notifications.mark-shown') }}', {
                    method: 'POST',
                    headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                      'X-Requested-With': 'XMLHttpRequest',
                      'Accept': 'application/json',
                      'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ notification_id: notification.id })
                  }).catch(() => {});
                }
                // Navigate to the link
                window.location.href = notification.link;
              },
              dismiss: true
            });
          }
          
          // Show notification with AdvancedToast
          window.AdvancedToast.info(
            'New Notification',
            notification.message,
            {
              duration: 8000,
              sound: true,
              actions: actions.length > 0 ? actions : undefined,
              onClose: function(){
                // Refresh notification dropdown after toast closes
                setTimeout(function(){ loadNotifDropdown(false); }, 500);
              }
            }
          );
        } else {
          // Fallback to simple alert if AdvancedToast is not available
          const text = truncate(String(notification.message||'').replace(/\s+/g,' ').trim(), 50);
          alert('Notification: ' + text);
        }
      }
      
      function truncate(s, n){ return (s && s.length>n) ? (s.slice(0, n-1)+'…') : s; }
      function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

      // Track shown notifications per login session (using sessionStorage)
      const NOTIFICATION_SESSION_KEY = 'ofisilink_notifications_session';
      const LOGIN_SESSION_KEY = 'ofisilink_login_time';
      let popupShownThisLogin = false;
      
      // Initialize login time if not set (first time this session)
      function initializeLoginSession(){
        try {
          const loginTime = sessionStorage.getItem(LOGIN_SESSION_KEY);
          if(!loginTime){
            // Set login time to current time
            sessionStorage.setItem(LOGIN_SESSION_KEY, Date.now().toString());
            popupShownThisLogin = false;
          } else {
            // Check if we've already shown popup this login
            const shownIds = getShownNotificationIds();
            popupShownThisLogin = shownIds.length > 0;
          }
        } catch(e){
          popupShownThisLogin = false;
        }
      }
      
      function getShownNotificationIds(){
        try {
          const stored = sessionStorage.getItem(NOTIFICATION_SESSION_KEY);
          if(stored){
            const data = JSON.parse(stored);
            return data.shownIds || [];
          }
        } catch(e){}
        return [];
      }
      
      function markNotificationAsShown(notificationId){
        try {
          const stored = sessionStorage.getItem(NOTIFICATION_SESSION_KEY);
          let data = { shownIds: [] };
          if(stored){
            data = JSON.parse(stored);
          }
          if(!data.shownIds){
            data.shownIds = [];
          }
          if(!data.shownIds.includes(notificationId)){
            data.shownIds.push(notificationId);
            sessionStorage.setItem(NOTIFICATION_SESSION_KEY, JSON.stringify(data));
          }
        } catch(e){}
      }
      
      // Show popup once per login for unread notifications
      async function showLoginNotificationsOnce(){
        // Only show once per login session
        if(popupShownThisLogin){
          return;
        }
        
        try {
          const res = await fetch('{{ route('notifications.unread') }}', { headers: {'X-Requested-With':'XMLHttpRequest'} });
          if(!res.ok) return;
          const data = await res.json();
          if(data && data.success && Array.isArray(data.notifications) && data.notifications.length > 0){
            const shownIds = getShownNotificationIds();
            
            // Filter out already shown notifications and login notifications
            const newNotifications = data.notifications.filter(function(n){
              // Skip if already shown
              if(shownIds.includes(n.id)){
                return false;
              }
              // Skip login notification - it's handled separately and only shown once
              if(n.message && n.message.includes('successfully logged into')){
                return false;
              }
              return true;
            });
            
            // Show popup for first unread notification if any (excluding login notification)
            if(newNotifications.length > 0){
              const firstNotification = newNotifications[0];
              showNotificationToast(firstNotification);
              markNotificationAsShown(firstNotification.id);
              popupShownThisLogin = true;
              
              // Also mark as shown in backend session
              fetch('{{ route('notifications.mark-shown') }}', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json',
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notification_id: firstNotification.id })
              }).catch(() => {});
            }
          }
        } catch (e) {
          // silent
        }
      }
      
      // Handle login notification separately - show only once per login from session (NO DB entry)
      function showLoginNotificationOnce(){
        const LOGIN_NOTIF_KEY = 'login_notification_shown';
        if(sessionStorage.getItem(LOGIN_NOTIF_KEY)){
          return; // Already shown this login
        }
        
        // Check if login notification message exists in server session
        @if(session('login_notification_message'))
          const loginMessage = '{{ session('login_notification_message') }}';
          if(loginMessage){
            // Show login notification as toast (no DB entry, just session-based)
            const loginNotification = {
              id: 'login_' + Date.now(),
              message: loginMessage,
              link: '{{ route('dashboard') }}',
              time: 'Just now'
            };
            
            // Show the notification toast
            showNotificationToast(loginNotification);
            
            // Mark as shown in sessionStorage to prevent showing again
            sessionStorage.setItem(LOGIN_NOTIF_KEY, 'true');
            
            // Clear the session data so it won't show on refresh
            fetch('{{ route('notifications.clear-login-notification') }}', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              }
            }).catch(() => {});
          }
        @endif
      }
      
      async function pollNotifications(){
        try {
          const res = await fetch('{{ route('notifications.unread') }}', { headers: {'X-Requested-With':'XMLHttpRequest'} });
          if(!res.ok) return;
          const data = await res.json();
          if(data && data.success){
            // Update bell count (only current day notifications)
            if(typeof data.count === 'number'){
              notifCount.style.display = data.count>0 ? 'inline-block':'none';
              notifCount.textContent = data.count;
              // Refresh dropdown to show new notifications
              loadNotifDropdown(false);
            }
          }
        } catch (e) {
          // silent
        }
      }

      // Handle notification click - mark as read and navigate
      window.handleNotificationClick = function(event, notifId, href) {
        event.preventDefault();
        
        // Mark notification as read via AJAX (if not already read)
        if (notifId) {
          fetch(`{{ url('notifications') }}/${notifId}/read`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          }).catch(() => {}); // Silent fail - don't block navigation
        }
        
        // Navigate to the link
        if (href && href !== '#') {
          window.location.href = href;
        }
      };
      
      // Initialize login session tracking
      initializeLoginSession();
      
      // Initial load and show popup once per login
      setTimeout(function(){ 
        pollNotifications(); 
        loadNotifDropdown(true);
        // Show login notification once per login (separate from other notifications)
        showLoginNotificationOnce();
        // Show popup once per login for other unread notifications (excluding login)
        showLoginNotificationsOnce();
      }, 1500);
      
      // Poll every 20 seconds to check for new notifications (count only, no popup)
      setInterval(pollNotifications, 20000);
      
      document.getElementById('notifBell').addEventListener('click', function(){ loadNotifDropdown(true); });
    })();

    // Dropdown hover functionality
    (function() {
      document.addEventListener('DOMContentLoaded', function() {
        // Enable hover for dropdowns with dropdown-hover class
        const hoverDropdowns = document.querySelectorAll('.dropdown-hover');
        hoverDropdowns.forEach(function(dropdown) {
          const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
          const menu = dropdown.querySelector('.dropdown-menu');
          
          if (toggle && menu) {
            let hoverTimeout;
            
            dropdown.addEventListener('mouseenter', function() {
              clearTimeout(hoverTimeout);
              const bsDropdown = bootstrap.Dropdown.getInstance(toggle) || new bootstrap.Dropdown(toggle);
              bsDropdown.show();
            });
            
            dropdown.addEventListener('mouseleave', function() {
              hoverTimeout = setTimeout(function() {
                const bsDropdown = bootstrap.Dropdown.getInstance(toggle);
                if (bsDropdown) {
                  bsDropdown.hide();
                }
              }, 200);
            });
          }
        });
      });
    })();

    // Auto-logout on idle with 30-second warning
    (function() {
      @php
        $sessionTimeoutMinutes = \App\Models\SystemSetting::getValue('session_timeout_minutes', 120);
        // Ensure minimum of 2 minutes and maximum of 1440 minutes (24 hours) for timeout
        $sessionTimeoutMinutes = max(2, min(1440, (int) $sessionTimeoutMinutes));
      @endphp
      let idleTimer;
      let warningTimer;
      let countdownInterval;
      const IDLE_TIMEOUT = {{ $sessionTimeoutMinutes }} * 60 * 1000; // Session timeout in milliseconds from system settings
      const WARNING_TIME = 30 * 1000; // 30 seconds warning before logout
      const WARNING_TIMEOUT = Math.max(0, IDLE_TIMEOUT - WARNING_TIME); // Show warning 30 seconds before logout (ensure non-negative)
      
      // Debug: Log timeout values (remove in production)
      console.log('Session timeout configured:', {
        minutes: {{ $sessionTimeoutMinutes }},
        idleTimeout: IDLE_TIMEOUT,
        warningTimeout: WARNING_TIMEOUT
      });
      const logoutUrl = '{{ route("logout") }}';
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
      let warningModal = null;
      let countdownSeconds = 30;

      // Create warning modal HTML
      function createWarningModal() {
        if (document.getElementById('idleWarningModal')) {
          return; // Modal already exists
        }
        
        const modalHTML = `
          <div class="modal fade" id="idleWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 99999;">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark">
                  <h5 class="modal-title">
                    <i class="bx bx-time-five me-2"></i>Session Timeout Warning
                  </h5>
                </div>
                <div class="modal-body text-center py-4">
                  <div class="mb-3">
                    <i class="bx bx-time fs-1 text-warning"></i>
                  </div>
                  <h6 class="mb-3">System Idle Detected</h6>
                  <p class="text-muted mb-3">
                    Your session has been idle for a while. The system will automatically log you out due to inactivity.
                  </p>
                  <div class="alert alert-warning mb-3">
                    <strong>Auto-logout in: <span id="countdownTimer" class="text-danger fs-4">30</span> seconds</strong>
                  </div>
                  <p class="text-muted small">
                    Click "Continue" below if you want to stay logged in.
                  </p>
                </div>
                <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn-primary btn-lg" id="continueSessionBtn">
                    <i class="bx bx-check-circle me-2"></i>Continue Session
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modalElement = document.getElementById('idleWarningModal');
        warningModal = new bootstrap.Modal(modalElement, {
          backdrop: 'static',
          keyboard: false
        });
        
        // Set up continue button handler
        const continueBtn = document.getElementById('continueSessionBtn');
        if (continueBtn) {
          continueBtn.addEventListener('click', function() {
            resetIdleTimer();
          });
        }
      }

      // Show warning modal
      function showWarningModal() {
        createWarningModal();
        countdownSeconds = 30;
        document.getElementById('countdownTimer').textContent = countdownSeconds;
        
        if (warningModal) {
          warningModal.show();
        }
        
        // Start countdown
        countdownInterval = setInterval(function() {
          countdownSeconds--;
          const countdownEl = document.getElementById('countdownTimer');
          if (countdownEl) {
            countdownEl.textContent = countdownSeconds;
          }
          
          if (countdownSeconds <= 0) {
            clearInterval(countdownInterval);
            // Auto-logout
            performLogout();
          }
        }, 1000);
      }

      // Hide warning modal
      function hideWarningModal() {
        if (countdownInterval) {
          clearInterval(countdownInterval);
          countdownInterval = null;
        }
        if (warningModal) {
          warningModal.hide();
        }
        countdownSeconds = 30;
      }

      // Perform logout
      function performLogout() {
        hideWarningModal();
        
        // Use relative URL to avoid port issues
        const form = document.createElement('form');
        form.method = 'POST';
        // Extract path from logoutUrl to avoid absolute URL issues
        const url = new URL(logoutUrl, window.location.origin);
        form.action = url.pathname;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
      }

      function resetIdleTimer() {
        // Clear existing timers
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        hideWarningModal();
        
      // Set warning timer (30 seconds before logout) - only if there's enough time
      if (WARNING_TIMEOUT > 0 && IDLE_TIMEOUT > WARNING_TIME) {
        warningTimer = setTimeout(function() {
          showWarningModal();
        }, WARNING_TIMEOUT);
      }
      
      // Set logout timer
      idleTimer = setTimeout(function() {
        performLogout();
      }, IDLE_TIMEOUT);
      }


      // Events that indicate user activity
      const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
      
      activityEvents.forEach(function(eventName) {
        document.addEventListener(eventName, resetIdleTimer, true);
      });

      // Initialize timer on page load
      resetIdleTimer();
    })();

    // Global AJAX error handler for session expiration
    (function() {
      const loginUrl = '{{ route("login") }}';
      
      // Handle jQuery AJAX errors
      if (typeof jQuery !== 'undefined') {
        $(document).ajaxError(function(event, xhr, settings) {
          // Check for session expiration errors
          if (xhr.status === 401 || xhr.status === 419 || xhr.status === 403) {
            // Don't redirect if already on login page
            if (window.location.pathname.includes('/login')) {
              return;
            }
            
            // Clear any existing session data
            sessionStorage.clear();
            localStorage.removeItem('session_data');
            
            // Redirect to login page
            window.location.href = loginUrl;
          }
        });
      }
      
      // Handle Fetch API errors globally
      const originalFetch = window.fetch;
      window.fetch = function(...args) {
        return originalFetch.apply(this, args)
          .then(response => {
            // Check for session expiration errors
            if (response.status === 401 || response.status === 419 || response.status === 403) {
              // Don't redirect if already on login page
              if (window.location.pathname.includes('/login')) {
                return response;
              }
              
              // Clear any existing session data
              sessionStorage.clear();
              localStorage.removeItem('session_data');
              
              // Redirect to login page
              window.location.href = loginUrl;
              return response;
            }
            return response;
          })
          .catch(error => {
            // Network errors or other fetch errors
            throw error;
          });
      };
      
      // Handle XMLHttpRequest errors
      const originalOpen = XMLHttpRequest.prototype.open;
      const originalSend = XMLHttpRequest.prototype.send;
      
      XMLHttpRequest.prototype.open = function(method, url, ...rest) {
        this._url = url;
        return originalOpen.apply(this, [method, url, ...rest]);
      };
      
      XMLHttpRequest.prototype.send = function(...args) {
        this.addEventListener('loadend', function() {
          if (this.status === 401 || this.status === 419 || this.status === 403) {
            // Don't redirect if already on login page
            if (window.location.pathname.includes('/login')) {
              return;
            }
            
            // Clear any existing session data
            sessionStorage.clear();
            localStorage.removeItem('session_data');
            
            // Redirect to login page
            window.location.href = loginUrl;
          }
        });
        
        return originalSend.apply(this, args);
      };
    })();
    
    // Auto-show Laravel flash messages with Advanced Toast
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            window.AdvancedToast.success('Success', '{{ session('success') }}', { duration: 5000, sound: true });
        @endif
        
        @if(session('error'))
            window.AdvancedToast.error('Error', '{{ session('error') }}', { duration: 7000, sound: true });
        @endif
        
        @if(session('warning'))
            window.AdvancedToast.warning('Warning', '{{ session('warning') }}', { duration: 6000, sound: true });
        @endif
        
        @if(session('info'))
            window.AdvancedToast.info('Information', '{{ session('info') }}', { duration: 5000, sound: true });
        @endif
        
        @if(session('message') && !session('success') && !session('error') && !session('warning') && !session('info'))
            window.AdvancedToast.info('Notification', '{{ session('message') }}', { duration: 5000 });
        @endif
    });
    </script>
  </body>
</html>