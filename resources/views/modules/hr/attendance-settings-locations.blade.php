@extends('layouts.app')

@section('title', 'Attendance Locations')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-map"></i> Attendance Locations
                </h4>
                <p class="text-muted">Manage physical office locations and GPS boundaries</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
                <button type="button" class="btn btn-primary" onclick="openLocationModal()">
                    <i class="bx bx-plus me-1"></i> Add Location
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .border-left-warning { border-left: 4px solid #ffc107; }
    .border-left-primary { border-left: 4px solid #007bff; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Locations</h6>
                            <h3 class="mb-0" id="statTotalLocations">{{ $stats['total_locations'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-map fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Locations</h6>
                            <h3 class="mb-0 text-success" id="statActiveLocations">{{ $stats['active_locations'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Locations Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-list-ul me-1"></i> Locations List
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="locationsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Address</th>
                                    <th>GPS Radius</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="locationsList">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Location Modal -->
@include('modules.hr.attendance-settings.modals.location-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const locationsData = @json($locations ?? []);

document.addEventListener('DOMContentLoaded', function() {
    loadLocations();
});

function loadLocations() {
    const tbody = document.getElementById('locationsList');
    if (!tbody) return;

    if (!locationsData || locationsData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bx bx-map fs-1"></i><p class="mt-2">No locations found</p></td></tr>';
        return;
    }
    
    tbody.innerHTML = locationsData.map(loc => `
        <tr>
            <td><strong>${loc.name || 'N/A'}</strong></td>
            <td><code>${loc.code || 'N/A'}</code></td>
            <td>${loc.address || 'N/A'}</td>
            <td>${loc.radius_meters || 100}m</td>
            <td>${loc.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editLocation(${loc.id})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteLocation(${loc.id}, '${loc.name || ''}')" title="Delete">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openLocationModal(locationId = null) {
    const modalTitle = document.getElementById('locationModalTitle');
    const form = document.getElementById('locationForm');
    const idInput = document.getElementById('locationId');
    const saveBtn = document.getElementById('saveLocationBtn');
    
    // Reset form
    form.reset();
    idInput.value = '';
    
    // Set default values if new
    if (!locationId) {
        modalTitle.textContent = 'Add New Location';
        saveBtn.innerHTML = '<i class="bx bx-save"></i> Save Location';
        document.getElementById('locIsActive').checked = true;
        document.getElementById('locRadius').value = 100;
    } else {
        // Edit mode
        modalTitle.textContent = 'Edit Location';
        saveBtn.innerHTML = '<i class="bx bx-save"></i> Update Location';
        
        // Find location data
        const location = locationsData.find(l => l.id == locationId);
        if (location) {
            idInput.value = location.id;
            document.getElementById('locName').value = location.name || '';
            document.getElementById('locCode').value = location.code || '';
            document.getElementById('locDescription').value = location.description || '';
            document.getElementById('locAddress').value = location.address || '';
            document.getElementById('locCity').value = location.city || '';
            document.getElementById('locState').value = location.state || '';
            document.getElementById('locCountry').value = location.country || '';
            document.getElementById('locPostalCode').value = location.postal_code || '';
            document.getElementById('locLatitude').value = location.latitude || '';
            document.getElementById('locLongitude').value = location.longitude || '';
            document.getElementById('locRadius').value = location.radius_meters || 100;
            
            document.getElementById('locIsActive').checked = location.is_active ? true : false;
            document.getElementById('locRequireGps').checked = location.require_gps ? true : false;
            document.getElementById('locAllowRemote').checked = location.allow_remote ? true : false;
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('locationModal'));
    modal.show();
}

function submitLocation(event) {
    event.preventDefault();
    
    const form = document.getElementById('locationForm');
    const formData = new FormData(form);
    const locationId = document.getElementById('locationId').value;
    const isEdit = locationId ? true : false;
    
    // Convert checkbox values
    formData.set('is_active', document.getElementById('locIsActive').checked ? 1 : 0);
    formData.set('require_gps', document.getElementById('locRequireGps').checked ? 1 : 0);
    formData.set('allow_remote', document.getElementById('locAllowRemote').checked ? 1 : 0);

    const url = isEdit 
        ? `/attendance-settings/locations/${locationId}`
        : `/attendance-settings/locations`;
        
    const method = isEdit ? 'POST' : 'POST'; // Using POST for both, but will add _method for PUT if edit
    
    if (isEdit) {
        formData.append('_method', 'PUT');
    }
    
    // Add CSRF token
    formData.append('_token', csrfToken);

    // Show loading state
    const saveBtn = document.getElementById('saveLocationBtn');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';

    fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
        
        if (data.success) {
            // Close modal
            const modalEl = document.getElementById('locationModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
            
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            
            // Reload page to reflect changes properly
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            console.error('Error data:', data);
            let errorMsg = data.message || 'An error occurred';
            
            if (data.errors) {
                const errors = Object.values(data.errors).flat();
                if (errors.length > 0) {
                    errorMsg = errors.join('<br>');
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMsg
            });
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
        
        Swal.fire({
            icon: 'error',
            title: 'System Error',
            text: 'Failed to connect to the server. Please try again.'
        });
    });
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        // Show loading toast or indicator
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Getting...';
        btn.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('locLatitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('locLongitude').value = position.coords.longitude.toFixed(6);
                
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Location Found',
                    text: `Lat: ${position.coords.latitude.toFixed(4)}, Long: ${position.coords.longitude.toFixed(4)}`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            },
            (error) => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                let msg = "Unknown error";
                switch(error.code) {
                    case error.PERMISSION_DENIED: msg = "User denied the request for Geolocation."; break;
                    case error.POSITION_UNAVAILABLE: msg = "Location information is unavailable."; break;
                    case error.TIMEOUT: msg = "The request to get user location timed out."; break;
                }
                Swal.fire('Error', msg, 'error');
            }
        );
    } else {
        Swal.fire('Error', 'Geolocation is not supported by this browser.', 'error');
    }
}

function editLocation(id) {
    openLocationModal(id);
}

function deleteLocation(id, name) {
    Swal.fire({
        title: 'Delete Location',
        html: 'Are you sure you want to delete <strong>' + name + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', csrfToken);
            
            fetch('/attendance-settings/locations/' + id, {
                method: 'POST', // Laravel spoofing
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    // Reload to refresh list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to delete location', 'error');
            });
        }
    });
}
</script>
@endpush
