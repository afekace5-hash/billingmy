<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- CSRF Meta Tag -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">



<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Halaman Isolir</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('routers/list') ?>">Router</a></li>
                            <li class="breadcrumb-item active">Halaman Isolir</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title mb-0">
                                    <i class="mdi mdi-cog-outline text-primary me-2"></i>
                                    Halaman Isolir
                                </h4>
                                <p class="card-title-desc mb-0">Setup layanan isolir otomatis untuk pelanggan yang telat bayar</p>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary btn-sm" id="addConfigBtn">
                                    <i class="mdi mdi-plus me-1"></i>
                                    Tambah Layanan
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">✨ Informasi Layanan Isolir:</h6>
                            <ol class="mb-0">
                                <li>Script ini berfungsi untuk menampilkan halaman isolir secara otomatis di perangkat pelanggan (misalnya saat internet dimatikan karena belum membayar tagihan).</li>
                                <li>Silakan copy script terminal di bawah dan sesuaikan dengan versi RouterOS (ROS) MikroTik yang Anda gunakan, agar tidak terjadi error saat dijalankan.</li>
                                <li>Setiap script hanya berlaku untuk satu perangkat MikroTik saja (1 ID perangkat = 1 script), dan tidak boleh digunakan ulang di router lain.</li>
                                <li>Biaya layanan hanya Rp 10.000 per bulan per MikroTik, sudah termasuk pemantauan otomatis dan pembaruan script jika diperlukan.</li>
                            </ol>
                        </div>

                        <!-- Service Table -->
                        <div class="table-responsive">
                            <?php if (empty($configs)): ?>
                                <div class="text-center py-5">
                                    <i class="mdi mdi-information-outline text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 text-muted">Belum ada layanan isolir</h5>
                                    <p class="text-muted">Klik "Tambah Layanan" untuk membuat konfigurasi isolir pertama Anda</p>
                                </div>
                            <?php else: ?>
                                <table class="table table-nowrap table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="8%">No</th>
                                            <th width="25%">Keterangan</th>
                                            <th width="20%">Lisensi</th>
                                            <th width="20%">Vpn Client</th>
                                            <th width="27%" class="text-center">Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($configs as $index => $config): ?>
                                            <?php
                                            $router = null;
                                            foreach ($routers as $r) {
                                                if ($r['id_lokasi'] == $config['router_id']) {
                                                    $router = $r;
                                                    break;
                                                }
                                            }
                                            $serviceNumber = $index + 1;
                                            $lastRunDate = $config['last_run'] ? date('d-m-Y H:i', strtotime($config['last_run'])) : '24-10-2025 10:10';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= $serviceNumber ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">HALAMAN ISOLIR</div>
                                                    <small class="text-muted"><?= esc($router['name'] ?? 'Unknown Router') ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold"><?= $lastRunDate ?></span>
                                                        <?php if ($config['is_enabled']): ?>
                                                            <span class="badge bg-warning text-dark mt-1">PERPANJANG LISENSI</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger mt-1">NONAKTIF</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn btn-dark btn-sm" onclick="showScriptTerminal(<?= $config['id'] ?>)" title="Script Terminal">
                                                        <i class="mdi mdi-console me-1"></i>
                                                        SCRIPT TERMINAL
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button class="btn btn-warning btn-sm" onclick="editConfig(<?= $config['id'] ?>)" title="Edit Halaman Isolir">
                                                            <i class="mdi mdi-pencil me-1"></i>
                                                            EDIT HALAMAN ISOLIR
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Run Auto Isolir Button (Hidden but functional) -->
        <div class="text-center mt-4" style="display: none;">
            <button class="btn btn-danger btn-lg" id="runAutoIsolirBtn">
                <i class="mdi mdi-play me-2"></i>
                Jalankan Auto Isolir
            </button>
        </div>
    </div>
</div>

<!-- Add/Edit Config Modal -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalLabel">
                    <i class="mdi mdi-cog-outline me-2"></i>
                    Tambah Layanan Isolir
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="configForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="configId" name="id">

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="mdi mdi-information me-2"></i>
                            Setup Layanan Isolir Otomatis
                        </h6>
                        <p class="mb-0">Layanan ini akan membuat halaman isolir otomatis untuk pelanggan yang telat bayar. Biaya layanan hanya <strong>Rp 10.000/bulan</strong> per MikroTik.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="routerSelect" class="form-label">Pilih Router MikroTik <span class="text-danger">*</span></label>
                                <select class="form-select" id="routerSelect" name="router_id" required>
                                    <option value="">-- Pilih Router --</option>
                                    <?php foreach ($routers as $router): ?>
                                        <option value="<?= $router['id_lokasi'] ?>"><?= esc($router['name']) ?> (<?= esc($router['ip_router']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="isolirIp" class="form-label">IP Address Isolir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="isolirIp" name="isolir_ip" placeholder="172.35.32.1" required>
                                <div class="form-text">IP yang akan digunakan untuk redirect pelanggan yang diisolir</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="isolirPageUrl" class="form-label">URL Halaman Isolir <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="isolirPageUrl" name="isolir_page_url" placeholder="https://isolir.kimonet.my.id/" value="https://isolir.kimonet.my.id/">
                        <div class="form-text">Halaman yang akan ditampilkan kepada pelanggan yang diisolir (bisa disesuaikan)</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ketentuan Isolir</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-success">Otomatis</span>
                                    <small class="text-muted d-block mt-2">Pelanggan akan diisolir otomatis ketika melebihi tanggal jatuh tempo</small>
                                </div>
                                <input type="hidden" id="gracePeriod" name="grace_period_days" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="isEnabledSelect" class="form-label">Status Layanan</label>
                                <select class="form-select" id="isEnabledSelect" name="is_enabled">
                                    <option value="1">Aktif (Direkomendasikan)</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            <i class="mdi mdi-alert-circle me-1"></i>
                            <strong>Catatan:</strong> Setelah menyimpan, Anda akan mendapatkan script terminal yang harus dijalankan di MikroTik router Anda.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>
                        Simpan Layanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    // Toastr configuration
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
    } else {
        console.log('jQuery is loaded, version:', jQuery.fn.jquery);
    }

    // Wait for page to load completely
    $(document).ready(function() {
        console.log('Document ready - Auto Isolir Config page loaded');

        // Add Config Button
        $('#addConfigBtn').on('click', function() {
            console.log('Add Config button clicked');
            showConfigModal();
        });



        // Run Auto Isolir Button
        $('#runAutoIsolirBtn').on('click', function() {
            console.log('Run Auto Isolir button clicked');

            if (confirm('Apakah Anda yakin ingin menjalankan auto isolir sekarang?')) {
                runAutoIsolir();
            }
        });
    });

    // Show Script Terminal Modal
    function showScriptTerminal(configId) {
        console.log('showScriptTerminal called with ID:', configId);

        // Get config data first
        $.ajax({
            url: '<?= site_url('routers/editAutoIsolirConfig') ?>/' + configId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const config = response.data;
                    showScriptModal(config);
                } else {
                    toastr.error('Gagal memuat data konfigurasi', 'Error');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Terjadi kesalahan: ' + error, 'Error');
            }
        });
    }

    // Show Script Modal with Terminal Commands
    function showScriptModal(config) {
        // Find router data
        let routerName = 'Unknown Router';
        let routerIP = 'Unknown IP';

        <?php foreach ($routers as $router): ?>
            if (<?= $router['id_lokasi'] ?> == config.router_id) {
                routerName = '<?= esc($router['name']) ?>';
                routerIP = '<?= esc($router['ip_router']) ?>';
            }
        <?php endforeach; ?>

        // Store config data for updating script
        window.currentScriptConfig = {
            config: config,
            routerName: routerName,
            routerIP: routerIP
        };

        const scriptContent = generateIsolirScript(config, routerName, routerIP, 'L2TP', 'v6');

        const modalHtml = `
            <div class="modal fade" id="scriptModal" tabindex="-1" aria-labelledby="scriptModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title" id="scriptModalLabel">
                                <i class="mdi mdi-console me-2"></i>
                                Script Terminal - ${routerName}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="bg-light p-3 border-bottom">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">SERVICE VPN</label>
                                        <select class="form-select" id="serviceVpnSelect" onchange="updateScript()">
                                            <option value="L2TP">L2TP</option>
                                            <option value="OVPN">OVPN</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Sesuaikan Versi RouterOS Milikmu</label>
                                        <select class="form-select" id="routerOsVersionSelect" onchange="updateScript()">
                                            <option value="v6">RouterOS v6</option>
                                            <option value="v7">RouterOS v7</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Router:</small>
                                        <div class="fw-bold">${routerName}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">IP Router:</small>
                                        <div class="fw-bold">${routerIP}</div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">IP Isolir:</small>
                                        <div class="fw-bold">${config.isolir_ip}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Halaman Isolir:</small>
                                        <div class="fw-bold">${config.isolir_page_url || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">
                                        <i class="mdi mdi-alert-circle me-2"></i>
                                        Petunjuk Penggunaan Script
                                    </h6>
                                    <ol class="mb-0">
                                        <li>Copy script di bawah ini ke terminal MikroTik Anda</li>
                                        <li>Jalankan script satu per satu secara berurutan</li>
                                        <li>Pastikan koneksi ke MikroTik stabil saat menjalankan script</li>
                                        <li>Jangan ubah parameter yang sudah dikonfigurasi</li>
                                    </ol>
                                </div>
                                
                                <div class="terminal-container">
                                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 0.9rem;"><code>${scriptContent}</code></pre>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <small>
                                        <strong>* COPY script di atas dan PASTE ke terminal winbox</strong><br>
                                        <strong>* Pastikan tidak ada error setelah di paste di terminal winbox</strong>
                                    </small>
                                </div>
                                
                                <div class="d-grid">
                                    <button class="btn btn-primary btn-lg" onclick="copyScriptToClipboard()" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); border: none;">
                                        COPY SCRIPT TERMINAL
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#scriptModal').remove();

        // Add modal to body
        $('body').append(modalHtml);

        // Show modal
        $('#scriptModal').modal('show');
    }

    // Generate MikroTik Isolir Script
    function generateIsolirScript(config, routerName, routerIP, serviceType = 'L2TP', routerOSVersion = 'v6') {
        const isolirIP = config.isolir_ip;
        const isolirURL = config.isolir_page_url || 'https://isolir.kimonet.my.id/';

        // Generate IP pool range
        const ipParts = isolirIP.split('.');
        const baseIP = `${ipParts[0]}.${ipParts[1]}.${ipParts[2]}`;
        const poolStart = `${baseIP}.2`;
        const poolEnd = `${baseIP}.254`;

        let script = `# Script Auto Isolir - ${routerName}
# Generated: ${new Date().toLocaleString()}
# Router IP: ${routerIP}
# Isolir IP: ${isolirIP}
# Service Type: ${serviceType}
# RouterOS Version: ${routerOSVersion}

`;

        // Complete unified script without section headers
        script += `/interface l2tp-client
add name="HALAMAN ISOLIR" connect-to="id1.vpn.eracloud.id" \\
    user="isoliroqbp2@085183112127" password="tfbksiei8kljqh7k4a4s3txijt654o8" disabled=no

/ip firewall nat
add chain=srcnat out-interface="HALAMAN ISOLIR" action="masquerade"

/ip pool
add name=poolsexpiredsisbro ranges=${poolStart}-${poolEnd}

/ppp profile
add name="expiredsisbro" local-address=${isolirIP} remote-address=poolsexpiredsisbro

/ip route rule
add src-address=${isolirIP}/24 table=ISOLIR action=lookup

/ip route
add routing-mark=ISOLIR gateway="HALAMAN ISOLIR"

/ip firewall nat
add action=redirect chain=dstnat comment="Auto Isolir - HTTP Redirect" \\
    dst-port=80 protocol=tcp src-address=${isolirIP}/24 \\
    to-ports=80

/ip firewall nat
add action=redirect chain=dstnat comment="Auto Isolir - HTTPS Redirect" \\
    dst-port=443 protocol=tcp src-address=${isolirIP}/24 \\
    to-ports=443

/ip hotspot walled-garden
add dst-host="*.isolir.my.id" comment="Allow isolir page"
add dst-host="*.kimonet.my.id" comment="Allow kimonet domain"
add dst-host="8.8.8.8" comment="Allow DNS"
add dst-host="8.8.4.4" comment="Allow DNS"

/system scheduler
add interval=1h name="check-expired-customers" \\
    start-date=jan/01/2025 start-time=00:00:00 \\
    on-event=":log info \\"Checking expired customers - Auto Isolir\\""

:log info "Auto Isolir Setup Completed - KIMONET"
:log info "Isolir IP Range: ${isolirIP}/24"
:log info "Pool Range: ${poolStart}-${poolEnd}"`;

        return script;
    }

    // Update script when dropdown values change
    function updateScript() {
        const serviceType = document.getElementById('serviceVpnSelect')?.value || 'L2TP';
        const routerOSVersion = document.getElementById('routerOsVersionSelect')?.value || 'v6';

        // Get current config data
        const configData = window.currentScriptConfig;
        if (configData) {
            const scriptContent = generateIsolirScript(
                configData.config,
                configData.routerName,
                configData.routerIP,
                serviceType,
                routerOSVersion
            );

            // Update the script content
            document.querySelector('#scriptModal pre code').textContent = scriptContent;
        }
    }

    // Copy Script to Clipboard
    function copyScriptToClipboard() {
        const scriptText = $('#scriptModal pre code').text();

        if (navigator.clipboard) {
            navigator.clipboard.writeText(scriptText).then(function() {
                toastr.success('Script berhasil di-copy ke clipboard', 'Berhasil');
            }, function(err) {
                toastr.error('Gagal copy script: ' + err, 'Error');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = scriptText;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                toastr.success('Script berhasil di-copy ke clipboard', 'Berhasil');
            } catch (err) {
                toastr.error('Gagal copy script', 'Error');
            }
            document.body.removeChild(textArea);
        }
    }

    // Show Config Modal
    function showConfigModal(configId = null) {
        console.log('showConfigModal called with ID:', configId);

        const modalTitle = configId ? 'Edit Layanan Isolir' : 'Tambah Layanan Isolir';
        const saveButtonText = configId ? 'Update Layanan' : 'Simpan Layanan';

        const modalContent = `
            <h4 class="modal-title" id="configModalLabel">${modalTitle}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        `;
        const formContent = `
            <div class="modal-body">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="mdi mdi-information me-2"></i>
                        Setup Layanan Isolir
                    </h6>
                    <p class="mb-0">Layanan ini akan membuat halaman isolir otomatis untuk pelanggan yang telat bayar. Biaya layanan hanya Rp 10.000/bulan per MikroTik.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="router_id" class="form-label">Pilih Router MikroTik <span class="text-danger">*</span></label>
                            <select class="form-select" id="router_id" name="router_id" required>
                                <option value="">-- Pilih Router --</option>
                                <?php foreach ($routers as $router): ?>
                                    <option value="<?= $router['id_lokasi'] ?>"><?= $router['name'] ?> (<?= $router['ip_router'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="isolir_ip" class="form-label">IP Address Isolir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="isolir_ip" name="isolir_ip" placeholder="172.35.32.1" required>
                            <div class="form-text">IP yang akan digunakan untuk redirect pelanggan yang diisolir</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="isolir_page_url" class="form-label">URL Halaman Isolir <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="isolir_page_url" name="isolir_page_url" value="https://isolir.kimonet.my.id/" required>
                    <div class="form-text">Halaman yang akan ditampilkan kepada pelanggan yang diisolir (bisa disesuaikan)</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ketentuan Isolir</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-success">Otomatis</span>
                                <small class="text-muted d-block">Pelanggan akan diisolir otomatis ketika melebihi tanggal jatuh tempo</small>
                            </div>
                            <input type="hidden" id="grace_period_days" name="grace_period_days" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_enabled" class="form-label">Status Layanan</label>
                            <select class="form-select" id="is_enabled" name="is_enabled">
                                <option value="1">Aktif (Direkomendasikan)</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <small>
                        <i class="mdi mdi-alert-circle me-1"></i>
                        <strong>Catatan:</strong> Setelah menyimpan, Anda akan mendapatkan script terminal yang harus dijalankan di MikroTik router Anda.
                    </small>
                </div>
                
                <input type="hidden" id="config_id" name="id" value="${configId || ''}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-content-save me-1"></i>
                    ${saveButtonText}
                </button>
            </div>
        `;

        $('#configModal .modal-header').html(modalContent);
        $('#configForm').html(formContent);

        // Load data if editing
        if (configId) {
            loadConfigData(configId);
        }

        $('#configModal').modal('show');
    }

    // Load Config Data for Editing
    function loadConfigData(configId) {
        console.log('Loading config data for ID:', configId);
        toastr.info('Memuat data konfigurasi...', 'Loading');
        $.ajax({
            url: '<?= site_url('routers/editAutoIsolirConfig') ?>/' + configId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Config data loaded:', response);
                if (response.success && response.data) {
                    const config = response.data;
                    console.log('Loading config data:', config);
                    $('#config_id').val(config.id); // Set the config ID for update
                    console.log('Config ID set to:', config.id);
                    $('#router_id').val(config.router_id);
                    $('#isolir_ip').val(config.isolir_ip);
                    $('#isolir_page_url').val(config.isolir_page_url);
                    // Grace period is now fixed at 0 (no grace period)
                    $('#is_enabled').val(config.is_enabled);
                    toastr.clear();
                } else {
                    toastr.error(response.message || 'Gagal memuat data konfigurasi', 'Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Load config error:', error);
                toastr.error('Terjadi kesalahan saat memuat data: ' + error, 'Error');
            }
        });
    }

    // Save Config
    $('#configForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Config form submitted');
        const formData = new FormData(this);
        const configId = $('#config_id').val();

        // Debug logging
        console.log('Config ID detected:', configId);
        console.log('Form data before submit:', Object.fromEntries(formData));
        console.log('is_enabled value:', formData.get('is_enabled'));

        const url = configId ?
            '<?= site_url('routers/updateAutoIsolirConfig') ?>' :
            '<?= site_url('routers/addAutoIsolirConfig') ?>';

        console.log('URL selected:', url);

        toastr.info('Menyimpan konfigurasi...', 'Proses'); // Add CSRF token to form data
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            formData.append('<?= csrf_token() ?>', csrfToken);
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Save config response:', response);
                toastr.clear();

                if (response.success) {
                    toastr.success(response.message || 'Konfigurasi berhasil disimpan', 'Berhasil');
                    $('#configModal').modal('hide');

                    // Reload halaman untuk melihat konfigurasi terbaru
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Gagal menyimpan konfigurasi', 'Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Save config error:', error);
                toastr.clear();
                toastr.error('Terjadi kesalahan saat menyimpan: ' + error, 'Error');
            }
        });
    });

    // Edit Config Function
    function editConfig(configId) {
        console.log('Edit config called for ID:', configId);
        showConfigModal(configId);
    }

    // Delete Config Function
    function deleteConfig(configId) {
        console.log('Delete config called for ID:', configId);

        if (confirm('Apakah Anda yakin ingin menghapus konfigurasi ini?')) {
            toastr.info('Menghapus konfigurasi...', 'Proses');
            $.ajax({
                url: '<?= site_url('routers/auto-isolir-config/delete') ?>/' + configId,
                type: 'POST',
                data: {
                    '<?= csrf_token() ?>': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Delete config response:', response);
                    toastr.clear();

                    if (response.success) {
                        toastr.success(response.message || 'Konfigurasi berhasil dihapus', 'Berhasil');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Gagal menghapus konfigurasi', 'Error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete config error:', error);
                    toastr.clear();
                    toastr.error('Terjadi kesalahan saat menghapus: ' + error, 'Error');
                }
            });
        }
    }

    // Run Auto Isolir
    function runAutoIsolir() {
        console.log('Running auto isolir');
        toastr.info('Menjalankan auto isolir...', 'Proses Berjalan');
        $.ajax({
            url: '<?= site_url('routers/auto-isolir/run') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                '<?= csrf_token() ?>': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Auto isolir response:', response);
                toastr.clear();

                if (response.success) {
                    toastr.success(response.message || 'Auto isolir berhasil dijalankan', 'Berhasil');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message || 'Gagal menjalankan auto isolir', 'Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Auto isolir error:', error);
                toastr.clear();
                toastr.error('Terjadi kesalahan saat menjalankan auto isolir: ' + error, 'Error');
            }
        });
    }

    // Manage Customers Function
    function manageCustomers(routerId, action) {
        console.log('Manage customers called for router:', routerId, 'action:', action);

        let actionText = '';
        let confirmText = '';
        let iconClass = '';

        switch (action) {
            case 'suspend':
                actionText = 'Suspend';
                confirmText = 'menangguhkan semua pelanggan';
                iconClass = 'mdi-pause-circle text-warning';
                break;
            case 'enable':
                actionText = 'Enable';
                confirmText = 'mengaktifkan semua pelanggan';
                iconClass = 'mdi-check-circle text-success';
                break;
            case 'disable':
                actionText = 'Disable';
                confirmText = 'menonaktifkan semua pelanggan';
                iconClass = 'mdi-close-circle text-danger';
                break;
            case 'kick':
                actionText = 'Kick';
                confirmText = 'memutus koneksi semua pelanggan';
                iconClass = 'mdi-logout text-warning';
                break;
            case 'reset':
                actionText = 'Reset';
                confirmText = 'mereset koneksi semua pelanggan';
                iconClass = 'mdi-refresh text-info';
                break;
            case 'change_profile':
                actionText = 'Ubah Profile';
                confirmText = 'mengubah profile pelanggan';
                iconClass = 'mdi-swap-horizontal text-primary';
                showChangeProfileModal(routerId);
                return;
            default:
                toastr.error('Aksi tidak dikenali', 'Error');
                return;
        }

        if (confirm(`Apakah Anda yakin ingin ${confirmText} di router ini?`)) {
            toastr.info(`Sedang ${actionText.toLowerCase()} pelanggan...`, 'Proses');

            $.ajax({
                url: '<?= site_url('routers/manage-customers') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    'router_id': routerId,
                    'action': action,
                    '<?= csrf_token() ?>': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Manage customers response:', response);
                    toastr.clear();

                    if (response.success) {
                        toastr.success(response.message || `${actionText} pelanggan berhasil`, 'Berhasil');
                        if (response.summary) {
                            toastr.info(`Total: ${response.summary.total}, Berhasil: ${response.summary.success}, Gagal: ${response.summary.failed}`, 'Ringkasan');
                        }
                    } else {
                        toastr.error(response.message || `Gagal ${actionText.toLowerCase()} pelanggan`, 'Error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Manage customers error:', error);
                    toastr.clear();
                    toastr.error(`Terjadi kesalahan saat ${actionText.toLowerCase()} pelanggan: ` + error, 'Error');
                }
            });
        }
    }

    // Show Change Profile Modal
    function showChangeProfileModal(routerId) {
        console.log('Show change profile modal for router:', routerId);

        // First, get available profiles from router
        toastr.info('Memuat profile yang tersedia...', 'Loading');

        $.ajax({
            url: '<?= site_url('routers/get-profiles') ?>/' + routerId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Profiles response:', response);
                toastr.clear();

                if (response.success && response.data) {
                    showProfileSelectionModal(routerId, response.data);
                } else {
                    toastr.error(response.message || 'Gagal memuat profile', 'Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Get profiles error:', error);
                toastr.clear();
                toastr.error('Terjadi kesalahan saat memuat profile: ' + error, 'Error');
            }
        });
    }

    // Show Profile Selection Modal
    function showProfileSelectionModal(routerId, profiles) {
        let profileOptions = '';
        profiles.forEach(function(profile) {
            profileOptions += `<option value="${profile.name}">${profile.name}</option>`;
        });

        const modalHtml = `
            <div class="modal fade" id="changeProfileModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ubah Profile Pelanggan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="changeProfileForm">
                                <input type="hidden" name="router_id" value="${routerId}">
                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                
                                <div class="mb-3">
                                    <label for="targetProfile" class="form-label">Profile Baru <span class="text-danger">*</span></label>
                                    <select class="form-select" id="targetProfile" name="target_profile" required>
                                        <option value="">Pilih Profile</option>
                                        ${profileOptions}
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customerFilter" class="form-label">Filter Pelanggan</label>
                                    <select class="form-select" id="customerFilter" name="customer_filter">
                                        <option value="all">Semua Pelanggan</option>
                                        <option value="overdue">Pelanggan Telat Bayar</option>
                                        <option value="active">Pelanggan Aktif</option>
                                        <option value="specific_profile">Profile Tertentu</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3" id="specificProfileDiv" style="display: none;">
                                    <label for="currentProfile" class="form-label">Profile Saat Ini</label>
                                    <select class="form-select" id="currentProfile" name="current_profile">
                                        <option value="">Pilih Profile</option>
                                        ${profileOptions}
                                    </select>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert-circle me-2"></i>
                                    <strong>Perhatian:</strong> Perubahan profile akan mempengaruhi kecepatan dan quota internet pelanggan.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="executeChangeProfile()">
                                <i class="mdi mdi-swap-horizontal me-1"></i>Ubah Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#changeProfileModal').remove();

        // Add modal to body
        $('body').append(modalHtml);

        // Show modal
        $('#changeProfileModal').modal('show');

        // Handle customer filter change
        $('#customerFilter').on('change', function() {
            if ($(this).val() === 'specific_profile') {
                $('#specificProfileDiv').show();
            } else {
                $('#specificProfileDiv').hide();
            }
        });
    }

    // Execute Change Profile
    function executeChangeProfile() {
        const formData = $('#changeProfileForm').serialize();
        const targetProfile = $('#targetProfile').val();

        if (!targetProfile) {
            toastr.error('Profile baru harus dipilih', 'Error');
            return;
        }

        const customerFilter = $('#customerFilter').val();
        let confirmText = `mengubah profile semua pelanggan ke "${targetProfile}"`;

        if (customerFilter === 'overdue') {
            confirmText = `mengubah profile pelanggan telat bayar ke "${targetProfile}"`;
        } else if (customerFilter === 'active') {
            confirmText = `mengubah profile pelanggan aktif ke "${targetProfile}"`;
        } else if (customerFilter === 'specific_profile') {
            const currentProfile = $('#currentProfile').val();
            if (!currentProfile) {
                toastr.error('Profile saat ini harus dipilih', 'Error');
                return;
            }
            confirmText = `mengubah profile dari "${currentProfile}" ke "${targetProfile}"`;
        }

        if (confirm(`Apakah Anda yakin ingin ${confirmText}?`)) {
            toastr.info('Sedang mengubah profile pelanggan...', 'Proses');

            $.ajax({
                url: '<?= site_url('routers/change-customer-profiles') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Change profile response:', response);
                    toastr.clear();

                    if (response.success) {
                        toastr.success(response.message || 'Profile pelanggan berhasil diubah', 'Berhasil');
                        if (response.summary) {
                            toastr.info(`Total: ${response.summary.total}, Berhasil: ${response.summary.success}, Gagal: ${response.summary.failed}`, 'Ringkasan');
                        }
                        $('#changeProfileModal').modal('hide');
                    } else {
                        toastr.error(response.message || 'Gagal mengubah profile pelanggan', 'Error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Change profile error:', error);
                    toastr.clear();
                    toastr.error('Terjadi kesalahan saat mengubah profile: ' + error, 'Error');
                }
            });
        }
    }
</script>
</script>
<?= $this->endSection() ?>