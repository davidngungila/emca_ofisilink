@extends('layouts.app')

@section('title', 'Edit SMS Provider - Communication Settings')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-message-rounded-dots me-2"></i>Edit SMS Provider
                            </h3>
                            <p class="mb-0 text-white-50">Update SMS gateway provider configuration for system notifications</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <a href="{{ route('admin.settings.communication.page') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Configuration Form -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-cog me-2"></i>Provider Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form id="smsProviderForm">
                        @csrf
                        <input type="hidden" name="type" value="sms">
                        <input type="hidden" id="providerId" value="{{ $provider->id }}">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Provider Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g., Primary SMS Gateway" value="{{ old('name', $provider->name) }}">
                                <small class="text-muted">A descriptive name for this SMS provider</small>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">SMS Username <span class="text-danger">*</span></label>
                                <input type="text" name="sms_username" class="form-control" required placeholder="SMS gateway username" value="{{ old('sms_username', $provider->sms_username) }}">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">SMS Password</label>
                                <div class="input-group">
                                    <input type="password" name="sms_password" id="sms_password" class="form-control" placeholder="Leave blank to keep current password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('sms_password')">
                                        <i class="bx bx-show" id="sms_password_icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMS From (Sender Name)</label>
                                <input type="text" name="sms_from" class="form-control" placeholder="OfisiLink" value="{{ old('sms_from', $provider->sms_from) }}">
                                <small class="text-muted">Name displayed as sender (if supported by gateway)</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMS API URL <span class="text-danger">*</span></label>
                                <input type="url" name="sms_url" class="form-control" required placeholder="https://messaging-service.co.tz/api/sms" value="{{ old('sms_url', $provider->sms_url) }}">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Optional description for this provider">{{ old('description', $provider->description) }}</textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $provider->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                                <small class="text-muted">Provider will be available for use</small>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_primary" id="isPrimary" {{ old('is_primary', $provider->is_primary) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPrimary">Set as Primary</label>
                                </div>
                                <small class="text-muted">Primary provider is used first</small>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.settings.communication.page') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary" onclick="testSmsProvider()">
                                        <i class="bx bx-refresh me-1"></i>Test Connection
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i>Update Provider
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Testing Section -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-test-tube me-2"></i>Test Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Test your configuration</strong> before saving to ensure everything works correctly.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Test Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-phone"></i>
                            </span>
                            <input type="text" id="testPhoneNumber" class="form-control" placeholder="255712345678" pattern="^255[0-9]{9}$">
                        </div>
                        <small class="text-muted">Format: 255XXXXXXXXX (12 digits starting with 255)</small>
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 mb-3" onclick="testSmsProvider()">
                        <i class="bx bx-send me-1"></i>Send Test SMS
                    </button>
                    
                    <div id="testResult" class="mt-3" style="display: none;">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">Test Result:</h6>
                                <div id="testResultContent"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-3">Connection Status</h6>
                        <div id="connectionStatus" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted small mt-2 mb-0">Click "Test Connection" to check status</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const token = '{{ csrf_token() }}';
const providerId = {{ $provider->id }};

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if(field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        field.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
}

// Test SMS Provider
function testSmsProvider() {
    const form = document.getElementById('smsProviderForm');
    const testPhone = document.getElementById('testPhoneNumber').value.trim();
    
    if (!testPhone) {
        Swal.fire({
            icon: 'warning',
            title: 'Phone Required',
            text: 'Please enter a test phone number',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Validate phone format
    if (!/^255[0-9]{9}$/.test(testPhone)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Format',
            text: 'Phone number must be in format: 255XXXXXXXXX (12 digits)',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Get form data
    const formData = {
        name: form.querySelector('input[name="name"]').value,
        type: 'sms',
        sms_username: form.querySelector('input[name="sms_username"]').value,
        sms_password: form.querySelector('input[name="sms_password"]').value || null,
        sms_from: form.querySelector('input[name="sms_from"]').value,
        sms_url: form.querySelector('input[name="sms_url"]').value,
        test_phone: testPhone
    };
    
    // Validate required fields
    if (!formData.sms_username || !formData.sms_url) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Configuration',
            text: 'Please fill in all required fields before testing',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Update connection status
    document.getElementById('connectionStatus').innerHTML = `
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Testing...</span>
        </div>
        <p class="text-muted small mt-2 mb-0">Testing connection...</p>
    `;
    
    Swal.fire({
        title: 'Testing Connection...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Testing SMS gateway connection...</p>
                <p class="text-muted small">Sending test SMS to ${testPhone}</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    // Test SMS using the communication test endpoint
    fetch('{{ route("admin.settings.communication.test-sms") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            sms_username: formData.sms_username,
            sms_password: formData.sms_password,
            sms_from: formData.sms_from || 'OfisiLink',
            sms_url: formData.sms_url,
            phone: testPhone
        })
    })
    .then(async res => {
        if (!res.ok) {
            const errorText = await res.text();
            let errorData;
            try {
                errorData = JSON.parse(errorText);
            } catch (e) {
                errorData = {
                    success: false,
                    message: `Server error (${res.status}): ${errorText || res.statusText}`,
                    error: errorText || res.statusText
                };
            }
            throw new Error(JSON.stringify(errorData));
        }
        
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await res.text();
            throw new Error(JSON.stringify({
                success: false,
                message: 'Server returned non-JSON response',
                error: text || 'Invalid response format'
            }));
        }
        
        const text = await res.text();
        if (!text || text.trim() === '') {
            throw new Error(JSON.stringify({
                success: false,
                message: 'Server returned empty response',
                error: 'Empty response from server'
            }));
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error(JSON.stringify({
                success: false,
                message: 'Failed to parse server response',
                error: `JSON parse error: ${e.message}. Response: ${text.substring(0, 200)}`
            }));
        }
    })
    .then(data => {
        Swal.close();
        
        if (data.success) {
            document.getElementById('connectionStatus').innerHTML = `
                <div class="text-success">
                    <i class="bx bx-check-circle fs-1"></i>
                    <p class="mt-2 mb-0 fw-bold">Connection Successful</p>
                    <p class="text-muted small">Test SMS sent successfully</p>
                </div>
            `;
            
            document.getElementById('testResult').style.display = 'block';
            document.getElementById('testResultContent').innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="bx bx-check-circle me-2"></i>
                    <strong>Success!</strong> Test SMS sent to ${testPhone}
                    <br><small class="text-muted">${data.message || 'Please check your phone'}</small>
                </div>
            `;
        } else {
            document.getElementById('connectionStatus').innerHTML = `
                <div class="text-danger">
                    <i class="bx bx-x-circle fs-1"></i>
                    <p class="mt-2 mb-0 fw-bold">Connection Failed</p>
                    <p class="text-muted small">Please check your configuration</p>
                </div>
            `;
            
            document.getElementById('testResult').style.display = 'block';
            document.getElementById('testResultContent').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bx bx-x-circle me-2"></i>
                    <strong>Failed!</strong> ${data.message || 'Could not send test SMS'}
                </div>
            `;
        }
    })
    .catch(err => {
        Swal.close();
        
        let errorMessage = 'Network error occurred. Please try again.';
        let errorDetails = '';
        
        try {
            const errorData = JSON.parse(err.message);
            if (errorData.message) {
                errorMessage = errorData.message;
            }
            if (errorData.error) {
                errorDetails = errorData.error;
            }
        } catch (e) {
            errorMessage = err.message || 'Network error occurred. Please try again.';
        }
        
        document.getElementById('connectionStatus').innerHTML = `
            <div class="text-danger">
                <i class="bx bx-x-circle fs-1"></i>
                <p class="mt-2 mb-0 fw-bold">Connection Failed</p>
                <p class="text-muted small">Please check your configuration</p>
            </div>
        `;
        
        document.getElementById('testResult').style.display = 'block';
        document.getElementById('testResultContent').innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="bx bx-x-circle me-2"></i>
                <strong>Failed!</strong> ${errorMessage}
                ${errorDetails ? '<br><small class="text-muted d-block mt-2"><strong>Details:</strong> ' + errorDetails + '</small>' : ''}
            </div>
        `;
        
        Swal.fire({
            icon: 'error',
            title: 'Test Failed',
            html: `
                <p>${errorMessage}</p>
                ${errorDetails ? '<p class="text-muted small mt-2">' + errorDetails + '</p>' : ''}
                <p class="text-muted small mt-3">Please check your SMS configuration and try again.</p>
            `,
            confirmButtonText: 'OK',
            width: '500px'
        });
    });
}

// Form Submission
document.getElementById('smsProviderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: this.querySelector('input[name="name"]').value,
        sms_username: this.querySelector('input[name="sms_username"]').value,
        sms_password: this.querySelector('input[name="sms_password"]').value || null,
        sms_from: this.querySelector('input[name="sms_from"]').value,
        sms_url: this.querySelector('input[name="sms_url"]').value,
        description: this.querySelector('textarea[name="description"]').value,
        is_active: this.querySelector('input[name="is_active"]').checked,
        is_primary: this.querySelector('input[name="is_primary"]').checked,
    };
    
    // Remove password if empty (to keep current password)
    if (!formData.sms_password) {
        delete formData.sms_password;
    }
    
    Swal.fire({
        title: 'Updating...',
        html: 'Please wait while we update the SMS provider',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch(`{{ url('/admin/settings/notification-providers') }}/${providerId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'SMS provider updated successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '{{ route("admin.settings.communication.page") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update SMS provider',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
});
</script>
@endpush

