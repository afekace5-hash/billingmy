<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Payment Callback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Test Payment Callback</h2>
                <p class="text-muted">Tool untuk testing apakah callback payment bekerja dengan baik</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Step 1: Create Test Invoice</h5>
                    </div>
                    <div class="card-body">
                        <p>Buat invoice testing untuk simulate pembayaran</p>
                        <button class="btn btn-primary" onclick="createTestInvoice()">Create Test Invoice</button>
                        <div id="invoice-result" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Step 2: Simulate Payment</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Order ID (dari step 1)</label>
                            <input type="text" class="form-control" id="order-id" placeholder="TEST-INV-xxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-control" id="payment-status">
                                <option value="settlement">Settlement (Success)</option>
                                <option value="pending">Pending</option>
                                <option value="cancel">Cancel</option>
                                <option value="expire">Expire</option>
                            </select>
                        </div>
                        <button class="btn btn-success" onclick="simulatePayment()">Simulate Payment</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Step 3: Check Result</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-info" onclick="checkInvoiceStatus()">Check Invoice Status</button>
                        <div id="status-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Callback Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Callback URL:</strong></p>
                        <code><?= base_url('payment/callback/midtrans') ?></code>

                        <p class="mt-3"><strong>Test notification URL:</strong></p>
                        <code><?= base_url('test-midtrans-callback.php') ?></code>

                        <p class="mt-3"><strong>Setup Guide:</strong></p>
                        <p>Di Midtrans dashboard, set Payment Notification URL ke: <code><?= base_url('payment/callback/midtrans') ?></code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        let currentOrderId = '';

        function createTestInvoice() {
            fetch('<?= site_url('test-payment-callback/create-test-invoice') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentOrderId = data.data.order_id;
                        document.getElementById('order-id').value = currentOrderId;

                        document.getElementById('invoice-result').innerHTML = `
                        <div class="alert alert-success">
                            <h6>Test Invoice Created:</h6>
                            <p><strong>Order ID:</strong> ${data.data.order_id}</p>
                            <p><strong>Customer:</strong> ${data.data.customer_name}</p>
                            <p><strong>Amount:</strong> Rp ${data.data.amount.toLocaleString('id-ID')}</p>
                        </div>
                    `;
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to create test invoice', 'error');
                });
        }

        function simulatePayment() {
            const orderId = document.getElementById('order-id').value;
            const status = document.getElementById('payment-status').value;

            if (!orderId) {
                Swal.fire('Error', 'Please enter Order ID', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);

            fetch('<?= site_url('test-payment-callback/simulate-callback') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', 'Payment callback simulated successfully!', 'success');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                    console.log('Callback response:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to simulate payment', 'error');
                });
        }

        function checkInvoiceStatus() {
            const orderId = document.getElementById('order-id').value;

            if (!orderId) {
                Swal.fire('Error', 'Please enter Order ID', 'error');
                return;
            }

            fetch(`<?= site_url('test-payment-callback/check-invoice-status') ?>?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const invoice = data.invoice;
                        const transaction = data.payment_transaction;

                        let statusClass = invoice.status === 'paid' ? 'success' : 'warning';

                        document.getElementById('status-result').innerHTML = `
                        <div class="alert alert-${statusClass}">
                            <h6>Invoice Status:</h6>
                            <p><strong>Status:</strong> ${invoice.status}</p>
                            <p><strong>Payment Date:</strong> ${invoice.payment_date || 'Not paid yet'}</p>
                            <p><strong>Amount:</strong> Rp ${parseInt(invoice.bill).toLocaleString('id-ID')}</p>
                            <p><strong>Gateway:</strong> ${invoice.payment_gateway || 'N/A'}</p>
                            <p><strong>Method:</strong> ${invoice.payment_method || 'N/A'}</p>
                            ${transaction ? `
                                <hr>
                                <h6>Payment Transaction Record:</h6>
                                <p><strong>Transaction Status:</strong> ${transaction.status}</p>
                                <p><strong>Payment Code:</strong> ${transaction.payment_code}</p>
                                <p><strong>Channel:</strong> ${transaction.channel}</p>
                            ` : '<p><em>No payment transaction record found</em></p>'}
                        </div>
                    `;
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to check invoice status', 'error');
                });
        }
    </script>
</body>

</html>