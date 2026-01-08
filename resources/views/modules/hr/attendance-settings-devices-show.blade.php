@extends('layouts.app')

@section('title', 'Device Details - ' . $device->name)

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-info-circle"></i> Device Details
                </h4>
                <p class="text-muted">View detailed information about the attendance device</p>
            </div>
            <div>
                <a href="/modules/hr/attendance/settings/devices" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Devices
                </a>
                <a href="/modules/hr/attendance/settings/devices/{{ $device->id }}/edit" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit Device
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .info-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
    <!-- Device Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-2">{{ $device->name }}</h3>
                            <p class="text-muted mb-0">Device ID: <code>{{ $device->device_id }}</code></p>
                        </div>
                        <div class="text-end">
                            @if($device->is_online)
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bx bx-check-circle me-1"></i>Online
                                </span>
                            @else
                                <span class="badge bg-danger fs-6 px-3 py-2">
                                    <i class="bx bx-x-circle me-1"></i>Offline
                                </span>
                            @endif
                            <br>
                            @if($device->is_active)
                                <span class="badge bg-primary mt-2">Active</span>
                            @else
                                <span class="badge bg-secondary mt-2">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Basic Information -->
        <div class="col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="40%">Name:</th>
                            <td><strong>{{ $device->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Device ID:</th>
                            <td><code>{{ $device->device_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Device Type:</th>
                            <td><span class="badge bg-info">{{ ucfirst($device->device_type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>{{ $device->location ? $device->location->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Manufacturer:</th>
                            <td>{{ $device->manufacturer ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Model:</th>
                            <td>{{ $device->model ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Serial Number:</th>
                            <td>{{ $device->serial_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($device->is_online)
                                    <span class="status-online"><i class="bx bx-check-circle me-1"></i>Online</span>
                                @else
                                    <span class="status-offline"><i class="bx bx-x-circle me-1"></i>Offline</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Active:</th>
                            <td>
                                @if($device->is_active)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Connection Information -->
        <div class="col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bx bx-network-chart me-2"></i>Connection Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="40%">Online Mode:</th>
                            <td>
                                @if($device->is_online_mode)
                                    <span class="badge bg-info"><i class="bx bx-globe me-1"></i>Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled (Local)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Local IP Address:</th>
                            <td>{{ $device->ip_address ?? 'N/A' }}</td>
                        </tr>
                        @if($device->is_online_mode && $device->public_ip_address)
                        <tr>
                            <th>Public IP Address:</th>
                            <td><strong class="text-info">{{ $device->public_ip_address }} <i class="bx bx-globe"></i></strong></td>
                        </tr>
                        @endif
                        <tr>
                            <th>Port:</th>
                            <td>{{ $device->port ?? '4370' }}</td>
                        </tr>
                        <tr>
                            <th>MAC Address:</th>
                            <td>{{ $device->mac_address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Connection Type:</th>
                            <td><span class="badge bg-secondary">{{ ucfirst($device->connection_type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Last Sync:</th>
                            <td><small class="text-muted">{{ $device->last_sync_at ? $device->last_sync_at->format('Y-m-d H:i:s') : 'Never' }}</small></td>
                        </tr>
                        <tr>
                            <th>Sync Interval:</th>
                            <td>{{ $device->sync_interval_minutes ?? 5 }} minutes</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Additional Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="40%">Created By:</th>
                            <td>{{ $device->creator ? $device->creator->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $device->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Updated By:</th>
                            <td>{{ $device->updater ? $device->updater->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $device->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($device->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $device->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Device Configuration</h6>
                </div>
                <div class="card-body">
                    @if($device->connection_config)
                    <div class="mb-3">
                        <strong>Connection Config:</strong>
                        <pre class="bg-light p-2 rounded mt-2" style="font-size: 0.85em;">{{ json_encode($device->connection_config, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                    
                    @if($device->capabilities)
                    <div class="mb-3">
                        <strong>Capabilities:</strong>
                        <pre class="bg-light p-2 rounded mt-2" style="font-size: 0.85em;">{{ json_encode($device->capabilities, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                    
                    @if($device->settings)
                    <div class="mb-3">
                        <strong>Settings:</strong>
                        <pre class="bg-light p-2 rounded mt-2" style="font-size: 0.85em;">{{ json_encode($device->settings, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                    
                    @if(!$device->connection_config && !$device->capabilities && !$device->settings)
                    <p class="text-muted mb-0">No additional configuration set</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">Device Actions</h6>
                            <p class="text-muted mb-0">Test connection, sync data, or manage device settings</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-warning me-2" onclick="testDeviceConnection({{ $device->id }})">
                                <i class="bx bx-wifi me-1"></i>Test Connection
                            </button>
                            <a href="/modules/hr/attendance/settings/devices/{{ $device->id }}/edit" class="btn btn-primary me-2">
                                <i class="bx bx-edit me-1"></i>Edit Device
                            </a>
                            <a href="/modules/hr/attendance/settings/devices" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';

function testDeviceConnection(deviceId) {
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
            let html = '<div style="text-align: left;">';
            html += '<p><strong>' + data.message.split('\n')[0] + '</strong></p>';
            if (data.device_info) {
                html += '<hr style="margin: 10px 0;">';
                html += '<p style="margin: 5px 0;"><strong>IP:</strong> ' + (data.device_info.ip || 'N/A') + '</p>';
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
            }).then(() => {
                // Reload page to update status
                window.location.reload();
            });
        } else {
            let errorHtml = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';
            if (data.message) {
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
</script>
@endpush

