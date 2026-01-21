<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">History Installation</h1>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-0">Showing <span id="entriesCount"><?= count($history) ?></span> of <?= count($history) ?> entries</p>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row" id="customerCards">
                    <?php if (empty($history)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Tidak ada history installation
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($history as $customer): ?>
                            <div class="col-md-6 col-lg-3 mb-4 customer-card"
                                data-name="<?= strtolower(esc($customer['nama'] ?? $customer['nama_pelanggan'] ?? '')) ?>"
                                data-package="<?= strtolower(esc($customer['package_name'] ?? '')) ?>"
                                data-branch="<?= strtolower(esc($customer['branch_name'] ?? '')) ?>">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary mb-3">
                                            <?= esc($customer['nama'] ?? $customer['nama_pelanggan'] ?? '-') ?>
                                        </h5>

                                        <table class="table table-sm table-borderless mb-3">
                                            <tr>
                                                <td class="text-muted" width="60">Paket</td>
                                                <td><strong><?= esc($customer['package_name'] ?? '-') ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Branch</td>
                                                <td><?= esc($customer['branch_name'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Lokasi</td>
                                                <td>
                                                    <?php if (!empty($customer['latitude']) && !empty($customer['longitude'])): ?>
                                                        <a href="https://www.google.com/maps?q=<?= $customer['latitude'] ?>,<?= $customer['longitude'] ?>"
                                                            target="_blank"
                                                            class="text-primary">
                                                            <i class="bi bi-geo-alt"></i> Open in maps
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Diinstall pada</td>
                                                <td><?= !empty($customer['tgl_aktivasi']) ? date('D, d M Y', strtotime($customer['tgl_aktivasi'])) : (!empty($customer['tgl_pasang']) ? date('D, d M Y', strtotime($customer['tgl_pasang'])) : '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">HP</td>
                                                <td><?= esc($customer['telepphone'] ?? '-') ?></td>
                                            </tr>
                                        </table>

                                        <a href="<?= base_url('installation/history/' . $customer['id_customers']) ?>"
                                            class="btn btn-primary w-100 custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                            <i class="bi bi-info-circle" style="font-size:20px; padding-right:5px;"></i> Info Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if (!empty($history) && count($history) > 8): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center" id="pagination">
                                    <!-- Pagination will be generated by JavaScript -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const customerCards = document.querySelectorAll('.customer-card');
            const entriesCount = document.getElementById('entriesCount');
            const cardsPerPage = 8;
            let currentPage = 1;
            let filteredCards = Array.from(customerCards);

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    filteredCards = [];

                    customerCards.forEach(card => {
                        const name = card.dataset.name;
                        const packageName = card.dataset.package;
                        const branch = card.dataset.branch;

                        if (name.includes(searchTerm) || packageName.includes(searchTerm) || branch.includes(searchTerm)) {
                            card.style.display = '';
                            filteredCards.push(card);
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    entriesCount.textContent = filteredCards.length;
                    currentPage = 1;
                    renderPagination();
                    showPage(currentPage);
                });
            }

            // Pagination functionality
            function showPage(page) {
                const start = (page - 1) * cardsPerPage;
                const end = start + cardsPerPage;

                filteredCards.forEach((card, index) => {
                    if (index >= start && index < end) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            function renderPagination() {
                const pagination = document.getElementById('pagination');
                if (!pagination || filteredCards.length <= cardsPerPage) {
                    if (pagination) pagination.innerHTML = '';
                    return;
                }

                const totalPages = Math.ceil(filteredCards.length / cardsPerPage);
                let paginationHTML = '';

                // Previous button
                paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">‹</a>
            </li>
        `;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
                }

                // Next button
                paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">›</a>
            </li>
        `;

                pagination.innerHTML = paginationHTML;

                // Add click handlers
                pagination.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.dataset.page);
                        if (page >= 1 && page <= totalPages) {
                            currentPage = page;
                            showPage(currentPage);
                            renderPagination();
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            }

            // Initialize pagination
            filteredCards = Array.from(customerCards);
            renderPagination();
            showPage(currentPage);
        });
    </script>

    <?= $this->endSection() ?>