<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>Notifications &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Notifications</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notifications</li>
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
                        <h4 class="card-title mb-4">Notifications</h4>

                        <!-- Notifications Table -->
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Action</th>
                                        <th>Notification</th>
                                        <th style="width: 200px;">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($notifications) && !empty($notifications)): ?>
                                        <?php foreach ($notifications as $notification): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-soft-danger"
                                                        onclick="deleteNotification(<?= $notification['id'] ?>)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-soft-primary"
                                                        onclick="markAsRead(<?= $notification['id'] ?>)">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="<?= isset($notification['is_read']) && !$notification['is_read'] ? 'fw-bold' : '' ?>">
                                                        <?= esc($notification['message']) ?>
                                                    </div>
                                                    <?php if (isset($notification['description'])): ?>
                                                        <small class="text-muted"><?= esc($notification['description']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= isset($notification['created_at']) ? date('d M Y H:i', strtotime($notification['created_at'])) : '-' ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bx bx-bell-off font-size-24 d-block mb-2"></i>
                                                    No notifications
                                                </div>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function deleteNotification(id) {
        Swal.fire({
            title: 'Hapus Notifikasi?',
            text: 'Apakah Anda yakin ingin menghapus notifikasi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your delete logic here
                fetch('<?= base_url('notifications/delete/') ?>' + id, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'Notifikasi telah dihapus.', 'success');
                            location.reload();
                        } else {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Terjadi kesalahan saat menghapus.', 'error');
                    });
            }
        });
    }

    function markAsRead(id) {
        fetch('<?= base_url('notifications/mark-read/') ?>' + id, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>
<?= $this->endSection() ?>