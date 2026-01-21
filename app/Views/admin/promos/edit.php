<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>Edit Promo<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Promo</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('/admin/promos') ?>">Promo</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Edit Promo</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('/admin/promos/update/' . $promo['id']) ?>" method="POST">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Promo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                                    id="title" name="title" value="<?= old('title', $promo['title']) ?>" required>
                                <?php if (session('errors.title')): ?>
                                    <div class="invalid-feedback"><?= session('errors.title') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="2"><?= old('description', $promo['description']) ?></textarea>
                                <small class="text-muted">Deskripsi singkat promo (opsional)</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="badge_text" class="form-label">Badge Text <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= session('errors.badge_text') ? 'is-invalid' : '' ?>"
                                            id="badge_text" name="badge_text" value="<?= old('badge_text', $promo['badge_text']) ?>"
                                            placeholder="Rp 100K / 24/7 / 100%" required>
                                        <?php if (session('errors.badge_text')): ?>
                                            <div class="invalid-feedback"><?= session('errors.badge_text') ?></div>
                                        <?php endif; ?>
                                        <small class="text-muted">Text besar yang tampil di card (max 50 karakter)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Urutan Tampil</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order"
                                            value="<?= old('display_order', $promo['display_order']) ?>" min="0">
                                        <small class="text-muted">Semakin kecil, semakin awal tampil</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="button_text" class="form-label">Text Tombol <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= session('errors.button_text') ? 'is-invalid' : '' ?>"
                                            id="button_text" name="button_text" value="<?= old('button_text', $promo['button_text']) ?>" required>
                                        <?php if (session('errors.button_text')): ?>
                                            <div class="invalid-feedback"><?= session('errors.button_text') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="button_action" class="form-label">Action Tombol <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?= session('errors.button_action') ? 'is-invalid' : '' ?>"
                                            id="button_action" name="button_action" value="<?= old('button_action', $promo['button_action']) ?>"
                                            placeholder="showPaymentOptions() atau <?= site_url('customer-portal/profile') ?>" required>
                                        <?php if (session('errors.button_action')): ?>
                                            <div class="invalid-feedback"><?= session('errors.button_action') ?></div>
                                        <?php endif; ?>
                                        <small class="text-muted">URL atau nama function javascript</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gradient_start" class="form-label">Warna Gradient Awal</label>
                                        <input type="color" class="form-control form-control-color"
                                            id="gradient_start" name="gradient_start" value="<?= old('gradient_start', $promo['gradient_start']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gradient_end" class="form-label">Warna Gradient Akhir</label>
                                        <input type="color" class="form-control form-control-color"
                                            id="gradient_end" name="gradient_end" value="<?= old('gradient_end', $promo['gradient_end']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                                            value="<?= old('start_date', $promo['start_date'] ? date('Y-m-d\TH:i', strtotime($promo['start_date'])) : '') ?>">
                                        <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">Tanggal Berakhir</label>
                                        <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                                            value="<?= old('end_date', $promo['end_date'] ? date('Y-m-d\TH:i', strtotime($promo['end_date'])) : '') ?>">
                                        <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                        <?= old('is_active', $promo['is_active']) == '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Aktifkan Promo</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update
                                </button>
                                <a href="<?= base_url('/admin/promos') ?>" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Preview Promo Card</h5>
                    </div>
                    <div class="card-body">
                        <div id="promo-preview" class="promo-card" style="min-width: 140px; border-radius: 16px; padding: 20px 15px; color: white; text-align: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                            <div class="promo-amount" id="preview-badge" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;"><?= esc($promo['badge_text']) ?></div>
                            <h6 id="preview-title" style="font-size: 13px; font-weight: 600; margin: 0 0 4px 0;"><?= esc($promo['title']) ?></h6>
                            <p id="preview-description" style="font-size: 11px; opacity: 0.9; margin: 0 0 12px 0;"><?= esc($promo['description']) ?></p>
                            <button class="btn-promo" id="preview-button" style="background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.5); color: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600;"><?= esc($promo['button_text']) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Live preview
    document.getElementById('title').addEventListener('input', function() {
        document.getElementById('preview-title').textContent = this.value || 'Judul Promo';
    });

    document.getElementById('description').addEventListener('input', function() {
        document.getElementById('preview-description').textContent = this.value || 'Deskripsi promo';
    });

    document.getElementById('badge_text').addEventListener('input', function() {
        document.getElementById('preview-badge').textContent = this.value || '24/7';
    });

    document.getElementById('button_text').addEventListener('input', function() {
        document.getElementById('preview-button').textContent = this.value || 'Lihat Detail';
    });

    function updateGradient() {
        const start = document.getElementById('gradient_start').value;
        const end = document.getElementById('gradient_end').value;
        document.getElementById('promo-preview').style.background = `linear-gradient(135deg, ${start} 0%, ${end} 100%)`;
    }

    document.getElementById('gradient_start').addEventListener('input', updateGradient);
    document.getElementById('gradient_end').addEventListener('input', updateGradient);

    // Set initial gradient
    updateGradient();
</script>

<?= $this->endSection() ?>