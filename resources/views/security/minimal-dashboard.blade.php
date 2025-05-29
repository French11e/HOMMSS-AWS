@extends('layouts.app')

@section('content')
<style>
    .security-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .security-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .security-icon {
        width: 24px;
        height: 24px;
        margin-right: 8px;
    }

    .status-badge {
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .status-active {
        background-color: #dcfce7;
        color: #166534;
    }

    .warning-banner {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #f59e0b;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .security-summary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 1px solid #3b82f6;
        border-radius: 8px;
    }

    .command-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px;
        font-family: 'Courier New', monospace;
        font-size: 14px;
    }
</style>
    <div class="container mt-4">
        <div class="warning-banner">
            <div class="d-flex align-items-center">
                <svg class="security-icon" viewBox="0 0 52 52" fill="currentColor">
                    <use href="#icon_shield" />
                </svg>
                <div>
                    <h5 class="mb-1">Security Testing Mode Active</h5>
                    <p class="mb-0"><strong>WARNING:</strong> This demonstrates security protections in HOMMSS.</p>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">HOMMSS Security Dashboard</h1>
            <p class="lead text-muted">Demonstrating enterprise-grade security protections</p>
        </div>

        <!-- XSS Protection -->
        <div class="security-card mb-4">
            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <svg class="security-icon" viewBox="0 0 52 52" fill="currentColor">
                    <use href="#icon_shield" />
                </svg>
                <h5 class="mb-0">XSS (Cross-Site Scripting) Protection</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0"><strong>Protection Status:</strong></p>
                    <span class="status-badge status-active">ACTIVE</span>
                </div>
                <p class="text-muted mb-3">Our application protects against XSS attacks using:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>htmlspecialchars()</strong> - Output encoding
                            </li>
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Content Security Policy</strong> - Browser control
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Blade Auto-escaping</strong> - Template protection
                            </li>
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Input Validation</strong> - Server-side filtering
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info border-0" style="background-color: #e0f2fe;">
                    <strong>Demo:</strong> Script tags like <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code>
                    are automatically escaped and rendered harmless.
                </div>
            </div>
        </div>

        <!-- SQL Injection Protection -->
        <div class="security-card mb-4">
            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white;">
                <svg class="security-icon" viewBox="0 0 52 52" fill="currentColor">
                    <use href="#icon_shield" />
                </svg>
                <h5 class="mb-0">SQL Injection Protection</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0"><strong>Protection Status:</strong></p>
                    <span class="status-badge status-active">ACTIVE</span>
                </div>
                <p class="text-muted mb-3">Our application protects against SQL injection using:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Laravel Eloquent ORM</strong> - Built-in protection
                            </li>
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Prepared Statements</strong> - Query parameterization
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Parameter Binding</strong> - Safe data handling
                            </li>
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Input Type Casting</strong> - Data validation
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info border-0" style="background-color: #e0f2fe;">
                    <strong>Demo:</strong> Malicious inputs like <code>1' OR '1'='1</code>
                    are safely handled through prepared statements.
                </div>
            </div>
        </div>

        <!-- Clickjacking Protection -->
        <div class="security-card mb-4">
            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                <svg class="security-icon" viewBox="0 0 52 52" fill="currentColor">
                    <use href="#icon_shield" />
                </svg>
                <h5 class="mb-0">Clickjacking Protection</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0"><strong>Protection Status:</strong></p>
                    <span class="status-badge status-active">ACTIVE</span>
                </div>
                <p class="text-muted mb-3">Our application protects against clickjacking using:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>X-Frame-Options: DENY</strong> - Iframe blocking
                            </li>
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>CSP frame-ancestors</strong> - Modern control
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>CSRF Protection</strong> - Request validation
                            </li>
                            <li class="mb-2">
                                <svg class="security-icon" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <strong>Referrer Validation</strong> - Origin checking
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info border-0" style="background-color: #e0f2fe;">
                    <strong>Demo:</strong> This application cannot be embedded in malicious iframes
                    due to security headers.
                </div>
            </div>
        </div>

        <!-- Security Summary -->
        <div class="security-summary">
            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%); color: white;">
                <svg class="security-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <h5 class="mb-0">Security Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-dark mb-3">Protection Status:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #10b981;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                XSS Protection: <span class="status-badge status-active ms-2">ACTIVE</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #10b981;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                SQL Injection Protection: <span class="status-badge status-active ms-2">ACTIVE</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #10b981;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Clickjacking Protection: <span class="status-badge status-active ms-2">ACTIVE</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #10b981;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                CSRF Protection: <span class="status-badge status-active ms-2">ACTIVE</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-dark mb-3">Security Features:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #3b82f6;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                HTTPS Encryption
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #3b82f6;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
                                </svg>
                                Secure Authentication
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #3b82f6;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                                Input Validation
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <svg class="security-icon" style="width: 16px; height: 16px; color: #3b82f6;" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Security Headers
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-success border-0 mt-4" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);">
                    <div class="d-flex align-items-center">
                        <svg class="security-icon" style="color: #166534;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h6 class="mb-1 text-dark">Enterprise-Grade Security</h6>
                            <p class="mb-0 text-dark">HOMMSS implements comprehensive security measures following OWASP best practices
                            to protect against common web vulnerabilities and ensure data integrity.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commands -->
        <div class="security-card mt-4">
            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white;">
                <svg class="security-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd" />
                </svg>
                <h5 class="mb-0">Security Testing Commands</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-dark">Enable Security Testing:</h6>
                        <div class="command-box">php artisan security:test enable</div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-dark">Disable Security Testing:</h6>
                        <div class="command-box">php artisan security:test disable</div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-dark">Check Security Status:</h6>
                        <div class="command-box">php artisan security:test status</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
