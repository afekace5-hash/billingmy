<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contoh Implementasi Flip Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-method-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .payment-method-card.selected {
            border-color: #007bff;
            background-color: #f0f8ff;
        }

        .admin-fee {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Contoh Implementasi Flip Payment Gateway</h1>
        <p class="text-muted">Demonstrasi integrasi Flip untuk pembayaran invoice</p>

        <div class="row mt-4">
            <div class="col-md-8">
                <!-- Invoice Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Informasi Tagihan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Nomor Invoice:</strong>
                            </div>
                            <div class="col-6">
                                INV-<?php echo date('Ymd-His'); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Nama Pelanggan:</strong>
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" id="customerName" value="John Doe">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Email:</strong>
                            </div>
                            <div class="col-6">
                                <input type="email" class="form-control" id="customerEmail" value="john@example.com">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>No. Telepon:</strong>
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" id="customerPhone" value="081234567890">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Jumlah Tagihan:</strong>
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="invoiceAmount" value="100000">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Pilih Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Load helper
                        helper('flip');

                        // Cek apakah Flip aktif
                        if (is_flip_active()) {
                            // Get payment methods
                            $methods = get_flip_payment_methods();

                            if (!empty($methods)) {
                                foreach ($methods as $method) {
                                    $adminFee = $method['admin_fee'] ?? 0;
                        ?>
                                    <div class="payment-method-card"
                                        data-method="<?php echo $method['code']; ?>"
                                        data-admin-fee="<?php echo $adminFee; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $method['name']; ?></strong>
                                                <?php if ($adminFee > 0): ?>
                                                    <div class="admin-fee">
                                                        Biaya Admin: <?php echo flip_format_currency($adminFee); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <i class="bi bi-check-circle" style="font-size: 1.5rem; color: #007bff; display: none;"></i>
                                            </div>
                                        </div>
                                    </div>
                        <?php
                                }
                            } else {
                                echo '<div class="alert alert-warning">Tidak ada metode pembayaran tersedia</div>';
                            }
                        } else {
                            echo '<div class="alert alert-danger">Flip payment gateway tidak aktif</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Payment Button -->
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg" id="btnPay" disabled>
                        Bayar Sekarang
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h5>Ringkasan Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tagihan:</span>
                            <span id="summaryAmount">Rp 100.000</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Biaya Admin:</span>
                            <span id="summaryAdminFee">Rp 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="summaryTotal">Rp 100.000</strong>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Metode: <span id="selectedMethod">-</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let selectedMethod = null;
            let selectedAdminFee = 0;
            let baseAmount = 100000;

            // Update amount when input changes
            $('#invoiceAmount').on('input', function() {
                baseAmount = parseInt($(this).val()) || 0;
                updateSummary();
            });

            // Payment method selection
            $('.payment-method-card').on('click', function() {
                // Remove selection from all cards
                $('.payment-method-card').removeClass('selected');
                $('.payment-method-card i').hide();

                // Select this card
                $(this).addClass('selected');
                $(this).find('i').show();

                // Get selected method
                selectedMethod = $(this).data('method');
                selectedAdminFee = parseInt($(this).data('admin-fee')) || 0;

                // Update summary
                updateSummary();

                // Enable payment button
                $('#btnPay').prop('disabled', false);
            });

            // Update summary
            function updateSummary() {
                const total = baseAmount + selectedAdminFee;

                $('#summaryAmount').text(formatCurrency(baseAmount));
                $('#summaryAdminFee').text(formatCurrency(selectedAdminFee));
                $('#summaryTotal').text(formatCurrency(total));
                $('#selectedMethod').text(selectedMethod || '-');
            }

            // Format currency
            function formatCurrency(amount) {
                return 'Rp ' + amount.toLocaleString('id-ID');
            }

            // Pay button click
            $('#btnPay').on('click', function() {
                if (!selectedMethod) {
                    alert('Silakan pilih metode pembayaran terlebih dahulu');
                    return;
                }

                const button = $(this);
                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

                // Prepare payment data
                const paymentData = {
                    order_id: 'INV-' + Date.now(),
                    amount: baseAmount + selectedAdminFee,
                    customer_name: $('#customerName').val(),
                    customer_email: $('#customerEmail').val(),
                    customer_phone: $('#customerPhone').val(),
                    description: 'Pembayaran Invoice',
                    method: selectedMethod,
                    return_url: window.location.origin + '/payment/success',
                    callback_url: window.location.origin + '/payment/callback/flip'
                };

                // Send to backend
                $.ajax({
                    url: '<?php echo base_url('payment/createInvoice'); ?>',
                    method: 'POST',
                    data: paymentData,
                    success: function(response) {
                        // Redirect to payment URL
                        if (response.payment_url) {
                            window.location.href = response.payment_url;
                        } else {
                            alert('Gagal membuat pembayaran: ' + (response.message || 'Unknown error'));
                            button.prop('disabled', false).html('Bayar Sekarang');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + error);
                        button.prop('disabled', false).html('Bayar Sekarang');
                    }
                });
            });
        });
    </script>
</body>

</html>