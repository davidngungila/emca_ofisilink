@extends('layouts.app')

@section('title', 'Attendance Devices')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-devices"></i> Biometric Devices
                </h4>
                <p class="text-muted">Manage and configure biometric attendance devices</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
                <a href="{{ route('attendance-settings.devices.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Device
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .device-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    .device-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .status-online {
        color: #28a745;
    }
    .status-offline {
        color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Devices</h6>
                            <h3 class="mb-0" id="statTotalDevices">{{ $stats['total_devices'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-devices fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Online Devices</h6>
                            <h3 class="mb-0 text-success" id="statOnlineDevices">{{ $stats['online_devices'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Offline Devices</h6>
                            <h3 class="mb-0 text-warning" id="statOfflineDevices">{{ ($stats['total_devices'] ?? 0) - ($stats['online_devices'] ?? 0) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-x-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bx bx-list-ul me-1"></i> Devices List
                    </h6>
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="refreshDeviceStatus()">
                            <i class="bx bx-refresh me-1"></i> Refresh Status
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="testAllDevices()">
                            <i class="bx bx-check-double me-1"></i> Test All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="devicesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Device ID</th>
                                    <th>IP Address</th>
                                    <th>Port</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Sync</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="devicesList">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
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

<!-- Device Modal -->
@include('modules.hr.attendance-settings.modals.device-modal')

<!-- View Device Details Modal -->
@include('modules.hr.attendance-settings.modals.view-device-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const devicesData = @json($devices ?? []);
const locations = @json($locations ?? []);
let currentDeviceId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Use devicesData if available (from server-side), otherwise fetch
    if (devicesData && devicesData.length >= 0) {
        displayDevices(devicesData);
    } else {
        loadDevices();
    }
    setupDeviceForm();
});

function displayDevices(devices) {
    const tbody = document.getElementById('devicesList');
    if (!tbody) return;

    if (!devices || devices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No devices found</p></td></tr>';
        return;
    }

    let html = '';
    devices.forEach(device => {
                const statusClass = device.is_online ? 'status-online' : 'status-offline';
                const statusIcon = device.is_online ? 'bx-check-circle' : 'bx-x-circle';
                const statusText = device.is_online ? 'Online' : 'Offline';
                const lastSync = device.last_sync_at ? new Date(device.last_sync_at).toLocaleString() : 'Never';
                
                const ipDisplay = device.is_online_mode && device.public_ip_address 
                    ? '<span title="Online Mode: ' + device.public_ip_address + '">' + device.public_ip_address + ' <i class="bx bx-globe text-info" title="Online Mode"></i></span>'
                    : (device.ip_address || 'N/A');
                
                html += '<tr>';
                html += '<td><strong>' + (device.name || 'N/A') + '</strong></td>';
                html += '<td><code>' + (device.device_id || 'N/A') + '</code></td>';
                html += '<td>' + ipDisplay + '</td>';
                html += '<td>' + (device.port || '4370') + '</td>';
                html += '<td>' + (device.location?.name || 'N/A') + '</td>';
                html += '<td><span class="' + statusClass + '"><i class="bx ' + statusIcon + ' me-1"></i>' + statusText + '</span></td>';
                html += '<td><small class="text-muted">' + lastSync + '</small></td>';
                html += '<td>';
                html += '<div class="btn-group" role="group">';
                html += '<button class="btn btn-sm btn-outline-info" onclick="viewDeviceDetails(' + device.id + ')" title="View More"><i class="bx bx-show"></i></button> ';
                html += '<a href="/modules/hr/attendance/settings/devices/' + device.id + '/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bx bx-edit"></i></a> ';
                html += '<button class="btn btn-sm btn-outline-warning" onclick="testDevice(' + device.id + ')" title="Test Connection"><i class="bx bx-wifi"></i></button> ';
                html += '<button class="btn btn-sm btn-outline-danger" onclick="deleteDevice(' + device.id + ', \'' + (device.name || '').replace(/'/g, "\\'") + '\')" title="Delete"><i class="bx bx-trash"></i></button>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });

    tbody.innerHTML = html;
}

function loadDevices() {
    const tbody = document.getElementById('devicesList');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tr>
    `;

    fetch('/attendance-settings/devices', {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Devices data:', data); // Debug log
        if (data.success && data.devices) {
            displayDevices(data.devices);
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bx bx-error-circle fs-1"></i><p class="mt-2">Failed to load devices</p></td></tr>';
        }
    })
    .catch(error => {
        console.error('Error loading devices:', error);
        let errorMessage = 'Failed to load devices';
        if (error.message) {
            errorMessage += ': ' + error.message;
        }
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bx bx-error-circle fs-1"></i><p class="mt-2">' + errorMessage + '</p><p class="text-muted small">Check browser console for details</p></td></tr>';
    });
}

function refreshDeviceStatus() {
    // Refresh device status logic
    loadDevices();
}

function testDevice(deviceId) {
    // Test device connection logic
    Swal.fire({
        title: 'Testing Connection...',
        text: 'Please wait while we test the device connection...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/attendance-settings/devices/' + deviceId + '/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success && data.is_online) {
            // Format message with device info
            let html = '<div style="text-align: left;">';
            html += '<p><strong>' + data.message.split('\n')[0] + '</strong></p>';
            if (data.device_info) {
                html += '<hr style="margin: 10px 0;">';
                html += '<p style="margin: 5px 0;"><strong>IP:</strong> ' + (data.device_info.ip || data.device?.ip_address || 'N/A') + '</p>';
                html += '<p style="margin: 5px 0;"><strong>Port:</strong> ' + (data.device?.port || '4370') + '</p>';
                if (data.device_info.model) {
                    html += '<p style="margin: 5px 0;"><strong>Model:</strong> ' + data.device_info.model + '</p>';
                }
                if (data.device_info.firmware) {
                    html += '<p style="margin: 5px 0;"><strong>Firmware:</strong> ' + data.device_info.firmware + '</p>';
                }
            }
            html += '</div>';
            
            Swal.fire({
                title: 'Success!',
                html: html,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            // Reload devices to update status
            loadDevices();
        } else if (data.success && !data.is_online) {
            // Format error message for better display
            let errorHtml = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';
            if (data.message) {
                // Replace newlines with HTML breaks
                errorHtml += '<pre style="white-space: pre-wrap; font-family: inherit; margin: 0;">' + data.message.replace(/\n/g, '<br>') + '</pre>';
            } else {
                errorHtml += '<p>Device is offline or unreachable</p>';
            }
            errorHtml += '</div>';
            
            Swal.fire({
                title: 'Device Offline',
                html: errorHtml,
                icon: 'warning',
                confirmButtonText: 'OK',
                width: '600px'
            });
            // Reload devices to update status
            loadDevices();
        } else {
            // Format error message for better display
            let errorHtml = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';
            if (data.message) {
                // Replace newlines with HTML breaks
                errorHtml += '<pre style="white-space: pre-wrap; font-family: inherit; margin: 0;">' + data.message.replace(/\n/g, '<br>') + '</pre>';
            } else {
                errorHtml += '<p>Failed to test device connection</p>';
            }
            errorHtml += '</div>';
            
            Swal.fire({
                title: 'Connection Error',
                html: errorHtml,
                icon: 'error',
                confirmButtonText: 'OK',
                width: '600px'
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Test device error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to test device connection. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

function testAllDevices() {
    // Test all devices logic
    Swal.fire('Info', 'Testing all devices...', 'info');
}

function editDevice(deviceId) {
    // Navigate to edit page
    window.location.href = '/modules/hr/attendance/settings/devices/' + deviceId + '/edit';
}

function deleteDevice(deviceId, deviceName) {
    Swal.fire({
        title: 'Delete Device',
        html: 'Are you sure you want to delete <strong>' + deviceName + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/attendance-settings/devices/' + deviceId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadDevices();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to delete device', 'error');
            });
        }
    });
}

function openDeviceModal(deviceId = null) {
    const modal = new bootstrap.Modal(document.getElementById('deviceModal'));
    const form = document.getElementById('deviceForm');
    const modalTitle = document.getElementById('deviceModalTitle');
    
    // Reset form
    form.reset();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    if (deviceId) {
        // Edit mode
        modalTitle.innerHTML = '<i class="bx bx-edit me-2"></i>Edit Device';
        currentDeviceId = deviceId;
        
        // Load device data
        fetch(`/attendance-settings/devices/${deviceId}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.device) {
                const device = data.device;
                document.getElementById('deviceId').value = device.id;
                document.getElementById('deviceName').value = device.name || '';
                document.getElementById('deviceDeviceId').value = device.device_id || '';
                document.getElementById('deviceType').value = device.device_type || 'biometric';
                document.getElementById('deviceLocation').value = device.location_id || '';
                document.getElementById('deviceManufacturer').value = device.manufacturer || '';
                document.getElementById('deviceModel').value = device.model || '';
                document.getElementById('deviceSerialNumber').value = device.serial_number || '';
                document.getElementById('deviceIpAddress').value = device.ip_address || '';
                document.getElementById('devicePort').value = device.port || '';
                document.getElementById('deviceMacAddress').value = device.mac_address || '';
                document.getElementById('deviceConnectionType').value = device.connection_type || 'network';
                document.getElementById('deviceSyncInterval').value = device.sync_interval_minutes || 5;
                document.getElementById('deviceConnectionConfig').value = device.connection_config ? JSON.stringify(device.connection_config, null, 2) : '';
                document.getElementById('deviceCapabilities').value = device.capabilities ? JSON.stringify(device.capabilities, null, 2) : '';
                document.getElementById('deviceSettings').value = device.settings ? JSON.stringify(device.settings, null, 2) : '';
                document.getElementById('deviceNotes').value = device.notes || '';
                document.getElementById('deviceIsActive').checked = device.is_active !== false;
                
                // Online mode fields
                const onlineModeToggle = document.getElementById('deviceIsOnlineMode');
                const onlineModeFields = document.getElementById('onlineModeFields');
                const publicIpField = document.getElementById('devicePublicIpAddress');
                if (onlineModeToggle && onlineModeFields && publicIpField) {
                    onlineModeToggle.checked = device.is_online_mode || false;
                    publicIpField.value = device.public_ip_address || '';
                    if (onlineModeToggle.checked) {
                        onlineModeFields.style.display = 'block';
                        publicIpField.setAttribute('required', 'required');
                        // Update port forwarding instructions
                        if (typeof updatePortForwardingInstructions === 'function') {
                            updatePortForwardingInstructions();
                        }
                    } else {
                        onlineModeFields.style.display = 'none';
                        publicIpField.removeAttribute('required');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading device:', error);
            Swal.fire('Error!', 'Failed to load device data', 'error');
        });
    } else {
        // Create mode
        modalTitle.innerHTML = '<i class="bx bx-plus me-2"></i>Add Device';
        currentDeviceId = null;
        document.getElementById('deviceId').value = '';
    }
    
    modal.show();
}

function setupDeviceForm() {
    const form = document.getElementById('deviceForm');
    if (!form) return;
    
    // Setup online mode toggle
    const onlineModeToggle = document.getElementById('deviceIsOnlineMode');
    const onlineModeFields = document.getElementById('onlineModeFields');
    const publicIpField = document.getElementById('devicePublicIpAddress');
    const localIpField = document.getElementById('deviceIpAddress');
    const portField = document.getElementById('devicePort');
    
    // Function to update port forwarding instructions
    function updatePortForwardingInstructions() {
        const port = portField?.value || '4370';
        const localIp = localIpField?.value || 'Device Local IP';
        
        const externalPortDisplay = document.getElementById('externalPortDisplay');
        const internalPortDisplay = document.getElementById('internalPortDisplay');
        const localIpDisplay = document.getElementById('localIpDisplay');
        const firewallPortDisplay = document.getElementById('firewallPortDisplay');
        
        if (externalPortDisplay) externalPortDisplay.textContent = port;
        if (internalPortDisplay) internalPortDisplay.textContent = port;
        if (localIpDisplay) localIpDisplay.textContent = localIp || 'Device Local IP';
        if (firewallPortDisplay) firewallPortDisplay.textContent = port;
    }
    
    if (onlineModeToggle && onlineModeFields) {
        onlineModeToggle.addEventListener('change', function() {
            if (this.checked) {
                onlineModeFields.style.display = 'block';
                if (publicIpField) {
                    publicIpField.setAttribute('required', 'required');
                }
                updatePortForwardingInstructions();
            } else {
                onlineModeFields.style.display = 'none';
                if (publicIpField) {
                    publicIpField.removeAttribute('required');
                }
            }
        });
    }
    
    // Update port forwarding instructions when port or local IP changes
    if (portField) {
        portField.addEventListener('input', updatePortForwardingInstructions);
        portField.addEventListener('change', updatePortForwardingInstructions);
    }
    if (localIpField) {
        localIpField.addEventListener('input', updatePortForwardingInstructions);
        localIpField.addEventListener('change', updatePortForwardingInstructions);
    }
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        const deviceId = document.getElementById('deviceId').value;
        
        // Parse JSON fields
        try {
            if (data.connection_config && data.connection_config.trim()) {
                data.connection_config = JSON.parse(data.connection_config);
            }
        } catch (e) {
            Swal.fire('Error!', 'Invalid JSON in Connection Config', 'error');
            return;
        }
        
        try {
            if (data.capabilities && data.capabilities.trim()) {
                data.capabilities = JSON.parse(data.capabilities);
            }
        } catch (e) {
            Swal.fire('Error!', 'Invalid JSON in Capabilities', 'error');
            return;
        }
        
        try {
            if (data.settings && data.settings.trim()) {
                data.settings = JSON.parse(data.settings);
            }
        } catch (e) {
            Swal.fire('Error!', 'Invalid JSON in Settings', 'error');
            return;
        }
        
        // Convert checkboxes to boolean
        data.is_active = document.getElementById('deviceIsActive').checked;
        data.is_online_mode = document.getElementById('deviceIsOnlineMode').checked;
        
        const url = deviceId 
            ? `/attendance-settings/devices/${deviceId}`
            : '/attendance-settings/devices';
        const method = deviceId ? 'PUT' : 'POST';
        
        Swal.fire({
            title: deviceId ? 'Updating Device...' : 'Creating Device...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('deviceModal')).hide();
                form.reset();
                // Reload devices
                loadDevices();
            } else {
                let errorMsg = data.message || 'An error occurred';
                if (data.errors) {
                    errorMsg += '<br><ul>';
                    Object.keys(data.errors).forEach(key => {
                        errorMsg += '<li>' + data.errors[key][0] + '</li>';
                    });
                    errorMsg += '</ul>';
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to save device', 'error');
        });
    });
}

function viewDeviceDetails(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('viewDeviceModal'));
    const content = document.getElementById('viewDeviceContent');
    
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading device details...</p>
        </div>
    `;
    
    currentDeviceId = deviceId;
    document.getElementById('editDeviceFromViewBtn').setAttribute('data-device-id', deviceId);
    
    fetch(`/attendance-settings/devices/${deviceId}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.device) {
            const device = data.device;
            const statusClass = device.is_online ? 'text-success' : 'text-danger';
            const statusIcon = device.is_online ? 'bx-check-circle' : 'bx-x-circle';
            const statusText = device.is_online ? 'Online' : 'Offline';
            const lastSync = device.last_sync_at ? new Date(device.last_sync_at).toLocaleString() : 'Never';
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td><strong>${device.name || 'N/A'}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Device ID:</th>
                                        <td><code>${device.device_id || 'N/A'}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Device Type:</th>
                                        <td><span class="badge bg-info">${device.device_type || 'N/A'}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td>${device.location?.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span class="${statusClass}"><i class="bx ${statusIcon} me-1"></i>${statusText}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Active:</th>
                                        <td>${device.is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="bx bx-network-chart me-2"></i>Connection Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Online Mode:</th>
                                        <td>${device.is_online_mode ? '<span class="badge bg-info"><i class="bx bx-globe me-1"></i>Enabled</span>' : '<span class="badge bg-secondary">Disabled (Local)</span>'}</td>
                                    </tr>
                                    <tr>
                                        <th>Local IP Address:</th>
                                        <td>${device.ip_address || 'N/A'}</td>
                                    </tr>
                                    ${device.is_online_mode && device.public_ip_address ? `
                                    <tr>
                                        <th>Public IP Address:</th>
                                        <td><strong class="text-info">${device.public_ip_address} <i class="bx bx-globe"></i></strong></td>
                                    </tr>
                                    ` : ''}
                                    <tr>
                                        <th>Port:</th>
                                        <td>${device.port || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>MAC Address:</th>
                                        <td>${device.mac_address || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Connection Type:</th>
                                        <td><span class="badge bg-secondary">${device.connection_type || 'N/A'}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Last Sync:</th>
                                        <td><small class="text-muted">${lastSync}</small></td>
                                    </tr>
                                    <tr>
                                        <th>Sync Interval:</th>
                                        <td>${device.sync_interval_minutes || 5} minutes</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bx bx-chip me-2"></i>Hardware Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Manufacturer:</th>
                                        <td>${device.manufacturer || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Model:</th>
                                        <td>${device.model || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Serial Number:</th>
                                        <td>${device.serial_number || 'N/A'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Created By:</th>
                                        <td>${device.creator?.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated By:</th>
                                        <td>${device.updater?.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td><small>${new Date(device.created_at).toLocaleString()}</small></td>
                                    </tr>
                                    <tr>
                                        <th>Updated At:</th>
                                        <td><small>${new Date(device.updated_at).toLocaleString()}</small></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                ${device.connection_config ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-code-alt me-2"></i>Connection Config</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(device.connection_config, null, 2)}</code></pre>
                    </div>
                </div>
                ` : ''}
                ${device.capabilities ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-list-check me-2"></i>Capabilities</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(device.capabilities, null, 2)}</code></pre>
                    </div>
                </div>
                ` : ''}
                ${device.settings ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-slider me-2"></i>Settings</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(device.settings, null, 2)}</code></pre>
                    </div>
                </div>
                ` : ''}
                ${device.notes ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-note me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <p>${device.notes}</p>
                    </div>
                </div>
                ` : ''}
            `;
        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${data.message || 'Failed to load device details'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error-circle me-2"></i>
                <strong>Error:</strong> Failed to load device details
            </div>
        `;
    });
    
    modal.show();
}

function editDeviceFromView() {
    const deviceId = document.getElementById('editDeviceFromViewBtn').getAttribute('data-device-id');
    if (deviceId) {
        bootstrap.Modal.getInstance(document.getElementById('viewDeviceModal')).hide();
        setTimeout(() => {
            openDeviceModal(deviceId);
        }, 300);
    }
}
</script>
@endpush









