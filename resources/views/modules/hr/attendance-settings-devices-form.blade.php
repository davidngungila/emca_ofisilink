@extends('layouts.app')

@section('title', $mode === 'create' ? 'Add Device' : 'Edit Device')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-{{ $mode === 'create' ? 'plus' : 'edit' }}"></i> {{ $mode === 'create' ? 'Add Device' : 'Edit Device' }}
                </h4>
                <p class="text-muted">{{ $mode === 'create' ? 'Add a new attendance device to the system' : 'Update device information' }}</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings.devices') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Devices
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .form-section-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ $mode === 'create' ? route('attendance-settings.devices.store') : route('attendance-settings.devices.update', $device->id) }}" method="POST" id="deviceForm">
        @csrf
        @if($mode === 'edit' && $device)
            @method('PUT')
        @endif

        <!-- Basic Information -->
        <div class="form-section">
            <h5 class="form-section-title">
                <i class="bx bx-info-circle me-2"></i>Basic Information
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="deviceName" class="form-label">Device Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="deviceName" name="name" value="{{ old('name', $device->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="deviceDeviceId" class="form-label">Device ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('device_id') is-invalid @enderror" id="deviceDeviceId" name="device_id" value="{{ old('device_id', $device->device_id ?? '') }}" required>
                    <small class="text-muted">Unique identifier for the device</small>
                    @error('device_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="deviceType" class="form-label">Device Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('device_type') is-invalid @enderror" id="deviceType" name="device_type" required>
                        <option value="biometric" {{ old('device_type', $device->device_type ?? 'biometric') === 'biometric' ? 'selected' : '' }}>Biometric</option>
                        <option value="rfid" {{ old('device_type', $device->device_type ?? '') === 'rfid' ? 'selected' : '' }}>RFID</option>
                        <option value="fingerprint" {{ old('device_type', $device->device_type ?? '') === 'fingerprint' ? 'selected' : '' }}>Fingerprint</option>
                        <option value="face_recognition" {{ old('device_type', $device->device_type ?? '') === 'face_recognition' ? 'selected' : '' }}>Face Recognition</option>
                        <option value="card_swipe" {{ old('device_type', $device->device_type ?? '') === 'card_swipe' ? 'selected' : '' }}>Card Swipe</option>
                        <option value="mobile" {{ old('device_type', $device->device_type ?? '') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    </select>
                    @error('device_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="deviceLocation" class="form-label">Location</label>
                    <select class="form-select @error('location_id') is-invalid @enderror" id="deviceLocation" name="location_id">
                        <option value="">Select Location</option>
                        @foreach($locations ?? [] as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $device->location_id ?? '') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="deviceManufacturer" class="form-label">Manufacturer</label>
                    <input type="text" class="form-control @error('manufacturer') is-invalid @enderror" id="deviceManufacturer" name="manufacturer" value="{{ old('manufacturer', $device->manufacturer ?? '') }}">
                    @error('manufacturer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="deviceModel" class="form-label">Model</label>
                    <input type="text" class="form-control @error('model') is-invalid @enderror" id="deviceModel" name="model" value="{{ old('model', $device->model ?? '') }}">
                    @error('model')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="deviceSerialNumber" class="form-label">Serial Number</label>
                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror" id="deviceSerialNumber" name="serial_number" value="{{ old('serial_number', $device->serial_number ?? '') }}">
                    @error('serial_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Network Configuration -->
        <div class="form-section">
            <h5 class="form-section-title">
                <i class="bx bx-network-chart me-2"></i>Network Configuration
            </h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="deviceIpAddress" class="form-label">Local IP Address</label>
                    <input type="text" class="form-control @error('ip_address') is-invalid @enderror" id="deviceIpAddress" name="ip_address" value="{{ old('ip_address', $device->ip_address ?? '') }}" placeholder="192.168.1.100">
                    @error('ip_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="devicePort" class="form-label">Port</label>
                    <input type="number" class="form-control @error('port') is-invalid @enderror" id="devicePort" name="port" min="1" max="65535" value="{{ old('port', $device->port ?? '4370') }}" placeholder="4370">
                    @error('port')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="deviceMacAddress" class="form-label">MAC Address</label>
                    <input type="text" class="form-control @error('mac_address') is-invalid @enderror" id="deviceMacAddress" name="mac_address" value="{{ old('mac_address', $device->mac_address ?? '') }}" placeholder="00:1B:44:11:3A:B7">
                    @error('mac_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="deviceConnectionType" class="form-label">Connection Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('connection_type') is-invalid @enderror" id="deviceConnectionType" name="connection_type" required>
                        <option value="network" {{ old('connection_type', $device->connection_type ?? 'network') === 'network' ? 'selected' : '' }}>Network</option>
                        <option value="usb" {{ old('connection_type', $device->connection_type ?? '') === 'usb' ? 'selected' : '' }}>USB</option>
                        <option value="bluetooth" {{ old('connection_type', $device->connection_type ?? '') === 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                        <option value="wifi" {{ old('connection_type', $device->connection_type ?? '') === 'wifi' ? 'selected' : '' }}>WiFi</option>
                    </select>
                    @error('connection_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="deviceSyncInterval" class="form-label">Sync Interval (minutes)</label>
                    <input type="number" class="form-control @error('sync_interval_minutes') is-invalid @enderror" id="deviceSyncInterval" name="sync_interval_minutes" min="1" max="1440" value="{{ old('sync_interval_minutes', $device->sync_interval_minutes ?? 5) }}">
                    <small class="text-muted">How often to sync with device</small>
                    @error('sync_interval_minutes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Online Mode Section -->
        <div class="form-section">
            <div class="card border-info mb-0">
                <div class="card-header bg-info bg-opacity-10">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="deviceIsOnlineMode" name="is_online_mode" value="1" {{ old('is_online_mode', ($device && $device->is_online_mode) ? true : false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="deviceIsOnlineMode">
                            <i class="bx bx-globe me-1"></i>Online Mode (Remote Access)
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">Enable this if the device is on a different network and requires public IP access</small>
                </div>
                <div class="card-body" id="onlineModeFields" style="display: {{ old('is_online_mode', ($device && $device->is_online_mode) ? true : false) ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="devicePublicIpAddress" class="form-label">Public IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('public_ip_address') is-invalid @enderror" id="devicePublicIpAddress" name="public_ip_address" value="{{ old('public_ip_address', $device->public_ip_address ?? '') }}" placeholder="41.59.154.147">
                            <small class="text-muted">Enter the public IP address for remote access. This will be used instead of local IP when online mode is enabled.</small>
                            @error('public_ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Online Mode Configuration Required:</strong>
                        <ol class="mb-0 mt-2" style="padding-left: 20px;">
                            <li><strong>Port Forwarding:</strong> Configure your router to forward:
                                <ul style="margin-top: 5px;">
                                    <li>External Port: <code id="externalPortDisplay">{{ old('port', $device->port ?? '4370') }}</code></li>
                                    <li>Internal IP: <span id="localIpDisplay">{{ old('ip_address', $device->ip_address ?? 'Device Local IP') }}</span></li>
                                    <li>Internal Port: <code id="internalPortDisplay">{{ old('port', $device->port ?? '4370') }}</code></li>
                                </ul>
                            </li>
                            <li><strong>Router Firewall:</strong> Allow incoming connections on port <code id="firewallPortDisplay">{{ old('port', ($device && $device->port) ? $device->port : '4370') }}</code></li>
                            <li><strong>Device Firewall:</strong> Ensure device allows connections from internet</li>
                            <li><strong>Public IP:</strong> Enter the router's public IP address (e.g., 41.59.154.147)</li>
                            <li><strong>Local IP:</strong> Enter the device's local network IP address (e.g., 192.168.1.100)</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="form-section">
            <h5 class="form-section-title">
                <i class="bx bx-cog me-2"></i>Additional Settings
            </h5>
            <div class="mb-3">
                <label for="deviceConnectionConfig" class="form-label">Connection Config (JSON)</label>
                <textarea class="form-control @error('connection_config') is-invalid @enderror" id="deviceConnectionConfig" name="connection_config" rows="3" placeholder='{"api_key": "xxx", "endpoint": "xxx"}'>{{ old('connection_config', ($device && !empty($device->connection_config)) ? json_encode($device->connection_config, JSON_PRETTY_PRINT) : '') }}</textarea>
                <small class="text-muted">JSON format for connection settings</small>
                @error('connection_config')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="deviceCapabilities" class="form-label">Capabilities (JSON)</label>
                <textarea class="form-control @error('capabilities') is-invalid @enderror" id="deviceCapabilities" name="capabilities" rows="2" placeholder='["fingerprint", "face_recognition"]'>{{ old('capabilities', ($device && !empty($device->capabilities)) ? json_encode($device->capabilities, JSON_PRETTY_PRINT) : '') }}</textarea>
                <small class="text-muted">Device capabilities in JSON array format</small>
                @error('capabilities')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="deviceSettings" class="form-label">Settings (JSON)</label>
                <textarea class="form-control @error('settings') is-invalid @enderror" id="deviceSettings" name="settings" rows="2" placeholder='{"timezone": "UTC", "language": "en"}'>{{ old('settings', ($device && !empty($device->settings)) ? json_encode($device->settings, JSON_PRETTY_PRINT) : '') }}</textarea>
                <small class="text-muted">Device-specific settings in JSON format</small>
                @error('settings')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="deviceNotes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="deviceNotes" name="notes" rows="3">{{ old('notes', ($device && $device->notes) ? $device->notes : '') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="deviceIsActive" name="is_active" value="1" {{ old('is_active', ($device && isset($device->is_active)) ? $device->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="deviceIsActive">
                        Active
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('modules.hr.attendance.settings.devices') }}" class="btn btn-secondary">
                <i class="bx bx-x me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>{{ $mode === 'create' ? 'Create Device' : 'Update Device' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Initial update
    updatePortForwardingInstructions();
});
</script>
@endpush

