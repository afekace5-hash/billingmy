<script>
    // Otomatis ganti angka 0 di depan menjadi 62 pada input telepon
    document.addEventListener('DOMContentLoaded', function() {
        var telepphoneInput = document.getElementById('telepphone');
        if (telepphoneInput) {
            telepphoneInput.addEventListener('input', function(e) {
                let val = telepphoneInput.value;
                // Jika dimulai dengan 0, ganti dengan 62
                if (val.length > 1 && val.startsWith('0')) {
                    telepphoneInput.value = '62' + val.substring(1);
                }
            });
        }
    });
</script>
<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Update Gawe &mdash; yukNikah</title>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
    /* Custom Styles for Create Customer Form */
    .card.border-0.shadow-sm {
        border-radius: 10px;
        overflow: hidden;
    }

    .card-header.bg-success {
        background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%) !important;
        padding: 1.25rem 1.5rem;
    }

    .card-header h4 {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .form-label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 1px solid #d1d9e6;
        border-radius: 5px;
        padding: 0.625rem 0.875rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #1abc9c;
        box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.15);
    }

    h5.text-primary {
        font-weight: 600;
        font-size: 1rem;
        color: #1abc9c !important;
        border-bottom: 2px solid #1abc9c;
        padding-bottom: 0.5rem;
    }

    .leaflet-map {
        border-radius: 8px;
        border: 2px solid #e0e0e0;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b7ddd 0%, #2c5fb3 100%);
        border: none;
        padding: 0.625rem 2rem;
        font-weight: 500;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 125, 221, 0.4);
    }

    .form-check-input:checked {
        background-color: #1abc9c;
        border-color: #1abc9c;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .position-absolute.btn {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 768px) {

        .col-lg-7,
        .col-lg-5 {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .pulse-animation {
        animation: pulse 1s ease-in-out infinite;
    }

    #secretButtonContainer {
        min-height: 40px;
    }

    #searchSecretBtn {
        transition: all 0.3s ease;
        display: none !important;
    }

    #searchSecretBtn.show-button {
        display: inline-block !important;
        visibility: visible !important;
    }

    #searchSecretBtn.d-inline-block {
        display: inline-block !important;
        visibility: visible !important;
    }

    #searchSecretBtn:not(.d-none) {
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Force visibility when needed */
    #searchSecretBtn[style*="display: inline-block"] {
        display: inline-block !important;
        visibility: visible !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Pelanggan</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Pelanggan</a></li>
                            <li class="breadcrumb-item active">Pelanggan</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-2 mb-4">
                <a href="<?= site_url('customers') ?>" class="btn btn-primary waves-effect waves-light">
                    <i class="bx bxs-left-arrow-circle font-size-16 align-middle me-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white border-0">
                        <h4 class="card-title mb-0 text-white">
                            <i class="bx bx-user-plus me-2"></i>Form New Customer
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="UserForm" enctype="multipart/form-data" method="POST" action="<?= site_url('customers') ?>">
                            <?= csrf_field() ?>

                            <!-- Layout 2 Kolom: Kiri Form, Kanan Peta -->
                            <div class="row">
                                <!-- Kolom Kiri: Form Data -->
                                <div class="col-lg-6">
                                    <!-- Data Customer Section -->
                                    <div class="">
                                        <h5 class="text-primary mb-3"><i class="bx bx-user me-2"></i>Data Customer</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Salesman</label>
                                                <select class="form-select" name="sales_id" id="sales_id">
                                                    <option value="">Admin</option>
                                                    <option value="20777">akanet</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                                <select class="form-select" name="branch_id" id="branch_id" required>
                                                    <option value="">Select Branch</option>
                                                </select>
                                                <div class="spinner-border spinner-border-sm text-primary mt-1" id="branch-load"
                                                    role="status" style="display: none;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Type & Kartu Identitas Section -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Customer Type</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="is_new_customer" id="customerTypeNew" value="1" checked>
                                                    <label class="form-check-label" for="customerTypeNew">
                                                        New
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="is_new_customer" id="customerTypeExisting" value="0">
                                                    <label class="form-check-label" for="customerTypeExisting">
                                                        Existing
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kartu Identitas (opsional)</label>
                                            <input type="file" class="form-control" name="photo" id="photo">
                                        </div>
                                    </div>

                                    <!-- Form for Existing Customer -->
                                    <div id="existingCustomerSection" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                                <select class="form-select" name="existing_customer_id" id="existing_customer_id">
                                                    <option value="">Pilih Customer</option>
                                                </select>
                                                <div class="spinner-border spinner-border-sm text-primary mt-1" id="customer-load"
                                                    role="status" style="display: none;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form for New Customer -->
                                    <div id="newCustomerSection" style="display: block;">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="nama_pelanggan" id="nama_pelanggan" placeholder="Nama Lengkap">
                                            </div>
                                        </div>
                                        <!-- Personal Info -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email" id="email" placeholder="Email">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nomor HP <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="telepphone" id="telepphone" placeholder="Nomor Hp. cth: 082243440959">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" name="password_confirm" id="password_confirm" placeholder="Konfirmasi Password">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Paket Installation Section -->
                                    <div class="mb-4 mt-4">
                                        <h5 class="text-primary mb-3"><i class="bx bx-package me-2"></i>Paket Installation</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Area Instalasi <span class="text-danger">*</span></label>
                                                <select class="form-select" name="area_id" id="area_id" required>
                                                    <option value="">Loading...</option>
                                                </select>
                                                <div class="spinner-border spinner-border-sm text-primary mt-1" id="cl-load"
                                                    role="status" style="display: none;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Paket Installation <span class="text-danger">*</span></label>
                                                <select class="form-select" name="id_paket" id="id_paket" required>
                                                    <option value="">Loading...</option>
                                                </select>
                                                <div class="spinner-border spinner-border-sm text-primary mt-1" id="paket-load"
                                                    role="status" style="display: none;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Installasi Baru <span class="text-danger">*</span></label>
                                                <select class="form-select" name="subscription_method" id="subscription_method" required>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-primary">Promo (opsional)</label>
                                                <select class="form-select" name="discount_id" id="discount_id">
                                                    <option value="0">Loading...</option>
                                                </select>
                                                <div class="spinner-border spinner-border-sm text-primary mt-1" id="discount-load"
                                                    role="status" style="display: none;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PPPoE Section (Hidden initially) -->
                                    <div class="mb-4 mt-4" id="pppoeSection" style="display: none;">
                                        <h5 class="text-primary mb-3"><i class="bx bx-network-chart me-2"></i>Data PPPoE</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">PPP Secret</label>
                                                <select class="form-select" name="ppp_secret" id="ppp_secret">
                                                    <option value="">Pilih PPP Secret</option>
                                                    <option value="tanpa_secret">Tanpa Secret</option>
                                                    <option value="ambil_dari_router">Ambil dari router</option>
                                                    <option value="buat_secret_baru">Buat secret baru</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Username PPPoE</label>
                                                <input type="text" class="form-control" name="pppoe_username" id="pppoe_username" placeholder="Username">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Password PPPoE</label>
                                                <input type="text" class="form-control" name="pppoe_password" id="pppoe_password" placeholder="Password">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan: Peta & Lokasi -->
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3"><i class="bx bx-map me-2"></i>Lokasi</h5>

                                        <!-- ODP Section -->
                                        <div class="mb-3">
                                            <label class="form-label">ODP (opsional)</label>
                                            <div class="card" style="height: 350px; position: relative;">
                                                <div id="map" class="leaflet-map" style="height: 100%; width: 100%;"></div>
                                                <button type="button" class="btn btn-primary btn-sm position-absolute"
                                                    style="top: 10px; right: 10px; z-index: 1000;" id="pilihODPBtn">
                                                    Pilih ODP
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm position-absolute"
                                                    style="top: 10px; right: 110px; z-index: 1000;" id="pilihTitikKoordinatBtn">
                                                    Pilih Titik Koordinat Instalasi
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Alamat Customer -->
                                        <div class="mb-3">
                                            <label class="form-label">Alamat Lengkap Customer <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="address" id="address" rows="4"
                                                placeholder="Alamat lengkap & patokan" required></textarea>
                                        </div>

                                        <!-- Koordinat (Hidden) -->
                                        <input type="hidden" name="coordinat" id="coordinat">

                                        <!-- Additional Hidden Fields -->
                                        <input type="hidden" name="status_tagihan" value="enable">
                                        <input type="hidden" name="login" value="enable">
                                        <input type="hidden" name="pppoe_service" value="pppoe">

                                        <!-- Hidden Biaya Fields (for single form submission) -->
                                        <input type="hidden" name="biaya_pasang" id="hidden_biaya_pasang">
                                        <input type="hidden" name="additional_fee_id" id="hidden_additional_fee_id">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Biaya Instalasi & Tambahan (Separate Visual Card) -->
        <div class="row ">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="bx bx-money me-2"></i>Biaya Instalasi & Tambahan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Biaya Instalasi (Rp)</label>
                                <input type="number" class="form-control" id="biaya_pasang_display" placeholder="Masukkan biaya instalasi" min="0" step="1000" onchange="syncBiaya()">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Biaya Tambahan</label>
                                <select class="form-select" id="additional_fee_id_display" onchange="syncBiaya()">
                                    <option value="">Pilih Biaya Tambahan (Opsional)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" id="submitAllData" onclick="submitCustomerData()">
                                    <i class="bx bx-check me-2"></i>Create
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals Section -->

    <div class="modal fade" id="addServerModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modelHeading">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addServerForm" name="addServerForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="name" class="col-form-label">Nama</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Nama">
                                    <input type="hidden" class="form-control" id="id" name="id">
                                    <span id="errorName" class="invalid-feedback text-danger" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="server_address" class="col-form-label">Alamat</label>
                                    <textarea class="form-control " name="server_address"
                                        id="server_address" rows="3"
                                        placeholder="Alamat"></textarea>
                                    <span id="errorAddress" class="invalid-feedback text-danger" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="due_date" class="col-form-label">Jatuh tempo</label>
                                    <input type="number" class="form-control" id="due_date" name="due_date"
                                        placeholder="Jatuh tempo">
                                    <span class="text-muted">ini adalah pengaturan utama untuk tanggal jatuh tempo</span>
                                    <span id="errorDueDate" class="invalid-feedback text-danger" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="mb-3">
                                    <label for="tax" class="col-form-label">Pajak</label>
                                    <div class="square-switch">
                                        <input type="checkbox" id="square-switch1" switch="none" name="tax" value="1"
                                            id="tax" />
                                        <label for="square-switch1" data-on-label="Ya"
                                            data-off-label="Tidak"></label>
                                    </div>
                                    <span id="errorTax" class="invalid-feedback text-danger" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-4" id="tax_hide">
                                <div class="mb-3">
                                    <label for="tax_amount" class="col-form-label">Jumlah Pajak</label>
                                    <div class="input-group" id="datepicker1">
                                        <input type="number" class="form-control "
                                            name="tax_amount" id="tax_amount" placeholder="Nilai pajak dalam persen"
                                            value="0">
                                        <span class="input-group-text">%</i></span>
                                    </div>
                                    <span id="errorTaxAMount" class="invalid-feedback text-danger" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveServer" class="btn btn-primary" value="create">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPackageModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modelHeading">Buat Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addPackageForm" name="addPackageForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name_package" class="col-form-label">Nama</label>
                            <input type="text" class="form-control" id="name_package" name="name_package"
                                placeholder="Nama">
                            <span id="errorNamePackage" class="invalid-feedback text-danger" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                        <div class="mb-3">
                            <label for="bandwidth" class="col-form-label">Bandwidth</label>
                            <input type="number" class="form-control" id="bandwidth" name="bandwidth"
                                placeholder="Bandwidth">
                            <span id="errorBandwidth" class="invalid-feedback text-danger" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="col-form-label">Harga</label>
                            <input type="string" class="form-control" id="price" name="price"
                                placeholder="Harga">
                            <span id="errorPrice" class="invalid-feedback text-danger" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="savePackage" class="btn btn-primary" value="create">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clusterModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Cluster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="clusterForm" name="clusterForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="cluster_name" class="col-form-label">Nama<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="cluster_name" name="cluster_name"
                                                placeholder="ODP SERVER 1 , BTS SERVER 1">
                                            <span id="error_cluster_name" class="invalid-feedback text-danger" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="cluster_number_of_ports" class="col-form-label">Jumlah Port<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="cluster_number_of_ports" name="cluster_number_of_ports" placeholder="1,2,3">
                                            <span class="text-muted">Opsional</span>
                                            <span id="error_cluster_number_of_ports" class="invalid-feedback text-danger" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-2">
                                        <label for="cluster_type" class="col-form-label">
                                            Jenis <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" name="cluster_type" id="cluster_type">
                                            <option value="ftth" selected>FTTH</option>
                                            <option value="wireless">WIRELESS</option>
                                        </select>
                                        <span id="error_cluster_type" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-2">
                                        <label for="cluster_server_location_id" class="col-form-label">
                                            Pilih Lokasi Server <span class="text-danger">*</span>
                                        </label> <select class="form-select" name="cluster_server_location_id" id="cluster_server_location_id">
                                            <option value="">Pilih Lokasi Server</option>
                                            <?php if (isset($lokasiServers) && !empty($lokasiServers)): ?>
                                                <?php foreach ($lokasiServers as $server): ?>
                                                    <option value="<?= $server['id_lokasi'] ?>"><?= esc($server['name']) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <span id="error_cluster_server_location_id" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-2">
                                        <label class="form-label">Koordinat Peta</label>
                                        <input class="form-control" type="text" name="cluster_coordinate" id="cluster_coordinate"
                                            placeholder="Koordinat Peta"
                                            value="">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="cluster_address" class="col-form-label">Alamat</label>
                                        <textarea class="form-control" name="cluster_address" id="cluster_address" rows="3" placeholder="Masukan Alamat"></textarea>
                                        <span id="error_cluster_address" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="clusterSaveBtn" class="btn btn-primary" value="create">Membuat</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Prorata -->
    <div class="modal fade" id="prorataConfirmModal" tabindex="-1" aria-labelledby="prorataConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="prorataConfirmModalLabel">
                        <i class="bx bx-calculator me-2"></i>
                        Konfirmasi Tagihan Prorata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Tagihan Prorata</strong> akan dihitung berdasarkan sisa hari dalam bulan ini mulai dari tanggal pemasangan.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bx bx-calendar me-2"></i> Detail Pemasangan</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Tanggal</strong></td>
                                            <td><span id="prorata-install-date"></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Periode</strong></td>
                                            <td><span id="prorata-period"></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Hari</strong></td>
                                            <td><span id="prorata-days"></span> dari <span id="prorata-total-days"></span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bx bx-money me-2"></i> Kalkulasi Biaya</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Harga Penuh</strong></td>
                                            <td><span id="prorata-full-price"></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Persentase</strong></td>
                                            <td><span id="prorata-percentage"></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tagihan</strong></td>
                                            <td><span id="prorata-amount" class="fw-bold fs-5"></span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bx bx-exclamation-triangle me-2"></i>
                        <strong>Catatan:</strong>
                        <ul class="mb-0">
                            <li>Tagihan prorata akan dibuat otomatis setelah customer disimpan</li>
                            <li>Perhitungan menggunakan pembulatan ke atas untuk memastikan tidak ada kerugian</li>
                            <li>Customer akan mendapat tagihan normal mulai bulan berikutnya</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-2"></i>
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmProrataBtn">
                        <i class="bx bx-check me-2"></i>
                        Ya, Buat Customer dengan Prorata
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- container-fluid -->
</div>
<!-- End Page-content -->
<!-- Leaflet Map Scripts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    // Default regional settings dari application settings
    const defaultRegional = <?= json_encode($defaultRegional ?? []) ?>;

    document.addEventListener('DOMContentLoaded', function() {

        // Aktifkan tooltip koordinat
        if (window.bootstrap && bootstrap.Tooltip) {
            var coordInput = document.getElementById('coordinat');
            if (coordInput) {
                new bootstrap.Tooltip(coordInput);
            }
        } else if (window.$ && $.fn.tooltip) {
            $('#coordinat').tooltip();
        }

        // Populate branch dropdown
        function loadBranches() {
            var $branchSelect = document.getElementById('branch_id');
            var $spinner = document.getElementById('branch-load');
            if ($spinner) $spinner.style.display = 'inline-block';

            fetch('<?= site_url('customer/branchOptions') ?>')
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function(response) {
                    if ($spinner) $spinner.style.display = 'none';
                    if ($branchSelect) {
                        $branchSelect.innerHTML = '<option value="">Select Branch</option>';

                        // Direct array response
                        var data = response;

                        console.log('Branch data:', data);

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(branch) {
                                var opt = document.createElement('option');
                                opt.value = branch.id_lokasi;
                                opt.textContent = branch.nama;
                                $branchSelect.appendChild(opt);
                            });

                            // Destroy and reinitialize Select2 if exists
                            if (window.$ && $.fn.select2) {
                                var $select = $('#branch_id');
                                if ($select.data('select2')) {
                                    $select.select2('destroy');
                                }
                                $select.select2({
                                    theme: 'bootstrap-5',
                                    width: '100%'
                                });
                            }
                        } else {
                            var opt = document.createElement('option');
                            opt.value = '';
                            opt.textContent = 'Tidak ada data branch';
                            $branchSelect.appendChild(opt);
                        }
                    }
                })
                .catch(function(err) {
                    if ($spinner) $spinner.style.display = 'none';
                    console.error('Gagal mengambil data branch:', err);
                    if ($branchSelect) {
                        $branchSelect.innerHTML = '<option value="">Error: ' + err.message + '</option>';
                    }
                });
        }
        loadBranches();

        // Populate Area Instalasi dropdown - using customer/areaOptions endpoint
        function loadAreas() {
            var $areaSelect = document.getElementById('area_id');
            var $spinner = document.getElementById('cl-load');

            console.log('loadAreas called, element found:', !!$areaSelect);
            if (!$areaSelect) {
                console.error('area_id element not found!');
                return;
            }

            if ($spinner) $spinner.style.display = 'inline-block';

            var url = '<?= site_url('customer/areaOptions') ?>';
            console.log('Fetching from URL:', url);

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    console.log('Response received:', {
                        status: response.status,
                        statusText: response.statusText,
                        headers: response.headers.get('content-type')
                    });
                    if (!response.ok) {
                        return response.text().then(function(text) {
                            console.error('Error response body:', text);
                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                        });
                    }
                    return response.text().then(function(text) {
                        console.log('Raw response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response');
                        }
                    });
                })
                .then(function(data) {
                    console.log('Parsed data:', data);
                    console.log('Data type:', typeof data);
                    console.log('Is array?:', Array.isArray(data));
                    console.log('Data length:', data ? data.length : 'N/A');

                    if ($spinner) $spinner.style.display = 'none';

                    $areaSelect.innerHTML = '<option value="">Pilih Area Instalasi</option>';

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(area) {
                            console.log('Processing area:', area);
                            var opt = document.createElement('option');
                            opt.value = area.id;
                            opt.textContent = area.name;
                            $areaSelect.appendChild(opt);
                        });
                        console.log('Successfully loaded ' + data.length + ' areas');

                        // Reinitialize Select2 after data is loaded
                        if (window.$ && $.fn.select2) {
                            var $select = $($areaSelect);
                            if ($select.data('select2')) {
                                $select.select2('destroy');
                            }
                            $select.select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: 'Pilih Area Instalasi'
                            });
                        }
                    } else {
                        var opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = 'Tidak ada data area';
                        $areaSelect.appendChild(opt);
                        console.warn('No area data found or invalid data format');
                    }
                })
                .catch(function(err) {
                    if ($spinner) $spinner.style.display = 'none';
                    console.error('Error loading areas:', err);
                    console.error('Error stack:', err.stack);
                    $areaSelect.innerHTML = '<option value="">Error: ' + err.message + '</option>';
                });
        }

        loadAreas();

        // Reload areas when branch changes (optional filter)
        var branchSelect = document.getElementById('branch_id');
        if (branchSelect) {
            branchSelect.addEventListener('change', function() {
                var branchId = this.value;
                if (branchId) {
                    // Load areas filtered by branch
                    fetch('<?= site_url('master/area/by-branch') ?>/' + branchId)
                        .then(function(response) {
                            if (!response.ok) throw new Error('HTTP ' + response.status);
                            return response.json();
                        })
                        .then(function(response) {
                            var $areaSelect = document.getElementById('area_id');
                            if ($areaSelect && response.success) {
                                $areaSelect.innerHTML = '<option value="">Pilih Area Instalasi</option>';
                                var areas = response.data || [];
                                areas.forEach(function(area) {
                                    var opt = document.createElement('option');
                                    opt.value = area.id;
                                    opt.textContent = area.area_name;
                                    $areaSelect.appendChild(opt);
                                });

                                // Reinitialize Select2 after data is loaded
                                if (window.$ && $.fn.select2) {
                                    var $select = $($areaSelect);
                                    if ($select.data('select2')) {
                                        $select.select2('destroy');
                                    }
                                    $select.select2({
                                        theme: 'bootstrap-5',
                                        width: '100%',
                                        placeholder: 'Pilih Area Instalasi'
                                    });
                                }
                            }
                        })
                        .catch(function(err) {
                            console.error('Error loading areas by branch:', err);
                        });
                } else {
                    loadAreas(); // Reload all areas
                }
            });
        }

        // Paket Installation will be loaded by loadPaketOptions() function (see below)
        // Removed duplicate loadPakets() function to avoid 404 error

        // Load Promo/Discount - Set default (discount feature may not be implemented)
        var $discountSelect = document.getElementById('discount_id');
        if ($discountSelect) {
            $discountSelect.innerHTML = '<option value="0">Tidak ada Promo</option>';
        }
        var $discountSpinner = document.getElementById('discount-load');
        if ($discountSpinner) {
            $discountSpinner.style.display = 'none';
        }

        // Koordinat default dari settings aplikasi atau fallback ke Batang
        const defaultCoords = defaultRegional.default_coordinat ?
            defaultRegional.default_coordinat.split(',').map(coord => parseFloat(coord.trim())) : [-6.9382, 109.7190]; // Fallback: Batang
        var map = L.map('map').setView(defaultCoords, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker;
        // If already has value, set marker
        var coordInput = document.getElementById('coordinat');
        if (coordInput && coordInput.value) {
            var parts = coordInput.value.split(',');
            if (parts.length === 2) {
                var lat = parseFloat(parts[0]);
                var lng = parseFloat(parts[1]);
                marker = L.marker([lat, lng]).addTo(map);
                map.setView([lat, lng], 16);
            }
        } else {
            // Add default marker at center position
            marker = L.marker(defaultCoords).addTo(map);
            // Set default coordinates to input
            coordInput.value = defaultCoords[0].toFixed(6) + ',' + defaultCoords[1].toFixed(6);
        }
        map.on('click', function(e) {
            var lat = e.latlng.lat.toFixed(6);
            var lng = e.latlng.lng.toFixed(6);
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
            if (coordInput) {
                coordInput.value = lat + ',' + lng;
            }
        });
    });
</script>

<!-- Additional Form Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Customer Type Toggle
        const customerTypeNew = document.getElementById('customerTypeNew');
        const customerTypeExisting = document.getElementById('customerTypeExisting');
        const newCustomerSection = document.getElementById('newCustomerSection');
        const existingCustomerSection = document.getElementById('existingCustomerSection');

        function toggleCustomerType() {
            if (customerTypeNew.checked) {
                newCustomerSection.style.display = 'block';
                existingCustomerSection.style.display = 'none';

                // Enable new customer fields
                document.getElementById('nama_pelanggan').required = true;
                document.getElementById('email').required = true;
                document.getElementById('telepphone').required = true;
                document.getElementById('password').required = true;
                document.getElementById('password_confirm').required = true;

                // Disable existing customer field
                document.getElementById('existing_customer_id').required = false;
            } else {
                newCustomerSection.style.display = 'none';
                existingCustomerSection.style.display = 'block';

                // Disable new customer fields
                document.getElementById('nama_pelanggan').required = false;
                document.getElementById('email').required = false;
                document.getElementById('telepphone').required = false;
                document.getElementById('password').required = false;
                document.getElementById('password_confirm').required = false;

                // Enable existing customer field
                document.getElementById('existing_customer_id').required = true;

                // Load existing customers
                loadExistingCustomers();
            }
        }

        // Load existing customers for dropdown
        function loadExistingCustomers() {
            const $customerSelect = document.getElementById('existing_customer_id');
            const $spinner = document.getElementById('customer-load');

            if ($spinner) $spinner.style.display = 'inline-block';

            fetch('<?= site_url('customer/getCustomerOptions') ?>')
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function(response) {
                    if ($spinner) $spinner.style.display = 'none';
                    if ($customerSelect) {
                        $customerSelect.innerHTML = '<option value="">Pilih Customer</option>';

                        // Check if response has data array
                        const customers = response.data || response;

                        if (Array.isArray(customers) && customers.length > 0) {
                            customers.forEach(function(customer) {
                                const opt = document.createElement('option');
                                opt.value = customer.id;
                                opt.textContent = customer.nama_pelanggan + ' [' + customer.email + ']';
                                $customerSelect.appendChild(opt);
                            });

                            // Destroy existing Select2 instance first
                            if (window.$ && $.fn.select2) {
                                var $select = $('#existing_customer_id');
                                if ($select.data('select2')) {
                                    $select.select2('destroy');
                                }
                                $select.select2({
                                    theme: 'bootstrap-5',
                                    width: '100%'
                                });
                            }
                        } else {
                            const opt = document.createElement('option');
                            opt.value = '';
                            opt.textContent = 'Tidak ada data customer';
                            $customerSelect.appendChild(opt);
                        }
                    }
                })
                .catch(function(err) {
                    if ($spinner) $spinner.style.display = 'none';
                    console.error('Gagal mengambil data customer:', err);
                    if ($customerSelect) {
                        $customerSelect.innerHTML = '<option value="">Error: ' + err.message + '</option>';
                    }
                });
        }

        // Event listeners for radio buttons
        customerTypeNew.addEventListener('change', toggleCustomerType);
        customerTypeExisting.addEventListener('change', toggleCustomerType);

        // Initial state
        toggleCustomerType();

        // Auto convert phone number: 0xxx to 62xxx
        const phoneInput = document.getElementById('telepphone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let val = phoneInput.value;
                if (val.length > 1 && val.startsWith('0')) {
                    phoneInput.value = '62' + val.substring(1);
                }
            });
        }

        // Load biaya tambahan options
        function loadBiayaTambahanOptions() {
            const $select = $('#additional_fee_id_display');
            if (!$select.length) return;

            fetch('<?= site_url('biaya_tambahan/list') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function(response) {
                    // Clear existing options
                    $select.empty();
                    $select.append(new Option('Pilih Biaya Tambahan (Opsional)', '', true, true));

                    // Add options from response
                    const fees = response.data || response;
                    if (Array.isArray(fees) && fees.length > 0) {
                        fees.forEach(function(fee) {
                            const label = fee.text || (fee.nama_biaya + ' - Rp ' + (fee.jumlah || fee.jumlah_biaya || 0).toLocaleString('id-ID'));
                            $select.append(new Option(label, fee.id));
                        });
                    }

                    // Reinitialize Select2
                    if ($select.data('select2')) {
                        $select.select2('destroy');
                    }
                    $select.select2({
                        theme: 'bootstrap-5',
                        width: '100%'
                    });
                })
                .catch(function(err) {
                    console.error('Gagal mengambil data biaya tambahan:', err);
                });
        }

        // Load biaya tambahan when page loads
        loadBiayaTambahanOptions();

        // Function to sync biaya data to hidden fields
        window.syncBiaya = function() {
            const biayaPasang = document.getElementById('biaya_pasang_display').value;
            const additionalFeeId = document.getElementById('additional_fee_id_display').value;

            document.getElementById('hidden_biaya_pasang').value = biayaPasang || '';
            document.getElementById('hidden_additional_fee_id').value = additionalFeeId || '';
        };

        // Initialize sync on page load
        setTimeout(syncBiaya, 1000);

        // Function to submit customer data with biaya
        window.submitCustomerData = function() {
            // Sync biaya data first
            syncBiaya();

            // Submit the UserForm
            document.getElementById('UserForm').submit();
        };

        // Initialize Select2
        if (typeof $.fn.select2 !== 'undefined') {
            // Initialize all form-select except those that will be loaded dynamically
            $('.form-select').not('#branch_id, #existing_customer_id, #id_paket, #area_id, #subscription_method, #additional_fee_id, #additional_fee_id_display').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumResultsForSearch: Infinity // Hide search box for dropdowns with few options
            });

            // Initialize subscription_method with delay to ensure DOM is ready
            setTimeout(function() {
                var $subscriptionMethod = $('#subscription_method');
                if ($subscriptionMethod.length > 0) {
                    $subscriptionMethod.empty(); // Clear any existing options
                    $subscriptionMethod.append(new Option('Pilih...', '', true, true));
                    $subscriptionMethod.append(new Option('Ya', 'prepaid', false, false));
                    $subscriptionMethod.append(new Option('Tidak', 'postpaid', false, false));

                    $subscriptionMethod.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        minimumResultsForSearch: Infinity,
                        placeholder: 'Pilih...'
                    });

                    console.log('subscription_method initialized with options:', $subscriptionMethod.find('option').length);
                }
            }, 500);
        }

        // Form validation
        const userForm = document.getElementById('UserForm');
        if (userForm) {
            userForm.addEventListener('submit', function(e) {
                const namaLengkap = document.querySelector('[name="nama_pelanggan"]').value;
                const email = document.querySelector('[name="email"]').value;
                const phone = document.querySelector('[name="telepphone"]').value;
                const branch = document.querySelector('[name="branch_id"]').value;
                const paket = document.querySelector('[name="id_paket"]').value;
                const address = document.querySelector('[name="address"]').value;

                if (!namaLengkap || !email || !phone || !branch || !paket || !address) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi (*)');
                    return false;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Format email tidak valid');
                    return false;
                }

                // Phone validation
                if (phone.length < 10) {
                    e.preventDefault();
                    alert('Nomor HP minimal 10 digit');
                    return false;
                }
            });
        }

        // Pilih ODP button handler
        const pilihODPBtn = document.getElementById('pilihODPBtn');
        if (pilihODPBtn) {
            pilihODPBtn.addEventListener('click', function() {
                alert('Fitur pilih ODP - Klik pada peta untuk memilih titik ODP');
            });
        }

        // Pilih Titik Koordinat button handler
        const pilihTitikBtn = document.getElementById('pilihTitikKoordinatBtn');
        if (pilihTitikBtn) {
            pilihTitikBtn.addEventListener('click', function() {
                alert('Klik pada peta untuk memilih titik koordinat instalasi customer');
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Populate Paket Internet dropdown
        function loadPaketOptions() {
            var $paketSelect = document.getElementById('id_paket');
            if (!$paketSelect) return;
            fetch('<?= site_url('customer/paketOptions') ?>')
                .then(function(response) {
                    if (!response.ok) {
                        console.error('Paket Options Error:', response.status);
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('Paket data received:', data);
                    // Store package data globally for prorata calculation
                    window.packageData = {};

                    $paketSelect.innerHTML = '<option value="">Pilih Paket</option>';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(paket) {
                            var opt = document.createElement('option');
                            opt.value = paket.id;
                            opt.textContent = paket.label;

                            // Extract price from different possible sources
                            let price = 0;
                            if (paket.price) {
                                price = parseInt(paket.price);
                            } else if (paket.label && paket.label.includes('Rp')) {
                                // Extract price from label like "NEW KIMONET 10 | 10 Mbps | Rp 110.000"
                                const priceMatch = paket.label.match(/Rp\s*([\d.,]+)/);
                                if (priceMatch) {
                                    price = parseInt(priceMatch[1].replace(/[.,]/g, ''));
                                }
                            }

                            opt.setAttribute('data-price', price);
                            $paketSelect.appendChild(opt);

                            // Store in global object for easy access
                            window.packageData[paket.id] = {
                                id: paket.id,
                                name: paket.name || paket.label,
                                label: paket.label,
                                price: price
                            };
                        });

                        // Initialize Select2 after data is loaded
                        if (window.$ && $.fn.select2) {
                            $('#id_paket').select2({
                                theme: 'bootstrap-5',
                                width: '100%'
                            });
                        }
                    } else {
                        var opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = 'Tidak ada data paket';
                        $paketSelect.appendChild(opt);
                    }
                })
                .catch(function(err) {
                    console.error('Error loading paket:', err);
                    $paketSelect.innerHTML = '<option value="">Gagal mengambil data paket</option>';
                });
        }

        loadPaketOptions();



        // Populate Biaya Tambahan dropdown
        function loadBiayaTambahanOptions() {
            var $biayaSelect = $('#additional_fee_id');
            if (!$biayaSelect.length) return;

            fetch('<?= site_url('biaya_tambahan/list') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function(response) {
                    // Clear existing options
                    $biayaSelect.empty();
                    $biayaSelect.append('<option value="">Pilih Biaya Tambahan</option>');

                    if (response.status === 'success' && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach(function(biaya) {
                            $biayaSelect.append('<option value="' + biaya.id + '">' + biaya.text + '</option>');
                        });
                    } else {
                        $biayaSelect.append('<option value="">Tidak ada data biaya tambahan</option>');
                    } // Reinitialize Select2 after adding options
                    if (typeof $.fn.select2 !== 'undefined') {
                        $biayaSelect.select2({
                            theme: 'default',
                            width: '100%',
                            allowClear: true,
                            placeholder: 'Pilih Biaya Tambahan'
                        });
                    }
                })
                .catch(function(err) {
                    $biayaSelect.empty();
                    $biayaSelect.append('<option value="">Gagal mengambil data biaya tambahan</option>');

                    // Reinitialize Select2 even on error
                    if (typeof $.fn.select2 !== 'undefined') {
                        $biayaSelect.select2({
                            theme: 'default',
                            width: '100%',
                            allowClear: true,
                            placeholder: 'Pilih Biaya Tambahan'
                        });
                    }
                });
        }
        loadBiayaTambahanOptions();
    });
</script>
<script>
    // Dynamic select for province, city, district, village with improved API handling
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for all select elements with .select2 class except additional_fee_id
        $('.select2').not('#additional_fee_id').select2({
            theme: 'default',
            width: '100%',
            allowClear: false
        });

        const provinceSelect = document.getElementById('province');
        const citySelect = document.getElementById('city');
        const districtSelect = document.getElementById('district');
        const villageSelect = document.getElementById('village');

        // Loading flags to prevent infinite loops
        let isLoadingCities = false;
        let isLoadingDistricts = false;
        let isLoadingVillages = false;
        let isInitialLoad = true;

        // Check if all elements exist
        if (!provinceSelect || !citySelect || !districtSelect || !villageSelect) {
            return;
        }

        // Helper to clear and set default option (works with Select2)
        function resetSelect(select, placeholder) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            // Trigger change event for Select2
            $(select).trigger('change');
        }

        // Show loading state
        function showLoadingState(select, text = 'Memuat...') {
            select.innerHTML = `<option value="">${text}</option>`;
            select.disabled = true;
        }

        // Hide loading state
        function hideLoadingState(select) {
            select.disabled = false;
        } // Improved fetch with fallback and error handling
        async function fetchWithFallback(primaryUrl, fallbackUrl, errorContext) {
            try {
                // Try primary API first (ibnux data-indonesia)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);

                const response = await fetch(primaryUrl, {
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                if (response.ok) {
                    const data = await response.json();
                    return data;
                }
                throw new Error(`Primary API failed: ${response.status}`);
            } catch (primaryError) {
                if (fallbackUrl) {
                    try {
                        // Try fallback API
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), 10000);

                        const fallbackResponse = await fetch(fallbackUrl, {
                            signal: controller.signal
                        });
                        clearTimeout(timeoutId);

                        if (fallbackResponse.ok) {
                            const data = await fallbackResponse.json();
                            return data;
                        }
                        throw new Error(`Fallback API failed: ${fallbackResponse.status}`);
                    } catch (fallbackError) {
                        throw new Error(`Kedua API gagal untuk ${errorContext}`);
                    }
                } else {
                    throw primaryError;
                }
            }
        }

        // Fetch provinces with improved error handling
        async function loadProvinces() {
            try {
                showLoadingState(provinceSelect, 'Memuat provinsi...');

                const data = await fetchWithFallback(
                    'https://ibnux.github.io/data-indonesia/provinsi.json',
                    'load provinces'
                );

                resetSelect(provinceSelect, 'Pilih Provinsi');
                hideLoadingState(provinceSelect);

                if (Array.isArray(data)) {
                    data.forEach(function(prov) {
                        const opt = document.createElement('option');
                        opt.value = prov.id;
                        opt.textContent = prov.nama || prov.name; // Support both formats
                        provinceSelect.appendChild(opt);
                    });

                    // Set default province if configured in application settings
                    if (defaultRegional.province_id && isInitialLoad) {
                        provinceSelect.value = defaultRegional.province_id;
                        $(provinceSelect).trigger('change');

                        // Auto-load cities for default province
                        setTimeout(() => {
                            loadCities(defaultRegional.province_id);
                        }, 100);
                    } else {
                        // Trigger change event for Select2
                        $(provinceSelect).trigger('change');
                        isInitialLoad = false;
                    }
                } else {
                    throw new Error('Data format tidak valid');
                }
            } catch (error) {
                resetSelect(provinceSelect, 'Error memuat provinsi');
                hideLoadingState(provinceSelect);

                // Show user-friendly error
                alert('Gagal memuat data provinsi. Silakan refresh halaman dan coba lagi.');
            }
        }

        // Fetch cities by province id with improved error handling
        async function loadCities(provinceId) {
            if (isLoadingCities || !provinceId) return;
            isLoadingCities = true;

            resetSelect(citySelect, 'Pilih Kota/Kabupaten');
            resetSelect(districtSelect, 'Pilih Kecamatan');
            resetSelect(villageSelect, 'Pilih Desa');

            try {
                showLoadingState(citySelect, 'Memuat kota/kabupaten...');

                const data = await fetchWithFallback(
                    `https://ibnux.github.io/data-indonesia/kabupaten/${provinceId}.json`,
                    'load cities'
                );

                resetSelect(citySelect, 'Pilih Kota/Kabupaten');
                hideLoadingState(citySelect);

                if (Array.isArray(data)) {
                    data.forEach(function(city) {
                        const opt = document.createElement('option');
                        opt.value = city.id;
                        opt.textContent = city.nama || city.name; // Support both formats
                        citySelect.appendChild(opt);
                    });

                    // Set default city if configured and matches current province
                    if (defaultRegional.city_id && provinceId === defaultRegional.province_id && isInitialLoad) {
                        citySelect.value = defaultRegional.city_id;
                        $(citySelect).trigger('change');

                        // Auto-load districts for default city
                        setTimeout(() => {
                            loadDistricts(defaultRegional.city_id);
                        }, 100);
                    } else {
                        // Trigger change event for Select2
                        $(citySelect).trigger('change');
                    }
                } else {
                    throw new Error('Data format tidak valid');
                }
            } catch (error) {
                resetSelect(citySelect, 'Error memuat kota');
                hideLoadingState(citySelect);
            } finally {
                isLoadingCities = false;
            }
        }

        // Fetch districts by city id with improved error handling
        async function loadDistricts(cityId) {
            if (isLoadingDistricts || !cityId) return;
            isLoadingDistricts = true;

            resetSelect(districtSelect, 'Pilih Kecamatan');
            resetSelect(villageSelect, 'Pilih Desa');

            try {
                showLoadingState(districtSelect, 'Memuat kecamatan...');

                const data = await fetchWithFallback(
                    `https://ibnux.github.io/data-indonesia/kecamatan/${cityId}.json`,
                    'load districts'
                );

                resetSelect(districtSelect, 'Pilih Kecamatan');
                hideLoadingState(districtSelect);

                if (Array.isArray(data)) {
                    data.forEach(function(district) {
                        const opt = document.createElement('option');
                        opt.value = district.id;
                        opt.textContent = district.nama || district.name; // Support both formats
                        districtSelect.appendChild(opt);
                    });

                    // Set default district if configured and matches current city
                    if (defaultRegional.district_id && cityId === defaultRegional.city_id && isInitialLoad) {
                        districtSelect.value = defaultRegional.district_id;
                        $(districtSelect).trigger('change');

                        // Auto-load villages for default district
                        setTimeout(() => {
                            loadVillages(defaultRegional.district_id);
                        }, 100);
                    } else {
                        // Trigger change event for Select2
                        $(districtSelect).trigger('change');
                    }
                } else {
                    throw new Error('Data format tidak valid');
                }
            } catch (error) {
                resetSelect(districtSelect, 'Error memuat kecamatan');
                hideLoadingState(districtSelect);
            } finally {
                isLoadingDistricts = false;
            }
        }

        // Fetch villages by district id with improved error handling
        async function loadVillages(districtId) {
            if (isLoadingVillages || !districtId) return;
            isLoadingVillages = true;

            resetSelect(villageSelect, 'Pilih Desa');

            try {
                showLoadingState(villageSelect, 'Memuat desa/kelurahan...');

                const data = await fetchWithFallback(
                    `https://ibnux.github.io/data-indonesia/kelurahan/${districtId}.json`,
                    'load villages'
                );

                resetSelect(villageSelect, 'Pilih Desa');
                hideLoadingState(villageSelect);

                if (Array.isArray(data)) {
                    data.forEach(function(village) {
                        const opt = document.createElement('option');
                        opt.value = village.id;
                        opt.textContent = village.nama || village.name; // Support both formats
                        villageSelect.appendChild(opt);
                    });

                    // Set default village if configured and matches current district
                    if (defaultRegional.village_id && districtId === defaultRegional.district_id && isInitialLoad) {
                        villageSelect.value = defaultRegional.village_id;
                        $(villageSelect).trigger('change');
                        isInitialLoad = false; // Mark initial load as complete
                    } else {
                        // Trigger change event for Select2
                        $(villageSelect).trigger('change');
                    }
                } else {
                    throw new Error('Data format tidak valid');
                }
            } catch (error) {
                resetSelect(villageSelect, 'Error memuat desa');
                hideLoadingState(villageSelect);
            } finally {
                isLoadingVillages = false;
            }
        }

        // Event listeners (using jQuery for Select2 compatibility)
        $(provinceSelect).on('change', function() {
            if (!isLoadingCities && this.value) {
                loadCities(this.value);
            }
        });
        $(citySelect).on('change', function() {
            if (!isLoadingDistricts && this.value) {
                loadDistricts(this.value);
            }
        });
        $(districtSelect).on('change', function() {
            if (!isLoadingVillages && this.value) {
                loadVillages(this.value);
            }
        });

        // Initial load (with delay to ensure Select2 is initialized)
        setTimeout(() => {
            loadProvinces();
        }, 100);

        // Load Group Profiles function
        function loadGroupProfiles() {
            const select = document.getElementById('group_profile_id');
            if (!select) {
                return;
            }

            // Show loading state
            select.innerHTML = '<option value="">Loading Group Profiles...</option>';

            fetch('<?= site_url('customer/getGroupProfiles') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        select.innerHTML = '<option value="">Pilih Group Profile</option>';

                        if (data.data && data.data.length > 0) {
                            data.data.forEach(profile => {
                                const option = document.createElement('option');
                                option.value = profile.id;
                                const ipRange = profile.ip_range_start && profile.ip_range_end ?
                                    ` (${profile.ip_range_start} - ${profile.ip_range_end})` :
                                    '';
                                option.textContent = `${profile.name}${ipRange}`;
                                select.appendChild(option);
                            });
                        } else {
                            // No data available
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'Tidak ada Group Profile tersedia';
                            select.appendChild(option);
                        }
                    } else {
                        // API returned error
                        select.innerHTML = '<option value="">Error loading Group Profiles</option>';
                    }
                })
                .catch(error => {
                    // Show user-friendly error in dropdown
                    if (select) {
                        select.innerHTML = '<option value="">Gagal memuat Group Profile</option>';
                    }

                    // Show alert to user
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-2';
                    alertDiv.innerHTML = `
                        <i class="bx bx-error-circle me-1"></i>
                        Gagal memuat Group Profile: ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;

                    const groupProfileContainer = select.closest('.mb-4');
                    if (groupProfileContainer) {
                        groupProfileContainer.appendChild(alertDiv);

                        // Auto dismiss after 5 seconds
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.remove();
                            }
                        }, 5000);
                    }
                });
        }

        // Load group profiles when page loads
        loadGroupProfiles();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    /* Debug styles for tabs */
    .tab-pane {
        min-height: 200px;
    }

    .tab-pane.active {
        display: block !important;
    }

    /* PPP Secrets Modal Styling */
    #pppSecretsModal .modal-dialog {
        max-width: 1200px;
    }

    #pppSecretsModal .table th {
        background-color: #495057;
        color: white;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border-color: #6c757d;
    }

    #pppSecretsModal .table td {
        vertical-align: middle;
        border-color: #dee2e6;
    }

    #pppSecretsModal .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    #pppSecretsModal .select-secret {
        transition: all 0.2s ease-in-out;
    }

    #pppSecretsModal .select-secret:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Loading indicator for search button */
    #searchSecretBtn.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    /* Service field transition */
    #serviceField {
        transition: all 0.3s ease-in-out;
    }

    /* Disabled field styling */
    .form-control.bg-light,
    .form-select.bg-light,
    select.bg-light {
        background-color: #f8f9fa !important;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-control:disabled,
    .form-select:disabled,
    select:disabled {
        background-color: #f8f9fa !important;
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Used IPs Modal Styling */
    #usedIPsModal .modal-dialog {
        max-width: 1400px;
    }

    #usedIPsModal .table th {
        background-color: #ffc107;
        color: #212529;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border-color: #ffab00;
    }

    #usedIPsModal .table td {
        vertical-align: middle;
        border-color: #dee2e6;
    }

    #usedIPsModal .table-hover tbody tr:hover {
        background-color: #fff8e1;
    }

    #usedIPsModal .badge {
        font-size: 0.875em;
    }

    /* Show Used IPs button styling */
    #showUsedIPsBtn {
        transition: all 0.2s ease-in-out;
    }

    #showUsedIPsBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
    }

    /* Auto-fill IP button styling */
    #autoFillIPBtn {
        transition: all 0.2s ease-in-out;
    }

    #autoFillIPBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(13, 202, 240, 0.3);
    }

    #autoFillIPBtn:disabled {
        transform: none;
        box-shadow: none;
    }
</style>
<script>
    // PPP Secret field dependency management
    document.addEventListener('DOMContentLoaded', function() {
        const pppSecretSelect = document.getElementById('ppp_secret');
        const usernameField = document.getElementById('pppoe_username');
        const passwordField = document.getElementById('pppoe_password');
        const serviceField = document.getElementById('pppoe_service');
        const serviceFieldContainer = document.getElementById('serviceField');
        const typeIpField = document.getElementById('pppoe_type_ip');
        const typeIpFieldContainer = document.getElementById('typeIpField');
        const localIpField = document.getElementById('pppoe_local_ip');
        const localIpFieldContainer = document.getElementById('localIpField');
        const remoteAddressField = document.getElementById('pppoe_remote_address');
        const remoteAddressFieldContainer = document.getElementById('remoteAddressField');
        const searchSecretBtn = document.getElementById('searchSecretBtn');

        function togglePppoeFields() {
            const selectedValue = pppSecretSelect ? pppSecretSelect.value : '';
            const isFromRouter = selectedValue === 'ambil_dari_router';

            // TOMBOL TOGGLE
            if (searchSecretBtn) {
                const secretBtnInfo = document.getElementById('secretBtnInfo');

                if (isFromRouter) {
                    searchSecretBtn.style.display = 'inline-block';
                    searchSecretBtn.style.visibility = 'visible';
                    searchSecretBtn.classList.remove('d-none');
                    searchSecretBtn.classList.add('d-inline-block', 'show-button');

                    if (secretBtnInfo) {
                        secretBtnInfo.style.display = 'block';
                    }

                } else {
                    searchSecretBtn.style.display = 'none';
                    searchSecretBtn.style.visibility = 'hidden';
                    searchSecretBtn.classList.add('d-none');
                    searchSecretBtn.classList.remove('d-inline-block', 'show-button');

                    if (secretBtnInfo) {
                        secretBtnInfo.style.display = 'none';
                    }
                }
            }

            // Other field toggles...
            const isNoSecret = selectedValue === 'tanpa_secret';
            const isNewSecret = selectedValue === 'buat_secret_baru';

            // Username/Password fields
            [usernameField, passwordField].forEach(field => {
                if (field) {
                    field.disabled = isNoSecret;
                    if (isNoSecret) {
                        field.value = '';
                        field.classList.add('bg-light');
                    } else {
                        field.classList.remove('bg-light');
                    }
                }
            });

            // Service field
            if (serviceField) {
                serviceField.disabled = !isNewSecret;
                if (!isNewSecret) {
                    serviceField.value = '';
                    serviceField.classList.add('bg-light');
                } else {
                    serviceField.classList.remove('bg-light');
                    if (serviceField.value === '' || serviceField.value === null) {
                        serviceField.value = 'pppoe';
                    }
                }
            }

            // Update PPP Secret information
            updatePppSecretInfo(selectedValue);
        }

        function updatePppSecretInfo(selectedValue) {
            const infoElement = document.getElementById('ppp_secret_info');
            if (!infoElement) return;

            let infoText = '';
            let className = 'text-muted';

            switch (selectedValue) {
                case 'tanpa_secret':
                    infoText = 'Customer tidak menggunakan PPPoE authentication';
                    className = 'text-info';
                    break;
                case 'ambil_dari_router':
                    infoText = 'Akan mengambil secret yang sudah ada di router MikroTik. <strong>Klik tombol di bawah kolom Username!</strong>';
                    className = 'text-warning';
                    break;
                case 'buat_secret_baru':
                    infoText = 'Akan membuat secret baru di router MikroTik';
                    className = 'text-success';
                    break;
                default:
                    infoText = 'Pilih opsi PPP Secret';
                    className = 'text-muted';
            }

            infoElement.innerHTML = `<small class="${className}"><i class="bx bx-info-circle"></i> ${infoText}</small>`;
        }

        function toggleTypeIpFields() {
            const selectedTypeIp = typeIpField ? typeIpField.value : '';
            const isIpPool = selectedTypeIp === 'ip_pool';
            const isRemoteAddress = selectedTypeIp === 'remote_address';
            const autoFillIPBtn = document.getElementById('autoFillIPBtn');
            const remoteAddressInput = document.getElementById('pppoe_remote_address');
            const localIpInput = document.getElementById('pppoe_local_ip');
            const groupProfileSelect = document.getElementById('group_profile_id');
            const remoteAddressFieldDiv = document.getElementById('remoteAddressField');

            // Handle Group Profile field
            if (groupProfileSelect) {
                if (isIpPool) {
                    groupProfileSelect.disabled = true;
                    groupProfileSelect.value = ''; // Clear selection
                    groupProfileSelect.classList.add('bg-light');
                } else {
                    groupProfileSelect.disabled = false;
                    groupProfileSelect.classList.remove('bg-light');
                }
            }

            // Handle Local IP field - TETAP AKTIF untuk semua mode
            if (localIpInput) {
                localIpInput.disabled = false;
                localIpInput.classList.remove('bg-light');
            }

            // Handle Remote Address field - TETAP AKTIF untuk semua mode
            if (remoteAddressInput) {
                remoteAddressInput.disabled = false;
                remoteAddressInput.classList.remove('bg-light');
            }

            // Handle Auto-fill button
            if (autoFillIPBtn) {
                if (isIpPool) {
                    // IP Pool mode - sembunyikan auto-fill button
                    autoFillIPBtn.style.display = 'none';
                    autoFillIPBtn.title = 'Auto-fill tidak tersedia untuk IP Pool';

                    // Show info message for IP Pool
                    showTypeIpInfo('Mode IP Pool dipilih. Group Profile dinonaktifkan, isi IP secara manual.', 'info', remoteAddressFieldDiv);

                } else if (isRemoteAddress) {
                    // Remote Address mode - tampilkan auto-fill button
                    autoFillIPBtn.style.display = 'inline-block';
                    autoFillIPBtn.title = 'Auto-fill IP dari Group Profile';

                    // Clear any existing info
                    clearTypeIpInfo(remoteAddressFieldDiv);

                    // Auto-fill IP jika Group Profile sudah dipilih
                    if (groupProfileSelect && groupProfileSelect.value) {
                        setTimeout(() => {
                            autoFillNextAvailableIP(groupProfileSelect.value);
                        }, 100);
                    }

                } else {
                    // No selection - hide auto-fill button
                    autoFillIPBtn.style.display = 'none';
                    autoFillIPBtn.title = 'Pilih Type IP terlebih dahulu';

                    // Clear any existing info
                    clearTypeIpInfo(remoteAddressFieldDiv);
                }
            }
        }

        // Helper functions for Type IP info messages
        function showTypeIpInfo(message, type, container) {
            if (!container) return;

            // Remove existing info
            clearTypeIpInfo(container);

            const alertClass = type === 'info' ? 'alert-info' : 'alert-warning';
            const iconClass = type === 'info' ? 'bx-info-circle' : 'bx-exclamation-triangle';

            const infoDiv = document.createElement('div');
            infoDiv.className = `alert ${alertClass} alert-dismissible fade show mt-2`;
            infoDiv.id = 'type-ip-info';
            infoDiv.innerHTML = `
                <i class="bx ${iconClass} me-1"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            container.appendChild(infoDiv);

            // Auto dismiss after 4 seconds
            setTimeout(() => {
                if (infoDiv.parentNode) {
                    infoDiv.remove();
                }
            }, 4000);
        }

        function clearTypeIpInfo(container) {
            if (!container) return;
            const existingInfo = container.querySelector('#type-ip-info');
            if (existingInfo) {
                existingInfo.remove();
            }
        }

        // Search PPP secrets from MikroTik router
        function searchPPPSecrets() {
            // Get selected server location for router connection
            const serverLocationId = document.getElementById('branch_id').value;
            if (!serverLocationId) {
                alert('Pilih Branch terlebih dahulu');
                return;
            }

            // Show modal first with loading state
            showLoadingModal();

            // Then perform the search
            performPPPSearch(serverLocationId);
        }

        function showLoadingModal() {
            // Create modal with loading state            
            const modalHtml = `
                <div class="modal fade" id="pppSecretsModal" tabindex="-1" aria-labelledby="pppSecretsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="pppSecretsModalLabel">
                                    <i class="bx bx-router me-2"></i>
                                    PPP Secret
                                </h5>
                                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <h5 class="mb-2">Menghubungkan ke Router MikroTik...</h5>
                                    <p class="text-muted">Sedang mengambil data PPP Secret, mohon tunggu sebentar</p>
                                    <div class="progress mt-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('pppSecretsModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('pppSecretsModal'));
            modal.show();

            return modal;
        }

        function performPPPSearch(serverLocationId) {
            fetch('<?= site_url('customer/searchPPPSecrets') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    },
                    body: JSON.stringify({
                        server_location_id: serverLocationId,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                }).then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        updateModalWithSecrets(data.data, data.filtered_count || 0, data.total_on_router || 0);
                    } else {
                        let message = data.message || 'Tidak ada PPP secret ditemukan di router';

                        // Special handling for all secrets used case
                        if (data.success && data.filtered_count > 0) {
                            message = `Semua ${data.total_on_router} PPP secret di router sudah digunakan oleh pelanggan lain`;
                        }

                        updateModalWithError(message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = error.message.includes('Failed to fetch') ?
                        'Gagal terhubung ke server. Periksa koneksi internet Anda.' :
                        `Gagal mengambil data dari router: ${error.message}`;
                    updateModalWithError(errorMessage);
                });
        }

        function updateModalWithSecrets(secrets, filteredCount = 0, totalOnRouter = 0) {
            const modalBody = document.querySelector('#pppSecretsModal .modal-body');
            const modalTitle = document.querySelector('#pppSecretsModal .modal-title');

            // Update title
            modalTitle.innerHTML = `<i class="bx bx-router me-2"></i>Pilih PPP Secret dari Router`;

            // Create info message about filtering
            let infoMessage = `${secrets.length} PPP Secret berhasil ditemukan`;
            if (filteredCount > 0) {
                infoMessage += ` (${filteredCount} secret sudah digunakan dan disembunyikan)`;
            } // Update body with table and DataTables
            modalBody.innerHTML = `
                <div class="mb-3">
                    <span class="badge bg-success">
                        <i class="bx bx-check-circle me-1"></i>
                        ${infoMessage}
                    </span>
                </div>
                <div class="table-responsive">
                    <table id="pppSecretsTable" class="table table-sm table-bordered table-hover" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">No</th>
                                <th width="25%">Username</th>
                                <th width="20%">Paket</th>
                                <th width="15%">IP Lokal</th>
                                <th width="15%">IP Remote</th>
                                <th width="17%">Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${secrets.map((secret, index) => `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>
                                        <i class="bx bx-user text-primary me-1"></i>
                                        <strong>${secret.name || '-'}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">${secret.profile || secret.service || '-'}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">${secret.local_address || '-'}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">${secret.remote_address || '-'}</small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-success select-secret" 
                                                data-name="${secret.name || ''}"
                                                data-password="${secret.password || ''}"
                                                data-profile="${secret.profile || ''}"
                                                data-service="${secret.service || ''}"
                                                data-local="${secret.local_address || ''}"
                                                data-remote="${secret.remote_address || ''}"
                                                title="Pilih secret ini">
                                            <i class="bx bx-check me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        Pilih salah satu PPP Secret untuk mengisi form secara otomatis
                    </small>
                </div>
            `;

            // Initialize DataTables after a short delay to ensure DOM is ready
            setTimeout(() => {
                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#pppSecretsTable')) {
                    $('#pppSecretsTable').DataTable().destroy();
                }

                // Initialize DataTables
                $('#pppSecretsTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50],
                    responsive: true,
                    language: {
                        url: '/backend/assets/datatables/i18n/id.json'
                    },
                    columnDefs: [{
                            orderable: false,
                            targets: [0, 5] // No sorting for No. and Pilih columns
                        },
                        {
                            className: 'text-center',
                            targets: [0, 3, 4, 5] // Center align for No., IP Lokal, IP Remote, and Pilih columns
                        }
                    ],
                    order: [
                        [1, 'asc']
                    ], // Sort by username by default
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    drawCallback: function() {
                        // Re-attach click handlers after table redraw
                        addSelectButtonHandlers();
                    }
                });
            }, 100);

            // Add click handlers for select buttons
            addSelectButtonHandlers();
        }

        function updateModalWithError(errorMessage) {
            const modalBody = document.querySelector('#pppSecretsModal .modal-body');
            const modalTitle = document.querySelector('#pppSecretsModal .modal-title');

            // Update title
            modalTitle.innerHTML = `<i class="bx bx-error-circle me-2"></i>Gagal Mengambil PPP Secret`;

            // Update body with error message
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-danger mb-3">Koneksi ke Router Gagal</h5>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error:</strong> ${errorMessage}
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn btn-primary me-2" onclick="retryPPPSearch()">
                            <i class="bx bx-refresh me-1"></i>Coba Lagi
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i>Tutup
                        </button>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Tips:</strong> Pastikan:
                            <ul class="list-unstyled mt-2">
                                <li>• Router MikroTik dapat diakses</li>
                                <li>• Username dan password router benar</li>
                                <li>• Port API (8728) terbuka</li>
                                <li>• Koneksi internet stabil</li>
                            </ul>
                        </small>
                    </div>
                </div>
            `;
        }

        function retryPPPSearch() {
            const serverLocationId = document.getElementById('branch_id').value;
            if (serverLocationId) {
                // Show loading state again
                const modalBody = document.querySelector('#pppSecretsModal .modal-body');
                const modalTitle = document.querySelector('#pppSecretsModal .modal-title');

                modalTitle.innerHTML = `<i class="bx bx-router me-2"></i>Mencari PPP Secret dari Router`;

                modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="mb-2">Mencoba lagi koneksi ke Router...</h5>
                        <p class="text-muted">Sedang mengambil data PPP Secret, mohon tunggu sebentar</p>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                `;

                // Retry the search
                performPPPSearch(serverLocationId);
            }
        }

        function addSelectButtonHandlers() {
            // Add click handlers for select buttons
            document.querySelectorAll('.select-secret').forEach(button => {
                button.addEventListener('click', function() {
                    // Show loading state on button
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Dipilih...';
                    this.disabled = true;

                    // Fill form fields with selected secret data
                    if (usernameField) usernameField.value = this.dataset.name;
                    if (passwordField) passwordField.value = this.dataset.password; // Use profile data if available, otherwise use service
                    if (serviceField) {
                        serviceField.value = this.dataset.profile || this.dataset.service || '';
                    }

                    if (localIpField) localIpField.value = this.dataset.local;
                    if (remoteAddressField) remoteAddressField.value = this.dataset.remote;



                    // Show success notification
                    showSuccessNotification(`
                    PPP Secret "${this.dataset.name}"
                    berhasil dipilih`);

                    // Close modal after a short delay
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('pppSecretsModal'));
                        if (modal) {
                            modal.hide();
                        }
                    }, 500);
                });
            }); // Clean up modal when hidden
            document.getElementById('pppSecretsModal').addEventListener('hidden.bs.modal', function() {
                // Destroy DataTable if it exists before removing modal
                if ($.fn.DataTable.isDataTable('#pppSecretsTable')) {
                    $('#pppSecretsTable').DataTable().destroy();
                }
                this.remove();
            });
        }

        function resetSearchButton() {
            if (searchSecretBtn) {
                searchSecretBtn.disabled = false;
                searchSecretBtn.classList.remove('loading');
                searchSecretBtn.innerHTML = '<i class="bx bx-search"></i> Cari';
            }
        }

        function showSuccessNotification(message) {
            // Create a temporary success notification
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="pppSuccessNotification">
                    <i class="bx bx-check-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Remove existing notification if any
            const existingAlert = document.getElementById('pppSuccessNotification');
            if (existingAlert) {
                existingAlert.remove();
            }
            // Insert after the search button
            searchSecretBtn.insertAdjacentHTML('afterend', alertHtml);
            // Auto-remove after 3 seconds
            setTimeout(() => {
                const alert = document.getElementById('pppSuccessNotification');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }

        function showErrorNotification(message) {
            // Create a temporary error notification
            const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="pppErrorNotification">
                    <i class="bx bx-error-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Remove existing notification if any
            const existingAlert = document.getElementById('pppErrorNotification');
            if (existingAlert) {
                existingAlert.remove();
            }
            // Insert after the search button or test button
            const targetElement = searchSecretBtn;
            if (targetElement) {
                targetElement.insertAdjacentHTML('afterend', alertHtml);
            }
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById('pppErrorNotification');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        } // Make retryPPPSearch available globally
        window.retryPPPSearch = retryPPPSearch;

        // Delay initialization to ensure Select2 is fully loaded
        setTimeout(function() {
            // Re-query elements after delay
            const pppSecretSelect = document.getElementById('ppp_secret');
            const searchSecretBtn = document.getElementById('searchSecretBtn');

            if (!pppSecretSelect || !searchSecretBtn) {
                console.error('PPP Secret elements not found after delay');
                return;
            }

            // Check if it's using Select2
            const isSelect2 = $(pppSecretSelect).hasClass('select2') || $(pppSecretSelect).data('select2');

            // Function to handle the change
            function handlePppSecretChange() {
                const currentValue = pppSecretSelect.value;
                const btn = document.getElementById('searchSecretBtn');
                const info = document.getElementById('secretBtnInfo');

                if (btn) {
                    if (currentValue === 'ambil_dari_router') {
                        // Show button
                        btn.style.display = 'inline-block';
                        btn.style.visibility = 'visible';
                        btn.classList.remove('d-none');
                        btn.classList.add('d-inline-block', 'show-button');

                        if (info) info.style.display = 'block';

                        // Flash confirmation
                        btn.style.backgroundColor = '#198754';
                        setTimeout(() => {
                            btn.style.backgroundColor = '';
                        }, 300);

                    } else {
                        // Hide button
                        btn.style.display = 'none';
                        btn.style.visibility = 'hidden';
                        btn.classList.add('d-none');
                        btn.classList.remove('d-inline-block', 'show-button');
                        if (info) info.style.display = 'none';
                    }
                }

                // Also call the main toggle function
                togglePppoeFields();
            }

            // Remove any existing listeners first
            pppSecretSelect.removeEventListener('change', handlePppSecretChange);

            // Add native change listener
            pppSecretSelect.addEventListener('change', handlePppSecretChange);

            // If it's Select2, also listen to Select2 events
            if (isSelect2) {
                $(pppSecretSelect).off('change.pppSecret').on('change.pppSecret', handlePppSecretChange);

                // Also listen to select2:select event
                $(pppSecretSelect).off('select2:select.pppSecret').on('select2:select.pppSecret', function(e) {
                    setTimeout(handlePppSecretChange, 100); // Delay to ensure value is updated
                });
            }

            // Initial check on page load
            handlePppSecretChange();

        }, 1000); // 1 second delay

        // Initial check for Type IP on page load
        if (typeIpField) {
            toggleTypeIpFields();

            // Add event listener for Type IP changes
            typeIpField.addEventListener('change', toggleTypeIpFields);
        } // Add event listener for search button
        if (searchSecretBtn) {
            searchSecretBtn.addEventListener('click', searchPPPSecrets);
        } // Add event listener for auto-fill IP button
        const autoFillIPBtn = document.getElementById('autoFillIPBtn');
        if (autoFillIPBtn) {
            autoFillIPBtn.addEventListener('click', function() {
                const typeIpSelect = document.getElementById('pppoe_type_ip');
                const groupProfileSelect = document.getElementById('group_profile_id');
                const groupProfileId = groupProfileSelect.value;
                const typeIp = typeIpSelect.value;

                if (!typeIp) {
                    alert('Pilih Type IP terlebih dahulu');
                    return;
                }

                if (typeIp === 'ip_pool') {
                    alert('Mode IP Pool dipilih. Silakan isi IP secara manual.');
                    return;
                }

                if (typeIp === 'remote_address') {
                    if (!groupProfileId) {
                        alert('Pilih Group Profile terlebih dahulu untuk mode Remote Address');
                        return;
                    }
                    autoFillNextAvailableIP(groupProfileId);
                } else {
                    alert('Pilih Type IP yang valid');
                }
            });
        }

        // Add event listener for Group Profile selection
        const groupProfileSelect = document.getElementById('group_profile_id');
        if (groupProfileSelect) {
            groupProfileSelect.addEventListener('change', function() {
                const typeIpSelect = document.getElementById('pppoe_type_ip');
                const typeIp = typeIpSelect ? typeIpSelect.value : '';

                // Show debug info for user
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                }
            });
        }

        // Add event listener for show used IPs button
        const showUsedIPsBtn = document.getElementById('showUsedIPsBtn');
        if (showUsedIPsBtn) {
            showUsedIPsBtn.addEventListener('click', function() {
                const groupProfileSelect = document.getElementById('group_profile_id');
                const groupProfileId = groupProfileSelect.value;
                showUsedIPsModal(groupProfileId);
            });
        }
    });

    // Form submission handler
    const userForm = document.getElementById('UserForm');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            const pppSecretSelect = document.getElementById('ppp_secret');
            const isNewSecret = pppSecretSelect && pppSecretSelect.value === 'buat_secret_baru';
            const isFromRouter = pppSecretSelect && pppSecretSelect.value === 'ambil_dari_router';

            // Jika secret diambil dari router, jangan buat secret baru di MikroTik
            if (isFromRouter) {
                // Langsung submit form tanpa membuat secret baru
                return; // Biarkan form submit normal
            }

            if (isNewSecret) {
                e.preventDefault(); // Stop form submission temporarily

                // Validate PPPoE fields first
                if (!validatePppoeFields()) {
                    return;
                }

                // Show loading
                const submitBtn = document.getElementById('submitForm');
                const originalText = submitBtn.value;
                submitBtn.disabled = true;
                submitBtn.value = 'Menyimpan ke MikroTik...';

                // Save PPPoE secret to MikroTik first
                savePppoeSecretToMikrotik()
                    .then(function(response) {
                        if (response.success) {
                            // Success, continue with form submission
                            submitBtn.value = 'Menyimpan Customer...';
                            userForm.submit();
                        } else {
                            // Error saving to MikroTik - provide more helpful error message
                            let errorMessage = response.message || 'Unknown error occurred';

                            // Check for common error types and provide user-friendly messages
                            if (errorMessage.includes('timeout') || errorMessage.includes('timed out')) {
                                errorMessage = 'Koneksi ke router MikroTik timeout. Silakan coba lagi atau hubungi administrator.';
                            } else if (errorMessage.includes('profile') && errorMessage.includes('exist')) {
                                errorMessage = 'Profile internet tidak ditemukan di router. Silakan hubungi administrator untuk mengatur profile yang sesuai.';
                            } else if (errorMessage.includes('connect') || errorMessage.includes('connection')) {
                                errorMessage = 'Tidak dapat terhubung ke router MikroTik. Silakan periksa koneksi atau hubungi administrator.';
                            } else if (errorMessage.includes('permission') || errorMessage.includes('access')) {
                                errorMessage = 'Tidak memiliki akses untuk membuat PPPoE di router. Silakan hubungi administrator.';
                            }
                            alert('❌ Gagal menyimpan PPPoE secret ke MikroTik:\n\n' + errorMessage + '\n\nTip: Customer tetap bisa disimpan terlebih dahulu, kemudian PPPoE dapat dibuat manual di router.');

                            // Ask if user wants to continue without MikroTik PPPoE creation
                            if (confirm('Apakah Anda ingin melanjutkan menyimpan customer tanpa membuat PPPoE di MikroTik?\n\n- YA: Customer disimpan, PPPoE dibuat manual nanti\n- TIDAK: Batalkan penyimpanan dan coba lagi')) {
                                submitBtn.value = 'Menyimpan Customer (tanpa PPPoE)...';
                                userForm.submit();
                            } else {
                                submitBtn.disabled = false;
                                submitBtn.value = originalText;
                            }
                        }
                    }).catch(function(error) {
                        console.error('PPPoE creation error:', error);
                        let errorMsg = 'Terjadi kesalahan saat menghubungi server untuk membuat PPPoE secret.';

                        if (error && error.message) {
                            errorMsg += '\n\nDetail: ' + error.message;
                        }

                        alert('❌ ' + errorMsg + '\n\nApakah Anda ingin melanjutkan menyimpan customer tanpa PPPoE?');

                        if (confirm('Lanjutkan menyimpan customer tanpa PPPoE?')) {
                            submitBtn.value = 'Menyimpan Customer (tanpa PPPoE)...';
                            userForm.submit();
                        } else {
                            submitBtn.disabled = false;
                            submitBtn.value = originalText;
                        }
                    });
            }
        });
    } // Validate PPPoE fields
    function validatePppoeFields() {
        const usernameEl = document.getElementById('pppoe_username');
        const passwordEl = document.getElementById('pppoe_password');
        const serverLocationEl = document.getElementById('branch_id');
        const paketEl = document.getElementById('id_paket');

        // Check if elements exist
        if (!usernameEl || !passwordEl || !serverLocationEl || !paketEl) {
            alert('Form elements not found. Please refresh the page and try again.');
            return false;
        }

        const username = usernameEl.value.trim();
        const password = passwordEl.value.trim();
        const serverLocation = serverLocationEl.value;
        const paket = paketEl.value;

        if (!username) {
            alert('Username PPPoE harus diisi');
            usernameEl.focus();
            return false;
        }

        if (!password) {
            alert('Password PPPoE harus diisi');
            passwordEl.focus();
            return false;
        }

        if (!serverLocation) {
            alert('Lokasi Server harus dipilih');
            serverLocationEl.focus();
            return false;
        }

        if (!paket) {
            alert('Paket Internet harus dipilih');
            paketEl.focus();
            return false;
        }
        return true;
    } // Save PPPoE secret to MikroTik
    function savePppoeSecretToMikrotik() {
        return new Promise(function(resolve, reject) {
            // Check if required form elements exist
            const requiredElements = [
                'branch_id',
                'id_paket',
                'pppoe_username',
                'pppoe_password'
            ];

            for (const elementId of requiredElements) {
                const element = document.getElementById(elementId);
                if (!element) {
                    reject(new Error(`Required form element '${elementId}' not found`));
                    return;
                }
            }

            const formData = new FormData();

            // Collect PPPoE data with null checks
            const serverLocationEl = document.getElementById('branch_id');
            const paketIdEl = document.getElementById('id_paket');
            const usernameEl = document.getElementById('pppoe_username');
            const passwordEl = document.getElementById('pppoe_password');
            const customerNameEl = document.getElementById('nama_pelanggan');
            const serviceEl = document.getElementById('pppoe_service');
            const typeIpEl = document.getElementById('pppoe_type_ip');
            const localIpEl = document.getElementById('pppoe_local_ip');
            const remoteIpEl = document.getElementById('pppoe_remote_address');

            formData.append('server_location_id', serverLocationEl ? serverLocationEl.value : '');
            formData.append('paket_id', paketIdEl ? paketIdEl.value : '');
            formData.append('username', usernameEl ? usernameEl.value : '');
            formData.append('password', passwordEl ? passwordEl.value : '');
            formData.append('customer_name', customerNameEl ? customerNameEl.value || '' : '');
            formData.append('service', serviceEl ? serviceEl.value || 'pppoe' : 'pppoe');
            formData.append('type_ip', typeIpEl ? typeIpEl.value || 'ip_pool' : 'ip_pool');
            formData.append('local_ip', localIpEl ? localIpEl.value || '' : '');
            formData.append('remote_ip', remoteIpEl ? remoteIpEl.value || '' : '');
            formData.append('comment', 'Created by billingku on ' + new Date().toISOString());

            // Add CSRF token - get from form or meta tag for better reliability
            const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]')?.value ||
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '<?= csrf_hash() ?>';
            formData.append('<?= csrf_token() ?>', csrfToken);

            fetch('<?= site_url('customer/savePppoeToMikrotik') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                }).then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    resolve(data);
                }).catch(function(error) {
                    reject(error);
                });
        });
    }
</script>

<script>
    // Auto-fill IP Functions (defined in global scope)
    function autoFillNextAvailableIP(groupProfileId) {
        const remoteAddressInput = document.getElementById('pppoe_remote_address');
        const localIpInput = document.getElementById('pppoe_local_ip');
        const autoFillBtn = document.getElementById('autoFillIPBtn');

        // Check if required elements exist
        if (!remoteAddressInput || !localIpInput || !autoFillBtn) {
            console.error('Required form elements not found for auto-fill IP functionality');
            alert('❌ Form elements not found. Please refresh the page and try again.');
            return;
        }

        // Validate group profile ID
        if (!groupProfileId) {
            alert('❌ Group Profile ID diperlukan untuk auto-fill IP');
            return;
        }

        const originalButtonContent = autoFillBtn.innerHTML;

        // Show loading state
        autoFillBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i>';
        autoFillBtn.disabled = true;

        // Prepare form data
        const formData = new FormData();
        formData.append('group_profile_id', groupProfileId);

        // Get CSRF token from form
        const csrfTokenInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
        if (csrfTokenInput) {
            formData.append('<?= csrf_token() ?>', csrfTokenInput.value);
        }

        fetch('<?= site_url('customer/getNextAvailableIP') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {

                if (data.success && data.data && data.data.remote_ip) {
                    // Fill Remote Address
                    remoteAddressInput.value = data.data.remote_ip;

                    // Fill Local IP if provided from group profile
                    if (data.data.local_ip) {
                        localIpInput.value = data.data.local_ip;
                    }

                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
                    const localIpText = data.data.local_ip ? ` dan Local IP ${data.data.local_ip}` : '';
                    const rangeText = data.data.ip_range ? ` (Range: ${data.data.ip_range})` : '';
                    const usedCountText = data.data.used_count ? ` [${data.data.used_count} IP sudah digunakan]` : '';
                    alertDiv.innerHTML = `
    <i class="bx bx-check-circle me-1"></i>
    Remote IP ${data.data.remote_ip}${localIpText} berhasil diisi otomatis${rangeText}${usedCountText}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
                    remoteAddressInput.parentNode.appendChild(alertDiv);

                    // Auto dismiss after 4 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 4000);
                } else {
                    alert('❌ ' + (data.message || 'Tidak ada IP yang tersedia di Group Profile ini'));
                }
            })
            .catch(error => {
                console.error('Error auto-filling IP:', error);
                alert('❌ Terjadi kesalahan saat mengisi IP otomatis: ' + error.message);
            }).finally(() => {
                // Restore button state
                if (autoFillBtn) {
                    autoFillBtn.innerHTML = originalButtonContent;
                    autoFillBtn.disabled = false;
                }
            });
    }

    // Used IPs Modal Functions (defined in global scope)
    function showUsedIPsModal(groupProfileId = null) {
        // Create modal with loading state
        const modalHtml = ` <div class="modal fade" id="usedIPsModal" tabindex="-1" aria-labelledby="usedIPsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usedIPsModalLabel">
                        <i class="bx bx-list-ul me-2"></i>
                        IP Address yang Sudah Digunakan
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="mb-2">Mengambil Data IP yang Digunakan...</h5>
                        <p class="text-muted">Sedang memuat daftar IP yang sudah digunakan pelanggan</p>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;

        // Remove existing modal if any
        const existingModal = document.getElementById('usedIPsModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('usedIPsModal'));
        modal.show();

        // Fetch used IPs data
        fetchUsedIPs(groupProfileId);
    }

    function fetchUsedIPs(groupProfileId = null) {
        const formData = new FormData();
        if (groupProfileId) {
            formData.append('group_profile_id', groupProfileId);
        }

        // Add CSRF token
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= site_url('customer/getUsedIPs') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsedIPs(data.data, data.message);
                } else {
                    showUsedIPsError(data.message || 'Gagal mengambil data IP yang digunakan');
                }
            })
            .catch(error => {
                console.error('Error fetching used IPs:', error);
                showUsedIPsError('Terjadi kesalahan saat mengambil data IP');
            });
    }

    function displayUsedIPs(usedIPs, message) {
        const modalBody = document.querySelector('#usedIPsModal .modal-body');
        const modalTitle = document.querySelector('#usedIPsModal .modal-title');

        modalTitle.innerHTML = `
    <i class="bx bx-list-ul me-2"></i>
    IP Address yang Sudah Digunakan (${usedIPs.length})
    `;

        if (usedIPs.length === 0) {
            modalBody.innerHTML = `
    <div class="text-center py-5">
        <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
        <h5 class="mt-3 mb-2">Tidak Ada IP yang Digunakan</h5>
        <p class="text-muted">Semua IP address masih tersedia untuk digunakan</p>
    </div>
    `;
            return;
        }

        let tableHtml = `
    <div class="mb-3">
        <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            ${message}
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-warning">
                <tr>
                    <th>No</th>
                    <th>Remote IP</th>
                    <th>Local IP</th>
                    <th>Nama Pelanggan</th>
                    <th>Nomor Layanan</th>
                    <th>Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                `;

        usedIPs.forEach((ip, index) => {
            tableHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td><span class="badge bg-danger">${ip.remote_address}</span></td>
                    <td><span class="badge bg-secondary">${ip.local_address || '-'}</span></td>
                    <td>${ip.customer_name}</td>
                    <td>${ip.service_number}</td>
                    <td>${ip.formatted_date}</td>
                </tr>
                `;
        });

        tableHtml += `
            </tbody>
        </table>
    </div>
    <div class="text-center mt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    </div>
    `;

        modalBody.innerHTML = tableHtml;
    }

    function showUsedIPsError(message) {
        const modalBody = document.querySelector('#usedIPsModal .modal-body');
        const modalTitle = document.querySelector('#usedIPsModal .modal-title');

        modalTitle.innerHTML = `
    <i class="bx bx-error me-2 text-danger"></i>
    Error - IP Address yang Digunakan
    `;

        modalBody.innerHTML = `
    <div class="text-center py-5">
        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
        <h5 class="mt-3 mb-2 text-danger">Gagal Memuat Data</h5>
        <p class="text-muted">${message}</p>
        <button type="button" class="btn btn-outline-danger" onclick="fetchUsedIPs()">
            <i class="bx bx-refresh me-1"></i>
            Coba Lagi
        </button>
    </div>
    `;
    } // Clean up modal when hidden
    document.addEventListener('hidden.bs.modal', function(event) {
        if (event.target.id === 'usedIPsModal') {
            event.target.remove();
        }
    });

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Function to show prorata confirmation modal
    function showProrataConfirmationModal(tglPasang, remainingDays, totalDaysInMonth, installDate) {
        // Get package information
        const paketSelect = document.getElementById('id_paket');

        if (!paketSelect || !paketSelect.value) {
            alert('⚠️ Silakan pilih paket terlebih dahulu untuk menghitung prorata!');
            return;
        }

        const selectedPackageId = paketSelect.value;
        let packagePrice = 0;
        let packageName = 'Paket Tidak Dipilih';

        // First try to get from global package data
        if (window.packageData && window.packageData[selectedPackageId]) {
            const packageInfo = window.packageData[selectedPackageId];
            packagePrice = packageInfo.price;
            packageName = packageInfo.label;
        } else {
            // Fallback: try to get from option attributes
            const selectedOption = paketSelect.querySelector(`option[value="${selectedPackageId}"]`);
            if (selectedOption) {
                packagePrice = selectedOption.getAttribute('data-price') || 0;
                packageName = selectedOption.textContent;
            }
        }

        // Convert to number if it's a string
        packagePrice = parseInt(packagePrice) || 0;

        if (!packagePrice || packagePrice <= 0) {

            // Try to get from select2 data if available
            try {
                const select2Data = $('#id_paket').select2('data');
                if (select2Data && select2Data[0]) {
                    // Sometimes price might be in the text
                    const text = select2Data[0].text || '';
                    const priceMatch = text.match(/Rp\s*([\d.,]+)/);
                    if (priceMatch) {
                        packagePrice = parseInt(priceMatch[1].replace(/[.,]/g, ''));
                        packageName = text;
                    }
                }
            } catch (e) {
                // Silent fail for Select2 data extraction
            }

            // Last resort: fetch package data directly from API
            if (!packagePrice || packagePrice <= 0) {
                alert('⚠️ Tidak dapat mengambil data harga paket.\n\nHal ini bisa terjadi karena:\n1. Data paket belum dimuat dengan sempurna\n2. Koneksi internet bermasalah\n3. Server sedang bermasalah\n\nSolusi:\n1. Refresh halaman dan tunggu paket dimuat\n2. Pilih paket lagi dengan hati-hati\n3. Hubungi administrator jika masalah berlanjut');
                return;
            }

            // If still no price found, show detailed error for debugging
            if (!packagePrice || packagePrice <= 0) {
                let debugInfo = `Debug Info:\n`;
                debugInfo += `- Selected ID: ${selectedPackageId}\n`;
                debugInfo += `- Package Price: ${packagePrice} (${typeof packagePrice})\n`;
                debugInfo += `- Has Global Data: ${!!(window.packageData && window.packageData[selectedPackageId])}\n`;
                debugInfo += `- Global Data: ${JSON.stringify(window.packageData?.[selectedPackageId] || 'null')}\n`;
                debugInfo += `- Available Package IDs: ${Object.keys(window.packageData || {}).join(', ')}\n`;

                alert('⚠️ Data harga paket tidak ditemukan dari API. Ini menunjukkan masalah dengan data paket.\n\n' + debugInfo + '\n\nSolusi:\n1. Refresh halaman dan pilih paket lagi\n2. Pastikan paket memiliki harga yang valid\n3. Hubungi administrator untuk memperbaiki data paket');
                return;
            }
        }

        // Calculate prorata amount
        const fullPrice = parseInt(packagePrice);
        const percentage = (remainingDays / totalDaysInMonth) * 100;
        const prorataAmount = Math.ceil((remainingDays / totalDaysInMonth) * fullPrice);

        // Format dates
        const installDateFormatted = installDate.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const periodFormatted = installDate.toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric'
        });

        // Populate modal content
        document.getElementById('prorata-install-date').textContent = installDateFormatted;
        document.getElementById('prorata-period').textContent = periodFormatted;
        document.getElementById('prorata-days').textContent = remainingDays;
        document.getElementById('prorata-total-days').textContent = totalDaysInMonth;
        document.getElementById('prorata-full-price').textContent = formatCurrency(fullPrice);
        document.getElementById('prorata-percentage').textContent = percentage.toFixed(1) + '%';
        document.getElementById('prorata-amount').textContent = formatCurrency(prorataAmount);

        // Show modal with proper z-index handling
        const modalElement = document.getElementById('prorataConfirmModal');
        const modal = new bootstrap.Modal(modalElement);

        // Ensure modal is on top
        modalElement.style.zIndex = '9999';

        // Hide any preloaders that might interfere
        const preloaders = document.querySelectorAll('.preloader, .spinner-border, .loading');
        preloaders.forEach(preloader => {
            if (preloader.style.zIndex >= 9999) {
                preloader.style.zIndex = '9997';
            }
        });

        modal.show();

        // Handle modal hide event to restore preloaders
        modalElement.addEventListener('hidden.bs.modal', function() {
            // Restore any preloaders that were hidden
            const preloaders = document.querySelectorAll('.preloader, .spinner-border, .loading');
            preloaders.forEach(preloader => {
                if (preloader.style.zIndex === '9997') {
                    preloader.style.zIndex = '';
                }
            });
        });

        // Handle confirmation button click
        const confirmBtn = document.getElementById('confirmProrataBtn');
        confirmBtn.onclick = function() {
            modal.hide();
            // Continue with form submission
            submitFormWithProrata();
        };
    }

    // Function to submit form after prorata confirmation
    function submitFormWithProrata() {
        const userForm = document.getElementById('UserForm');

        // Temporarily remove event listener to avoid infinite loop
        const newForm = userForm.cloneNode(true);
        userForm.parentNode.replaceChild(newForm, userForm);

        // Submit the form
        newForm.submit();
    }
</script>

<!-- DataTables JS - Load after jQuery -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
    // Override document.write to prevent violations
    (function() {
        const originalWrite = document.write;
        document.write = function(content) {
            // Silently prevent document.write violations
            return;
        };
    })();

    // Reduce browser console noise for production
    if (typeof console !== 'undefined' && console.log) {
        // Store original console methods
        const originalLog = console.log;
        const originalInfo = console.info;

        // Override console.log to filter out browser loading messages
        console.log = function() {
            const message = arguments[0];
            if (typeof message === 'string' && (
                    message.includes('selesai memuat') ||
                    message.includes('finished loading') ||
                    message.includes('XHR finished') ||
                    message.includes('Ambil selesai memuat')
                )) {
                return; // Silent ignore browser loading messages
            }
            return originalLog.apply(console, arguments);
        };

        console.info = function() {
            const message = arguments[0];
            if (typeof message === 'string' && (
                    message.includes('selesai memuat') ||
                    message.includes('finished loading') ||
                    message.includes('XHR finished') ||
                    message.includes('Ambil selesai memuat')
                )) {
                return; // Silent ignore browser loading messages
            }
            return originalInfo.apply(console, arguments);
        };
    }
</script>

<?= $this->endSection() ?>