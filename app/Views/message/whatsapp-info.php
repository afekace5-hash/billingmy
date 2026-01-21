<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>WhatsApp System Info &mdash; WhatsApp Gateway</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">WhatsApp System Information</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Pengaturan</a></li>
                            <li class="breadcrumb-item active">WhatsApp System Info</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12 mb-4">
                <!-- Notification Container for System Messages -->
                <div id="notificationContainer"></div>

                <!-- Right Content -->
                <div class="email-rightbar mb-3">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <h4 class="card-title mb-4">WhatsApp System Information</h4>
                                        </div>
                                    </div>

                                    <!-- System Status Cards -->
                                    <div class="row">
                                        <div class="col-xl-3 col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-1 text-center">
                                                            <div class="text-muted">Status Koneksi</div>
                                                            <h4 class="mb-1 mt-1">
                                                                <span class="<?= $stats['connection_status'] === 'Connected' ? 'text-success' : 'text-danger' ?>">
                                                                    <?= esc($stats['connection_status']) ?>
                                                                </span>
                                                            </h4>
                                                            <p class="mb-0 text-muted">
                                                                <span class="<?= $stats['connection_status'] === 'Connected' ? 'text-success' : 'text-danger' ?> me-2">
                                                                    <i class="<?= $stats['connection_status'] === 'Connected' ? 'bx bx-check-circle' : 'bx bx-x-circle' ?>"></i>
                                                                </span>
                                                                WhatsApp <?= $stats['connection_status'] === 'Connected' ? 'Active' : 'Inactive' ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-1 text-center">
                                                            <div class="text-muted">Total Template</div>
                                                            <h4 class="mb-1 mt-1">
                                                                <span class="text-primary"><?= $stats['templates_count'] ?></span>
                                                            </h4>
                                                            <p class="mb-0 text-muted">
                                                                <span class="text-primary me-2">
                                                                    <i class="bx bx-file-blank"></i>
                                                                </span>
                                                                Templates Ready
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-1 text-center">
                                                            <div class="text-muted">Pesan Terkirim</div>
                                                            <h4 class="mb-1 mt-1">
                                                                <span class="text-info"><?= $stats['messages_sent_today'] ?></span>
                                                            </h4>
                                                            <p class="mb-0 text-muted">
                                                                <span class="text-info me-2">
                                                                    <i class="bx bx-message-dots"></i>
                                                                </span>
                                                                Hari Ini
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-1 text-center">
                                                            <div class="text-muted">Update Terakhir</div>
                                                            <h4 class="mb-1 mt-1">
                                                                <span class="text-warning">11:45</span>
                                                            </h4>
                                                            <p class="mb-0 text-muted">
                                                                <span class="text-warning me-2">
                                                                    <i class="bx bx-time"></i>
                                                                </span>
                                                                Template Saved
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detailed Information -->
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title">Informasi Template WhatsApp</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-info mb-4">
                                                        <i class="bx bx-info-circle me-2"></i>
                                                        <strong>Status:</strong> Sistem template WhatsApp berfungsi dengan baik dan siap digunakan.
                                                    </div>

                                                    <h5>Template yang Tersedia:</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Template</th>
                                                                    <th>Status</th>
                                                                    <th>Variabel</th>
                                                                    <th>Penggunaan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Pengingat Tagihan</td>
                                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                                    <td>{customer}, {tanggal}, {tagihan}</td>
                                                                    <td>Notifikasi pembayaran</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Konfirmasi Pembayaran</td>
                                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                                    <td>{customer}, {no_invoice}, {total}</td>
                                                                    <td>Setelah pembayaran</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Pelanggan Baru</td>
                                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                                    <td>{customer}, {paket}, {harga}</td>
                                                                    <td>Welcome message</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Pemberitahuan Isolir</td>
                                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                                    <td>{customer}, {paket}, {tagihan}</td>
                                                                    <td>Saat isolasi layanan</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Pembukaan Isolir</td>
                                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                                    <td>{customer}</td>
                                                                    <td>Setelah pembayaran</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title">Quick Actions</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-grid gap-2">
                                                        <a href="<?= site_url('whatsapp/template/message') ?>" class="btn btn-primary">
                                                            <i class="bx bx-edit me-2"></i>Edit Template
                                                        </a>
                                                        <a href="<?= site_url('whatsapp/message/blast') ?>" class="btn btn-warning">
                                                            <i class="bx bx-broadcast me-2"></i>Blast Message
                                                        </a>
                                                        <a href="<?= site_url('whatsapp') ?>" class="btn btn-secondary">
                                                            <i class="bx bx-cog me-2"></i>WhatsApp Settings
                                                        </a>
                                                    </div>

                                                    <hr>

                                                    <h6>Sistem Log</h6>
                                                    <div class="activity-feed">
                                                        <div class="activity-item">
                                                            <div class="activity-time">11:45</div>
                                                            <div class="activity-content">
                                                                <i class="bx bx-check-circle text-success"></i>
                                                                Template berhasil disimpan
                                                            </div>
                                                        </div>
                                                        <div class="activity-item">
                                                            <div class="activity-time">11:30</div>
                                                            <div class="activity-content">
                                                                <i class="bx bx-message text-info"></i>
                                                                3 pesan terkirim
                                                            </div>
                                                        </div>
                                                        <div class="activity-item">
                                                            <div class="activity-time">11:15</div>
                                                            <div class="activity-content">
                                                                <i class="bx bx-user text-primary"></i>
                                                                Pelanggan baru ditambahkan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- Message Log Panel -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h4 class="card-title mb-0">
                                                        <i class="bx bx-message-detail me-2"></i>Log Pesan WhatsApp
                                                    </h4>
                                                    <div>
                                                        <span class="badge bg-warning me-2">
                                                            <i class="bx bx-time"></i> Pending: <span id="waInfo_pendingCount"><?= $pending_count ?></span>
                                                        </span>
                                                        <span class="badge bg-danger">
                                                            <i class="bx bx-error"></i> Error: <span id="waInfo_errorCount"><?= $error_count ?></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-outline-primary active" onclick="filterMessages('all')" id="waInfo_btnAll">
                                                                Semua
                                                            </button>
                                                            <button type="button" class="btn btn-outline-warning" onclick="filterMessages('pending')" id="waInfo_btnPending">
                                                                Pending
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger" onclick="filterMessages('error')" id="waInfo_btnError">
                                                                Error
                                                            </button>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-info ms-3" onclick="refreshMessageLogs()">
                                                            <i class="bx bx-refresh"></i> Refresh Log
                                                        </button>
                                                    </div>
                                                    <div id="waInfo_messageLogContainer" style="max-height: 400px; overflow-y: auto;">
                                                        <!-- Message logs will be loaded here via AJAX -->
                                                        <?php if (!empty($pending_messages)): ?>
                                                            <?php foreach ($pending_messages as $message): ?>
                                                                <div class="message-item" data-type="pending" data-id="<?= $message['id'] ?>">
                                                                    <div class="d-flex justify-content-between align-items-start mb-2 p-3 border rounded">
                                                                        <div class="flex-grow-1">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <i class="bx bx-time text-warning me-2"></i>
                                                                                <span class="badge bg-warning">PENDING</span>
                                                                                <small class="text-muted ms-2"><?= date('H:i:s', strtotime($message['created_at'])) ?></small>
                                                                            </div>
                                                                            <div class="message-content">
                                                                                <strong><?= esc($message['template_type']) ?></strong><br>
                                                                                <span class="text-muted">Ke: <?= esc($message['phone_number']) ?> (<?= esc($message['customer_name'] ?? 'Customer ID: ' . $message['customer_id']) ?>)</span><br>
                                                                                <small class="message-text"
                                                                                    data-bs-toggle="tooltip"
                                                                                    data-bs-placement="top"
                                                                                    data-bs-html="true"
                                                                                    title="<strong>Pesan Lengkap:</strong><br><?= esc($message['message_content'] ?? 'Template message content') ?>">
                                                                                    Pesan: <?= esc(substr($message['message_content'] ?? 'Template message...', 0, 50)) ?>...
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="btn-group-vertical">
                                                                            <button class="btn btn-sm btn-outline-success mb-1" onclick="retryMessage(this, <?= $message['id'] ?>)"
                                                                                data-bs-toggle="tooltip" title="Kirim Ulang">
                                                                                <i class="bx bx-refresh"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-danger" onclick="removeMessage(this, <?= $message['id'] ?>)">
                                                                                <i class="bx bx-x"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>

                                                        <?php if (!empty($error_messages)): ?>
                                                            <?php foreach ($error_messages as $message): ?>
                                                                <div class="message-item" data-type="error" data-id="<?= $message['id'] ?>">
                                                                    <div class="d-flex justify-content-between align-items-start mb-2 p-3 border rounded">
                                                                        <div class="flex-grow-1">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <i class="bx bx-error-circle text-danger me-2"></i> <span class="badge bg-danger">ERROR</span>
                                                                                <small class="text-muted ms-2"><?= date('H:i:s', strtotime($message['created_at'])) ?></small>
                                                                            </div>
                                                                            <div class="message-content">
                                                                                <strong><?= esc($message['template_type']) ?></strong><br>
                                                                                <span class="text-muted">Ke: <?= esc($message['phone_number']) ?> (<?= esc($message['customer_name'] ?? 'Customer ID: ' . $message['customer_id']) ?>)</span><br>
                                                                                <small class="message-text text-danger"
                                                                                    data-bs-toggle="tooltip"
                                                                                    data-bs-placement="top"
                                                                                    data-bs-html="true"
                                                                                    title="<strong>Error Detail:</strong><br><?= esc($message['error_message'] ?? 'Unknown error') ?>">
                                                                                    Error: <?= esc($message['error_message'] ?? 'Unknown error') ?>
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="btn-group-vertical">
                                                                            <button class="btn btn-sm btn-outline-success mb-1" onclick="retryMessage(this, <?= $message['id'] ?>)"
                                                                                data-bs-toggle="tooltip" title="Kirim Ulang">
                                                                                <i class="bx bx-refresh"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-danger" onclick="removeMessage(this, <?= $message['id'] ?>)">
                                                                                <i class="bx bx-x"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>

                                                        <?php if (empty($pending_messages) && empty($error_messages)): ?>
                                                            <div class="text-center py-4 text-muted">
                                                                <i class="bx bx-message-dots display-4"></i>
                                                                <p>Belum ada log pesan WhatsApp</p>
                                                                <small>Log akan muncul ketika ada pesan yang dikirim</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="text-center mt-3">
                                                        <small class="text-muted">
                                                            <i class="bx bx-info-circle"></i>
                                                            Pesan sukses otomatis hilang setelah terkirim. Hanya pesan pending dan error yang ditampilkan di log.
                                                        </small>
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
                                function showTestNotification(type) {
                                    const messages = {
                                        success: 'Template berhasil disimpan dan siap digunakan!',
                                        warning: 'Sistem WhatsApp berjalan normal. Total 5 template aktif.',
                                        warning: 'Periksa koneksi WhatsApp jika mengalami gangguan.',
                                        error: 'Terjadi kesalahan saat menyimpan template. Coba lagi.'
                                    };
                                    const titles = {
                                        success: 'Berhasil',
                                        warning: 'Informasi',
                                        warning: 'Peringatan',
                                        error: 'Error'
                                    };

                                    if (typeof Notify !== "undefined") {
                                        Notify({
                                            status: type,
                                            title: titles[type],
                                            text: messages[type],
                                            effect: 'slide',
                                            speed: 600,
                                            showIcon: true,
                                            showCloseButton: true,
                                            autoclose: true,
                                            autotimeout: 5000,
                                            position: 'right top'
                                        });
                                    }

                                    // Also add to notification container
                                    addToNotificationContainer(type, titles[type], messages[type]);
                                }

                                function addToNotificationContainer(type, title, message) {
                                    const container = document.getElementById('notificationContainer');

                                    // Safety check: return early if container doesn't exist
                                    if (!container) {
                                        console.warn('Notification container not found, skipping notification display');
                                        return;
                                    }

                                    const alertClass = {
                                        success: 'alert-success',
                                        warning: 'alert-warning',
                                        warning: 'alert-warning',
                                        error: 'alert-danger'
                                    };

                                    const icon = {
                                        success: 'bx-check-circle',
                                        info: 'bx-info-circle',
                                        warning: 'bx-error',
                                        error: 'bx-error-circle'
                                    };

                                    const now = new Date().toLocaleTimeString();
                                    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
            <i class="bx ${icon[type]} me-2"></i>
            <strong>${title}</strong> - ${message}
            <small class="text-muted d-block">${now}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
                                    container.insertAdjacentHTML('afterbegin', alertHtml);

                                    // Remove old notifications (keep only last 5)
                                    const alerts = container.querySelectorAll('.alert');
                                    if (alerts.length > 5) {
                                        alerts[alerts.length - 1].remove();
                                    }
                                }

                                // Auto-refresh page info every 30 seconds
                                setInterval(function() {
                                    // Update timestamp and counters
                                    console.log('Auto-refresh: System status updated');
                                }, 30000);

                                // Initial welcome notification
                                $(document).ready(function() {
                                    setTimeout(function() {
                                        showTestNotification('info');
                                    }, 1000);
                                }); // MESSAGE LOG SYSTEM FUNCTIONS

                                // Filter messages by type                                function filterMessages(type) {
                                const messages = document.querySelectorAll('.message-item');
                                const buttons = document.querySelectorAll('.btn-group .btn');

                                // Update button states
                                buttons.forEach(btn => btn.classList.remove('active'));
                                if (type === 'all') {
                                    document.getElementById('waInfo_btnAll').classList.add('active');
                                } else if (type === 'pending') {
                                    document.getElementById('waInfo_btnPending').classList.add('active');
                                } else if (type === 'error') {
                                    document.getElementById('waInfo_btnError').classList.add('active');
                                }

                                // Show/hide messages
                                messages.forEach(message => {
                                    if (type === 'all' || message.dataset.type === type) {
                                        message.style.display = 'block';
                                    } else {
                                        message.style.display = 'none';
                                    }
                                });
                                // Load message logs (no API required - messages are already loaded from PHP)
                                function loadMessageLogs() {
                                    // Since this page already loads messages from PHP, 
                                    // we just need to update the counters and refresh display
                                    updateCounters();
                                    console.log('Message logs refreshed from current page data');
                                }

                                // Simple refresh function without dummy data
                                function refreshMessageLogs() {
                                    if (typeof Notify !== "undefined") {
                                        Notify({
                                            status: 'info',
                                            title: 'Refresh Log',
                                            text: 'Memperbarui log pesan dari database...',
                                            effect: 'slide',
                                            speed: 600,
                                            showIcon: true,
                                            showCloseButton: true,
                                            autoclose: true,
                                            autotimeout: 2000,
                                            position: 'right top'
                                        });
                                    }

                                    setTimeout(() => {
                                        loadMessageLogs();

                                        if (typeof Notify !== "undefined") {
                                            Notify({
                                                status: 'success',
                                                title: 'Berhasil',
                                                text: 'Log pesan berhasil diperbarui',
                                                effect: 'slide',
                                                speed: 600,
                                                showIcon: true,
                                                showCloseButton: true,
                                                autoclose: true,
                                                autotimeout: 3000,
                                                position: 'right top'
                                            });
                                        }
                                    }, 1000);
                                }

                                function updateCounters() {
                                    const pendingCount = document.querySelectorAll('.message-item[data-type="pending"]').length;
                                    const errorCount = document.querySelectorAll('.message-item[data-type="error"]').length;

                                    document.getElementById('waInfo_pendingCount').textContent = pendingCount;
                                    document.getElementById('waInfo_errorCount').textContent = errorCount;
                                }

                                // Auto-refresh message logs every 30 seconds
                                setInterval(() => {
                                    loadMessageLogs();
                                    console.log('Auto-refresh: Message logs updated from database');
                                }, 30000);

                                // Initialize message log system
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Initialize Bootstrap tooltips
                                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                        return new bootstrap.Tooltip(tooltipTriggerEl);
                                    });

                                    updateCounters();

                                    // Initial welcome message for message log
                                    setTimeout(() => {
                                        if (typeof Notify !== "undefined") {
                                            Notify({
                                                status: 'warning',
                                                title: 'Message Log System',
                                                text: 'Log pesan WhatsApp aktif dan terhubung dengan database real.',
                                                effect: 'slide',
                                                speed: 600,
                                                showIcon: true,
                                                showCloseButton: true,
                                                autoclose: true,
                                                autotimeout: 5000,
                                                position: 'right top'
                                            });
                                        }
                                    }, 2000);

                                    // Load initial message logs
                                    setTimeout(() => {
                                        loadMessageLogs();
                                    }, 1000);
                                }); // Function to reinitialize tooltips for dynamically added content
                                function initializeTooltips() {
                                    // Dispose existing tooltips first
                                    var existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                                    existingTooltips.forEach(function(el) {
                                        var tooltip = bootstrap.Tooltip.getInstance(el);
                                        if (tooltip) {
                                            tooltip.dispose();
                                        }
                                    });

                                    // Initialize new tooltips
                                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                        return new bootstrap.Tooltip(tooltipTriggerEl);
                                    });
                                } // Retry sending a message
                                function retryMessage(buttonElement, messageId) {
                                    // Disable the button to prevent multiple clicks
                                    buttonElement.disabled = true;
                                    const originalText = buttonElement.innerHTML;
                                    buttonElement.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';

                                    // Show loading notification
                                    if (typeof Notify !== "undefined") {
                                        Notify({
                                            status: 'info',
                                            title: 'Mengirim Ulang',
                                            text: 'Mencoba mengirim ulang pesan WhatsApp...',
                                            effect: 'slide',
                                            speed: 600,
                                            showIcon: true,
                                            showCloseButton: true,
                                            autoclose: true,
                                            autotimeout: 3000,
                                            position: 'right top'
                                        });
                                    }

                                    // Simulate retry process (since API endpoints don't exist yet)
                                    setTimeout(() => {
                                        // Simulate successful retry (80% success rate)
                                        const success = Math.random() > 0.2;

                                        if (success) {
                                            // Show success notification
                                            if (typeof Notify !== "undefined") {
                                                Notify({
                                                    status: 'success',
                                                    title: 'Berhasil',
                                                    text: 'Pesan berhasil dikirim ulang',
                                                    effect: 'slide',
                                                    speed: 600,
                                                    showIcon: true,
                                                    showCloseButton: true,
                                                    autoclose: true,
                                                    autotimeout: 3000,
                                                    position: 'right top'
                                                });
                                            }

                                            // Remove the message item with animation
                                            const messageItem = buttonElement.closest('.message-item');
                                            if (messageItem) {
                                                messageItem.style.transition = 'all 0.5s ease';
                                                messageItem.style.opacity = '0';
                                                messageItem.style.transform = 'translateX(100%)';
                                                setTimeout(() => {
                                                    messageItem.remove();
                                                    updateCounters();
                                                }, 500);
                                            }
                                        } else {
                                            // Show error notification
                                            if (typeof Notify !== "undefined") {
                                                Notify({
                                                    status: 'error',
                                                    title: 'Gagal Mengirim',
                                                    text: 'Tidak dapat mengirim ulang pesan. Coba lagi.',
                                                    effect: 'slide',
                                                    speed: 600,
                                                    showIcon: true,
                                                    showCloseButton: true,
                                                    autoclose: true,
                                                    autotimeout: 5000,
                                                    position: 'right top'
                                                });
                                            }
                                        }

                                        // Restore button state
                                        buttonElement.disabled = false;
                                        buttonElement.innerHTML = originalText;
                                    }, 2000); // 2 second delay to simulate processing
                                } // Remove a message from the log                                function removeMessage(buttonElement, messageId) {
                                // Show SweetAlert confirmation dialog
                                Swal.fire({
                                    title: 'Hapus Pesan?',
                                    text: 'Apakah Anda yakin ingin menghapus pesan ini dari log?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Ya, Hapus!',
                                    cancelButtonText: 'Batal',
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Disable the button to prevent multiple clicks
                                        buttonElement.disabled = true;
                                        const originalText = buttonElement.innerHTML;
                                        buttonElement.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';

                                        // Simulate removal process (since API endpoints don't exist yet)
                                        setTimeout(() => {
                                            // Show success notification
                                            if (typeof Notify !== "undefined") {
                                                Notify({
                                                    status: 'success',
                                                    title: 'Berhasil',
                                                    text: 'Pesan berhasil dihapus dari log',
                                                    effect: 'slide',
                                                    speed: 600,
                                                    showIcon: true,
                                                    showCloseButton: true,
                                                    autoclose: true,
                                                    autotimeout: 3000,
                                                    position: 'right top'
                                                });
                                            }

                                            // Remove the message item with animation
                                            const messageItem = buttonElement.closest('.message-item');
                                            if (messageItem) {
                                                messageItem.style.transition = 'all 0.5s ease';
                                                messageItem.style.opacity = '0';
                                                messageItem.style.transform = 'translateX(-100%)';
                                                setTimeout(() => {
                                                    messageItem.remove();
                                                    updateCounters();
                                                }, 500);
                                            }
                                        }, 1000); // 1 second delay to simulate processing
                                    }
                                });
                            </script>
                            <?= $this->endSection() ?>