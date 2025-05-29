<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOMMSS Security Testing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { margin-top: 15px; padding: 15px; border-radius: 5px; }
        .protected { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .warning-banner { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="warning-banner">
            <h5>⚠️ Security Testing Mode Active</h5>
            <p><strong>WARNING:</strong> This is for demonstration purposes only!</p>
        </div>

        <h1>HOMMSS Security Testing Dashboard</h1>
        <p class="lead">Demonstrate security protections against common attacks</p>

        <!-- XSS Testing -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>XSS (Cross-Site Scripting) Protection Test</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" id="xss-payload" class="form-control" 
                               value="&lt;script&gt;alert('XSS Test')&lt;/script&gt;" 
                               placeholder="Enter XSS payload">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-danger" onclick="testXSS()">Test XSS Protection</button>
                    </div>
                </div>
                <div id="xss-result"></div>
            </div>
        </div>

        <!-- SQL Injection Testing -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>SQL Injection Protection Test</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" id="sql-payload" class="form-control" 
                               value="1' OR '1'='1" 
                               placeholder="Enter SQL injection payload">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-warning" onclick="testSQL()">Test SQL Protection</button>
                    </div>
                </div>
                <div id="sql-result"></div>
            </div>
        </div>

        <!-- Security Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Security Status</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-info" onclick="getStatus()">Get Security Status</button>
                <div id="status-result"></div>
            </div>
        </div>

        <!-- Security Information -->
        <div class="card">
            <div class="card-header">
                <h5>Security Protections Implemented</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>XSS Protection:</h6>
                        <ul>
                            <li>htmlspecialchars() output encoding</li>
                            <li>Content Security Policy headers</li>
                            <li>Blade template auto-escaping</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>SQL Injection Protection:</h6>
                        <ul>
                            <li>Laravel Eloquent ORM</li>
                            <li>Prepared statements</li>
                            <li>Parameter binding</li>
                        </ul>
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
            
            fetch('/security/xss-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ payload: payload })
            })
            .then(response => response.json())
            .then(data => {
                displayResult('xss-result', data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('xss-result').innerHTML = 
                    '<div class="test-result protected">Error testing XSS protection</div>';
            });
        }

        // SQL Testing
        function testSQL() {
            const payload = document.getElementById('sql-payload').value;
            
            fetch('/security/sql-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ input: payload })
            })
            .then(response => response.json())
            .then(data => {
                displayResult('sql-result', data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('sql-result').innerHTML = 
                    '<div class="test-result protected">Error testing SQL protection</div>';
            });
        }

        // Get Status
        function getStatus() {
            fetch('/security/status')
            .then(response => response.json())
            .then(data => {
                displayStatus(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Display test results
        function displayResult(elementId, data) {
            const element = document.getElementById(elementId);
            
            element.innerHTML = `
                <div class="test-result protected">
                    <h6>Status: ${data.status}</h6>
                    <p>${data.message}</p>
                    <small><strong>Protection:</strong> ${data.protection}</small>
                </div>
            `;
        }

        // Display status
        function displayStatus(data) {
            const element = document.getElementById('status-result');
            
            let protectionsList = '';
            for (const [key, value] of Object.entries(data.protections)) {
                protectionsList += `<li><strong>${key.replace('_', ' ')}:</strong> ${value}</li>`;
            }
            
            element.innerHTML = `
                <div class="test-result protected mt-3">
                    <h6>Security Status: ${data.security_status}</h6>
                    <p>${data.message}</p>
                    <ul>${protectionsList}</ul>
                </div>
            `;
        }
    </script>
</body>
</html>
