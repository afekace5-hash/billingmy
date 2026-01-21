<?= $this->extend('customer_dashboard/admin_layout') ?>

<?= $this->section('title') ?>Tagihan Saya<?= $this->endSection() ?>

<?= $this->section('page-title') ?>Tagihan Saya<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Link to external CSS -->
<link href="<?= base_url() ?>backend/assets/css/payment-methods-mobile.css" rel="stylesheet" type="text/css" />

<style>
    @media (max-width: 768px) {
        .page-title-box {
            display: none !important;
        }

        .page-content {
            background: #F5F5F5;
            padding: 15px !important;
        }

        .invoice-header-mobile {
            background: linear-gradient(135deg, #6B52AE 0%, #8B6AC7 100%);
            margin: -15px -15px 20px -15px;
            padding: 20px 15px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .invoice-header-mobile h1 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .invoice-header-mobile .notif-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .invoice-item-mobile {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .invoice-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            background: #F3F4F6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #6B52AE;
        }

        .invoice-details {
            flex-grow: 1;
        }

        .invoice-details h6 {
            font-size: 14px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 4px 0;
        }

        .invoice-details p {
            font-size: 12px;
            color: #9CA3AF;
            margin: 0;
        }

        .invoice-amount {
            text-align: right;
        }

        .invoice-amount .price {
            font-size: 15px;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 4px 0;
        }

        .invoice-amount .status {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
        }

        .invoice-amount .status.unpaid {
            background: #FEE2E2;
            color: #DC2626;
        }

        .invoice-amount .status.paid {
            background: #D1FAE5;
            color: #059669;
        }

        .mobile-view {
            display: block;
        }

        .desktop-view {
            display: none !important;
        }
    }

    @media (min-width: 769px) {
        .mobile-view {
            display: none !important;
        }

        .desktop-view {
            display: block !important;
        }
    }
</style>

<!-- Mobile View -->
<div class="mobile-view">
    <div class="invoice-header-mobile">
        <h1>Tagihan</h1>
        <div class="notif-icon">
            <i class="bx bx-bell"></i>
        </div>
    </div>

    <?php if (!empty($unpaid_invoices)): ?>
        <?php foreach ($unpaid_invoices as $invoice):
            $total = (float)$invoice['bill'] + (float)$invoice['arrears'] + (float)$invoice['additional_fee'] - (float)$invoice['discount'];
        ?>
            <div class="invoice-item-mobile" onclick="showInvoiceDetail(<?= $invoice['id'] ?>)">
                <div class="invoice-icon">
                    <i class="bx bx-receipt"></i>
                </div>
                <div class="invoice-details">
                    <h6><?= esc($invoice['invoice_no']) ?></h6>
                    <p><?= date('d M Y', strtotime($invoice['created_at'])) ?></p>
                </div>
                <div class="invoice-amount">
                    <div class="price">Rp <?= number_format($total, 0, ',', '.') ?></div>
                    <span class="status unpaid">BELUM DIBAYAR</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bx bx-receipt" style="font-size: 48px; color: #D1D5DB;"></i>
            <p class="text-muted mt-2">Tidak ada tagihan</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($paid_invoices)): ?>
        <?php foreach (array_slice($paid_invoices, 0, 5) as $invoice):
            $total = (float)$invoice['bill'] + (float)$invoice['arrears'] + (float)$invoice['additional_fee'] - (float)$invoice['discount'];
        ?>
            <div class="invoice-item-mobile" onclick="showInvoiceDetail(<?= $invoice['id'] ?>)">
                <div class="invoice-icon">
                    <i class="bx bx-check-circle"></i>
                </div>
                <div class="invoice-details">
                    <h6><?= esc($invoice['invoice_no']) ?></h6>
                    <p><?= date('d M Y', strtotime($invoice['created_at'])) ?></p>
                </div>
                <div class="invoice-amount">
                    <div class="price">Rp <?= number_format($total, 0, ',', '.') ?></div>
                    <span class="status paid">SUDAH DIBAYAR</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Desktop View -->
<div class="desktop-view">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tagihan Saya</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?= site_url('customer-portal/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tagihan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Unpaid Invoices -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-danger text-white border-0" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title text-white mb-0">
                            <i class="bx bx-receipt me-2"></i>
                            Tagihan Belum Dibayar
                        </h4>
                        <span class="badge bg-white text-danger fs-6 fw-bold px-3 py-2"><?= count($unpaid_invoices) ?> Tagihan</span>
                    </div>
                </div>
                <div class="card-body p-4" style="background-color: #f8f9fa;">
                    <?php if (!empty($unpaid_invoices)): ?>
                        <div class="row g-4">
                            <?php
                            foreach ($unpaid_invoices as $invoice):
                                $total = (float)$invoice['bill'] + (float)$invoice['arrears'] + (float)$invoice['additional_fee'] - (float)$invoice['discount'];
                                $dueDate = strtotime($invoice['due_date'] ?? $invoice['created_at']);
                                $today = strtotime(date('Y-m-d'));
                                $daysUntilDue = floor(($dueDate - $today) / 86400);

                                // Determine status
                                if ($daysUntilDue < 0) {
                                    $statusClass = 'danger';
                                    $statusText = 'Terlambat ' . abs($daysUntilDue) . ' hari';
                                    $statusIcon = 'bx-error-circle';
                                    $cardBorder = 'border-danger';
                                    $urgencyBadge = '<span class="badge bg-danger"><i class="bx bx-time me-1"></i>Overdue</span>';
                                } elseif ($daysUntilDue == 0) {
                                    $statusClass = 'warning';
                                    $statusText = 'Jatuh tempo hari ini';
                                    $statusIcon = 'bx-alarm';
                                    $cardBorder = 'border-warning';
                                    $urgencyBadge = '<span class="badge bg-warning text-dark"><i class="bx bx-alarm-exclamation me-1"></i>Hari Ini</span>';
                                } elseif ($daysUntilDue <= 3) {
                                    $statusClass = 'warning';
                                    $statusText = $daysUntilDue . ' hari lagi';
                                    $statusIcon = 'bx-time-five';
                                    $cardBorder = 'border-warning';
                                    $urgencyBadge = '<span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Segera</span>';
                                } else {
                                    $statusClass = 'info';
                                    $statusText = $daysUntilDue . ' hari lagi';
                                    $statusIcon = 'bx-calendar';
                                    $cardBorder = 'border-info';
                                    $urgencyBadge = '<span class="badge bg-info"><i class="bx bx-check me-1"></i>Normal</span>';
                                }
                            ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 shadow-sm hover-lift <?= $cardBorder ?>" style="border-width: 2px; transition: all 0.3s ease;">
                                        <div class="card-body p-4">
                                            <!-- Header -->
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1 fw-bold text-<?= $statusClass ?>">
                                                        <i class="bx bx-file me-1"></i><?= esc($invoice['invoice_no']) ?>
                                                    </h5>
                                                    <p class="text-muted mb-0 small">
                                                        <i class="bx bx-calendar-alt me-1"></i>
                                                        Periode: <?= esc($invoice['periode']) ?>
                                                    </p>
                                                </div>
                                                <?= $urgencyBadge ?>
                                            </div>

                                            <!-- Due Date Alert -->
                                            <div class="alert alert-<?= $statusClass ?> alert-dismissible fade show py-2 mb-3" role="alert" style="background-color: rgba(var(--bs-<?= $statusClass ?>-rgb), 0.1); border-left: 3px solid var(--bs-<?= $statusClass ?>);">
                                                <i class="bx <?= $statusIcon ?> me-1"></i>
                                                <small class="fw-semibold"><?= $statusText ?></small>
                                                <div class="small text-muted">Jatuh tempo: <?= date('d M Y', $dueDate) ?></div>
                                            </div>

                                            <!-- Invoice Details -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed #dee2e6;">
                                                    <span class="text-muted"><i class="bx bx-receipt me-1"></i>Tagihan Bulanan</span>
                                                    <strong class="text-dark">Rp <?= number_format((float)$invoice['bill'], 0, ',', '.') ?></strong>
                                                </div>

                                                <?php if ((float)$invoice['arrears'] > 0): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed #dee2e6;">
                                                        <span class="text-danger"><i class="bx bx-error-circle me-1"></i>Tunggakan</span>
                                                        <strong class="text-danger">Rp <?= number_format((float)$invoice['arrears'], 0, ',', '.') ?></strong>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ((float)$invoice['additional_fee'] > 0): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed #dee2e6;">
                                                        <span class="text-muted"><i class="bx bx-plus-circle me-1"></i>Biaya Tambahan</span>
                                                        <span class="text-dark">Rp <?= number_format((float)$invoice['additional_fee'], 0, ',', '.') ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ((float)$invoice['discount'] > 0): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed #dee2e6;">
                                                        <span class="text-success"><i class="bx bx-badge-check me-1"></i>Diskon</span>
                                                        <span class="text-success fw-semibold">-Rp <?= number_format((float)$invoice['discount'], 0, ',', '.') ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 2px solid #dee2e6;">
                                                    <strong class="text-dark fs-6"><i class="bx bx-wallet me-1"></i>Total Pembayaran</strong>
                                                    <strong class="text-<?= $statusClass ?> fs-5">
                                                        Rp <?= number_format($total, 0, ',', '.') ?>
                                                    </strong>
                                                </div>
                                            </div>

                                            <!-- Action Button -->
                                            <button class="btn btn-<?= $statusClass == 'danger' ? 'danger' : 'success' ?> w-100 btn-lg shadow-sm mt-2"
                                                onclick="payInvoice(<?= $invoice['id'] ?>, '<?= esc($invoice['invoice_no']) ?>', <?= $total ?>)"
                                                style="border-radius: 8px; font-weight: 600;">
                                                <i class="bx bx-credit-card me-2"></i>Bayar Sekarang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bx bx-check-circle font-size-48 text-success mb-3"></i>
                            <h5 class="text-success">Semua Tagihan Sudah Lunas!</h5>
                            <p class="text-muted">Tidak ada tagihan yang belum dibayar saat ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Paid Invoices -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="card-title text-white mb-0">
                        <i class="bx bx-check-circle me-2"></i>
                        Riwayat Pembayaran (5 Terakhir)
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($paid_invoices)): ?>
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Periode</th>
                                        <th>Jumlah Bayar</th>
                                        <th>Metode Bayar</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paid_invoices as $invoice):
                                        // Hitung total yang dibayar
                                        $totalPaid = (float)$invoice['bill'] + (float)$invoice['arrears'] + (float)$invoice['additional_fee'] - (float)$invoice['discount'];
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="text-dark fw-bold"><?= esc($invoice['invoice_no']) ?></span>
                                            </td>
                                            <td><?= esc($invoice['periode']) ?></td>
                                            <td>
                                                <span class="text-success fw-semibold">
                                                    Rp <?= number_format($totalPaid, 0, ',', '.') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= ucfirst($invoice['payment_method'] ?? 'Manual') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('d M Y H:i', strtotime($invoice['payment_date'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Lunas</span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('customer-portal/download-invoice/' . $invoice['id']) ?>"
                                                    class="btn btn-sm btn-primary"
                                                    target="_blank"
                                                    title="Download Invoice">
                                                    <i class="bx bx-download me-1"></i>Download
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bx bx-receipt font-size-48 text-muted mb-3"></i>
                            <p class="text-muted">Belum ada riwayat pembayaran</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Payment Modal - Snap BI Style -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="margin: -0.5rem -0.5rem -0.5rem auto;"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 pb-4">
                <!-- Invoice Summary -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="bx bx-receipt text-primary" style="font-size: 32px;"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-1" id="invoice-number">INV-2024-001</h6>
                    <p class="text-muted small mb-0">Pembayaran Tagihan Internet</p>
                </div>

                <!-- Amount -->
                <div class="text-center mb-4">
                    <div class="h4 fw-bold text-primary mb-0" id="payment-amount">Rp 150.000</div>
                </div>

                <!-- Payment Methods -->
                <div class="mb-3">
                    <h6 class="fw-semibold mb-3 text-dark">Pilih Metode Pembayaran</h6>
                    <div id="payment-methods-list">
                        <!-- Payment methods will be loaded here -->
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="text-center">
                    <small class="text-muted">
                        <i class="bx bx-shield-check me-1"></i>
                        Transaksi Anda dilindungi enkripsi SSL
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0" style="border-radius: 16px;">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="fw-semibold mb-2">Memproses Pembayaran</h6>
                <p class="text-muted small mb-0">Mohon tunggu, jangan tutup halaman ini</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let selectedInvoice = null;

    function payInvoice(invoiceId, invoiceNo, amount) {
        selectedInvoice = {
            id: invoiceId,
            no: invoiceNo,
            amount: amount
        };

        // Show payment modal with available methods
        loadPaymentMethods();
    }

    async function loadPaymentMethods() {
        try {
            // Show loading
            Swal.fire({
                title: 'Memuat Metode Pembayaran...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Load payment methods from database
            const paymentMethods = <?= json_encode($active_gateways ?? []) ?>;

            console.log('Payment methods loaded:', paymentMethods); // Debug log
            console.log('Number of methods:', paymentMethods ? paymentMethods.length : 0); // Debug count

            if (!paymentMethods || paymentMethods.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada Metode Pembayaran',
                    text: 'Mohon maaf, saat ini tidak ada metode pembayaran yang tersedia. Silakan hubungi customer service.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            window.currentPaymentMethods = paymentMethods;

            // Close loading
            Swal.close();

            // Update invoice details in modal
            document.getElementById('invoice-number').textContent = selectedInvoice.no;
            document.getElementById('payment-amount').textContent = `Rp ${formatRupiah(selectedInvoice.amount)}`;

            // Build payment methods HTML
            let methodsHtml = '';

            // Display all available payment methods
            window.currentPaymentMethods.forEach((method) => {
                const adminFee = method.admin_fee || 0;
                let totalAmount = selectedInvoice.amount;
                let adminFeeValue = 0; // Nilai admin fee untuk dikirim ke backend
                let adminFeeLabel = '';

                // Hitung biaya admin berdasarkan tipe (percent atau fixed)
                if (method.admin_fee_type === 'percent') {
                    const feeNominal = Math.round(selectedInvoice.amount * (adminFee / 100));
                    totalAmount += feeNominal;
                    adminFeeValue = feeNominal;
                    adminFeeLabel = `<small class="text-muted">Biaya admin: ${adminFee}% (Rp ${formatRupiah(feeNominal)})</small>`;
                } else if (adminFee > 0) {
                    totalAmount += adminFee;
                    adminFeeValue = adminFee;
                    adminFeeLabel = `<small class="text-muted">Biaya admin: Rp ${formatRupiah(adminFee)}</small>`;
                } else {
                    adminFeeLabel = '<small class="text-success">Tanpa biaya admin</small>';
                }

                const logo = getMethodLogo(method.code);

                methodsHtml += `
                <div class="payment-method-item p-3 border rounded-3 mb-2" 
                     onclick="selectPaymentMethod('${method.gateway}', '${method.code}', '${method.name}', ${totalAmount}, ${adminFeeValue})"
                     style="cursor: pointer; transition: all 0.2s; border: 1px solid #e5e7eb !important;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            ${logo}
                        </div>
                        <div class="flex-1">
                            <div class="fw-semibold text-dark" style="font-size: 14px;">${method.name}</div>
                            ${adminFeeLabel}
                        </div>
                    </div>
                </div>`;
            });

            // Show payment modal
            document.getElementById('payment-methods-list').innerHTML = methodsHtml;

            // Add hover effects
            document.querySelectorAll('.payment-method-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.borderColor = '#3b82f6';
                    this.style.backgroundColor = '#f8fafc';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.borderColor = '#e5e7eb';
                    this.style.backgroundColor = 'transparent';
                });
            });

            new bootstrap.Modal(document.getElementById('paymentModal')).show();

        } catch (error) {
            console.error('Error loading payment methods:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat metode pembayaran. Silakan coba lagi.'
            });
        }
    }

    function selectPaymentMethod(gateway, code, name, totalAmount, adminFee = 0) {
        console.log('selectPaymentMethod called with adminFee:', adminFee);
        // Close payment modal
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();

        // Show processing with SweetAlert
        Swal.fire({
            title: 'Memproses Pembayaran...',
            text: `${name} - Rp ${formatRupiah(totalAmount)}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Process payment
        setTimeout(() => {
            processPayment(gateway, code, totalAmount, adminFee);
        }, 500);
    }

    async function processPayment(gateway, method, amount, adminFee = 0) {
        try {
            console.log('processPayment called with adminFee:', adminFee);
            // Show processing
            Swal.fire({
                title: 'Memproses Pembayaran...',
                text: 'Mohon tunggu, jangan tutup halaman ini',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make payment request
            const response = await fetch('<?= site_url('customer-portal/pay-invoice') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    invoice_id: selectedInvoice.id,
                    gateway: gateway,
                    method: method,
                    amount: amount,
                    admin_fee: adminFee // Kirim admin fee ke backend
                })
            });

            const result = await response.json();

            if (result.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();

                if (result.payment_url) {
                    // Redirect to payment gateway
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Anda akan diarahkan ke halaman pembayaran...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.open(result.payment_url, '_blank');
                    });
                } else {
                    // Manual payment success
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        text: result.message || 'Pembayaran Anda telah berhasil diproses.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                }
            } else {
                throw new Error(result.message || 'Pembayaran gagal');
            }

        } catch (error) {
            console.error('Payment error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Gagal',
                text: error.message || 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.'
            });
        }
    }

    function getMethodLogo(code) {
        const logos = {
            // Duitku codes - Bank Transfer / Virtual Account
            'BC': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // BCA VA
            'M2': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // Mandiri VA
            'I1': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // BNI VA
            'BR': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // BRI VA
            'B1': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // CIMB Niaga VA
            'BT': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // Permata VA
            'A1': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // ATM Bersama
            'NC': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // BNC VA
            'BV': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // BSI VA
            'AG': '<i class="bx bx-credit-card font-size-24 text-primary"></i>', // Bank Artha Graha

            // Duitku codes - E-Wallet
            'OV': '<i class="bx bx-wallet font-size-24 text-info"></i>', // OVO
            'DA': '<i class="bx bx-wallet font-size-24 text-primary"></i>', // DANA
            'LA': '<i class="bx bx-wallet font-size-24 text-danger"></i>', // LinkAja
            'LF': '<i class="bx bx-wallet font-size-24 text-danger"></i>', // LinkAja Fixed Fee
            'SA': '<i class="bx bx-wallet font-size-24 text-warning"></i>', // ShopeePay App
            'SL': '<i class="bx bx-wallet font-size-24 text-warning"></i>', // ShopeePay Link
            'OL': '<i class="bx bx-wallet font-size-24 text-info"></i>', // OVO Link

            // Duitku codes - QRIS
            'SP': '<i class="bx bx-qr font-size-24 text-success"></i>', // ShopeePay QRIS
            'LQ': '<i class="bx bx-qr font-size-24 text-success"></i>', // LinkAja QRIS
            'NQ': '<i class="bx bx-qr font-size-24 text-success"></i>', // Nobu QRIS
            'GQ': '<i class="bx bx-qr font-size-24 text-success"></i>', // Gudang Voucher QRIS

            // Duitku codes - Retail
            'FT': '<i class="bx bx-store font-size-24 text-danger"></i>', // Alfamart/Pos
            'IR': '<i class="bx bx-store font-size-24 text-primary"></i>', // Indomaret

            // Legacy codes (for backward compatibility)
            'bca': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
            'bni': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
            'bri': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
            'mandiri': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
            'permata': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',

            // Virtual Account (legacy)
            'bca_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',
            'bni_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',
            'bri_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',
            'mandiri_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',

            // E-Wallet (legacy)
            'gopay': '<i class="bx bx-wallet font-size-24 text-success"></i>',
            'shopeepay': '<i class="bx bx-wallet font-size-24 text-warning"></i>',
            'ovo': '<i class="bx bx-wallet font-size-24 text-info"></i>',
            'dana': '<i class="bx bx-wallet font-size-24 text-primary"></i>',
            'linkaja': '<i class="bx bx-wallet font-size-24 text-danger"></i>',

            // QRIS (legacy)
            'qris': '<i class="bx bx-qr font-size-24 text-success"></i>',

            // Convenience Store (legacy)
            'alfamart': '<i class="bx bx-store font-size-24 text-danger"></i>',
            'indomaret': '<i class="bx bx-store font-size-24 text-primary"></i>',
        };

        // Try with original code first, then lowercase
        return logos[code] || logos[code.toLowerCase()] || '<i class="bx bx-credit-card font-size-24 text-primary"></i>';
    }

    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }

    // Show invoice detail screen
    async function showInvoiceDetail(invoiceId) {
        Swal.fire({
            title: 'Memuat Detail...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // Fetch invoice details from server
            const response = await fetch(`<?= site_url('customer-portal/get-invoice-detail') ?>/${invoiceId}`);
            const result = await response.json();

            if (!result.success) {
                Swal.fire('Error', result.message || 'Gagal memuat detail tagihan', 'error');
                return;
            }

            const invoice = result.invoice;
            const customer = result.customer || {};

            // Calculate total
            const bill = parseFloat(invoice.bill || 0);
            const arrears = parseFloat(invoice.arrears || 0);
            const additionalFee = parseFloat(invoice.additional_fee || 0);
            const discount = parseFloat(invoice.discount || 0);
            const ppn = parseFloat(invoice.ppn || 0);
            const totalAmount = bill + arrears + additionalFee - discount + ppn;

            // Format dates
            const paidDate = invoice.payment_date ? new Date(invoice.payment_date).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : '-';

            // Status badge
            let statusBadge = '';
            if (invoice.status === 'paid') {
                statusBadge = '<span class="status-badge paid">Sudah Dibayar</span>';
            } else if (invoice.status === 'unpaid') {
                statusBadge = '<span class="status-badge unpaid">Belum Dibayar</span>';
            } else if (invoice.status === 'pending') {
                statusBadge = '<span class="status-badge pending">Menunggu Pembayaran</span>';
            } else if (invoice.status === 'failed') {
                statusBadge = '<span class="status-badge failed">Gagal</span>';
            }

            // Payment type mapping
            const paymentTypeMap = {
                'prepaid': 'Prabayar',
                'postpaid': 'Pascabayar',
                'prabayar': 'Prabayar',
                'pascabayar': 'Pascabayar'
            };

            const connectionTypeMap = {
                'HOTSPOT': 'Hotspot',
                'PPPOE': 'PPPoE',
                'STATIC': 'Static IP',
                'FIXED': 'Fixed'
            };

            const html = `
                <div class="invoice-detail-container">
                    <div class="invoice-detail-header">
                        <h3>Total Tagihan</h3>
                        <div class="amount">Rp ${new Intl.NumberFormat('id-ID').format(totalAmount)}</div>
                        <p class="subtitle">${statusBadge}</p>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Layanan</div>
                        <div class="detail-row">
                            <span class="detail-label">ID Layanan</span>
                            <span class="detail-value">${customer.nomor_layanan || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama Layanan</span>
                            <span class="detail-value">${customer.nama_pelanggan || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe Koneksi</span>
                            <span class="detail-value">${connectionTypeMap[customer.tipe_koneksi] || customer.tipe_koneksi || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe Pembayaran</span>
                            <span class="detail-value">${paymentTypeMap[customer.tipe_pembayaran] || customer.tipe_pembayaran || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe Langganan</span>
                            <span class="detail-value">${customer.subscription_type || 'FIXED'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Username</span>
                            <span class="detail-value">${customer.username || '-'}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Tagihan</div>
                        <div class="detail-row">
                            <span class="detail-label">ID Tagihan</span>
                            <span class="detail-value">${invoice.invoice_no || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Tagihan</span>
                            <span class="detail-value">Rp ${new Intl.NumberFormat('id-ID').format(bill)}</span>
                        </div>
                        ${arrears > 0 ? `<div class="detail-row">
                            <span class="detail-label">Tunggakan</span>
                            <span class="detail-value">Rp ${new Intl.NumberFormat('id-ID').format(arrears)}</span>
                        </div>` : ''}
                        <div class="detail-row">
                            <span class="detail-label">Biaya Admin</span>
                            <span class="detail-value">Rp ${new Intl.NumberFormat('id-ID').format(additionalFee)}</span>
                        </div>
                        ${discount > 0 ? `<div class="detail-row">
                            <span class="detail-label">Diskon</span>
                            <span class="detail-value">Rp ${new Intl.NumberFormat('id-ID').format(discount)}</span>
                        </div>` : ''}
                        <div class="detail-row">
                            <span class="detail-label">PPN</span>
                            <span class="detail-value">Rp ${new Intl.NumberFormat('id-ID').format(ppn)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Tagihan</span>
                            <span class="detail-value">Rp ${new Intl.NumberFormat('id-ID').format(totalAmount)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">${statusBadge}</span>
                        </div>
                        ${invoice.status === 'paid' ? `<div class="detail-row">
                            <span class="detail-label">Dibayar Pada</span>
                            <span class="detail-value">${paidDate}</span>
                        </div>` : ''}
                    </div>
                </div>

                ${invoice.status === 'unpaid' || invoice.status === 'pending' ? `
                    <div class="payment-actions">
                        <button class="btn-primary" onclick="Swal.close(); payInvoice(${invoice.id}, '${invoice.invoice_no}', ${totalAmount});">
                            Bayar Sekarang
                        </button>
                    </div>
                ` : ''}
            `;

            const isMobile = window.innerWidth <= 768;

            await Swal.fire({
                html: `
                    <div class="payment-modal-header">
                        <div class="back-btn" onclick="Swal.close()">
                            <i class="bx bx-arrow-back"></i>
                        </div>
                        <h3>Detail Tagihan</h3>
                    </div>
                    ${html}
                `,
                showConfirmButton: false,
                showCancelButton: false,
                customClass: {
                    container: isMobile ? 'payment-modal-mobile' : '',
                    popup: isMobile ? 'swal2-mobile-fullscreen' : 'invoice-detail-modal'
                },
                width: isMobile ? '100%' : '600px',
                padding: 0,
                background: '#f9fafb'
            });

        } catch (error) {
            console.error('Error loading invoice detail:', error);
            Swal.fire('Error', 'Gagal memuat detail tagihan', 'error');
        }
    }

    // Auto pay if URL has pay parameter
    <?php if (isset($_GET['pay'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Find invoice and trigger payment
            const invoiceId = <?= (int)$_GET['pay'] ?>;
            // You would need to get the invoice details first
            // For now, just scroll to that invoice
            setTimeout(() => {
                document.querySelector(`[onclick*="payInvoice(${invoiceId}"]`)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                document.querySelector(`[onclick*="payInvoice(${invoiceId}"]`)?.click();
            }, 500);
        });
    <?php endif; ?>

    // Check payment success from URL parameter
    <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == '1'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Pembayaran Berhasil!',
                html: '<p class=\"mb-2\">Tagihan Anda telah berhasil dibayar.</p>' +
                    '<p class=\"text-muted small\">Terima kasih atas pembayaran Anda. Akun Anda akan segera aktif kembali.</p>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981'
            }).then(() => {
                // Clean URL
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
                // Reload to refresh invoice list
                location.reload();
            });
        });
    <?php endif; ?>

    // Auto-open payment for first unpaid invoice if autopay parameter exists
    <?php if (isset($_GET['autopay']) && !empty($unpaid_invoices)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const firstInvoice = <?= json_encode($unpaid_invoices[0]) ?>;
            if (firstInvoice) {
                const totalAmount = parseFloat(firstInvoice.bill) +
                    parseFloat(firstInvoice.arrears || 0) +
                    parseFloat(firstInvoice.additional_fee || 0) -
                    parseFloat(firstInvoice.discount || 0);
                setTimeout(() => {
                    payInvoice(firstInvoice.id, firstInvoice.invoice_no, totalAmount);
                }, 500);
            }
        });
    <?php endif; ?>
</script>

<style>
    /* Custom styles for invoice cards */
    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    }

    .badge {
        transition: all 0.3s ease;
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .payment-method-item:hover {
        background-color: #f8f9fa !important;
        border-color: #3b82f6 !important;
        transform: scale(1.02);
    }
</style>

<?= $this->endSection() ?>