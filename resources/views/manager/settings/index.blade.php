@extends('layouts.manager')
@section('title', 'Settings - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Email Configuration" subtitle="Mail delivery and template settings" />

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="mail" class="text-primary" style="width:20px;height:20px;"></i>
                    <strong>Mail Configuration</strong>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/{{ $prefix }}/settings/mail">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Mail Method</label>
                            <select name="mail_mailer" id="mail_mailer" class="form-select" onchange="toggleMailerFields()">
                                <option value="gmail-oauth" @selected(old('mail_mailer', $mailSettings['mail_mailer']) === 'gmail-oauth')>Gmail OAuth (XOAUTH2)</option>
                                <option value="smtp" @selected(old('mail_mailer', $mailSettings['mail_mailer']) === 'smtp')>SMTP (App Password)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username (Email)</label>
                            <input type="email" name="mail_username" class="form-control"
                                   value="{{ old('mail_username', $mailSettings['mail_username']) }}"
                                   placeholder="admin@apobox.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Address</label>
                            <input type="email" name="mail_from_address" class="form-control"
                                   value="{{ old('mail_from_address', $mailSettings['mail_from_address']) }}"
                                   placeholder="no-reply@apobox.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text" name="mail_from_name" class="form-control"
                                   value="{{ old('mail_from_name', $mailSettings['mail_from_name']) }}"
                                   placeholder="APO Box" required>
                        </div>
                    </div>

                    {{-- SMTP-specific fields --}}
                    <div id="smtp-fields" class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="mail_host" class="form-control"
                                   value="{{ old('mail_host', $mailSettings['mail_host']) }}"
                                   placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="mail_port" class="form-control"
                                   value="{{ old('mail_port', $mailSettings['mail_port']) }}"
                                   placeholder="587" min="1" max="65535">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Encryption</label>
                            <select name="mail_encryption" class="form-select">
                                <option value="tls" @selected(old('mail_encryption', $mailSettings['mail_encryption']) === 'tls')>TLS</option>
                                <option value="ssl" @selected(old('mail_encryption', $mailSettings['mail_encryption']) === 'ssl')>SSL</option>
                                <option value="none" @selected(old('mail_encryption', $mailSettings['mail_encryption']) === 'none')>None</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Password (App Password)
                                @if($mailSettings['mail_password_set'] ?? false)
                                    <span class="badge bg-success-subtle text-success ms-1">Set</span>
                                @endif
                            </label>
                            <input type="password" name="mail_password" class="form-control"
                                   placeholder="{{ ($mailSettings['mail_password_set'] ?? false) ? 'Leave blank to keep current' : 'Enter app password' }}">
                        </div>
                    </div>

                    {{-- OAuth-specific section --}}
                    <div id="oauth-fields" class="mt-3">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div>
                                <h6 class="mb-1">Gmail OAuth Connection</h6>
                                <p class="text-muted small mb-0">
                                    @if($oauthConfigured)
                                        <span class="badge bg-success-subtle text-success">Connected</span>
                                        OAuth refresh token is configured.
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Not configured</span>
                                        Click Connect to authorize Gmail access.
                                    @endif
                                </p>
                            </div>
                            <a href="/{{ $prefix }}/settings/gmail-oauth" class="btn btn-sm btn-outline-primary">
                                <i data-lucide="log-in" class="icon"></i> {{ $oauthConfigured ? 'Reconnect' : 'Connect' }} Gmail
                            </a>
                        </div>
                    </div>

                    @if(session('gmail_refresh_token'))
                        <div class="alert alert-success mt-3">
                            <h6 class="alert-heading mb-2"><i data-lucide="check-circle" class="icon"></i> Refresh Token Obtained</h6>
                            <p class="mb-2">Set this as <code>GOOGLE_MAIL_REFRESH_TOKEN</code> in Railway:</p>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace small" value="{{ session('gmail_refresh_token') }}" readonly id="refresh-token-value">
                                <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('refresh-token-value').value); this.textContent='Copied!'">
                                    <i data-lucide="copy" class="icon"></i> Copy
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="icon"></i> Save Mail Settings
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Send Test Email</h6>
                        <p class="text-muted small mb-0">Sends a test email to <strong>{{ auth('admin')->user()->email }}</strong></p>
                    </div>
                    <form method="POST" action="/{{ $prefix }}/settings/test-email">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i data-lucide="send" class="icon"></i> Send Test
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1"><i data-lucide="file-text" class="icon"></i> Email Templates</h6>
                    <p class="text-muted small mb-0">View and edit email templates sent to customers</p>
                </div>
                <a href="/{{ $prefix }}/settings/email-templates" class="btn btn-outline-primary btn-sm">
                    <i data-lucide="pencil" class="icon"></i> Manage
                </a>
            </div>
        </div>
        <div class="card" id="help-smtp">
            <div class="card-body">
                <h6 class="card-title"><i data-lucide="info" class="icon"></i> Gmail App Password</h6>
                <p class="text-muted small">To use Gmail with SMTP, you need a Google App Password:</p>
                <ol class="text-muted small">
                    <li>Enable 2-Step Verification on your Google account</li>
                    <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a></li>
                    <li>Create a new app password for "Mail"</li>
                    <li>Use the 16-character password in the Password field</li>
                </ol>
                <p class="text-muted small mb-0">
                    Recommended: Host <code>smtp.gmail.com</code>, Port <code>587</code>, Encryption <code>TLS</code>.
                </p>
            </div>
        </div>
        <div class="card mt-3" id="help-oauth">
            <div class="card-body">
                <h6 class="card-title"><i data-lucide="info" class="icon"></i> Gmail OAuth Setup</h6>
                <p class="text-muted small">OAuth uses Google's secure authorization flow instead of a password.</p>
                <ol class="text-muted small">
                    <li>Ensure <code>GOOGLE_CLIENT_ID</code> and <code>GOOGLE_CLIENT_SECRET</code> are set in Railway</li>
                    <li>Add the callback URL to Google Cloud Console authorized redirect URIs</li>
                    <li>Click "Connect Gmail" and authorize</li>
                    <li>Copy the refresh token to Railway as <code>GOOGLE_MAIL_REFRESH_TOKEN</code></li>
                </ol>
                <p class="text-muted small mb-0">
                    Callback URL:<br>
                    <code class="small">{{ url("/{$prefix}/settings/gmail-oauth/callback") }}</code>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMailerFields() {
    const mailer = document.getElementById('mail_mailer').value;
    document.getElementById('smtp-fields').style.display = mailer === 'smtp' ? '' : 'none';
    document.getElementById('oauth-fields').style.display = mailer === 'gmail-oauth' ? '' : 'none';
    document.getElementById('help-smtp').style.display = mailer === 'smtp' ? '' : 'none';
    document.getElementById('help-oauth').style.display = mailer === 'gmail-oauth' ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleMailerFields);
</script>
@endsection
