<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Laporan &mdash; SDN Krengseng 02</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Laporan</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Laporan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-bar-chart-alt-2 display-1 text-primary mb-3"></i>
                        <h4>Laporan Keuangan</h4>
                        <p class="text-muted">Semua laporan keuangan dapat diakses melalui menu:</p>
                        <div class="mt-4">
                            <a href="<?= site_url('transaction/transaction') ?>" class="btn btn-primary me-2">
                                <i class="bx bx-transfer me-2"></i>Transaction
                            </a>
                            <a href="<?= site_url('invoices') ?>" class="btn btn-success">
                                <i class="bx bx-file me-2"></i>Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row d-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Laporan</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h5 class="text-primary">Rp 0</h5>
                                        <p class="text-muted mb-0">Total Pemasukan Hari Ini</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h5 class="text-danger">Rp 0</h5>
                                        <p class="text-muted mb-0">Total Pengeluaran Hari Ini</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h5 class="text-success">0</h5>
                                        <p class="text-muted mb-0">Pembayaran Online Hari Ini</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h5 class="text-info">Rp 0</h5>
                                        <p class="text-muted mb-0">Saldo Akhir</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>