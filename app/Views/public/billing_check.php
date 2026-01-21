<!DOCTYPE html>
<html lang="id" data-layout="vertical" data-topbar="light" data-sidebar="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= isset($customer) ? 'Billing Payment - ' . $customer['nama_pelanggan'] : 'Cek Tagihan Internet' ?></title>

    <!-- Page Version for Cache Busting -->
    <script>
        window.pageVersion = '<?= time() ?>';
    </script>

    <!-- Template Assets -->
    <link rel="shortcut icon" href="<?= base_url() ?>backend/assets/images/logo-sm.png">
    <link href="<?= base_url() ?>backend/assets/css/bootstrap.min.css?v=<?= time() ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url() ?>backend/assets/css/icons.min.css?v=<?= time() ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url() ?>backend/assets/css/app.min.css?v=<?= time() ?>" rel="stylesheet" type="text/css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-soft-primary">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-sm-5">
                        <div class="text-center mb-4">
                            <img src="<?= base_url('backend/assets/images/difihome.png') ?>" alt="Logo" class="mb-3" style="max-width:200px; width:100%;">
                            <h4 class="fw-bold text-dark mb-1">
                                <?= isset($company['name']) && $company['name'] ? esc($company['name']) : 'PT. KIMONET DIGITAL SYNERGY' ?>
                            </h4>
                            <p class="text-muted mb-0">Cek atau Bayar Tagihan Internet</p>
                        </div><?php if (isset($error)): ?>
                            <div class="alert alert-danger text-center">
                                <i class="mdi mdi-alert-circle-outline h1 mb-3"></i>
                                <h5 class="alert-heading">Tagihan Tidak Ditemukan</h5>
                                <p class="mb-2">Nomor pelanggan yang Anda cari tidak ditemukan dalam sistem</p>
                                <hr>
                                <p class="mb-0 small"><?= esc($error) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($customer) && $customer): ?>
                            <!-- DETAIL TAGIHAN -->
                            <?= view('public/billing_detail', [
                                'customer' => $customer,
                                'unpaidInvoices' => $unpaidInvoices,
                                'activeGateways' => $activeGateways
                            ]) ?>
                        <?php else: ?>
                            <form method="get" action="<?= base_url() ?>public-billing/check-bill" autocomplete="off">
                                <div class="mb-3">
                                    <label for="nomor_layanan" class="form-label fw-semibold">Nomor Internet</label>
                                    <input type="text" id="nomor_layanan" name="nomor_layanan"
                                        class="form-control form-control-lg text-center fw-bold"
                                        placeholder="Masukkan nomor internet Anda"
                                        maxlength="20" required autofocus value="<?= esc($nomor_layanan ?? '') ?>">
                                </div>
                                <button type="submit" class="btn btn-primary btn w-100 fw-bold">
                                    <i class="mdi mdi-magnify me-1"></i> CEK TAGIHAN
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-2">Download aplikasi android</p>
                            <a href="#" target="_blank" class="d-inline-block mb-3">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/7/7a/Google_Play_2022_logo.svg"
                                    alt="Google Play" style="height:40px;">
                            </a>
                            <p class="text-muted small mb-0">
                                Support by Billing <a href="https://sisbro.id" class="text-primary" target="_blank">difihome.my.id</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Add CSRF token for secure requests
            const csrfToken = '<?= csrf_hash() ?>';
            const csrfName = '<?= csrf_token() ?>';

            document.addEventListener('DOMContentLoaded', function() {
                var input = document.getElementById('nomor_layanan');
                if (input && !input.value) {
                    // Only load from localStorage, no dummy data
                    input.value = localStorage.getItem('last_nomor_layanan') || '';
                }
                if (input) {
                    input.addEventListener('change', function() {
                        localStorage.setItem('last_nomor_layanan', input.value);
                    });
                }

                // Check URL parameters for payment status
                checkUrlParameters();
            });

            // Force refresh function with cache bust
            function forceRefresh() {
                // Add timestamp to bust cache
                const timestamp = new Date().getTime();
                const url = window.location.pathname + '?_t=' + timestamp;
                window.location.href = url;
            }

            // Detect payment success from URL parameters
            function checkUrlParameters() {
                const urlParams = new URLSearchParams(window.location.search);
                const orderStatus = urlParams.get('order_status');
                const transactionStatus = urlParams.get('transaction_status');
                const statusCode = urlParams.get('status_code');
                const orderId = urlParams.get('order_id');
                const paymentSuccess = urlParams.get('payment_success');
                const cacheTimestamp = urlParams.get('_t');

                // If only cache-bust parameter exists, don't show any modal
                if (cacheTimestamp && !orderStatus && !transactionStatus && !paymentSuccess) {
                    return;
                }

                // Check if returning from Midtrans payment
                if (orderStatus === 'success' || transactionStatus === 'settlement' || transactionStatus === 'capture' || paymentSuccess === '1') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        html: '<p class="mb-2">Tagihan Anda telah berhasil dibayar.</p>' +
                            '<p class="text-muted small">Status akun Anda akan segera aktif kembali.</p>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // Clean URL and reload to refresh invoice status
                        window.location.href = window.location.pathname;
                    });
                } else if (transactionStatus === 'pending') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Pembayaran',
                        html: '<p class="mb-2">Pembayaran Anda sedang diproses.</p>' +
                            '<p class="text-muted small">Silakan selesaikan pembayaran sesuai instruksi.</p>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.href = window.location.pathname;
                    });
                } else if (transactionStatus === 'deny' || transactionStatus === 'expire' || transactionStatus === 'cancel' || orderStatus === 'failed') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Pembayaran Gagal',
                        html: '<p class="mb-2">Pembayaran Anda tidak dapat diproses.</p>' +
                            '<p class="text-muted small">Silakan coba lagi atau gunakan metode pembayaran lain.</p>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ef4444'
                    }).then(() => {
                        window.location.href = window.location.pathname;
                    });
                }
            }

            // Function to check payment status manually
            async function checkPaymentStatus(orderId) {
                try {
                    // If no orderId, just do nothing
                    if (!orderId || orderId === 'undefined' || orderId === 'null' || orderId.trim() === '') {
                        return;
                    }

                    Swal.fire({
                        title: 'Mengecek Status Pembayaran...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const response = await fetch(`<?= base_url('check-payment-status/') ?>${orderId}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.success && result.status === 'paid') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            html: '<p class="mb-2">' + result.message + '</p>' +
                                '<p class="text-muted small">Halaman akan di-refresh untuk menampilkan status terbaru.</p>',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else if (result.success && result.status === 'pending') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Pembayaran Menunggu',
                            html: '<p class="mb-2">' + result.message + '</p>' +
                                '<p class="text-muted small">Status: ' + result.transaction_status + '</p>',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3b82f6'
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Status Belum Berubah',
                            text: result.message || 'Pembayaran masih dalam proses.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#f59e0b'
                        });
                    }
                } catch (error) {
                    console.error('Check status error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengecek status pembayaran.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        </script>
        <script>
            let selectedInvoice = null;

            function payInvoice(invoiceId) {
                console.log('payInvoice called with ID:', invoiceId);
                selectedInvoice = invoiceId;

                // Check if gateways are available
                <?php if (!empty($activeGateways)): ?>
                    // Load payment methods directly
                    loadPaymentMethods(invoiceId);
                <?php else: ?>
                    // Show warning for no gateways
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Metode Pembayaran',
                        text: 'Mohon maaf, saat ini tidak ada metode pembayaran yang tersedia. Silakan hubungi customer service.',
                        confirmButtonColor: '#2563eb'
                    });
                <?php endif; ?>
            }

            async function loadPaymentMethods(invoiceId) {
                try {
                    // Show loading
                    Swal.fire({
                        title: 'Memuat Metode Pembayaran...',
                        html: 'Mengambil daftar rekening pembayaran yang tersedia',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Fetch payment methods from all active gateways with CSRF token
                    const formData = new FormData();
                    formData.append(csrfName, csrfToken);

                    const response = await fetch('<?= base_url('api/payment-methods') ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    // Get invoice details for payment amount calculation
                    const invoiceFormData = new FormData();
                    invoiceFormData.append(csrfName, csrfToken);

                    const invoiceResponse = await fetch(`<?= base_url('api/invoice-details/') ?>${invoiceId}`, {
                        method: 'POST',
                        body: invoiceFormData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    let invoiceData = null;
                    try {
                        if (invoiceResponse.ok) {
                            invoiceData = await invoiceResponse.json();
                        }
                    } catch (e) {
                        console.log('Could not fetch invoice details, using default values');
                    }

                    if (data.success && data.payment_methods && data.payment_methods.length > 0) {
                        showPaymentMethodsModal(data.payment_methods, invoiceId, invoiceData);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Metode Pembayaran',
                            text: data.message || 'Tidak ada metode pembayaran yang tersedia saat ini.',
                            confirmButtonColor: '#2563eb'
                        });
                    }
                } catch (error) {
                    console.error('Error loading payment methods:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat memuat metode pembayaran. Silakan coba lagi.',
                        confirmButtonColor: '#2563eb'
                    });
                }
            }

            function showPaymentMethodsModal(paymentMethods, invoiceId, invoiceData) {
                // Calculate payment amounts with enhanced debugging and proper parsing
                let baseAmount = 0;

                // Debug invoice data
                console.log('Invoice Data received:', invoiceData);

                // Priority 1: Use API data if available
                if (invoiceData && invoiceData.success && invoiceData.data && invoiceData.data.bill) {
                    const billValue = invoiceData.data.bill;
                    console.log('Bill value from API:', billValue, 'Type:', typeof billValue);

                    // Ensure we get the correct numeric value
                    if (typeof billValue === 'number') {
                        baseAmount = billValue;
                    } else if (typeof billValue === 'string') {
                        // Remove any non-numeric characters except digits
                        const cleanBill = billValue.replace(/[^\d]/g, '');
                        baseAmount = parseInt(cleanBill) || 0;
                    } else {
                        baseAmount = parseInt(billValue) || 0;
                    }
                    console.log('Parsed API amount:', baseAmount);
                }

                // Priority 2: Fallback to PHP data if API fails
                if (baseAmount === 0) {
                    <?php if (isset($unpaidInvoices) && !empty($unpaidInvoices)): ?>
                        const phpBillValue = <?= $unpaidInvoices[0]['bill'] ?? 0 ?>;
                        console.log('Bill value from PHP:', phpBillValue, 'Type:', typeof phpBillValue);
                        baseAmount = parseInt(phpBillValue) || 0;
                        console.log('Parsed PHP amount:', baseAmount);
                    <?php endif; ?>
                }

                // Priority 3: Try DOM extraction as last resort
                if (baseAmount === 0) {
                    const billElement = document.querySelector('input[value*="Rp"]');
                    if (billElement) {
                        const billText = billElement.value;
                        console.log('Bill text from DOM:', billText);

                        // Enhanced regex to handle Indonesian number format
                        const billMatch = billText.match(/Rp\s*([\d.,]+)/);
                        if (billMatch) {
                            // Remove all non-digit characters for proper parsing
                            const cleanAmount = billMatch[1].replace(/[^\d]/g, '');
                            baseAmount = parseInt(cleanAmount) || 0;
                            console.log('Extracted amount from DOM:', billMatch[1], '→ Clean:', cleanAmount, '→ Parsed:', baseAmount);
                        }
                    }
                }

                // Priority 4: Set demo default if everything fails
                if (baseAmount === 0) {
                    baseAmount = 150000; // Default amount for demo
                    console.log('Using fallback default amount:', baseAmount);
                }

                console.log('=== FINAL AMOUNT CALCULATION ===');
                console.log('Base amount:', baseAmount);
                console.log('Formatted display: Rp', new Intl.NumberFormat('id-ID').format(baseAmount));

                // Hanya tampilkan metode pembayaran yang aktif dari API
                let methodsHtml = `
                <div class="mb-4 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Tagihan</small><br>
                            <strong>Rp ${formatRupiah(baseAmount)}</strong>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">Total Bayar</small><br>
                            <strong class="text-primary fs-5">Rp ${formatRupiah(baseAmount)}</strong>
                        </div>
                    </div>
                </div>
            `;

                if (Array.isArray(paymentMethods) && paymentMethods.length > 0) {
                    methodsHtml += `<div class="mb-4"><div class="list-group">`;
                    paymentMethods.forEach((method, index) => {
                        const adminFee = method.admin_fee || 0;
                        let totalAmount = baseAmount;
                        let adminFeeValue = 0; // Nilai admin fee untuk dikirim ke backend
                        let adminFeeLabel = '';

                        // Debug log
                        console.log(`Method: ${method.name}, Code: ${method.code}, Admin Fee: ${adminFee}, Type: ${method.admin_fee_type}`);

                        if (method.admin_fee_type === 'percent') {
                            // adminFee sudah dalam bentuk persentase (0.7, 1.67, 2) jadi langsung dibagi 100
                            const feeNominal = Math.round(baseAmount * (adminFee / 100));
                            totalAmount += feeNominal;
                            adminFeeValue = feeNominal;
                            adminFeeLabel = `<small class="text-muted">Admin: ${adminFee}% (Rp ${formatRupiah(feeNominal)})</small>`;
                        } else if (adminFee > 0) {
                            totalAmount += adminFee;
                            adminFeeValue = adminFee;
                            adminFeeLabel = `<small class="text-muted">Admin: Rp ${formatRupiah(adminFee)}</small>`;
                        } else {
                            adminFeeLabel = '<small class="text-success">Tanpa Biaya Admin</small>';
                        }
                        const logo = getMethodLogo(method.code);
                        methodsHtml += `
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action" 
                             onclick="selectPaymentMethod('${method.gateway}', '${method.code}', '${method.name}', ${invoiceId}, ${totalAmount}, ${adminFeeValue})">
                            <div class="row align-items-center">
                                <div class="col-2 col-sm-1 text-center">
                                    ${logo}
                                </div>
                                <div class="col-6 col-sm-7">
                                    <div class="fw-semibold">${method.name}</div>
                                    <small class="text-muted">${method.provider || method.gateway.toUpperCase()}</small>
                                </div>
                                <div class="col-4 text-end">
                                    <strong class="text-primary d-block">Rp ${formatRupiah(totalAmount)}</strong>
                                    ${adminFeeLabel}
                                </div>
                            </div>
                        </a>`;
                    });
                    methodsHtml += '</div>';
                } else {
                    methodsHtml += `<div class='alert alert-warning text-center mb-0'>Tidak ada metode pembayaran aktif.</div>`;
                }

                Swal.fire({
                    title: '<i class="mdi mdi-credit-card-multiple me-2"></i>Pilih Metode Pembayaran',
                    html: methodsHtml,
                    width: '600px',
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: '<i class="mdi mdi-close me-1"></i> Tutup',
                    customClass: {
                        cancelButton: 'btn btn-secondary'
                    }
                });
            }

            function groupPaymentMethods(methods) {
                const groups = {
                    bank_transfer: [],
                    ewallet: [],
                    card: [],
                    qr_code: [],
                    other: []
                };

                methods.forEach(method => {
                    const type = getMethodType(method.code);
                    groups[type].push(method);
                });

                return groups;
            }

            function getTypeLabel(type) {
                const labels = {
                    bank_transfer: 'Transfer Bank',
                    ewallet: 'E-Wallet',
                    card: 'Kartu Kredit/Debit',
                    qr_code: 'QRIS',
                    other: 'Metode Lainnya'
                };
                return labels[type] || 'Lainnya';
            }

            function getMethodIcon(code) {
                const icons = {
                    'bca_va': 'bank',
                    'bni_va': 'bank',
                    'bri_va': 'bank',
                    'mandiri_va': 'bank',
                    'permata_va': 'bank',
                    'echannel': 'bank',
                    'gopay': 'phone',
                    'ovo': 'wallet2',
                    'dana': 'wallet',
                    'shopeepay': 'bag-check',
                    'linkaja': 'phone',
                    'credit_card': 'credit-card',
                    'qris': 'qr-code'
                };
                return icons[code] || 'payment';
            }

            function getMethodColor(type) {
                const colors = {
                    'bank_transfer': 'primary',
                    'ewallet': 'success',
                    'card': 'info',
                    'qr_code': 'warning',
                    'other': 'secondary'
                };
                return colors[type] || 'secondary';
            }

            function selectPaymentMethod(gateway, methodCode, methodName, invoiceId, totalAmount, adminFee = 0) {
                console.log('selectPaymentMethod called with adminFee:', adminFee);
                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    html: `
                    <div class="text-center">
                        <div class="mb-3">
                            ${getMethodLogo(methodCode)}
                            <h5 class="mt-3">${methodName}</h5>
                            <small class="text-muted">Gateway: ${gateway.toUpperCase()}</small>
                        </div>
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="fw-bold text-primary fs-4">Total Pembayaran</div>
                            <div class="fw-bold text-dark fs-3">Rp ${formatRupiah(totalAmount)}</div>
                        </div>
                        <p>Anda akan diarahkan ke halaman pembayaran untuk melanjutkan transaksi.</p>
                    </div>
                `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processPayment(invoiceId, gateway, methodCode, adminFee);
                    }
                });
            }

            function getMethodType(code) {
                if (code.includes('_va') || code === 'echannel') return 'bank_transfer';
                if (['gopay', 'ovo', 'dana', 'shopeepay', 'linkaja'].includes(code)) return 'ewallet';
                if (code === 'credit_card') return 'card';
                if (code === 'qris') return 'qr_code';
                return 'other';
            }

            function processPayment(invoiceId, gateway, methodCode, adminFee = 0) {
                console.log('processPayment called with adminFee:', adminFee);
                // Show loading
                Swal.fire({
                    title: 'Memproses Pembayaran...',
                    html: 'Sedang membuat transaksi pembayaran',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create form data with CSRF token
                const formData = new FormData();
                formData.append('invoice_id', invoiceId);
                formData.append('gateway', gateway);
                formData.append('method', methodCode);
                formData.append('admin_fee', adminFee); // Kirim admin fee ke backend
                formData.append(csrfName, csrfToken);

                // Send payment request with proper headers and error handling
                fetch('<?= base_url('billing/pay') ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Payment response:', data);

                        if (data.success) {
                            if (data.payment_url) {
                                // Open payment URL in new tab
                                const paymentWindow = window.open(data.payment_url, '_blank');

                                if (paymentWindow) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Halaman pembayaran telah dibuka. Silakan selesaikan pembayaran.',
                                        confirmButtonText: 'Refresh Halaman',
                                        confirmButtonColor: '#2563eb'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    // Popup blocked, show manual link
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Popup Diblokir',
                                        html: `
                                    <p>Browser memblokir popup. Silakan klik link di bawah untuk membuka halaman pembayaran:</p>
                                    <a href="${data.payment_url}" target="_blank" class="btn btn-primary">Buka Halaman Pembayaran</a>
                                `,
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#2563eb'
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Pembayaran berhasil diproses.',
                                    confirmButtonColor: '#2563eb'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message || 'Terjadi kesalahan saat memproses pembayaran.',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Payment error:', error);

                        let errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';

                        if (error.message.includes('403')) {
                            errorMessage = 'Akses ditolak. Silakan refresh halaman dan coba lagi.';
                        } else if (error.message.includes('404')) {
                            errorMessage = 'Layanan pembayaran tidak ditemukan.';
                        } else if (error.message.includes('500')) {
                            errorMessage = 'Terjadi kesalahan server. Silakan coba beberapa saat lagi.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            confirmButtonColor: '#2563eb'
                        });
                    });
            }

            // Helper functions for formatting and logos
            function formatRupiah(amount) {
                return new Intl.NumberFormat('id-ID').format(amount);
            }

            function getMethodLogo(methodCode) {
                // Return logo/icon HTML using mdi icons from template
                const icons = {
                    'bca_va': '<span class="avatar-sm rounded bg-primary text-white"><span class="avatar-title fs-4">BCA</span></span>',
                    'bni_va': '<span class="avatar-sm rounded bg-warning text-white"><span class="avatar-title fs-4">BNI</span></span>',
                    'bri_va': '<span class="avatar-sm rounded bg-info text-white"><span class="avatar-title fs-4">BRI</span></span>',
                    'mandiri_va': '<span class="avatar-sm rounded bg-warning text-dark"><span class="avatar-title fs-4">MDR</span></span>',
                    'permata_va': '<span class="avatar-sm rounded bg-success text-white"><span class="avatar-title fs-5">PMT</span></span>',
                    'echannel': '<span class="avatar-sm rounded bg-primary text-white"><span class="avatar-title fs-5">ECH</span></span>',
                    'gopay': '<span class="avatar-sm rounded-circle bg-success text-white"><span class="avatar-title">GP</span></span>',
                    'ovo': '<span class="avatar-sm rounded-circle bg-purple text-white"><span class="avatar-title">OVO</span></span>',
                    'dana': '<span class="avatar-sm rounded-circle bg-info text-white"><span class="avatar-title">DN</span></span>',
                    'shopeepay': '<span class="avatar-sm rounded bg-danger text-white"><span class="avatar-title fs-5">SP</span></span>',
                    'linkaja': '<span class="avatar-sm rounded bg-danger text-white"><span class="avatar-title fs-5">LA</span></span>',
                    'credit_card': '<span class="avatar-sm rounded bg-primary text-white"><span class="avatar-title"><i class="mdi mdi-credit-card fs-4"></i></span></span>',
                    'qris': '<span class="avatar-sm rounded bg-warning text-white"><span class="avatar-title"><i class="mdi mdi-qrcode fs-4"></i></span></span>',
                    'bca': '<span class="avatar-sm rounded bg-primary text-white"><span class="avatar-title fs-4">BCA</span></span>',
                    'bni': '<span class="avatar-sm rounded bg-warning text-white"><span class="avatar-title fs-4">BNI</span></span>',
                    'bri': '<span class="avatar-sm rounded bg-info text-white"><span class="avatar-title fs-4">BRI</span></span>',
                    'mandiri': '<span class="avatar-sm rounded bg-warning text-dark"><span class="avatar-title fs-4">MDR</span></span>',
                    'permata': '<span class="avatar-sm rounded bg-success text-white"><span class="avatar-title fs-5">PMT</span></span>',
                    'cimb': '<span class="avatar-sm rounded bg-danger text-white"><span class="avatar-title fs-4">CMB</span></span>',
                    'danamon': '<span class="avatar-sm rounded bg-primary text-white"><span class="avatar-title fs-5">DNM</span></span>',
                    'alfamart': '<span class="avatar-sm rounded bg-danger text-white"><span class="avatar-title fs-5">ALF</span></span>',
                    'indomaret': '<span class="avatar-sm rounded bg-warning text-dark"><span class="avatar-title fs-5">IDM</span></span>'
                };

                return icons[methodCode] || '<span class="avatar-sm rounded bg-secondary text-white"><span class="avatar-title"><i class="mdi mdi-cash fs-4"></i></span></span>';
            }
        </script>
</body>

</html>