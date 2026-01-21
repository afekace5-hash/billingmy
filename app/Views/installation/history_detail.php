<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="content" style="padding-top: 2rem; padding-bottom: 4rem;">
    <div class="container-fluid" style="padding-left: 1.5rem; padding-right: 1.5rem;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Installation Detail</h1>
            <a href="<?= base_url('installation/history') ?>" class="btn btn-secondary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                <i class='bx  bx-arrow-out-left-stroke-circle-half' style=" font-size:20px; padding-right:5px;"></i> Back
            </a>
        </div>

        <div class="card shadow mb-5">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Installation Detail</h6>
            </div>
            <div class="card-body" style="padding: 2rem 2rem 3rem 2rem !important;">
                <!-- Installation Info Section -->
                <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">Installation Information</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="180">Inet. Package</td>
                                <td>: <?= esc($customer['package_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Harga Paket</td>
                                <td>: Rp. <?= number_format($customer['harga'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Customer</td>
                                <td>: <?= esc($customer['nama'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sales</td>
                                <td>: <?= esc($customer['sales_name'] ?? 'Admin') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Teknisi</td>
                                <td>: Admin</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Area</td>
                                <td>: <?= esc($customer['area_name'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="180">ODP</td>
                                <td>: <?= !empty($customer['odp_name']) ? esc($customer['odp_name']) : (!empty($customer['odp_id']) ? 'ODP #' . $customer['odp_id'] : '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address</td>
                                <td>: <?= esc($customer['alamat'] ?? '-') ?>
                                    <?php if (!empty($customer['latitude']) && !empty($customer['longitude'])): ?>
                                        <br>
                                        <a href="https://www.google.com/maps?q=<?= $customer['latitude'] ?>,<?= $customer['longitude'] ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary mt-1 custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                            <i class='bx bx-map' style="font-size:16px; padding-right:5px;"></i> Maps
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Method</td>
                                <td>: <?= esc($customer['payment_method'] ?? 'Sistem') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Installation</td>
                                <td>: <span class="badge bg-success"><?= esc($customer['status_installation'] ?? 'Installed') ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Layanan</td>
                                <td>: <span class="badge bg-success"><?= esc($customer['status_layanan'] ?? 'Active') ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- PPOE Detail Section -->
                <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">PPPoE Account</h6>
                <table class="table table-sm table-borderless mb-4">
                    <tr>
                        <td class="text-muted" width="180">PPOE ID</td>
                        <td>: <?= esc($customer['pppoe_id'] ?? '*') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status PPPoE</td>
                        <td>:
                            <?php if (!empty($customer['pppoe_username'])): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not Created</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Name</td>
                        <td>: <?= esc($customer['nama'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Username</td>
                        <td>: <?= esc($customer['pppoe_username'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Password</td>
                        <td>: <?= esc($customer['pppoe_password'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Local Address</td>
                        <td>: <?= esc($customer['local_address'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Remote Address</td>
                        <td>: <?= esc($customer['remote_address'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Sync</td>
                        <td>: <?= !empty($customer['last_sync_pppoe']) ? date('d M Y H:i:s', strtotime($customer['last_sync_pppoe'])) : 'Never' ?></td>
                    </tr>
                </table>

                <?php if (empty($customer['pppoe_id']) || empty($customer['username_pppoe'])): ?>
                    <div class="text-center mb-4">
                        <button type="button" class="btn btn-primary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;" id="btnCreatePPPOe" data-customer-id="<?= $customer['id_customers'] ?>">
                            <i class='bx  bx-refresh-cw' style="font-size:20px; padding-right:5px;"></i> Create PPPOe
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Router & Connection Detail Section -->
                <h6 class="font-weight-bold text-primary mb-3 border-bottom pb-2">Router & Connection</h6>
                <table class="table table-sm table-borderless mb-5">
                    <tr>
                        <td class="text-muted" width="180">Router/Branch</td>
                        <td>: <?= esc($customer['branch_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Pasang</td>
                        <td>: <?= !empty($customer['tgl_pasang']) ? date('d M Y', strtotime($customer['tgl_pasang'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Aktivasi</td>
                        <td>: <?= !empty($customer['tgl_aktivasi']) ? date('d M Y', strtotime($customer['tgl_aktivasi'])) : (!empty($customer['tgl_pasang']) ? date('d M Y', strtotime($customer['tgl_pasang'])) : '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tipe Customer</td>
                        <td>: <?= esc($customer['tipe_customer'] ?? 'Retail') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Handle Create PPPOe button
    document.addEventListener('DOMContentLoaded', function() {
        const btnCreatePPPOe = document.getElementById('btnCreatePPPOe');

        if (btnCreatePPPOe) {
            btnCreatePPPOe.addEventListener('click', function() {
                const customerId = this.dataset.customerId;

                Swal.fire({
                    title: 'Create PPPOe Account?',
                    text: 'Ini akan membuat akun PPPOe untuk customer ini di router',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Create!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Creating PPPOe...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // TODO: Implement API call to create PPPOe
                        fetch(`<?= base_url('pppoe/create') ?>/${customerId}`, {
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
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: data.message || 'PPPOe account has been created',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Failed!', data.message || 'Failed to create PPPOe account', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'An error occurred while creating PPPOe account', 'error');
                            });
                    }
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>