<?= $this->extend('customer_dashboard/admin_layout') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('page-title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Link to external CSS -->
<link href="<?= base_url() ?>backend/assets/css/customer-mobile.css" rel="stylesheet" type="text/css" />
<link href="<?= base_url() ?>backend/assets/css/payment-methods-mobile.css" rel="stylesheet" type="text/css" />

<!-- Desktop View -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Dashboard Customer Portal</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Customer Portal</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Mobile View -->
<div class="mobile-view">
    <!-- Mobile Top Bar -->
    <div class="mobile-top-bar">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="bx bx-wifi"></i>
            </div>
            <div class="app-title">DIFIHOME</div>
        </div>
        <div class="notif-icon">
            <i class="bx bx-bell"></i>
        </div>
    </div>

    <!-- Mobile Header -->
    <div class="mobile-header">
        <div class="user-card">
            <div class="user-left">
                <img src="<?= getUserAvatar() ?>" alt="Avatar" class="user-avatar">
                <div class="user-details">
                    <h6>Customer</h6>
                    <h5><?= esc($customer['nomor_layanan']) ?></h5>
                </div>
            </div>
            <div class="user-right">
                <h6>Saldo PPOB</h6>
                <h4>Rp 0</h4>
            </div>
        </div>

        <!-- Main Menu Grid (inside same card) -->
        <div class="main-menu-grid">
            <a href="<?= site_url('customer-portal/invoices') ?>" class="menu-item">
                <div class="icon-box">
                    <i class="bx bx-receipt"></i>
                </div>
                <span>Tagihan</span>
            </a>
            <a href="#" onclick="showPaymentOptions(); return false;" class="menu-item">
                <div class="icon-box">
                    <i class="bx bx-wallet"></i>
                </div>
                <span>Bayar</span>
            </a>
            <a href="<?= site_url('customer-portal/profile') ?>" class="menu-item">
                <div class="icon-box">
                    <i class="bx bx-user"></i>
                </div>
                <span>Profile</span>
            </a>
            <a href="#" onclick="contactWhatsApp(); return false;" class="menu-item">
                <div class="icon-box">
                    <i class="bx bxl-whatsapp"></i>
                </div>
                <span>Bantuan</span>
            </a>
        </div>
    </div>

    <!-- Usage Card (Hotspot Style) -->
    <div class="usage-card">
        <div class="usage-header">
            <span class="usage-label">PAKET <?= strtoupper(esc($customer['package_name'] ?? 'INTERNET')) ?></span>
            <span class="usage-status">AKTIF</span>
        </div>
        <div class="usage-stats">
            <div class="stat-item">
                <div class="stat-circle">
                    <i class="bx bx-upload"></i>
                </div>
                <div class="stat-info">
                    <h4>Unlimited</h4>
                    <p>UPLOAD</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-circle">
                    <i class="bx bx-download"></i>
                </div>
                <div class="stat-info">
                    <h4>Unlimited</h4>
                    <p>DOWNLOAD</p>
                </div>
            </div>
        </div>
        <div class="usage-footer">
            <span class="usage-badge">TERPAKAI</span>
            <span class="usage-date">Aktif s/d <?= date('d M Y', strtotime($customer['tgl_tempo'])) ?></span>
        </div>
    </div>

    <!-- Penawaran Terbaik Section -->
    <!-- TODO: Section ini akan digunakan untuk menampilkan promo/penawaran yang dibuat oleh admin -->
    <!-- Data akan diambil dari database tabel promos/offers -->
    <div class="section-header-row">
        <h5 class="section-title-mobile">
            <span>Penawaran Terbaik</span>
        </h5>
        <a href="<?= site_url('customer-portal/invoices') ?>" class="see-all-link">
            Lainnya <i class="bx bx-chevron-right"></i>
        </a>
    </div>

    <!-- Promo Cards -->
    <!-- NOTE: Dynamic promo cards from admin panel -->
    <div class="promo-cards">
        <?php if (!empty($active_promos)): ?>
            <?php foreach ($active_promos as $promo): ?>
                <div class="promo-card" style="background: linear-gradient(135deg, <?= esc($promo['gradient_start']) ?> 0%, <?= esc($promo['gradient_end']) ?> 100%);">
                    <div class="promo-amount"><?= esc($promo['badge_text']) ?></div>
                    <h6><?= esc($promo['title']) ?></h6>
                    <?php if (!empty($promo['description'])): ?>
                        <p><?= esc($promo['description']) ?></p>
                    <?php endif; ?>
                    <?php
                    // Check if action is a URL or javascript function
                    $isUrl = (strpos($promo['button_action'], 'http') === 0 || strpos($promo['button_action'], site_url()) === 0);
                    $action = $isUrl ? "window.location.href='" . esc($promo['button_action']) . "'" : esc($promo['button_action']);
                    ?>
                    <button onclick="<?= $action ?>" class="btn-promo"><?= esc($promo['button_text']) ?></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback: Default promo if no promos created yet -->
            <?php if ($unpaid_count > 0): ?>
                <div class="promo-card">
                    <div class="promo-amount">Rp <?= number_format($total_unpaid / 1000, 0) ?>K</div>
                    <h6>Bayar Tagihan</h6>
                    <p>Tagihan bulan ini</p>
                    <button onclick="showPaymentOptions()" class="btn-promo">Bayar Sekarang</button>
                </div>
            <?php endif; ?>
            <div class="promo-card">
                <div class="promo-amount">24/7</div>
                <h6>Layanan CS</h6>
                <p>Bantuan kapan saja</p>
                <button onclick="contactWhatsApp()" class="btn-promo">Hubungi CS</button>
            </div>
            <div class="promo-card">
                <div class="promo-amount">100%</div>
                <h6>Uptime Terjamin</h6>
                <p>Koneksi stabil</p>
                <button onclick="window.location.href='<?= site_url('customer-portal/profile') ?>'" class="btn-promo">Lihat Detail</button>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Statistics Cards Row -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Tagihan Bulan Ini</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                            Rp <?= number_format($total_unpaid, 0, ',', '.') ?>
                        </h4>
                        <span class="badge badge-soft-<?= $unpaid_count > 0 ? 'danger' : 'success' ?>"><?= $unpaid_count ?> tagihan</span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-white border border-primary rounded fs-3">
                            <i class="bx bx-receipt text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Paket Internet</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-18 fw-semibold ff-secondary mb-2">
                            <?= esc($customer['package_name'] ?? '-') ?>
                        </h4>
                        <span class="text-muted">Rp <?= number_format((float)($customer['package_price'] ?? 0), 0, ',', '.') ?>/bulan</span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-white border border-info rounded fs-3">
                            <i class="bx bx-wifi text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Status Koneksi</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-2 text-<?= $customer['isolir_status'] == 1 ? 'danger' : 'success' ?>">
                            <?= $customer['isolir_status'] == 1 ? 'Terisolir' : 'Aktif' ?>
                        </h4>
                        <span class="text-muted"><?= $customer['isolir_status'] == 1 ? 'Bayar untuk aktifkan' : 'Koneksi normal' ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-white border border-<?= $customer['isolir_status'] == 1 ? 'danger' : 'success' ?> rounded fs-3">
                            <i class="bx <?= $customer['isolir_status'] == 1 ? 'bx-wifi-off' : 'bx-wifi' ?> text-<?= $customer['isolir_status'] == 1 ? 'danger' : 'success' ?>"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Bantuan</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-18 fw-semibold ff-secondary mb-2">
                            Customer Service
                        </h4>
                        <a href="#" onclick="contactCS(); return false;" class="text-decoration-none link-primary">
                            <i class="bx bxl-whatsapp"></i> Hubungi Kami
                        </a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-white border border-warning rounded fs-3">
                            <i class="bx bx-phone text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<?php if ($unpaid_count > 0): ?>
    <div class="row action-buttons-desktop">
        <div class="col-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #5b73e8 0%, #4e63d7 100%);">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="text-white mb-2 fw-semibold">
                                <i class="bx bx-info-circle me-2"></i>Anda memiliki <?= $unpaid_count ?> tagihan yang belum dibayar
                            </h5>
                            <p class="text-white-50 mb-0">Total: Rp <?= number_format($total_unpaid, 0, ',', '.') ?></p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="<?= site_url('customer-portal/invoices') ?>" class="btn btn-light me-2">
                                <i class="bx bx-receipt me-1"></i> Lihat Tagihan
                            </a>
                            <button onclick="showPaymentOptions()" class="btn btn-success">
                                <i class="bx bx-wallet me-1"></i> Bayar Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payment Modal (copy from invoices.php) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="paymentModalLabel">Pilih Metode Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden input to store invoice ID -->
                    <input type="hidden" id="modal-invoice-id" value="">

                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Invoice:</span>
                            <strong id="invoice-number">-</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Pembayaran:</span>
                            <strong class="text-primary" id="payment-amount">Rp 0</strong>
                        </div>
                    </div>
                    <div id="payment-methods-list">
                        <!-- Payment methods will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php
    // Check if there are any active payment gateways
    $hasActiveGateways = !empty($active_gateways) && count($active_gateways) > 0;
    $hasMidtrans = false;
    $hasDuitku = false;

    // Check which gateways are active
    if ($hasActiveGateways) {
        foreach ($active_gateways as $gateway) {
            // Safely check gateway_type
            $gatewayType = $gateway['gateway_type'] ?? '';
            $isActive = isset($gateway['is_active']) && $gateway['is_active'];

            if ($gatewayType === 'midtrans' && $isActive) {
                $hasMidtrans = true;
            }
            if ($gatewayType === 'duitku' && $isActive) {
                $hasDuitku = true;
            }
        }
    }

    // Load Midtrans snap.js if Midtrans is active
    $snapUrl = 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = '';

    if ($hasMidtrans && !empty($midtrans_config)) {
        // Check if production
        if ($midtrans_config['environment'] === 'production') {
            $snapUrl = 'https://app.midtrans.com/snap/snap.js';
        }

        // Try to get client key from settings JSON
        if (!empty($midtrans_config['settings'])) {
            $settings = json_decode($midtrans_config['settings'], true);
            $clientKey = $settings['client_key'] ?? '';
        }

        // Fallback: use api_secret as client key if available
        if (empty($clientKey) && !empty($midtrans_config['api_secret'])) {
            $clientKey = $midtrans_config['api_secret'];
        }

        // Final fallback to .env
        if (empty($clientKey)) {
            $clientKey = getenv('MIDTRANS_CLIENT_KEY') ?: '';
        }
    }
    ?>
    <?php if ($hasMidtrans && !empty($clientKey)): ?>
        <script src="<?= $snapUrl ?>" data-client-key="<?= $clientKey ?>"></script>
        <script>
            console.log('Midtrans snap.js loaded with client key');
        </script>
    <?php endif; ?>
    <script>
        // Store payment gateway availability
        window.paymentGateways = {
            midtrans: <?= $hasMidtrans ? 'true' : 'false' ?>,
            duitku: <?= $hasDuitku ? 'true' : 'false' ?>,
            hasAnyGateway: <?= $hasActiveGateways ? 'true' : 'false' ?>
        };
        console.log('Available payment gateways:', window.paymentGateways);
    </script>
    <script>
        // ApexCharts - Only render if element exists
        if (document.querySelector("#expenseChart")) {
            var options = {
                series: [<?= $total_unpaid ?>, 0],
                chart: {
                    type: 'donut',
                    height: 240
                },
                labels: ['Tagihan', 'Terbayar'],
                colors: ['#405189', '#0ab39c'],
                legend: {
                    show: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    color: '#495057',
                                    offsetY: -10
                                },
                                value: {
                                    show: true,
                                    fontSize: '20px',
                                    fontWeight: 600,
                                    color: '#212529',
                                    offsetY: 10,
                                    formatter: function(val) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total Tagihan',
                                    fontSize: '14px',
                                    fontWeight: 400,
                                    color: '#878a99',
                                    formatter: function(w) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(<?= $total_unpaid ?>);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 200
                        }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#expenseChart"), options);
            chart.render();
        }

        function contactCS() {
            const phoneNumber = '6285183112127';
            const message = `Halo, saya pelanggan dengan nomor layanan <?= $customer['nomor_layanan'] ?>. Saya ingin bertanya tentang layanan internet.`;
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        let selectedInvoice = null;

        function showPaymentOptions() {
            <?php if (!empty($unpaid_invoices)): ?>
                const firstInvoice = <?= json_encode($unpaid_invoices[0]) ?>;
                console.log('Processing payment for invoice:', firstInvoice);

                // Check if id exists
                if (!firstInvoice.id) {
                    console.error('Invoice ID not found!');
                    Swal.fire('Error', 'Invoice ID tidak ditemukan', 'error');
                    return;
                }

                const totalAmount = parseFloat(firstInvoice.bill) +
                    parseFloat(firstInvoice.arrears || 0) +
                    parseFloat(firstInvoice.additional_fee || 0) -
                    parseFloat(firstInvoice.discount || 0);

                selectedInvoice = {
                    id: firstInvoice.id,
                    invoice_no: firstInvoice.invoice_no,
                    amount: totalAmount
                };

                // Check available payment gateways
                if (!window.paymentGateways.hasAnyGateway) {
                    Swal.fire('Error', 'Tidak ada payment gateway yang aktif', 'error');
                    return;
                }

                // If only one gateway is active, process directly
                if (window.paymentGateways.midtrans && !window.paymentGateways.duitku) {
                    processMidtransPayment(firstInvoice.id, firstInvoice.invoice_no, totalAmount);
                    return;
                } else if (!window.paymentGateways.midtrans && window.paymentGateways.duitku) {
                    showDuitkuPaymentMethods(firstInvoice.id, firstInvoice.invoice_no, totalAmount);
                    return;
                }

                // Show gateway selection modal if both are available
                showGatewaySelection(firstInvoice.id, firstInvoice.invoice_no, totalAmount);
            <?php else: ?>
                Swal.fire('Info', 'Tidak ada tagihan yang harus dibayar', 'info');
            <?php endif; ?>
        }

        function showGatewaySelection(invoiceId, invoiceNo, amount) {
            let gatewayOptions = '';

            if (window.paymentGateways.midtrans) {
                gatewayOptions += '<button class="btn btn-primary btn-block mb-2" onclick="processMidtransPayment(' + invoiceId + ', \'' + invoiceNo + '\', ' + amount + ')">Midtrans (Kartu Kredit, E-Wallet, VA)</button>';
            }

            if (window.paymentGateways.duitku) {
                gatewayOptions += '<button class="btn btn-success btn-block" onclick="showDuitkuPaymentMethods(' + invoiceId + ', \'' + invoiceNo + '\', ' + amount + ')">Duitku (VA, E-Wallet, Retail)</button>';
            }

            Swal.fire({
                title: 'Pilih Payment Gateway',
                html: '<div style="display: flex; flex-direction: column; gap: 10px;">' + gatewayOptions + '</div>',
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal'
            });
        }

        async function showDuitkuPaymentMethods(invoiceId, invoiceNo, amount) {
            // Show loading while fetching payment methods
            Swal.fire({
                title: 'Memuat metode pembayaran...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // Fetch available payment methods from Duitku API
                const response = await fetch('<?= base_url('customer-portal/duitku-payment-methods') ?>');
                const result = await response.json();

                if (!result.success || !result.methods || result.methods.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Tidak ada metode pembayaran yang tersedia saat ini'
                    });
                    return;
                }

                // Logo mapping for payment methods
                const logoMap = {
                    'BC': 'https://dashboard.duitku.com/images/logomethod/logoBca.png',
                    'M2': 'https://dashboard.duitku.com/images/logomethod/logoMandiri.png',
                    'BN': 'https://dashboard.duitku.com/images/logomethod/logoBni.png',
                    'BRI': 'https://dashboard.duitku.com/images/logomethod/logoBri.png',
                    'OV': 'https://dashboard.duitku.com/images/logomethod/logoOvo.png',
                    'DA': 'https://dashboard.duitku.com/images/logomethod/logoDana.png',
                    'SP': 'https://dashboard.duitku.com/images/logomethod/logoShopee.png',
                    'LF': 'https://dashboard.duitku.com/images/logomethod/logoLinkaja.png',
                    'A1': 'https://dashboard.duitku.com/images/logomethod/logoAlfamart.png',
                    'I1': 'https://dashboard.duitku.com/images/logomethod/logoIndomaret.png',
                    'VC': 'https://dashboard.duitku.com/images/logomethod/logoVisa.png',
                    'CIMB': 'https://dashboard.duitku.com/images/logomethod/logoCimb.png',
                    'VA': 'https://dashboard.duitku.com/images/logomethod/logoMaybank.png',
                    'FT': 'https://dashboard.duitku.com/images/logomethod/logoQris.png'
                };

                // Group methods by type
                const methodsByType = {
                    'qris': [],
                    'ewallet': [],
                    'bank_transfer': [],
                    'retail': [],
                    'card': []
                };

                result.methods.forEach(method => {
                    const type = method.type || 'bank_transfer';
                    if (methodsByType[type]) {
                        methodsByType[type].push(method);
                    }
                });

                // Check if mobile
                const isMobile = window.innerWidth <= 768;

                // Build HTML for payment methods with modern list design
                let html = '';

                if (isMobile) {
                    html += '<div class="payment-modal-header">';
                    html += '<div class="back-btn" onclick="Swal.close()"><i class="bx bx-arrow-back"></i></div>';
                    html += '<h3>Metode Pembayaran</h3>';
                    html += '</div>';
                }

                html += '<div class="payment-methods-container">';

                // Helper function to create payment item
                const createPaymentItem = (method) => {
                    const logo = logoMap[method.code] || '';
                    const fee = method.fee || 0;
                    const feeType = method.fee_type || 'fixed';
                    let feeText = '+ Rp 0';
                    let feeClass = 'free';

                    if (fee > 0) {
                        if (feeType === 'percent') {
                            feeText = `+ ${fee}%`;
                            feeClass = 'percent';
                        } else {
                            feeText = `+ Rp ${new Intl.NumberFormat('id-ID').format(fee)}`;
                            feeClass = 'charged';
                        }
                    }

                    let item = `<div class="payment-method-item" data-code="${method.code}" data-name="${method.name}">`;
                    item += '<div class="payment-method-icon">';
                    if (logo) {
                        item += `<img src="${logo}" alt="${method.name}">`;
                    } else {
                        item += `<i class="bx bx-credit-card"></i>`;
                    }
                    item += '</div>';
                    item += '<div class="payment-method-details">';
                    item += `<h4>${method.name}</h4>`;
                    item += `<p>Pembayaran Menggunakan ${method.name}</p>`;
                    item += '</div>';
                    item += '<div class="payment-method-fee">';
                    item += `<p class="fee-amount ${feeClass}">${feeText}</p>`;
                    item += '</div>';
                    item += '</div>';
                    return item;
                };

                // QRIS Section
                if (methodsByType.qris.length > 0) {
                    html += '<div class="payment-category-header">QRIS</div>';
                    methodsByType.qris.forEach(method => {
                        html += createPaymentItem(method);
                    });
                }

                // E-Wallet Section
                if (methodsByType.ewallet.length > 0) {
                    html += '<div class="payment-category-header">E-Wallet</div>';
                    methodsByType.ewallet.forEach(method => {
                        html += createPaymentItem(method);
                    });
                }

                // Bank Transfer Section
                if (methodsByType.bank_transfer.length > 0) {
                    html += '<div class="payment-category-header">Virtual Account</div>';
                    methodsByType.bank_transfer.forEach(method => {
                        html += createPaymentItem(method);
                    });
                }

                // Credit Card Section
                if (methodsByType.card.length > 0) {
                    html += '<div class="payment-category-header">Credit Card</div>';
                    methodsByType.card.forEach(method => {
                        html += createPaymentItem(method);
                    });
                }

                // Retail Section
                if (methodsByType.retail.length > 0) {
                    html += '<div class="payment-category-header">Retail</div>';
                    methodsByType.retail.forEach(method => {
                        html += createPaymentItem(method);
                    });
                }

                html += '</div>';

                // Show custom HTML modal
                const swalConfig = {
                    html: html,
                    showConfirmButton: false,
                    showCancelButton: !isMobile,
                    cancelButtonText: 'Batal',
                    showCloseButton: !isMobile,
                    customClass: {
                        container: isMobile ? 'payment-modal-mobile' : '',
                        popup: isMobile ? 'swal2-mobile-fullscreen' : 'compact-payment-modal'
                    },
                    width: isMobile ? '100%' : '600px',
                    padding: '0',
                    background: isMobile ? '#F9FAFB' : 'white',
                    didOpen: () => {
                        // Add click handlers to payment items
                        document.querySelectorAll('.payment-method-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const code = this.getAttribute('data-code');
                                const name = this.getAttribute('data-name');
                                Swal.close();
                                showPaymentConfirmation(invoiceId, invoiceNo, amount, code, name, 'duitku');
                            });
                        });
                    }
                };

                if (!isMobile) {
                    swalConfig.title = '<span style="font-size: 18px; font-weight: 600;">Pilih Metode Pembayaran</span>';
                }

                await Swal.fire(swalConfig);

            } catch (error) {
                console.error('Error fetching payment methods:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Gagal memuat metode pembayaran. Silakan coba lagi.'
                });
            }
        }

        async function showPaymentConfirmation(invoiceId, invoiceNo, amount, paymentMethod, paymentName, gateway) {
            // Get invoice details
            const invoice = <?= !empty($unpaid_invoices) ? json_encode($unpaid_invoices[0]) : 'null' ?>;
            if (!invoice) {
                Swal.fire('Error', 'Data tagihan tidak ditemukan', 'error');
                return;
            }

            const bill = parseFloat(invoice.bill || 0);
            const arrears = parseFloat(invoice.arrears || 0);
            const additionalFee = parseFloat(invoice.additional_fee || 0);
            const discount = parseFloat(invoice.discount || 0);
            const ppn = 0; // Adjust if you have PPN
            const adminFee = 0; // Adjust based on payment method
            const totalAmount = bill + arrears + additionalFee - discount + ppn + adminFee;

            const html = `
                <div class="payment-confirmation-container">
                    <div class="confirmation-card">
                        <div class="confirmation-card-title">RINCIAN PAKET</div>
                        <div class="confirmation-row">
                            <span class="confirmation-label">ID Tagihan</span>
                            <span class="confirmation-value">${invoiceNo}</span>
                        </div>
                        <div class="confirmation-row">
                            <span class="confirmation-label">Nama Profil</span>
                            <span class="confirmation-value">${invoice.package_name || 'Paket Internet'}</span>
                        </div>
                        <div class="confirmation-row">
                            <span class="confirmation-label">Metode Bayar</span>
                            <span class="confirmation-value">${paymentName}</span>
                        </div>
                    </div>

                    <div class="confirmation-card">
                        <div class="confirmation-card-title">RINCIAN TAGIHAN</div>
                        <div class="confirmation-row">
                            <span class="confirmation-label">Jumlah Tagihan</span>
                            <span class="confirmation-value">Rp ${new Intl.NumberFormat('id-ID').format(bill)}</span>
                        </div>
                        ${arrears > 0 ? `<div class="confirmation-row">
                            <span class="confirmation-label">Tunggakan</span>
                            <span class="confirmation-value">Rp ${new Intl.NumberFormat('id-ID').format(arrears)}</span>
                        </div>` : ''}
                        ${discount > 0 ? `<div class="confirmation-row">
                            <span class="confirmation-label">Diskon</span>
                            <span class="confirmation-value">- Rp ${new Intl.NumberFormat('id-ID').format(discount)}</span>
                        </div>` : ''}
                        <div class="confirmation-row">
                            <span class="confirmation-label">PPN</span>
                            <span class="confirmation-value">Rp ${new Intl.NumberFormat('id-ID').format(ppn)}</span>
                        </div>
                        ${additionalFee > 0 ? `<div class="confirmation-row">
                            <span class="confirmation-label">Biaya Admin</span>
                            <span class="confirmation-value">Rp ${new Intl.NumberFormat('id-ID').format(additionalFee)}</span>
                        </div>` : ''}
                        <div class="confirmation-row total">
                            <span class="confirmation-label">Total Bayar</span>
                            <span class="confirmation-value">Rp ${new Intl.NumberFormat('id-ID').format(totalAmount)}</span>
                        </div>
                    </div>
                </div>

                <div class="process-payment-btn">
                    <button onclick="confirmPayment()">Proses Pembayaran</button>
                </div>
            `;

            const result = await Swal.fire({
                html: `
                    <div class="payment-modal-header">
                        <div class="back-btn" onclick="Swal.clickCancel()">
                            <i class="bx bx-arrow-back"></i>
                        </div>
                        <h3>Periksa Pembayaran</h3>
                    </div>
                    ${html}
                `,
                showConfirmButton: false,
                showCancelButton: false,
                customClass: {
                    container: 'payment-modal-mobile',
                    popup: 'swal2-popup-mobile'
                },
                width: '100%',
                padding: 0,
                background: '#f9fafb',
                didOpen: () => {
                    // Store data for confirm button
                    window.pendingPayment = {
                        invoiceId,
                        invoiceNo,
                        amount: totalAmount,
                        paymentMethod,
                        gateway
                    };
                }
            });
        }

        window.confirmPayment = async function() {
            if (!window.pendingPayment) return;

            const {
                invoiceId,
                invoiceNo,
                amount,
                paymentMethod,
                gateway
            } = window.pendingPayment;

            if (gateway === 'duitku') {
                await processDuitkuPaymentFinal(invoiceId, invoiceNo, amount, paymentMethod);
            } else if (gateway === 'midtrans') {
                await processMidtransPaymentFinal(invoiceId, invoiceNo, amount);
            }
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
                const createdDate = invoice.created_at ? new Date(invoice.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';

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
                            <div class="detail-row">
                                <span class="detail-label">Uptime</span>
                                <span class="detail-value">${customer.uptime || '00:00:00'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Unggah</span>
                                <span class="detail-value">${customer.bytes_out || '0.0'} B</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Unduh</span>
                                <span class="detail-value">${customer.bytes_in || '0.0'} B</span>
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
                            <button class="btn-primary" onclick="Swal.close(); showPaymentOptions();">
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

        async function processDuitkuPaymentFinal(invoiceId, invoiceNo, amount, paymentMethod) {

            console.log('Processing Duitku payment:', {
                invoiceId,
                invoiceNo,
                amount,
                paymentMethod
            });

            Swal.fire({
                title: 'Memproses Pembayaran...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfName = document.querySelector('meta[name="csrf-token-name"]').getAttribute('content');

                const payloadData = {
                    invoice_id: parseInt(invoiceId),
                    gateway: 'duitku',
                    payment_code: paymentMethod,
                    amount: parseInt(amount)
                };

                payloadData[csrfName] = csrfToken;

                console.log('Sending Duitku payment request:', payloadData);

                const response = await fetch('<?= site_url('customer-portal/process-payment') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payloadData)
                });

                const result = await response.json();
                console.log('Duitku payment response:', result);

                Swal.close();

                if (result.success && result.payment_url) {
                    // Open payment URL
                    window.open(result.payment_url, '_blank');

                    // Show detail screen after opening payment
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Pembayaran Diproses',
                            text: 'Silakan selesaikan pembayaran di tab baru',
                            icon: 'info',
                            confirmButtonText: 'Lihat Detail',
                            showCancelButton: true,
                            cancelButtonText: 'Nanti'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showInvoiceDetail(invoiceId);
                            }
                        });
                    }, 1000);
                } else {
                    Swal.fire('Gagal', result.message || 'Pembayaran gagal diproses', 'error');
                }
            } catch (error) {
                console.error('Duitku payment error:', error);
                Swal.close();
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            }
        }

        async function processMidtransPayment(invoiceId, invoiceNo, amount) {
            console.log('Processing Midtrans payment:', {
                invoiceId,
                invoiceNo,
                amount
            });

            // Check if Midtrans is available
            if (!window.paymentGateways.midtrans) {
                console.error('Midtrans gateway not active!');
                Swal.fire('Error', 'Payment gateway Midtrans tidak aktif.', 'error');
                return;
            }

            // Check if Midtrans snap is loaded
            if (typeof window.snap === 'undefined') {
                console.error('Midtrans snap.js not loaded!');
                Swal.fire('Error', 'Midtrans belum siap. Silakan refresh halaman atau gunakan payment gateway lain.', 'error');
                return;
            }

            Swal.fire({
                title: 'Memproses Pembayaran...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfName = document.querySelector('meta[name="csrf-token-name"]').getAttribute('content');

                const payloadData = {
                    invoice_id: parseInt(invoiceId),
                    gateway: 'midtrans',
                    payment_code: 'snap',
                    amount: parseInt(amount)
                };

                payloadData[csrfName] = csrfToken;

                console.log('Sending payment request:', payloadData);

                const response = await fetch('<?= site_url('customer-portal/process-payment') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payloadData)
                });

                const result = await response.json();
                console.log('Payment response:', result);

                if (result.success && result.snap_token) {
                    Swal.close();
                    console.log('Opening Midtrans Snap with token:', result.snap_token);

                    window.snap.pay(result.snap_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            Swal.fire('Berhasil!', 'Pembayaran berhasil diproses', 'success')
                                .then(() => {
                                    showInvoiceDetail(invoiceId);
                                });
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            Swal.fire('Menunggu Konfirmasi', 'Pembayaran Anda sedang diproses', 'info')
                                .then(() => {
                                    showInvoiceDetail(invoiceId);
                                });
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            Swal.fire('Gagal', 'Pembayaran gagal diproses', 'error')
                                .then(() => {
                                    showInvoiceDetail(invoiceId);
                                });
                        },
                        onClose: function() {
                            console.log('Payment popup closed by user');
                            showInvoiceDetail(invoiceId);
                        }
                    });
                } else {
                    Swal.fire('Error', result.message || 'Gagal memproses pembayaran', 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                Swal.fire('Error', 'Gagal memproses pembayaran', 'error');
            }
        }

        async function loadPaymentMethods() {
            try {
                console.log('loadPaymentMethods - selectedInvoice:', selectedInvoice);

                if (!selectedInvoice || !selectedInvoice.id) {
                    Swal.fire('Error', 'Invoice data tidak valid', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Memuat Metode Pembayaran...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const paymentMethods = <?= json_encode($active_gateways ?? []) ?>;

                if (!paymentMethods || paymentMethods.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Metode Pembayaran',
                        text: 'Mohon maaf, saat ini tidak ada metode pembayaran yang tersedia.',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                window.currentPaymentMethods = paymentMethods;
                Swal.close();

                // Store invoice ID in hidden input
                console.log('About to store - selectedInvoice:', selectedInvoice);
                console.log('selectedInvoice.id:', selectedInvoice.id, 'type:', typeof selectedInvoice.id);
                document.getElementById('modal-invoice-id').value = selectedInvoice.id;
                console.log('Stored invoice ID in hidden input:', selectedInvoice.id);
                console.log('Hidden input value after set:', document.getElementById('modal-invoice-id').value);

                document.getElementById('invoice-number').textContent = selectedInvoice.no;
                document.getElementById('payment-amount').textContent = `Rp ${formatRupiah(selectedInvoice.amount)}`;
                let methodsHtml = '';
                const categories = {
                    'Bank Transfer': ['bca', 'bni', 'bri', 'mandiri', 'permata'],
                    'Virtual Account': ['bca_va', 'bni_va', 'bri_va', 'mandiri_va'],
                    'E-Wallet': ['gopay', 'shopeepay', 'ovo', 'dana', 'linkaja'],
                    'QRIS': ['qris'],
                    'Convenience Store': ['alfamart', 'indomaret']
                };

                Object.keys(categories).forEach(categoryName => {
                    const categoryMethods = window.currentPaymentMethods.filter(method =>
                        categories[categoryName].includes(method.code)
                    );

                    if (categoryMethods.length > 0) {
                        categoryMethods.forEach((method) => {
                            const adminFee = method.admin_fee || 0;
                            let totalAmount = selectedInvoice.amount;
                            let adminFeeLabel = '';
                            let adminFeeValue = 0; // Nilai admin fee untuk dikirim ke backend

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
                             data-gateway="${method.gateway}" 
                             data-code="${method.code}" 
                             data-name="${method.name}" 
                             data-amount="${totalAmount}"
                             data-admin-fee="${adminFeeValue}"
                             style="cursor: pointer; transition: all 0.2s; border: 1px solid #e5e7eb !important;">
                            <div class="d-flex align-items-center">
                                <div class="me-3">${logo}</div>
                                <div class="flex-1">
                                    <div class="fw-semibold text-dark" style="font-size: 14px;">${method.name}</div>
                                    ${adminFeeLabel}
                                </div>
                                <div class="text-end">
                                    <i class="bx bx-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </div>`;
                        });
                    }
                });

                document.getElementById('payment-methods-list').innerHTML = methodsHtml;

                document.querySelectorAll('.payment-method-item').forEach(item => {
                    item.addEventListener('mouseenter', function() {
                        this.style.borderColor = '#3b82f6';
                        this.style.backgroundColor = '#f8fafc';
                    });
                    item.addEventListener('mouseleave', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.backgroundColor = 'white';
                    });

                    // Add click handler
                    item.addEventListener('click', function() {
                        const invoiceId = document.getElementById('modal-invoice-id').value;
                        const gateway = this.getAttribute('data-gateway');
                        const code = this.getAttribute('data-code');
                        const name = this.getAttribute('data-name');
                        const amount = this.getAttribute('data-amount');
                        const adminFee = this.getAttribute('data-admin-fee'); // Ambil admin fee

                        console.log('Payment method clicked:');
                        console.log('  invoiceId from hidden input:', invoiceId, 'type:', typeof invoiceId);
                        console.log('  gateway:', gateway);
                        console.log('  code:', code);
                        console.log('  amount:', amount);
                        console.log('  adminFee:', adminFee);
                        selectPaymentMethod(gateway, code, name, amount, invoiceId, adminFee);
                    });
                });

                const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                paymentModal.show();

            } catch (error) {
                console.error('Error loading payment methods:', error);
                Swal.fire('Error', 'Gagal memuat metode pembayaran', 'error');
            }
        }

        async function selectPaymentMethod(gateway, code, name, totalAmount, invoiceId, adminFee = 0) {
            console.log('selectPaymentMethod called with:', {
                gateway,
                code,
                name,
                totalAmount,
                invoiceId,
                invoiceIdType: typeof invoiceId,
                adminFee
            });

            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            modal.hide();

            Swal.fire({
                title: 'Memproses Pembayaran...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfName = document.querySelector('meta[name="csrf-token-name"]').getAttribute('content');

                const payloadData = {
                    invoice_id: invoiceId ? parseInt(invoiceId) : 0,
                    gateway: gateway,
                    payment_code: code,
                    amount: parseInt(totalAmount),
                    admin_fee: parseInt(adminFee) // Kirim admin fee ke backend
                };

                // Add CSRF token to payload
                payloadData[csrfName] = csrfToken;

                console.log('Sending payment data:', payloadData);

                const response = await fetch('<?= site_url('customer-portal/process-payment') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payloadData)
                });
                const result = await response.json();
                console.log('Payment response:', result);

                if (result.success) {
                    Swal.close();
                    if (gateway === 'midtrans' && result.snap_token) {
                        console.log('Opening Midtrans snap with token:', result.snap_token);

                        // Check if snap is loaded
                        if (typeof window.snap === 'undefined') {
                            console.error('Midtrans snap.js not loaded!');
                            Swal.fire('Error', 'Midtrans payment gateway belum siap. Silakan refresh halaman.', 'error');
                            return;
                        }

                        window.snap.pay(result.snap_token, {
                            onSuccess: function(result) {
                                console.log('Payment success:', result);
                                Swal.fire('Berhasil!', 'Pembayaran berhasil diproses', 'success')
                                    .then(() => window.location.reload());
                            },
                            onPending: function(result) {
                                console.log('Payment pending:', result);
                                Swal.fire('Menunggu', 'Pembayaran menunggu konfirmasi', 'info')
                                    .then(() => window.location.reload());
                            },
                            onError: function(result) {
                                console.log('Payment error:', result);
                                Swal.fire('Gagal', 'Pembayaran gagal diproses', 'error');
                            },
                            onClose: function() {
                                console.log('Payment popup closed');
                            }
                        });
                    } else {
                        Swal.fire('Berhasil!', result.message, 'success')
                            .then(() => window.location.reload());
                    }
                } else {
                    Swal.fire('Error', result.message || 'Terjadi kesalahan', 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                Swal.fire('Error', 'Gagal memproses pembayaran', 'error');
            }
        }

        function getMethodLogo(code) {
            const logos = {
                'bca': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
                'bni': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
                'bri': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
                'mandiri': '<i class="bx bxl-mastercard font-size-24 text-primary"></i>',
                'bca_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',
                'bni_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',
                'bri_va': '<i class="bx bx-credit-card font-size-24 text-primary"></i>',
                'gopay': '<i class="bx bx-wallet font-size-24 text-success"></i>',
                'qris': '<i class="bx bx-qr font-size-24 text-success"></i>',
                'alfamart': '<i class="bx bx-store font-size-24 text-danger"></i>',
            };
            return logos[code.toLowerCase()] || '<i class="bx bx-credit-card font-size-24 text-primary"></i>';
        }

        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        // Show Invoice Detail Screen
        function showInvoiceDetail(invoice) {
            console.log('Showing invoice detail:', invoice);

            // Calculate amounts
            const bill = parseFloat(invoice.bill || 0);
            const arrears = parseFloat(invoice.arrears || 0);
            const additionalFee = parseFloat(invoice.additional_fee || 0);
            const discount = parseFloat(invoice.discount || 0);
            const ppn = parseFloat(invoice.ppn || 0);
            const adminFee = parseFloat(invoice.admin_fee || 0);
            const totalAmount = bill + arrears + additionalFee - discount + ppn + adminFee;

            // Determine status badge
            let statusBadge = '';
            let statusText = '';
            if (invoice.status === 'paid' || invoice.status === 'Lunas') {
                statusBadge = '<span class="status-badge paid">Sudah Dibayar</span>';
                statusText = 'Sudah Dibayar';
            } else if (invoice.status === 'pending') {
                statusBadge = '<span class="status-badge pending">Menunggu Pembayaran</span>';
                statusText = 'Menunggu Pembayaran';
            } else if (invoice.status === 'failed' || invoice.status === 'Gagal') {
                statusBadge = '<span class="status-badge failed">Gagal</span>';
                statusText = 'Gagal';
            } else {
                statusBadge = '<span class="status-badge unpaid">Belum Dibayar</span>';
                statusText = 'Belum Dibayar';
            }

            // Format payment date
            const paymentDate = invoice.paid_at || invoice.payment_date || '-';
            const formattedDate = paymentDate !== '-' ? new Date(paymentDate).toLocaleString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : '-';

            // Build HTML
            const html = `
                <div class="payment-modal-header">
                    <div class="back-btn" onclick="Swal.close()">
                        <i class="bx bx-arrow-back"></i>
                    </div>
                    <h3>Detail Tagihan</h3>
                </div>
                <div class="invoice-detail-container">
                    <div class="invoice-detail-header">
                        <h3>Silahkan melakukan pembayaran sebesar</h3>
                        <div class="amount">Rp ${formatRupiah(totalAmount)}</div>
                        <p class="subtitle">dengan incian sebagai berikut</p>
                    </div>

                    <!-- LAYANAN Section -->
                    <div class="detail-section">
                        <div class="detail-section-title">Layanan</div>
                        <div class="detail-row">
                            <span class="detail-label">ID Layanan</span>
                            <span class="detail-value">${invoice.customer_id || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama Layanan</span>
                            <span class="detail-value">${invoice.profile_name || 'Hotspot 2Jam'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe Koneksi</span>
                            <span class="detail-value">${invoice.connection_type || 'HOTSPOT'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe Pembayaran</span>
                            <span class="detail-value">${invoice.payment_type || 'Prabayar'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe Langganan</span>
                            <span class="detail-value">${invoice.subscription_type || 'FIXED'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Username</span>
                            <span class="detail-value">${invoice.username || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Uptime</span>
                            <span class="detail-value">${invoice.uptime || '00:00:00'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Unggah</span>
                            <span class="detail-value">${invoice.upload || '0.0 B'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Unduh</span>
                            <span class="detail-value">${invoice.download || '0.0 B'}</span>
                        </div>
                    </div>

                    <!-- TAGIHAN Section -->
                    <div class="detail-section">
                        <div class="detail-section-title">Tagihan</div>
                        <div class="detail-row">
                            <span class="detail-label">ID Tagihan</span>
                            <span class="detail-value">${invoice.invoice_no || invoice.id}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Tagihan</span>
                            <span class="detail-value">Rp ${formatRupiah(bill)}</span>
                        </div>
                        ${arrears > 0 ? `
                        <div class="detail-row">
                            <span class="detail-label">Tunggakan</span>
                            <span class="detail-value">Rp ${formatRupiah(arrears)}</span>
                        </div>
                        ` : ''}
                        ${additionalFee > 0 ? `
                        <div class="detail-row">
                            <span class="detail-label">Biaya Tambahan</span>
                            <span class="detail-value">Rp ${formatRupiah(additionalFee)}</span>
                        </div>
                        ` : ''}
                        ${discount > 0 ? `
                        <div class="detail-row">
                            <span class="detail-label">Diskon</span>
                            <span class="detail-value">Rp ${formatRupiah(discount)}</span>
                        </div>
                        ` : ''}
                        ${ppn > 0 ? `
                        <div class="detail-row">
                            <span class="detail-label">PPN</span>
                            <span class="detail-value">Rp ${formatRupiah(ppn)}</span>
                        </div>
                        ` : ''}
                        ${adminFee > 0 ? `
                        <div class="detail-row">
                            <span class="detail-label">Biaya Admin</span>
                            <span class="detail-value">Rp ${formatRupiah(adminFee)}</span>
                        </div>
                        ` : ''}
                        <div class="detail-row">
                            <span class="detail-label">Kode Unik</span>
                            <span class="detail-value">${invoice.unique_code || invoice.code || '555'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nominal Transfer</span>
                            <span class="detail-value">Rp ${formatRupiah(totalAmount)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Tagihan</span>
                            <span class="detail-value">Rp ${formatRupiah(totalAmount)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">${statusBadge}</span>
                        </div>
                        ${formattedDate !== '-' ? `
                        <div class="detail-row">
                            <span class="detail-label">Dibayar Pada</span>
                            <span class="detail-value">${formattedDate}</span>
                        </div>
                        ` : ''}
                    </div>

                    ${invoice.status !== 'paid' && invoice.status !== 'Lunas' ? `
                    <div class="payment-actions">
                        <button class="btn-primary" onclick="Swal.close(); showPaymentOptions();">
                            Bayar Sekarang
                        </button>
                        <button class="btn-secondary" onclick="Swal.close();">
                            Tutup
                        </button>
                    </div>
                    ` : ''}
                </div>
            `;

            Swal.fire({
                html: html,
                width: '100%',
                showConfirmButton: false,
                showCloseButton: false,
                customClass: {
                    popup: 'payment-modal-mobile'
                },
                padding: 0
            });
        }

        // Add click handler to invoice cards
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile invoice cards (promo cards that show unpaid invoices)
            const invoiceCards = document.querySelectorAll('.promo-card[data-invoice-id]');
            invoiceCards.forEach(card => {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    const invoiceId = this.getAttribute('data-invoice-id');
                    <?php if (!empty($unpaid_invoices)): ?>
                        const invoices = <?= json_encode($unpaid_invoices) ?>;
                        const invoice = invoices.find(inv => inv.id == invoiceId);
                        if (invoice) {
                            showInvoiceDetail(invoice);
                        }
                    <?php endif; ?>
                });
            });
        });
    </script>

    <?= $this->endSection() ?>