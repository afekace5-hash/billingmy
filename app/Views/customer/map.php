<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Peta Lokasi Pelanggan &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin="" />

<style>
    /* Map container styling - lebih tinggi dan modern */
    #customerMap {
        height: 650px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        background-color: #f8f9fa;
        border: 2px solid #e9ecef;
        position: relative;
        z-index: 1;
    }

    /* Animated dashed lines - smooth animation */
    @keyframes dash {
        to {
            stroke-dashoffset: -40;
        }
    }

    .animated-dash-line {
        animation: dash 2s linear infinite;
    }

    /* Neon pin glow effect */
    .neon-pin,
    .neon-odp-pin {
        transition: all 0.3s ease;
    }

    .neon-pin:hover,
    .neon-odp-pin:hover {
        transform: scale(1.3);
    }

    /* Legend styling */
    .map-legend {
        background: rgba(0, 0, 0, 0.85);
        border: 1px solid #333;
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        font-size: 13px;
        color: #fff;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-weight: 500;
        color: #fff;
    }

    .legend-color {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        margin-right: 10px;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .legend-color.green {
        background-color: #28a745;
    }

    .legend-color.orange {
        background-color: #ffc107;
    }

    .legend-color.red {
        background-color: #dc3545;
    }

    .legend-color.blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Statistics Cards - more modern */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Filter buttons - more compact and modern */
    .filter-buttons {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }

    .filter-buttons .btn {
        margin-right: 8px;
        margin-bottom: 8px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        padding: 8px 16px;
        transition: all 0.2s ease;
    }

    .filter-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced Popup styling */
    .popup-content {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        min-width: 340px;
        max-width: 400px;
        padding: 0;
        margin: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .popup-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 12px 12px 0 0;
        margin: -12px -16px 0 -16px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .popup-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: -50%;
        width: 200%;
        height: 100%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        animation: headerShine 3s ease-in-out infinite;
    }

    @keyframes headerShine {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .popup-title {
        font-weight: 600;
        font-size: 16px;
        margin: 0 0 4px 0;
    }

    .popup-header small {
        opacity: 0.9;
        font-size: 12px;
    }

    .popup-body {
        padding: 12px 0;
    }

    .popup-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .popup-table tr {
        border-bottom: 1px solid #f1f3f4;
    }

    .popup-table tr:last-child {
        border-bottom: none;
    }

    .popup-table td {
        padding: 8px 4px;
        vertical-align: top;
        line-height: 1.4;
    }

    .popup-icon {
        width: 28px;
        text-align: center;
        padding-right: 8px;
    }

    .popup-icon i {
        font-size: 16px;
        display: block;
    }

    .popup-label {
        width: 80px;
        font-weight: 600;
        color: #495057;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding-right: 12px;
    }

    .popup-value {
        color: #2c3e50;
        font-size: 13px;
        word-wrap: break-word;
        line-height: 1.3;
    }

    .popup-status {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        color: white;
        display: inline-block;
        margin-top: 1px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
        text-align: center;
        min-width: 60px;
    }

    .popup-status.status-active {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .popup-status.status-inactive {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: #212529;
    }

    .popup-status.status-overdue {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
    }

    .popup-status.status-unknown {
        background: linear-gradient(135deg, #6c757d, #5a6268);
    }

    .popup-footer {
        background-color: #f8f9fa;
        padding: 8px 16px;
        margin: 8px -16px -12px -16px;
        border-radius: 0 0 8px 8px;
        text-align: center;
        border-top: 1px solid #e9ecef;
    }

    .popup-footer small {
        font-size: 10px;
        color: #6c757d;
        font-family: monospace;
    }

    /* Enhanced WiFi icon for ODP markers */
    .custom-div-icon {
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(255, 255, 255, 0.95));
        border: 3px solid #007bff;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4), 0 0 0 4px rgba(0, 123, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .custom-div-icon:hover {
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.2), rgba(255, 255, 255, 0.9));
        transform: scale(1.15);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6), 0 0 0 6px rgba(0, 123, 255, 0.2);
    }

    /* Custom pin icon for customers */
    .custom-div-icon-pin {
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.4));
        transition: all 0.3s ease;
    }

    .custom-div-icon-pin:hover {
        transform: scale(1.2);
        filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.6));
    }

    /* Pin icon berdasarkan status */
    .pin-active {
        color: #28a745 !important;
        animation: activePulse 2s infinite;
    }

    .pin-inactive {
        color: #ffc107 !important;
        animation: inactiveBlink 3s infinite;
    }

    .pin-overdue {
        color: #dc3545 !important;
        animation: overduePulse 1.5s infinite;
    }

    .pin-default {
        color: #17a2b8 !important;
    }

    @keyframes activePulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes inactiveBlink {

        0%,
        50% {
            opacity: 1;
        }

        25%,
        75% {
            opacity: 0.5;
        }
    }

    @keyframes overduePulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.15);
        }

        100% {
            transform: scale(1);
        }
    }

    /* Enhanced connection line styles */
    .connection-line-active {
        stroke-width: 4;
        opacity: 0.8;
        stroke: #28a745;
        stroke-dasharray: 10 5;
        animation: activeLineFlow 2s linear infinite;
        filter: drop-shadow(0 0 6px rgba(40, 167, 69, 0.6));
    }

    .connection-line-inactive {
        stroke-width: 4;
        opacity: 0.7;
        stroke: #ffc107;
        stroke-dasharray: 8 8;
        animation: inactiveLineFlow 3s linear infinite;
        filter: drop-shadow(0 0 4px rgba(255, 193, 7, 0.5));
    }

    .connection-line-overdue {
        stroke-width: 4;
        opacity: 0.8;
        stroke: #dc3545;
        stroke-dasharray: 5 10;
        animation: overdueLineFlow 1.5s linear infinite;
        filter: drop-shadow(0 0 8px rgba(220, 53, 69, 0.7));
    }

    .connection-line-default {
        stroke-width: 3;
        opacity: 0.6;
        stroke: #6c757d;
        stroke-dasharray: 6 6;
    }

    @keyframes activeLineFlow {
        0% {
            stroke-dashoffset: 0;
        }

        100% {
            stroke-dashoffset: -15;
        }
    }

    @keyframes inactiveLineFlow {
        0% {
            stroke-dashoffset: 0;
        }

        100% {
            stroke-dashoffset: 16;
        }
    }

    @keyframes overdueLineFlow {
        0% {
            stroke-dashoffset: 0;
        }

        100% {
            stroke-dashoffset: -15;
        }
    }

    /* Inline progress marker styles */
    .inline-progress-marker {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        pointer-events: none;
    }

    .inline-progress {
        background: rgba(255, 255, 255, 0.95);
        border: 2px solid rgba(0, 0, 0, 0.15);
        border-radius: 6px;
        height: 10px;
        width: 90px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25), inset 0 1px 2px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
    }

    .inline-progress .progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        background-size: 16px 16px;
        background-image: linear-gradient(45deg, rgba(255, 255, 255, .2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .2) 50%, rgba(255, 255, 255, .2) 75%, transparent 75%, transparent);
        position: relative;
    }

    .inline-progress .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: progressShine 2s ease-in-out infinite;
    }

    @keyframes progressShine {
        0% {
            transform: translateX(-100%);
        }

        50% {
            transform: translateX(100%);
        }

        100% {
            transform: translateX(-100%);
        }
    }

    .inline-progress .progress-bar-animated {
        animation: inline-progress-stripes 1s linear infinite;
    }

    @keyframes inline-progress-stripes {
        0% {
            background-position: 12px 0;
        }

        100% {
            background-position: 0 0;
        }
    }

    .connection-progress .progress {
        height: 8px !important;
        margin-bottom: 6px;
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .connection-progress .progress-bar {
        transition: width 0.6s ease;
        border-radius: 4px;
    }

    .connection-progress .connection-info {
        font-size: 11px;
        line-height: 1.2;
    }

    /* Bootstrap progress bar animations */
    .progress-bar-animated {
        animation: progress-bar-stripes 1s linear infinite;
    }

    @keyframes progress-bar-stripes {
        0% {
            background-position-x: 1rem;
        }

        100% {
            background-position-x: 0;
        }
    }

    /* Bootstrap badge styles */
    .badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .badge-success {
        color: #fff;
        background-color: #28a745;
    }

    .badge-warning {
        color: #212529;
        background-color: #ffc107;
    }

    .badge-danger {
        color: #fff;
        background-color: #dc3545;
    }

    @keyframes progressBarActive {
        0% {
            stroke-dasharray: 20 10;
            stroke-dashoffset: 0;
        }

        100% {
            stroke-dasharray: 20 10;
            stroke-dashoffset: 30;
        }
    }

    @keyframes progressBarInactive {
        0% {
            stroke-dasharray: 15 15;
            stroke-dashoffset: 0;
        }

        100% {
            stroke-dasharray: 15 15;
            stroke-dashoffset: -30;
        }
    }

    @keyframes progressShadow {
        0% {
            stroke-dasharray: 20 10;
            stroke-dashoffset: 5;
        }

        100% {
            stroke-dasharray: 20 10;
            stroke-dashoffset: 35;
        }
    }

    /* Signal dot styles */
    .signal-dot {
        animation: signalPulse 2s ease-in-out infinite;
        box-shadow: 0 0 10px rgba(33, 150, 243, 0.8);
    }

    @keyframes signalPulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        25% {
            transform: scale(1.5);
            opacity: 0.8;
        }

        50% {
            transform: scale(1.2);
            opacity: 0.9;
        }

        75% {
            transform: scale(1.8);
            opacity: 0.6;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Animated dashed connection line for customer to ODP */
    .animated-dash-line {
        stroke: #007bff;
        stroke-width: 2.5;
        stroke-dasharray: 4 6;
        animation: dash-move 1.2s linear infinite;
    }

    @keyframes dash-move {
        to {
            stroke-dashoffset: -20;
        }
    }

    /* Responsive design */
    @media (max-width: 768px) {
        #customerMap {
            height: 400px;
        }

        .filter-buttons .btn {
            width: 100%;
            margin-right: 0;
            margin-bottom: 5px;
        }

        .card-body {
            padding: 1rem;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Peta Lokasi Pelanggan</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('customers') ?>">Pelanggan</a></li>
                            <li class="breadcrumb-item active">Peta Lokasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex text-muted">
                            <div class="flex-shrink-0 me-3 align-self-center">
                                <i class="mdi mdi-account-group h4 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="mb-1">Total Pelanggan</p>
                                <h5 class="mb-0"><?= esc($stats['total_customers']) ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex text-muted">
                            <div class="flex-shrink-0 me-3 align-self-center">
                                <i class="mdi mdi-check-circle h4 text-success"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="mb-1">Pelanggan Aktif</p>
                                <h5 class="mb-0 text-success"><?= esc($stats['active_customers']) ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex text-muted">
                            <div class="flex-shrink-0 me-3 align-self-center">
                                <i class="mdi mdi-pause-circle h4 text-warning"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="mb-1">Tidak Aktif</p>
                                <h5 class="mb-0 text-warning"><?= esc($stats['inactive_customers']) ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex text-muted">
                            <div class="flex-shrink-0 me-3 align-self-center">
                                <i class="mdi mdi-alert-circle h4 text-danger"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="mb-1">Terlambat</p>
                                <h5 class="mb-0 text-danger"><?= esc($stats['overdue_customers']) ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filter Buttons -->
                        <div class="filter-buttons">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="showAll">
                                <i class="mdi mdi-map-marker-multiple"></i> Tampilkan Semua
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="showActive">
                                <i class="mdi mdi-check-circle"></i> Hanya Aktif
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" id="showUnpaid">
                                <i class="mdi mdi-clock-outline"></i> Belum Bayar
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="showOverdue">
                                <i class="mdi mdi-alert-circle"></i> Overdue
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="showODP">
                                <i class="mdi mdi-wifi"></i> Hanya ODP
                            </button>
                            <button type="button" class="btn btn-outline-dark btn-sm" id="showCustomers">
                                <i class="mdi mdi-account-group"></i> Hanya Pelanggan
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" id="resetView">
                                <i class="mdi mdi-refresh"></i> Reset View
                            </button>
                            <a href="<?= site_url('customers') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar
                            </a>
                        </div>

                        <!-- Map Container -->
                        <div id="customerMap" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

<script>
    var map;
    var allMarkers = [];
    var currentFilter = 'all';

    $(document).ready(function() {
        console.log('DOM ready, starting map initialization...');

        setTimeout(function() {
            console.log('Attempting map initialization...');
            initializeCustomerMap();
        }, 1000);
    });

    function initializeCustomerMap() {
        var mapContainer = $('#customerMap');

        // Show loading message dengan pattern yang sama seperti clustering
        mapContainer.html('<div style="display:flex;align-items:center;justify-content:center;height:500px;color:#666;font-size:16px;"><div>Loading customer map tiles...</div></div>');

        if (typeof L === 'undefined') {
            mapContainer.html('<div class="alert alert-danger">Leaflet library tidak dimuat!</div>');
            return;
        }

        try {
            // Cleanup existing map if any
            if (map) {
                map.remove();
            }

            // Get center coordinates from PHP
            var centerLat = <?= $centerLat ?? -6.200000 ?>;
            var centerLng = <?= $centerLng ?? 106.816666 ?>;

            console.log('Creating map with center:', centerLat, centerLng);

            // Create map with closer zoom for better view
            map = L.map('customerMap').setView([centerLat, centerLng], 16);

            // Add light map tiles (normal street map)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            console.log('Map tiles added successfully');

            // Add legend control
            var legend = L.control({
                position: 'bottomright'
            });
            legend.onAdd = function(map) {
                var div = L.DomUtil.create('div', 'map-legend');
                div.innerHTML = '<h6 style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px;">Keterangan</h6>' +
                    '<div class="legend-item">' +
                    '<div class="legend-color green"></div><span>Pelanggan Aktif</span>' +
                    '</div>' +
                    '<div class="legend-item">' +
                    '<div class="legend-color orange"></div><span>Tidak Aktif</span>' +
                    '</div>' +
                    '<div class="legend-item">' +
                    '<div class="legend-color red"></div><span>Terlambat</span>' +
                    '</div>' +
                    '<div class="legend-item">' +
                    '<div class="legend-color blue"></div><span>ODP/Clustering</span>' +
                    '</div>' +
                    '<hr style="margin: 8px 0;">' +
                    '<small style="color: #6c757d;">Garis putus-putus = koneksi ke ODP</small>';
                return div;
            };
            legend.addTo(map);

            // Force invalidateSize with multiple attempts (clustering pattern)
            setTimeout(function() {
                if (map) {
                    map.invalidateSize(true);
                    console.log('Map size invalidated');
                }
            }, 500);

            setTimeout(function() {
                if (map) {
                    map.invalidateSize(true);
                    map._resetView(map.getCenter(), map.getZoom(), true);
                    console.log('Map view reset');
                }
            }, 1500);

            // Load customer data and markers
            loadCustomerMarkers();

            // Load ODP markers
            loadODPMarkers();

            console.log('Map initialization completed');

        } catch (error) {
            console.error('Map initialization error:', error);
            mapContainer.html('<div class="alert alert-danger">Error initializing map: ' + error.message + '</div>');
        }
    }

    function loadCustomerMarkers() {
        var customers = <?= json_encode($customers ?? []) ?>;

        console.log('=== Customer Markers Debug ===');
        console.log('Total customers received:', customers.length);
        console.log('Customers data type:', typeof customers);
        console.log('Is array?:', Array.isArray(customers));
        console.log('Full customers data:', customers);

        if (customers.length > 0) {
            console.log('First customer detail:', customers[0]);
            console.log('First customer keys:', Object.keys(customers[0]));
            console.log('First customer lat:', customers[0].lat);
            console.log('First customer lng:', customers[0].lng);
        }

        console.log('Map object exists?:', typeof map !== 'undefined' && map !== null);

        if (!customers || customers.length === 0) {
            console.error('❌ No customer data available!');

            // Tampilkan pesan informatif dengan tombol aksi
            var noDataAlert = '<div class="alert alert-info position-absolute" style="top:10px;left:50%;transform:translateX(-50%);z-index:1000;width:500px;margin-left:-250px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">' +
                '<h5><i class="bx bx-info-circle me-2"></i>Belum Ada Data Lokasi Pelanggan</h5>' +
                '<p class="mb-2">Untuk menampilkan peta monitoring seperti gambar contoh, Anda perlu menambahkan data koordinat (latitude & longitude) pada:</p>' +
                '<ul class="mb-3">' +
                '<li><strong>Data Pelanggan</strong> - untuk pin hijau/merah/oranye</li>' +
                '<li><strong>Data Clustering (ODP)</strong> - untuk pin biru dan garis koneksi</li>' +
                '</ul>' +
                '<div class="d-flex gap-2">' +
                '<a href="<?= site_url('customers') ?>" class="btn btn-primary btn-sm">' +
                '<i class="bx bx-edit me-1"></i>Tambah Koordinat Pelanggan</a>' +
                '<a href="<?= site_url('clustering') ?>" class="btn btn-info btn-sm">' +
                '<i class="bx bx-map-pin me-1"></i>Kelola Clustering/ODP</a>' +
                '</div>' +
                '<hr class="my-2">' +
                '<small class="text-muted"><strong>Cara ambil koordinat:</strong> Buka Google Maps → Klik kanan lokasi → Copy koordinat</small>' +
                '</div>';

            $('#customerMap').append(noDataAlert);
            return;
        }

        console.log('✓ Customer data available, proceeding to add markers...');

        allMarkers = [];
        var validMarkers = 0;

        customers.forEach(function(customer, index) {
            console.log('Processing customer ' + index + ':', customer.name, 'Lat:', customer.lat, 'Lng:', customer.lng);

            if (customer.lat && customer.lng && customer.lat != 0 && customer.lng != 0) {
                validMarkers++;
                // Pin hijau neon seperti screenshot - lebih kecil dan simpel
                var pinColor = '#00ff00'; // Neon green default
                var glowColor = 'rgba(0, 255, 0, 0.8)';

                if (customer.status_color === 'green') {
                    pinColor = '#00ff00';
                    glowColor = 'rgba(0, 255, 0, 0.8)';
                } else if (customer.status_color === 'orange') {
                    pinColor = '#ffaa00';
                    glowColor = 'rgba(255, 170, 0, 0.8)';
                } else if (customer.status_color === 'red') {
                    pinColor = '#ff3333';
                    glowColor = 'rgba(255, 51, 51, 0.8)';
                }

                // Pin kecil dengan glow seperti screenshot
                var pinIcon = L.divIcon({
                    html: '<div style="background: ' + pinColor + '; border-radius: 50%; width: 14px; height: 14px; box-shadow: 0 0 10px ' + glowColor + ', 0 0 20px ' + glowColor + ';"></div>',
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                    popupAnchor: [0, -7],
                    className: 'neon-pin'
                });

                var marker = L.marker([customer.lat, customer.lng], {
                    icon: pinIcon
                }).addTo(map);

                // Add label nama customer di atas pin (seperti screenshot)
                var label = L.marker([customer.lat, customer.lng], {
                    icon: L.divIcon({
                        html: '<div style="color: #fff; font-size: 11px; font-weight: bold; text-shadow: 1px 1px 2px #000, 0 0 5px #000; white-space: nowrap; transform: translateY(-25px);">' +
                            (customer.name || 'N/A') + '</div>',
                        iconSize: [0, 0],
                        className: 'customer-label'
                    })
                }).addTo(map);

                allMarkers.push(label);

                // Create popup content with comprehensive customer info
                var statusText = customer.status || 'N/A';
                var statusClass = 'status-' + (customer.status || 'unknown');
                var statusIcon = getStatusIcon(customer.status);

                var popupContent = '<div class="popup-content">' +
                    '<div class="popup-header">' +
                    '<div class="popup-title">' + (customer.name || 'N/A') + '</div>' +
                    '<small>' + (customer.service_number || 'No Service Number') + '</small>' +
                    '</div>' +
                    '<div class="popup-body">' +
                    '<table class="popup-table">' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-map-marker text-primary"></i></td>' +
                    '<td class="popup-label">Alamat</td>' +
                    '<td class="popup-value">' + (customer.address || 'Alamat tidak tersedia') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-phone text-success"></i></td>' +
                    '<td class="popup-label">Telepon</td>' +
                    '<td class="popup-value">' + (customer.phone || 'Tidak ada') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-package-variant text-info"></i></td>' +
                    '<td class="popup-label">Paket</td>' +
                    '<td class="popup-value">' + (customer.package_name || 'Tidak ada paket') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-cash text-warning"></i></td>' +
                    '<td class="popup-label">Tarif</td>' +
                    '<td class="popup-value">Rp ' + formatCurrency(customer.price || 0) + '/bulan</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon">' + statusIcon + '</td>' +
                    '<td class="popup-label">Status</td>' +
                    '<td class="popup-value"><span class="popup-status ' + statusClass + '">' + statusText.toUpperCase() + '</span></td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-server text-info"></i></td>' +
                    '<td class="popup-label">Server</td>' +
                    '<td class="popup-value">' + (customer.server_name || 'N/A') + '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>' +
                    '<div class="popup-footer">' +
                    '<small>Koordinat: ' + customer.lat.toFixed(6) + ', ' + customer.lng.toFixed(6) + '</small>' +
                    '</div>' +
                    '</div>';

                marker.bindPopup(popupContent);

                // Garis koneksi KUNING NEON seperti screenshot
                if (customer.cluster_lat && customer.cluster_lng) {
                    // Garis kuning neon untuk semua koneksi seperti screenshot
                    var lineColor = '#ffdd00'; // Yellow neon

                    var connectionLine = L.polyline([
                        [customer.lat, customer.lng],
                        [customer.cluster_lat, customer.cluster_lng]
                    ], {
                        color: lineColor,
                        weight: 2,
                        opacity: 0.9,
                        dashArray: '10, 10',
                        className: 'animated-dash-line'
                    }).addTo(map);
                    connectionLine.customerData = customer;
                    allMarkers.push(connectionLine);
                }
                marker.customerData = customer;
                allMarkers.push(marker);
            } else {
                console.warn('Invalid coordinates for customer:', customer.name, 'Lat:', customer.lat, 'Lng:', customer.lng);
            }
        });

        console.log('=== Markers Summary ===');
        console.log('Valid markers added:', validMarkers);
        console.log('Total markers in array:', allMarkers.length);

        if (validMarkers === 0) {
            $('#customerMap').append('<div class="alert alert-danger position-absolute" style="top:10px;left:50%;transform:translateX(-50%);z-index:1000;width:400px;margin-left:-200px;">Tidak ada pelanggan dengan koordinat lokasi yang valid!</div>');
        } else {
            console.log('✓ Successfully added ' + validMarkers + ' customer markers to map');
        }

        // Auto fit bounds with delay (clustering pattern)
        setTimeout(function() {
            if (allMarkers.length > 0) {
                var group = L.featureGroup(allMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
                console.log('✓ Map bounds fitted successfully to show all markers');
            }
        }, 2000);

        // Setup filter buttons
        setupFilterButtons();
    }

    function loadODPMarkers() {
        var odps = <?= json_encode($odps ?? []) ?>;
        console.log('Loading', odps.length, 'ODPs');

        if (!odps || odps.length === 0) {
            console.log('No ODP data available');
            return;
        }

        odps.forEach(function(odp, index) {
            if (odp.lat && odp.lng) {
                // ODP marker ORANYE seperti screenshot - lebih besar dari customer pin
                var odpIcon = L.divIcon({
                    html: '<div style="background: #ff8800; border-radius: 50%; width: 18px; height: 18px; box-shadow: 0 0 15px rgba(255, 136, 0, 0.9), 0 0 30px rgba(255, 136, 0, 0.6);"></div>',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    popupAnchor: [0, -9],
                    className: 'neon-odp-pin'
                });

                var marker = L.marker([odp.lat, odp.lng], {
                    icon: odpIcon
                }).addTo(map);

                // Create popup content for ODP
                var popupContent = '<div class="popup-content">' +
                    '<div class="popup-header">' +
                    '<div class="popup-title">' + (odp.name || 'N/A') + '</div>' +
                    '<small>ODP - ' + (odp.type_option || 'No Type') + '</small>' +
                    '</div>' +
                    '<div class="popup-body">' +
                    '<table class="popup-table">' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-map-marker text-primary"></i></td>' +
                    '<td class="popup-label">Alamat</td>' +
                    '<td class="popup-value">' + (odp.address || 'Alamat tidak tersedia') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-server text-info"></i></td>' +
                    '<td class="popup-label">Server</td>' +
                    '<td class="popup-value">' + (odp.server || 'Tidak ada') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-ethernet text-success"></i></td>' +
                    '<td class="popup-label">Total Port</td>' +
                    '<td class="popup-value">' + (odp.total_ports || '0') + ' port</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-account-multiple text-warning"></i></td>' +
                    '<td class="popup-label">Terpakai</td>' +
                    '<td class="popup-value">' + (odp.connected_customers || '0') + ' pelanggan</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td class="popup-icon"><i class="mdi mdi-ethernet-cable text-secondary"></i></td>' +
                    '<td class="popup-label">Tersedia</td>' +
                    '<td class="popup-value">' + (odp.available_ports || '0') + ' port</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>' +
                    '<div class="popup-footer">' +
                    '<small>Koordinat: ' + odp.lat.toFixed(6) + ', ' + odp.lng.toFixed(6) + '</small>' +
                    '</div>' +
                    '</div>';

                marker.bindPopup(popupContent);

                // Store ODP data in marker for filtering
                marker.odpData = odp;
                marker.markerType = 'odp';
                allMarkers.push(marker);
            }
        });

        console.log('Added', odps.length, 'ODP markers to map');
    }

    function getStatusColor(status) {
        switch (status) {
            case 'active':
                return 'green';
            case 'inactive':
                return 'orange';
            case 'overdue':
                return 'red';
            default:
                return 'blue';
        }
    }

    function getStatusIcon(status) {
        switch (status) {
            case 'active':
                return '<i class="mdi mdi-check-circle text-success"></i>';
            case 'inactive':
                return '<i class="mdi mdi-pause-circle text-warning"></i>';
            case 'overdue':
                return '<i class="mdi mdi-alert-circle text-danger"></i>';
            default:
                return '<i class="mdi mdi-help-circle text-muted"></i>';
        }
    }

    function formatCurrency(amount) {
        if (!amount || amount == 0) return '0';
        return new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatDate(dateString) {
        if (!dateString) return 'Tidak diketahui';
        try {
            var date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            return 'Format tanggal tidak valid';
        }
    }

    function animateSignalFlow(marker, path, duration) {
        var start = Date.now();
        var startPoint = path[0];
        var endPoint = path[1];

        function animate() {
            var elapsed = Date.now() - start;
            var progress = elapsed / duration;

            if (progress >= 1) {
                progress = 1;
                // Reset animation with delay
                setTimeout(function() {
                    animateSignalFlow(marker, path, duration);
                }, Math.random() * 1000 + 500); // Random delay between 0.5-1.5s
            }

            // Smooth easing function for more realistic signal flow
            var easeProgress = progress < 0.5 ?
                2 * progress * progress :
                1 - Math.pow(-2 * progress + 2, 3) / 2;

            // Linear interpolation between start and end points
            var lat = startPoint[0] + (endPoint[0] - startPoint[0]) * easeProgress;
            var lng = startPoint[1] + (endPoint[1] - startPoint[1]) * easeProgress;

            marker.setLatLng([lat, lng]);

            // Change opacity based on progress for fading effect
            var opacity = Math.sin(progress * Math.PI);
            marker.setStyle({
                fillOpacity: opacity
            });

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }

        animate();
    }

    function setupFilterButtons() {
        $('#showAll').click(function() {
            filterMarkers('all');
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#showActive').click(function() {
            filterMarkers('active');
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#showUnpaid').click(function() {
            filterMarkers('unpaid');
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#showOverdue').click(function() {
            filterMarkers('overdue');
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#showODP').click(function() {
            filterMarkers('odp');
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#showCustomers').click(function() {
            filterMarkers('customers');
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#resetView').click(function() {
            if (map && allMarkers.length > 0) {
                var group = L.featureGroup(allMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        });

        // Set default active button
        $('#showAll').addClass('active');
    }

    function filterMarkers(filter) {
        currentFilter = filter;

        allMarkers.forEach(function(marker) {
            var show = true;

            // Check marker type (customer or ODP)
            if (marker.markerType === 'odp') {
                // This is an ODP marker
                var odp = marker.odpData;

                switch (filter) {
                    case 'odp':
                        show = true;
                        break;
                    case 'customers':
                        show = false;
                        break;
                    case 'all':
                        show = true;
                        break;
                    default:
                        show = false; // Hide ODPs for customer-specific filters
                        break;
                }
            } else {
                // This is a customer marker
                var customer = marker.customerData;

                switch (filter) {
                    case 'active':
                        show = customer.status === 'active';
                        break;
                    case 'unpaid':
                        show = customer.status === 'inactive';
                        break;
                    case 'overdue':
                        show = customer.status === 'overdue';
                        break;
                    case 'odp':
                        show = false; // Hide customers when showing only ODPs
                        break;
                    case 'customers':
                        show = true; // Show all customers
                        break;
                    case 'all':
                    default:
                        show = true;
                        break;
                }
            }

            if (show) {
                marker.addTo(map);
            } else {
                map.removeLayer(marker);
            }
        });
    }

    // Animated dashed line for connection (JS based)
    function animateDashedLines() {
        var offset = 0;
        setInterval(function() {
            var lines = document.querySelectorAll('.animated-dash-line');
            offset = (offset + 2) % 20;
            lines.forEach(function(line) {
                line.setAttribute('stroke-dashoffset', offset);
            });
        }, 60);
    }

    $(document).ready(function() {
        // ...existing code...
        setTimeout(function() {
            animateDashedLines();
        }, 2000);
    });
</script>
<?= $this->endSection() ?>