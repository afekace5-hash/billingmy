<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="bx bx-cog me-2"></i>Payment Gateway Setup Guide</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('payment') ?>">Payment</a></li>
                            <li class="breadcrumb-item active">Setup Guide</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="text-white mb-2">Quick Setup Actions</h5>
                                <p class="text-white-50 mb-0">Complete these steps to get your payment system running</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <a href="<?= site_url('settings/payment-gateway-simple') ?>" class="btn btn-light">
                                        <i class="bx bx-cog me-1"></i>Configure Gateways
                                    </a>
                                    <a href="<?= site_url('payment') ?>" class="btn btn-outline-light">
                                        <i class="bx bx-test-tube me-1"></i>Test Payments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Steps -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-list-check me-2"></i>Setup Checklist</h5>
                    </div>
                    <div class="card-body">
                        <!-- Step 1: Database -->
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-success rounded-circle">
                                        <i class="bx bx-check text-white"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Database Setup</h6>
                                <p class="text-muted mb-0">✅ Payment gateway tables created successfully</p>
                                <small class="text-success">Already completed - payment_gateways table exists</small>
                            </div>
                        </div>

                        <!-- Step 2: Gateway Configuration -->
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-warning rounded-circle">
                                        <span class="fw-bold text-white">2</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Configure Payment Gateways</h6>
                                <p class="text-muted mb-2">Add your payment gateway credentials to start accepting payments</p>
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Next Step:</strong> Configure Xendit (you already have API key in .env file)
                                </div>
                                <a href="<?= site_url('settings/payment-gateway-simple') ?>" class="btn btn-primary btn-sm">
                                    <i class="bx bx-cog me-1"></i>Configure Now
                                </a>
                            </div>
                        </div>

                        <!-- Step 3: Test Connection -->
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-secondary rounded-circle">
                                        <span class="fw-bold text-white">3</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Test Gateway Connection</h6>
                                <p class="text-muted mb-2">Verify that your payment gateways are working correctly</p>
                                <button class="btn btn-outline-primary btn-sm" onclick="testAllConnections()">
                                    <i class="bx bx-test-tube me-1"></i>Test All Connections
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Live Testing -->
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-secondary rounded-circle">
                                        <span class="fw-bold text-white">4</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Test Payment Flow</h6>
                                <p class="text-muted mb-2">Create test payments to ensure end-to-end functionality</p>
                                <a href="<?= site_url('payment') ?>" class="btn btn-outline-success btn-sm">
                                    <i class="bx bx-credit-card me-1"></i>Create Test Payment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gateway Status Panel -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-shield-check me-2"></i>Gateway Status</h5>
                    </div>
                    <div class="card-body">
                        <div id="gatewayStatusList">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading gateway status...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Configuration -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-zap me-2"></i>Quick Configure Xendit</h5>
                    </div>
                    <div class="card-body">
                        <form id="quickXenditForm">
                            <div class="mb-3">
                                <label class="form-label">Secret Key</label>
                                <input type="password" class="form-control" id="xendit_secret"
                                    placeholder="xnd_development_..." value="xnd_development_1rDfkEcGCc5A5xSRSeMg66TgWplNqPipqw4TZni7FIVdSe6JbjcCNFt3k6DNmcw">
                                <div class="form-text">From your Xendit dashboard</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Environment</label>
                                <select class="form-select" id="xendit_env">
                                    <option value="test">Test/Development</option>
                                    <option value="production">Production</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-save me-1"></i>Quick Configure
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gateway Comparison -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i>Payment Gateway Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gateway</th>
                                        <th>Best For</th>
                                        <th>Fee Range</th>
                                        <th>Payment Methods</th>
                                        <th>Integration</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-credit-card text-success me-2"></i>
                                                <strong>Xendit</strong>
                                            </div>
                                        </td>
                                        <td>Startups, SMEs</td>
                                        <td>2.9% + Rp 2,000</td>
                                        <td>VA, E-wallet, Card</td>
                                        <td><span class="badge bg-success">Easy</span></td>
                                        <td>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureGateway('xendit')">
                                                Configure
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-credit-card text-info me-2"></i>
                                                <strong>Midtrans</strong>
                                            </div>
                                        </td>
                                        <td>E-commerce</td>
                                        <td>2.9% + Rp 2,000</td>
                                        <td>Full Payment Suite</td>
                                        <td><span class="badge bg-success">Easy</span></td>
                                        <td>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureGateway('midtrans')">
                                                Configure
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-credit-card text-primary me-2"></i>

                                            </div>
                                        </td>
                                        <td>Local Business</td>
                                        <td>1.5% - 3%</td>
                                        <td>Local Banks, E-wallet</td>
                                        <td><span class="badge bg-warning">Medium</span></td>
                                        <td>

                                            Configure
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-credit-card text-warning me-2"></i>

                                            </div>
                                        </td>
                                        <td>Enterprise</td>
                                        <td>Custom Rates</td>
                                        <td>Banking Solutions</td>
                                        <td><span class="badge bg-warning">Medium</span></td>
                                        <td>

                                            Configure
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-credit-card text-danger me-2"></i>
                                                <strong>Duitku</strong>
                                            </div>
                                        </td>
                                        <td>Local Market</td>
                                        <td>1.2% - 2.5%</td>
                                        <td>Local Focus</td>
                                        <td><span class="badge bg-success">Easy</span></td>
                                        <td>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureGateway('duitku')">
                                                Configure
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        loadGatewayStatus();
    });

    function loadGatewayStatus() {
        $.ajax({
            url: '<?= site_url('payment/gateway-status') ?>',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    response.data.forEach(gateway => {
                        const isActive = gateway.is_active == 1;
                        const hasConfig = gateway.api_key && gateway.api_key.trim() !== '';

                        let statusIcon, statusText, statusClass;
                        if (isActive && hasConfig) {
                            statusIcon = 'bx-check-circle';
                            statusText = 'Active';
                            statusClass = 'text-success';
                        } else if (hasConfig) {
                            statusIcon = 'bx-info-circle';
                            statusText = 'Configured';
                            statusClass = 'text-warning';
                        } else {
                            statusIcon = 'bx-x-circle';
                            statusText = 'Not Configured';
                            statusClass = 'text-muted';
                        }

                        html += `
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bx ${statusIcon} ${statusClass} me-2"></i>
                                <div>
                                    <div class="fw-medium">${gateway.gateway_name}</div>
                                    <small class="text-muted">${gateway.environment}</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">${statusText}</span>
                        </div>
                    `;
                    });
                    $('#gatewayStatusList').html(html);
                }
            },
            error: function() {
                $('#gatewayStatusList').html('<div class="text-danger">Failed to load status</div>');
            }
        });
    }

    function testAllConnections() {
        Swal.fire({
            title: 'Testing Connections...',
            html: 'Testing all configured payment gateways',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const gateways = ['xendit', 'midtrans', 'duitku'];
        let results = [];
        let completed = 0;

        gateways.forEach(gateway => {
            $.ajax({
                url: '<?= site_url('payment/test-connection') ?>',
                type: 'POST',
                data: {
                    gateway: gateway,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    results.push({
                        gateway: gateway,
                        success: response.success,
                        message: response.message
                    });
                },
                error: function() {
                    results.push({
                        gateway: gateway,
                        success: false,
                        message: 'Connection failed'
                    });
                },
                complete: function() {
                    completed++;
                    if (completed === gateways.length) {
                        showTestResults(results);
                    }
                }
            });
        });
    }

    function showTestResults(results) {
        let html = '<div class="text-start">';
        results.forEach(result => {
            const icon = result.success ? 'check-circle text-success' : 'x-circle text-danger';
            html += `
            <div class="d-flex align-items-center mb-2">
                <i class="bx bx-${icon} me-2"></i>
                <div>
                    <strong>${result.gateway.toUpperCase()}</strong><br>
                    <small>${result.message}</small>
                </div>
            </div>
        `;
        });
        html += '</div>';

        Swal.fire({
            title: 'Connection Test Results',
            html: html,
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    function configureGateway(gateway) {
        if (gateway === 'midtrans') {
            // Show notification URL that needs to be configured in Midtrans dashboard
            Swal.fire({
                icon: 'info',
                title: 'Midtrans Setup Required',
                html: `
                    <div class="text-left">
                        <p><strong>Before configuring Midtrans, please setup the following in your Midtrans Dashboard:</strong></p>
                        <hr>
                        <p><strong>Payment Notification URL:</strong></p>
                        <code style="background: #f8f9fa; padding: 5px; border-radius: 3px; display: block; margin: 5px 0;">
                            <?= base_url('payment/callback/midtrans') ?>
                        </code>
                        <hr>
                        <p><small><strong>Steps:</strong><br>
                        1. Login to Midtrans Dashboard<br>
                        2. Go to Settings > Configuration<br>
                        3. Set Payment Notification URL<br>
                        4. Click Update<br>
                        5. Then continue with configuration here</small></p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Continue to Config',
                cancelButtonText: 'Copy URL & Cancel',
                width: 600
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= site_url('settings/payment-gateway-simple') ?>?gateway=' + gateway;
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Copy URL to clipboard
                    navigator.clipboard.writeText('<?= base_url('payment/callback/midtrans') ?>').then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'URL Copied!',
                            text: 'Notification URL has been copied to clipboard',
                            timer: 2000
                        });
                    });
                }
            });
        } else {
            window.location.href = '<?= site_url('settings/payment-gateway-simple') ?>?gateway=' + gateway;
        }
    }

    // Quick Xendit configuration
    $('#quickXenditForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('gateway_type', 'xendit');
        formData.append('gateway_name', 'Xendit');
        formData.append('is_active', '1');
        formData.append('api_key', $('#xendit_secret').val());
        formData.append('environment', $('#xendit_env').val());
        formData.append('webhook_url', '<?= base_url('payment/callback/xendit') ?>');

        $.ajax({
            url: '<?= site_url('settings/payment-getway') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Xendit configured successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadGatewayStatus();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to configure Xendit'
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>