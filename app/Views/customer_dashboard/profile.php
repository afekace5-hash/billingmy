<?= $this->extend('customer_dashboard/admin_layout') ?>

<?= $this->section('title') ?>Profile Saya<?= $this->endSection() ?>

<?= $this->section('page-title') ?>Profile Saya<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Profile Saya</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('customer-portal/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Header Card -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-2 col-md-3">
                                <div class="text-center">
                                    <img src="<?= getUserAvatar() ?>" alt="" class="avatar-xl rounded-circle img-thumbnail">
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6">
                                <div class="mt-3 mt-md-0">
                                    <h4 class="mb-2"><?= esc($customer['nama_pelanggan']) ?></h4>
                                    <p class="text-muted mb-3">
                                        <i class="mdi mdi-account-box me-1"></i><?= esc($customer['nomor_layanan']) ?>
                                        <span class="mx-2">|</span>
                                        <i class="mdi mdi-phone me-1"></i><?= esc($customer['telepphone']) ?>
                                    </p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge badge-soft-<?= $customer['isolir_status'] == 1 ? 'danger' : 'success' ?> font-size-12 px-3 py-2">
                                            <i class="mdi mdi-<?= $customer['isolir_status'] == 1 ? 'wifi-off' : 'wifi' ?> me-1"></i>
                                            <?= $customer['isolir_status'] == 1 ? 'Layanan Diisolir' : 'Layanan Aktif' ?>
                                        </span>
                                        <span class="badge badge-soft-info font-size-12 px-3 py-2">
                                            <i class="mdi mdi-calendar me-1"></i>Bergabung sejak <?= date('M Y', strtotime($customer['tgl_pasang'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="mt-3 mt-md-0 d-grid gap-2">
                                    <a href="<?= site_url('customer-portal/invoices') ?>" class="btn btn-primary waves-effect waves-light">
                                        <i class="bx bx-receipt me-1"></i> Lihat Tagihan
                                    </a>
                                    <button onclick="contactWhatsApp()" class="btn btn-success waves-effect waves-light">
                                        <i class="bx bxl-whatsapp me-1"></i> Hubungi CS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-soft-primary text-primary rounded fs-3">
                                                <i class="mdi mdi-file-document-multiple"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Tagihan Aktif</p>
                                        <h4 class="mb-0">
                                            <?php
                                            $unpaidCount = 0;
                                            if (!empty($recent_invoices)) {
                                                foreach ($recent_invoices as $inv) {
                                                    if ($inv['status'] != 'paid') $unpaidCount++;
                                                }
                                            }
                                            echo $unpaidCount;
                                            ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-soft-success text-success rounded fs-3">
                                                <i class="mdi mdi-wifi"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Paket Internet</p>
                                        <h4 class="mb-0 fs-16"><?= esc($customer['package_name'] ?: '-') ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-soft-warning text-warning rounded fs-3">
                                                <i class="mdi mdi-calendar-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Tgl Tempo</p>
                                        <h4 class="mb-0 fs-16"><?= date('d M Y', strtotime($customer['tgl_tempo'])) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Customer Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Informasi Pelanggan</h4>
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" width="200">Nama Lengkap :</th>
                                        <td><?= esc($customer['nama_pelanggan']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Nomor Layanan :</th>
                                        <td><?= esc($customer['nomor_layanan']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Nomor Telepon :</th>
                                        <td><?= esc($customer['telepphone']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email :</th>
                                        <td><?= esc($customer['email'] ?: '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Alamat :</th>
                                        <td><?= esc($customer['address'] ?: $customer['cluster_address']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Tanggal Pasang :</th>
                                        <td><?= date('d F Y', strtotime($customer['tgl_pasang'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Tanggal Tempo :</th>
                                        <td>
                                            <?= date('d F Y', strtotime($customer['tgl_tempo'])) ?>
                                            <?php if ($customer['status_tagihan'] == 'Belum Lunas'): ?>
                                                <span class="badge badge-soft-danger ms-2">Belum Lunas</span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-success ms-2">Lunas</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Package Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Paket Internet</h4>
                        <div class="text-center">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-soft-primary text-primary rounded-circle font-size-24">
                                    <i class="bx bx-wifi"></i>
                                </div>
                            </div>
                            <h5 class="font-size-16 mb-1"><?= esc($customer['package_name'] ?: 'Tidak ada paket') ?></h5>
                            <p class="text-muted">
                                <span class="fw-semibold font-size-18">Rp <?= number_format((float)($customer['package_price'] ?: 0), 0, ',', '.') ?></span>/bulan
                            </p>

                            <?php if ($customer['isolir_status'] == 1): ?>
                                <span class="badge badge-soft-danger font-size-12 px-3 py-2">
                                    <i class="bx bx-wifi-off me-1"></i>Layanan Diisolir
                                </span>
                                <div class="alert alert-danger mt-3 mb-0" role="alert">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    Silakan lakukan pembayaran untuk mengaktifkan kembali layanan.
                                </div>
                            <?php else: ?>
                                <span class="badge badge-soft-success font-size-12 px-3 py-2">
                                    <i class="bx bx-wifi me-1"></i>Layanan Aktif
                                </span>
                                <p class="text-muted mt-3 mb-0">
                                    <i class="mdi mdi-check-circle-outline text-success me-1"></i>
                                    Koneksi internet Anda berjalan normal
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Connection Status -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Status Koneksi</h4>
                        <div class="text-center" id="connection-status">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted mb-0">Mengecek koneksi...</p>
                        </div>
                        <div class="text-center mt-3">
                            <button onclick="checkConnection()" class="btn btn-soft-primary waves-effect waves-light btn-sm">
                                <i class="bx bx-refresh me-1"></i>Refresh Status
                            </button>
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
    function checkConnection() {
        const statusDiv = document.getElementById('connection-status');
        statusDiv.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted mb-0">Mengecek koneksi...</p>
    `;

        // Simulate connection check
        setTimeout(() => {
            <?php if ($customer['isolir_status'] == 1): ?>
                statusDiv.innerHTML = `
                <div class="text-danger">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title bg-soft-danger text-danger rounded-circle font-size-36">
                            <i class="bx bx-wifi-off"></i>
                        </div>
                    </div>
                    <h6 class="text-danger mb-1">Tidak Terhubung</h6>
                    <p class="text-muted mb-0"><small>Layanan sedang diisolir</small></p>
                </div>
            `;
            <?php else: ?>
                statusDiv.innerHTML = `
                <div class="text-success">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title bg-soft-success text-success rounded-circle font-size-36">
                            <i class="bx bx-wifi"></i>
                        </div>
                    </div>
                    <h6 class="text-success mb-1">Terhubung</h6>
                    <p class="text-muted mb-0"><small>Layanan berjalan normal</small></p>
                </div>
            `;
            <?php endif; ?>
        }, 1500);
    }

    // Auto check connection on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkConnection();
    });
</script>
<?= $this->endSection() ?>