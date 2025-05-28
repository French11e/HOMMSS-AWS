<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOMMSS Security Testing Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .vulnerability-card { border-left: 4px solid #dc3545; }
        .protection-card { border-left: 4px solid #28a745; }
        .test-result { margin-top: 15px; padding: 15px; border-radius: 5px; }
        .vulnerable { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .protected { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .warning-banner { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="warning-banner">
            <h5><i class="fas fa-exclamation-triangle"></i> Security Testing Mode</h5>
            <p><strong>WARNING:</strong> This dashboard is for security testing purposes only. Do not use in production!</p>
        </div>

        <h1>HOMMSS Security Testing Dashboard</h1>
        <p class="lead">Test and demonstrate security vulnerabilities and protections</p>

        <!-- Security Status Overview -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Security Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-info" onclick="getSecurityReport()">Get Security Report</button>
                        <div id="security-overview"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- XSS Testing -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card vulnerability-card">
                    <div class="card-header">
                        <h5>XSS (Cross-Site Scripting) Testing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" id="xss-payload" class="form-control" 
                                       value="&lt;script&gt;alert('XSS Test')&lt;/script&gt;" 
                                       placeholder="Enter XSS payload">
                            </div>
                            <div class="col-md-4">
                                <select id="xss-type" class="form-select">
                                    <option value="reflected">Reflected XSS</option>
                                    <option value="stored">Stored XSS</option>
                                    <option value="dom">DOM XSS</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger" onclick="testXSS()">Test XSS Attack</button>
                            <small class="text-muted">Tests XSS protection mechanisms</small>
                        </div>
                        <div id="xss-result"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clickjacking Testing -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card vulnerability-card">
                    <div class="card-header">
                        <h5>Clickjacking Testing</h5>
                    </div>
                    <div class="card-body">
                        <p>Test if the application can be embedded in malicious iframes</p>
                        <button class="btn btn-warning" onclick="testClickjacking()">Test Clickjacking Protection</button>
                        <button class="btn btn-secondary" onclick="openClickjackingDemo()">Open Iframe Demo</button>
                        <div id="clickjacking-result"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQL Injection Testing -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card vulnerability-card">
                    <div class="card-header">
                        <h5>SQL Injection Testing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" id="sql-payload" class="form-control" 
                                       value="1' OR '1'='1" 
                                       placeholder="Enter SQL injection payload">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-danger" onclick="testSQLInjection()">Test SQL Injection</button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Common payloads: <code>1' OR '1'='1</code>, <code>'; DROP TABLE users; --</code></small>
                        </div>
                        <div id="sql-result"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Recommendations -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card protection-card">
                    <div class="card-header">
                        <h5>Security Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>XSS Protection</h6>
                                <ul class="small">
                                    <li>Use htmlspecialchars() for output</li>
                                    <li>Implement Content Security Policy</li>
                                    <li>Validate all inputs</li>
                                    <li>Use Blade auto-escaping</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6>Clickjacking Protection</h6>
                                <ul class="small">
                                    <li>Set X-Frame-Options header</li>
                                    <li>Use CSP frame-ancestors</li>
                                    <li>Implement CSRF protection</li>
                                    <li>Validate referrer headers</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6>SQL Injection Protection</h6>
                                <ul class="small">
                                    <li>Use prepared statements</li>
                                    <li>Validate all inputs</li>
                                    <li>Use Laravel Eloquent</li>
                                    <li>Implement input sanitization</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // XSS Testing
        function testXSS() {
            const payload = document.getElementById('xss-payload').value;
            const type = document.getElementById('xss-type').value;
            
            fetch('/security/xss-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payload: payload,
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                displayResult('xss-result', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Clickjacking Testing
        function testClickjacking() {
            fetch('/security/clickjacking-test', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                displayResult('clickjacking-result', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // SQL Injection Testing
        function testSQLInjection() {
            const payload = document.getElementById('sql-payload').value;
            
            fetch('/security/sql-injection-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    input: payload
                })
            })
            .then(response => response.json())
            .then(data => {
                displayResult('sql-result', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Get Security Report
        function getSecurityReport() {
            fetch('/security/report')
            .then(response => response.json())
            .then(data => {
                displaySecurityOverview(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Display test results
        function displayResult(elementId, data) {
            const element = document.getElementById(elementId);
            const statusClass = data.status === 'PROTECTED' ? 'protected' : 'vulnerable';
            
            element.innerHTML = `
                <div class="test-result ${statusClass}">
                    <h6>Status: ${data.status}</h6>
                    <p>${data.message}</p>
                    ${data.protection ? `<small><strong>Protection:</strong> ${data.protection}</small>` : ''}
                    ${data.warning ? `<small class="text-danger"><strong>Warning:</strong> ${data.warning}</small>` : ''}
                </div>
            `;
        }

        // Display security overview
        function displaySecurityOverview(data) {
            const element = document.getElementById('security-overview');
            const statusClass = data.security_status === 'SECURE' ? 'protected' : 'vulnerable';
            
            let vulnerabilitiesList = '';
            if (data.active_vulnerabilities.length > 0) {
                vulnerabilitiesList = '<ul>' + data.active_vulnerabilities.map(v => `<li>${v}</li>`).join('') + '</ul>';
            }
            
            element.innerHTML = `
                <div class="test-result ${statusClass} mt-3">
                    <h6>Overall Security Status: ${data.security_status}</h6>
                    ${data.active_vulnerabilities.length > 0 ? 
                        `<p><strong>Active Vulnerabilities:</strong></p>${vulnerabilitiesList}` : 
                        '<p>All security protections are active.</p>'
                    }
                </div>
            `;
        }

        // Open clickjacking demo
        function openClickjackingDemo() {
            window.open('/security/clickjacking-demo', '_blank');
        }
    </script>
</body>
</html>
