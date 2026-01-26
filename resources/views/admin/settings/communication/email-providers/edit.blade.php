@extends('layouts.app')

@section('title', 'Edit Email Provider - Communication Settings')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-danger" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-envelope me-2"></i>Edit Email Provider
                            </h3>
                            <p class="mb-0 text-white-50">Update email (SMTP) provider configuration for system notifications</p>
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
                    <form id="emailProviderForm">
                        @csrf
                        <input type="hidden" name="type" value="email">
                        <input type="hidden" id="providerId" value="{{ $provider->id }}">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Provider Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g., Primary SMTP Server" value="{{ old('name', $provider->name) }}">
                                <small class="text-muted">A descriptive name for this email provider</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mailer Type <span class="text-danger">*</span></label>
                                <select name="mailer_type" class="form-select" required>
                                    <option value="smtp" {{ old('mailer_type', $provider->mailer_type) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ old('mailer_type', $provider->mailer_type) == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="mailgun" {{ old('mailer_type', $provider->mailer_type) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ old('mailer_type', $provider->mailer_type) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" name="mail_host" class="form-control" required placeholder="smtp.gmail.com" value="{{ old('mail_host', $provider->mail_host) }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SMTP Port <span class="text-danger">*</span></label>
                                <input type="number" name="mail_port" class="form-control" required placeholder="587" min="1" max="65535" value="{{ old('mail_port', $provider->mail_port) }}">
                                <small class="text-muted">Common ports: 587 (TLS), 465 (SSL), 25 (Standard)</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Encryption <span class="text-danger">*</span></label>
                                <select name="mail_encryption" class="form-select" required>
                                    <option value="tls" {{ old('mail_encryption', $provider->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption', $provider->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">SMTP Username</label>
                                <input type="email" name="mail_username" class="form-control" placeholder="your-email@gmail.com" value="{{ old('mail_username', $provider->mail_username) }}">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">SMTP Password</label>
                                <div class="input-group">
                                    <input type="password" name="mail_password" id="mail_password" class="form-control" placeholder="Leave blank to keep current password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('mail_password')">
                                        <i class="bx bx-show" id="mail_password_icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Leave blank to keep current password. For Gmail, use App Password (not your regular password)</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">From Email Address</label>
                                <input type="email" name="mail_from_address" class="form-control" placeholder="noreply@example.com" value="{{ old('mail_from_address', $provider->mail_from_address) }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">From Name</label>
                                <input type="text" name="mail_from_name" class="form-control" placeholder="OfisiLink System" value="{{ old('mail_from_name', $provider->mail_from_name) }}">
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
                                    <button type="button" class="btn btn-outline-primary" onclick="testEmailProvider()">
                                        <i class="bx bx-refresh me-1"></i>Test Connection
                                    </button>
                                    <button type="submit" class="btn btn-danger">
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
                        <label class="form-label fw-bold">Test Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-envelope"></i>
                            </span>
                            <input type="email" id="testEmailAddress" class="form-control" placeholder="test@example.com" value="{{ auth()->user()->email ?? '' }}">
                        </div>
                        <small class="text-muted">Enter the email address to send a test email to</small>
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 mb-3" onclick="testEmailProvider()">
                        <i class="bx bx-send me-1"></i>Send Test Email
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

// Test Email Provider
function testEmailProvider() {
    const form = document.getElementById('emailProviderForm');
    const testEmail = document.getElementById('testEmailAddress').value.trim();
    
    if (!testEmail) {
        Swal.fire({
            icon: 'warning',
            title: 'Email Required',
            text: 'Please enter a test email address',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(testEmail)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Get form data
    const formData = {
        name: form.querySelector('input[name="name"]').value,
        type: 'email',
        mailer_type: form.querySelector('select[name="mailer_type"]').value,
        mail_host: form.querySelector('input[name="mail_host"]').value,
        mail_port: parseInt(form.querySelector('input[name="mail_port"]').value),
        mail_encryption: form.querySelector('select[name="mail_encryption"]').value,
        mail_username: form.querySelector('input[name="mail_username"]').value,
        mail_password: form.querySelector('input[name="mail_password"]').value,
        mail_from_address: form.querySelector('input[name="mail_from_address"]').value,
        mail_from_name: form.querySelector('input[name="mail_from_name"]').value,
        test_email: testEmail
    };
    
    // Validate required fields
    if (!formData.mail_host || !formData.mail_port) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Configuration',
            text: 'Please enter SMTP host and port before testing',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Validate host is not localhost for production
    const hostLower = formData.mail_host.toLowerCase().trim();
    if (hostLower === '127.0.0.1' || hostLower === 'localhost' || hostLower === '::1' || hostLower === '0.0.0.0') {
        Swal.fire({
            icon: 'warning',
            title: 'Localhost Detected',
            html: `
                <p><strong>You're using localhost (${formData.mail_host})</strong> which won't work in production.</p>
                <p class="text-start mt-3"><strong>For production, use:</strong></p>
                <ul class="text-start">
                    <li><strong>Gmail:</strong> smtp.gmail.com (Port: 587 with TLS or 465 with SSL)</li>
                    <li><strong>Outlook/Hotmail:</strong> smtp-mail.outlook.com (Port: 587 with TLS)</li>
                    <li><strong>Yahoo:</strong> smtp.mail.yahoo.com (Port: 587 with TLS)</li>
                    <li><strong>Custom SMTP:</strong> Your provider's SMTP server address</li>
                </ul>
                <p class="text-muted small mt-3">Please update the SMTP Host field with a valid production SMTP server.</p>
            `,
            confirmButtonText: 'OK',
            width: '600px'
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
                <div class="spinner-border text-danger mb-3" role="status"></div>
                <p>Testing SMTP connection to ${formData.mail_host}:${formData.mail_port}...</p>
                <p class="text-muted small">Sending test email to ${testEmail}</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    // Test email using the communication test endpoint
    fetch('{{ route("admin.settings.communication.test-email") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            mail_host: formData.mail_host,
            mail_port: formData.mail_port,
            mail_encryption: formData.mail_encryption,
            mail_username: formData.mail_username,
            mail_password: formData.mail_password || null,
            mail_from_address: formData.mail_from_address || formData.mail_username,
            mail_from_name: formData.mail_from_name || 'OfisiLink System',
            email: testEmail
        })
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        
        if (data.success) {
            document.getElementById('connectionStatus').innerHTML = `
                <div class="text-success">
                    <i class="bx bx-check-circle fs-1"></i>
                    <p class="mt-2 mb-0 fw-bold">Connection Successful</p>
                    <p class="text-muted small">Test email sent successfully</p>
                </div>
            `;
            
            document.getElementById('testResult').style.display = 'block';
            document.getElementById('testResultContent').innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="bx bx-check-circle me-2"></i>
                    <strong>Success!</strong> Test email sent to ${testEmail}
                    <br><small class="text-muted">${data.message || 'Please check your inbox'}</small>
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
            
            let errorHtml = `
                <div class="alert alert-danger mb-0">
                    <i class="bx bx-x-circle me-2"></i>
                    <strong>Failed!</strong> ${data.message || 'Could not send test email'}
            `;
            
            if (data.error) {
                errorHtml += `<br><small class="text-muted"><strong>Error:</strong> ${data.error}</small>`;
            }
            
            if (data.suggestion) {
                errorHtml += `<br><small class="text-muted mt-2 d-block"><strong>Suggestion:</strong> ${data.suggestion}</small>`;
            }
            
            errorHtml += `</div>`;
            document.getElementById('testResultContent').innerHTML = errorHtml;
        }
    })
    .catch(err => {
        Swal.close();
        document.getElementById('connectionStatus').innerHTML = `
            <div class="text-danger">
                <i class="bx bx-x-circle fs-1"></i>
                <p class="mt-2 mb-0 fw-bold">Network Error</p>
                <p class="text-muted small">Please try again</p>
            </div>
        `;
        
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Network error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
}

// Form Submission
document.getElementById('emailProviderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: this.querySelector('input[name="name"]').value,
        mailer_type: this.querySelector('select[name="mailer_type"]').value,
        mail_host: this.querySelector('input[name="mail_host"]').value,
        mail_port: parseInt(this.querySelector('input[name="mail_port"]').value),
        mail_encryption: this.querySelector('select[name="mail_encryption"]').value,
        mail_username: this.querySelector('input[name="mail_username"]').value,
        mail_password: this.querySelector('input[name="mail_password"]').value || null,
        mail_from_address: this.querySelector('input[name="mail_from_address"]').value,
        mail_from_name: this.querySelector('input[name="mail_from_name"]').value,
        description: this.querySelector('textarea[name="description"]').value,
        is_active: this.querySelector('input[name="is_active"]').checked,
        is_primary: this.querySelector('input[name="is_primary"]').checked,
    };
    
    // Remove password if empty (to keep current password)
    if (!formData.mail_password) {
        delete formData.mail_password;
    }
    
    Swal.fire({
        title: 'Updating...',
        html: 'Please wait while we update the email provider',
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
                text: data.message || 'Email provider updated successfully',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '{{ route("admin.settings.communication.page") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update email provider',
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





