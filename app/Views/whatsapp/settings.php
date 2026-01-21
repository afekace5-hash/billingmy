<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>Pengaturan Notifikasi &mdash; WhatsApp Management</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Pengaturan Notifikasi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('whatsapp/account') ?>">WhatsApp</a></li>
                            <li class="breadcrumb-item active">Pengaturan Notifikasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('whatsapp/settings/save') ?>" method="POST">
                            <?= csrf_field() ?>

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label text-dark fw-medium">
                                            Kirim notifikasi tagihan (H-7, H-3, H-1, H jatuh tempo) ?
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="notif_invoice" id="notif_invoice" value="1" <?= (isset($settings['notif_invoice']) && $settings['notif_invoice']) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-muted" for="notif_invoice">
                                                tidak
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label text-dark fw-medium">
                                            Kirim notifikasi konfirmasi pembayaran ?
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="notif_payment" id="notif_payment" value="1" <?= (isset($settings['notif_payment']) && $settings['notif_payment']) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-muted" for="notif_payment">
                                                tidak
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label text-dark fw-medium">
                                            Kirim notifikasi reminder ke pelanggan ?
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="notif_reminder" id="notif_reminder" value="1" <?= (isset($settings['notif_reminder']) && $settings['notif_reminder']) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-muted" for="notif_reminder">
                                                tidak
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label text-dark fw-medium">
                                            Kirim notifikasi lainnya (isolir, pelanggan baru, dll) ?
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="notif_other" id="notif_other" value="1" <?= (isset($settings['notif_other']) && $settings['notif_other']) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-muted" for="notif_other">
                                                tidak
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save"></i> Simpan
                                    </button>
                                    <a href="<?= site_url('whatsapp/account') ?>" class="btn btn-secondary px-4">
                                        <i class="bx bx-x"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Update label text when checkbox is toggled
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.textContent = this.checked ? 'ya' : 'tidak';
        });

        // Set initial label text
        const label = checkbox.nextElementSibling;
        label.textContent = checkbox.checked ? 'ya' : 'tidak';
    });
</script>
<?= $this->endSection() ?>