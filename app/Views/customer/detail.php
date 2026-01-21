<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Detail Pelanggan </title>
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

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6">
                            <div>
                                <blockquote class="blockquote font-size-16 mb-0">
                                    <p>Nomor Layanan
                                        <span class="text-info">
                                            <?= esc($customer->nomor_layanan ?? '-') ?>
                                        </span>
                                    </p>
                                    <footer class="blockquote-footer">Nama Pelanggan
                                        <strong class="text-primary">
                                            <cite><?= esc($customer->nama_pelanggan ?? '-') ?></cite>
                                        </strong>
                                    </footer>
                                </blockquote>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="mt-4 mt-lg-0">
                                <blockquote class="blockquote  blockquote-reverse font-size-16 mb-0">
                                    <p><strong class="text-danger">
                                            Rp <?= number_format($unpaidTotal ?? 0, 0, ',', '.') ?>
                                        </strong></p>
                                    <footer class="blockquote-footer">Tagihan yang belum dibayar <cite title="Source Title"></cite></footer>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-4">
                <div class="card" style="padding-bottom: 1%">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Data Pelanggan</h4>
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Tanggal pemasangan</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?php
                                            // Format tanggal pemasangan dari database
                                            if (!empty($customer->tgl_pasang)) {
                                                $dateObj = DateTime::createFromFormat('Y-m-d', $customer->tgl_pasang);
                                                if ($dateObj) {
                                                    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                                    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                    $dayName = $days[(int)$dateObj->format('w')];
                                                    $day = $dateObj->format('d');
                                                    $month = $months[(int)$dateObj->format('m') - 1];
                                                    $year = $dateObj->format('Y');
                                                    echo "$dayName, $day $month $year";
                                                } else {
                                                    echo esc($customer->tgl_pasang);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Nama</th>
                                        <th width="1%">:</th>
                                        <td width="18%" style="text-align: left;"><?= esc($customer->nama_pelanggan ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Telepon</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?= esc($customer->telepphone ?? '-') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Email</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?= esc($customer->email ?? '-') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Harga</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?php
                                            if (!empty($customer->paket_harga)) {
                                                echo 'Rp ' . number_format($customer->paket_harga, 0, ',', '.');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Metode Langganan</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?php
                                            if (!empty($customer->subscription_method)) {
                                                if (strtolower($customer->subscription_method) === 'prepaid' || strtolower($customer->subscription_method) === 'prabayar') {
                                                    echo '<span class="text-success">Prabayar</span>';
                                                } elseif (strtolower($customer->subscription_method) === 'postpaid' || strtolower($customer->subscription_method) === 'pascabayar') {
                                                    echo '<span class="text-info">Pascabayar</span>';
                                                } else {
                                                    echo esc(ucfirst($customer->subscription_method));
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Biaya Tambahan</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            -
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Diskon</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            -
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Paket</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <span class="text-success font-size-12">
                                                <?= esc($customer->paket_label ?? '-') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Tanggal Jatuh Tempo</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <span class="text-danger font-size-12">
                                                <?php
                                                if (!empty($customer->tgl_tempo)) {
                                                    // tgl_tempo biasanya format Y-m-d
                                                    $tgl = $customer->tgl_tempo;
                                                    $dateObj = \DateTime::createFromFormat('Y-m-d', $tgl);
                                                    if ($dateObj) {
                                                        echo $dateObj->format('d');
                                                    } else {
                                                        echo esc($tgl);
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Login</th>
                                        <th width="1%">:</th>
                                        <td width="18%" style="text-align: left;">
                                            <span class="text-success font-size-12">
                                                <?php
                                                if (isset($customer->login)) {
                                                    if (strtolower($customer->login) === 'enable') {
                                                        echo 'ENABLE';
                                                    } elseif (strtolower($customer->login) === 'disable') {
                                                        echo '<span class="text-danger">DISABLE</span>';
                                                    } else {
                                                        echo esc(strtoupper($customer->login));
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- PPPoE Information Section -->
                                    <?php if (!empty($customer->pppoe_username)): ?>
                                        <tr>
                                            <th width="30%" style="text-align: left;">PPPoE Username</th>
                                            <th width="1%">:</th>
                                            <td width="18%" style="text-align: left;">
                                                <code class="text-primary"><?= esc($customer->pppoe_username) ?></code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="30%" style="text-align: left;">PPPoE Service</th>
                                            <th width="1%">:</th>
                                            <td width="18%" style="text-align: left;">
                                                <span class="badge bg-info"><?= esc($customer->pppoe_service ?? 'pppoe') ?></span>
                                            </td>
                                        </tr>
                                        <?php if (!empty($customer->pppoe_remote_address)): ?>
                                            <tr>
                                                <th width="30%" style="text-align: left;">Remote Address</th>
                                                <th width="1%">:</th>
                                                <td width="18%" style="text-align: left;">
                                                    <code class="text-success"><?= esc($customer->pppoe_remote_address) ?></code>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th width="30%" style="text-align: left;">PPPoE Status</th>
                                            <th width="1%">:</th>
                                            <td width="18%" style="text-align: left;">
                                                <?php if (!empty($customer->pppoe_comment)): ?>
                                                    <?php if (strpos($customer->pppoe_comment, 'successfully') !== false): ?>
                                                        <span class="badge bg-success">Synced</span>
                                                        <small class="text-muted d-block"><?= esc($customer->pppoe_comment) ?></small>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Error</span>
                                                        <small class="text-danger d-block"><?= esc($customer->pppoe_comment) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not Synced</span>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="syncPppoeBtn"
                                                        data-customer-id="<?= $customer->id_customers ?>">
                                                        <i class="bx bx-sync me-1"></i> Sync to MikroTik
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <!-- End PPPoE Information Section -->
                                    <tr>
                                        <th width="30%" style="text-align: left;">Alamat</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?= esc($customer->address ?? $customer->cluster_address ?? '-') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Nomor KTP</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;">
                                            <?= esc($customer->no_ktp ?? '-') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Propinsi</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;" id="province-display">
                                            <?php
                                            // Jika sudah ada nama provinsi dari API, tampilkan langsung
                                            if (!empty($customer->province_name) && $customer->province_name !== '-' && !is_numeric($customer->province_name)) {
                                                echo esc($customer->province_name);
                                            } elseif (!empty($customer->province) && is_numeric($customer->province)) {
                                                echo '<span class="text-muted location-loading" data-type="province" data-id="' . esc($customer->province) . '">';
                                                echo '<i class="bx bx-loader-alt bx-spin"></i> Memuat...';
                                                echo '</span>';
                                            } else {
                                                echo esc($customer->province ?? '-');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Kota</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;" id="city-display">
                                            <?php
                                            if (!empty($customer->city_name) && $customer->city_name !== '-' && !is_numeric($customer->city_name)) {
                                                echo esc($customer->city_name);
                                            } elseif (!empty($customer->city) && is_numeric($customer->city)) {
                                                echo '<span class="text-muted location-loading" data-type="city" data-id="' . esc($customer->city) . '" data-parent="' . esc($customer->province ?? '') . '">';
                                                echo '<i class="bx bx-loader-alt bx-spin"></i> Memuat...';
                                                echo '</span>';
                                            } else {
                                                echo esc($customer->city ?? '-');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Kecamatan</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;" id="district-display">
                                            <?php
                                            if (!empty($customer->district_name) && $customer->district_name !== '-' && !is_numeric($customer->district_name)) {
                                                echo esc($customer->district_name);
                                            } elseif (!empty($customer->district) && is_numeric($customer->district)) {
                                                echo '<span class="text-muted location-loading" data-type="district" data-id="' . esc($customer->district) . '" data-parent="' . esc($customer->city ?? '') . '">';
                                                echo '<i class="bx bx-loader-alt bx-spin"></i> Memuat...';
                                                echo '</span>';
                                            } else {
                                                echo esc($customer->district ?? '-');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="30%" style="text-align: left;">Desa</th>
                                        <th width=1%>:</th>
                                        <td width="18%" style="text-align: left;" id="village-display">
                                            <?php
                                            if (!empty($customer->village_name) && $customer->village_name !== '-' && !is_numeric($customer->village_name)) {
                                                echo esc($customer->village_name);
                                            } elseif (!empty($customer->village) && is_numeric($customer->village)) {
                                                echo '<span class="text-muted location-loading" data-type="village" data-id="' . esc($customer->village) . '" data-parent="' . esc($customer->district ?? '') . '">';
                                                echo '<i class="bx bx-loader-alt bx-spin"></i> Memuat...';
                                                echo '</span>';
                                            } else {
                                                echo esc($customer->village ?? '-');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- end card -->
            </div>
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Riwayat Pembayaran</h4>
                        <h5 class="mb-4">Filter by :</h5>
                        <form>
                            <div class="row mb-4">
                                <div class="col-xl-4 col-md-4 col-sm-12">
                                    <div class="mb-3">
                                        <label class="form-label">Status :</label>
                                        <select class="form-control select2-search-disable" name="filterStatus"
                                            id="filterStatus">
                                            <option value=""></option>
                                            <option value="0" selected>Tampilkan semua</option>
                                            <option value="paid">
                                                LUNAS
                                            </option>
                                            <option value="unpaid">
                                                BELUM LUNAS
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-12">
                                    <div class="mb-3">
                                        <label class="form-label">Periode :</label>
                                        <select class="form-control select2-search-disable" name="filterPeriode"
                                            id="filterPeriode">
                                            <option value=""></option>
                                            <option value="0" selected>Tampilkan semua</option>
                                            <option value="2025-01">Januari 2025 </option>
                                            <option value="2025-02">Februari 2025 </option>
                                            <option value="2025-03">Maret 2025 </option>
                                            <option value="2025-04">April 2025 </option>
                                            <option value="2025-05">Mei 2025 </option>
                                            <option value="2025-06">Juni 2025 </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-4 col-sm-12 text-end">
                                    <div class="mb-3" style="margin-top: 8.5%;">
                                        <button type="button" id="generateSingle" data-slug="uji-coba"
                                            class="btn btn-success waves-effect btn-label waves-light">
                                            <i class="mdi mdi-account-cash-outline label-icon"></i>
                                            Generate Tagihan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <table class="table table-striped table-hover align-middle table-bordered customer_datatable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">#</th>
                                    <th>No. Invoice</th>
                                    <th>Periode</th>
                                    <th>Tagihan</th>
                                    <th>Biaya Lain</th>
                                    <th>Diskon</th>
                                    <th width="100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="generateModal" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Tagihan | Uji Coba</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="generateForm" name="generateForm" class="form-horizontal">
                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" class="form-control" id="customer_slug" name="customer_slug" value="<?= esc($customer->id_customers ?? '') ?>">
                                <div class="col-lg-12 mb-3">
                                    <div class="mb-3">
                                        <label for="month" class="col-form-label">Jumlah tagihan yang akan di generate<span class="text-danger">*</span></label>
                                        <select class="form-select" name="month" id="month">
                                            <option value=""></option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                        <span id="error_month" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="saveGenerate" class="btn btn-primary" value="create">
                                Submit
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="myModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: #4f65d9; color: #fff; border-top-left-radius: 18px; border-top-right-radius: 18px;">
                        <h5 class="modal-title fw-bold" id="modelHeading">Detail Pembayaran</h5>
                        <span class="badge bg-light text-dark ms-3" id="displayInvoiceNo" style="font-size: 1rem;"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="CreateForm" name="CreateForm" class="form-horizontal">
                        <input type="hidden" id="invoice_no" name="invoice_no" value="">
                        <div class="modal-body p-4">
                            <!-- Informasi Pelanggan -->
                            <div class="border-start border-primary border-4 ps-3 mb-3">
                                <h6 class="text-primary mb-2"><i class="bx bx-user me-2"></i>Informasi Pelanggan</h6>
                                <div class="row">
                                    <div class="col-lg-4 mb-2">
                                        <label for="nomor_layanan" class="form-label fw-semibold small">Nomor Layanan</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="nomor_layanan" name="nomor_layanan" placeholder="Nomor Layanan" readonly>
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label for="customer" class="form-label fw-semibold small">Pelanggan</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="customer" name="customer" placeholder="Pelanggan" readonly>
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label for="periode" class="form-label fw-semibold small">Periode</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="periode" name="periode" placeholder="Periode" readonly>
                                    </div>
                                </div>
                            </div>
                            <!-- Informasi Tagihan -->
                            <div class="border-start border-success border-4 ps-3 mb-3">
                                <h6 class="text-success mb-2"><i class="bx bx-receipt me-2"></i>Informasi Tagihan</h6>
                                <div class="row">
                                    <div class="col-lg-4 mb-2">
                                        <label for="inputPackage" class="form-label fw-semibold small">Paket</label>
                                        <div class="p-2 bg-light rounded border">
                                            <span id="inputPackage" name="inputPackage"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label for="inputBill" class="form-label fw-semibold small">Tagihan</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="inputBill" name="inputBill" placeholder="Tagihan" readonly>
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label for="ip_router" class="form-label fw-semibold small">Status</label>
                                        <div class="p-2 bg-light rounded border">
                                            <span id="inputStatus" name="inputStatus"></span>
                                        </div>
                                        <span id="errorRouterip" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                    </div>
                                </div>
                            </div>
                            <!-- Form Pembayaran -->
                            <div class="border-start border-warning border-4 ps-3 mb-3">
                                <h6 class="text-warning mb-2"><i class="bx bx-money me-2"></i>Form Pembayaran</h6>
                                <div class="row">
                                    <div class="col-lg-6 mb-2">
                                        <label for="inputPayment" class="form-label fw-semibold small"><i class="bx bx-money-withdraw me-1"></i>Jumlah yang dibayarkan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inputPayment" name="inputPayment" placeholder="Masukkan jumlah pembayaran">
                                        <span id="errorinputPayment" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                    </div>
                                    <div class="col-lg-6 mb-2">
                                        <label for="paymentDate" class="form-label fw-semibold small"><i class="bx bx-calendar me-1"></i>Tanggal Pembayaran <span class="text-danger">*</span></label>
                                        <div class="input-group" id="datepicker-payment">
                                            <input type="text" class="form-control" id="paymentDate" name="paymentDate" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container="#datepicker-payment" data-provide="datepicker" data-date-autoclose="true" autocomplete="off" value="<?= date('Y-m-d') ?>">
                                            <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                        </div>
                                        <span id="errorpaymentDate" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                        <!-- Bootstrap Datepicker CSS -->
                                        <link rel="stylesheet" href="/backend/assets/libs/datepicker/css/bootstrap-datepicker.min.css">
                                        <!-- Bootstrap Datepicker JS -->
                                        <script src="/backend/assets/libs/datepicker/js/bootstrap-datepicker.min.js"></script>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                if (typeof $.fn.datepicker !== 'undefined') {
                                                    $('#paymentDate').datepicker({
                                                        format: 'yyyy-mm-dd',
                                                        autoclose: true,
                                                        todayHighlight: true
                                                    });
                                                }
                                            });
                                        </script>
                                    </div>
                                    <div class="col-lg-6 mb-2">
                                        <label for="arrears" class="form-label fw-semibold small"><i class="bx bx-error-circle me-1"></i>Tunggakan</label>
                                        <input type="text" class="form-control bg-light" id="arrears" name="arrears" placeholder="Tunggakan" readonly>
                                        <span id="errorName" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                    </div>
                                    <div class="col-lg-6 mb-2">
                                        <label for="paymentMethod" class="form-label fw-semibold small"><i class="bx bx-credit-card me-1"></i>Cara Pembayaran <span class="text-danger">*</span></label>
                                        <select class="form-select" name="paymentMethod" id="paymentMethod">
                                            <option value="">Tolong pilih</option>
                                            <option value="bank transfer">BANK TRANSFER</option>
                                            <option value="cash">CASH</option>
                                        </select>
                                        <span id="errorpaymentMethod" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                    </div>
                                    <div class="col-lg-6 mb-2 bank" style="display: none;">
                                        <label for="bank" class="form-label fw-semibold small">Bank<span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" name="bank" id="bank">
                                            <option value="">Pilih Bank</option>
                                        </select>
                                        <script>
                                            $(document).ready(function() {
                                                function loadBankOptions() {
                                                    $.ajax({
                                                        url: '/customer/getBankOptions',
                                                        type: 'GET',
                                                        dataType: 'json',
                                                        success: function(data) {
                                                            var $bankSelect = $('#bank');
                                                            $bankSelect.empty();
                                                            $bankSelect.append('<option value="">Pilih Bank</option>');
                                                            if (Array.isArray(data)) {
                                                                data.forEach(function(bank) {
                                                                    $bankSelect.append('<option value="' + bank.id + '">' + bank.name + ' - ' + bank.account_number + ' a.n. ' + bank.account_holder + '</option>');
                                                                });
                                                            }
                                                        },
                                                        error: function() {
                                                            // fallback: keep default option
                                                        }
                                                    });
                                                }
                                                // Load on modal show
                                                $('#myModal').on('show.bs.modal', function() {
                                                    loadBankOptions();
                                                });
                                            });
                                        </script>
                                        <span id="errorbank" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                    </div>
                                    <div class="col-lg-6 mb-2 receiver" style="display: none;">
                                        <label for="receiver" class="form-label fw-semibold small">Penerima<span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" name="receiver" id="receiver">
                                            <option value="">Pilih Penerima</option>
                                            <option value="20777">Office</option>
                                        </select>
                                        <span id="errorreceiver" class="invalid-feedback text-danger" role="alert"><strong></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light d-flex justify-content-between p-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>Close</button>
                            <button type="submit" id="saveBtn" class="btn btn-success btn-sm px-4" value="create"><i class="bx bx-check me-1"></i>Konfirmasi pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->

<style>
    /* Fix label icon styling to prevent messy appearance */
    .btn-label {
        position: relative;
        padding-left: 44px !important;
    }

    .btn-label .label-icon {
        position: absolute;
        width: 35.5px;
        height: 100%;
        left: calc(var(--bs-border-width, 1px) * -1);
        top: calc(var(--bs-border-width, 1px) * -1);
        bottom: calc(var(--bs-border-width, 1px) * -1);
        background-color: rgba(255, 255, 255, 0.1);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-top-left-radius: var(--bs-border-radius, 0.25rem);
        border-bottom-left-radius: var(--bs-border-radius, 0.25rem);
    }

    .btn-label.btn-light .label-icon {
        background-color: rgba(52, 58, 64, 0.05);
        border-right: 1px solid rgba(52, 58, 64, 0.2);
    }

    .btn-label.btn-success .label-icon {
        background-color: rgba(255, 255, 255, 0.1);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Ensure proper icon positioning */
    .btn-label .label-icon i {
        font-size: 16px;
        line-height: 1;
    }

    /* Fix any potential conflicts with existing styles */
    .btn-label.waves-effect {
        overflow: hidden;
    }
</style>

<!-- Toastr CSS -->
<link rel="stylesheet" href="/backend/assets/libs/toastr/toastr.min.css">
<!-- Toastr JS -->
<script src="/backend/assets/libs/toastr/toastr.min.js"></script>
<script>
    $(document).on({
        ajaxStart: function() {
            $('#myLoading').removeClass("dontDisplay").addClass("d-flex");
        },
        ajaxStop: function() {
            $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
        }
    });

    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });
    });

    $(function() {
        $("body").tooltip({
            selector: '[data-toggle="tooltip"]',
            container: 'body'
        });
    });
</script>

<script>
    $(function() {
        var customerId = "<?= esc($customer_id) ?>";
        var url = "/transaction/invoices/get/history/" + customerId;
        console.log('Debug: Customer ID:', customerId);
        console.log('Debug: AJAX URL:', url);
        var table = $('.customer_datatable').DataTable({
            // ✅ PERFORMANCE OPTIMIZATIONS
            processing: true,
            serverSide: true,
            scrollX: true,
            responsive: false,
            autoWidth: false,
            deferRender: true, // Only render rows that are visible
            stateSave: false, // Disable state saving for better performance
            pageLength: 10, // Reasonable default page size
            lengthMenu: [5, 10, 25, 50], // Limit options to prevent large datasets

            ajax: {
                url: url,
                type: 'GET',
                timeout: 30000, // 30 second timeout
                data: function(d) {
                    d.filterStatus = $('#filterStatus').val();
                    d.filterPeriode = $('#filterPeriode').val();
                    console.log('Debug: Sending data:', d);
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status:', xhr.status);

                    // Show user-friendly error message
                    showToastMessage('error', 'Gagal memuat data riwayat pembayaran. Silakan refresh halaman.', 'Error');
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false
                }, // # (autonumber)
                {
                    data: 'invoice_no',
                    name: 'invoice_no',
                    defaultContent: '-'
                }, {
                    // Periode
                    data: 'periode',
                    name: 'periode',
                    defaultContent: '-',
                    render: function(data, type, row) {
                        if (data && data.match(/^\d{4}-\d{2}$/)) {
                            var monthNames = [
                                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                            ];

                            var parts = data.split('-');
                            var year = parseInt(parts[0]);
                            var month = parseInt(parts[1]) - 1; // JavaScript months are 0-indexed

                            var monthName = monthNames[month];

                            // Get first and last day of the month
                            var firstDay = new Date(year, month, 1);
                            var lastDay = new Date(year, month + 1, 0);

                            var firstDayStr = ('0' + firstDay.getDate()).slice(-2) + '/' +
                                ('0' + (firstDay.getMonth() + 1)).slice(-2) + '/' +
                                firstDay.getFullYear();
                            var lastDayStr = ('0' + lastDay.getDate()).slice(-2) + '/' +
                                ('0' + (lastDay.getMonth() + 1)).slice(-2) + '/' +
                                lastDay.getFullYear();

                            return monthName + ' ' + year + '<br><small class="text-muted">(' + firstDayStr + ' - ' + lastDayStr + ')</small>';
                        }
                        return data || '-';
                    }
                }, // Periode
                {
                    data: 'bill',
                    name: 'bill',
                    render: function(data, type, row) {
                        if (data) {
                            var packageInfo = '';
                            if (row.package && row.package !== '-') {
                                packageInfo = '<span class="text-success font-size-12">' + row.package + '</span><br>';
                            }
                            return '<div class="text-center">' + packageInfo + '<span class="text-primary font-size-12">Rp ' + parseInt(data).toLocaleString('id-ID') + '</span></div>';
                        }
                        return '<div class="text-center">-</div>';
                    }
                }, // Tagihan
                {
                    data: 'additional_fee',
                    name: 'additional_fee',
                    render: function(data, type, row) {
                        if (data && data > 0) {
                            return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                        }
                        return '-';
                    }
                }, // Biaya Lain
                {
                    data: 'discount',
                    name: 'discount',
                    render: function(data, type, row) {
                        if (data && data > 0) {
                            return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                        }
                        return '-';
                    }
                }, // Diskon
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    visible: false,
                    searchable: false
                }, // Customer Name (hidden)
                {
                    data: 'package',
                    name: 'package',
                    visible: false,
                    searchable: false
                }, { // Package (hidden)                {
                    data: 'status',
                    name: 'status',
                    visible: false,
                    searchable: false
                }, // Status (hidden)
                {
                    data: 'arrears',
                    name: 'arrears',
                    visible: false,
                    searchable: false
                }, // Arrears (hidden)
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var actions = '';
                        // Tombol Konfirmasi Pembayaran
                        if (row.status !== 'paid' && row.status !== 'lunas') {
                            actions += '<button type="button" class="btn btn-sm btn-primary me-1 payInvoice" ' +
                                'data-id="' + row.invoice_no + '" ' +
                                'data-total="' + (row.bill || 0) + '" ' +
                                'data-customer="' + (row.customer_name || '<?= esc($customer->nama_pelanggan ?? '') ?>') + '" ' +
                                'data-periode="' + (row.periode || '') + '" ' +
                                'data-package="' + (row.package || '') + '" ' +
                                'data-status="' + (row.status || '') + '" ' +
                                'data-arrears="' + (row.arrears || 0) + '" ' +
                                'data-service_no="' + '<?= esc($customer->nomor_layanan ?? '') ?>' + '" ' +
                                'title="Konfirmasi Pembayaran"><i class="mdi mdi-cash"></i></button>';
                        }
                        // Tombol Lihat Detail
                        actions += '<button type="button" class="btn btn-sm btn-info view-detail" data-id="' + row.invoice_no + '" title="Lihat Detail"><i class="mdi mdi-eye"></i></button>';
                        return actions;
                    }
                } // Tindakan
            ],
            columnDefs: [{
                    width: "50px",
                    targets: 0
                },
                {
                    width: "150px",
                    targets: 1
                },
                {
                    width: "200px",
                    targets: 2
                },
                {
                    width: "200px",
                    targets: 3
                },
                {
                    width: "120px",
                    targets: 4
                },
                {
                    width: "120px",
                    targets: 5
                },
                {
                    width: "120px",
                    targets: 6
                }, {
                    className: 'text-center',
                    targets: [0, 6]
                },
                {
                    className: 'text-end',
                    targets: [4, 5]
                },
                {
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }
            ],
            order: [
                [2, 'desc']
            ],
            language: {
                processing: "Memuat data...",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Tidak ada data tagihan yang ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada data yang tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });
        $('body').on('click', '.deleteData', function() {
            var id = $(this).data("id");
            Swal.fire({
                title: "Apa kamu yakin?",
                text: "Anda tidak akan dapat mengembalikan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Ya, hapus saja."
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "/invoices/:id";
                    url = url.replace(':id', id);

                    $.ajax({
                        type: "DELETE",
                        url: url,
                        dataType: 'json',
                        success: function(data) {
                            table.draw();
                            if (data.status === 'success') {
                                toastr.success(data.message, data.title || 'Berhasil', {
                                    timeOut: 5000,
                                    progressBar: true
                                });
                            } else if (data.status === 'warning') {
                                toastr.warning(data.message, data.title || 'Peringatan', {
                                    timeOut: 5000,
                                    progressBar: true
                                });
                            } else {
                                toastr.error(data.message, data.title || 'Error', {
                                    timeOut: 5000,
                                    progressBar: true
                                });
                            }
                        },
                        error: function(data) {
                            console.log('Error:', data);
                        }
                    });
                }
            })
        });
        $("#filterStatus").select2({
            minimumResultsForSearch: 1 / 0,
            placeholder: "Pilih Status",
            formatNoMatches: function() {
                return "Tidak ada data yang ditemukan";
            }
        });
        $('#filterStatus').on('change', function() {
            table.draw();
        });
        // $('#filterStatus').trigger('change');

        $("#filterPeriode").select2({
            placeholder: "Pilih Periode",
            formatNoMatches: function() {
                return "Tidak ada data yang ditemukan";
            }
        });
        $('#filterPeriode').on('change', function() {
            table.draw();
        });
        // $('#filterPeriode').trigger('change');

        //modal payment validation
        $('#myModal').on('shown.bs.modal', function() {
            $('#inputPayment').focus();
        });
        $('body').on('click', '.payInvoice', function() {
            // Reset error and form states
            $('#errorinputPayment').hide();
            $('#inputPayment').removeClass(' is-invalid');
            $('#errorpaymentMethod').hide();
            $('#paymentMethod').removeClass(' is-invalid');
            $('#errorbank').hide();
            $('#bank').removeClass(' is-invalid');
            $('#errorreceiver').hide();
            $('#receiver').removeClass(' is-invalid');
            $('#CreateForm').trigger("reset");

            // Show modal explicitly
            $('#myModal').modal('show');

            // Set tanggal pembayaran ke hari ini (format yyyy-mm-dd)
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var yyyy = today.getFullYear();
            var todayString = yyyy + '-' + mm + '-' + dd;
            if ($('#paymentDate').length) {
                $('#paymentDate').val(todayString);
                setTimeout(function() {
                    if ($('#paymentDate').val() !== todayString) {
                        $('#paymentDate').val(todayString);
                        $('#paymentDate').trigger('change');
                    }
                }, 100);
            }

            // Set modal heading dan invoice number
            var invoiceNo = $(this).data("invoice_no") || $(this).data("id") || '';
            $('#modelHeading').html('Detail Pembayaran');
            // Always update the hidden field value
            $("#CreateForm input[name='invoice_no']").val(invoiceNo);
            // Set invoice number in display jika ada
            if ($('#displayInvoiceNo').length) {
                $('#displayInvoiceNo').text(invoiceNo);
            }

            // Format bill dan payment
            let billRaw = $(this).data("total");
            let bill = (typeof billRaw === 'string') ? billRaw.replace(/\D/g, '') : billRaw;
            if (!bill || isNaN(bill)) bill = 0;
            let billAmount = parseInt(bill);
            let formattedBill = billAmount.toLocaleString('id-ID');

            setTimeout(function() {
                $('#inputBill').val(formattedBill).trigger('change');
                $('#inputPayment').val(formattedBill).trigger('change');
                $('#nomor_layanan').val($(this).data("service_no") || '').trigger('change');
                $('#customer').val($(this).data("customer") || '').trigger('change');
            }.bind(this), 100);

            // Format periode
            var periodeRaw = $(this).data("periode") || '';
            var periodeFormatted = periodeRaw;
            if (/^\d{4}-\d{2}$/.test(periodeRaw)) {
                var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                var parts = periodeRaw.split('-');
                var year = parseInt(parts[0]);
                var month = parseInt(parts[1]) - 1;
                if (month >= 0 && month < 12) {
                    periodeFormatted = monthNames[month] + ' ' + year;
                }
            }
            $('#periode').val(periodeFormatted);
            $('#inputPackage').html('<span class="badge bg-info font-size-12">' + ($(this).data('package') || '-') + '</span>');

            // Set status
            if ($(this).data("status") == 'paid') {
                $('#inputStatus').html('<span class="badge bg-success font-size-12">LUNAS</span>');
            } else {
                $('#inputStatus').html('<span class="badge bg-warning font-size-12">BELUM LUNAS</span>');
            }

            // Set tunggakan/arrears
            $('#arrears').val($(this).data("arrears") || 0).trigger('keyup');

            $('#id').val('');
            // Modal sudah di-show di atas
        });

        $('body').on('click', '#generateSingle', function() {
            // Form reset is handled by 'show.bs.modal' event
            $('#generateModal').modal('show');
        }); // Removed Select2 init for old #month select
        $('body').on('click', '#saveGenerate', function() {
            if (window.isSubmittingGenerate) return;
            window.isSubmittingGenerate = true;
            console.log('saveGenerate button clicked'); // Debug log

            // Clear previous errors
            $('#error_month').hide().find('strong').text('');
            $('#month').removeClass('is-invalid');

            var customer_slug = $('#customer_slug').val();
            var month = $('#month').val();

            console.log('customer_slug:', customer_slug); // Debug log
            console.log('month:', month); // Debug log

            var isValid = true;
            if (!customer_slug) {
                toastr.error('Customer ID tidak valid.', 'Error', {
                    timeOut: 5000,
                    progressBar: true
                });
                isValid = false;
            }

            if (!month) {
                $('#month').addClass('is-invalid');
                $('#error_month').show().find('strong').text('Jumlah tagihan wajib dipilih.');
                isValid = false;
            } else if (isNaN(parseInt(month)) || parseInt(month) <= 0 || parseInt(month) > 12) {
                $('#month').addClass('is-invalid');
                $('#error_month').show().find('strong').text('Jumlah tagihan harus antara 1-12.');
                isValid = false;
            }

            if (!isValid) {
                window.isSubmittingGenerate = false;
                return;
            } // Disable button dan show loading
            $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Generating...');
            $(this).prop('disabled', true);

            var url = "/invoices/generate";

            // Get CSRF token
            var csrfName = $('meta[name="csrf-name"]').attr('content') || '<?= csrf_token() ?>';
            var csrfHash = $('meta[name="csrf-token"]').attr('content') || '<?= csrf_hash() ?>';

            var requestData = {
                customer_slug: customer_slug,
                month: parseInt(month)
            };
            requestData[csrfName] = csrfHash;

            console.log('About to send AJAX request to:', url); // Debug log
            console.log('Request data:', requestData); // Debug log

            $.ajax({
                url: url,
                data: requestData,
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    console.log('AJAX Success:', data); // Debug log
                    if (table) { // Check if table is defined
                        table.draw();
                    }
                    $('#generateModal').modal('hide');
                    $('#saveGenerate').html('Submit').prop('disabled', false);
                    window.isSubmittingGenerate = false;

                    if (data.status === 'success') {
                        toastr.success(data.message, data.title || 'Berhasil', {
                            timeOut: 5000,
                            progressBar: true
                        });
                    } else if (data.status === 'warning') {
                        toastr.warning(data.message, data.title || 'Peringatan', {
                            timeOut: 5000,
                            progressBar: true
                        });
                    } else {
                        toastr.error(data.message, data.title || 'Error', {
                            timeOut: 5000,
                            progressBar: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr, status, error); // Debug log
                    console.log('Response Text:', xhr.responseText); // Debug log

                    $('#saveGenerate').html('Submit').prop('disabled', false);
                    window.isSubmittingGenerate = false;

                    var errorMsg = "Terjadi kesalahan yang tidak terduga. Silakan coba lagi.";
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            if (errors.customer_slug) {
                                toastr.error(errors.customer_slug, 'Error', {
                                    timeOut: 5000,
                                    progressBar: true
                                });
                            }
                            if (errors.month) {
                                $('#month').addClass('is-invalid');
                                $('#error_month').show().find('strong').text(errors.month);
                            }
                            // Jika ada general message dan tidak ada field error spesifik
                            if (xhr.responseJSON.message && !errors.customer_slug && !errors.month) {
                                toastr.error(xhr.responseJSON.message, 'Error', {
                                    timeOut: 5000,
                                    progressBar: true
                                });
                            }
                        } else if (xhr.responseJSON.message) {
                            // Fallback ke general message jika tidak ada object 'errors'
                            toastr.error(xhr.responseJSON.message, 'Error', {
                                timeOut: 5000,
                                progressBar: true
                            });
                        }
                    } else {
                        toastr.error(errorMsg, 'Error', {
                            timeOut: 5000,
                            progressBar: true
                        });
                    }
                }
            });
        });

        // When generateModal is shown, clear form and errors
        $('#generateModal').on('show.bs.modal', function() {
            $('#generateForm').trigger("reset");
            $('#error_month').hide().find('strong').text('');
            $('#month').removeClass('is-invalid');
            // Reset submit button
            $('#saveGenerate').html('Submit').prop('disabled', false);
        });

        $('#paymentMethod').on('change', function() {
            if ($('#paymentMethod').val() == 'bank transfer') {
                $('.bank').fadeIn();
                $('.receiver').hide();
                $('#receiver').val('');
            } else {
                $('#bank').val('');
                $('.bank').hide();
                $('.receiver').fadeIn();
            }
        });

        $("#inputBill").keyup(function() {
            if ($(this).val().length > 3) {
                var n = parseInt($(this).val().replace(/\D/g, ''), 10);
                $(this).val(n.toLocaleString('id'));
            }
        });
        $('#inputPayment').keypress(function(e) {
            var charCode = (e.which) ? e.which : event.keyCode
            if (String.fromCharCode(charCode).match(/[^0-9]/g))
                return false;
        });
        $("#inputPayment").keyup(function() {
            if ($(this).val().length > 3) {
                var n = parseInt($(this).val().replace(/\D/g, ''), 10);
                $(this).val(n.toLocaleString('id'));
            }
        });

        $("#arrears").keyup(function() {
            if ($(this).val().length > 3) {
                var n = parseInt($(this).val().replace(/(?!^-)[^0-9.]/g, "").replace(/(\..*)\./g, '$1'));
                $(this).val(n.toLocaleString('id'));
            }
        });

        $("#inputBill, #inputPayment").keyup(function() {
            var bill = $("#inputBill").val().split(".").join("");
            var payment = $("#inputPayment").val().split(".").join("");

            var arrears = parseInt(bill) - parseInt(payment);
            $("#arrears").val(arrears).trigger('keyup');
        });

        $('#saveBtn').click(function(e) {
            e.preventDefault();
            let valid = true;
            $('#errorinputPayment').hide();
            $('#inputPayment').removeClass(' is-invalid');
            $('#errorpaymentMethod').hide();
            $('#paymentMethod').removeClass(' is-invalid');
            $('#errorbank').hide();
            $('#bank').removeClass(' is-invalid');
            $('#errorreceiver').hide();
            $('#receiver').removeClass(' is-invalid');
            var bill = $("#inputBill").val().split(".").join("");
            var payment = $("#inputPayment").val().split(".").join("");
            var method = $("#paymentMethod").val();
            if (!payment.length) { // zero-length string AFTER a trim
                $('#errorinputPayment').show().html("Masukan pembayaran diperlukan");
                $('#inputPayment').addClass(' is-invalid');
                valid = false;
            }
            if (method == '') {
                $('#errorpaymentMethod').show().html("Masukan metode pembayaran diperlukan");
                $('#paymentMethod').addClass(' is-invalid');
                valid = false;
            }
            if ($('#paymentMethod').val() == 'bank transfer') {
                if ($('#bank').val() == '') {
                    $('#errorbank').show().html("Masukan bank diperlukan");
                    $('#bank').addClass(' is-invalid');
                    valid = false;
                }
            }
            if ($('#paymentMethod').val() == 'cash') {
                if ($('#receiver').val() == '') {
                    $('#errorreceiver').show().html("Masukan penerima diperlukan");
                    $('#receiver').addClass(' is-invalid');
                    valid = false;
                }
            }
            if (parseInt(payment) > parseInt(bill)) {
                $('#errorinputPayment').show().html("Pembayaran tidak boleh lebih besar dari tagihan");
                $('#inputPayment').addClass(' is-invalid');
                valid = false;
            }

            if (valid) {
                $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Sending..');
                this.disabled = true;
                var url = '/invoices/payment-confirmation';
                $.ajax({
                    data: $('#CreateForm').serialize(),
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#CreateForm').trigger("reset");
                        $('#myModal').modal('hide');
                        if (data.status === 'success') {
                            toastr.success(data.message, data.title || 'Berhasil', {
                                timeOut: 5000,
                                progressBar: true
                            });
                        } else if (data.status === 'warning') {
                            toastr.warning(data.message, data.title || 'Peringatan', {
                                timeOut: 5000,
                                progressBar: true
                            });
                        } else {
                            toastr.error(data.message, data.title || 'Error', {
                                timeOut: 5000,
                                progressBar: true
                            });
                        }
                        $('#saveBtn').html("Konfirmasi pembayaran");
                        $('#saveBtn').prop('disabled', false);
                        location.reload();
                    },
                    error: function(data) {
                        $('#saveBtn').html("Konfirmasi pembayaran");
                        $('#saveBtn').prop('disabled', false);
                        location.reload();
                    }
                });
            }
        });

        // Event handler for payment confirmation button
        $('body').on('click', '.confirm-payment', function() {
            var invoiceNo = $(this).data('invoice');
            var customerName = $(this).data('customer');
            var periode = $(this).data('periode');
            var bill = $(this).data('bill');
            var arrears = $(this).data('arrears');

            // Populate payment confirmation modal
            $('#modelHeading').text('Konfirmasi Pembayaran');
            $('#invoice_no').val(invoiceNo);
            $('#nomor_layanan').val('<?= esc($customer->nomor_layanan ?? '') ?>');
            $('#customer').val(customerName || '<?= esc($customer->nama_pelanggan ?? '') ?>');
            $('#periode').val(periode);
            $('#inputBill').val(parseInt(bill).toLocaleString('id-ID'));
            $('#arrears').val(parseInt(arrears).toLocaleString('id-ID'));

            // Reset form fields
            $('#inputPayment').val('');
            $('#paymentMethod').val('').trigger('change');
            $('#bank').val('');
            $('#receiver').val('');

            // Show modal
            $('#myModal').modal('show');
        }); // Event handler for view detail button
        $('body').on('click', '.view-detail', function() {
            var invoiceNo = $(this).data('id');
            if (invoiceNo) {
                window.open('/invoices/print/' + invoiceNo, '_blank');
            }
        });

        // Load location names asynchronously
        async function fetchRegionName(type, id, parentId = null) {
            if (!id || isNaN(id)) return '-';
            let url = '';
            switch (type) {
                case 'province':
                    url = `https://ibnux.github.io/data-indonesia/provinsi.json`;
                    break;
                case 'city':
                    if (!parentId) return '-';
                    url = `https://ibnux.github.io/data-indonesia/kabupaten/${parentId}.json`;
                    break;
                case 'district':
                    if (!parentId) return '-';
                    url = `https://ibnux.github.io/data-indonesia/kecamatan/${parentId}.json`;
                    break;
                case 'village':
                    if (!parentId) return '-';
                    url = `https://ibnux.github.io/data-indonesia/kelurahan/${parentId}.json`;
                    break;
                default:
                    return '-';
            }
            try {
                const response = await fetch(url, {
                    cache: 'force-cache'
                });
                if (!response.ok) return '-';
                const data = await response.json();
                const found = data.find(item => String(item.id) === String(id));
                return found ? (found.nama || found.name || '-') : '-';
            } catch (e) {
                return '-';
            }
        }

        async function loadLocationNames() {
            const $loadings = $('.location-loading');
            if ($loadings.length === 0) return;

            // Province
            const $prov = $('[data-type="province"]');
            if ($prov.length) {
                const provId = $prov.data('id');
                const provName = await fetchRegionName('province', provId);
                $prov.text(provName !== '-' ? provName : 'Provinsi: ' + provId);
            }

            // City
            const $city = $('[data-type="city"]');
            if ($city.length) {
                const cityId = $city.data('id');
                const provId = $city.data('parent');
                const cityName = await fetchRegionName('city', cityId, provId);
                $city.text(cityName !== '-' ? cityName : 'Kota: ' + cityId);
            }

            // District
            const $district = $('[data-type="district"]');
            if ($district.length) {
                const districtId = $district.data('id');
                const cityId = $district.data('parent');
                const districtName = await fetchRegionName('district', districtId, cityId);
                $district.text(districtName !== '-' ? districtName : 'Kecamatan: ' + districtId);
            }

            // Village
            const $village = $('[data-type="village"]');
            if ($village.length) {
                const villageId = $village.data('id');
                const districtId = $village.data('parent');
                const villageName = await fetchRegionName('village', villageId, districtId);
                $village.text(villageName !== '-' ? villageName : 'Desa: ' + villageId);
            }
        }
        // Load location names after page is ready
        loadLocationNames();

        // Handle PPPoE Sync Button
        $('#syncPppoeBtn').on('click', function() {
            var customerId = $(this).data('customer-id');
            var $btn = $(this);
            var originalText = $btn.html();

            // Show loading state
            $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Syncing...');

            // Perform sync request
            $.ajax({
                url: '/customers/sync-pppoe/' + customerId,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        toastr.success(response.message || 'PPP secret synchronized successfully', 'Success', {
                            progressBar: true,
                            timeOut: 5000
                        });

                        // Refresh page to show updated status
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Show error message
                        toastr.error(response.message || 'Failed to synchronize PPP secret', 'Error', {
                            progressBar: true,
                            timeOut: 8000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Failed to sync PPP secret';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (status === 'timeout') {
                        errorMessage = 'Request timeout. The sync operation may still be in progress.';
                    } else if (error) {
                        errorMessage += ': ' + error;
                    }
                    toastr.error(errorMessage, 'Error', {
                        progressBar: true,
                        timeOut: 10000
                    });
                },
                complete: function() {
                    // Restore button state
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>