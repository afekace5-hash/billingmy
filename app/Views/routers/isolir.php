<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><?= $title ?></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('routers/list') ?>">Router</a></li>
                            <li class="breadcrumb-item active">Isolir</li>
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
                                    <i class="mdi mdi-lock-outline text-warning me-2"></i>
                                    Manajemen Isolir PPPoE
                                </h4>
                                <p class="card-title-desc mb-0">Kelola isolir dan pembukaan isolir untuk customer PPPoE</p>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-warning" id="bulkIsolirBtn" disabled>
                                        <i class="mdi mdi-lock me-1"></i>
                                        Isolir Terpilih
                                    </button>
                                    <button class="btn btn-success" id="bulkUnIsolirBtn" disabled>
                                        <i class="mdi mdi-lock-open me-1"></i>
                                        Buka Isolir Terpilih
                                    </button>
                                    <button class="btn btn-info" id="refreshBtn">
                                        <i class="mdi mdi-refresh me-1"></i>
                                        Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Controls -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="filterRouter" class="form-label">Filter Router</label> <select class="form-select" id="filterRouter">
                                    <option value="">Semua Router</option>
                                    <?php foreach ($routers as $router): ?>
                                        <option value="<?= $router['id_lokasi'] ?>"><?= esc($router['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterStatus" class="form-label">Status Isolir</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="0">Normal (Tidak Diisolir)</option>
                                    <option value="1">Diisolir</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="searchCustomer" class="form-label">Cari Customer</label>
                                <input type="text" class="form-control" id="searchCustomer" placeholder="Nama customer atau username PPPoE...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="applyFilterBtn">
                                    <i class="mdi mdi-filter me-1"></i>
                                    Filter
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-white bg-opacity-25 font-size-20">
                                                    <i class="mdi mdi-account-multiple"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-white mb-1" id="totalCustomers">-</h5>
                                                <p class="text-white-75 mb-0">Total Customer PPPoE</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-white bg-opacity-25 font-size-20">
                                                    <i class="mdi mdi-lock-open"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-white mb-1" id="normalCustomers">-</h5>
                                                <p class="text-white-75 mb-0">Normal (Tidak Diisolir)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-white bg-opacity-25 font-size-20">
                                                    <i class="mdi mdi-lock"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-white mb-1" id="isolatedCustomers">-</h5>
                                                <p class="text-white-75 mb-0">Diisolir</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title rounded-circle bg-white bg-opacity-25 font-size-20">
                                                    <i class="mdi mdi-percent"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-white mb-1" id="isolationRate">-</h5>
                                                <p class="text-white-75 mb-0">Tingkat Isolir</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Table -->
                        <div class="table-responsive">
                            <table class="table table-nowrap table-hover mb-0" id="customerTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="3%">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                                <label class="form-check-label" for="selectAll"></label>
                                            </div>
                                        </th>
                                        <th width="20%">Nama Customer</th>
                                        <th width="15%">Username PPPoE</th>
                                        <th width="12%">Router</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Tanggal Isolir</th>
                                        <th width="15%">Alasan</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="customerTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Loading data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <div class="dataTables_info">
                                    Menampilkan <span id="showingFrom">0</span> sampai <span id="showingTo">0</span> dari <span id="totalEntries">0</span> entries
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="dataTables_paginate float-end">
                                    <ul class="pagination pagination-rounded mb-0" id="pagination">
                                        <!-- Pagination akan diisi via JavaScript -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Isolir Logs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-history text-info me-2"></i>
                            Log Aktivitas Isolir Terbaru
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Customer</th>
                                        <th>Username PPPoE</th>
                                        <th>Router</th>
                                        <th>Aksi</th>
                                        <th>Status</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody id="logTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Loading logs...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Isolir Modal -->
<div class="modal fade" id="isolirModal" tabindex="-1" aria-labelledby="isolirModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="isolirModalLabel">Konfirmasi Isolir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="isolirForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="isolirCustomerId" name="customer_id">
                    <input type="hidden" id="isolirRouterId" name="router_id">
                    <input type="hidden" id="isolirAction" name="action" value="isolir">

                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        Anda akan mengisolir customer: <strong id="isolirCustomerName"></strong>
                    </div>

                    <div class="mb-3">
                        <label for="isolirReason" class="form-label">Alasan Isolir <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="isolirReason" name="reason" rows="3" placeholder="Masukkan alasan isolir..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="mdi mdi-lock me-1"></i>
                        Isolir Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Un-Isolir Modal -->
<div class="modal fade" id="unIsolirModal" tabindex="-1" aria-labelledby="unIsolirModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unIsolirModalLabel">Konfirmasi Buka Isolir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="unIsolirForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="unIsolirCustomerId" name="customer_id">
                    <input type="hidden" id="unIsolirRouterId" name="router_id">
                    <input type="hidden" id="unIsolirAction" name="action" value="unIsolir">

                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Anda akan membuka isolir untuk customer: <strong id="unIsolirCustomerName"></strong>
                    </div>

                    <p class="text-muted">Customer akan dapat menggunakan layanan PPPoE kembali setelah isolir dibuka.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-lock-open me-1"></i>
                        Buka Isolir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">Konfirmasi Aksi Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkActionForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="bulkCustomerIds" name="customer_ids">
                    <input type="hidden" id="bulkAction" name="action">

                    <div id="bulkActionAlert"></div>

                    <p>Customer yang dipilih: <strong id="bulkSelectedCount">0</strong></p>

                    <div class="mb-3" id="bulkReasonField" style="display: none;">
                        <label for="bulkReason" class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="bulkReason" name="reason" rows="3" placeholder="Masukkan alasan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="bulkActionConfirmBtn" class="btn">
                        <i class="mdi mdi-check me-1"></i>
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        let itemsPerPage = 25;
        let totalItems = 0;
        let allCustomers = <?= json_encode($customers ?: []) ?>;
        let filteredCustomers = [...allCustomers];
        let selectedCustomers = [];

        // Debug: Show data status
        console.log('Data loaded from PHP:', allCustomers.length, 'customers');
        if (allCustomers.length > 0) {
            console.log('First customer:', allCustomers[0]);
        } else {
            console.log('NO CUSTOMERS LOADED!');
        }

        // IMMEDIATE DATA RENDERING - NO DELAYS
        renderDataNow();

        // Force show something in table if no data
        if (allCustomers.length === 0) {
            $('#customerTableBody').html('<tr><td colspan="8" class="text-center text-danger"><strong>⚠️ NO DATA LOADED - Check database connection</strong></td></tr>');
        }

        // Router mapping for quick lookup
        const routers = <?= json_encode($routers) ?>;
        const routerMap = {};
        routers.forEach(router => {
            routerMap[router.id_lokasi] = router.name;
        });

        // RENDER FUNCTION - SIMPLE AND DIRECT
        function renderDataNow() {
            console.log('renderDataNow called with', allCustomers.length, 'customers');

            if (allCustomers.length === 0) {
                $('#customerTableBody').html('<tr><td colspan="8" class="text-center text-warning">Tidak ada customer dengan PPPoE username di database</td></tr>');
                $('#totalCustomers').text('0');
                $('#normalCustomers').text('0');
                $('#isolatedCustomers').text('0');
                $('#isolationRate').text('0%');
                return;
            }

            // Render customers directly from PHP data
            let html = '';
            let isolated = 0;
            let normal = 0;

            allCustomers.forEach(customer => {
                const isIsolated = customer.isolir_status == 1;
                if (isIsolated) isolated++;
                else normal++;

                const statusBadge = isIsolated ?
                    '<span class="badge bg-warning">Diisolir</span>' :
                    '<span class="badge bg-success">Normal</span>';

                const isolirDate = customer.isolir_date ?
                    new Date(customer.isolir_date).toLocaleDateString('id-ID') :
                    '-';

                const reason = customer.isolir_reason || '-';
                const routerName = routerMap[customer.id_lokasi_server] || 'Router ID: ' + customer.id_lokasi_server;

                html += `
                <tr>
                    <td><div class="form-check"><input class="form-check-input customer-checkbox" type="checkbox" data-customer-id="${customer.id_customers}"></div></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                    ${customer.nama_pelanggan.charAt(0).toUpperCase()}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">${customer.nama_pelanggan}</h6>
                                <small class="text-muted">ID: ${customer.id_customers}</small>
                            </div>
                        </div>
                    </td>
                    <td><code>${customer.pppoe_username}</code></td>
                    <td>${routerName}</td>
                    <td>${statusBadge}</td>
                    <td>${isolirDate}</td>
                    <td class="text-truncate" style="max-width: 150px;" title="${reason}">${reason}</td>
                    <td>
                        ${isIsolated 
                            ? '<button class="btn btn-sm btn-success"><i class="mdi mdi-lock-open"></i></button>'
                            : '<button class="btn btn-sm btn-warning"><i class="mdi mdi-lock"></i></button>'
                        }
                    </td>
                </tr>`;
            });

            $('#customerTableBody').html(html);

            // Update statistics with real data
            const total = allCustomers.length;
            $('#totalCustomers').text(total);
            $('#normalCustomers').text(normal);
            $('#isolatedCustomers').text(isolated);
            $('#isolationRate').text(total > 0 ? Math.round((isolated / total) * 100) + '%' : '0%');

            // Update pagination info
            $('#showingFrom').text(total > 0 ? 1 : 0);
            $('#showingTo').text(total);
            $('#totalEntries').text(total);

            console.log('Data rendered:', total, 'total,', isolated, 'isolated,', normal, 'normal');
        }

        // Initialize page - DIRECT RENDER
        console.log('Loading data...', allCustomers.length, 'customers found');

        // Pastikan data langsung dirender saat halaman dimuat
        $(document).ready(function() {
            setTimeout(function() {
                renderDataNow();
                loadRecentLogs();
            }, 500); // Beri waktu sebentar untuk DOM siap
        });

        // Event Listeners
        $('#applyFilterBtn').on('click', applyFilters);
        $('#searchCustomer').on('keyup', debounce(applyFilters, 300));
        $('#refreshBtn').on('click', refreshData);
        $('#selectAll').on('change', toggleSelectAll);
        $('#bulkIsolirBtn').on('click', () => showBulkActionModal('isolir'));
        $('#bulkUnIsolirBtn').on('click', () => showBulkActionModal('unIsolir'));

        // Form submissions
        $('#isolirForm').on('submit', handleIsolirSubmit);
        $('#unIsolirForm').on('submit', handleUnIsolirSubmit);
        $('#bulkActionForm').on('submit', handleBulkActionSubmit);

        function loadCustomerData() {
            console.log('loadCustomerData called');
            updateTable();
            updatePagination();
            updateStatistics();
        }

        function applyFilters() {
            const routerFilter = $('#filterRouter').val();
            const statusFilter = $('#filterStatus').val();
            const searchTerm = $('#searchCustomer').val().toLowerCase();

            filteredCustomers = allCustomers.filter(customer => {
                let matchRouter = !routerFilter || customer.id_lokasi_server == routerFilter;
                let matchStatus = statusFilter === '' || customer.isolir_status == statusFilter;
                let matchSearch = !searchTerm ||
                    customer.nama_pelanggan.toLowerCase().includes(searchTerm) ||
                    customer.pppoe_username.toLowerCase().includes(searchTerm);

                return matchRouter && matchStatus && matchSearch;
            });

            currentPage = 1;
            selectedCustomers = [];
            updateTable();
            updatePagination();
            updateStatistics();
            updateBulkButtons();
        }

        function updateTable() {
            console.log('updateTable called with', filteredCustomers.length, 'customers');

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageCustomers = filteredCustomers.slice(startIndex, endIndex);

            let html = '';
            if (pageCustomers.length === 0) {
                if (filteredCustomers.length === 0 && allCustomers.length === 0) {
                    html = '<tr><td colspan="8" class="text-center text-warning">Tidak ada customer dengan PPPoE username di database</td></tr>';
                } else {
                    const filterStatus = $('#filterStatus').val();
                    let message = 'Tidak ada data customer';
                    if (filterStatus === '1') {
                        message = 'Tidak ada customer yang sedang diisolir';
                    } else if (filterStatus === '0') {
                        message = 'Tidak ada customer dengan status normal';
                    }
                    html = `<tr><td colspan="8" class="text-center text-muted">${message}</td></tr>`;
                }
            } else {
                pageCustomers.forEach(customer => {
                    const isSelected = selectedCustomers.includes(customer.id_customers);
                    const statusBadge = customer.isolir_status == 1 ?
                        '<span class="badge bg-warning">Diisolir</span>' :
                        '<span class="badge bg-success">Normal</span>';

                    const isolirDate = customer.isolir_date ?
                        new Date(customer.isolir_date).toLocaleDateString('id-ID') :
                        '-';

                    const reason = customer.isolir_reason || '-';
                    const routerName = routerMap[customer.id_lokasi_server] || 'Unknown';

                    html += `
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input customer-checkbox" type="checkbox" 
                                       data-customer-id="${customer.id_customers}" ${isSelected ? 'checked' : ''}>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                        ${customer.nama_pelanggan.charAt(0).toUpperCase()}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">${customer.nama_pelanggan}</h6>
                                    <small class="text-muted">ID: ${customer.id_customers}</small>
                                </div>
                            </div>
                        </td>
                        <td><code>${customer.pppoe_username}</code></td>
                        <td>${routerName}</td>
                        <td>${statusBadge}</td>
                        <td>${isolirDate}</td>
                        <td class="text-truncate" style="max-width: 150px;" title="${reason}">${reason}</td>
                        <td>
                            ${customer.isolir_status == 1 
                                ? `<button class="btn btn-sm btn-success" onclick="showUnIsolirModal(${customer.id_customers}, '${customer.nama_pelanggan}', ${customer.id_lokasi_server})">
                                     <i class="mdi mdi-lock-open"></i>
                                   </button>`
                                : `<button class="btn btn-sm btn-warning" onclick="showIsolirModal(${customer.id_customers}, '${customer.nama_pelanggan}', ${customer.id_lokasi_server})">
                                     <i class="mdi mdi-lock"></i>
                                   </button>`
                            }
                        </td>
                    </tr>
                `;
                });
            }

            $('#customerTableBody').html(html);

            // Update pagination info
            const startItem = filteredCustomers.length > 0 ? startIndex + 1 : 0;
            const endItem = Math.min(endIndex, filteredCustomers.length);
            $('#showingFrom').text(startItem);
            $('#showingTo').text(endItem);
            $('#totalEntries').text(filteredCustomers.length);

            // Attach checkbox event listeners
            $('.customer-checkbox').on('change', handleCustomerSelection);
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredCustomers.length / itemsPerPage);
            let html = '';

            if (totalPages > 1) {
                // Previous button
                html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                     </li>`;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                             </li>`;
                    } else if (i === currentPage - 3 || i === currentPage + 3) {
                        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                // Next button
                html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                     </li>`;
            }

            $('#pagination').html(html);

            // Attach pagination event listeners
            $('#pagination a').on('click', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                    currentPage = page;
                    updateTable();
                    updatePagination();
                }
            });
        }

        function updateStatistics() {
            const total = allCustomers.length;
            const totalFiltered = filteredCustomers.length;
            const isolated = allCustomers.filter(c => c.isolir_status == 1).length;
            const normal = total - isolated;
            const rate = total > 0 ? ((isolated / total) * 100).toFixed(1) + '%' : '0%';

            $('#totalCustomers').text(total);
            $('#normalCustomers').text(normal);
            $('#isolatedCustomers').text(isolated);
            $('#isolationRate').text(rate);
        }

        function handleCustomerSelection() {
            const customerId = parseInt($(this).data('customer-id'));
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                if (!selectedCustomers.includes(customerId)) {
                    selectedCustomers.push(customerId);
                }
            } else {
                selectedCustomers = selectedCustomers.filter(id => id !== customerId);
            }

            updateSelectAllCheckbox();
            updateBulkButtons();
        }

        function toggleSelectAll() {
            const isChecked = $('#selectAll').is(':checked');
            const visibleCustomers = filteredCustomers.slice(
                (currentPage - 1) * itemsPerPage,
                currentPage * itemsPerPage
            );

            $('.customer-checkbox').prop('checked', isChecked);

            if (isChecked) {
                visibleCustomers.forEach(customer => {
                    if (!selectedCustomers.includes(customer.id_customers)) {
                        selectedCustomers.push(customer.id_customers);
                    }
                });
            } else {
                const visibleIds = visibleCustomers.map(c => c.id_customers);
                selectedCustomers = selectedCustomers.filter(id => !visibleIds.includes(id));
            }

            updateBulkButtons();
        }

        function updateSelectAllCheckbox() {
            const visibleCustomers = filteredCustomers.slice(
                (currentPage - 1) * itemsPerPage,
                currentPage * itemsPerPage
            );
            const visibleIds = visibleCustomers.map(c => c.id_customers);
            const selectedVisibleCount = selectedCustomers.filter(id => visibleIds.includes(id)).length;

            $('#selectAll').prop('checked', selectedVisibleCount === visibleIds.length && visibleIds.length > 0);
        }

        function updateBulkButtons() {
            const hasSelected = selectedCustomers.length > 0;
            $('#bulkIsolirBtn, #bulkUnIsolirBtn').prop('disabled', !hasSelected);

            if (hasSelected) {
                $('#bulkIsolirBtn, #bulkUnIsolirBtn').find('.badge').remove();
                $('#bulkIsolirBtn').append(`<span class="badge bg-light text-dark ms-1">${selectedCustomers.length}</span>`);
                $('#bulkUnIsolirBtn').append(`<span class="badge bg-light text-dark ms-1">${selectedCustomers.length}</span>`);
            }
        }

        function refreshData() {
            $('#refreshBtn').find('i').addClass('mdi-spin');

            // Reload customer data from server
            $.ajax({
                url: '<?= site_url('routers/isolir') ?>',
                method: 'GET',
                data: {
                    ajax: true // Flag untuk request AJAX
                },
                success: function(response) {
                    if (response.success && response.customers) {
                        // Update customer data
                        allCustomers = response.customers;
                        filteredCustomers = [...allCustomers];
                        selectedCustomers = [];

                        // Refresh display
                        loadCustomerData();
                        loadRecentLogs();
                        updateBulkButtons();

                        showToast('success', 'Data berhasil direfresh dari Mikrotik');
                    } else {
                        showToast('error', 'Gagal memuat data dari server');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat data';
                    showToast('error', message);
                },
                complete: function() {
                    $('#refreshBtn').find('i').removeClass('mdi-spin');
                }
            });
        }

        function loadRecentLogs() {
            $.ajax({
                url: '<?= site_url('routers/isolir/log') ?>',
                method: 'GET',
                data: {
                    limit: 10
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.data.length === 0) {
                            html = '<tr><td colspan="7" class="text-center text-muted">Belum ada log aktivitas</td></tr>';
                        } else {
                            response.data.forEach(log => {
                                const date = new Date(log.created_at).toLocaleString('id-ID');
                                const statusBadge = log.status === 'success' ?
                                    '<span class="badge bg-success">Berhasil</span>' :
                                    '<span class="badge bg-danger">Gagal</span>';
                                const actionBadge = log.action === 'isolir' ?
                                    '<span class="badge bg-warning">Isolir</span>' :
                                    '<span class="badge bg-success">Buka Isolir</span>';

                                html += `
                                <tr>
                                    <td>${date}</td>
                                    <td>${log.nama_pelanggan || '-'}</td>
                                    <td><code>${log.pppoe_username || '-'}</code></td>
                                    <td>${log.router_name || '-'}</td>
                                    <td>${actionBadge}</td>
                                    <td>${statusBadge}</td>
                                    <td class="text-truncate" style="max-width: 200px;" title="${log.reason || '-'}">${log.reason || '-'}</td>
                                </tr>
                            `;
                            });
                        }
                        $('#logTableBody').html(html);
                    }
                },
                error: function() {
                    $('#logTableBody').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat log</td></tr>');
                }
            });
        }

        // Modal Functions
        window.showIsolirModal = function(customerId, customerName, routerId) {
            $('#isolirCustomerId').val(customerId);
            $('#isolirCustomerName').text(customerName);
            $('#isolirRouterId').val(routerId);
            $('#isolirReason').val('');
            $('#isolirModal').modal('show');
        };

        window.showUnIsolirModal = function(customerId, customerName, routerId) {
            $('#unIsolirCustomerId').val(customerId);
            $('#unIsolirCustomerName').text(customerName);
            $('#unIsolirRouterId').val(routerId);
            $('#unIsolirModal').modal('show');
        };

        function showBulkActionModal(action) {
            if (selectedCustomers.length === 0) return;

            const isIsolir = action === 'isolir';
            $('#bulkAction').val(action);
            $('#bulkCustomerIds').val(JSON.stringify(selectedCustomers));
            $('#bulkSelectedCount').text(selectedCustomers.length);

            $('#bulkActionAlert').html(`
            <div class="alert alert-${isIsolir ? 'warning' : 'info'}">
                <i class="mdi mdi-${isIsolir ? 'alert-circle' : 'information'} me-2"></i>
                Anda akan ${isIsolir ? 'mengisolir' : 'membuka isolir'} ${selectedCustomers.length} customer yang dipilih.
            </div>
        `);

            $('#bulkActionModalLabel').text(`Konfirmasi ${isIsolir ? 'Isolir' : 'Buka Isolir'} Massal`);
            $('#bulkReasonField').toggle(isIsolir);
            $('#bulkReason').prop('required', isIsolir);

            const btn = $('#bulkActionConfirmBtn');
            btn.removeClass('btn-warning btn-success')
                .addClass(isIsolir ? 'btn-warning' : 'btn-success')
                .html(`<i class="mdi mdi-${isIsolir ? 'lock' : 'lock-open'} me-1"></i> ${isIsolir ? 'Isolir' : 'Buka Isolir'} Semua`);

            $('#bulkActionModal').modal('show');
        }

        // Form Handlers
        function handleIsolirSubmit(e) {
            e.preventDefault();
            submitIsolirAction('<?= site_url('routers/isolir/execute') ?>', '#isolirForm', '#isolirModal');
        }

        function handleUnIsolirSubmit(e) {
            e.preventDefault();
            submitIsolirAction('<?= site_url('routers/isolir/execute') ?>', '#unIsolirForm', '#unIsolirModal');
        }

        function handleBulkActionSubmit(e) {
            e.preventDefault();
            submitIsolirAction('<?= site_url('routers/isolir/bulk-execute') ?>', '#bulkActionForm', '#bulkActionModal');
        }

        function submitIsolirAction(url, formSelector, modalSelector) {
            const $form = $(formSelector);
            const $modal = $(modalSelector);
            const $submitBtn = $form.find('button[type="submit"]');

            // Disable submit button
            $submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Processing...');

            $.ajax({
                url: url,
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        $modal.modal('hide');

                        // Refresh data - in real implementation, reload from server
                        refreshData();
                        selectedCustomers = [];
                        updateBulkButtons();
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
                    showToast('error', message);
                },
                complete: function() {
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false);

                    // Reset button text based on form
                    if (formSelector === '#isolirForm') {
                        $submitBtn.html('<i class="mdi mdi-lock me-1"></i> Isolir Customer');
                    } else if (formSelector === '#unIsolirForm') {
                        $submitBtn.html('<i class="mdi mdi-lock-open me-1"></i> Buka Isolir');
                    } else {
                        const action = $('#bulkAction').val();
                        const isIsolir = action === 'isolir';
                        $submitBtn.html(`<i class="mdi mdi-${isIsolir ? 'lock' : 'lock-open'} me-1"></i> ${isIsolir ? 'Isolir' : 'Buka Isolir'} Semua`);
                    }
                }
            });
        }

        // Utility Functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function showToast(type, message) {
            // Use your existing toast notification system
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(message);
            }
        }
    });
</script>
<?= $this->endSection() ?>