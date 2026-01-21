<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        height: 100%;
        width: 100%;
        border-radius: 8px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4 mt-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-2">On Progress Installation</h4>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">Showing <span id="entriesCount"><?= count($onprogress) ?></span> of <?= count($onprogress) ?> entries</small>
                </div>
            </div>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('installation/waiting-list') ?>">Installation</a></li>
                    <li class="breadcrumb-item active" aria-current="page">On Progress</li>
                </ol>
            </nav>
        </div>

        <!-- Cards Section -->
        <div class="row" id="customerCards">
            <?php if (empty($onprogress)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Tidak ada customer dalam on progress installation
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($onprogress as $customer): ?>
                    <div class="col-lg-3 col-md-6 mb-4 customer-card"
                        data-name="<?= esc(strtolower($customer['nama_pelanggan'])) ?>"
                        data-package="<?= esc(strtolower($customer['package_name'] ?? '')) ?>"
                        data-branch="<?= esc(strtolower($customer['branch_name'] ?? '')) ?>">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">
                                    <i class="bi bi-person-circle me-2"></i><?= esc($customer['nama_pelanggan']) ?>
                                </h5>
                                <div class="mb-2">
                                    <small class="text-muted">Paket:</small>
                                    <span class="fw-bold ms-2"><?= esc($customer['package_name'] ?? 'Belum ditentukan') ?></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Branch:</small>
                                    <span class="ms-2"><?= esc($customer['branch_name'] ?? 'Belum ditentukan') ?></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Lokasi:</small>
                                    <span class="ms-2">
                                        <?php if (!empty($customer['coordinat'])): ?>
                                            <a href="https://www.google.com/maps?q=<?= esc($customer['coordinat']) ?>"
                                                target="_blank" class="text-decoration-none">
                                                <i class="bi bi-geo-alt-fill text-danger"></i> Open in maps
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ada lokasi</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">HP:</small>
                                    <span class="ms-2"><?= esc($customer['telepphone'] ?? '-') ?></span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <button class="btn btn-sm btn-primary btn-detail w-100"
                                        data-id="<?= $customer['id_customers'] ?>"
                                        data-name="<?= esc($customer['nama_pelanggan']) ?>">
                                        <i class="bi bi-info-circle me-1"></i> Info Detail
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-danger btn-cancel flex-fill"
                                            data-id="<?= $customer['id_customers'] ?>"
                                            data-name="<?= esc($customer['nama_pelanggan']) ?>">
                                            <i class="bi bi-x-circle me-1"></i> Cancel ×
                                        </button>
                                        <button class="btn btn-sm btn-info text-white btn-activate-modal flex-fill"
                                            data-id="<?= $customer['id_customers'] ?>"
                                            data-name="<?= esc($customer['nama_pelanggan']) ?>">
                                            <i class="bi bi-check-circle me-1"></i> Activate ✓
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Detail Modal -->
        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="detailModalLabel">Info Detail Installation</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detailContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activate Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="activateModalLabel">Activate Installation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="activateForm">
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <input type="text" class="form-control" id="activateCustomer" name="customer" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Inet Package Price</label>
                                <input type="text" class="form-control" id="activatePackagePrice" name="package_price" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <input type="text" class="form-control" id="activateAreaDisplay" readonly placeholder="Auto-filled from ODP">
                                <input type="hidden" id="activateArea" name="area">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ODP</label>
                                <select class="form-select" id="activateODP" name="odp">
                                    <option value="">Pilih ODP</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Select ODP Point</label>
                                <p class="text-muted small mb-2">Klik map untuk set lokasi customer, lalu pilih ODP untuk koneksi</p>
                                <div id="activateMap" style="height: 400px; border-radius: 8px;"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Router WIFI *</label>
                                <select class="form-select" id="activateRouter" name="router"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Activated *</label>
                                <input type="date" class="form-control" id="activateDate" name="date_activated">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method *</label>
                                <select class="form-select" id="activatePaymentMethod" name="payment_method"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pemegang Uang IKR</label>
                                <select class="form-select" id="activatePemegangIKR" name="pemegang_ikr"></select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tipe Customer *</label>
                                <input type="text" class="form-control" id="activateTipeCustomer" name="tipe_customer" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader Teknisi *</label>
                                <input type="text" class="form-control" id="activateLeaderTeknisi" name="leader_teknisi" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tim Teknisi *</label>
                                <select class="form-select" id="activateTimTeknisi" name="tim_teknisi">
                                    <option value="">Pilih Teknisi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activateCreatePPOE" name="create_ppoe" checked>
                        <label class="form-check-label" for="activateCreatePPOE">
                            Create PPOE?
                        </label>
                    </div>
                    <button type="submit" class="btn btn-info text-white">Process Activate ✓</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const customerCards = document.querySelectorAll('.customer-card');
        const entriesCount = document.getElementById('entriesCount');
        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let visibleCount = 0;

                customerCards.forEach(card => {
                    const name = card.dataset.name;
                    const packageName = card.dataset.package;
                    const branch = card.dataset.branch;

                    if (name.includes(searchTerm) || packageName.includes(searchTerm) || branch.includes(searchTerm)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                entriesCount.textContent = visibleCount;
            });
        }

        // Detail button click
        document.querySelectorAll('.btn-detail').forEach(button => {
            button.addEventListener('click', function() {
                const customerId = this.dataset.id;

                // Show modal
                detailModal.show();

                // Load customer detail
                fetch(`<?= base_url('installation/get-customer-detail') ?>/${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const customer = data.data;
                            document.getElementById('detailContent').innerHTML = `
                                <div class="container-fluid">
                                    <div class="row mb-2">
                                        <div class="col-12 fw-bold">Internet Package</div>
                                        <div class="col-12 mb-2">${customer.package_name ? customer.package_name + (customer.harga_paket ? ' (Rp. ' + customer.harga_paket + ')' : '') : '-'}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <div class="mb-2"><span class="fw-bold">Sales Name</span><br>${customer.nama_sales || '-'}</div>
                                            <div class="mb-2"><span class="fw-bold">Customer Phone</span><br>${customer.telepphone ? `<a href="tel:${customer.telepphone}">${customer.telepphone}</a>` : '-'}</div>
                                            <div class="mb-2"><span class="fw-bold">Area</span><br>${customer.area || '-'}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2"><span class="fw-bold">Customer Name</span><br>${customer.nama_pelanggan || '-'}</div>
                                            <div class="mb-2"><span class="fw-bold">Branch</span><br>${customer.branch_name || '-'}</div>
                                            <div class="mb-2"><span class="fw-bold">ODP</span><br>${customer.odp || '-'}</div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 fw-bold">Customer Address</div>
                                        <div class="col-12">
                                            ${customer.alamat_kecamatan ? '[' + customer.alamat_kecamatan + ']' : ''}<br>
                                            ${customer.alamat ? customer.alamat : '-'}
                                        </div>
                                    </div>
                                </div>
                            `;
                            // Tombol Open in Maps
                            let mapsBtn = '';
                            if (customer.coordinat) {
                                mapsBtn = `<button type="button" class="btn btn-success" onclick="window.open('https://www.google.com/maps?q=${customer.coordinat}','_blank')">Open in Maps</button>`;
                            }
                            document.querySelector('#detailModal .modal-footer').innerHTML = `
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                ${mapsBtn}
                            `;
                        } else {
                            document.getElementById('detailContent').innerHTML = `
                            <div class="alert alert-danger">${data.message}</div>
                        `;
                        }
                    })
                    .catch(error => {
                        document.getElementById('detailContent').innerHTML = `
                        <div class="alert alert-danger">Terjadi kesalahan saat memuat data</div>
                    `;
                    });
            });
        });

        // Activate Modal button click
        const activateModalElement = document.getElementById('activateModal');
        const activateModal = activateModalElement ? new bootstrap.Modal(activateModalElement) : null;
        let currentCustomerData = null;

        // Event handler untuk setelah modal dibuka
        if (activateModalElement) {
            activateModalElement.addEventListener('shown.bs.modal', function() {
                console.log('Modal fully shown, now setting values...');
                if (currentCustomerData) {
                    const customerInput = document.getElementById('activateCustomer');
                    const priceInput = document.getElementById('activatePackagePrice');
                    const tipeInput = document.getElementById('activateTipeCustomer');
                    const leaderInput = document.getElementById('activateLeaderTeknisi');
                    const dateInput = document.getElementById('activateDate');
                    const areaInput = document.getElementById('activateArea');

                    if (customerInput) {
                        customerInput.value = currentCustomerData.nama_pelanggan || 'N/A';
                        console.log('Customer name set:', customerInput.value);
                    }

                    if (priceInput) {
                        priceInput.value = currentCustomerData.harga ? 'Rp. ' + parseInt(currentCustomerData.harga).toLocaleString('id-ID') : 'Rp. 0';
                        console.log('Price set:', priceInput.value);
                    }

                    if (tipeInput) tipeInput.value = currentCustomerData.tipe_customer || 'Customer';
                    if (leaderInput) leaderInput.value = currentCustomerData.nama_sales || 'Admin';
                    if (dateInput) dateInput.value = (new Date()).toISOString().slice(0, 10);

                    // Area dan ODP akan di-set ketika user klik marker ODP di map
                }
            });
        }

        document.querySelectorAll('.btn-activate-modal').forEach(button => {
            button.addEventListener('click', function() {
                if (!activateModal) {
                    alert('Activate modal tidak ditemukan');
                    return;
                }

                const customerId = this.dataset.id;
                console.log('Customer ID:', customerId);

                // Store customer ID in form
                document.getElementById('activateForm').dataset.customerId = customerId;

                // Load customer detail untuk prefill form
                fetch(`<?= base_url('installation/get-customer-detail') ?>/${customerId}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            currentCustomerData = data.data;
                            console.log('Customer data saved:', currentCustomerData);

                            // Show modal - values will be set in shown.bs.modal event
                            activateModal.show();
                        } else {
                            alert('Gagal memuat data customer: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data customer');
                    });
            });
        });

        // Handle activation form submission
        const activateForm = document.getElementById('activateForm');
        if (activateForm) {
            activateForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const customerId = this.dataset.customerId;
                if (!customerId) {
                    Swal.fire('Error', 'Customer ID tidak ditemukan', 'error');
                    return;
                }

                // Get form data
                const formData = {
                    odp: document.getElementById('activateODP').value,
                    area: document.getElementById('activateArea').value,
                    router: document.getElementById('activateRouter').value,
                    date_activated: document.getElementById('activateDate').value,
                    payment_method: document.getElementById('activatePaymentMethod').value,
                    pemegang_ikr: document.getElementById('activatePemegangIKR').value,
                    tim_teknisi: document.getElementById('activateTimTeknisi').value,
                    create_ppoe: document.getElementById('activateCreatePPOE').checked ? 1 : 0,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                };

                // Validate required fields
                if (!formData.router || !formData.date_activated || !formData.payment_method) {
                    Swal.fire('Perhatian', 'Mohon lengkapi semua field yang wajib diisi (*)', 'warning');
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Sedang memproses aktivasi customer',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit to server
                fetch(`<?= base_url('installation/activate') ?>/${customerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'Customer berhasil diaktifkan',
                                showCancelButton: true,
                                confirmButtonText: 'Lihat Detail',
                                cancelButtonText: 'OK'
                            }).then((result) => {
                                // Close modal
                                activateModal.hide();

                                if (result.isConfirmed) {
                                    // Redirect to installation history/detail page
                                    window.location.href = `<?= base_url('installation/history') ?>/${customerId}`;
                                } else {
                                    // Reload page
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Terjadi kesalahan saat mengaktifkan customer', 'error');
                    });
            });
        }

        // Cancel button click
        document.querySelectorAll('.btn-cancel').forEach(button => {
            button.addEventListener('click', function() {
                const customerId = this.dataset.id;
                const customerName = this.dataset.name;

                Swal.fire({
                    title: 'Batalkan Instalasi?',
                    html: `Customer: <strong>${customerName}</strong><br><small>Customer akan kembali ke waitinglist</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const card = this.closest('.customer-card');

                        fetch(`<?= base_url('installation/cancel-progress') ?>/${customerId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                                },
                                body: JSON.stringify({
                                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove card with animation
                                    card.style.transition = 'opacity 0.3s';
                                    card.style.opacity = '0';
                                    setTimeout(() => {
                                        card.remove();
                                        // Update count
                                        const remaining = document.querySelectorAll('.customer-card').length;
                                        entriesCount.textContent = remaining;

                                        // Show message if no more cards
                                        if (remaining === 0) {
                                            document.getElementById('customerCards').innerHTML = `
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Tidak ada customer dalam on progress installation
                                            </div>
                                        </div>
                                    `;
                                        }
                                    }, 300);

                                    Swal.fire('Berhasil!', data.message, 'success');
                                } else {
                                    Swal.fire('Gagal!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error!', 'Terjadi kesalahan saat membatalkan instalasi', 'error');
                            });
                    }
                });
            });
        });

        // Activate button click
        document.querySelectorAll('.btn-activate').forEach(button => {
            button.addEventListener('click', function() {
                const customerId = this.dataset.id;
                const customerName = this.dataset.name;

                Swal.fire({
                    title: 'Aktifkan Customer?',
                    html: `Customer: <strong>${customerName}</strong><br><small>Instalasi akan selesai dan customer menjadi aktif</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Aktifkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const card = this.closest('.customer-card');

                        fetch(`<?= base_url('installation/activate') ?>/${customerId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                                },
                                body: JSON.stringify({
                                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove card with animation
                                    card.style.transition = 'opacity 0.3s';
                                    card.style.opacity = '0';
                                    setTimeout(() => {
                                        card.remove();
                                        // Update count
                                        const remaining = document.querySelectorAll('.customer-card').length;
                                        entriesCount.textContent = remaining;

                                        // Show message if no more cards
                                        if (remaining === 0) {
                                            document.getElementById('customerCards').innerHTML = `
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Tidak ada customer dalam on progress installation
                                            </div>
                                        </div>
                                    `;
                                        }
                                    }, 300);

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Diaktifkan!',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    Swal.fire('Gagal!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error!', 'Terjadi kesalahan saat mengaktifkan customer', 'error');
                            });
                    }
                });
            });
        });

        // Load dropdown data when page loads
        function loadDropdownData() {
            // Load ALL ODPs from database to display on map
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('start', 0);
            formData.append('length', 1000);
            formData.append('search[value]', '');
            formData.append('order[0][column]', 1);
            formData.append('order[0][dir]', 'desc');
            formData.append('draw', 1);

            fetch('<?= base_url('master/odp/data') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(result => {
                    console.log('ODP API Response:', result);
                    if (result.data && Array.isArray(result.data)) {
                        console.log('Loaded', result.data.length, 'ODPs for map');
                        console.log('Sample ODP data (FULL):', JSON.stringify(result.data[0], null, 2));
                        console.log('Keys in first ODP:', Object.keys(result.data[0]));
                        currentOdpData = result.data;

                        // Populate ODP dropdown
                        const odpSelect = document.getElementById('activateODP');
                        if (odpSelect) {
                            odpSelect.innerHTML = '<option value="">Pilih ODP</option>';
                            result.data.forEach(odp => {
                                odpSelect.innerHTML += `<option value="${odp.id}" data-area-id="${odp.area_id}" data-area-name="${odp.area_name || ''}" data-lat="${odp.latitude}" data-lng="${odp.longitude}">${odp.odp_name}</option>`;
                            });
                            console.log('ODP dropdown populated with', result.data.length, 'options');

                            // Setup ODP dropdown change event AFTER dropdown is populated
                            odpSelect.addEventListener('change', function() {
                                console.log('=== ODP Dropdown Changed ===');
                                const selectedOption = this.options[this.selectedIndex];
                                console.log('Selected value:', this.value);

                                if (this.value) {
                                    // Set area from selected ODP
                                    const areaId = selectedOption.getAttribute('data-area-id');
                                    const areaName = selectedOption.getAttribute('data-area-name');
                                    const odpLat = parseFloat(selectedOption.getAttribute('data-lat'));
                                    const odpLng = parseFloat(selectedOption.getAttribute('data-lng'));

                                    console.log('Area ID:', areaId, 'Area Name:', areaName);
                                    console.log('ODP Coordinates:', odpLat, odpLng);
                                    console.log('Customer marker exists?', !!customerLocationMarker);

                                    document.getElementById('activateArea').value = areaId;
                                    document.getElementById('activateAreaDisplay').value = areaName;

                                    // Draw dashed line if customer location already set
                                    if (customerLocationMarker && activateMap) {
                                        console.log('✅ Drawing line from ODP to customer...');

                                        // Remove previous connection line
                                        if (connectionLine) {
                                            console.log('Removing previous line');
                                            activateMap.removeLayer(connectionLine);
                                        }

                                        const customerLatLng = customerLocationMarker.getLatLng();
                                        console.log('Customer position:', customerLatLng);
                                        console.log('ODP position:', odpLat, odpLng);

                                        if (odpLat && odpLng && !isNaN(odpLat) && !isNaN(odpLng)) {
                                            connectionLine = L.polyline(
                                                [
                                                    [odpLat, odpLng],
                                                    [customerLatLng.lat, customerLatLng.lng]
                                                ], {
                                                    color: '#FF0000',
                                                    weight: 3,
                                                    dashArray: '10, 10',
                                                    opacity: 0.8
                                                }
                                            ).addTo(activateMap);

                                            console.log('✅ Line created and added to map!');
                                        } else {
                                            console.error('❌ Invalid ODP coordinates:', odpLat, odpLng);
                                        }
                                    } else {
                                        console.warn('⚠️ Customer location not set yet! Click map first.');
                                    }
                                } else {
                                    // Clear area if no ODP selected
                                    document.getElementById('activateArea').value = '';
                                    document.getElementById('activateAreaDisplay').value = '';

                                    // Remove connection line
                                    if (connectionLine && activateMap) {
                                        activateMap.removeLayer(connectionLine);
                                        connectionLine = null;
                                    }
                                }
                            });
                        }

                        // Display markers if map is already initialized
                        if (activateMap) {
                            console.log('Map is ready, calling displayOdpMarkers()');
                            displayOdpMarkers();
                        } else {
                            console.log('Map not initialized yet, markers will be displayed when modal opens');
                        }
                    } else {
                        console.error('Invalid ODP data format:', result);
                    }
                })
                .catch(error => console.error('Error loading ODPs:', error));

            // Load Routers
            fetch('<?= base_url('customer/branchOptions') ?>')
                .then(response => response.json())
                .then(data => {
                    const routerSelect = document.getElementById('activateRouter');
                    if (routerSelect && Array.isArray(data)) {
                        routerSelect.innerHTML = '<option value="">Pilih Router</option>';
                        data.forEach(router => {
                            routerSelect.innerHTML += `<option value="${router.id}">${router.name}</option>`;
                        });
                    }
                })
                .catch(error => console.error('Error loading routers:', error));

            // Load Payment Methods
            const paymentSelect = document.getElementById('activatePaymentMethod');
            if (paymentSelect) {
                paymentSelect.innerHTML = `
                    <option value="">Pilih Metode Pembayaran</option>
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                `;
            }

            // Load Pemegang IKR
            const pemegangSelect = document.getElementById('activatePemegangIKR');
            if (pemegangSelect) {
                pemegangSelect.innerHTML = `
                    <option value="">Pilih Pemegang IKR</option>
                    <option value="admin">Admin</option>
                    <option value="teknisi">Teknisi</option>
                `;
            }
        }

        // Variable untuk menyimpan customer location marker dan polyline
        let customerLocationMarker = null;
        let connectionLine = null;

        // Initialize Leaflet Map
        let activateMap = null;
        let odpMarkers = [];
        let selectedMarker = null;
        let currentOdpData = []; // Store current ODP data

        // Function to display ODP markers on map
        function displayOdpMarkers() {
            console.log('=== displayOdpMarkers() called ===');
            if (!activateMap) {
                console.log('❌ Map not initialized yet');
                return;
            }

            if (currentOdpData.length === 0) {
                console.log('❌ No ODP data available');
                return;
            }

            console.log('✅ Displaying', currentOdpData.length, 'ODP markers');
            console.log('Current ODP data:', currentOdpData);

            // Clear previous markers
            odpMarkers.forEach(marker => activateMap.removeLayer(marker));
            odpMarkers = [];

            // Clear selected marker
            if (selectedMarker) {
                activateMap.removeLayer(selectedMarker);
                selectedMarker = null;
            }

            // Add markers for each ODP
            currentOdpData.forEach(odp => {
                console.log('Processing ODP:', odp.odp_name, 'Lat:', odp.latitude, 'Lng:', odp.longitude);

                // Skip ODP with invalid coordinates (0,0 or null)
                if (!odp.latitude || !odp.longitude ||
                    parseFloat(odp.latitude) === 0 || parseFloat(odp.longitude) === 0) {
                    console.warn('⚠️ SKIP - ODP has no valid coordinates:', odp.odp_name);
                    return;
                }

                const lat = parseFloat(odp.latitude);
                const lng = parseFloat(odp.longitude);

                console.log('Adding marker at:', lat, lng);

                // Create custom RED icon for ODP
                const odpIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                const marker = L.marker([lat, lng], {
                        icon: odpIcon
                    })
                    .addTo(activateMap)
                    .bindPopup(`<b>${odp.odp_name}</b><br>Click to select`);

                // Add click event to marker
                marker.on('click', function() {
                    // Set dropdown value
                    document.getElementById('activateODP').value = odp.id;

                    // Trigger change event untuk update Area
                    const odpSelect = document.getElementById('activateODP');
                    if (odpSelect) {
                        odpSelect.dispatchEvent(new Event('change'));
                    }
                });

                odpMarkers.push(marker);
            });

            // Show warning if no valid markers
            if (odpMarkers.length === 0) {
                console.error('❌ NO ODP MARKERS DISPLAYED - All ODPs have invalid coordinates (0,0 or NULL)');
                console.error('⚠️ Please edit ODPs in Master > ODP and set their map coordinates!');
                alert('⚠️ Tidak ada ODP dengan koordinat valid!\n\nSilakan isi koordinat ODP di menu Master > ODP terlebih dahulu dengan klik map untuk set lokasi.');
            }

            // Fit map bounds to show all markers
            if (odpMarkers.length > 0) {
                const group = L.featureGroup(odpMarkers);
                activateMap.fitBounds(group.getBounds().pad(0.1));
            }
        }

        // Add another event listener for map initialization when modal is shown
        if (activateModalElement) {
            activateModalElement.addEventListener('shown.bs.modal', function() {
                setTimeout(function() {
                    if (!activateMap) {
                        activateMap = L.map('activateMap').setView([-6.2088, 106.8456], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(activateMap);
                        console.log('Map initialized');

                        // Click map untuk set lokasi customer (BLUE marker) - KLIK MAP DULU!
                        activateMap.on('click', function(e) {
                            // Remove previous customer marker
                            if (customerLocationMarker) {
                                activateMap.removeLayer(customerLocationMarker);
                            }

                            // Add BLUE marker untuk lokasi customer
                            const customerIcon = L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowSize: [41, 41]
                            });

                            customerLocationMarker = L.marker(e.latlng, {
                                    icon: customerIcon
                                })
                                .addTo(activateMap)
                                .bindPopup('📍 Lokasi Customer')
                                .openPopup();

                            console.log('Customer location set at:', e.latlng);
                        });
                    } else {
                        activateMap.invalidateSize();
                    }

                    // Display ODP markers
                    displayOdpMarkers();

                    // Auto-load ODP data on page load
                    loadDropdownData();
                }, 300);
            });
        }
    });
</script>
<?= $this->endSection() ?>