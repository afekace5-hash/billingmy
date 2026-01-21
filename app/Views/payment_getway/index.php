<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Payment Gateway Demo</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Payment</a></li>
                            <li class="breadcrumb-item active">Demo</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>


        <!-- Gateway Status -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Payment Gateway Status</h5>
                        <button type="button" class="btn btn-outline-info btn-sm" id="btnTestConnection" onclick="testGatewayConnection()">
                            <i class="bx bx-plug me-1"></i>Test Koneksi
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-2 d-block d-md-none">
                            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="testGatewayConnection()">
                                <i class="bx bx-plug me-1"></i>Test Koneksi
                            </button>
                        </div>
                        <div id="gatewayStatus">Loading gateway status...</div>
                        <div id="testConnectionResult" class="mt-3"></div>
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
    });

    function loadPaymentMethods() {
        $('#paymentMethods').html('<i class="bx bx-loader-alt bx-spin me-2"></i>Loading...');

        $.ajax({
            url: '<?= site_url('payment/methods') ?>',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '<div class="row">';

                    Object.keys(response.data).forEach(gateway => {
                        const gatewayData = response.data[gateway];
                        html += `
                        <div class="col-md-4 mb-2">
                            <div class="border rounded p-2">
                                <h6 class="mb-1">${gatewayData.name}</h6>
                                <small class="text-muted">${gatewayData.methods.length} methods available</small>
                            </div>
                        </div>
                    `;
                    });

                    html += '</div>';
                    $('#paymentMethods').html(html);
                } else {
                    $('#paymentMethods').html('<span class="text-warning">No payment methods available</span>');
                }
            },
            error: function() {
                $('#paymentMethods').html('<span class="text-danger">Failed to load payment methods</span>');
            }
        });
    }

    function loadGatewayStatus() {
        $('#gatewayStatus').html('<i class="bx bx-loader-alt bx-spin me-2"></i>Loading...');
        $.ajax({
            url: '<?= site_url('payment/gateway-status') ?>',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '<div class="row">';
                    response.data.forEach(gateway => {
                        const statusClass = gateway.is_active ? 'success' : 'secondary';
                        const statusText = gateway.is_active ? 'Active' : 'Inactive';
                        html += `
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h6 class="card-title">${gateway.gateway_name}</h6>
                                    <span class="badge bg-${statusClass}">${statusText}</span>
                                    <div class="mt-2">
                                        <small class="text-muted">${gateway.environment}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                    html += '</div>';
                    $('#gatewayStatus').html(html);
                } else {
                    $('#gatewayStatus').html('<span class="text-warning">No gateways configured</span>');
                }
            },
            error: function() {
                $('#gatewayStatus').html('<span class="text-danger">Failed to load gateway status</span>');
            }
        });
    }

    function testGatewayConnection() {
        // Update both test result areas
        const loadingHtml = '<i class="bx bx-loader-alt bx-spin me-2"></i>Testing connection...';
        $('#testConnectionResult').html(loadingHtml);
        $('#quickTestResult').html(loadingHtml).show();

        $.ajax({
            url: '<?= site_url('payment/test-connection') ?>',
            type: 'GET',
            success: function(response) {
                let html = '';
                if (response.success) {
                    html = '<div class="alert alert-success mb-0">';
                    html += '<i class="bx bx-check-circle me-2"></i>' + (response.message || 'Koneksi ke gateway berhasil!');
                    html += '</div>';
                } else {
                    html = '<div class="alert alert-warning mb-0">';
                    html += '<i class="bx bx-error me-2"></i>' + (response.message || 'Koneksi gagal.');
                    html += '</div>';
                }
                $('#testConnectionResult').html(html);
                $('#quickTestResult').html(html).show();
            },
            error: function() {
                const errorHtml = '<div class="alert alert-danger mb-0"><i class="bx bx-x-circle me-2"></i>Gagal menghubungi server.</div>';
                $('#testConnectionResult').html(errorHtml);
                $('#quickTestResult').html(errorHtml).show();
            }
        });
    }
</script>
<?= $this->endSection() ?>