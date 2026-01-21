<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?= $title ?></h4>
            </div>
            <div class="card-body">

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('billing/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomor_layanan" class="form-label">Nomor Layanan</label>
                                <input type="text" class="form-control" id="nomor_layanan" name="nomor_layanan"
                                    value="<?= old('nomor_layanan') ?>" required>
                                <div class="form-text">Masukkan nomor layanan customer</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Jumlah Tagihan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="amount" name="amount"
                                        value="<?= old('amount') ?>" min="1" step="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Tagihan</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= old('description') ?></textarea>
                        <div class="form-text">Contoh: Tagihan Internet Bulan Januari 2025</div>
                    </div>

                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Tanggal Kadaluarsa</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                            value="<?= old('expires_at', date('Y-m-d\TH:i', strtotime('+24 hours'))) ?>">
                        <div class="form-text">Kosongkan untuk menggunakan default 24 jam</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Buat Billing Link
                        </button>
                        <a href="<?= base_url('billing') ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    // Auto-suggest customers based on nomor_layanan
    document.getElementById('nomor_layanan').addEventListener('input', function() {
        // This could be enhanced with AJAX to search customers
        // For now, just basic validation
    });
</script>

<?= $this->endSection() ?>