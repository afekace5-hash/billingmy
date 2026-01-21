<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title"><?= $title ?></h4>
                <a href="<?= base_url('billing/create') ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Buat Billing Link
                </a>
            </div>
            <div class="card-body">

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Token</th>
                                <th>Nomor Layanan</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Gateway</th>
                                <th>Expires At</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($billingLinks)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada billing link</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($billingLinks as $link): ?>
                                    <tr>
                                        <td>
                                            <code><?= substr($link['token'], 0, 8) ?>...</code>
                                        </td>
                                        <td><?= esc($link['nomor_layanan']) ?></td>
                                        <td><?= esc($link['customer_name'] ?? '-') ?></td>
                                        <td>Rp <?= number_format($link['amount'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'expired' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statusClass[$link['status']] ?? 'secondary' ?>">
                                                <?= ucfirst($link['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($link['payment_gateway'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($link['expires_at']): ?>
                                                <?= date('d/m/Y H:i', strtotime($link['expires_at'])) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($link['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('pay/' . $link['token']) ?>"
                                                    class="btn btn-info" target="_blank" title="View Payment">
                                                    <i class="fa fa-external-link"></i>
                                                </a>
                                                <button type="button" class="btn btn-secondary"
                                                    onclick="copyToClipboard('<?= base_url('pay/' . $link['token']) ?>')"
                                                    title="Copy Link">
                                                    <i class="fa fa-copy"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger"
                                                    onclick="deleteBillingLink(<?= $link['id'] ?>)"
                                                    title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pager): ?>
                    <div class="d-flex justify-content-center">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus billing link ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="post" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Link berhasil disalin!');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    function deleteBillingLink(id) {
        document.getElementById('deleteForm').action = '<?= base_url('billing/delete/') ?>' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?= $this->endSection() ?>