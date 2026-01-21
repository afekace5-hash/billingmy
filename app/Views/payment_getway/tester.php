<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="bx bx-test-tube me-2"></i>Payment Gateway Tester</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('payment') ?>">Payment</a></li>
                            <li class="breadcrumb-item active">Tester</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="alert-heading mb-2"><i class="bx bx-info-circle me-2"></i>Payment Testing Environment</h6>
                            <p class="mb-0">This page allows you to test your payment gateway integrations safely. All transactions are in test mode.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= site_url('payment/setup-guide') ?>" class="btn btn-outline-primary">
                                <i class="bx bx-cog me-1"></i>Setup Guide
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Payment Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-credit-card me-2"></i>Create Test Payment
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Gateway Status Alert -->
                        <div id="gatewayAlert" class="alert alert-warning" style="display: none;">
                            <i class="bx bx-exclamation-triangle me-2"></i>
                            <span id="gatewayAlertText"></span>
                        </div>

                        <!-- Available Payment Methods -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bx bx-info-circle me-2"></i>Available Payment Methods</h6>
                            <div id="paymentMethods">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    <span>Loading payment methods...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form id="paymentTestForm">
                            <?= csrf_field() ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="customer_name" value="John Doe" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="customer_email" value="john.doe@example.com" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Phone</label>
                                        <input type="tel" class="form-control" name="customer_phone" value="08123456789">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Amount (IDR) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="amount" value="100000" min="10000" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="2" placeholder="Payment description...">Test payment for internet service - <?= date('Y-m-d H:i:s') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Gateway</label>
                                        <select class="form-select" name="gateway" id="gatewaySelect">
                                            <option value="">Auto Select</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method</label>
                                        <select class="form-select" name="method" id="methodSelect">
                                            <option value="auto">Auto Select</option>
                                            <option value="qris">QRIS</option>
                                            <option value="bca_va">Virtual Account BCA</option>
                                            <option value="bni_va">Virtual Account BNI</option>
                                            <option value="bri_va">Virtual Account BRI</option>
                                            <option value="mandiri_va">Virtual Account Mandiri</option>
                                            <option value="ovo">OVO</option>
                                            <option value="dana">DANA</option>
                                            <option value="gopay">GoPay</option>
                                            <option value="shopeepay">ShopeePay</option>
                                            <option value="credit_card">Credit Card</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Test Scenario</label>
                                        <select class="form-select" id="testScenario">
                                            <option value="normal">Normal Payment</option>
                                            <option value="small_amount">Small Amount (Rp 15,000)</option>
                                            <option value="large_amount">Large Amount (Rp 5,000,000)</option>
                                            <option value="special_chars">Special Characters in Name</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-credit-card me-1"></i>Create Test Payment
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="refreshMethods()">
                                    <i class="bx bx-refresh me-1"></i>Refresh Methods
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="testConnections()">
                                    <i class="bx bx-test-tube me-1"></i>Test Connections
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Gateway Status & Info -->
            <div class="col-lg-4">
                <!-- Gateway Status -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bx bx-shield-check me-2"></i>Gateway Status</h6>
                    </div>
                    <div class="card-body">
                        <div id="gatewayStatus">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span>Loading status...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bx bx-clipboard-check me-2"></i>Last Test Results</h6>
                    </div>
                    <div class="card-body">
                        <div id="testResults">
                            <p class="text-muted mb-0">No tests run yet. Click "Create Test Payment" to start testing.</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bx bx-zap me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="loadSampleData('internet_bill')">
                                <i class="bx bx-wifi me-1"></i>Load Internet Bill Sample
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="loadSampleData('subscription')">
                                <i class="bx bx-calendar me-1"></i>Load Subscription Sample
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="loadSampleData('one_time')">
                                <i class="bx bx-shopping-cart me-1"></i>Load One-time Payment
                            </button>
                            <hr>
                            <a href="<?= site_url('cek-tagihan') ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="bx bx-external-link me-1"></i>Test Public Billing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Instructions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Testing Instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Xendit Test Cards:</h6>
                                <ul class="list-unstyled">
                                    <li><code>4000000000000002</code> - Successful payment</li>
                                    <li><code>4000000000000010</code> - Declined payment</li>
                                    <li><code>4000000000000028</code> - Expired card</li>
                                </ul>

                                <h6 class="mt-3">Test Virtual Account:</h6>
                                <ul class="list-unstyled">
                                    <li>All VA numbers in test mode will be simulated</li>
                                    <li>Check webhook logs for payment status updates</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>E-wallet Testing:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>OVO:</strong> Use any phone number in test mode</li>
                                    <li><strong>DANA:</strong> Test mode generates QR codes</li>
                                    <li><strong>GoPay:</strong> QR code simulation available</li>
                                </ul>

                                <h6 class="mt-3">Monitoring:</h6>
                                <ul class="list-unstyled">
                                    <li>Check <code>writable/logs/</code> for error logs</li>
                                    <li>Monitor payment webhook callbacks</li>
                                    <li>Verify database updates in payment tables</li>
                                </ul>
                            </div>
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
        loadPaymentMethods();
        loadGatewayStatus();

        // Test scenario change handler
        $('#testScenario').on('change', function() {
            applyTestScenario($(this).val());
        });
    });

    function loadPaymentMethods() {
        $('#paymentMethods').html('<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span>Loading payment methods...</span></div>');

        $.ajax({
            url: '<?= site_url('payment/methods') ?>',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '<div class="row g-2">';
                    let gatewayOptions = '<option value="">Auto Select</option>';

                    Object.keys(response.data).forEach(gateway => {
                        const gatewayData = response.data[gateway];
                        html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded p-2 text-center">
                                <div class="fw-medium">${gatewayData.name}</div>
                                <small class="text-success">${gatewayData.methods.length} methods</small>
                            </div>
                        </div>
                    `;
                        gatewayOptions += `<option value="${gateway}">${gatewayData.name}</option>`;
                    });

                    html += '</div>';
                    $('#paymentMethods').html(html);
                    $('#gatewaySelect').html(gatewayOptions);

                    $('#gatewayAlert').hide();
                } else {
                    $('#paymentMethods').html('<span class="text-warning">No payment methods available</span>');
                    $('#gatewayAlert').show().find('#gatewayAlertText').text('No payment gateways are currently active. Please configure at least one gateway.');
                }
            },
            error: function() {
                $('#paymentMethods').html('<span class="text-danger">Failed to load payment methods</span>');
                $('#gatewayAlert').show().find('#gatewayAlertText').text('Error loading payment methods. Check your gateway configuration.');
            }
        });
    }

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
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bx ${statusIcon} ${statusClass} me-2"></i>
                                <div>
                                    <small class="fw-medium">${gateway.gateway_name}</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">${gateway.environment}</div>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">${statusText}</span>
                        </div>
                    `;
                    });
                    $('#gatewayStatus').html(html);
                }
            },
            error: function() {
                $('#gatewayStatus').html('<div class="text-danger">Failed to load status</div>');
            }
        });
    }

    function refreshMethods() {
        loadPaymentMethods();
        loadGatewayStatus();
    }

    function testConnections() {
        Swal.fire({
            title: 'Testing Gateway Connections...',
            html: 'Checking all configured payment gateways',
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
                        showConnectionResults(results);
                    }
                }
            });
        });
    }

    function showConnectionResults(results) {
        let html = '<div class="text-start">';
        results.forEach(result => {
            const icon = result.success ? 'check-circle text-success' : 'x-circle text-danger';
            html += `
            <div class="d-flex align-items-start mb-2">
                <i class="bx bx-${icon} me-2 mt-1"></i>
                <div>
                    <strong>${result.gateway.toUpperCase()}</strong><br>
                    <small class="text-muted">${result.message}</small>
                </div>
            </div>
        `;
        });
        html += '</div>';

        // Update test results card
        $('#testResults').html(html);

        Swal.fire({
            title: 'Connection Test Results',
            html: html,
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    function applyTestScenario(scenario) {
        switch (scenario) {
            case 'small_amount':
                $('input[name="amount"]').val(15000);
                $('input[name="customer_name"]').val('Small Payment Test');
                $('textarea[name="description"]').val('Test small amount payment - Rp 15,000');
                break;
            case 'large_amount':
                $('input[name="amount"]').val(5000000);
                $('input[name="customer_name"]').val('Large Payment Test');
                $('textarea[name="description"]').val('Test large amount payment - Rp 5,000,000');
                break;
            case 'special_chars':
                $('input[name="customer_name"]').val('Testér Spëcial Chårs');
                $('input[name="customer_email"]').val('test.special@example.com');
                $('textarea[name="description"]').val('Payment with special characters: ñáéíóú & symbols @#$%');
                break;
            default:
                $('input[name="amount"]').val(100000);
                $('input[name="customer_name"]').val('John Doe');
                $('input[name="customer_email"]').val('john.doe@example.com');
                $('textarea[name="description"]').val('Test payment for internet service - <?= date('Y-m-d H:i:s') ?>');
        }
    }

    function loadSampleData(type) {
        switch (type) {
            case 'internet_bill':
                $('input[name="customer_name"]').val('Internet Customer');
                $('input[name="customer_email"]').val('customer@internet.com');
                $('input[name="customer_phone"]').val('08123456789');
                $('input[name="amount"]').val(350000);
                $('textarea[name="description"]').val('Tagihan Internet Bulan ' + new Date().toLocaleDateString('id-ID', {
                    month: 'long',
                    year: 'numeric'
                }));
                $('select[name="method"]').val('bca_va');
                break;
            case 'subscription':
                $('input[name="customer_name"]').val('Subscription Customer');
                $('input[name="customer_email"]').val('subscriber@example.com');
                $('input[name="amount"]').val(150000);
                $('textarea[name="description"]').val('Monthly Subscription Fee - Premium Package');
                $('select[name="method"]').val('ovo');
                break;
            case 'one_time':
                $('input[name="customer_name"]').val('One-time Customer');
                $('input[name="customer_email"]').val('onetime@example.com');
                $('input[name="amount"]').val(75000);
                $('textarea[name="description"]').val('One-time service charge');
                $('select[name="method"]').val('dana');
                break;
        }
    }

    // Payment form submission
    $('#paymentTestForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Creating Payment...').prop('disabled', true);

        $.ajax({
            url: '<?= site_url('payment/createInvoice') ?>',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Update test results
                    $('#testResults').html(`
                    <div class="alert alert-success">
                        <strong>✓ Payment Created Successfully</strong><br>
                        <small>Transaction ID: ${response.transaction_id || 'N/A'}</small>
                    </div>
                `);

                    if (response.payment_url) {
                        Swal.fire({
                            title: 'Payment Created!',
                            text: 'Do you want to open the payment page?',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Open Payment Page',
                            cancelButtonText: 'Stay Here'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(response.payment_url, '_blank');
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Payment Created!',
                            text: response.message || 'Payment has been created successfully',
                            icon: 'success'
                        });
                    }
                } else {
                    $('#testResults').html(`
                    <div class="alert alert-danger">
                        <strong>✗ Payment Failed</strong><br>
                        <small>${response.message || 'Unknown error occurred'}</small>
                    </div>
                `);

                    Swal.fire({
                        title: 'Payment Failed',
                        text: response.message || 'Failed to create payment',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                $('#testResults').html(`
                <div class="alert alert-danger">
                    <strong>✗ System Error</strong><br>
                    <small>HTTP ${xhr.status}: ${xhr.statusText}</small>
                </div>
            `);

                Swal.fire({
                    title: 'System Error',
                    text: 'A system error occurred while creating the payment',
                    icon: 'error'
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
</script>
<?= $this->endSection() ?>