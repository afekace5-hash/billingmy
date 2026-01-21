<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Dashboard &mdash; BILLING INTERNET</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Debug Dashboard Values -->

<div class="page-content">
    <div class="container-fluid">
        <!-- Statistics Cards Row 1 with Enhanced Design -->
        <div class="row g-3 mb-3">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">BILLING INTERNET</h4>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary-subtle">
                                    <i class="ri-group-line text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Pelanggan</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <span class="counter-value" data-target="<?= $totalCustomers ?>"><?= $totalCustomers ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="ri-arrow-up-line align-middle"></i> Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success-subtle">
                                    <i class="ri-check-double-line text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pembayaran Bulan Ini</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <span class="counter-value" data-target="<?= $paidInvoices ?>"><?= $paidInvoices ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success-subtle text-success">
                                <i class="ri-money-dollar-circle-line align-middle"></i> Lunas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning-subtle">
                                    <i class="ri-time-line text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Belum Bayar</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <span class="counter-value" data-target="<?= $unpaidInvoices ?>"><?= $unpaidInvoices ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="ri-timer-line align-middle"></i> Pending
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-danger border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-danger-subtle">
                                    <i class="ri-user-forbid-line text-danger"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Suspended</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <span class="counter-value" data-target="<?= $suspendedCustomers ?>"><?= $suspendedCustomers ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-danger-subtle text-danger">
                                <i class="ri-close-circle-line align-middle"></i> Non-Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- Statistics Cards Row 2 with Enhanced Design -->
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success-subtle">
                                    <i class="ri-money-dollar-circle-line text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Pendapatan</p>
                                <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="counter-value" data-target="<?= $totalRevenue ?? 0 ?>">Rp <?= number_format($totalRevenue ?? 0, 0, ',', '.') ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success-subtle text-success">
                                <i class="ri-funds-line align-middle"></i> Pendapatan
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-info border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info-subtle">
                                    <i class="ri-wallet-3-line text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Saldo Bersih</p>
                                <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="counter-value" data-target="<?= $netBalance ?? 0 ?>">Rp <?= number_format($netBalance ?? 0, 0, ',', '.') ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-info-subtle text-info">
                                <i class="ri-safe-2-line align-middle"></i> Saldo
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary-subtle">
                                    <i class="ri-coins-line text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Bayar Hari Ini</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <span class="counter-value" data-target="<?= $todayPayments ?>"><?= $todayPayments ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="ri-calendar-check-line align-middle"></i> Hari Ini
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card modern-stats-card border-0 shadow-sm h-100 border-start border-danger border-4">
                    <div class="card-body p-3 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-danger-subtle">
                                    <i class="ri-user-unfollow-line text-danger"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted text-uppercase fw-medium mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Isolir Hari Ini</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <span class="counter-value" data-target="<?= $overdueInvoices ?>"><?= $overdueInvoices ?></span>
                                </h4>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-danger-subtle text-danger">
                                <i class="ri-shield-cross-line align-middle"></i> Terisolir
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- Financial Charts Section -->
        <!-- Traffic & System Info Section -->
        <div class="row">
            <div class="col-xl-8 h-100">
                <div class="card h-100 rounded-4">
                    <div class="card-header border-0 align-items-center d-flex bg-gradient-primary text-white rounded-top-4">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Traffic Monitoring</h4>
                        <div>
                            <select id="serverSelect" class="form-select form-select-sm">
                                <option value="server1">SERVER 1</option>
                                <option value="server2">SERVER 2</option>
                            </select>
                        </div>
                        <div class="ms-2">
                            <select id="interfaceSelect" class="form-select form-select-sm">
                                <option value="isp_indibiz">ISP_INDIBIZ</option>
                                <option value="isp_backup">ISP_BACKUP</option>
                            </select>
                        </div>
                        <div class="ms-2">
                            <button id="refreshTraffic" class="btn btn-sm btn-primary">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body rounded-bottom-4" style="overflow:hidden;">
                        <!-- Current Stats -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <span class="avatar-title bg-primary rounded-circle">
                                            <i class="ri-upload-2-line text-white"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0">TX</p>
                                        <h6 class="mb-0" id="currentTxRate">0 Mbps</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <span class="avatar-title bg-danger rounded-circle">
                                            <i class="ri-download-2-line text-white"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0">RX</p>
                                        <h6 class="mb-0" id="currentRxRate">0 Mbps</h6>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Traffic Chart -->
                        <div class="position-relative rounded-4 border" style="height: 325px; padding: 10px; overflow: hidden; background: #f8fafc;">
                            <div id="trafficChart" style="width: 100%; height: 100%;"></div>
                        </div>

                        <!-- Legend -->
                        <div class="d-flex justify-content-center mt-3">
                            <div class="me-3">
                                <span class="badge bg-primary me-1">●</span>
                                <small>TX</small>
                            </div>
                            <div>
                                <span class="badge bg-danger me-1">●</span>
                                <small>RX</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 h-100">
                <div class="card system-info-card border-0 shadow-sm h-100">
                    <div class="card-header bg-gradient-info text-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <div class="avatar-title bg-white bg-opacity-25 rounded-circle">
                                        <i class="ri-server-line text-white"></i>
                                    </div>
                                </div>
                                <h5 class="card-title mb-0 text-white">System Information</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-light border-0" onclick="refreshSystemInfo()" id="refreshSystemBtn" title="Refresh system information">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator me-2" id="connectionStatus">
                                    <!-- Loader removed as requested -->
                                </div>
                                <small class="text-white-50" id="lastUpdate">Last updated: -</small>
                            </div>
                            <!-- Info text removed as requested -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Router Info Section -->
                        <div class="p-3 border-bottom">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="system-info-item">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ri-cpu-line text-primary me-2"></i>
                                            <span class="fw-semibold text-dark">Board</span>
                                        </div>
                                        <div class="system-value" id="boardName">-</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="system-info-item">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ri-code-s-slash-line text-success me-2"></i>
                                            <span class="fw-semibold text-dark">Version</span>
                                        </div>
                                        <div class="system-value" id="system-version">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="p-3 border-bottom">
                            <div class="system-info-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-dashboard-line text-warning me-2"></i>
                                        <span class="fw-semibold text-dark">CPU Usage</span>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning" id="cpuBadge">
                                        <span id="cpu-usage">--%</span>
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 6px;">
                                    <div class="progress-bar bg-gradient-warning" role="progressbar"
                                        style="width: 0%;"
                                        id="cpuProgressBar"
                                        aria-valuenow="0"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="system-info-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-hard-drive-line text-info me-2"></i>
                                        <span class="fw-semibold text-dark">Memory Usage</span>
                                    </div>
                                    <span class="badge bg-info-subtle text-info" id="memoryBadge">
                                        <span id="memory-usage">--%</span>
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 6px;">
                                    <div class="progress-bar bg-gradient-info" role="progressbar"
                                        style="width: 0%;"
                                        id="memoryProgressBar">
                                    </div>
                                </div>
                                <small class="text-muted mt-1" id="memoryDetails">
                                    N/A
                                </small>
                            </div>

                            <div class="system-info-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-save-line text-danger me-2"></i>
                                        <span class="fw-semibold text-dark">Disk Usage</span>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger" id="diskBadge">
                                        <span id="disk-usage">--%</span>
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 6px;">
                                    <div class="progress-bar bg-gradient-danger" role="progressbar"
                                        style="width: 0%;"
                                        id="diskProgressBar">
                                    </div>
                                </div>
                                <small class="text-muted mt-1" id="diskDetails">
                                    N/A
                                </small>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="system-info-item">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ri-time-line text-secondary me-2"></i>
                                            <span class="fw-semibold text-dark">Uptime</span>
                                        </div>
                                        <div class="system-value text-success" id="system-uptime">-</div>
                                    </div>
                                </div>
                                <!-- Temperature and Voltage removed as requested -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Analytics Section -->
        <div class="row" style="margin-top: 0;">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header bg-gradient-success text-white">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <i class="ri-line-chart-line me-2"></i>Pendapatan vs Pengeluaran
                            </h4>
                            <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-light border-0" onclick="refreshRevenueChart()" id="refreshRevenueBtn" title="Refresh chart">
                                    <i class="ri-refresh-line"></i>
                                </button>
                                <span class="badge bg-light text-muted" id="revenuePeriodBadge">Periode: Bulan Ini</span>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div id="revenueExpenseChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-1">Metode Pembayaran</h5>
                        <div class="text-muted mb-2" id="paymentMethodPeriod">Periode<br>--</div>
                        <div class="fw-bold mb-2" style="font-size:1.2rem">
                            <span id="paymentMethodTotal">0</span> Transaksi
                        </div>
                        <div id="paymentMethodChart" style="height:220px;"></div>
                        <div class="d-flex justify-content-center mt-3 gap-3">
                            <span><span class="badge bg-danger" style="width:16px;height:16px;display:inline-block;border-radius:50%;"></span> TUNAI</span>
                            <span><span class="badge bg-warning" style="width:16px;height:16px;display:inline-block;border-radius:50%;"></span> VIRTUAL AKUN</span>
                            <span><span class="badge bg-primary" style="width:16px;height:16px;display:inline-block;border-radius:50%;"></span> QRIS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lazy load ApexCharts only when needed -->
<script>
    // Load ApexCharts asynchronously
    function loadApexCharts() {
        return new Promise((resolve, reject) => {
            if (window.ApexCharts) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
            script.async = true;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    // Metode Pembayaran Chart
    async function initPaymentMethodChart() {
        await loadApexCharts();

        // Gunakan data dari PHP controller
        const paymentMethodData = <?= json_encode($paymentMethodData ?? ['labels' => [], 'data' => [], 'total' => 0]) ?>;

        // Data untuk chart - ambil dari PHP
        let chartLabels = paymentMethodData.labels || [];
        let chartData = paymentMethodData.data || [];
        let totalTransaksi = paymentMethodData.total || 0;

        // Jika tidak ada data, tampilkan empty state
        if (totalTransaksi === 0 || chartData.length === 0) {
            chartLabels = ['Tidak ada data'];
            chartData = [1]; // Minimal 1 untuk render chart
        }

        // Dynamic colors based on payment method
        const colorMap = {
            'MANUAL': '#ff6384',
            'TUNAI': '#ff6384',
            'TRANSFER': '#36a2eb',
            'BNI_VA': '#ffcd56',
            'VIRTUAL_ACCOUNT': '#ffcd56',
            'VIRTUAL AKUN': '#ffcd56',
            'QRIS': '#4bc0c0',
            'FLIP': '#9966ff',
            'MIDTRANS': '#ff9f40',
            'XENDIT': '#c9cbcf',
            'SHOPEE_PAY': '#ff6384',
            'OVO': '#4bc0c0',
            'GOPAY': '#36a2eb',
            'DANA': '#ffcd56'
        };

        const defaultColors = ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff9f40', '#c9cbcf'];
        const chartColors = chartLabels.map((label, index) => {
            return colorMap[label.toUpperCase()] || defaultColors[index % defaultColors.length];
        });

        // Periode bulan berjalan
        const currentMonth = new Date().toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long'
        });

        document.getElementById('paymentMethodPeriod').innerHTML = `Periode<br>${currentMonth}`;
        document.getElementById('paymentMethodTotal').textContent = totalTransaksi;

        // Chart options
        const options = {
            chart: {
                type: 'donut',
                height: 220
            },
            series: chartData,
            labels: chartLabels,
            colors: chartColors,
            legend: {
                show: false
            },
            dataLabels: {
                formatter: function(val) {
                    return totalTransaksi === 0 ? '0%' : val.toFixed(1) + '%';
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: totalTransaksi === 0 ? 'Tidak ada' : 'Total',
                                formatter: function(w) {
                                    return totalTransaksi === 0 ? 'transaksi' : totalTransaksi;
                                }
                            }
                        }
                    }
                }
            },
            stroke: {
                width: 0
            },
            noData: {
                text: 'Tidak ada data transaksi',
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: undefined,
                    fontSize: '14px',
                    fontFamily: undefined
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#paymentMethodChart"), options);
        chart.render().then(() => {
            // Chart rendered successfully
        }).catch((error) => {
            console.error('Error rendering payment method chart:', error);
        });
    }
    document.addEventListener('DOMContentLoaded', initPaymentMethodChart);
</script>

<script>
    // ================= TRAFFIC MONITORING =================
    let trafficChart = null;
    let trafficChartOptions = null;
    let trafficInterval = null;

    async function initTrafficMonitor() {
        await loadApexCharts();
        // Setup chart options
        trafficChartOptions = {
            chart: {
                id: 'trafficChart',
                type: 'line',
                height: 305,
                animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                },
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                parentHeightOffset: 0
            },
            series: [{
                    name: 'TX',
                    data: []
                },
                {
                    name: 'RX',
                    data: []
                }
            ],
            xaxis: {
                type: 'datetime',
                labels: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    formatter: val => {
                        if (val === 0) return '0 Mbps';
                        return parseFloat(val).toFixed(1) + ' Mbps';
                    }
                },
                min: 0,
                tickAmount: 6,
                forceNiceScale: true,
                decimalsInFloat: 1
            },
            colors: ['#4f6bed', '#dc3545'],
            stroke: {
                width: 3,
                curve: 'smooth'
            },
            legend: {
                show: false
            },
            grid: {
                show: true,
                borderColor: '#e8eaed',
                strokeDashArray: 2,
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 0
                },
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                x: {
                    format: 'HH:mm:ss'
                }
            }
        };
        if (!trafficChart) {
            trafficChart = new ApexCharts(document.querySelector('#trafficChart'), trafficChartOptions);
            trafficChart.render();
        }
        startTrafficPolling();
        // Event listeners for select changes
        document.getElementById('serverSelect').addEventListener('change', restartTrafficPolling);
        document.getElementById('interfaceSelect').addEventListener('change', restartTrafficPolling);
        document.getElementById('refreshTraffic').addEventListener('click', restartTrafficPolling);
    }

    function getSelectedTrafficParams() {
        const server = document.getElementById('serverSelect').value;
        const iface = document.getElementById('interfaceSelect').value;
        return {
            server,
            iface
        };
    }

    function startTrafficPolling() {
        if (trafficInterval) clearInterval(trafficInterval);
        // Reset chart data
        if (trafficChart) {
            trafficChart.updateSeries([{
                    name: 'TX',
                    data: []
                },
                {
                    name: 'RX',
                    data: []
                }
            ]);
        }
        fetchAndUpdateTraffic();
        trafficInterval = setInterval(fetchAndUpdateTraffic, 5000); // 5 detik - reduced frequency
    }

    function restartTrafficPolling() {
        startTrafficPolling();
    }

    async function fetchAndUpdateTraffic() {
        const {
            server,
            iface
        } = getSelectedTrafficParams();

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 8000); // 8 second timeout

            const res = await fetch(`/api/traffic/data/${encodeURIComponent(server)}/${encodeURIComponent(iface)}`, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            clearTimeout(timeoutId);

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            const result = await res.json();

            if (result && result.status === 'success' && result.data) {
                const now = new Date();
                const tx = parseFloat(result.data.tx_mbps) || 0;
                const rx = parseFloat(result.data.rx_mbps) || 0;

                // Update current TX/RX displays
                document.getElementById('currentTxRate').textContent = tx.toFixed(1) + ' Mbps';
                document.getElementById('currentRxRate').textContent = rx.toFixed(1) + ' Mbps';

                // Update chart if it exists
                if (trafficChart && trafficChart.w && trafficChart.w.globals) {
                    const chartData = trafficChart.w.globals.series.map((series, idx) => {
                        let data = series.data.slice(-29); // keep last 29 points
                        let value = idx === 0 ? tx : rx;
                        data.push({
                            x: now.getTime(),
                            y: value
                        });
                        return {
                            name: idx === 0 ? 'TX' : 'RX',
                            data
                        };
                    });
                    trafficChart.updateSeries(chartData, false);
                }
                console.log(`Traffic updated: TX ${tx.toFixed(1)} Mbps, RX ${rx.toFixed(1)} Mbps`);
            } else {
                throw new Error('Invalid response data');
            }
        } catch (e) {
            // Silent fail - just show 0 values without console errors
            document.getElementById('currentTxRate').textContent = '0.0 Mbps';
            document.getElementById('currentRxRate').textContent = '0.0 Mbps';

            if (e.name === 'AbortError') {
                console.log('Traffic request timeout - using fallback values');
            } else {
                console.log('Traffic update failed - using fallback values');
            }
        }
    }

    // Inisialisasi traffic monitor setelah chart lain
    const oldDOMContentLoaded = document.onreadystatechange;
    document.addEventListener('DOMContentLoaded', function() {
        initTrafficMonitor();
    });
</script>

<style>
    /* Modern Stats Card Styling */
    .modern-stats-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        background: #ffffff;
        overflow: hidden;
        position: relative;
    }

    /* Background gradients for each card type */
    .border-primary.modern-stats-card {
        background: linear-gradient(135deg, #ffffff 0%, #f0f2ff 100%);
    }

    .border-success.modern-stats-card {
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    }

    .border-warning.modern-stats-card {
        background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
    }

    .border-danger.modern-stats-card {
        background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
    }

    .border-info.modern-stats-card {
        background: linear-gradient(135deg, #ffffff 0%, #f0fbff 100%);
    }

    /* Colored corners for each card type */
    .modern-stats-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        opacity: 0.1;
        border-radius: 0 12px 0 100%;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .modern-stats-card:hover::after {
        opacity: 0.15;
    }

    /* Primary color gradient corner */
    .border-primary.modern-stats-card::after {
        background: linear-gradient(135deg, transparent 0%, #667eea 100%);
    }

    /* Success color gradient corner */
    .border-success.modern-stats-card::after {
        background: linear-gradient(135deg, transparent 0%, #28c76f 100%);
    }

    /* Warning color gradient corner */
    .border-warning.modern-stats-card::after {
        background: linear-gradient(135deg, transparent 0%, #ff9f43 100%);
    }

    /* Danger color gradient corner */
    .border-danger.modern-stats-card::after {
        background: linear-gradient(135deg, transparent 0%, #ea5455 100%);
    }

    /* Info color gradient corner */
    .border-info.modern-stats-card::after {
        background: linear-gradient(135deg, transparent 0%, #00cfe8 100%);
    }

    .modern-stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    }

    .modern-stats-card .card-body {
        position: relative;
        z-index: 1;
    }

    /* Stats Icon Container */
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .modern-stats-card:hover .stats-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stats-icon i {
        font-size: 1.5rem;
    }

    /* Enhanced Counter */
    .counter-value {
        display: inline-block;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    /* Badge Styling */
    .badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        letter-spacing: 0.3px;
    }

    /* Color Variations */
    .bg-primary-subtle {
        background-color: rgba(102, 126, 234, 0.1) !important;
    }

    .bg-success-subtle {
        background-color: rgba(40, 199, 111, 0.1) !important;
    }

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-danger-subtle {
        background-color: rgba(244, 67, 54, 0.1) !important;
    }

    .bg-info-subtle {
        background-color: rgba(51, 181, 229, 0.1) !important;
    }

    .text-primary {
        color: #667eea !important;
    }

    .text-success {
        color: #28c76f !important;
    }

    .text-warning {
        color: #ff9f43 !important;
    }

    .text-danger {
        color: #ea5455 !important;
    }

    .text-info {
        color: #00cfe8 !important;
    }

    /* Gradient Backgrounds for Charts */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #28c76f 0%, #1e8449 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #ff9f43 0%, #ff6348 100%);
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #ea5455 0%, #c0392b 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #00cfe8 0%, #0097a7 100%);
    }

    /* Animated Numbers */
    @keyframes countUp {
        0% {
            opacity: 0;
            transform: translateY(20px) scale(0.8);
        }

        50% {
            opacity: 0.7;
            transform: translateY(-5px) scale(1.1);
        }

        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .counter-value {
        animation: countUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        font-weight: 800;
        letter-spacing: 1px;
    }

    /* Pulse Animation for Important Metrics */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(102, 126, 234, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
        }
    }

    /* Essential counter styling */
    .counter-value {
        font-weight: 800;
        letter-spacing: 1px;
    }

    /* Essential card hover effects */
    .hover-card:hover .avatar-md .avatar-title i {
        transform: scale(1.01);
    }

    /* Essential system status indicators */
    .system-status-good {
        color: #28a745 !important;
    }

    .system-status-warning {
        color: #ffc107 !important;
    }

    .system-status-danger {
        color: #dc3545 !important;
    }

    /* Real-time indicator */
    .real-time-indicator {
        animation: pulse-realtime 2s infinite;
        font-size: 0.8rem;
        border-radius: 20px;
        padding: 0.35rem 0.75rem;
        font-weight: 500;
    }

    @keyframes pulse-realtime {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    /* Progress bars */
    .progress {
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 6px;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
        transition: width 0.6s ease;
        border-radius: 6px;
    }

    /* Loading spinner */
    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* Reduce vertical gap between stacked rows (Traffic Monitoring & Pendapatan vs Pengeluaran) */
    .row+.row {
        margin-top: 12px !important;
    }

    /* Better spacing for statistics cards */
    .row.g-4.mb-4+.row.g-4.mb-4 {
        margin-top: -10px !important;
    }
</style>

<script>
    // Essential dashboard initialization
    const pageStartTime = performance.now();

    // Cache untuk menghindari API calls berulang
    const dashboardCache = {
        systemInfo: null,
        lastUpdate: null
    };

    const CACHE_DURATION = 2 * 60 * 1000; // 2 minutes

    function isCacheValid() {
        return dashboardCache.lastUpdate &&
            (Date.now() - dashboardCache.lastUpdate) < CACHE_DURATION;
    }

    // Counter protection
    function quickCounterFix() {
        document.querySelectorAll('.counter-value').forEach(function(element) {
            const target = element.getAttribute('data-target');
            const current = element.textContent || element.innerHTML;

            if (current.includes('%') && target && !target.includes('%')) {
                element.textContent = target;
                element.innerHTML = target;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize system info for real MikroTik data
        initSystemInfo();

        // Initialize essential components
        initializeChartsAsync();

        // Counter protection
        quickCounterFix();

        // Add keyboard shortcut for manual refresh (Ctrl+R or F5)
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey && event.key === 'r') || event.key === 'F5') {
                if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault();
                    console.log('Manual refresh triggered via keyboard');
                    refreshCustomerStatistics();
                }
            }
        });
    });

    // Async chart initialization
    async function initializeChartsAsync() {
        try {
            // Load ApexCharts library first
            await loadApexCharts();

            // DISABLED: Use server-side statistics values instead of API fetch
            // Customer statistics are already rendered by PHP in the view
            // await initCustomerStatistics();

            // Initialize revenue chart
            await initRevenueExpenseChart();

            // DISABLED: Auto-refresh statistics (using server-side values)
            // setInterval(() => {
            //     console.log('Auto-refreshing customer statistics...');
            //     initCustomerStatistics(0);
            // }, 600000); // 10 minutes

        } catch (error) {
            console.warn('Chart initialization failed:', error);
        }
    }

    // Simple System Information Functions
    function refreshSystemInfo() {
        console.log('=== refreshSystemInfo called ===');

        // Clear cache to ensure fresh data
        dashboardCache.systemInfo = null;
        dashboardCache.lastUpdate = null;

        updateConnectionStatus('loading');

        // Get the first connected router ID from lokasi_server
        // Use the same approach as router list for consistency
        const apiUrl = '<?= base_url('api/dashboard/system-info') ?>';
        console.log('Fetching system info from:', apiUrl);

        fetch(apiUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-cache'
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('=== Full API Response ===', data);

                if (data.status === 'success' && data.data) {
                    // Use the system data directly from controller
                    let systemData = data.data;
                    console.log('System data extracted:', systemData);
                    console.log('Is fallback?', systemData.is_fallback);

                    let isConnected = systemData && !systemData.is_fallback;

                    if (systemData) {
                        updateSystemInfoDisplay(systemData);
                        updateConnectionStatus(isConnected ? 'online' : 'offline');
                        updateLastUpdateTime();
                    } else {
                        console.warn('No system data in response');
                        updateSystemInfoWithNotAvailable();
                        updateConnectionStatus('offline');
                    }
                } else {
                    console.error('Invalid response format:', data);
                    throw new Error(data.message || 'Error fetching system information');
                }
            })
            .catch(error => {
                console.error('=== Failed to get system info ===', error);
                updateConnectionStatus('offline');
                updateSystemInfoWithNotAvailable();
            });
    }

    function updateSystemInfoWithNotAvailable() {
        // Update all system info elements with 'Not available'
        const elements = [
            'cpu-usage', 'memory-usage', 'disk-usage',
            'system-uptime', 'system-version', 'boardName'
        ];

        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = 'Not available';
        });

        // Update details with proper formatting
        const memoryDetails = document.getElementById('memoryDetails');
        if (memoryDetails) {
            memoryDetails.textContent = '0 B of 0 B';
            memoryDetails.style.display = 'block';
        }

        const diskDetails = document.getElementById('diskDetails');
        if (diskDetails) {
            diskDetails.textContent = '0 B of 0 B';
            diskDetails.style.display = 'block';
        }

        // Reset progress bars
        const progressBars = ['cpuProgressBar', 'memoryProgressBar', 'diskProgressBar'];
        progressBars.forEach(id => {
            const bar = document.getElementById(id);
            if (bar) {
                bar.style.width = '0%';
                bar.className = 'progress-bar bg-secondary';
            }
        });
    }

    function updateSystemInfoDisplay(systemData) {
        console.log('updateSystemInfoDisplay called with:', systemData);

        // Update CPU usage
        const cpuElement = document.getElementById('cpu-usage');
        const cpuProgressBar = document.getElementById('cpuProgressBar');
        if (cpuElement && systemData.cpu_usage) {
            const cpuValue = parseFloat(systemData.cpu_usage.toString().replace('%', '')) || 0;
            cpuElement.textContent = cpuValue + '%';
            if (cpuProgressBar) {
                cpuProgressBar.style.width = cpuValue + '%';
                cpuProgressBar.className = 'progress-bar';
                if (cpuValue > 80) cpuProgressBar.classList.add('bg-danger');
                else if (cpuValue > 60) cpuProgressBar.classList.add('bg-warning');
                else cpuProgressBar.classList.add('bg-success');
            }
        }

        // Update Memory usage with details
        const memoryElement = document.getElementById('memory-usage');
        const memoryProgressBar = document.getElementById('memoryProgressBar');
        const memoryDetails = document.getElementById('memoryDetails');
        if (memoryElement && systemData.memory_usage) {
            const memoryValue = parseFloat(systemData.memory_usage.toString().replace('%', '')) || 0;
            memoryElement.textContent = memoryValue + '%';
            if (memoryProgressBar) {
                memoryProgressBar.style.width = memoryValue + '%';
                memoryProgressBar.className = 'progress-bar';
                if (memoryValue > 85) memoryProgressBar.classList.add('bg-danger');
                else if (memoryValue > 70) memoryProgressBar.classList.add('bg-warning');
                else memoryProgressBar.classList.add('bg-info');
            }
            if (memoryDetails) {
                const memUsed = systemData.memory_used || '0 B';
                const memTotal = systemData.memory_total || '0 B';
                memoryDetails.textContent = `${memUsed} of ${memTotal}`;
                memoryDetails.style.display = 'block';
            }
        } else if (memoryDetails) {
            memoryDetails.textContent = '0 B of 0 B';
            memoryDetails.style.display = 'block';
        }

        // Update Disk usage with details
        const diskElement = document.getElementById('disk-usage');
        const diskProgressBar = document.getElementById('diskProgressBar');
        const diskDetails = document.getElementById('diskDetails');
        if (diskElement && systemData.disk_usage) {
            const diskValue = parseFloat(systemData.disk_usage.toString().replace('%', '')) || 0;
            diskElement.textContent = diskValue + '%';
            if (diskProgressBar) {
                diskProgressBar.style.width = diskValue + '%';
                diskProgressBar.className = 'progress-bar';
                if (diskValue > 90) diskProgressBar.classList.add('bg-danger');
                else if (diskValue > 75) diskProgressBar.classList.add('bg-warning');
                else diskProgressBar.classList.add('bg-success');
            }
            if (diskDetails) {
                const diskUsed = systemData.disk_used || '0 B';
                const diskTotal = systemData.disk_total || '0 B';
                diskDetails.textContent = `${diskUsed} of ${diskTotal}`;
                diskDetails.style.display = 'block';
            }
        } else if (diskDetails) {
            diskDetails.textContent = '0 B of 0 B';
            diskDetails.style.display = 'block';
        }

        // Update other system info with comprehensive fallbacks
        const uptimeElement = document.getElementById('system-uptime');
        const versionElement = document.getElementById('system-version');
        const boardElement = document.getElementById('boardName');

        // Update uptime
        if (uptimeElement) {
            uptimeElement.textContent = systemData.uptime || 'Unknown';
        }

        // Update version with multiple fallback attempts
        if (versionElement) {
            // Try multiple possible field names
            const versionValue = systemData.version ||
                systemData.system_version ||
                systemData.routeros_version ||
                systemData['router-version'] ||
                'Unknown Version';
            versionElement.textContent = versionValue;
            console.log('Version updated to:', versionValue, 'from systemData:', {
                version: systemData.version,
                system_version: systemData.system_version,
                routeros_version: systemData.routeros_version
            });
        }

        // Update board name
        if (boardElement) {
            const boardValue = systemData.board_name ||
                systemData['board-name'] ||
                'Unknown';
            boardElement.textContent = boardValue;
        }
    }



    function updateConnectionStatus(status) {
        const connectionStatus = document.getElementById('connectionStatus');
        if (!connectionStatus) return;

        let statusHTML = '';
        switch (status) {
            case 'loading':
                statusHTML = '<span class="badge bg-warning">Connecting...</span>';
                break;
            case 'online':
            case 'connected':
                statusHTML = '<span class="badge bg-success">Online</span>';
                break;
            case 'offline':
            case 'error':
                statusHTML = '<span class="badge bg-danger">Offline</span>';
                break;
        }
        connectionStatus.innerHTML = statusHTML;
    }

    function updateLastUpdateTime() {
        const lastUpdateElement = document.getElementById('lastUpdate');
        if (lastUpdateElement) {
            const now = new Date();
            lastUpdateElement.textContent = 'Last updated: ' + now.toLocaleTimeString();
        }
    }

    function initSystemInfo() {
        console.log('=== initSystemInfo called - Starting system info initialization ===');
        refreshSystemInfo();
        // Auto-refresh system info every 90 seconds for better performance
        const refreshInterval = setInterval(refreshSystemInfo, 90000);
        window.systemInfoInterval = refreshInterval;
        console.log('System info auto-refresh interval set to 90 seconds');
    }

    // Load ApexCharts library
    function loadApexCharts() {
        return new Promise((resolve, reject) => {
            if (window.ApexCharts) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    // Customer Statistics Functions
    async function initCustomerStatistics(retryCount = 0) {
        const maxRetries = 1; // Reduced retries
        const timeoutDuration = 15000; // Increased to 15 seconds

        // Simplified API endpoints - remove failing endpoints
        const apiEndpoints = [
            '/api/dashboard/customer-stats',
            '/api/dashboard/statistics'
        ];

        const currentEndpoint = apiEndpoints[retryCount % apiEndpoints.length];

        try {
            // Show loading indicator for statistics
            if (retryCount === 0) {
                showStatisticsLoading();
            }

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), timeoutDuration);

            const customerStatsResponse = await fetch(currentEndpoint, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache',
                    'Accept': 'application/json'
                }
            });
            clearTimeout(timeoutId); // Check if response is not login page or unauthorized
            if (customerStatsResponse.url.includes('login') || customerStatsResponse.status === 302) {
                console.warn('User not logged in, keeping server-side values for customer statistics');
                hideStatisticsLoading();
                showStatisticsError('Not authenticated');
                return;
            }

            if (customerStatsResponse.status === 401) {
                console.warn('Authentication required for customer statistics');
                hideStatisticsLoading();
                showStatisticsError('Authentication required');
                return;
            }

            if (!customerStatsResponse.ok) {
                throw new Error(`HTTP ${customerStatsResponse.status}: ${customerStatsResponse.statusText}`);
            }

            const customerData = await customerStatsResponse.json();

            if (customerData.status === 'success' && customerData.data) {
                updateCustomerStatistics(customerData.data);
                hideStatisticsLoading();
                console.log('Customer statistics updated successfully');
            } else {
                throw new Error('Invalid response format or no data received');
            }

        } catch (error) {
            hideStatisticsLoading();

            if (error.name === 'AbortError') {
                if (retryCount < maxRetries) {
                    console.log(`Customer statistics timeout on ${currentEndpoint}, retrying...`);
                    setTimeout(() => initCustomerStatistics(retryCount + 1), 3000);
                    return;
                } else {
                    console.log('Customer statistics timeout, using server-side values');
                    // Don't show error, just keep existing values
                }
            } else if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
                if (retryCount < maxRetries) {
                    console.log(`Network error on customer statistics, retrying...`);
                    setTimeout(() => initCustomerStatistics(retryCount + 1), 3000);
                    return;
                } else {
                    console.log('Network error on customer statistics, using server-side values');
                    // Don't show error, just keep existing values
                }
            } else {
                console.log(`Customer statistics error: ${error.message}`);
                if (retryCount < maxRetries) {
                    setTimeout(() => initCustomerStatistics(retryCount + 1), 3000);
                    return;
                }
                // Don't show error, just keep existing values
            }
        }
    }

    function showStatisticsLoading() {
        const loadingElements = document.querySelectorAll('.counter-value');
        loadingElements.forEach(element => {
            const originalValue = element.textContent;
            element.setAttribute('data-original-value', originalValue);
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        });
    }

    function hideStatisticsLoading() {
        const loadingElements = document.querySelectorAll('.counter-value');
        loadingElements.forEach(element => {
            if (element.innerHTML.includes('fa-spinner')) {
                const originalValue = element.getAttribute('data-original-value') || '0';
                element.textContent = originalValue;
                element.removeAttribute('data-original-value');
            }
        });
    }

    function showStatisticsError(errorType) {
        const errorElements = document.querySelectorAll('.counter-value');
        errorElements.forEach(element => {
            if (element.innerHTML.includes('fa-spinner')) {
                const originalValue = element.getAttribute('data-original-value') || '0';
                element.textContent = originalValue;
                element.removeAttribute('data-original-value');
                element.style.opacity = '1'; // Keep full opacity
                element.title = `Using server-side values`;
            }
        });

        // No notification - keep silent for better UX
    }

    function refreshCustomerStatistics() {
        const refreshBtn = document.getElementById('refreshStatsBtn');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="ri-loader-2-line ri-spin me-1"></i>Refreshing...';

            initCustomerStatistics(0).finally(() => {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalContent;
            });
        } else {
            initCustomerStatistics(0);
        }
    }

    function updateCustomerStatistics(statisticsData) {
        const counterMappings = [{
                selector: '.counter-value[data-target="<?= $totalCustomers ?>"]',
                value: statisticsData.total_customers,
                label: 'Total Customers'
            },
            {
                selector: '.counter-value[data-target="<?= $paidInvoices ?>"]',
                value: statisticsData.paid_invoices,
                label: 'Paid Invoices'
            },
            {
                selector: '.counter-value[data-target="<?= $unpaidInvoices ?>"]',
                value: statisticsData.unpaid_invoices,
                label: 'Unpaid Invoices'
            },
            {
                selector: '.counter-value[data-target="<?= $suspendedCustomers ?>"]',
                value: statisticsData.suspended_customers,
                label: 'Suspended Customers'
            },
            {
                selector: '.counter-value[data-target="<?= $notInstalledCustomers ?>"]',
                value: statisticsData.not_installed_customers,
                label: 'Not Installed Customers'
            }
        ];

        counterMappings.forEach(mapping => {
            const elements = document.querySelectorAll(mapping.selector);
            elements.forEach(element => {
                if (element && mapping.value !== undefined && mapping.value !== null) {
                    const numericValue = parseInt(mapping.value) || 0;
                    element.textContent = numericValue.toString();
                    element.setAttribute('data-target', numericValue);
                }
            });
        });

        // Additional protection against percentage symbols
        document.querySelectorAll('.counter-value').forEach(element => {
            if (element.textContent.includes('%')) {
                const cleanValue = element.textContent.replace('%', '').trim();
                if (!isNaN(cleanValue) && cleanValue !== '') {
                    element.textContent = cleanValue;
                }
            }
        });

    }

    // Revenue vs Expense Chart Functions
    let revenueExpenseChart = null;
    async function initRevenueExpenseChart() {
        dashboardCache.financialChart = null;
        dashboardCache.lastUpdate = null;

        try {
            // Set timeout untuk request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 8000); // 8 detik timeout

            // Call the financial chart API endpoint
            const response = await fetch('/api/dashboard/financial-chart', {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();

            // Check if we have valid data from the API
            if (result && result.status === 'success' && result.data) {
                const chartData = {
                    months: result.data.months || [],
                    revenue: result.data.revenue || [],
                    expense: result.data.expense || []
                };

                dashboardCache.financialChart = {
                    data: chartData,
                    period: result.period || 'Periode tidak tersedia'
                };
                dashboardCache.lastUpdate = Date.now();
                updateRevenueExpenseChart(chartData);
                updateFinancialPeriod(dashboardCache.financialChart.period);
                return;
            }

            throw new Error('Invalid data format from API');
        } catch (error) {
            console.error('Error loading financial chart:', error);
            console.error('Error loading financial chart:', error);
            loadDefaultRevenueExpenseChart();
        }
    }

    function updateRevenueExpenseChart(chartData, skipDefault = false) {
        const chartContainer = document.querySelector("#revenueExpenseChart");

        if (!chartData || !chartData.revenue || !chartData.expense || !chartData.months) {
            if (!skipDefault) loadDefaultRevenueExpenseChart();
            return;
        }

        const revenue = chartData.revenue.map(val => parseFloat(val) || 0);
        const expense = chartData.expense.map(val => parseFloat(val) || 0);
        const months = chartData.months || [];

        // Periksa apakah ada data yang valid
        const hasData = revenue.some(val => val > 0) || expense.some(val => val > 0);

        const maxValue = Math.max(...revenue, ...expense, 500000);
        const yAxisMax = Math.ceil(maxValue / 500000) * 500000;
        const tickCount = yAxisMax / 500000;
        const revenueExpenseOptions = {
            series: [{
                name: 'Pendapatan',
                data: revenue.length > 0 ? revenue : [0]
            }, {
                name: 'Pengeluaran',
                data: expense.length > 0 ? expense : [0]
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            colors: ['#5b73e8', '#f1556c'],
            dataLabels: {
                enabled: false // Nonaktifkan data labels di dalam chart
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient'
            },
            xaxis: {
                categories: months.length > 0 ? months : ['No Data']
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                tickAmount: tickCount,
                forceNiceScale: false,
                labels: {
                    formatter: function(val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            }
        };

        // Tambahkan subtitle hanya jika tidak ada data
        if (!hasData) {
            revenueExpenseOptions.subtitle = {
                text: 'Belum ada transaksi pada periode ini',
                align: 'center',
                style: {
                    fontSize: '14px',
                    color: '#999'
                }
            };
        }

        if (revenueExpenseChart) {
            try {
                revenueExpenseChart.destroy();
            } catch (e) {}
        }

        try {
            revenueExpenseChart = new ApexCharts(chartContainer, revenueExpenseOptions);
            revenueExpenseChart.render().catch(() => {
                chartContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="height: 350px;"><div class="text-center"><i class="ri-alert-line text-warning fs-1"></i><br><span class="text-muted">Error loading chart</span></div></div>';
            });
        } catch (error) {
            chartContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="height: 350px;"><div class="text-center"><i class="ri-alert-line text-danger fs-1"></i><br><span class="text-muted">Chart failed to load</span></div></div>';
        }
    }

    function updateFinancialPeriod(period) {
        const periodBadge = document.getElementById('revenuePeriodBadge');
        if (periodBadge) {
            if (period && period.includes(' - ')) {
                periodBadge.textContent = `Periode: ${period}`;
            } else if (period && period.includes('Belum ada')) {
                periodBadge.textContent = period;
            } else {
                periodBadge.textContent = `Periode: ${period || 'Bulan Berjalan'}`;
            }
        }
    }

    // Fungsi refresh chart revenue
    function refreshRevenueChart() {
        const refreshBtn = document.getElementById('refreshRevenueBtn');
        const periodBadge = document.getElementById('revenuePeriodBadge');

        if (refreshBtn) {
            refreshBtn.disabled = true;
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="ri-loader-2-line ri-spin"></i>';

            if (periodBadge) {
                periodBadge.textContent = 'Memuat ulang...';
            }

            initRevenueExpenseChart().finally(() => {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalContent;
            });
        } else {
            initRevenueExpenseChart();
        }
    }

    function loadDefaultRevenueExpenseChart() {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const months = [];
        const today = new Date();

        for (let i = 2; i >= 0; i--) {
            const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
            months.push(`${monthNames[date.getMonth()]} ${date.getFullYear()}`);
        }

        const defaultData = {
            months: months,
            revenue: [0, 0, 0],
            expense: [0, 0, 0]
        };

        updateRevenueExpenseChart(defaultData, true);
        updateFinancialPeriod(`${months[0]} - ${months[2]} (Belum ada data)`);
    }

    // Final initialization - enhanced counter protection
    document.addEventListener('DOMContentLoaded', quickCounterFix);
</script>
<?= $this->endSection() ?>