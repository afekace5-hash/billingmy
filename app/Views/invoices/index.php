    <?= $this->extend('layout/default') ?>

    <?= $this->section('title') ?>
    <title>Update Gawe &mdash; yukNikah</title>
    <?= $this->endSection() ?>

    <?= $this->section('content') ?>
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Tagihan</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Tagihan</a></li>
                                <li class="breadcrumb-item active">Tagihan</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx-user-voice h2 text-success mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Pelanggan Aktif</p>
                                    <h5 class="mb-0 text-success" id="w_activeCustomers"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx-notepad h2 text-info mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Total Tagihan</p>
                                    <h5 class="mb-0 text-info" id="w_total"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx-notepad h2 text-success mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Tagihan Terbit</p>
                                    <h5 class="mb-0 text-success" id="w_totalInvoice"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <a class="notGenerated" href="javascript:void(0)">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3 align-self-center">
                                        <i class="bx bx-notepad h2 text-danger mb-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-2">Tagihan Belum Terbit</p>
                                        <input type="hidden" name="notGenerated" id="notGenerated">
                                        <h5 class="mb-0 text-danger" id="w_invoiceNotGenerated"></h5>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx-list-check h2 text-success mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Tagihan Lunas</p>
                                    <h5 class="mb-0 text-success" id="w_paidInvoice"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx bx-timer h2 text-danger mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Tagihan Belum Lunas</p>
                                    <h5 class="mb-0 text-danger" id="w_unpaidInvoice"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx-list-check h2 text-success mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Tagihan Lunas</p>
                                    <h5 class="mb-0 text-success" id="w_paidInvoiceAmount"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3 align-self-center">
                                    <i class="bx bx bx-timer h2 text-danger mb-0"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-2">Tagihan Belum Lunas</p>
                                    <h5 class="mb-0 text-danger" id="w_unpaidInvoiceAmount"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12 table-responsive">

                    <div class="card" style="margin-bottom: 12px;display: none; background: #fbfbfb;" id="ticket-filters">
                        <div class="card-body border-bottom">
                            <form action="<?= base_url('transaction/invoices/import') ?>" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <input type="hidden" name="_token" value="c1x3h2ZZo1xPDG4jux5gR3PwbnSuEZEhOz4tSrcP">
                                    <div class="col-lg-6 col-sm-12 col-md-12 mt-2">
                                        <div>
                                            <div>
                                                <div class="input-group">
                                                    <input type="file" class="form-control" id="file" name="file"
                                                        aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                                    <button class="btn btn-primary" type="submit" id="submit">Import</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 col-md-12 mt-2">
                                        <div class="text-lg-center text-md-start"> <a href="<?= base_url('transaction/invoices/import/download-example') ?>"
                                                class="mb-2 btn btn-info waves-effect btn-label waves-light">
                                                <i class="mdi mdi-file-excel-outline label-icon"></i>
                                                Download Format Import
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body border-bottom">
                            <div>
                                <div class="col-lg-12">
                                    <div class="alert alert-warning" role="alert" style="margin: 0;">
                                        - Sebelum melakukan Import tagihan pastikan dulu Anda sudah bisa generate tagihan<br>
                                        - Pastikan Nomor Layanan dan Nama Pelanggan sesuai dengan aplikasi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center mb-3">
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="mb-0 me-2">Month:</label>
                                        <select class="form-select" id="filterPeriodeTop" style="width: 200px;">
                                            <option value="">Tampilkan Semua</option>
                                        </select>
                                        <!-- Hidden select for compatibility -->
                                        <select id="filterPeriode" style="display: none;">
                                            <option value="">Tampilkan Semua</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2 text-md-end">
                                    <button type="button" class="btn btn-primary me-2" id="manualPaidBtn" style="display:inline-flex;align-items:center;justify-content:center;">
                                        <i class="bx bx-money me-1" style="font-size:20px; padding-right:5px;"></i>Manual Paid
                                    </button>
                                    <button type=" button" class="btn btn-secondary" id="filterBtn">
                                        <i class="bx bx-filter me-1"></i>Filter
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-bordered customer_datatable" style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="80px">Action</th>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Invoice</th>
                                            <th>Status Invoice</th>
                                            <th>Payment Method</th>
                                            <th>Bill Payment ID</th>
                                            <th>Grand Total</th>
                                            <th>Status Payment</th>
                                            <th>Paid Amount</th>
                                            <th>Paid at</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="myModal" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white">
                            <div class="d-flex align-items-center">
                                <h5 class="modal-title me-3 text-white" id="modelHeading">
                                    <i class="bx bx-receipt me-2"></i>Detail Pembayaran
                                </h5>
                                <div class="badge bg-light text-dark px-3 py-2 fs-6 fw-bold" style="font-family: monospace; letter-spacing: 0.5px;" id="displayInvoiceNo"></div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="CreateForm" name="CreateForm" class="form-horizontal">
                            <div class="modal-body p-3">
                                <input type="hidden" class="form-control" id="invoice_no" name="invoice_no">

                                <!-- Informasi Pelanggan -->
                                <div class="border-start border-primary border-4 ps-3 mb-3">
                                    <h6 class="text-primary mb-2">
                                        <i class="bx bx-user me-2"></i>Informasi Pelanggan
                                    </h6>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label for="service_no" class="form-label fw-semibold small">Nomor Layanan</label>
                                                <input type="text" class="form-control form-control-sm bg-light" id="service_no" name="service_no"
                                                    placeholder="Nomor Layanan" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label for="customer" class="form-label fw-semibold small">Pelanggan</label>
                                                <input type="text" class="form-control form-control-sm bg-light" id="customer" name="customer"
                                                    placeholder="Pelanggan" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label for="periode" class="form-label fw-semibold small">Periode</label>
                                                <input type="text" class="form-control form-control-sm bg-light" id="periode" name="periode"
                                                    placeholder="Periode" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Informasi Tagihan -->
                                <div class="border-start border-success border-4 ps-3 mb-3">
                                    <h6 class="text-success mb-2">
                                        <i class="bx bx-receipt me-2"></i>Informasi Tagihan
                                    </h6>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label for="inputPackage" class="form-label fw-semibold small">Paket</label>
                                                <div class="p-2 bg-light rounded border">
                                                    <span id="inputPackage" name="inputPackage" class="fw-bold text-primary small"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label for="inputBill" class="form-label fw-semibold small">Tagihan</label>
                                                <input type="text" class="form-control form-control-sm bg-light" id="inputBill" name="inputBill"
                                                    placeholder="Tagihan" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-2">
                                                <label for="ip_router" class="form-label fw-semibold small">Status</label>
                                                <div class="p-2 bg-light rounded border">
                                                    <span id="inputStatus" name="inputStatus" class="fw-bold small"></span>
                                                </div>
                                                <span id="errorRouterip" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Form Pembayaran -->
                                <div class="border-start border-warning border-4 ps-3 mb-3">
                                    <h6 class="text-warning mb-2">
                                        <i class="bx bx-money me-2"></i>Form Pembayaran
                                    </h6>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-2">
                                                <label for="inputPayment" class="form-label fw-semibold small">
                                                    <i class="bx bx-money-withdraw me-1"></i>Jumlah yang dibayarkan
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="inputPayment" name="inputPayment"
                                                    placeholder="Masukkan jumlah pembayaran">
                                                <span id="errorinputPayment" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-2">
                                                <label for="paymentDate" class="form-label fw-semibold small">
                                                    <i class="bx bx-calendar me-1"></i>Tanggal Pembayaran
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" class="form-control form-control-sm" id="paymentDate" name="paymentDate">
                                                <span id="errorpaymentDate" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-2">
                                                <label for="arrears" class="form-label fw-semibold small">
                                                    <i class="bx bx-error-circle me-1"></i>Tunggakan
                                                </label>
                                                <input type="text" class="form-control form-control-sm bg-light" id="arrears" name="arrears"
                                                    placeholder="Tunggakan" readonly>
                                                <span id="errorName" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-2">
                                                <label for="paymentMethod" class="form-label fw-semibold small">
                                                    <i class="bx bx-credit-card me-1"></i>Cara Pembayaran
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select form-select-sm" name="paymentMethod" id="paymentMethod">
                                                    <option value="">Tolong pilih</option>
                                                    <option value="bank transfer">BANK TRANSFER</option>
                                                    <option value="cash">CASH</option>
                                                </select>
                                                <span id="errorpaymentMethod" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-2 bank" style="display: none;">
                                            <div class="mb-2">
                                                <label for="m_bank" class="form-label fw-semibold small">Bank<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="m_bank" id="m_bank">
                                                    <option value="">Pilih Bank</option>
                                                </select>
                                                <span id="errorbank" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-2 receiver" style="display: none;">
                                            <div class="mb-2">
                                                <label for="receiver" class="form-label fw-semibold small">Penerima<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" name="receiver" id="receiver">
                                                    <option value="">Pilih Penerima</option>
                                                    <option value="Office">Office</option>
                                                </select>
                                                <span id="errorreceiver" class="invalid-feedback text-danger" role="alert">
                                                    <strong></strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light d-flex justify-content-between p-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i>Close
                                </button>
                                <button type="submit" id="saveBtn" class="btn btn-success btn-sm px-4" value="create">
                                    <i class="bx bx-check me-1"></i>Konfirmasi pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="multiPayModal" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modelHeading">Pembayaran Masal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="multiPay" name="multiPay" class="form-horizontal">
                            <div class="modal-body">
                                <div class="row mb-2">
                                    <div class="col-md-12">
                                        <div class="alert alert-info alert-dismissible fade show mb-0" role="alert">
                                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                                            Semua tagihan yang di checklist akan di bayar full/lunas sesuai dengan tagihan pelanggan.
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <input type="hidden" class="form-control" id="m_ids" name="m_ids">
                                    <div class="col-lg-6 mb-3">
                                        <div class="mb-3">
                                            <label for="m_paymentMethod" class="col-form-label">Cara Pembayaran<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="m_paymentMethod" id="m_paymentMethod">
                                                <option value="">Tolong pilih</option>
                                                <option value="bank transfer">BANK TRANSFER</option>
                                                <option value="cash">CASH</option>
                                            </select>
                                            <span id="errorm_paymentMethod" class="invalid-feedback text-danger" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-3 m_bank" style="display: none;">
                                        <div class="mb-3">
                                            <label for="m_bank" class="col-form-label">Bank<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="m_bank" id="m_bank">

                                            </select>
                                            <span id="errorm_bank" class="invalid-feedback text-danger" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-3 m_receiver" style="display: none;">
                                        <div class="mb-3">
                                            <label for="m_receiver" class="col-form-label">Penerima<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" name="m_receiver" id="m_receiver">

                                                <option value="20777">akanet</option>
                                            </select>
                                            <span id="errorm_receiver" class="invalid-feedback text-danger" role="alert">
                                                <strong></strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" id="saveMultipayment" class="btn btn-primary" value="create">Bayar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Static Backdrop Modal -->
            <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
                aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="" id="formDeleteAll">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Hapus Data Terpilih</h5>
                                <button type="button" class="btn-close cancelDelete" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin akan menghapus data yang terpilih ?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light cancelDelete" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" id="okButton">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Delete All Modal -->
            <div class="modal fade" id="deleteAllModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
                aria-labelledby="deleteAllModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="" id="DeleteAll">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteAllModalLabel">Hapus Data Semua Data Pada Filter Periode Aktif</h5>
                                <button type="button" class="btn-close cancelDelete" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin akan menghapus data filter periode aktif ?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light cancelDelete" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" id="deleteAllButton">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="differenceInvoice" tabindex="-1" aria-labelledby="differenceInvoiceTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modelHeading">Invoice Belum Terbit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3" id="table-difference">

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Paid Modal -->
            <div class="modal fade" id="manualPaidModal" tabindex="-1" aria-labelledby="manualPaidModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title text-white" id="manualPaidModalTitle">
                                <i class="bx bx-money me-2"></i>Force Manual Paid
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="manualPaidForm" name="manualPaidForm">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="manualPaidCustomer" class="form-label fw-semibold">Customer</label>
                                        <select class="form-select" id="manualPaidCustomer" name="customer_id" required>
                                            <option value="">Pilih Customer</option>
                                        </select>
                                        <span id="errorManualPaidCustomer" class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="manualPaidInvoice" class="form-label fw-semibold">Invoice</label>
                                        <select class="form-select" id="manualPaidInvoice" name="invoice_id" required disabled>
                                            <option value="">✓ Select Invoice</option>
                                        </select>
                                        <span id="errorManualPaidInvoice" class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Total Tagihan</label>
                                        <div class="p-3 bg-light rounded">
                                            <h4 class="text-success mb-0" id="manualPaidTotal">Rp.0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success" id="manualPaidSubmitBtn">
                                    <i class="bx bx-check me-1"></i>Force Paid
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Invoice Detail View Modal -->
            <div class="modal fade" id="invoiceDetailModal" tabindex="-1" aria-labelledby="invoiceDetailModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title text-white" id="invoiceDetailModalTitle">
                                <i class="bx bx-receipt me-2"></i>Detail Transaction
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">ID</label>
                                    <div class="fw-semibold" id="viewInvoiceId">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Code</label>
                                    <div class="fw-semibold" id="viewInvoiceCode">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Related ID (Invoice)</label>
                                    <div class="fw-semibold" id="viewRelatedId">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Branch</label>
                                    <div class="fw-semibold" id="viewBranch">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Date</label>
                                    <div class="fw-semibold" id="viewDate">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Payment Method</label>
                                    <div class="fw-semibold" id="viewPaymentMethod">-</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-muted small mb-1">Category</label>
                                    <div class="fw-semibold" id="viewCategory">-</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-muted small mb-1">Attachment</label>
                                    <div class="fw-semibold" id="viewAttachment">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Type</label>
                                    <div class="fw-semibold" id="viewType">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Amount</label>
                                    <div class="fw-semibold text-success fs-5" id="viewAmount">Rp. 0</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-muted small mb-1">Transaction</label>
                                    <div class="fw-semibold text-primary" id="viewTransaction">-</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-muted small mb-1">Description</label>
                                    <div class="fw-semibold" id="viewDescription">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Created at</label>
                                    <div class="fw-semibold" id="viewCreatedAt">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Created by</label>
                                    <div class="fw-semibold" id="viewCreatedBy">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Last Update</label>
                                    <div class="fw-semibold" id="viewUpdatedAt">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">Updated by</label>
                                    <div class="fw-semibold" id="viewUpdatedBy">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- container-fluid -->
    </div> <!-- End Page-content -->
    <script>
        // Helper to get CSRF token from meta tag
        function getCsrfToken() {
            return $('meta[name="csrf-token"]').attr('content');
        }

        function getCsrfName() {
            return $('meta[name="csrf-name"]').attr('content');
        }

        // Setup global AJAX defaults for CSRF
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!settings.crossDomain) {
                    var csrfToken = getCsrfToken();
                    if (csrfToken) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    }
                }
            }
        });

        // Update CSRF token after AJAX response
        $(document).ajaxComplete(function(event, xhr, settings) {
            var newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newToken) {
                $('meta[name="csrf-token"]').attr('content', newToken);
            }
        });

        $(function() {
            // Inisialisasi select2 untuk Customer pada modal pembayaran manual
            if ($.fn.select2) {
                var $manualPaidCustomer = $('#manualPaidCustomer');
                $manualPaidCustomer.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Pilih Customer',
                    dropdownParent: $('#manualPaidModal')
                });
                // Refresh select2 saat modal dibuka
                $('#manualPaidModal').on('shown.bs.modal', function() {
                    $manualPaidCustomer.trigger('change.select2');
                });
                // Tambahkan CSS agar dropdown select2 bisa discroll
                var style = document.createElement('style');
                style.innerHTML = '.select2-container .select2-results__options { max-height: 300px; overflow-y: auto !important; }';
                document.head.appendChild(style);
            }
            // Dynamically populate Generate Tagihan dropdown
            function loadGenerateTagihanDropdown() {
                $.ajax({
                    url: "<?= base_url('invoices/available-periods') ?>",
                    type: "GET",
                    dataType: "json",
                    success: function(periods) {
                        var $dropdown = $('#generateTagihanDropdown');
                        $dropdown.empty();
                        if (!Array.isArray(periods) || periods.length === 0) {
                            $dropdown.append('<li><span class="dropdown-item">Tidak ada periode</span></li>');
                        } else {
                            if (periods.length > 0) {
                                var item = periods[0];
                                var value = item.periode || item.value || item;
                                var match = /^\d{4}-\d{2}$/.test(value);
                                var formatted = '';
                                if (match) {
                                    var parts = value.split('-');
                                    var year = parts[0];
                                    var month = parseInt(parts[1], 10);
                                    var monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                    if (month >= 1 && month <= 12) {
                                        formatted = 'Bulan ' + monthNames[month - 1] + ' ' + year;
                                    } else {
                                        formatted = value;
                                    }
                                } else {
                                    formatted = value;
                                }
                                $dropdown.append('<li><a class="dropdown-item" href="#" data-periode="' + value + '">' + formatted + '</a></li>');
                            } else {
                                $dropdown.append('<li><span class="dropdown-item">Tidak ada periode</span></li>');
                            }
                        }
                    },
                    error: function() {
                        $('#generateTagihanDropdown').html('<li><span class="dropdown-item text-danger">Gagal memuat periode</span></li>');
                    }
                });
            }

            // Call on page load
            loadGenerateTagihanDropdown();

            // Handle click on dynamic dropdown items
            $('body').on('click', '#generateTagihanDropdown a.dropdown-item', function(e) {
                e.preventDefault();
                var periode = $(this).data('periode');
                // Trigger bill generation for selected period
                var url = "<?= base_url('transaction/invoices/generate') ?>";
                var postData = {
                    periode: periode
                };
                var btn = $(this);
                btn.prop('disabled', true);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: postData,
                    success: function(resp) {
                        showNotif('success', 'Sukses', 'Tagihan berhasil digenerate untuk periode ' + periode);
                        btn.prop('disabled', false);
                    },
                    error: function() {
                        showNotif('error', 'Gagal', 'Gagal generate tagihan untuk periode ' + periode);
                        btn.prop('disabled', false);
                    }
                });
            });
            // Load available periods first
            loadAvailablePeriods();

            $('.toggle-filter').click(function() {
                $('#ticket-filters').toggle('blind');
            });
            var isMobile = false; //initiate as false
            // device detection
            if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) ||
                /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
                isMobile = true;
            }

            var role = "true";
            var showActionColumn = role == 'true' ? true : false;
            var defaultDueDate = "server";
            var table = initServerSideDataTable('.customer_datatable', {
                pageLength: 10,
                responsive: false, // Disable responsive to use scrollX
                scrollX: true, // Enable horizontal scrolling
                searching: true,
                ajax: {
                    url: "<?= site_url('transaction/invoices/get/data') ?>",
                    type: 'POST',
                    data: function(d) {
                        // Add CSRF token
                        d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';

                        d.filterPackage = $('#filterPackage').val();
                        d.filterServer = $('#filterServer').val();
                        d.filterDistrict = $('#filterDistrict').val();
                        d.filterVillage = $('#filterVillage').val();
                        d.filterStatus = $('#filterStatus').val();
                        // Use filterPeriodeTop if filterPeriode is empty
                        d.filterPeriode = $('#filterPeriode').val() || $('#filterPeriodeTop').val();
                        d.filterDueDate = $('#filterDueDate').val();
                        d.filterNewCustomer = $('#filterNewCustomer').val();
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable AJAX error:', xhr, error, code);
                        console.error('Response text:', xhr.responseText);
                        console.error('Status:', xhr.status);
                        console.error('DataTable Ajax Error Details:', {
                            xhr: xhr,
                            error: error,
                            code: code,
                            responseText: xhr.responseText,
                            status: xhr.status
                        });
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn,
                        render: function(data, type, row) {
                            return '<button type="button" class="btn btn-sm btn-info viewInvoice" data-id="' + row.id + '" title="View Detail"><i class="bx bx-show"></i></button>';
                        }
                    },
                    {
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '1%'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer.name',
                        render: function(data, type, row, meta) {
                            return '<div>' +
                                '<strong><span>' + (data || '-') + '</span></strong>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'package',
                        name: 'package',
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return '<span class="text-success">' + (data || '-') + '</span>';
                        }
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no',
                        render: function(data, type, row) {
                            return '<span class="text-info font-size-12">' + (data || '-') + '</span>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        searchable: false,
                        render: function(data, type, row) {
                            // Status Invoice column - shows payment status
                            if (data === 'paid') {
                                return '<span class="badge bg-success">Paid</span>';
                            } else if (data === 'pending') {
                                return '<span class="badge bg-warning">Pending</span>';
                            } else {
                                return '<span class="badge bg-danger">Unpaid</span>';
                            }
                        }
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method',
                        render: function(data, type, row) {
                            return data ? data.toUpperCase() : '-';
                        }
                    },
                    {
                        data: 'payment_id',
                        name: 'payment_id',
                        render: function(data, type, row) {
                            if (data && data !== '-') {
                                // Jika transaction ID panjang, tampilkan dengan copy button
                                if (data.length > 20) {
                                    return '<div class="d-flex align-items-center">' +
                                        '<span class="text-truncate me-2" style="max-width: 150px;" title="' + data + '">' + data + '</span>' +
                                        '<button class="btn btn-sm btn-outline-secondary copy-btn" onclick="navigator.clipboard.writeText(\'' + data + '\'); this.innerHTML=\'<i class=\\\'bx bx-check\\\'></i>\'; setTimeout(() => this.innerHTML=\'<i class=\\\'bx bx-copy\\\'></i>\', 1000);" title="Copy">' +
                                        '<i class="bx bx-copy"></i></button>' +
                                        '</div>';
                                }
                                return data;
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'bill',
                        name: 'bill',
                        searchable: false,
                        render: function(data, type, row) {
                            if (data && !isNaN(data)) {
                                let billAmount = parseInt(data);
                                return 'Rp ' + billAmount.toLocaleString('id-ID');
                            }
                            return 'Rp 0';
                        }
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status',
                        searchable: false,
                        render: function(data, type, row) {
                            // Show payment request log status
                            let paymentMethod = row.payment_method || '';
                            let invoiceStatus = row.status || '';

                            // If invoice is paid, check if manual or gateway
                            if (invoiceStatus === 'paid') {
                                if (paymentMethod.toLowerCase() === 'cash' || paymentMethod.toLowerCase() === 'manual') {
                                    return '<span class="badge" style="background-color: #e91e63; color: white;">Paid - Manual</span>';
                                }
                                return '<span class="badge bg-success">Paid</span>';
                            }

                            // Show payment log status if available
                            if (data && data !== '-') {
                                if (data === 'success') {
                                    return '<span class="badge bg-success">Success</span>';
                                } else if (data === 'pending') {
                                    return '<span class="badge bg-warning">Pending</span>';
                                } else if (data === 'failed') {
                                    return '<span class="badge bg-danger">Failed</span>';
                                } else if (data === 'expired') {
                                    return '<span class="badge bg-secondary">Expired</span>';
                                }
                                return '<span class="badge bg-info">' + data.toUpperCase() + '</span>';
                            }

                            // Fallback to invoice status
                            if (invoiceStatus === 'pending') {
                                return '<span class="badge bg-secondary">Pending</span>';
                            } else if (invoiceStatus === 'expired') {
                                return '<span class="badge bg-danger">Expired</span>';
                            }

                            return '<span class="badge bg-secondary">-</span>';
                        }
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount',
                        searchable: false,
                        render: function(data, type, row) {
                            if (data && !isNaN(data) && parseInt(data) > 0) {
                                let paidAmount = parseInt(data);
                                return 'Rp ' + paidAmount.toLocaleString('id-ID');
                            }
                            return 'Rp 0';
                        }
                    },
                    {
                        data: 'payment_date',
                        name: 'payment_date',
                        searchable: false,
                        render: function(data, type, row) {
                            if (data) {
                                // Format date with full Indonesian day name, date, month, year, and time
                                var date = new Date(data);
                                if (!isNaN(date.getTime())) {
                                    var dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                    ];

                                    var dayName = dayNames[date.getDay()];
                                    var day = date.getDate();
                                    var monthName = monthNames[date.getMonth()];
                                    var year = date.getFullYear();
                                    var hours = String(date.getHours()).padStart(2, '0');
                                    var minutes = String(date.getMinutes()).padStart(2, '0');

                                    return dayName + ', ' + day + ' ' + monthName + ' ' + year + ' ' + hours + ':' + minutes;
                                }
                            }
                            return '-';
                        }
                    },
                ],
                order: [
                    [5, 'desc'] // Order by periode (Status Invoice) column
                ],
                rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                    var totalRecords = this.DataTable.settings[0].json.recordsFiltered;
                    var pageInfo = this.api().page.info();
                    var rowIndexOnPage = displayIndex - pageInfo.start;
                    var rowsOnPage = pageInfo.length;

                    // For rows near the bottom of the current page, mark for potential dropup
                    if (rowIndexOnPage >= rowsOnPage - 2) {
                        $('.dropdown', row).addClass('dropup-candidate');
                    }

                    // Force apply button styling via JavaScript
                    $(row).find('.btn-action').each(function() {
                        $(this).css({
                            'padding': '0.3rem 0.4rem',
                            'font-size': '0.75rem',
                            'min-width': '26px',
                            'height': '28px',
                            'border-radius': '0.25rem',
                            'margin': '0 1px',
                            'box-shadow': '0 1px 3px rgba(0, 0, 0, 0.12)',
                            'font-weight': '500',
                            'display': 'inline-flex',
                            'align-items': 'center',
                            'justify-content': 'center',
                            'vertical-align': 'middle',
                            'transition': 'all 0.2s ease-in-out'
                        });

                        // Apply color-specific styles
                        if ($(this).hasClass('btn-info')) {
                            $(this).css({
                                'background': 'linear-gradient(135deg, #17a2b8, #1ab3cc)',
                                'border-color': '#17a2b8',
                                'color': 'white'
                            });
                        } else if ($(this).hasClass('btn-secondary')) {
                            $(this).css({
                                'background': 'linear-gradient(135deg, #6c757d, #7d8691)',
                                'border-color': '#6c757d',
                                'color': 'white'
                            });
                        } else if ($(this).hasClass('btn-primary')) {
                            $(this).css({
                                'background': 'linear-gradient(135deg, #007bff, #1a88ff)',
                                'border-color': '#007bff',
                                'color': 'white'
                            });
                        } else if ($(this).hasClass('btn-warning')) {
                            $(this).css({
                                'background': 'linear-gradient(135deg, #ffc107, #ffcd39)',
                                'border-color': '#ffc107',
                                'color': '#212529'
                            });
                        } else if ($(this).hasClass('btn-success')) {
                            $(this).css({
                                'background': 'linear-gradient(135deg, #28a745, #34ce57)',
                                'border-color': '#28a745',
                                'color': 'white'
                            });
                        } else if ($(this).hasClass('btn-danger')) {
                            $(this).css({
                                'background': 'linear-gradient(135deg, #dc3545, #e4606d)',
                                'border-color': '#dc3545',
                                'color': 'white'
                            });
                        }

                        // Style the icons
                        $(this).find('i').css({
                            'font-size': '11px',
                            'line-height': '1',
                            'margin': '0'
                        });
                    });
                },
                columnDefs: [{
                        width: "1%",
                        targets: [0, 1]
                    },
                    {
                        width: "180px",
                        targets: [2] // Customer name
                    },
                    {
                        width: "10%",
                        targets: [3] // Periode
                    },
                    {
                        width: "20%",
                        targets: [4] // Package & Bill
                    },
                    {
                        width: "8%",
                        targets: [5] // Status
                    },
                    {
                        width: "10%",
                        targets: [6, 7] // Additional fee, discount
                    },
                    {
                        width: "12%",
                        targets: [8] // Server
                    },
                    {
                        width: "120px",
                        targets: [9] // Action - Modern compact width for balanced buttons   
                    },
                    {
                        className: 'text-center',
                        targets: [0, 1, 5, 6, 7, 9]
                    }
                ]
            }); // Close DataTable configuration

            // Add error handling for DataTable
            table.on('error.dt', function(e, settings, techNote, message) {
                console.error('DataTable error:', message);
                alert('Terjadi kesalahan saat memuat data. Silakan coba refresh halaman.');
            });

            let debounceSearch = null;

            // Target input bawaan DataTables
            $('#searchTableList').on('keyup', function() {
                clearTimeout(debounceSearch); // Hapus timer sebelumnya

                const searchTerm = $(this).val();
                console.log(searchTerm);

                debounceSearch = setTimeout(() => {
                    // Trigger search secara manual
                    table.draw();
                }, 500); // Delay 500ms
            });

            table.on('draw.dt', function() {
                $("#checkAll").prop('checked', false);

                // Pastikan semua button action tidak disabled
                $('.btn-action').prop('disabled', false);

                // Apply button styling with hover effects
                $('.btn-action').off('mouseenter mouseleave').on('mouseenter', function() {
                    $(this).css({
                        'transform': 'translateY(-1px)',
                        'box-shadow': '0 3px 6px rgba(0, 0, 0, 0.16)',
                        'opacity': '0.95'
                    });
                }).on('mouseleave', function() {
                    $(this).css({
                        'transform': 'translateY(0)',
                        'box-shadow': '0 1px 3px rgba(0, 0, 0, 0.12)',
                        'opacity': '1'
                    });
                });
            });
            var ids = [];
            //delete
            $('#deleteSelected').on('click', function() {
                ids = [];
                $('.mCheckbox').each(function(i, chk) {
                    if (chk.checked) {
                        ids.push($(this).val());
                    }
                });
                // console.log(ids);
                if (ids.length > 0) {
                    $('#deleteModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'tidak ada data yang di pilih!',
                    })
                }
            });

            $('#deleteAll').on('click', function() {
                $('#deleteAllModal').modal('show');
            });

            $('#deleteAllButton').on('click', function(e) {
                e.preventDefault();
                var periode = $('#filterPeriode').val();

                if (periode) {
                    $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Menghapus..');
                    $('#deleteAllButton').prop('disabled', true);
                    var url = '<?= base_url("invoices/delete-all") ?>';
                    var data = {
                        'periode': periode
                    };
                    $.ajax({
                        data: data,
                        url: url,
                        type: "DELETE",
                        dataType: 'json',
                        success: function(data) {
                            $('#deleteAllModal').modal('hide');
                            table.draw(false);
                            getWidgetInvoice();
                            $('#deleteAllButton').html("Hapus");
                            $('#deleteAllButton').prop('disabled', false);
                            showNotif(data.status, data.title, data.message);
                        },
                        error: function(data) {
                            $('#deleteAllModal').modal('hide');
                            $('#deleteAllButton').html("Hapus");
                            $('#deleteAllButton').prop('disabled', false);
                            showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                        }
                    });
                }
            });

            $('#okButton').on('click', function(e) {
                e.preventDefault();
                if (ids.length > 0) {
                    $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Menghapus..');
                    $('#okButton').prop('disabled', true);
                    var url = '<?= base_url("invoices/delete") ?>';
                    $.ajax({
                        data: {
                            'id': ids
                        },
                        url: url,
                        type: "DELETE",
                        dataType: 'json',
                        success: function(data) {
                            $('#deleteModal').modal('hide');
                            table.draw(false);
                            getWidgetInvoice();
                            $('#okButton').html("Hapus");
                            $('#okButton').prop('disabled', false);
                            showNotif(data.status, data.title, data.message);
                        },
                        error: function(data) {
                            $('#deleteModal').modal('hide');
                            $('#okButton').html("Hapus");
                            $('#okButton').prop('disabled', false);
                            showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                        }
                    });
                }
            });
            $('.cancelDelete').on('click', function() {
                ids = [];
            });

            $('#paySelected').on('click', function() {
                $('#multiPay').trigger("reset");
                $('.m_bank').hide();
                $('.m_receiver').hide();
                ids = [];
                $('.mCheckbox').each(function(i, chk) {
                    if (chk.checked) {
                        ids.push($(this).val());
                    }
                });
                // Ambil data bank dari endpoint master bank (AJAX)
                if (ids.length > 0) {
                    // Ganti URL sesuai endpoint bank master Anda
                    $.ajax({
                        url: "<?= base_url('master-bank/list-json') ?>",
                        type: "GET",
                        dataType: 'json',
                        success: function(data) {
                            var $bankSelect = $('#m_bank');
                            $bankSelect.empty();
                            $bankSelect.append('<option value="">Pilih Bank</option>');
                            if (data && data.length > 0) {
                                data.forEach(function(bank) {
                                    $bankSelect.append('<option value="' + bank.id + '">' + bank.nama_bank + '</option>');
                                });
                            }
                            $('#multiPayModal').modal('show');
                        },
                        error: function() {
                            // Fallback jika gagal, tetap tampilkan modal
                            $('#multiPayModal').modal('show');
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'tidak ada data yang di pilih!',
                    })
                }
            });

            $('#saveMultipayment').click(function(e) {
                e.preventDefault();
                let valid = true;
                $('#errorpaymentMethod').hide();
                $('#paymentMethod').removeClass(' is-invalid');
                $('#errorbank').hide();
                $('#bank').removeClass(' is-invalid');
                $('#errorreceiver').hide();
                $('#receiver').removeClass(' is-invalid');
                var method = $("#m_paymentMethod").val();

                if (method == '') {
                    $('#errorm_paymentMethod').show().html("Masukan metode pembayaran diperlukan");
                    $('#m_paymentMethod').addClass(' is-invalid');
                    valid = false;
                }
                if ($('#m_paymentMethod').val() == 'bank transfer') {
                    if ($('#m_bank').val() == '') {
                        $('#errorm_bank').show().html("Masukan bank diperlukan");
                        $('#m_bank').addClass(' is-invalid');
                        valid = false;
                    }
                }
                if ($('#m_paymentMethod').val() == 'cash') {
                    if ($('#m_receiver').val() == '') {
                        $('#errorm_receiver').show().html("Masukan penerima diperlukan");
                        $('#m_receiver').addClass(' is-invalid');
                        valid = false;
                    }
                }
                if (valid) {
                    $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Sending..');
                    this.disabled = true;

                    var url = '<?= base_url("invoices/multi-payment") ?>';
                    $.ajax({
                        data: {
                            'm_paymentMethod': $('#m_paymentMethod').val(),
                            'm_bank': $('#m_bank').val(),
                            'm_receiver': $('#m_receiver').val(),
                            'ids': ids
                        },
                        url: url,
                        type: "POST",
                        dataType: 'json',
                        success: function(data) {
                            // console.log(data);
                            $('#multiPay').trigger("reset");
                            $('#multiPayModal').modal('hide');
                            table.draw(false);
                            getWidgetInvoice();
                            showNotif(data.status, data.title, data.message);
                            $('#saveMultipayment').html("Bayar");
                            $('#saveMultipayment').prop('disabled', false);
                        },
                        error: function(data) {
                            $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
                            $('#saveMultipayment').prop('disabled', false);
                            $('#saveMultipayment').html("Bayar");
                            showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                        }
                    });
                }
            });

            $('#m_paymentMethod').on('change', function() {
                if ($('#m_paymentMethod').val() == 'bank transfer') {
                    // Ambil ulang data bank setiap kali user pilih bank transfer
                    $.ajax({
                        url: "<?= base_url('master-bank/list-json') ?>",
                        type: "GET",
                        dataType: 'json',
                        success: function(data) {
                            var $bankSelect = $('#m_bank');
                            $bankSelect.empty();
                            $bankSelect.append('<option value="">Pilih Bank</option>');
                            if (data && data.length > 0) {
                                data.forEach(function(bank) {
                                    $bankSelect.append('<option value="' + bank.id + '">' + bank.bank_name + '</option>');
                                });
                            }
                            $('.m_bank').fadeIn();
                            $('.m_receiver').hide();
                            $('#m_receiver').val('');
                        },
                        error: function() {
                            $('.m_bank').fadeIn();
                            $('.m_receiver').hide();
                            $('#m_receiver').val('');
                        }
                    });
                } else {
                    $('#m_bank').val('');
                    $('.m_bank').hide();
                    $('.m_receiver').fadeIn();
                }
            });

            $("#checkAll").click(function() {
                var cells = table.column(0).nodes(), // Cells from 1st column
                    state = this.checked;
                for (var i = 0; i < cells.length; i += 1) {
                    cells[i].querySelector("input[type='checkbox']").checked = state;
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
                        var url = "<?= base_url('transaction/invoices') ?>/" + id;

                        $.ajax({
                            type: "DELETE",
                            url: url,
                            dataType: 'json',
                            success: function(data) {
                                table.draw(false);
                                getWidgetInvoice();
                                showNotif(data.status, data.title, data.message);
                            },
                            error: function(data) {
                                showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                                // console.log('Error:', data);
                            }
                        });
                    }
                })
            });

            $("#filterPackage").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Pilih Paket Internet",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterPackage').on('change', function() {
                table.draw(false);
            });
            // $('#filterPackage').trigger('change');

            $("#filterServer").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Pilih Lokasi Server",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterServer').on('change', function() {
                table.draw(false);
            });
            $("#filterNewCustomer").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Filter untuk Pelanggan baru",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterNewCustomer').on('change', function() {
                table.draw(false);
            });
            // $('#filterServer').trigger('change');

            $("#filterDistrict").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Pilih Kecamatan",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterDistrict').on('change', function() {
                table.draw(false);
            });
            // $('#filterDistrict').trigger('change');

            $("#filterVillage").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Pilih Desa",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterVillage').on('change', function() {
                table.draw(false);
            });
            // $('#filterVillage').trigger('change');

            $("#filterStatus").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Pilih Status",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterStatus').on('change', function() {
                table.draw(false);
            });
            // $('#filterStatus').trigger('change');

            $("#filterDueDate").select2({
                minimumResultsForSearch: 1 / 0,
                placeholder: "Jatuh Tempo",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterDueDate').on('change', function() {
                table.draw(false);
            });
            // $('#filterDueDate').trigger('change');

            $("#filterPeriode").select2({
                placeholder: "Pilih Periode",
                formatNoMatches: function() {
                    return "Tidak ada data yang ditemukan";
                }
            });
            $('#filterPeriode').on('change', function() {
                var selectedVal = $(this).val();
                $('#filterPeriodeTop').val(selectedVal);
                updateAlertPeriode(selectedVal);
                table.draw(false);
                getWidgetInvoice();
            });

            // Sync filterPeriodeTop with filterPeriode and directly trigger table refresh
            $('#filterPeriodeTop').on('change', function() {
                var selectedVal = $(this).val();
                console.log('filterPeriodeTop changed to:', selectedVal);
                $('#filterPeriode').val(selectedVal);
                updateAlertPeriode(selectedVal);
                table.draw(false);
                getWidgetInvoice();
            });

            // Manual Paid button
            $('#manualPaidBtn').on('click', function() {
                $('#manualPaidModal').modal('show');
                loadCustomersForManualPaid();
            });

            // Filter button (toggle filter visibility)
            $('#filterBtn').on('click', function() {
                $('#advancedFilters').slideToggle();
            });

            // $('#filterPeriode').trigger('change');

            $('#resetFilter').on('click', function() {
                $('#filterPackage').val('').trigger('change');
                $('#filterServer').val('').trigger('change');
                $('#filterDistrict').val('').trigger('change');
                $('#filterVillage').val('').trigger('change');
                $('#filterStatus').val('').trigger('change');
                // $('#filterPeriode').val('').trigger('change');
                table.draw(false);
                getWidgetInvoice();
            });

            $('.generateBills').click(function(e) {
                var type = $(this).data("type");
                e.preventDefault();
                this.disabled = true;
                var url = "<?= base_url('transaction/invoices/generate') ?>";
                var periode = '';
                if (type === 'now') {
                    periode = $('#filterPeriode').val();
                } else if (type === 'next') {
                    var nextOpt = $('#filterPeriode option:selected').next('option').val();
                    periode = nextOpt || $('#filterPeriode').val();
                }
                if (!periode || periode === '0') {
                    showNotif('error', 'Generate Tagihan', 'Silakan pilih periode terlebih dahulu!');
                    $('.generateBills').prop('disabled', false);
                    return;
                }
                var postData = {
                    periode: periode
                };
                $.ajax({
                    url: url,
                    type: "POST",
                    data: postData,
                    dataType: 'json',
                    success: function(data) {
                        showNotif(data.status, 'Generate Tagihan', data.message);
                        $('.generateBills').prop('disabled', false);
                        table.draw(false);
                        getWidgetInvoice();
                    },
                    error: function(xhr) {
                        $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
                        $('.generateBills').prop('disabled', false);
                        if (xhr.responseJSON) {
                            showNotif(xhr.responseJSON.status || 'error', 'Generate Tagihan', xhr.responseJSON.message || 'Terjadi kesalahan!');
                        } else {
                            showNotif('error', 'Generate Tagihan', 'Terjadi kesalahan!');
                        }
                    }
                });
            });

            $('#generateProrates').click(function(e) {
                e.preventDefault();
                $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Sending..');
                this.disabled = true;
                var url = '<?= base_url("invoices/generate-prorates") ?>';
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        showNotif(data.status, data.title, data.message);
                        $('#generateProrates').html("<i class='bx bx-detail label-icon'></i>Hasilkan tagihan prorata");
                        $('#generateProrates').prop('disabled', false);
                        table.draw(false);
                        getWidgetInvoice();
                    },
                    error: function(data) {
                        showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                        $('#generateProrates').html("<i class='bx bx-detail label-icon'></i>Hasilkan tagihan prorata");
                        $('#generateProrates').prop('disabled', false);
                    }
                });
            });

            //modal payment validation
            $('#myModal').on('shown.bs.modal', function() {
                $('#inputPayment').focus();
                // Tanggal pembayaran sudah diatur di event payInvoice
                console.log('Modal shown, current payment date:', $('#paymentDate').val());
            });

            // Event ketika modal ditutup - reset button dan form
            $('#myModal').on('hidden.bs.modal', function() {
                // Reset button save ke kondisi normal
                $('#saveBtn').html("Konfirmasi pembayaran");
                $('#saveBtn').prop('disabled', false);

                // Pastikan semua button action di tabel tetap aktif
                $('.btn-action').prop('disabled', false);
                $('.viewInvoice').prop('disabled', false);

                // Reset form dan error states
                $('#CreateForm').trigger("reset");
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').hide();

                console.log('Modal closed, button and form reset');
            }); // Event ketika modal pembayaran massal ditutup - reset button dan form
            $('#multiPayModal').on('hidden.bs.modal', function() {
                // Reset button save ke kondisi normal
                $('#saveMultipayment').html("Bayar");
                $('#saveMultipayment').prop('disabled', false);

                // Reset form dan error states
                $('#multiPay').trigger("reset");
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').hide();
                $('.m_bank, .m_receiver').hide();

                console.log('Multi-payment modal closed, button and form reset');
            });

            // Event ketika modal delete ditutup - reset button
            $('#deleteModal').on('hidden.bs.modal', function() {
                $('#okButton').html("Hapus");
                $('#okButton').prop('disabled', false);
                console.log('Delete modal closed, button reset');
            });

            // Event ketika modal delete all ditutup - reset button
            $('#deleteAllModal').on('hidden.bs.modal', function() {
                $('#deleteAllButton').html("Hapus");
                $('#deleteAllButton').prop('disabled', false);
                console.log('Delete all modal closed, button reset');
            });

            // Event untuk memastikan semua button ter-reset saat halaman dimuat ulang atau ditutup
            $(window).on('beforeunload', function() {
                resetAllButtons();
            });

            // Juga reset button saat halaman selesai dimuat
            $(document).ready(function() {
                resetAllButtons();

                // Set interval untuk memastikan button action selalu aktif
                setInterval(ensureButtonsActive, 500); // Check setiap 0.5 detik

                // Pastikan button aktif setiap kali tabel di-redraw
                table.on('draw.dt', function() {
                    setTimeout(ensureButtonsActive, 100);
                });

                // Check for new payment parameters from URL
                checkNewPaymentParams();
            });

            // Function to check URL parameters for new payment
            function checkNewPaymentParams() {
                const urlParams = new URLSearchParams(window.location.search);
                const newPayment = urlParams.get('new_payment');
                const customerId = urlParams.get('customer_id');
                const customerName = urlParams.get('customer_name');
                const serviceNo = urlParams.get('service_no');

                if (newPayment === '1' && customerId && customerName) {
                    // Auto open payment modal for the selected customer
                    setTimeout(function() {
                        openNewPaymentModal(customerId, customerName, serviceNo);
                        // Clear URL parameters
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }, 500);
                }
            }

            // Function to open payment modal for new payment
            function openNewPaymentModal(customerId, customerName, serviceNo) {
                // Reset and prepare payment modal
                $('#CreateForm').trigger("reset");
                $('.invalid-feedback').hide();
                $('.is-invalid').removeClass('is-invalid');

                // Set customer data
                $('#customer').val(customerName);
                $('#service_no').val(serviceNo);

                // Set current date
                var today = new Date();
                var dateString = today.getFullYear() + '-' +
                    String(today.getMonth() + 1).padStart(2, '0') + '-' +
                    String(today.getDate()).padStart(2, '0');
                $('#paymentDate').val(dateString);

                // Calculate arrears for this customer
                calculateArrears(customerId, null);

                // Set modal title and clear invoice fields for new payment
                $('#modelHeading').html('<i class="bx bx-receipt me-2"></i>Pembayaran Baru - ' + customerName);
                $('#invoice_no').val(''); // Clear invoice number for new payment
                $('#inputPackage').html('<span class="badge bg-info font-size-12">PEMBAYARAN BARU</span>');
                $('#inputStatus').html('<span class="badge bg-primary font-size-12">PEMBAYARAN BARU</span>');
                $('#periode').val('Pembayaran Manual');

                // Show modal
                $('#myModal').modal('show');
            }

            // Monitor perubahan pada field paymentDate
            $('#paymentDate').on('change', function() {
                console.log('Payment date changed to:', $(this).val());
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
                    if (!isNaN(n)) {
                        $(this).val(n.toLocaleString('id-ID'));
                    }
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

            $('body').on('click', '.viewInvoice', function() {
                var button = $(this);
                var invoiceId = button.data("id");

                // Show loading
                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');

                // Fetch invoice detail
                $.ajax({
                    url: "<?= base_url('transaction/invoices/get-detail') ?>",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        invoice_id: invoiceId,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success' && response.data) {
                            var invoice = response.data;
                            var transaction = response.transaction || {};

                            // Populate modal
                            $('#viewInvoiceId').text(invoice.id || '-');
                            $('#viewInvoiceCode').text(transaction.code || '-');
                            $('#viewRelatedId').text(invoice.invoice_no || '-');
                            $('#viewBranch').text(transaction.branch || invoice.branch || '-');

                            // Format date
                            var paymentDate = invoice.payment_date ? new Date(invoice.payment_date).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            }) : '-';
                            $('#viewDate').text(paymentDate);

                            $('#viewPaymentMethod').text(invoice.payment_method ? invoice.payment_method.toUpperCase() : '-');
                            $('#viewCategory').text(transaction.category || 'Pembayaran Invoice Manual');
                            $('#viewAttachment').text(transaction.attachment || '-');
                            $('#viewType').text(invoice.status === 'paid' ? 'In' : 'Pending');

                            // Format amount
                            var amount = parseInt(invoice.paid_amount || invoice.bill || 0);
                            $('#viewAmount').text('Rp. ' + amount.toLocaleString('id-ID'));

                            $('#viewTransaction').text('PAID ' + (invoice.customer_name || 'Customer') + ' ' + invoice.invoice_no);
                            $('#viewDescription').text(transaction.description || invoice.periode || '-');

                            // Format timestamps
                            var createdAt = invoice.created_at ? new Date(invoice.created_at).toLocaleString('id-ID', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : '-';
                            $('#viewCreatedAt').text(createdAt);
                            $('#viewCreatedBy').text(transaction.created_by_name || 'Admin');

                            var updatedAt = invoice.updated_at && invoice.updated_at !== invoice.created_at ?
                                new Date(invoice.updated_at).toLocaleString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) : 'beberapa detik yang lalu';
                            $('#viewUpdatedAt').text(updatedAt);
                            $('#viewUpdatedBy').text(transaction.updated_by_name || '-');

                            // Show modal
                            $('#invoiceDetailModal').modal('show');
                        } else {
                            showNotif('error', 'Error', response.message || 'Gagal memuat detail invoice');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading invoice detail:', xhr);
                        showNotif('error', 'Error', 'Gagal memuat detail invoice');
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="bx bx-show"></i>');
                    }
                });
            });

            function showPaymentModal(button) {
                // Debug data dari button
                console.log('Button data:', {
                    id: button.data('id'),
                    invoice_no: button.data('invoice_no'),
                    bill: button.data('bill'),
                    package: button.data('package'),
                    status: button.data('status'),
                    customer: button.data('customer'),
                    service_no: button.data('service_no'),
                    periode: button.data('periode')
                });

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

                // UPDATED: Cek dan set tanggal pembayaran (timestamp: <?= time() ?>)
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();
                var todayString = yyyy + '-' + mm + '-' + dd;

                // Force set the payment date
                $('#paymentDate').val(todayString);

                // Try alternative method if first doesn't work
                setTimeout(function() {
                    if ($('#paymentDate').val() !== todayString) {
                        console.log('Retrying to set payment date...');
                        $('#paymentDate').val(todayString);
                        $('#paymentDate').trigger('change');
                    }
                }, 100);

                // Set modal heading and invoice number
                var invoiceNo = button.data("invoice_no") || button.data("id") || '';
                $('#modelHeading').html('Detail Pembayaran');
                $('#invoice_no').val(invoiceNo);

                // Set invoice number in display
                $('#displayInvoiceNo').text(invoiceNo); // Format bill and payment as plain number (remove dots/commas)
                let billRaw = button.data("bill");
                let bill = (typeof billRaw === 'string') ? billRaw.replace(/\D/g, '') : billRaw;
                if (!bill || isNaN(bill)) bill = 0;

                // Convert to integer and format properly for millions
                let billAmount = parseInt(bill);
                let formattedBill = billAmount.toLocaleString('id-ID');

                // Debug logs untuk troubleshooting
                console.log('Bill calculation:', {
                    billRaw: billRaw,
                    bill: bill,
                    billAmount: billAmount,
                    formattedBill: formattedBill
                });

                // Show modal debug info
                console.log('Modal fields before setting:', {
                    inputBill: $('#inputBill').val(),
                    inputPayment: $('#inputPayment').val(),
                    service_no: $('#service_no').val(),
                    customer: $('#customer').val(),
                    periode: $('#periode').val()
                });

                // Set input values with proper formatting and trigger change events
                setTimeout(function() {
                    $('#inputBill').val(formattedBill).trigger('change');
                    $('#inputPayment').val(formattedBill).trigger('change');
                    $('#service_no').val(button.data("service_no") || '').trigger('change');
                    $('#customer').val(button.data("customer") || '').trigger('change');
                }, 100);

                // Debug log setelah set nilai
                console.log('Modal fields after setting:', {
                    inputBill: $('#inputBill').val(),
                    inputPayment: $('#inputPayment').val(),
                    service_no: $('#service_no').val(),
                    customer: $('#customer').val()
                });

                // Format periode as "Bulan YYYY" if possible
                var periodeRaw = button.data("periode") || '';
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
                $('#invoice_no').val(invoiceNo);
                $('#inputPackage').html('<span class="badge bg-info font-size-12">' + (button.data('package') || '-') + '</span>');

                // Set status with badge style
                if (button.data("status") == 'paid') {
                    $('#inputStatus').html('<span class="badge bg-success font-size-12">LUNAS</span>');
                } else {
                    $('#inputStatus').html('<span class="badge bg-warning font-size-12">BELUM LUNAS</span>');
                }

                // Calculate and set arrears (tunggakan) from unpaid invoices
                calculateArrears(button.data("customer_id"), button.data("id"));

                $('#id').val('');
                $('#myModal').modal('show');
            }

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
                $('#errorpaymentDate').hide();
                $('#paymentDate').removeClass(' is-invalid');
                $('#errorpaymentMethod').hide();
                $('#paymentMethod').removeClass(' is-invalid');
                $('#errorbank').hide();
                $('#bank').removeClass(' is-invalid');
                $('#errorreceiver').hide();
                $('#receiver').removeClass(' is-invalid');
                var bill = $("#inputBill").val().split(".").join("");
                var payment = $("#inputPayment").val().split(".").join("");
                var paymentDate = $("#paymentDate").val();
                var method = $("#paymentMethod").val();
                if (!payment.length) { // zero-length string AFTER a trim
                    $('#errorinputPayment').show().html("Masukan pembayaran diperlukan");
                    $('#inputPayment').addClass(' is-invalid');
                    valid = false;
                }
                if (!paymentDate.length) {
                    $('#errorpaymentDate').show().html("Masukan tanggal pembayaran diperlukan");
                    $('#paymentDate').addClass(' is-invalid');
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
                    var url = '<?= base_url("invoices/payment-confirmation") ?>';

                    // Manual data collection to ensure all fields are included
                    var formData = {
                        'invoice_no': $('#invoice_no').val(),
                        'inputBill': $('#inputBill').val(),
                        'inputPayment': $('#inputPayment').val(),
                        'paymentMethod': $('#paymentMethod').val(),
                        'bank': $('#bank').val(),
                        'receiver': $('#receiver').val(),
                        'arrears': $('#arrears').val(),
                        'paymentDate': $('#paymentDate').val(),
                        'service_no': $('#service_no').val(),
                        'customer': $('#customer').val(),
                        'csrf_interneter': $('input[name="csrf_interneter"]').val()
                    };

                    // Debug: Log form data before sending
                    console.log('Manual form data being sent:', formData);
                    console.log('Payment date value before submit:', $('#paymentDate').val());

                    $.ajax({
                        data: formData,
                        url: url,
                        type: "POST",
                        dataType: 'json',
                        success: function(data) {
                            $('#CreateForm').trigger("reset");
                            $('#myModal').modal('hide');
                            table.draw(false);
                            getWidgetInvoice();
                            showNotif(data.status, data.title, data.message);
                            // Redirect ke print invoice jika tersedia
                            if (data.print_invoice_url) {
                                // Redirect ke halaman invoice di tab yang sama
                                setTimeout(function() {
                                    window.location.href = data.print_invoice_url;
                                }, 1500);
                            }
                            $('#saveBtn').html("Konfirmasi pembayaran");
                            $('#saveBtn').prop('disabled', false);
                        },
                        error: function(data) {
                            $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
                            $('#saveBtn').html("Konfirmasi pembayaran");
                            $('#saveBtn').prop('disabled', false);
                            if (data.responseJSON) {
                                showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                            }
                        }
                    });
                }
            });

            //WhatsApp
            $('body').on('click', '#sendBillPaid', function(e) {
                e.preventDefault();
                var invoice = $(this).data("id");
                var url = "<?= base_url('transaction/invoices/whatsapp/sendBillPaid') ?>/" + invoice;
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        showNotif(data.status, data.title, data.message);
                        table.draw(false);
                        getWidgetInvoice();
                    },
                    error: function(data) {
                        showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                    }
                });
            });
            $('body').on('click', '#sendBillReminder', function(e) {
                e.preventDefault();
                var invoice = $(this).data("id");
                var url = "<?= base_url('transaction/invoices/whatsapp/sendBillReminder') ?>/" + invoice;
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        showNotif(data.status, data.title, data.message);

                        table.draw(false);
                        getWidgetInvoice();
                    },
                    error: function(data) {
                        showNotif(data.responseJSON.status, data.responseJSON.title, data.responseJSON.message);
                    }
                });
            });
            $('body').on('click', '.copyToClipboard', function(e) {
                e.preventDefault();
                var link = $(this).data("link");
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(link).select();
                document.execCommand("copy");
                $temp.remove();
                document.execCommand("copy");

                showNotif('success', 'Berhasil', 'Link Pembayaran berhasil di copy ke clipboard')
            }); // Copy Billing Link handler (new)
            $('body').on('click', '.copyBillingLink', function(e) {
                e.preventDefault();
                var serviceNumber = $(this).data("service");
                var billingLink = "<?= base_url() ?>" + serviceNumber;

                // Copy to clipboard
                navigator.clipboard.writeText(billingLink).then(function() {
                    showNotif('success', 'Berhasil', 'Link tagihan berhasil disalin ke clipboard');
                }).catch(function(err) {
                    // Fallback for older browsers
                    var $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(billingLink).select();
                    document.execCommand("copy");
                    $temp.remove();
                    showNotif('success', 'Berhasil', 'Link tagihan berhasil disalin ke clipboard');
                });
            });

            // Invoice History handler
            $('body').on('click', '.invoiceHistory', function(e) {
                e.preventDefault();
                var id = $(this).data("id");
                // You can implement history modal or redirect logic here
                // For now, let's show a simple notification
                showNotif('info', 'Riwayat', 'Menampilkan riwayat untuk invoice: ' + id);
                // Example: window.open('/invoices/history/' + id, '_blank');
            });

            function getWidgetInvoice() {
                // e.preventDefault();
                var url = '<?= base_url('transaction/invoices/widget/get-widget-invoice') ?>';
                var selectedPeriode = $('#filterPeriode').val();

                // Update alert text dengan periode yang dipilih
                updateAlertPeriode(selectedPeriode);

                $.ajax({
                    data: {
                        filterPeriode: selectedPeriode
                    },
                    url: url,
                    type: "GET",
                    dataType: 'json',
                    success: function(data) {
                        setTimeout(() => {
                            // console.log(data);
                            if (data.activeCustomers !== undefined) {
                                $('#w_activeCustomers').html(data.activeCustomers);
                            }
                            if (data.total !== undefined) {
                                $('#w_total').html(data.total);
                            }
                            if (data.totalInvoice !== undefined) {
                                $('#w_totalInvoice').html(data.totalInvoice);
                            }
                            if (data.invoiceNotGenerated !== undefined) {
                                $('#w_invoiceNotGenerated').html(data.invoiceNotGenerated);
                                $('#notGenerated').val(data.invoiceNotGenerated);
                            }

                            if (data.paidInvoice !== undefined) {
                                $('#w_paidInvoice').html(data.paidInvoice);
                            }
                            if (data.unpaidInvoice !== undefined) {
                                $('#w_unpaidInvoice').html(data.unpaidInvoice);
                            }
                            if (data.paidInvoiceAmount !== undefined) {
                                $('#w_paidInvoiceAmount').html(data.paidInvoiceAmount);
                            }
                            if (data.unpaidInvoiceAmount !== undefined) {
                                $('#w_unpaidInvoiceAmount').html(data.unpaidInvoiceAmount);
                            }
                        }, 200);
                    },
                    error: function(data) {
                        $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
                        // console.log('Error:', data);
                    }
                });
            }

            // Fungsi untuk load periode yang tersedia secara dinamis
            function loadAvailablePeriods() {
                $.ajax({
                    url: "<?= base_url('transaction/invoices/available-periods') ?>",
                    type: "GET",
                    dataType: 'json',
                    success: function(periods) {
                        var $filterPeriode = $('#filterPeriode');
                        var $filterPeriodeTop = $('#filterPeriodeTop');

                        // Clear existing options except "Tampilkan Semua"
                        $filterPeriode.find('option:not(:first)').remove();
                        $filterPeriodeTop.find('option:not(:first)').remove();

                        if (periods && periods.length > 0) {
                            var latestPeriod = null;

                            // Add options from periods data
                            periods.forEach(function(period, index) {
                                var selected = (index === 0) ? 'selected' : ''; // Select the first (latest) period
                                $filterPeriode.append('<option value="' + period.value + '" ' + selected + '>' + period.text + '</option>');
                                $filterPeriodeTop.append('<option value="' + period.value + '" ' + selected + '>' + period.text + '</option>');

                                if (index === 0) {
                                    latestPeriod = period.value;
                                }
                            });

                            // Update alert, widget, dan tabel dengan periode terbaru
                            if (latestPeriod) {
                                updateAlertPeriode(latestPeriod);
                                getWidgetInvoice();
                                if (typeof table !== 'undefined') {
                                    table.draw(false);
                                }
                            }
                        } else {
                            // Jika tidak ada data periode, tambahkan periode bulan ini sebagai fallback
                            var currentDate = new Date();
                            var currentYear = currentDate.getFullYear();
                            var currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                            var currentPeriod = currentYear + '-' + currentMonth;
                            var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                            ];
                            var monthName = monthNames[currentDate.getMonth()];

                            $filterPeriode.append('<option value="' + currentPeriod + '" selected>' + monthName + ' ' + currentYear + '</option>');
                            $filterPeriodeTop.append('<option value="' + currentPeriod + '" selected>' + monthName + ' ' + currentYear + '</option>');
                            updateAlertPeriode(currentPeriod);
                            getWidgetInvoice();
                        }

                        // Refresh select2 if it's already initialized
                        if ($filterPeriode.hasClass('select2-hidden-accessible')) {
                            $filterPeriode.trigger('change.select2');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading available periods:', error);

                        // Fallback: add current month period
                        var currentDate = new Date();
                        var currentYear = currentDate.getFullYear();
                        var currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                        var currentPeriod = currentYear + '-' + currentMonth;
                        var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];
                        var monthName = monthNames[currentDate.getMonth()];

                        $('#filterPeriode').append('<option value="' + currentPeriod + '" selected>' + monthName + ' ' + currentYear + '</option>');
                        $('#filterPeriodeTop').append('<option value="' + currentPeriod + '" selected>' + monthName + ' ' + currentYear + '</option>');
                        updateAlertPeriode(currentPeriod);
                        getWidgetInvoice();
                    }
                });
            }

            // Fungsi untuk update text alert dengan periode yang dipilih
            function updateAlertPeriode(periode) {
                var alertText = "Data pada widget mengacu pada data bulan berjalan";

                if (periode && periode !== '') {
                    // Parse periode (format: YYYY-MM)
                    var parts = periode.split('-');
                    if (parts.length === 2) {
                        var year = parts[0];
                        var month = parseInt(parts[1]);
                        var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];

                        if (month >= 1 && month <= 12) {
                            alertText = "Data pada widget mengacu pada periode (" + monthNames[month - 1] + "-" + year + ")";
                        }
                    }
                }

                $('#alertPeriodeText').text(alertText);
            }

            $('.notGenerated').click(function() {
                var value = $('#notGenerated').val();
                var url = '<?= base_url("invoices/widget/get-difference-invoice") ?>/' + value;
                // console.log(data);
                $.get(url, function(data) {
                    $('#table-difference').html(data);
                    $('#differenceInvoice').modal('show');
                });
            });
            // End of $(function() {
        }); // Close the main $(function() { block

        function showNotif(status, title, message) {
            let fullMessage = `<strong>${title}</strong><br>${message}`;

            if (status === 'success') {
                toastr.success(fullMessage);
            } else if (status === 'error') {
                toastr.error(fullMessage);
            } else if (status === 'warning') {
                toastr.warning(fullMessage);
            } else {
                toastr.info(fullMessage);
            }
        }

        // Fungsi helper untuk mereset semua button ke kondisi normal
        function resetAllButtons() {
            $('#saveBtn').prop('disabled', false).html("Konfirmasi pembayaran");
            $('#saveMultipayment').prop('disabled', false).html("Bayar");
            $('#okButton').prop('disabled', false).html("Hapus");
            $('#deleteAllButton').prop('disabled', false).html("Hapus");
            $('.generateBills').prop('disabled', false);
            $('#generateProrates').prop('disabled', false);
            ensureButtonsActive(); // Pastikan button action juga aktif
            console.log('All buttons reset to normal state');
        }

        // Override semua event yang mencoba men-disable button action
        $(document).on('click', '.btn-action', function() {
            $(this).prop('disabled', false);
        });

        // Override untuk button viewInvoice
        $(document).on('click', '.viewInvoice', function() {
            $(this).prop('disabled', false);
        });

        // Fungsi untuk memastikan button action selalu aktif tanpa override jQuery methods
        function ensureButtonsActive() {
            $('.btn-action, .viewInvoice').each(function() {
                if ($(this).prop('disabled') || $(this).attr('disabled')) {
                    $(this).prop('disabled', false).removeAttr('disabled');
                    console.log('Re-enabled button:', this);
                }
            });
        }
    </script>
    <script>
        let printCharacteristic = null;
        async function dot(btn) {
            let options = {};
            options.acceptAllDevices = true;
            // Ambil data dari tombol jika ada (untuk thermal print per-row)
            let statusText = '';
            let customerNo = '';
            let customerName = '';
            let customerPhone = '';
            let paket = '';
            let bill = '';
            let tarif = '';
            let serviceNo = '';
            let periode = '';
            let usagePeriod = '';
            let keterangan = '';
            let paymentUrl = '';
            if (btn && btn.getAttribute) {
                statusText = btn.getAttribute('data-status_text') || '';
                customerNo = btn.getAttribute('data-customer_no') || '';
                customerName = btn.getAttribute('data-customer_name') || '';
                customerPhone = btn.getAttribute('data-customer_phone') || '';
                paket = btn.getAttribute('data-paket') || '';
                bill = btn.getAttribute('data-bill') || '';
                tarif = btn.getAttribute('data-tarif') || bill;
                serviceNo = btn.getAttribute('data-service_no') || '';
                periode = btn.getAttribute('data-periode') || '';
                usagePeriod = btn.getAttribute('data-usage_period') || '';
                keterangan = btn.getAttribute('data-keterangan') || '';
                paymentUrl = btn.getAttribute('data-payment_url') || '';
            }
            if (printCharacteristic == null) {
                navigator.bluetooth.requestDevice({
                        filters: [{
                            services: ['000018f0-0000-1000-8000-00805f9b34fb']
                        }]
                    })
                    .then(device => device.gatt.connect())
                    .then(server => server.getPrimaryService("000018f0-0000-1000-8000-00805f9b34fb"))
                    .then(service => service.getCharacteristic("00002af1-0000-1000-8000-00805f9b34fb"))
                    .then(characteristic => {
                        printCharacteristic = characteristic;
                        sendPrinterData({
                            statusText,
                            customerNo,
                            customerName,
                            customerPhone,
                            paket,
                            bill,
                            tarif,
                            serviceNo,
                            periode,
                            usagePeriod,
                            keterangan,
                            paymentUrl
                        });
                    })
                    .catch(handleError);
            } else {
                sendPrinterData({
                    statusText,
                    customerNo,
                    customerName,
                    customerPhone,
                    paket,
                    bill,
                    tarif,
                    serviceNo,
                    periode,
                    usagePeriod,
                    keterangan,
                    paymentUrl
                });
            }
        }

        async function sendPrinterData(data = {}) {
            let encoder = new TextEncoder("utf-8");
            let centerfont = '\x1B' + '\x61' + '\x31';
            let normalfont = '\x1D' + '\x21' + '\x00';
            let left = '\x1B' + '\x61' + '\x30';
            let kanan = '\x1B' + '\x61' + '\x32';
            let boldON = '\x1B' + '\x45' + '\x0D';
            let boldOFF = '\x1B' + '\x45' + '\x0A';

            // Data dinamis dari PHP
            let perusahaan = "<?= addslashes(strtoupper(getCompanyData()['name'] ?? 'PT. KIMONET DIGITAL SYNERGY')) ?>";
            let slogan = "<?= addslashes(getCompanyData()['tagline'] ?? 'Dari Kita, Untuk Konektivitas Nusantara') ?>";
            let alamatusaha = "<?= addslashes(getCompanyData()['address'] ?? 'Dusun Lebo Kulon Rt02/rw08') ?>";
            let telpusaha = "Telp <?= addslashes(getCompanyData()['phone'] ?? '085183112127') ?>";
            let kota_kantor = "<?= addslashes(getCompanyData()['city'] ?? 'Batang') ?>";
            let email = "<?= addslashes(getCompanyData()['website'] ?? 'www.kimonet.my.id') ?>";
            let tgl = "TANGGAL   : <?= date('d-m-Y H:i:s') ?>";

            // Gunakan data dari tombol jika ada, jika tidak fallback ke PHP
            let pesan = "NOPEL     : " + (data.customerNo || "<?= isset($invoice) ? esc($invoice->customer_no) : '' ?>");
            let layanan = "NO LAYANAN: " + (data.serviceNo || "<?= isset($invoice) ? esc($invoice->service_no ?? '') : '' ?>");
            let nama = "NAMA      : " + (data.customerName || "<?= isset($invoice) ? esc($invoice->customer_name) : '' ?>");
            let phone = "TELP      : " + (data.customerPhone || "<?= isset($invoice) ? esc($invoice->customer_phone) : '' ?>");
            let paket = "PAKET     : " + (data.paket || "<?= isset($invoice) ? esc($invoice->paket) : '' ?>");
            let periode = "PERIODE   : " + (data.periode || "<?php if (isset($invoice) && isset($invoice->periode)) {
                                                                    $bulan_indo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                                    $date = strtotime($invoice->periode . '-01');
                                                                    $bulan_num = (int)date('n', $date);
                                                                    $tahun = date('Y', $date);
                                                                    echo strtoupper($bulan_indo[$bulan_num - 1] . ' ' . $tahun);
                                                                } else {
                                                                    echo '';
                                                                } ?>");
            let pemakaian = "PEMAKAIAN : " + (data.usagePeriod || "<?= isset($invoice) ? esc($invoice->usage_period) : '' ?>");
            let tarif = "TARIF/BLN : Rp " + (data.tarif ? parseInt(data.tarif).toLocaleString('id-ID') : "<?= isset($invoice) ? number_format((int) $invoice->bill, 0, ',', '.') : '' ?>");
            let pembayaran = "TOTAL TAG : Rp " + (data.bill ? parseInt(data.bill).toLocaleString('id-ID') : "<?= isset($invoice) ? number_format((int) $invoice->bill, 0, ',', '.') : '' ?>");
            let keterangan = "CATATAN   : " + (data.keterangan || "<?= isset($invoice) ? esc($invoice->keterangan ?? '-') : '' ?>");

            let judul = centerfont + " " + boldON + "BUKTI PEMBAYARAN" + boldOFF + " ";
            let footer = centerfont + "\nTerima kasih";
            let kasir = kanan + "Kasir,\n\n";
            let namakasir = kanan + "[ OFFICE ]\n";

            // Print urutan
            await printCharacteristic.writeValue(encoder.encode(perusahaan + "\n"));
            await printCharacteristic.writeValue(encoder.encode(slogan + "\n"));
            await printCharacteristic.writeValue(encoder.encode(telpusaha + "\n"));
            await printCharacteristic.writeValue(encoder.encode(alamatusaha + "\n"));
            await printCharacteristic.writeValue(encoder.encode(kota_kantor + "\n\n"));
            await printCharacteristic.writeValue(encoder.encode(judul + "\n\n"));
            await printCharacteristic.writeValue(encoder.encode(left + tgl + "\n"));
            await printCharacteristic.writeValue(encoder.encode(pesan + "\n"));
            await printCharacteristic.writeValue(encoder.encode(layanan + "\n"));
            await printCharacteristic.writeValue(encoder.encode(nama + "\n"));
            await printCharacteristic.writeValue(encoder.encode(phone + "\n"));
            await printCharacteristic.writeValue(encoder.encode(paket + "\n"));
            await printCharacteristic.writeValue(encoder.encode(periode + "\n"));
            await printCharacteristic.writeValue(encoder.encode(pemakaian + "\n"));
            await printCharacteristic.writeValue(encoder.encode(tarif + "\n"));
            await printCharacteristic.writeValue(encoder.encode(pembayaran + "\n"));
            await printCharacteristic.writeValue(encoder.encode(keterangan + "\n"));
            // Tambahkan status lunas/belum lunas
            if (data.statusText) {
                await printCharacteristic.writeValue(encoder.encode(centerfont + "\nSTATUS : " + data.statusText + "\n"));
            }
            await printCharacteristic.writeValue(encoder.encode(kasir));
            await printCharacteristic.writeValue(encoder.encode("\n" + namakasir));

            // Rekening pembayaran
            await printCharacteristic.writeValue(encoder.encode(left + "\nRekening Pembayaran Transfer :\n"));
            <?php if (!empty($activeBanks)): ?>
                <?php foreach ($activeBanks as $bank): ?>
                    await printCharacteristic.writeValue(encoder.encode("<?= addslashes(strtoupper($bank['bank_name'])) ?>\n"));
                    await printCharacteristic.writeValue(encoder.encode("<?= addslashes($bank['account_number']) ?>-<?= addslashes($bank['account_holder']) ?>\n"));
                <?php endforeach; ?>
            <?php else: ?>
                await printCharacteristic.writeValue(encoder.encode("Tidak ada rekening bank aktif\n"));
            <?php endif; ?>

            let ol = '\nPembayaran Online :\nKamu bisa mengunakan QRIS,VIRTUAL AKUN,INDOMARET dan ALFAMART Kunjungi :\n' + (data.paymentUrl || "<?= base_url() ?>");
            await printCharacteristic.writeValue(encoder.encode(ol + "\n"));
            let text = encoder.encode(footer + "\n" + "\n" + '\u000A\u000D');
            return printCharacteristic.writeValue(text).then(() => {
                console.log('Write done.');
            }).catch(error => {
                handleError(error);
            });
        }

        function handleError(error) {
            printCharacteristic = null;
            alert(error);
        }

        // Function to calculate arrears from unpaid invoices
        function calculateArrears(customerId, currentInvoiceId) {
            if (!customerId) {
                $('#arrears').val('0');
                return;
            }

            $.ajax({
                url: "<?= base_url('transaction/invoices/get-unpaid-total') ?>",
                type: "POST",
                dataType: 'json',
                data: {
                    customer_id: customerId,
                    exclude_invoice_id: currentInvoiceId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        var totalArrears = response.total_unpaid || 0;
                        var formattedArrears = parseInt(totalArrears).toLocaleString('id');
                        $('#arrears').val(formattedArrears);

                        console.log('Tunggakan calculated for customer ' + customerId + ': ' + formattedArrears);
                    } else {
                        $('#arrears').val('0');
                        console.error('Error calculating arrears:', response.message);
                    }
                },
                error: function() {
                    $('#arrears').val('0');
                    console.error('Failed to calculate arrears');
                }
            });
        }

        // Ambil data bank dari master bank saat modal pembayaran dibuka
        $('#myModal').on('show.bs.modal', function() {
            var $bankSelect = $('#m_bank');
            $bankSelect.empty();
            $bankSelect.append('<option value="">Pilih Bank</option>');
            $.ajax({
                url: "<?= base_url('master-bank/list-json') ?>",
                type: "GET",
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        data.forEach(function(bank) {
                            $bankSelect.append('<option value="' + bank.id + '">' + bank.bank_name + '</option>');
                        });
                    }
                }
            });
        });

        // Manual Paid Modal Functions
        function loadCustomersForManualPaid() {
            var $customerSelect = $('#manualPaidCustomer');
            $customerSelect.empty();
            $customerSelect.append('<option value="">Pilih Customer</option>');

            $.ajax({
                url: "<?= base_url('customer/getCustomerOptions') ?>",
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        response.data.forEach(function(customer) {
                            $customerSelect.append('<option value="' + customer.id + '">' + customer.nama_pelanggan + ' - ' + customer.nomor_layanan + '</option>');
                        });
                    } else {
                        showNotif('info', 'Info', 'Tidak ada customer aktif');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading customers:', xhr);
                    showNotif('error', 'Error', 'Gagal memuat data customer');
                }
            });
        }

        // Load invoices when customer is selected
        $('#manualPaidCustomer').on('change', function() {
            var customerId = $(this).val();
            var $invoiceSelect = $('#manualPaidInvoice');

            $invoiceSelect.empty().append('<option value="">✓ Select Invoice</option>');
            $('#manualPaidTotal').text('Rp.0');

            if (customerId) {
                $invoiceSelect.prop('disabled', false);

                // Show loading state
                $invoiceSelect.append('<option value="">Loading...</option>');

                $.ajax({
                    url: "<?= base_url('transaction/invoices/get-by-customer') ?>",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        customer_id: customerId,
                        status: 'unpaid',
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        console.log('Invoice response:', response);

                        $invoiceSelect.empty().append('<option value="">✓ Select Invoice</option>');

                        if (response.status === 'success') {
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(function(invoice) {
                                    console.log('Invoice item:', invoice);
                                    var billFormatted = parseInt(invoice.total || invoice.bill).toLocaleString('id-ID');

                                    // Format periode ke bahasa Indonesia
                                    var periodeFormatted = invoice.periode;
                                    if (invoice.periode && /^\d{4}-\d{2}$/.test(invoice.periode)) {
                                        var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        var parts = invoice.periode.split('-');
                                        var year = parseInt(parts[0]);
                                        var month = parseInt(parts[1]) - 1;
                                        if (month >= 0 && month < 12) {
                                            periodeFormatted = monthNames[month] + ' ' + year;
                                        }
                                    }

                                    var invoiceText = invoice.invoice_no + ' | ' + periodeFormatted + ' | Rp.' + billFormatted;
                                    $invoiceSelect.append('<option value="' + invoice.id + '" data-bill="' + (invoice.total || invoice.bill) + '">' + invoiceText + '</option>');
                                });
                            } else {
                                $invoiceSelect.append('<option value="">Tidak ada tagihan unpaid</option>');
                                console.log('No unpaid invoices found');
                            }
                        } else {
                            $invoiceSelect.append('<option value="">Error: ' + (response.message || 'Unknown error') + '</option>');
                            console.error('Error response:', response);
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX Error loading invoices:', xhr);
                        console.error('Response text:', xhr.responseText);

                        $invoiceSelect.empty().append('<option value="">✓ Select Invoice</option>');

                        var message = 'Gagal memuat data invoice';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showNotif('error', 'Error', message);
                        $invoiceSelect.append('<option value="">Error loading invoices</option>');
                    }
                });
            } else {
                $invoiceSelect.prop('disabled', true);
            }
        });

        // Update total when invoice is selected
        $('#manualPaidInvoice').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var bill = selectedOption.data('bill');

            if (bill) {
                var billFormatted = 'Rp.' + parseInt(bill).toLocaleString('id-ID');
                $('#manualPaidTotal').text(billFormatted);
            } else {
                $('#manualPaidTotal').text('Rp.0');
            }
        });

        // Handle manual paid form submission
        $('#manualPaidForm').on('submit', function(e) {
            e.preventDefault();

            var customerId = $('#manualPaidCustomer').val();
            var invoiceId = $('#manualPaidInvoice').val();

            if (!customerId) {
                showNotif('warning', 'Peringatan', 'Silakan pilih customer terlebih dahulu');
                return;
            }

            if (!invoiceId) {
                showNotif('warning', 'Peringatan', 'Silakan pilih invoice terlebih dahulu');
                return;
            }

            var $submitBtn = $('#manualPaidSubmitBtn');
            $submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');

            $.ajax({
                url: "<?= base_url('transaction/invoices/manual-paid') ?>",
                type: "POST",
                dataType: 'json',
                data: {
                    invoice_id: invoiceId,
                    customer_id: customerId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showNotif('success', 'Berhasil', response.message || 'Invoice berhasil dipaid secara manual');
                        $('#manualPaidModal').modal('hide');
                        $('#manualPaidForm')[0].reset();
                        $('#manualPaidInvoice').prop('disabled', true);
                        $('#manualPaidTotal').text('Rp.0');
                        table.draw(false);
                        getWidgetInvoice();
                    } else {
                        showNotif('error', 'Gagal', response.message || 'Gagal melakukan manual paid');
                    }
                },
                error: function(xhr) {
                    var message = 'Terjadi kesalahan saat melakukan manual paid';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showNotif('error', 'Error', message);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html('<i class="bx bx-check me-1"></i>Force Paid');
                }
            });
        });

        // Reset modal when closed
        $('#manualPaidModal').on('hidden.bs.modal', function() {
            $('#manualPaidForm')[0].reset();
            $('#manualPaidInvoice').prop('disabled', true).empty().append('<option value="">✓ Select Invoice</option>');
            $('#manualPaidTotal').text('Rp.0');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
        });
    </script>
    <?= $this->endSection() ?>

    <?= $this->section('styles') ?>
    <style>
        /* DataTable Header Alignment Fix */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: flex;
            align-items: center;
        }
    </style>
    <?= $this->endSection() ?>