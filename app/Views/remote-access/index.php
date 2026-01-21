<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Remote Access - Geniacs</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Remote Access</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Alert -->
        <?php if (!$geniacs_configured): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Geniacs belum dikonfigurasi!</strong> Silakan konfigurasi Geniacs terlebih dahulu untuk menggunakan fitur remote access.
                        <button type="button" class="btn btn-sm btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#configModal">
                            <i class="bx bx-cog"></i> Konfigurasi Sekarang
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between bg-light">
                        <h5 class="card-title mb-0">Daftar Pelanggan</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-info custom-radius" data-bs-toggle="modal" data-bs-target="#configModal" style="display:inline-flex;align-items:center;justify-content:center;">
                                <i class="bx bx-cog" style="font-size:20px; padding-right:5px;"></i> Konfigurasi Geniacs
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="customersTable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Serial Number</th>
                                    <th>ONU Model</th>
                                    <th>PPPoE Username</th>
                                    <th>PPPoE MAC</th>
                                    <th>Status</th>
                                    <th>RxPower (dBm)</th>
                                    <th>SSID</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Modal -->
    <div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configModalLabel">Konfigurasi Geniacs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="configForm">
                        <div class="mb-3">
                            <label for="geniacs_url" class="form-label">Server URL <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="geniacs_url" name="geniacs_url"
                                value="<?= esc($geniacs_url) ?>"
                                placeholder="http://geniacs.example.com:7557" required>
                            <small class="text-muted">URL server GenieACS (contoh: http://192.168.1.100:7557)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary custom-radius" data-bs-dismiss="modal" style="display:inline-flex;align-items:center;justify-content:center;">Tutup</button> -->
                    <button type="button" class="btn btn-warning custom-radius" id="btnTestConnection" style="display:inline-flex;align-items:center;justify-content:center;">
                        <i class="bx bx-wifi" style=" font-size:20px; padding-right:5px;"></i> Test Koneksi
                    </button>
                    <button type="button" class="btn btn-primary custom-radius" id="btnSaveConfig" style="display:inline-flex;align-items:center;justify-content:center;">
                        <i class="bx bx-save" style=" font-size:20px; padding-right:5px;"></i> Simpan Konfigurasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="customerDetailContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Remote Access Modal -->
    <div class="modal fade" id="remoteModal" tabindex="-1" aria-labelledby="remoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="remoteModalLabel">Remote Access - <span id="customerName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="remoteAccessContent" style="min-height: 600px;">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Connecting...</span>
                        </div>
                        <p class="mt-3">Menghubungkan ke Geniacs...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WiFi Settings Modal -->
    <div class="modal fade" id="wifiModal" tabindex="-1" aria-labelledby="wifiModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wifiModalLabel">WiFi Settings - <span id="wifiDeviceSerial"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="wifiLoadingContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Memuat WiFi settings...</p>
                        </div>
                    </div>
                    <form id="wifiForm" style="display:none;">
                        <input type="hidden" id="wifi_device_id" name="device_id">
                        <div class="mb-3">
                            <label for="wifi_ssid" class="form-label">SSID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wifi_ssid" name="ssid" required>
                            <small class="text-muted">Nama jaringan WiFi yang akan ditampilkan</small>
                        </div>
                        <div class="mb-3">
                            <label for="wifi_password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="wifi_password" name="password" minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Catatan:</strong> Perubahan akan diterapkan saat device berikutnya terhubung ke GenieACS. Pastikan device dalam kondisi online.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary custom-radius" data-bs-dismiss="modal" style="display:inline-flex;align-items:center;justify-content:center;">Tutup</button>
                    <button type="button" class="btn btn-primary custom-radius" id="btnSaveWifi" style="display:none;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="bx bx-save" style=" font-size:20px; padding-right:5px;"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const geniacsConfigured = <?= $geniacs_configured ? 'true' : 'false' ?>;

            // Initialize DataTable
            const table = $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('remote-access/getData') ?>',
                    type: 'POST',
                    data: function(d) {
                        d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                    }
                },
                columns: [{
                        data: 'serial_number'
                    },
                    {
                        data: 'model'
                    },
                    {
                        data: 'pppoe_username'
                    },
                    {
                        data: 'pppoe_mac'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'signal'
                    },
                    {
                        data: 'ssid'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });

            // Remote Access Button Click
            $(document).on('click', '.remote-access-btn', function() {
                if (!geniacsConfigured) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Geniacs Belum Dikonfigurasi',
                        text: 'Silakan konfigurasi Geniacs terlebih dahulu',
                        confirmButtonText: 'Konfigurasi Sekarang'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#configModal').modal('show');
                        }
                    });
                    return;
                }

                const customerId = $(this).data('id');
                const customerName = $(this).data('name');

                $('#customerName').text(customerName);
                $('#remoteModal').modal('show');

                // Connect to Geniacs
                $.ajax({
                    url: '<?= site_url('remote-access/connectGeniacs') ?>',
                    type: 'POST',
                    data: {
                        customer_id: customerId,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Show device info with link to web interface
                            const deviceData = response.data;
                            $('#remoteAccessContent').html(
                                '<div class="card m-3">' +
                                '<div class="card-body">' +
                                '<h5 class="card-title">Informasi Device</h5>' +
                                '<table class="table table-sm">' +
                                '<tr><th width="150">Serial Number:</th><td>' + deviceData.serial_number + '</td></tr>' +
                                '<tr><th>Model:</th><td>' + deviceData.model + '</td></tr>' +
                                '<tr><th>IP Address:</th><td>' + deviceData.ip_address + '</td></tr>' +
                                '</table>' +
                                '<a href="' + deviceData.web_interface_url + '" target="_blank" class="btn btn-primary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">' +
                                '<i class="bx bx-link-external me-1" style="font-size:20px; padding-right:5px;"></i> Buka Web Interface ONT' +
                                '</a>' +
                                '<p class="text-muted mt-3 mb-0">' +
                                '<small><i class="bx bx-info-circle me-1"></i> ' +
                                'Link akan membuka halaman login ONT di tab baru. Gunakan kredensial default device atau yang sudah dikonfigurasi.' +
                                '</small></p>' +
                                '</div>' +
                                '</div>'
                            );
                        } else {
                            $('#remoteAccessContent').html(
                                '<div class="alert alert-danger m-3">' +
                                '<i class="bx bx-error-circle me-2"></i>' +
                                response.message + '</div>'
                            );
                        }
                    },
                    error: function(xhr) {
                        $('#remoteAccessContent').html(
                            '<div class="alert alert-danger m-3">' +
                            '<i class="bx bx-error-circle me-2"></i>' +
                            'Gagal menghubungkan ke Geniacs</div>'
                        );
                    }
                });
            });

            // View Details Button Click
            $(document).on('click', '.view-details-btn', function() {
                const customerId = $(this).data('id');

                $('#detailModal').modal('show');

                $.ajax({
                    url: '<?= site_url('remote-access/getCustomerDetail') ?>/' + customerId,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            const customer = response.data;
                            let html = '<div class="row">';
                            html += '<div class="col-md-6">';
                            html += '<table class="table table-sm">';
                            html += '<tr><th width="40%">Nama</th><td>' + customer.nama_pelanggan + '</td></tr>';
                            html += '<tr><th>No. Layanan</th><td>' + customer.nomor_layanan + '</td></tr>';
                            html += '<tr><th>No. Telepon</th><td>' + (customer.telepphone || '-') + '</td></tr>';
                            html += '<tr><th>Email</th><td>' + (customer.email || '-') + '</td></tr>';
                            html += '<tr><th>Alamat</th><td>' + customer.address + '</td></tr>';
                            html += '</table>';
                            html += '</div>';
                            html += '<div class="col-md-6">';
                            html += '<table class="table table-sm">';
                            html += '<tr><th width="40%">PPPoE Username</th><td>' + (customer.pppoe_username || '-') + '</td></tr>';
                            html += '<tr><th>Local IP</th><td>' + (customer.pppoe_local_ip || '-') + '</td></tr>';
                            html += '<tr><th>Remote IP</th><td>' + (customer.pppoe_remote_address || '-') + '</td></tr>';
                            html += '<tr><th>Profile</th><td>' + (customer.profile || '-') + '</td></tr>';
                            html += '<tr><th>Status</th><td>' + customer.status + '</td></tr>';
                            html += '</table>';
                            html += '</div>';
                            html += '</div>';

                            $('#customerDetailContent').html(html);
                        } else {
                            $('#customerDetailContent').html(
                                '<div class="alert alert-danger">Gagal memuat detail pelanggan</div>'
                            );
                        }
                    }
                });
            });

            // WiFi Settings Button Click
            $(document).on('click', '.wifi-settings-btn', function() {
                if (!geniacsConfigured) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Geniacs Belum Dikonfigurasi',
                        text: 'Silakan konfigurasi Geniacs terlebih dahulu',
                        confirmButtonText: 'Konfigurasi Sekarang'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#configModal').modal('show');
                        }
                    });
                    return;
                }

                const deviceId = $(this).data('id');
                const deviceSerial = $(this).data('serial');

                $('#wifiDeviceSerial').text(deviceSerial);
                $('#wifi_device_id').val(deviceId);
                $('#wifiLoadingContent').show();
                $('#wifiForm').hide();
                $('#btnSaveWifi').hide();
                $('#wifiModal').modal('show');

                // Load WiFi settings
                $.ajax({
                    url: '<?= site_url('remote-access/getWifiSettings') ?>',
                    type: 'POST',
                    data: {
                        device_id: deviceId,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Check if SSID is available
                            if (response.data.ssid && response.data.ssid !== '-') {
                                $('#wifi_ssid').val(response.data.ssid);
                                $('#wifi_password').val(''); // Don't show current password
                                $('#wifiLoadingContent').hide();
                                $('#wifiForm').show();
                                $('#btnSaveWifi').show();
                            } else {
                                $('#wifiLoadingContent').html(
                                    '<div class="alert alert-warning">' +
                                    '<i class="bx bx-info-circle me-2"></i>' +
                                    '<strong>WiFi tidak ditemukan</strong><br>' +
                                    'Device ini mungkin tidak memiliki konfigurasi WiFi atau WiFi tidak aktif.' +
                                    '</div>'
                                );
                            }
                        } else {
                            $('#wifiLoadingContent').html(
                                '<div class="alert alert-danger">' +
                                '<i class="bx bx-error-circle me-2"></i>' +
                                '<strong>Gagal memuat WiFi settings</strong><br>' +
                                response.message + '</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMsg = 'Gagal memuat WiFi settings';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            errorMsg += ': ' + error;
                        }

                        $('#wifiLoadingContent').html(
                            '<div class="alert alert-danger">' +
                            '<i class="bx bx-error-circle me-2"></i>' +
                            '<strong>Error</strong><br>' + errorMsg +
                            '<br><small class="text-muted">Pastikan GenieACS terkoneksi dan device online</small>' +
                            '</div>'
                        );
                    }
                });
            });

            // Toggle Password Visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#wifi_password');
                const icon = $(this).find('i');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('bx-show').addClass('bx-hide');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('bx-hide').addClass('bx-show');
                }
            });

            // Save WiFi Settings
            $('#btnSaveWifi').on('click', function() {
                const btn = $(this);
                const deviceId = $('#wifi_device_id').val();
                const ssid = $('#wifi_ssid').val();
                const password = $('#wifi_password').val();

                if (!ssid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Lengkap',
                        text: 'SSID tidak boleh kosong'
                    });
                    return;
                }

                if (password && password.length < 8) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Password Tidak Valid',
                        text: 'Password minimal 8 karakter'
                    });
                    return;
                }

                btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Menyimpan...');

                $.ajax({
                    url: '<?= site_url('remote-access/updateWifiSettings') ?>',
                    type: 'POST',
                    data: {
                        device_id: deviceId,
                        ssid: ssid,
                        password: password,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                $('#wifiModal').modal('hide');
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan WiFi settings'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bx bx-save"></i> Simpan Perubahan');
                    }
                });
            });

            // Test Connection
            $('#btnTestConnection').on('click', function() {
                const btn = $(this);
                const url = $('#geniacs_url').val();

                if (!url) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Lengkap',
                        text: 'Silakan isi URL Server GenieACS'
                    });
                    return;
                }

                btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Testing...');

                $.ajax({
                    url: '<?= site_url('remote-access/testConnection') ?>',
                    type: 'POST',
                    data: {
                        geniacs_url: url,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Koneksi Berhasil',
                                text: response.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Koneksi Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat testing koneksi'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bx bx-wifi"></i> Test Koneksi');
                    }
                });
            });

            // Save Configuration
            $('#btnSaveConfig').on('click', function() {
                const btn = $(this);
                const url = $('#geniacs_url').val();

                if (!url) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Lengkap',
                        text: 'Silakan isi URL Server GenieACS'
                    });
                    return;
                }

                btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Menyimpan...');

                $.ajax({
                    url: '<?= site_url('remote-access/saveConfiguration') ?>',
                    type: 'POST',
                    data: {
                        geniacs_url: url,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan konfigurasi'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bx bx-save"></i> Simpan Konfigurasi');
                    }
                });
            });

            // Reset remote modal content when closed
            $('#remoteModal').on('hidden.bs.modal', function() {
                $('#remoteAccessContent').html(
                    '<div class="text-center p-5">' +
                    '<div class="spinner-border text-primary" role="status">' +
                    '<span class="visually-hidden">Connecting...</span>' +
                    '</div>' +
                    '<p class="mt-3">Menghubungkan ke Geniacs...</p>' +
                    '</div>'
                );
            });
        });
    </script>

    <?= $this->endSection() ?>