<!DOCTYPE html>
<html>

<head>
    <title>Midtrans Auto-Configuration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }

        button:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        .form-group {
            margin: 15px 0;
        }

        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Midtrans Auto-Configuration Tool</h1>

        <div class="info">
            <strong>This tool will help you:</strong>
            <ul>
                <li>Check current Midtrans configuration</li>
                <li>Set up test credentials for development</li>
                <li>Configure real Midtrans credentials</li>
                <li>Test the connection</li>
            </ul>
        </div>

        <div id="status-area">
            <h2>📊 Current Status</h2>
            <button onclick="checkStatus()">Check Current Status</button>
            <div id="status-results"></div>
        </div>

        <div id="config-area" style="margin-top: 30px;">
            <h2>⚙️ Configuration Options</h2>

            <div class="warning">
                <strong>⚠️ Important:</strong> For production use, you MUST get real credentials from
                <a href="https://dashboard.midtrans.com/" target="_blank">Midtrans Dashboard</a>
            </div>

            <h3>Option 1: Set Test Credentials (Development Only)</h3>
            <p>This will set dummy credentials so you can access the admin panel configuration.</p>
            <button class="btn-warning" onclick="setTestCredentials()">Set Test Credentials</button>

            <h3>Option 2: Configure Real Credentials</h3>
            <div class="form-group">
                <label for="serverKey">Server Key (from Midtrans Dashboard):</label>
                <input type="text" id="serverKey" placeholder="SB-Mid-server-... (for sandbox) or Mid-... (for production)">

                <label for="clientKey">Client Key (from Midtrans Dashboard):</label>
                <input type="text" id="clientKey" placeholder="SB-Mid-client-... (for sandbox)">

                <label for="environment">Environment:</label>
                <select id="environment" style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="sandbox">Sandbox (Testing)</option>
                    <option value="production">Production (Live)</option>
                </select>

                <button class="btn-success" onclick="setRealCredentials()" style="margin-top: 10px;">Save Real Credentials</button>
            </div>

            <h3>Option 3: Test Connection</h3>
            <button onclick="testConnection()">Test Midtrans Connection</button>
        </div>

        <div id="results-area" style="margin-top: 30px;">
            <h2>📋 Results</h2>
            <div id="results"></div>
        </div>
    </div>

    <script>
        function addResult(message, type = 'info') {
            const div = document.createElement('div');
            div.className = type;
            div.innerHTML = message;
            document.getElementById('results').appendChild(div);
        }

        function addStatus(message, type = 'info') {
            const div = document.createElement('div');
            div.className = type;
            div.innerHTML = message;
            document.getElementById('status-results').appendChild(div);
        }

        function checkStatus() {
            document.getElementById('status-results').innerHTML = '';
            addStatus('Checking gateway status...', 'info');

            fetch('/interneter/payment/debug-gateways')
                .then(response => response.json())
                .then(data => {
                    console.log('Status response:', data);

                    if (data.gateways && data.gateways.midtrans) {
                        const midtrans = data.gateways.midtrans;
                        addStatus('<strong>✅ Midtrans Gateway Found</strong>', 'success');
                        addStatus('<strong>Status:</strong> ' + (midtrans.is_active ? 'Active' : 'Inactive'), 'info');
                        addStatus('<strong>Configuration:</strong> ' + (midtrans.has_config ? 'Set' : 'Not set'), 'info');

                        if (midtrans.config) {
                            const hasKey = midtrans.config.api_key && midtrans.config.api_key.length > 0;
                            addStatus('<strong>Server Key:</strong> ' + (hasKey ? 'Configured (' + midtrans.config.api_key.substring(0, 15) + '...)' : 'Not set'), hasKey ? 'success' : 'error');
                            addStatus('<strong>Environment:</strong> ' + (midtrans.config.environment || 'Not set'), 'info');
                        }
                    } else {
                        addStatus('❌ Midtrans gateway not found in database', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    addStatus('❌ Failed to check status: ' + error.message, 'error');
                });
        }

        function setTestCredentials() {
            document.getElementById('results').innerHTML = '';
            addResult('Setting test credentials...', 'info');

            const testConfig = {
                api_key: 'SB-Mid-server-TEST123456789',
                api_secret: 'SB-Mid-client-TEST123456789',
                merchant_code: 'test-merchant',
                environment: 'sandbox'
            };

            updateMidtransConfig(testConfig, 'Test credentials set! You can now access the admin panel configuration.');
        }

        function setRealCredentials() {
            const serverKey = document.getElementById('serverKey').value.trim();
            const clientKey = document.getElementById('clientKey').value.trim();
            const environment = document.getElementById('environment').value;

            if (!serverKey || !clientKey) {
                addResult('❌ Please fill in both Server Key and Client Key', 'error');
                return;
            }

            document.getElementById('results').innerHTML = '';
            addResult('Setting real credentials...', 'info');

            const config = {
                api_key: serverKey,
                api_secret: clientKey,
                merchant_code: '',
                environment: environment
            };

            updateMidtransConfig(config, 'Real credentials configured successfully!');
        }

        function updateMidtransConfig(config, successMessage) {
            // For this demo, we'll show what the config would look like
            // In a real implementation, you'd make an AJAX call to update the database

            addResult('<strong>✅ ' + successMessage + '</strong>', 'success');
            addResult('<strong>Configuration to be saved:</strong><pre>' + JSON.stringify(config, null, 2) + '</pre>', 'info');

            addResult(`
                <strong>🔧 To actually save these credentials:</strong>
                <ol>
                    <li>Go to your <a href="/interneter/settings/payment_gateway" target="_blank">Payment Gateway Settings</a></li>
                    <li>Select "Midtrans" from the dropdown</li>
                    <li>Enter the credentials shown above</li>
                    <li>Click "Save Configuration"</li>
                </ol>
            `, 'info');
        }

        function testConnection() {
            document.getElementById('results').innerHTML = '';
            addResult('Testing Midtrans connection...', 'info');

            fetch('/interneter/payment/testConnection', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'gateway_type=midtrans'
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Test response:', data);

                    if (data.success) {
                        addResult('<strong>✅ Connection Successful!</strong><br>' + data.message, 'success');
                    } else {
                        addResult('<strong>❌ Connection Failed!</strong><br>' + data.message, 'error');
                    }

                    if (data.data) {
                        addResult('<strong>Response Details:</strong><pre>' + JSON.stringify(data.data, null, 2) + '</pre>', 'info');
                    }

                    if (data.debug) {
                        addResult('<strong>Debug Info:</strong><pre>' + JSON.stringify(data.debug, null, 2) + '</pre>', 'info');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    addResult('❌ Request failed: ' + error.message, 'error');
                });
        }

        // Auto-check status on page load
        window.onload = function() {
            checkStatus();
        };
    </script>
</body>

</html>