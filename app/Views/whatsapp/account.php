<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>Whatsapp Account &mdash; WhatsApp Management</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Whatsapp Account</h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('whatsapp/settings') ?>" class="btn btn-secondary me-2">
                            <i class="bx bx-cog"></i> Pengaturan Notifikasi
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                            <i class="bx bx-plus"></i> Add Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <?php if (empty($accounts)): ?>
            <!-- Alert jika belum ada perangkat -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">
                            <i class="bx bxl-whatsapp me-2"></i>Perangkat WhatsApp Belum Terhubung
                        </h4>
                        <p class="mb-2">Anda belum menambahkan perangkat WhatsApp. Untuk mengirim notifikasi WhatsApp, Anda perlu:</p>
                        <ol class="mb-3">
                            <li>Klik tombol <strong>"Add Account"</strong> di pojok kanan atas</li>
                            <li>Masukkan nomor WhatsApp Anda (contoh: 628123456789)</li>
                            <li>Scan QR Code yang muncul menggunakan WhatsApp di ponsel Anda</li>
                            <li>Setelah tersambung, perangkat akan aktif dan siap mengirim notifikasi</li>
                        </ol>
                        <hr>
                        <p class="mb-0">
                            <strong>Catatan:</strong> Pastikan nomor WhatsApp yang didaftarkan adalah nomor aktif dan terhubung ke internet.
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($accounts)): ?>
            <!-- Info card jika sudah ada perangkat -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Perangkat Aktif:</strong>
                        Anda memiliki <?= count($accounts) ?> perangkat WhatsApp yang terdaftar.
                        Pastikan status perangkat dalam kondisi <span class="badge bg-success">Active</span> untuk dapat mengirim notifikasi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="accountTable" class="table table-bordered dt-responsive nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action</th>
                                        <th>Phone Number</th>
                                        <th>Account Name</th>
                                        <th>QR Code</th>
                                        <th>Is Active?</th>
                                        <th>Error Logs</th>
                                        <th>Last Online</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($accounts) && !empty($accounts)): ?>
                                        <?php foreach ($accounts as $account): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="showQRCode('<?= $account['id'] ?>')">
                                                        <i class="bx bx-qr"></i> Reload
                                                    </button>
                                                </td>
                                                <td><?= esc($account['number'] ?? '-') ?></td>
                                                <td>Demo</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="showQRCode('<?= $account['id'] ?>')">
                                                        Wait for QR Code...
                                                    </button>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                                <td>
                                                    <span class="text-muted">-</span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($account['updated_at'])): ?>
                                                        <?= date('d M Y H:i', strtotime($account['updated_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bx bxl-whatsapp font-size-24 d-block mb-2"></i>
                                                    No WhatsApp accounts configured
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($accounts)): ?>
            <!-- Panduan Setup Card -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title text-white mb-0">
                                <i class="bx bx-book-open me-2"></i>Panduan Setup WhatsApp Account
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Langkah 1: Tambah Account</h6>
                                    <ul class="ps-3">
                                        <li>Klik tombol <strong>"Add Account"</strong></li>
                                        <li>Masukkan nomor WhatsApp dengan kode negara (628xxx)</li>
                                        <li>Format: 628xxxxxxxxxx (tanpa +, -, atau spasi)</li>
                                        <li>Klik <strong>"Add Account"</strong></li>
                                    </ul>

                                    <h6 class="text-primary mt-3">Langkah 2: Scan QR Code</h6>
                                    <ul class="ps-3">
                                        <li>Setelah account ditambahkan, klik tombol <strong>"Wait for QR Code..."</strong></li>
                                        <li>QR Code akan muncul di modal</li>
                                        <li>Buka WhatsApp di ponsel Anda</li>
                                        <li>Masuk ke <strong>Settings → Linked Devices → Link a Device</strong></li>
                                        <li>Scan QR Code yang muncul</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Langkah 3: Verifikasi Koneksi</h6>
                                    <ul class="ps-3">
                                        <li>Status akan berubah menjadi <span class="badge bg-success">Active</span></li>
                                        <li>Perangkat siap mengirim notifikasi</li>
                                        <li>Cek "Last Online" untuk melihat koneksi terakhir</li>
                                    </ul>

                                    <div class="alert alert-info mt-3">
                                        <strong><i class="bx bx-info-circle me-1"></i>Tips:</strong>
                                        <ul class="mb-0 ps-3">
                                            <li>Pastikan ponsel terhubung ke internet</li>
                                            <li>Jangan logout dari WhatsApp Web</li>
                                            <li>Reload QR jika expired (2 menit)</li>
                                            <li>Gunakan nomor yang selalu aktif</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-center">
                                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                    <i class="bx bx-plus-circle me-2"></i>Tambah WhatsApp Account Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addAccountForm" action="<?= base_url('whatsapp/account/add') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalLabel">Add WhatsApp Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="628xxxxxxxxxx" required>
                        <small class="text-muted">Enter phone number with country code (e.g., 628123456789)</small>
                    </div>
                    <div class="mb-3">
                        <label for="account_name" class="form-label">Account Name</label>
                        <input type="text" class="form-control" id="account_name" name="account_name" placeholder="Account Name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrCodeModalLabel">
                    <i class="bx bxl-whatsapp me-2"></i>Scan QR Code - WhatsApp Authentication
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeContainer">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Generating QR Code...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="btnReloadQR" onclick="reloadQRCode()">
                    <i class="bx bx-refresh"></i> Reload QR Code
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Required datatable js -->
<script src="<?= base_url('assets/libs/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('#accountTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true
        });
    });

    let currentAccountId = null;

    function showQRCode(accountId) {
        currentAccountId = accountId;
        $('#qrCodeModal').modal('show');
        loadQRCode(accountId);
    }

    function reloadQRCode() {
        if (currentAccountId) {
            loadQRCode(currentAccountId);
        }
    }

    function loadQRCode(accountId) {
        $('#qrCodeContainer').html(`
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Generating QR Code...</p>
        `);

        // Fetch QR Code from server
        fetch('<?= base_url('whatsapp/account/qrcode/') ?>' + accountId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.already_connected) {
                    // Device already connected - show success message
                    $('#qrCodeContainer').html(`
                        <div class="alert alert-success">
                            <i class="bx bx-check-circle font-size-48 d-block mb-3"></i>
                            <h5 class="alert-heading">Perangkat Sudah Terhubung!</h5>
                            <p class="mb-0">${data.message}</p>
                            <hr>
                            <p class="mb-0 small">
                                WhatsApp Anda siap digunakan untuk mengirim notifikasi billing. 
                                Anda tidak perlu scan QR Code lagi kecuali perangkat terputus.
                            </p>
                        </div>
                    `);
                    // Hide reload button since device is connected
                    $('#btnReloadQR').hide();
                } else if (data.success && data.qrcode) {
                    // QR Code generated successfully
                    $('#qrCodeContainer').html(`
                        <div class="mb-3">
                            <img src="${data.qrcode}" alt="QR Code" class="img-fluid border rounded p-2" style="max-width: 350px; background: white;">
                        </div>
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bx bx-info-circle"></i> Cara Scan:</h6>
                            <ol class="mb-0 text-start">
                                <li>Buka WhatsApp di ponsel Anda</li>
                                <li>Tap <strong>Menu (⋮)</strong> atau <strong>Settings</strong></li>
                                <li>Pilih <strong>Linked Devices</strong></li>
                                <li>Tap <strong>Link a Device</strong></li>
                                <li>Arahkan kamera ke QR Code ini</li>
                            </ol>
                        </div>
                        <p class="text-muted small">QR Code akan expired dalam 2 menit. Klik "Reload" jika expired.</p>
                    `);
                    $('#btnReloadQR').show();
                } else {
                    // Error generating QR Code
                    $('#qrCodeContainer').html(`
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle font-size-24"></i>
                            <h6 class="mt-2">Gagal Generate QR Code</h6>
                            <p>${data.message || 'Terjadi kesalahan saat generate QR Code'}</p>
                            <hr>
                            <p class="mb-0 small text-start">
                                <strong>Troubleshooting:</strong><br>
                                1. <strong>Cek Server:</strong> Pastikan server WhatsApp API aktif di <code>${'<?= getenv("WHATSAPP_BASE_URL") ?>' || 'https://wazero.difihome.my.id'}</code><br>
                                2. <strong>Cek Koneksi:</strong> Pastikan internet Anda stabil<br>
                                3. <strong>Cek Nomor:</strong> Pastikan nomor WhatsApp belum terhubung di perangkat lain<br>
                                4. <strong>Cek API Key:</strong> API Key sudah ter-generate otomatis<br>
                                5. <strong>Reload:</strong> Klik tombol "Reload QR Code" untuk mencoba lagi<br>
                                6. <strong>Log:</strong> Cek writable/logs/log-<?= date('Y-m-d') ?>.php untuk detail error
                            </p>
                        </div>
                    `);
                    $('#btnReloadQR').show();
                }
            })
            .catch(error => {
                console.error('QR Code error:', error);
                $('#qrCodeContainer').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-x-circle font-size-24"></i>
                        <h6 class="mt-2">Error Loading QR Code</h6>
                        <p>Tidak dapat terhubung ke server.</p>
                        <p class="mb-0 small">${error.message || 'Unknown error'}</p>
                    </div>
                `);
            });
    }
</script>
<?= $this->endSection() ?>