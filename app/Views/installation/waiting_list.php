<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Waitinglist Installation &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Waitinglist Installation</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Waitinglist Installation</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, package, branch...">
                                    <button class="btn btn-primary" type="button" id="searchBtn">
                                        <i class="bx bx-search-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="text-muted">Showing <span id="showingStart">1</span> to <span id="showingEnd">0</span> of <span id="totalEntries">0</span> entries</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="row" id="waitinglistCards">
            <?php if (empty($waitinglist)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bx bx-info-circle font-size-20 me-2"></i>
                        Tidak ada customer dalam waitinglist
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($waitinglist as $customer): ?>
                    <div class="col-lg-3 col-md-6 mb-4 customer-card"
                        data-name="<?= esc($customer['nama_pelanggan']) ?>"
                        data-package="<?= esc($customer['package_name']) ?>"
                        data-branch="<?= esc($customer['branch_name']) ?>">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3"><?= esc($customer['nama_pelanggan']) ?></h5>

                                <div class="mb-2">
                                    <strong>Paket:</strong> <?= esc($customer['package_name'] ?? '-') ?>
                                </div>

                                <div class="mb-2">
                                    <strong>Branch:</strong> <?= esc($customer['branch_name'] ?? 'Belum ditentukan') ?>
                                </div>

                                <?php if (!empty($customer['coordinat'])): ?>
                                    <div class="mb-3">
                                        <strong>Lokasi:</strong>
                                        <a href="https://www.google.com/maps?q=<?= esc($customer['coordinat']) ?>"
                                            target="_blank" class="text-primary">
                                            Open in maps
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <strong>HP:</strong> <?= esc($customer['telepphone'] ?? '-') ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-sm btn-primary btn-detail"
                                        data-id="<?= $customer['id_customers'] ?>">
                                        <i class="bx bx-info-circle me-1"></i> Detail
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info text-white btn-process"
                                        data-id="<?= $customer['id_customers'] ?>"
                                        data-name="<?= esc($customer['nama_pelanggan']) ?>">
                                        <i class="bx bx-check me-1"></i> Process
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-cancel"
                                        data-id="<?= $customer['id_customers'] ?>"
                                        data-name="<?= esc($customer['nama_pelanggan']) ?>">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Info Detail Installation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const cards = document.querySelectorAll('.customer-card');

        // Update statistics
        updateStatistics();

        // Search functionality
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const packageName = card.getAttribute('data-package').toLowerCase();
                const branch = card.getAttribute('data-branch').toLowerCase();

                if (name.includes(searchTerm) ||
                    packageName.includes(searchTerm) ||
                    branch.includes(searchTerm) ||
                    searchTerm === '') {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            updateStatistics(visibleCount);
        }

        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Update statistics
        function updateStatistics(visibleCount = null) {
            const total = cards.length;
            const showing = visibleCount !== null ? visibleCount : total;

            document.getElementById('showingStart').textContent = showing > 0 ? '1' : '0';
            document.getElementById('showingEnd').textContent = showing;
            document.getElementById('totalEntries').textContent = total;
        }

        // Detail button
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const customerId = this.getAttribute('data-id');
                const modal = new bootstrap.Modal(document.getElementById('detailModal'));

                fetch('<?= site_url('installation/get-customer-detail') ?>/' + customerId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const customer = data.data;
                            document.getElementById('detailContent').innerHTML = `
                            <div class="mb-3">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="text-muted small">Sales Name</label>
                                        <div class="fw-medium">Admin</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small">Customer Name</label>
                                        <div class="fw-medium">${customer.nama_pelanggan}</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="text-muted small">Customer Phone</label>
                                        <div class="fw-medium text-primary">${customer.telepphone || '-'}</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small">Branch</label>
                                        <div class="fw-medium">${customer.branch_name || '-'}</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="text-muted small">Area</label>
                                        <div class="fw-medium">${customer.area || '-'}</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small">ODP</label>
                                        <div class="fw-medium">${customer.odp || '-'}</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label class="text-muted small">Customer Address</label>
                                        <div class="fw-medium">${customer.address || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                        } else {
                            document.getElementById('detailContent').innerHTML = `
                            <div class="alert alert-danger">${data.message}</div>
                        `;
                        }
                    })
                    .catch(error => {
                        document.getElementById('detailContent').innerHTML = `
                        <div class="alert alert-danger">Error loading data</div>
                    `;
                    });

                modal.show();
            });
        });

        // Process button
        document.querySelectorAll('.btn-process').forEach(btn => {
            btn.addEventListener('click', function() {
                const customerId = this.getAttribute('data-id');
                const customerName = this.getAttribute('data-name');

                Swal.fire({
                    title: 'Proses Instalasi?',
                    text: `Customer: ${customerName}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const card = this.closest('.customer-card');

                        fetch('<?= site_url('installation/process') ?>/' + customerId, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
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
                                                        Tidak ada customer dalam waitinglist
                                                    </div>
                                                </div>
                                            `;
                                        }
                                    }, 300);

                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        html: data.message + '<br><br><small>Silakan lihat di menu <strong>On Progress Installation</strong></small>',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    Swal.fire('Gagal!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error!', 'Terjadi kesalahan saat memproses', 'error');
                            });
                    }
                });
            });
        });

        // Cancel button
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function() {
                const customerId = this.getAttribute('data-id');
                const customerName = this.getAttribute('data-name');

                Swal.fire({
                    title: 'Batalkan Waitinglist?',
                    text: `Customer: ${customerName}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const card = this.closest('.customer-card');

                        fetch('<?= site_url('installation/cancel') ?>/' + customerId, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
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
                                                        Tidak ada customer dalam waitinglist
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
                                Swal.fire('Error!', 'Terjadi kesalahan', 'error');
                            });
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>