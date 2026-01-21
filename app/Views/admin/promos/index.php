<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>Kelola Promo<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Kelola Promo & Penawaran</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Promo</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-all me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Promo List -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Daftar Promo</h5>
                        <a href="<?= base_url('/admin/promos/create') ?>" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Tambah Promo
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="20%">Judul</th>
                                        <th width="15%">Badge</th>
                                        <th width="20%">Tombol</th>
                                        <th width="10%">Urutan</th>
                                        <th width="10%">Periode</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($promos)): ?>
                                        <?php foreach ($promos as $index => $promo): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, <?= esc($promo['gradient_start']) ?> 0%, <?= esc($promo['gradient_end']) ?> 100%);"></div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0"><?= esc($promo['title']) ?></h6>
                                                            <?php if (!empty($promo['description'])): ?>
                                                                <small class="text-muted"><?= esc($promo['description']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-info"><?= esc($promo['badge_text']) ?></span>
                                                </td>
                                                <td>
                                                    <small><?= esc($promo['button_text']) ?></small><br>
                                                    <small class="text-muted"><?= esc($promo['button_action']) ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary"><?= $promo['display_order'] ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($promo['start_date'] || $promo['end_date']): ?>
                                                        <small>
                                                            <?= $promo['start_date'] ? date('d/m/Y', strtotime($promo['start_date'])) : '-' ?>
                                                            <br>s/d<br>
                                                            <?= $promo['end_date'] ? date('d/m/Y', strtotime($promo['end_date'])) : '-' ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Tanpa batas</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="switch-<?= $promo['id'] ?>"
                                                            <?= $promo['is_active'] == 1 ? 'checked' : '' ?>
                                                            onchange="toggleActive(<?= $promo['id'] ?>)">
                                                        <label class="form-check-label" for="switch-<?= $promo['id'] ?>">
                                                            <?= $promo['is_active'] == 1 ? 'Aktif' : 'Non-aktif' ?>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="<?= base_url('/admin/promos/edit/' . $promo['id']) ?>"
                                                            class="btn btn-sm btn-soft-info" title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <button onclick="deletePromo(<?= $promo['id'] ?>)"
                                                            class="btn btn-sm btn-soft-danger" title="Hapus">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="bx bx-info-circle font-size-24 d-block mb-2"></i>
                                                Belum ada promo. <a href="<?= base_url('/admin/promos/create') ?>">Tambah promo pertama</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleActive(id) {
        fetch(`<?= base_url('/admin/promos/toggle-active') ?>/${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                    location.reload();
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
    }

    function deletePromo(id) {
        Swal.fire({
            title: 'Hapus Promo?',
            text: 'Promo yang dihapus tidak dapat dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?= base_url('/admin/promos/delete') ?>/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
            }
        });
    }
</script>

<?= $this->endSection() ?>