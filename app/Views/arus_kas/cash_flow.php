<style>
    /* NUCLEAR APPROACH: HIDE ALL LOADING ELEMENTS AND ANIMATIONS */
    .loading,
    .spinner,
    .spin,
    [class*="spin"],
    [class*="loading"],
    .select2-selection__loading,
    .select2-loading-results,
    [class*="hourglass"],
    [class*="rotate"],
    [class*="animation"],
    .fa-spin,
    .bx-spin,
    .mdi-spin,
    .ti-spin,
    *[class*="loading"]::before,
    *[class*="loading"]::after,
    *[class*="spin"]::before,
    *[class*="spin"]::after,
    .modal .loading,
    .modal .spinner,
    .modal [class*="spin"],
    #myModal .loading,
    #myModal .spinner,
    #myModal [class*="spin"],
    [data-loading],
    [data-spinner],
    [data-spin] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        animation: none !important;
        transform: none !important;
        -webkit-animation: none !important;
        -moz-animation: none !important;
        -o-animation: none !important;
    }

    /* Block all CSS animations and transforms globally */
    * {
        animation-duration: 0s !important;
        animation-delay: 0s !important;
        transition-duration: 0s !important;
    }

    /* Force specific icons to not spin */
    .bx-hourglass,
    .fa-hourglass,
    .mdi-hourglass {
        animation: none !important;
        transform: none !important;
    }

    /* NUCLEAR: Hide global preloader completely */
    #preloader,
    .preloader,
    .preloader-content,
    .preloader-logo {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        z-index: -1 !important;
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
    }

    /* Remove preloader-active class effects */
    body.preloader-active {
        overflow: visible !important;
    }

    /* Agar pagination selalu di kanan bawah dan tidak ikut scroll */
    .dataTables_wrapper {
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right !important;
        text-align: right !important;
        margin-top: 10px;
    }

    .dataTables_wrapper .dataTables_info {
        float: left !important;
        margin-top: 10px;
    }

    /* Wrap untuk kolom keterangan */
    .text-wrap {
        white-space: normal !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        max-width: 500px !important;
        min-width: 300px !important;
        width: 400px !important;
    }

    /* Target khusus untuk kolom keterangan di tabel */
    .my_datatable td.text-wrap {
        max-width: 500px !important;
        min-width: 300px !important;
        width: 400px !important;
    }

    /* Header kolom keterangan */
    .my_datatable th:nth-child(5) {
        width: 400px !important;
        min-width: 300px !important;
    }

    @media (max-width: 767px) {

        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_info {
            float: none !important;
            text-align: center !important;
        }
    }
</style>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Data Kas Komprehensif</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Arus Kas</a></li>
                            <li class="breadcrumb-item active">Data Kas Lengkap</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form id="changeMonth">
                        <div class="float-end col-md-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control select2-search-disable" name="month" id="month">
                                        <!-- Bulan akan diisi secara dinamis oleh JS -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-control select2-search-disable" name="year" id="year">
                                        <!-- Tahun akan diisi secara dinamis oleh JS -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <h4 class="card-title">Filter Periode</h4>
                        <h6 class="card-subtitle font-14 text-muted">
                            Filter data kas berdasarkan periode (menampilkan semua sumber: manual dan invoice)
                        </h6>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Total Pendapatan</p>
                                <h4 class="mb-0" id="w_income"></h4>
                                <small class="text-muted">Kas + Invoice + Gateway</small>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                    <span class="avatar-title rounded-circle bg-success">
                                        <i class="bx bx bx-trending-up font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Saldo Bersih</p>
                                <h4 class="mb-0" id="w_balances"></h4>
                                <small class="text-muted">Pendapatan - Pengeluaran</small>
                            </div>

                            <div class="flex-shrink-0 align-self-center ">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="bx bx bx-wallet-alt font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Total Pengeluaran</p>
                                <h4 class="mb-0" id="w_expenditure"></h4>
                                <small class="text-muted">Manual Entry Kas</small>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-danger mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-danger">
                                        <i class="bx bx bx-trending-down font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12.text-right">
                            <a href="javascript:void(0)" id="createNewData" class="btn btn-primary waves-effect btn-label waves-light">
                                <i class="bx bx-plus label-icon"></i>
                                Data Kas
                            </a>
                        </div>
                        <div class="text-md-right text-lg-end col-md-6">
                            <button type="button" id="deleteAll" data-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                data-bs-original-title="Hapus semua data kas berdasarkan filter periode aktif sekarang" class="mb-2 btn btn-danger waves-effect btn-label waves-light">
                                <i class="bx bx-trash label-icon"></i>
                                Hapus Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped table-hover align-middle table-bordered my_datatable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama / Sumber</th>
                                    <th>Tanggal</th>
                                    <th>Kategori / Tipe</th>
                                    <th>Keterangan</th>
                                    <th>Pendapatan</th>
                                    <th>Pengeluaran</th>
                                    <th width="100px">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modelHeading">Tambah Data Kas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="CreateForm" name="CreateForm" class="form-horizontal">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="nama" class="col-form-label">Nama Data Kas<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Data Kas">
                                        <input type="hidden" class="form-control" id="id" name="id" value="">
                                        <span id="errornama" class="invalid-feedback text-danger" role="alert" style="display: none;">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="amount" class="col-form-label">Jumlah<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="amount" name="amount" placeholder="Jumlah">
                                        <span id="erroramount" class="invalid-feedback text-danger" role="alert" style="display: none;">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="transaction_date" class="col-form-label">
                                            Tanggal Transaksi
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group" id="datepicker2">
                                            <input type="text" class="form-control datepicker-top" placeholder="dd/mm/yyyy"
                                                data-date-format="dd/mm/yyyy" data-date-container='#datepicker2'
                                                data-provide="datepicker" data-date-autoclose="true" id="transaction_date"
                                                name="transaction_date" autocomplete="off">
                                            <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                        </div>
                                        <span id="error_transaction_date" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="customer_cash_flow_category_id" class="col-form-label">Kategori Kas<span class="text-danger">*</span></label>
                                        <select class="form-control" name="category_id" id="customer_cash_flow_category_id" style="background: white; border: 1px solid #ced4da;">
                                            <option value="">Pilih Kategori Kas</option>
                                            <?php if (isset($categories) && is_array($categories)): ?>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= esc($cat['id_category']) ?>"><?= esc($cat['nama']) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <span id="error_customer_cash_flow_category_id" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="description" class="col-form-label">Keterangan<span class="text-danger">*</span></label>
                                        <textarea type="text" class="form-control" id="description" name="description" placeholder="Keterangan"></textarea>
                                        <span id="error_description" class="invalid-feedback text-danger" role="alert" style="display: none;">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="saveBtn" class="btn btn-primary" value="create">Buat baru</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                        <?= csrf_field() ?>
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteAllModalLabel">Hapus Semua Data Kas Berdasarkan Filter Periode Aktif</h5>
                            <button type="button" class="btn-close cancelDelete" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin akan menghapus data kas berdasarkan filter periode aktif ?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light cancelDelete" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="deleteAllButton">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->
<script>
    let table; // Declare table variable in global scope

    $(function() {
        // Initialize datatable menggunakan initServerSideDataTable seperti mutasi keuangan
        table = initServerSideDataTable('.my_datatable', {
            scrollX: true,
            pageLength: 10, // Set pagination menjadi 10 per halaman
            ajax: {
                url: "/arus_kas/data",
                type: 'GET',
                data: function(d) {
                    d.month = $('#month').val();
                    d.year = $('#year').val();
                    // CSRF for DataTables
                    var csrfName = $('#CreateForm input[type="hidden"][name^="csrf"]').attr('name');
                    var csrfHash = $('#CreateForm input[type="hidden"][name^="csrf"]').val();
                    if (csrfName && csrfHash) {
                        d[csrfName] = csrfHash;
                    }
                },
                dataSrc: function(json) {
                    // Update widget summary
                    if (json.summary) {
                        $('#w_income').html('<span class="text-success fw-bold">Rp ' + (json.summary.income || 0).toLocaleString('id') + '</span>');
                        $('#w_expenditure').html('<span class="text-danger fw-bold">Rp ' + (json.summary.expenditure || 0).toLocaleString('id') + '</span>');
                        $('#w_balances').html('<span class="text-primary fw-bold">Rp ' + (json.summary.saldo || 0).toLocaleString('id') + '</span>');
                    } else {
                        $('#w_income').html('<span class="text-success fw-bold">Rp 0</span>');
                        $('#w_expenditure').html('<span class="text-danger fw-bold">Rp 0</span>');
                        $('#w_balances').html('<span class="text-primary fw-bold">Rp 0</span>');
                    }
                    return json.data;
                }
            },
            order: [
                [2, 'desc']
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'transaction_date',
                    name: 'transaction_date',
                    searchable: false,
                    render: function(data, type, row) {
                        if (!data) return '';
                        var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        var parts = data.split('-');
                        if (parts.length !== 3) return data;
                        var year = parseInt(parts[0], 10);
                        var month = parseInt(parts[1], 10) - 1;
                        var day = parseInt(parts[2], 10);
                        var dateObj = new Date(year, month, day);
                        var dayName = days[dateObj.getDay()];
                        var monthName = months[month];
                        var dayStr = (day < 10 ? '0' : '') + day;
                        return dayName + ', ' + dayStr + ' ' + monthName + ' ' + year;
                    }
                },
                {
                    data: 'category_name',
                    name: 'category_name',
                    searchable: false,
                    render: function(data, type, row) {
                        if (!data) return '';
                        if (row.type === 'income') {
                            return '<span class="text-success">' + data + '</span><br><small class="text-success">Pendapatan</small>';
                        } else if (row.type === 'expenditure') {
                            return '<span class="text-danger">' + data + '</span><br><small class="text-danger">Pengeluaran</small>';
                        }
                        return data;
                    }
                },
                {
                    data: 'description',
                    name: 'description',
                    className: 'text-wrap',
                    width: '400px'
                },
                {
                    data: 'income',
                    name: 'income',
                    searchable: false,
                    className: 'text-success text-end'
                },
                {
                    data: 'expenditure',
                    name: 'expenditure',
                    searchable: false,
                    className: 'text-danger text-end'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        });

        // Initialize month options secara dinamis
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1; // getMonth() returns 0-11, so add 1
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const monthSelect = $('#month');

        for (let i = 1; i <= 12; i++) {
            const monthValue = i.toString().padStart(2, '0');
            const isSelected = i === currentMonth;
            monthSelect.append(new Option(monthNames[i - 1], monthValue, isSelected, isSelected));
        }

        // Initialize year options
        const currentYear = new Date().getFullYear();
        const yearSelect = $('#year');
        for (let i = currentYear - 5; i <= currentYear + 5; i++) {
            yearSelect.append(new Option(i, i, i === currentYear, i === currentYear));
        }

        // NO Select2 initialization - use plain HTML selects to avoid loading spinners

        // Modal event handlers dengan sistem yang lebih baik
        $('#myModal').on('show.bs.modal', function() {
            console.log('Modal showing - preparing form');
            // PreloaderManager akan handle ini otomatis
        });

        $('#myModal').on('shown.bs.modal', function() {
            console.log('Modal shown - form ready');
            // PreloaderManager akan handle cleanup otomatis
        });

        $('#myModal').on('hidden.bs.modal', function() {
            console.log('Modal hidden - resetting form state');
            // PreloaderManager akan handle reset otomatis

            // Custom reset untuk form ini
            $('#CreateForm').trigger("reset");
            $('#id').val('');
            $('#saveBtn').html("Tambah").prop('disabled', false);
            $('#modelHeading').html("Tambah Data Kas");
        });

        // Handle filter changes
        $('#month, #year').on('change', function() {
            if (table) {
                table.ajax.reload(function() {
                    // Hanya adjust columns
                    setTimeout(function() {
                        table.columns.adjust();
                    }, 50);
                });
            }
        });

        // Fix header alignment on window resize
        $(window).on('resize', function() {
            if (table) {
                setTimeout(function() {
                    table.columns.adjust();
                }, 50);
            }
        });

        $('#createNewData').click(function() {
            console.log('Create new data clicked');

            // DISABLE GLOBAL PRELOADER COMPLETELY
            if (window.showPreloader) window.showPreloader = function() {};
            if (window.hidePreloader) window.hidePreloader = function() {};

            // FORCE HIDE GLOBAL PRELOADER
            const globalPreloader = document.getElementById('preloader');
            if (globalPreloader) {
                globalPreloader.style.display = 'none !important';
                globalPreloader.style.visibility = 'hidden !important';
                globalPreloader.style.opacity = '0 !important';
            }
            document.body.classList.remove('preloader-active');

            // FORCE REMOVE ANY LOADING STATES
            $('body').find('.loading, .spinner, .spin, [class*="spin"], [class*="loading"]').remove();
            $('*').removeClass('loading spinning');

            // Clear all error states
            $('#errornama').hide();
            $('#nama').removeClass(' is-invalid');
            $('#erroramount').hide();
            $('#amount').removeClass(' is-invalid');
            $('#error_transaction_date').hide();
            $('#transaction_date').removeClass(' is-invalid');
            $('#error_customer_cash_flow_category_id').hide();
            $('#customer_cash_flow_category_id').removeClass(' is-invalid');
            $('#error_description').hide();
            $('#description').removeClass(' is-invalid');

            // Reset form state SEBELUM modal ditampilkan
            $('#saveBtn').html("Tambah");
            $('#CreateForm').trigger("reset");
            $('#id').val('');
            $('#modelHeading').html("Tambah Data Kas");

            console.log('Opening modal for new data');

            var modalEl = document.getElementById('myModal');
            if (modalEl) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

                // CONTINUOUS PRELOADER BLOCKING
                const blockPreloader = setInterval(function() {
                    const globalPreloader = document.getElementById('preloader');
                    if (globalPreloader) {
                        globalPreloader.style.display = 'none !important';
                        globalPreloader.style.visibility = 'hidden !important';
                        globalPreloader.style.opacity = '0 !important';
                    }
                    document.body.classList.remove('preloader-active');
                    $('body').find('.loading, .spinner, .spin, [class*="spin"], [class*="loading"]').remove();
                    $('*').removeClass('loading spinning');
                }, 50);

                // Stop blocking after 5 seconds
                setTimeout(function() {
                    clearInterval(blockPreloader);
                    console.log('Preloader blocking stopped');
                }, 5000);
            } else {
                alert('Element modal dengan id myModal tidak ditemukan di DOM.');
            }
        });
        $('body').on('click', '.editData', function() {
            // Bersihkan error dan form
            $('#errornama').hide();
            $('#nama').removeClass(' is-invalid');
            $('#erroramount').hide();
            $('#amount').removeClass(' is-invalid');
            $('#error_transaction_date').hide();
            $('#transaction_date').removeClass(' is-invalid');
            $('#error_customer_cash_flow_category_id').hide();
            $('#customer_cash_flow_category_id').removeClass(' is-invalid');
            $('#error_description').hide();
            $('#description').removeClass(' is-invalid');

            var Data_id = $(this).data('id');

            // Tampilkan modal untuk edit data
            $('#modelHeading').html("Update Data Kas");
            $('#saveBtn').html("Update");

            var modalEl = document.getElementById('myModal');
            if (modalEl) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            // Get data from server
            $.get('/arus_kas/getFlow/' + Data_id, function(response) {
                if (response.status === 'success') {
                    var data = response.data;
                    $('#id').val(data.id);
                    $('#nama').val(data.name);
                    // Amount sudah diformat dari server, langsung tampilkan
                    $('#amount').val(data.amount).trigger('keyup');
                    $('#transaction_date').val(data.transaction_date);
                    $('#customer_cash_flow_category_id').val(data.category_id).trigger('change');
                    $('#description').val(data.description);
                } else {
                    Swal.fire('Error', response.message || 'Gagal mengambil data', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Gagal mengambil data dari server', 'error');
            });
        });
        $('#saveBtn').click(function(e) {
            e.preventDefault();

            // Clear all validation errors
            const errorFields = ['nama', 'amount', 'transaction_date', 'customer_cash_flow_category_id', 'description'];
            errorFields.forEach(field => {
                $(`#error${field}, #error_${field}`).hide();
                $(`#${field}`).removeClass('is-invalid');
            });

            // Set loading state
            const $btn = $(this);
            const originalText = $btn.html();
            $btn.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...').prop('disabled', true);

            // Prepare form data
            var formData = $('#CreateForm').serializeArray();
            var cleanedData = {};

            // Convert to object and clean amount
            $.each(formData, function(i, field) {
                if (field.name === 'amount') {
                    cleanedData[field.name] = field.value.replace(/\./g, '');
                } else {
                    cleanedData[field.name] = field.value;
                }
            });

            // Make AJAX request dengan PreloaderManager
            const ajaxOptions = {
                data: cleanedData,
                url: '/arus_kas/flowSave',
                type: "POST",
                dataType: 'json',
                showPreloader: false, // Modal form jangan show global preloader
                success: function(data) {
                    console.log('Response from server:', data);

                    // Hide modal
                    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('myModal'));
                    modal.hide();

                    // Reload table
                    if (typeof table !== 'undefined' && table.draw) {
                        table.draw();
                    }

                    // Show notification
                    if (typeof Notify !== 'undefined') {
                        Notify({
                            status: data.status,
                            title: data.title,
                            text: data.message,
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true,
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Save error:', xhr);

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(field => {
                            const errorElement = $(`#error${field}, #error_${field}`);
                            const inputElement = $(`#${field}`);

                            if (errorElement.length) {
                                errorElement.show().find('strong').html(errors[field]);
                            }
                            if (inputElement.length) {
                                inputElement.addClass('is-invalid');
                            }
                        });
                    } else {
                        alert('Terjadi kesalahan saat menyimpan data');
                    }
                },
                complete: function() {
                    // Reset button state
                    $btn.html(originalText).prop('disabled', false);
                }
            };

            // Use PreloaderManager if available, otherwise use regular AJAX
            if (window.PreloaderManager) {
                window.PreloaderManager.ajaxWrapper(ajaxOptions);
            } else {
                $.ajax(ajaxOptions);
            }
        });

        $('body').on('click', '.deleteData', function() {
            var id = $(this).data("id");
            Swal.fire({
                title: "Apakah Anda Yakin ?",
                text: "Data yang dihapus tidak dapat dikembalikan",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Ya, hapus saja"
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "/arus_kas/delete/" + id;
                    // Get CSRF token from the form
                    var csrfToken = $('#CreateForm input[name="<?= csrf_token() ?>"]').val();
                    var data = {};
                    data['<?= csrf_token() ?>'] = csrfToken;

                    $.ajax({
                        type: "POST", // Changed from DELETE to POST
                        url: url,
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                table.draw();
                                Notify({
                                    status: 'success',
                                    title: response.title || 'Berhasil',
                                    text: response.message || 'Data kas berhasil dihapus',
                                    effect: 'slide',
                                    speed: 500,
                                    showCloseButton: true,
                                    autotimeout: 5000,
                                    autoclose: true,
                                });
                            } else {
                                Swal.fire('Gagal', response.message || 'Gagal menghapus data kas', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = 'Gagal menghapus data kas.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('Gagal', errorMessage, 'error');
                        }
                    });
                }
            });
        });

        $('#date').datepicker({
            orientation: "top right",
            todayHighlight: true,
            startDate: '-1m', // controll start date like startDate: '-2m' m: means Month
            endDate: 'now'
        });
        $("#amount").keyup(function() {
            if ($(this).val().length > 3) {
                var n = parseInt($(this).val().replace(/\D/g, ''), 10);
                $(this).val(n.toLocaleString('id'));
            }
        });
        $('#amount').trigger('keyup');
        $('#amount').keypress(function(e) {
            var charCode = (e.which) ? e.which : event.keyCode
            if (String.fromCharCode(charCode).match(/[^0-9]/g))
                return false;
        });
        $('#month, #year').on('change', function(e) {
            table.draw();
        });
        // Tampilkan modal hanya saat tombol Hapus Semua diklik
        $('#deleteAll').on('click', function(e) {
            e.preventDefault();
            $('#deleteAllModal').modal('show');
        });
        // Pastikan tombol di header memanggil event ini
        $('#deleteAll').off('click').on('click', function(e) {
            e.preventDefault();
            $('#deleteAllModal').modal('show');
        });
    });

    $(document).ready(function() {
        // Month dan year sudah diinisialisasi di atas, tidak perlu diulang lagi

        // Use plain HTML selects - no Select2 to avoid loading issues

        // Handle filter changes
        $('#month, #year').on('change', function() {
            table.ajax.reload();
        });

        // Trigger initial load
        $('#month').trigger('change');
    });

    $('#deleteAllButton').on('click', function(e) {
        e.preventDefault();
        var year = $('#year').val();
        var month = $('#month').val();

        if (year && month) {
            $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Menghapus..');
            $('#deleteAllButton').prop('disabled', true);
            var url = '/arus_kas/deleteAll';
            $.ajax({
                data: {
                    'year': year,
                    'month': month,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                url: url,
                type: "POST", // Changed from DELETE to POST
                dataType: 'json',
                success: function(data) {
                    $('#deleteAllModal').modal('hide');
                    getWidgetCash && getWidgetCash();
                    table.draw();
                    $('#deleteAllButton').html("Hapus");
                    $('#deleteAllButton').prop('disabled', false);
                    Notify({
                        status: data.status,
                        title: data.title,
                        text: data.message,
                        effect: 'slide',
                        speed: 500,
                        showCloseButton: true,
                        autotimeout: 5000,
                        autoclose: true,
                    });
                },
                error: function(data) {
                    $('#deleteAllModal').modal('hide');
                    $('#deleteAllButton').html("Hapus");
                    $('#deleteAllButton').prop('disabled', false);
                    Notify({
                        status: data.status,
                        title: data.title,
                        text: data.message,
                        effect: 'slide',
                        speed: 500,
                        showCloseButton: true,
                        autotimeout: 5000,
                        autoclose: true,
                    });
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include custom.js for DataTable functions -->
<script src="<?= base_url() ?>backend/assets/js/custom.js"></script>
<?= $this->endSection() ?>