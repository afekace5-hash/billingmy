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
</style>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid" data-select2-id="15">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Kategori</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('customers') ?>">Kategori</a></li>
                            <li class="breadcrumb-item active">Kategori</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-12.text-right">
                                <a href="javascript:void(0)" id="createNewData" class="btn btn-primary waves-effect btn-label waves-light">
                                    <i class="bx bx-plus label-icon"></i>
                                    Kategori Kas
                                </a>
                            </div>
                            <div class="text-md-right text-lg-end col-md-6 diplayMenu" style="display:none;">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 table-responsive">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered align-middle dt-responsive  nowrap w-100 my_datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Jenis Kas</th>
                                    <th>Keterangan</th>
                                    <th width="100px">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">Tambah Kategori Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="CreateForm" name="CreateForm" class="form-horizontal">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="name" class="col-form-label">Nama Kategori Kas<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="nama" placeholder="Nama Kategori Kas">
                                <input type="hidden" class="form-control" id="id" name="id_category" value="">
                                <span id="errorname" class="invalid-feedback text-danger" role="alert" style="display: none;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="type" class="col-form-label">Jenis Kas<span class="text-danger">*</span></label>
                                <select class="form-control" name="jenis_kas" id="type" style="background: white; border: 1px solid #ced4da;">
                                    <option value="">Pilih Jenis Kas</option>
                                    <option value="pemasukan">Pendapatan</option>
                                    <option value="pengeluaran">Pengeluaran</option>
                                </select>
                                <span id="errortype" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="desc" class="col-form-label">Keterangan<span class="text-danger">*</span></label>
                                <textarea type="text" class="form-control" id="desc" name="keterangan" placeholder="Keterangan"></textarea>
                                <span id="errordesc" class="invalid-feedback text-danger" role="alert" style="display: none;">
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

</div>
<!-- Semua dependensi JS (jQuery, Bootstrap, DataTables, Select2) diasumsikan sudah di-load di layout/default.php -->
<script>
    $(function() {
        // Event listener untuk modal events dengan preloader blocking
        $('#myModal').on('shown.bs.modal', function() {
            console.log('Modal shown - AGGRESSIVELY removing any loading states');

            // DISABLE GLOBAL PRELOADER FUNCTIONS
            if (window.showPreloader) window.showPreloader = function() {};
            if (window.hidePreloader) window.hidePreloader = function() {};

            // FORCE HIDE GLOBAL PRELOADER IMMEDIATELY
            const globalPreloader = document.getElementById('preloader');
            if (globalPreloader) {
                globalPreloader.style.display = 'none !important';
                globalPreloader.style.visibility = 'hidden !important';
                globalPreloader.style.opacity = '0 !important';
                globalPreloader.remove(); // NUCLEAR: Remove it from DOM completely
            }
            document.body.classList.remove('preloader-active');

            // Remove any loading elements
            $('body').find('.loading, .spinner, .spin, [class*="spin"], [class*="loading"]').remove();
            $('*').removeClass('loading spinning');

            $('#name').focus();
        });

        $('#myModal').on('hidden.bs.modal', function() {
            console.log('Modal hidden - BLOCKING preloader permanently');

            // PERMANENTLY DISABLE GLOBAL PRELOADER
            if (window.showPreloader) window.showPreloader = function() {
                console.log('Preloader blocked!');
            };
            if (window.hidePreloader) window.hidePreloader = function() {
                console.log('Preloader already blocked!');
            };

            // ENSURE GLOBAL PRELOADER STAYS HIDDEN
            const globalPreloader = document.getElementById('preloader');
            if (globalPreloader) {
                globalPreloader.style.display = 'none !important';
                globalPreloader.style.visibility = 'hidden !important';
                globalPreloader.style.opacity = '0 !important';
            }
            document.body.classList.remove('preloader-active');
        });

        // NO Select2 initialization - use plain HTML selects to avoid loading spinners

        var table = $('.my_datatable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            language: {
                url: "/backend/assets/libs/datatables/i18n/id.json"
            },
            ajax: {
                url: "/arus_kas/categoryList", // Ganti dengan endpoint yang benar di Controller Anda
                type: 'GET',
                data: function(d) {
                    d.filterMonth = $('#changeMonthSelect').val();
                    // CSRF for DataTables (ambil dari input hidden di form)
                    var csrfName = $('#CreateForm input[type="hidden"][name^="csrf"]').attr('name');
                    var csrfHash = $('#CreateForm input[type="hidden"][name^="csrf"]').val();
                    if (csrfName && csrfHash) {
                        d[csrfName] = csrfHash;
                    }
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'jenis_kas',
                    name: 'jenis_kas',
                    searchable: false
                },
                {
                    data: 'keterangan',
                    name: 'keterangan',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            columnDefs: [{
                    width: "3%",
                    targets: [0]
                },
                {
                    width: "10%",
                    targets: [4]
                },
                {
                    width: "15%",
                    targets: [3]
                },
                {
                    width: "10%",
                    targets: [1, 2]
                },
            ],
        });

        $('#createNewData').click(function() {
            console.log('Create new category clicked');

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

            $('#type').val('');
            $('#errorname').hide();
            $('#name').removeClass(' is-invalid');
            $('#errortype').hide();
            $('#type').removeClass(' is-invalid');
            $('#errordesc').hide();
            $('#desc').removeClass(' is-invalid');
            $('#amount').removeClass(' is-invalid');
            $('#erroramount').hide();

            $('#saveBtn').html("Tambah");
            $('#CreateForm').trigger("reset");
            $('#id').val('');
            $('#modelHeading').html("Tambah Kategori Kas");

            // Gunakan Bootstrap 5 Modal API yang benar
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
            $('#date').datepicker('setDate', null);
            $('#type').val('').trigger('change');
            $('#errorname').hide();
            $('#name').removeClass(' is-invalid');
            $('#errortype').hide();
            $('#type').removeClass(' is-invalid');
            $('#errordesc').hide();
            $('#desc').removeClass(' is-invalid');
            $('#amount').removeClass(' is-invalid');
            $('#erroramount').hide();


            var Data_id = $(this).data('id');
            var url = '/arus_kas/categoryEdit/' + Data_id;
            $.get(url, function(data) {
                $('#modelHeading').html("Update Kategori Kas");
                $('#saveBtn').html("Update");
                // Gunakan Bootstrap 5 Modal API yang benar
                var modalEl = document.getElementById('myModal');
                if (modalEl) {
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                } else {
                    alert('Element modal dengan id myModal tidak ditemukan di DOM.');
                }
                $('#id').val(data.id_category);
                $('#name').val(data.nama);
                $('#type').val(data.jenis_kas).change();
                $('#desc').val(data.keterangan);
            })
        });

        $('#saveBtn').click(function(e) {
            $('#errorname').hide();
            $('#name').removeClass(' is-invalid');
            $('#errortype').hide();
            $('#type').removeClass(' is-invalid');
            $('#errordesc').hide();
            $('#desc').removeClass(' is-invalid');
            $('#amount').removeClass(' is-invalid');
            $('#erroramount').hide();

            e.preventDefault();

            $(this).html('Menyimpan...');
            this.disabled = true;
            var id = $('#id').val();
            var url = '/arus_kas/categorySave';
            var formData = $('#CreateForm').serialize();
            $.ajax({
                data: formData,
                url: url,
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    $('#CreateForm').trigger("reset");
                    // Bootstrap 5: hide modal
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('myModal'));
                        modal.hide();
                    }
                    table.draw();

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
                    $('#saveBtn').html("Tambah");
                    $('#saveBtn').prop('disabled', false);
                },
                error: function(data) {
                    $('#saveBtn').html("Tambah");
                    $('#saveBtn').prop('disabled', false);
                    if (data.responseJSON && data.responseJSON.message) {
                        var errors = data.responseJSON.message;
                        if (errors.nama !== undefined) {
                            $('#errorname').show().html(errors.nama);
                            $('#name').addClass(' is-invalid');
                        }
                        if (errors.jenis_kas !== undefined) {
                            $('#errortype').show().html(errors.jenis_kas);
                            $('#type').addClass(' is-invalid');
                        }
                        if (errors.keterangan !== undefined) {
                            $('#errordesc').show().html(errors.keterangan);
                            $('#desc').addClass(' is-invalid');
                        }
                    }
                }
            });
        });

        $('body').on('click', '.deleteData', function() {
            var id = $(this).data("id");
            Swal.fire({
                title: "Apakah Anda Yakin ?",
                text: "Data yang di hapus tidak dapatkan di kembalikan",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Ya, hapus saja"
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "/arus_kas/categoryDelete/" + id;

                    $.ajax({
                        type: "DELETE",
                        url: url,
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        success: function(data) {
                            table.draw();

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
                            console.log('Error:', data);
                        }
                    });
                }
            })
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
        $('#changeMonthSelect').on('change', function(e) {

            table.draw();
        });

        // No Select2 for month filter - use plain HTML select
    });
</script>

<script>
    function formatUptime(t) {
        if (t) {
            var e = t.search("w"),
                s = t.search("d"),
                n = t.search("h"),
                r = t.search("m"),
                i = t.search("s");
            e > 0 ?
                ((weak = 7 * Number(t.split("w")[0])),
                    (t_day = t.substring(e + 1, t.legth))) :
                e < 0 && ((weak = ""), (t_day = t.substring(t.legth))),
                s > 0 ?
                (weak > 0 ?
                    (day = Number(t_day.split("d")[0])) :
                    (day = t_day.split("d")[0]),
                    (t_hour = t.substring(s + 1, t.legth))) :
                s < 0 && ((day = ""), (t_hour = t_day.substring(t.legth))),
                n > 0 ?
                ((hour = t_hour.split("h")[0]),
                    1 == hour.length ? (hour = "0" + hour + ":") : (hour += ":"),
                    (t_minute = t.substring(n + 1, t.legth))) :
                n < 0 && ((hour = "00:"), (t_minute = t.substring(s + 1, t.legth))),
                r > 0 ?
                ((minute = t_minute.split("m")[0]),
                    1 == minute.length && (minute = "0" + minute),
                    (t_sec = t.substring(r + 1, t.legth))) :
                r < 0 && n < 0 ?
                ((minute = "00"), (t_sec = t.substring(s + 1, t.legth))) :
                r < 0 && ((minute = "00"), (t_sec = t.substring(n + 1, t.legth))),
                i > 0 ?
                ((sec = t_sec.split("s")[0]),
                    1 == sec.length ? (sec = ":0" + sec) : (sec = ":" + sec)) :
                i < 0 && (sec = ":00");
            var a = Number(weak) + Number(day);
            return a < 1 ? (a = "") : (a += "d "), a + hour + minute + sec;
        }
    }
</script>
<?= $this->endSection() ?>S