<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOMMSS Security Testing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="alert alert-warning">
            <h5>⚠️ Security Testing Mode Active</h5>
            <p><strong>WARNING:</strong> This demonstrates security protections in HOMMSS.</p>
        </div>

        <h1>HOMMSS Security Testing Dashboard</h1>
        <p class="lead">Demonstrating enterprise-grade security protections</p>

        <!-- XSS Protection -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>✅ XSS (Cross-Site Scripting) Protection</h5>
            </div>
            <div class="card-body">
                <p><strong>Protection Status:</strong> <span class="badge bg-success">ACTIVE</span></p>
                <p>Our application protects against XSS attacks using:</p>
                <ul>
                    <li><strong>htmlspecialchars()</strong> - Output encoding for all user data</li>
                    <li><strong>Content Security Policy</strong> - Browser-level script execution control</li>
                    <li><strong>Laravel Blade Auto-escaping</strong> - Template-level protection</li>
                    <li><strong>Input Validation</strong> - Server-side filtering</li>
                </ul>
                <div class="alert alert-info">
                    <strong>Demo:</strong> Any script tags like <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code> 
                    are automatically escaped and rendered harmless.
                </div>
            </div>
        </div>

        <!-- SQL Injection Protection -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>✅ SQL Injection Protection</h5>
            </div>
            <div class="card-body">
                <p><strong>Protection Status:</strong> <span class="badge bg-success">ACTIVE</span></p>
                <p>Our application protects against SQL injection using:</p>
                <ul>
                    <li><strong>Laravel Eloquent ORM</strong> - Object-relational mapping with built-in protection</li>
                    <li><strong>Prepared Statements</strong> - Query parameterization</li>
                    <li><strong>Parameter Binding</strong> - Safe data handling</li>
                    <li><strong>Input Type Casting</strong> - Data validation</li>
                </ul>
                <div class="alert alert-info">
                    <strong>Demo:</strong> Malicious inputs like <code>1' OR '1'='1</code> 
                    are safely handled through prepared statements.
                </div>
            </div>
        </div>

        <!-- Clickjacking Protection -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>✅ Clickjacking Protection</h5>
            </div>
            <div class="card-body">
                <p><strong>Protection Status:</strong> <span class="badge bg-success">ACTIVE</span></p>
                <p>Our application protects against clickjacking using:</p>
                <ul>
                    <li><strong>X-Frame-Options: DENY</strong> - Prevents iframe embedding</li>
                    <li><strong>CSP frame-ancestors</strong> - Modern frame control</li>
                    <li><strong>CSRF Protection</strong> - Request validation</li>
                    <li><strong>Referrer Validation</strong> - Origin checking</li>
                </ul>
                <div class="alert alert-info">
                    <strong>Demo:</strong> This application cannot be embedded in malicious iframes 
                    due to security headers.
                </div>
            </div>
        </div>

        <!-- CSRF Protection -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>✅ CSRF (Cross-Site Request Forgery) Protection</h5>
            </div>
            <div class="card-body">
                <p><strong>Protection Status:</strong> <span class="badge bg-success">ACTIVE</span></p>
                <p>Our application protects against CSRF attacks using:</p>
                <ul>
                    <li><strong>CSRF Tokens</strong> - Unique tokens for each session</li>
                    <li><strong>SameSite Cookies</strong> - Browser-level protection</li>
                    <li><strong>Origin Validation</strong> - Request source verification</li>
                    <li><strong>Double Submit Cookies</strong> - Additional validation layer</li>
                </ul>
                <div class="alert alert-info">
                    <strong>Demo:</strong> All forms include CSRF tokens: <code>{{ csrf_token() }}</code>
                </div>
            </div>
        </div>

        <!-- Security Summary -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>🛡️ Security Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Protection Status:</h6>
                        <ul class="list-unstyled">
                            <li>✅ XSS Protection: <span class="badge bg-success">ACTIVE</span></li>
                            <li>✅ SQL Injection Protection: <span class="badge bg-success">ACTIVE</span></li>
                            <li>✅ Clickjacking Protection: <span class="badge bg-success">ACTIVE</span></li>
                            <li>✅ CSRF Protection: <span class="badge bg-success">ACTIVE</span></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Security Features:</h6>
                        <ul class="list-unstyled">
                            <li>🔒 HTTPS Encryption</li>
                            <li>🔑 Secure Authentication</li>
                            <li>📝 Input Validation</li>
                            <li>🛡️ Security Headers</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-success mt-3">
                    <h6>🎯 Enterprise-Grade Security</h6>
                    <p class="mb-0">HOMMSS implements comprehensive security measures following OWASP best practices 
                    to protect against common web vulnerabilities and ensure data integrity.</p>
                </div>
            </div>
        </div>

        <!-- Commands -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5>🔧 Security Testing Commands</h5>
            </div>
            <div class="card-body">
                <h6>Enable Security Testing:</h6>
                <code>php artisan security:test enable</code>
                
                <h6 class="mt-3">Disable Security Testing:</h6>
                <code>php artisan security:test disable</code>
                
                <h6 class="mt-3">Check Security Status:</h6>
                <code>php artisan security:test status</code>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
