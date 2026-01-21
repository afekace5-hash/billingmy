<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Clus &mdash; SDN Krengseng 02</title>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Router</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">List Router</a></li>
                            <li class="breadcrumb-item active">Router</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div> <!-- end page title -->

        <!-- Action Buttons Row -->
        <!-- Loading Progress Indicator -->
        <div class="row mb-3 d-none" id="loadingProgress">
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="flex-grow-1">
                            <strong>Loading MikroTik Data...</strong>
                            <div class="mt-1">
                                <span id="progressText">Preparing to load routers...</span>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="progressBar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Alert for Auto Loading -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                    <i class="mdi mdi-check-circle me-2"></i>
                    <strong>Auto-Loading Enabled:</strong> Data MikroTik (CPU, Memory, PPPoE count) akan dimuat secara otomatis bertahap untuk semua router.
                    Halaman akan dimuat cepat, kemudian data router akan diperbarui satu per satu. Anda dapat menggunakan tombol <strong>"Check Connection Status"</strong> untuk refresh status koneksi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Main Content Row: Router Cards and PPPoE Table -->
        <div class="row g-3">
            <!-- Router Cards Column -->
            <div class="col-xl-4 col-lg-8 equal-height">
                <div class="row g-3">
                    <?php if (isset($routers) && is_array($routers) && count($routers) > 0): ?>
                        <?php foreach ($routers as $router): ?>
                            <div class="col-12 mb-3">
                                <div class="card router-card-compact shadow-sm" data-router-id="<?= esc($router['id_lokasi']) ?>">
                                    <div class="ribbon-wrapper ribbon-lg">
                                        <?php if (isset($router['is_connected']) && $router['is_connected'] == 1): ?>
                                            <div class="ribbon bg-success text-white">ONLINE</div>
                                        <?php else: ?>
                                            <div class="ribbon bg-danger text-white">OFFLINE</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <img src="https://mikrotik.com/img/mtv2/newlogo.svg" alt="MikroTik" width="180" height="40" />
                                            <div class="router-status text-end">
                                                <?php if (isset($router['is_connected']) && $router['is_connected'] == 1): ?>
                                                    <span class="badge bg-soft-success text-success font-size-12 rounded-pill px-3"><i class="bx bx-check-circle me-1"></i>Connected</span>
                                                <?php else: ?>
                                                    <span class="badge bg-soft-danger text-danger font-size-12 rounded-pill px-3"><i class="bx bx-error-circle me-1"></i>Disconnected</span>
                                                <?php endif; ?>
                                                <div class="last-checked-time mt-1">
                                                    <small class="text-muted d-block">Last checked: Never</small>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="fs-17 mb-2 d-flex align-items-center">
                                            <i class="bx bx-router text-primary me-2"></i>
                                            <?= esc($router['name'] ?? '-') ?>
                                        </h5>
                                        <p class="text-muted mb-3">
                                            <i class="bx bx-globe text-info me-1"></i>
                                            <?= esc($router['ip_router'] ?? '-') ?>
                                        </p>
                                        <div class="router-info-grid p-2 mb-3 bg-light rounded">
                                            <div class="info-item d-flex align-items-center mb-2">
                                                <div class="info-icon me-2">
                                                    <i class="bx bx-desktop text-primary"></i>
                                                </div>
                                                <div class="info-content">
                                                    <small class="text-muted d-block">Router OS</small>
                                                    <span class="router-os fw-medium">-</span>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex align-items-center mb-2">
                                                <div class="info-icon me-2">
                                                    <i class="bx bx-chip text-success"></i>
                                                </div>
                                                <div class="info-content">
                                                    <small class="text-muted d-block">CPU Frequency</small>
                                                    <span class="router-cpu fw-medium">-</span>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex align-items-center mb-2">
                                                <div class="info-icon me-2">
                                                    <i class="bx bx-microchip text-info"></i>
                                                </div>
                                                <div class="info-content">
                                                    <small class="text-muted d-block">Architecture</small>
                                                    <span class="router-arch fw-medium">-</span>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex align-items-center mb-1">
                                                <div class="info-icon me-2">
                                                    <i class="bx bx-server text-warning"></i>
                                                </div>
                                                <div class="info-content">
                                                    <small class="text-muted d-block">Board Name</small>
                                                    <span class="router-board fw-medium">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="stats-section">
                                            <!-- Statistics Cards in Horizontal Layout -->
                                            <div class="row g-3">
                                                <!-- PPPoE Statistics Column -->
                                                <div class="col-6">
                                                    <!-- PPPoE Statistics Header -->
                                                    <div class="stats-header mb-2">
                                                        <h6 class="stats-title fw-semibold border-start border-primary border-3 ps-2">
                                                            <i class="bx bx-wifi text-primary me-1"></i>
                                                            PPPoE Statistics
                                                        </h6>
                                                    </div>
                                                    <!-- PPPoE Cards Row -->
                                                    <div class="row g-2">
                                                        <div class="col-12 mb-2">
                                                            <div class="stat-card-modern bg-gradient-success text-white shadow-lg border-0 rounded-3 overflow-hidden position-relative">
                                                                <div class="stat-card-body p-3">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="stat-content">
                                                                            <div class="stat-label text-white-50 mb-1">PPPoE Aktif</div>
                                                                            <div class="stat-value pppoe-active-count fw-bold fs-3 text-white">20</div>
                                                                        </div>
                                                                        <div class="stat-icon-modern">
                                                                            <div class="icon-circle rounded-circle">
                                                                                <i class="bx bx-user-check"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="stat-footer mt-2">
                                                                        <small class="text-white-50">
                                                                            <i class="bx bx-trending-up me-1"></i>
                                                                            Aktif Saat Ini
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="stat-pattern"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="stat-card-modern bg-gradient-danger text-white shadow-lg border-0 rounded-3 overflow-hidden position-relative">
                                                                <div class="stat-card-body p-3">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="stat-content">
                                                                            <div class="stat-label text-white-50 mb-1">PPPoE Tidak Aktif</div>
                                                                            <div class="stat-value pppoe-inactive-count fw-bold fs-3 text-white">0</div>
                                                                        </div>
                                                                        <div class="stat-icon-modern">
                                                                            <div class="icon-circle rounded-circle">
                                                                                <i class="bx bx-user-x"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="stat-footer mt-2">
                                                                        <small class="text-white-50">
                                                                            <i class="bx bx-trending-down me-1"></i>
                                                                            Terputus Koneksi
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="stat-pattern"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- IP Binding Statistics Column -->
                                                <div class="col-6">
                                                    <!-- Binding Statistics Header -->
                                                    <div class="stats-header mb-2">
                                                        <h6 class="stats-title fw-semibold border-start border-info border-3 ps-2">
                                                            <i class="bx bx-link text-info me-1"></i>
                                                            IP Binding Statistics
                                                        </h6>
                                                    </div>
                                                    <!-- IP Binding Cards Row -->
                                                    <div class="row g-2">
                                                        <div class="col-12 mb-2">
                                                            <div class="stat-card-modern bg-gradient-info text-white shadow-lg border-0 rounded-3 overflow-hidden position-relative">
                                                                <div class="stat-card-body p-3">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="stat-content">
                                                                            <div class="stat-label text-white-50 mb-1">Binding Aktif</div>
                                                                            <div class="stat-value binding-active-count fw-bold fs-3 text-white">-</div>
                                                                        </div>
                                                                        <div class="stat-icon-modern">
                                                                            <div class="icon-circle rounded-circle">
                                                                                <i class="bx bx-link"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="stat-footer mt-2">
                                                                        <small class="text-white-50">
                                                                            <i class="bx bx-check-circle me-1"></i>
                                                                            Terhubung
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="stat-pattern"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="stat-card-modern bg-gradient-warning text-white shadow-lg border-0 rounded-3 overflow-hidden position-relative">
                                                                <div class="stat-card-body p-3">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="stat-content">
                                                                            <div class="stat-label text-white-50 mb-1">Binding Tidak Aktif</div>
                                                                            <div class="stat-value binding-inactive-count fw-bold fs-3 text-white">-</div>
                                                                        </div>
                                                                        <div class="stat-icon-modern">
                                                                            <div class="icon-circle rounded-circle">
                                                                                <i class="bx bx-unlink"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="stat-footer mt-2">
                                                                        <small class="text-white-50">
                                                                            <i class="bx bx-error-circle me-1"></i>
                                                                            Menunggu Koneksi
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="stat-pattern"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <div class="avatar-md mx-auto mb-4">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24">
                                            <i class="bx bx-router"></i>
                                        </div>
                                    </div>
                                    <h5 class="mb-3">Tidak Ada Router</h5>
                                    <p class="text-muted mb-4">Belum ada router yang terdaftar dalam sistem.</p>
                                    <a href="<?= site_url('server-locations') ?>" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i>
                                        Tambah Router
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div> <!-- End Router Cards Column -->

        </div> <!-- End Main Content Row -->
    </div> <!-- End container-fluid -->
</div> <!-- End page-content -->

<!-- Modal Edit Data Koneksi Router -->
<div class="modal fade" id="myModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="CreateForm" name="CreateForm" class="form-horizontal">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" class="form-control" id="id" name="id">
                    <input type="hidden" class="form-control" id="method" name="method">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger mb-0" role="alert">
                                Pastikan VPN remote yang di pakai aktif dengan port Mikrotik API 8728 dan pastikan IP => Service => Api pada mikrotik <code>enable</code>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="ip_router" class="col-form-label">Vpn/tunnel/IP Public Router<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ip_router" name="ip_router"
                                    placeholder="Vpn/tunnel/IP Public Router">
                                <span id="errorRouterip" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="port_api" class="col-form-label">Port API Vpn/tunnel/IP Public<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="port_api" name="port_api"
                                    placeholder="Port API Vpn/tunnel/IP Public" value="8728">
                                <span id="error_port_api" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="username" class="col-form-label">Username Mikrotik<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Username Mikrotik">
                                <span id="error_username" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="password" class="col-form-label">Kata sandi Mikrotik<span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_router" name="password"
                                placeholder="Kata sandi Mikrotik" autocomplete="current-password">
                            <span id="error_password_router" class="invalid-feedback text-danger" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-6 align-middle">
                        <div class="mb-3 align-middle">
                            <div id="badge-connected" style="display: none;"></div>
                            <input type="hidden" id="is_connected" name="is_connected">
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="submit" id="saveBtn" class="btn btn-primary" value="create">Save changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        </form>
    </div>
</div>
</div>
</div>
<!-- container-fluid -->
</div>
<!-- End Page-content -->
<style>
    /* Nav tabs styling for better text visibility */
    .nav-pills .nav-link {
        color: #495057 !important;
        background-color: transparent;
    }

    .nav-pills .nav-link.active {
        color: #fff !important;
        background-color: #007bff !important;
    }

    .nav-pills .nav-link:hover {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff !important;
    }

    /* Ensure badge text is visible */
    .nav-pills .nav-link .badge {
        color: #fff !important;
    }

    .nav-pills .nav-link:not(.active) .bg-success {
        background-color: #28a745 !important;
    }

    .nav-pills .nav-link:not(.active) .bg-secondary {
        background-color: #6c757d !important;
    }

    /* PPPoE table text visibility */
    .tab-content {
        color: #212529 !important;
    }

    .tab-pane {
        color: #212529 !important;
    }

    .pppoe-table {
        color: #212529 !important;
    }

    .pppoe-table td,
    .pppoe-table th {
        color: #212529 !important;
    }

    /* Enhanced Router Card Styling */
    .router-card-compact {
        transition: all 0.3s ease;
        overflow: hidden;
        border: none;
        border-radius: 0.5rem;
    }

    .router-card-compact:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .stat-card {
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: scale(1.03);
    }

    .stat-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-value {
        font-size: 20px;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    /* Ribbon styling */
    .ribbon-wrapper {
        position: absolute;
        top: -3px;
        right: -3px;
        height: 88px;
        width: 88px;
        overflow: hidden;
        z-index: 1;
    }

    .ribbon {
        position: absolute;
        top: 15px;
        right: -25px;
        padding: 3px 10px;
        width: 110px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        transform: rotate(45deg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* PPPoE Table enhancements */
    .pppoe-table-card {
        border-radius: 0.5rem;
        overflow: hidden;
        border: none;
    }

    .pppoe-table thead th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .pppoe-table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.04);
    }

    /* PPPoE Table Text Visibility Fix */
    .pppoe-table td,
    .pppoe-table th {
        color: #495057 !important;
        /* Dark gray text for better visibility */
    }

    .pppoe-table tbody td {
        font-weight: 500;
    }

    .pppoe-table .text-center {
        color: #6c757d !important;
        /* Muted text for loading/empty states */
    }

    /* Tab content text fix */
    .tab-content {
        color: #212529 !important;
        /* Ensure dark text in tab content */
    }

    /* Nav tabs text visibility fix */
    .nav-pills .nav-link {
        color: #495057 !important;
        /* Dark text for inactive tabs */
    }

    .nav-pills .nav-link.active {
        color: #fff !important;
        /* White text for active tab */
    }

    .nav-pills .nav-link:hover {
        color: #495057 !important;
        /* Dark text on hover */
    }

    .nav-pills .nav-link.active:hover {
        color: #fff !important;
        /* White text for active tab on hover */
    }

    /* Button styling */
    .btn {
        border-radius: 4px;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .btn-group .btn {
        border-radius: 4px;
    }

    /* Status indicators */
    .loading {
        position: relative;
        color: transparent !important;
    }

    .loading:after {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-image: linear-gradient(90deg, #f0f0f0, #f8f8f8, #f0f0f0);
        background-size: 200% 100%;
        border-radius: 4px;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Router info grid */
    .router-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .info-item {
        padding: 5px;
        border-radius: 6px;
    }

    .info-item:hover {
        background-color: rgba(0, 123, 255, 0.04);
    }

    /* Modern Statistics Card Styling */
    .stat-card-modern {
        border-radius: 12px !important;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .stat-card-modern:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
    }

    .stat-card-body {
        position: relative;
        z-index: 2;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
    }

    .icon-circle {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.2) !important;
        border: 2px solid rgba(255, 255, 255, 0.3) !important;
    }

    .icon-circle i {
        font-size: 28px !important;
        line-height: 1 !important;
        display: inline-block !important;
        color: white !important;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    /* Force icon visibility with high contrast */
    .stat-card-modern .icon-circle i {
        color: rgba(255, 255, 255, 0.95) !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        font-weight: normal !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
    }

    /* Enhanced icon hover effects */
    .stat-card-modern:hover .icon-circle {
        background: rgba(255, 255, 255, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        transform: rotate(5deg) scale(1.05);
    }

    .stat-card-modern:hover .icon-circle i {
        color: white !important;
        text-shadow: 0 3px 6px rgba(0, 0, 0, 0.5) !important;
    }

    /* Debugging - temporarily add a background to see if icons are there */
    .icon-circle::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        z-index: -1;
    }

    /* Specific icon size classes */
    .fs-1 {
        font-size: 2.5rem !important;
    }

    .fs-20 {
        font-size: 1.25rem !important;
    }

    .stat-card-modern:hover .icon-circle {
        transform: rotate(10deg) scale(1.1);
    }

    .stat-pattern {
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background-image:
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, .1) 2px, transparent 2px),
            radial-gradient(circle at 80% 50%, rgba(255, 255, 255, .1) 2px, transparent 2px),
            radial-gradient(circle at 40% 20%, rgba(255, 255, 255, .1) 1px, transparent 1px),
            radial-gradient(circle at 60% 80%, rgba(255, 255, 255, .1) 1px, transparent 1px);
        background-size: 50px 50px, 40px 40px, 30px 30px, 35px 35px;
        opacity: 0.3;
        z-index: 1;
    }

    .stat-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 8px;
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .stats-title {
        font-size: 0.85rem;
        margin-bottom: 0;
    }
</style>
<script>
    // Base URL for AJAX requests - for hosting compatibility
    var baseUrl = '<?= base_url() ?>';

    // Global variable dan function untuk router focus
    var currentFocusedRouterId = null;

    // Toast notification function using Toastr
    function showToastMessage(type, message, title = '') {
        // Configure Toastr options
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

        // Show notification based on type
        switch (type) {
            case 'success':
                toastr.success(message, title || 'Success');
                break;
            case 'error':
                toastr.error(message, title || 'Error');
                break;
            case 'warning':
                toastr.warning(message, title || 'Warning');
                break;
            case 'info':
                toastr.info(message, title || 'Info');
                break;
            default:
                toastr.info(message, title || 'Notification');
        }
    }
    var isInitialLoad = true;

    // Function to update selected router name in the PPPoE table header
    function updateSelectedRouterName(routerId) {
        if (routerId) {
            // Find the router name from the cards
            const routerCard = $('[data-router-id="' + routerId + '"]');
            const routerName = routerCard.find('h5.fs-17').text().trim();
            const routerIP = routerCard.find('p.text-muted').text().trim();

            if (routerName) {
                $('#selectedRouterName').html('<i class="bx bx-router me-1"></i>' + routerName);
            } else {
                $('#selectedRouterName').html('<i class="bx bx-router me-1"></i>Router #' + routerId);
            }

            // Add active class to the selected router card and remove from others
            $('.router-card-compact').removeClass('border-primary');
            routerCard.addClass('border-primary');
        } else {
            $('#selectedRouterName').html('<i class="bx bx-router me-1"></i>Not Selected');
        }
    }

    // Override updateFocusedRouter function to also update the selected router name
    function updateFocusedRouter(routerId) {
        if (routerId) {
            currentFocusedRouterId = routerId;
            // Update the selected router name in the PPPoE table header
            updateSelectedRouterName(routerId);
        }
    }

    // Removed: disconnectPppoe and connectPppoe functions - PPPoE list disabled

    // Function to load MikroTik info for all routers with lazy loading and batching
    function loadAllRouterInfo() {
        const routerElements = $('.router-card-compact');
        const routerIds = [];

        // Collect all router IDs
        routerElements.each(function() {
            var routerId = $(this).data('router-id');
            if (!routerId) {
                // Try to find router ID from the card structure
                var cardContent = $(this).html();
                var match = cardContent.match(/data-id="(\d+)"/);
                if (match) {
                    routerId = match[1];
                }
            }
            if (routerId) {
                routerIds.push(routerId);
            }
        });

        // Show progress indicator automatically
        $('#loadingProgress').removeClass('d-none');
        updateProgress(0, routerIds.length, 'Auto-loading router data...');

        // Load routers with delay between each request to prevent overloading
        loadRoutersSequentially(routerIds, 0);
    } // Function to update progress indicator
    function updateProgress(current, total, message) {
        const percentage = total > 0 ? Math.round((current / total) * 100) : 0;
        $('#progressText').text(message || `Loading router ${current + 1} of ${total}...`);
        $('#progressBar').css('width', percentage + '%');
    }

    // Function to hide progress indicator
    function hideProgress() {
        $('#loadingProgress').addClass('d-none');
    } // Function to load routers sequentially with delay
    function loadRoutersSequentially(routerIds, index) {
        // Check if loading should continue
        if (!isLoadingInProgress || index >= routerIds.length) {
            hideProgress();

            if (index >= routerIds.length) {
                showToastMessage('success', 'All router data loaded automatically!');
            }

            // Reset loading state (no button states to manage for auto-loading)
            isLoadingInProgress = false;
            return;
        }

        const routerId = routerIds[index];

        // Update progress
        updateProgress(index, routerIds.length, `Auto-loading router ${index + 1} of ${routerIds.length}...`);

        // Show loading indicator for current router
        var routerCard = $('.editData[data-id="' + routerId + '"]').closest('.router-card-compact');
        routerCard.find('.router-os, .router-cpu, .router-arch, .router-board').text('Loading...');
        routerCard.find('.pppoe-active-count, .pppoe-inactive-count, .binding-active-count, .binding-inactive-count').addClass('loading').text('...');

        // Load current router
        loadMikrotikInfoForCard(routerId);

        // Continue with next router after delay (reduced delay for faster loading)
        loadingTimeoutId = setTimeout(function() {
            loadRoutersSequentially(routerIds, index + 1);
        }, 800); // 800ms delay between requests untuk loading yang lebih cepat
    }

    // Function to update router connection status UI
    function updateRouterConnectionStatus(routerId, isConnected, updateDatabase = true) {
        const routerCard = $('[data-router-id="' + routerId + '"]');

        if (routerCard.length > 0) {
            // Update ribbon
            const ribbon = routerCard.find('.ribbon');
            if (isConnected) {
                ribbon.removeClass('bg-danger').addClass('bg-success').text('ONLINE');
            } else {
                ribbon.removeClass('bg-success').addClass('bg-danger').text('OFFLINE');
            }

            // Update status badge
            const statusBadge = routerCard.find('.router-status .badge');
            if (isConnected) {
                statusBadge.removeClass('bg-soft-danger text-danger')
                    .addClass('bg-soft-success text-success')
                    .html('<i class="bx bx-check-circle me-1"></i>Connected');
            } else {
                statusBadge.removeClass('bg-soft-success text-success')
                    .addClass('bg-soft-danger text-danger')
                    .html('<i class="bx bx-error-circle me-1"></i>Disconnected');
            }

            // Add last updated timestamp
            updateLastCheckedTimestamp(routerCard);

            // Update database if requested
            if (updateDatabase) {
                updateRouterStatusInDatabase(routerId, isConnected);
            }
        }
    } // Function to update last checked timestamp
    function updateLastCheckedTimestamp(routerCard) {
        const now = new Date();
        const timeString = now.toLocaleTimeString();

        // Update timestamp element
        let timestampElement = routerCard.find('.last-checked-time');
        if (timestampElement.length > 0) {
            timestampElement.html('<small class="text-muted d-block">Last checked: ' + timeString + '</small>');
        }
    }

    // Function to update router status in database
    function updateRouterStatusInDatabase(routerId, isConnected) {

    }

    // Function to reload all router statuses - modified to not override successful connections
    function reloadAllRouterStatuses() {
        let totalRouters = 0;
        let checkedRouters = 0;

        $('[data-router-id]').each(function() {
            const routerId = $(this).data('router-id');
            if (routerId) {
                totalRouters++;

                // Check if router already shows as connected (has data loaded successfully)
                const routerCard = $(this);
                const currentStatus = routerCard.find('.router-status .badge');
                const isCurrentlyConnected = currentStatus.hasClass('bg-soft-success');

                if (isCurrentlyConnected) {
                    checkedRouters++;
                } else {
                    // Only check status if router is not already marked as connected
                    checkSingleRouterStatus(routerId, function() {
                        checkedRouters++;
                    });
                }
            }
        });
    } // Function to check single router status
    function checkSingleRouterStatus(routerId, callback) {
        const routerCard = $('[data-router-id="' + routerId + '"]');

        // Show loading state
        const statusBadge = routerCard.find('.router-status .badge');
        const originalContent = statusBadge.html();
        statusBadge.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Checking...');

        // Try to get status by testing MikroTik connection instead of dedicated status endpoint
        $.ajax({
            url: baseUrl + 'routers/' + routerId + '/mikrotik-info',
            method: 'GET',
            timeout: 5000, // 5 second timeout for status check
            success: function(response) {
                // If we can get MikroTik data, router is connected
                updateRouterConnectionStatus(routerId, true, false);
            },
            error: function(xhr, status, error) {
                // If we can't get MikroTik data, router is disconnected
                updateRouterConnectionStatus(routerId, false, false);
            },
            complete: function() {
                // Execute callback if provided
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }

    function loadMikrotikInfoForCard(routerId) {
        // Find the router card for this specific router
        var routerCard = $('[data-router-id="' + routerId + '"]');

        // Skip if already loading or loaded
        if (routerCard.hasClass('mikrotik-loading') || routerCard.hasClass('mikrotik-loaded')) {
            return;
        }

        // Mark as loading
        routerCard.addClass('mikrotik-loading');

        // Set loading states for this specific card
        routerCard.find('.router-os, .router-cpu, .router-arch, .router-board').text('Loading...');
        routerCard.find('.pppoe-active-count, .pppoe-inactive-count, .binding-active-count, .binding-inactive-count').addClass('loading').text('...');

        // Set timeout untuk request yang lebih pendek (reduced to 3 seconds for better UX)
        $.ajax({
            url: baseUrl + 'routers/' + routerId + '/mikrotik-info',
            method: 'GET',
            timeout: 3000, // 3 detik timeout untuk response yang lebih cepat
            success: function(data) {
                // Update router info for this specific card
                routerCard.find('.router-os').text(data.version || '-');
                routerCard.find('.router-cpu').text(data['cpu-frequency'] || '-');
                routerCard.find('.router-arch').text(data['architecture-name'] || '-');
                routerCard.find('.router-board').text(data['board-name'] || '-');
                routerCard.find('.pppoe-active-count').removeClass('loading').text(data.active_count || 0);
                routerCard.find('.pppoe-inactive-count').removeClass('loading').text(data.inactive_count || 0);
                routerCard.find('.binding-active-count').removeClass('loading').text(data.binding_active_count || 0);
                routerCard.find('.binding-inactive-count').removeClass('loading').text(data.binding_inactive_count || 0);

                // Mark as loaded and remove loading state
                routerCard.removeClass('mikrotik-loading').addClass('mikrotik-loaded');

                // IMPORTANT: Update connection status to Connected AFTER updating the data
                setTimeout(function() {
                    updateRouterConnectionStatus(routerId, true, true);
                }, 100);
            },
            error: function(xhr, status, error) {
                // Remove loading state immediately
                routerCard.removeClass('mikrotik-loading');

                // Handle different error types for this specific card
                let errorMessage = 'Offline';
                let detailError = error;

                if (status === 'timeout') {
                    console.warn('⚠ MikroTik timeout for router', routerId);
                    errorMessage = 'Timeout';
                    detailError = 'Router tidak merespons dalam 3 detik';
                } else if (xhr.status === 503) {
                    console.warn('⚠ MikroTik router', routerId, 'unavailable (503)');
                    errorMessage = 'Unavailable';
                    detailError = 'Router tidak dapat diakses';
                } else if (xhr.status === 500) {
                    console.warn('⚠ MikroTik router', routerId, 'internal server error (500)');
                    errorMessage = 'Server Error';
                    detailError = 'Error koneksi ke MikroTik';
                } else {
                    console.error('✗ Failed to load MikroTik info for router', routerId, ':', xhr.status, error);
                    errorMessage = 'Connection Failed';
                    detailError = 'Router tidak dapat dihubungi';
                }

                // Display clear error status instead of generic "Not available"
                routerCard.find('.router-os').text(errorMessage);
                routerCard.find('.router-cpu').text(errorMessage);
                routerCard.find('.router-arch').text(errorMessage);
                routerCard.find('.router-board').text(errorMessage);
                routerCard.find('.pppoe-active-count, .pppoe-inactive-count, .binding-active-count, .binding-inactive-count').removeClass('loading').text('-');

                // Update connection status to Disconnected since the request failed
                updateRouterConnectionStatus(routerId, false, true);

                // Show user-friendly notification for failed connections (optional, only for first router)
                if (routerId && parseInt(routerId) <= 21) { // Only show for first few routers to avoid spam
                    console.log('Router', routerId, 'connection failed:', detailError);
                }
            }
        });
    }

    // End of JavaScript functions

    // Global variable to control loading
    let isLoadingInProgress = false;
    let loadingTimeoutId = null;

    // Document ready initialization
    $(function() {
        // Initial setup: load info for the first router and set it as focused
        var firstRouterId = $('[data-router-id]').first().data('router-id');
        if (firstRouterId) {
            // Set current focused router
            updateFocusedRouter(firstRouterId);

            // Load MikroTik info for first router immediately
            loadMikrotikInfoForCard(firstRouterId);
        }

        // Auto-loading all routers with staggered timing for better performance
        setTimeout(function() {
            isLoadingInProgress = true;
            loadAllRouterInfo();
        }, 1500); // Start loading other routers after 1.5 seconds untuk response yang lebih cepat

        // Auto-check router statuses after all data is loaded
        setTimeout(function() {
            reloadAllRouterStatuses();
        }, 15000); // Check statuses after 15 seconds

        // Removed: Auto-refresh for PPPoE table - PPPoE list disabled

        // Set up auto-refresh for router statuses every 60 seconds (1 minute)
        setInterval(function() {
            reloadAllRouterStatuses();
        }, 60000); // Changed back to 60s (1 minute)

        // Set up auto-refresh for router info every 3 minutes
        setInterval(function() {
            isLoadingInProgress = true;
            loadAllRouterInfo();
        }, 180000); // 3 minutes auto-refresh

        // Removed: refreshPppoeTable button handler - PPPoE list disabled

        // Handle click on router card for automatic PPPoE loading
        $(document).on('click', '.router-card-compact', function(e) {
            // Don't trigger if clicking on buttons inside the card
            if ($(e.target).closest('button, .btn').length > 0) {
                return;
            }

            var routerId = $(this).data('router-id');
            if (routerId && routerId !== currentFocusedRouterId) {
                // Update focused router
                updateFocusedRouter(routerId);

                // Load MikroTik info for this router
                loadMikrotikInfoForCard(routerId);

                // Removed: Auto-load PPPoE table - PPPoE list disabled

                // Visual feedback - highlight selected card
                $('.router-card-compact').removeClass('border-primary shadow-lg');
                $(this).addClass('border-primary shadow-lg');
            }
        });
    }); // End of $(function() {
</script>
<?= $this->endSection() ?>