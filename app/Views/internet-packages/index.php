<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Paket</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Paket</a></li>
                            <li class="breadcrumb-item active">Paket</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12 table-responsive">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4">
                            <a href="javascript:void(0)" id="createNewData"
                                class="btn btn-primary waves-effect btn-label waves-light">
                                <i class="bx bx-plus label-icon"></i> Paket
                            </a>
                        </div>

                        <table class="table table-bordered dt-responsive  nowrap w-100 user_datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Bandwidth</th>
                                    <th>Harga</th>
                                    <th width="100px">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="myModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modelHeading">Buat Paket</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="CreateForm" name="CreateForm" class="form-horizontal">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nama" class="col-form-label">Nama<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="name" placeholder="Nama">
                                <input type="hidden" class="form-control" id="id" name="id">
                                <span id="errorNama" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="bandwidth" class="col-form-label">Bandwidth<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="bandwidth" name="bandwidth_profile" placeholder="Bandwidth">
                                <span id="errorBandwidth" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="harga" class="col-form-label">Harga<span class="text-danger">*</span></label>
                                <input type="string" class="form-control" id="harga" name="price" placeholder="Harga">
                                <span id="errorharga" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="saveBtn" class="btn btn-primary" value="create">Save changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        $('#myModal').on('shown.bs.modal', function() {
            $('#nama').focus();
        });
        var role = "true";
        var showActionColumn = role == 'true' ? true : false;

        // Hanya angka untuk input harga
        $('#harga').on('keypress', function(e) {
            var charCode = (e.which) ? e.which : event.keyCode;
            // Allow: backspace, delete, left, right, tab
            if ($.inArray(charCode, [8, 9, 37, 39, 46]) !== -1) return true;
            if (String.fromCharCode(charCode).match(/[^0-9]/g)) return false;
        });

        // Format harga saat user mengetik
        $("#harga").on('input', function() {
            var val = $(this).val().replace(/\D/g, '');
            if (val.length > 0) {
                var n = parseInt(val, 10);
                $(this).val(n.toLocaleString('id'));
            } else {
                $(this).val('');
            }
        });

        var table = $('.user_datatable').DataTable({
            pageLength: "10",
            processing: true,
            serverSide: true,
            ajax: "<?= site_url('internet-packages') ?>",
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
                    data: 'bandwidth',
                    name: 'bandwidth'
                },
                {
                    data: 'harga',
                    name: 'harga'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    visible: showActionColumn
                },
            ],
            columnDefs: [{
                    width: "3%",
                    targets: 0
                },
                {
                    width: "10%",
                    targets: 2
                },
                {
                    className: 'text-center',
                    targets: [0, 2, 3, 4]
                },
            ],
        });

        $("#harga").keyup(function() {
            if ($(this).val().length > 3) {
                var n = parseInt($(this).val().replace(/\D/g, ''), 10);
                $(this).val(n.toLocaleString('id'));
            }
        });

        $('#createNewData').click(function() {
            $('#errorNama').hide();
            $('#nama').removeClass(' is-invalid');
            $('#errorBandwidth').hide();
            $('#bandwidth').removeClass(' is-invalid');
            $('#errorPrice').hide();
            $('#harga').removeClass(' is-invalid');
            $('#saveBtn').html("Buat baru");
            $('#CreateForm').trigger("reset");
            $('#id').val('');
            $('#modelHeading').html("Buat Paket");
            $('#myModal').modal('show');
        });

        $('body').on('click', '.editData', function() {
            $('#errorName').hide();
            $('#nama').removeClass(' is-invalid');
            $('#errorBandwidth').hide();
            $('#bandwidth').removeClass(' is-invalid');
            $('#errorPrice').hide();
            $('#harga').removeClass(' is-invalid');
            var Data_id = $(this).data('id');
            var url = '<?= site_url('internet-packages') ?>/' + Data_id + '/edit';
            url = url.replace(':id', Data_id);
            $.get(url, function(response) {
                if (response.status === 'success' && response.data) {
                    $('#modelHeading').html("Perbarui Paket");
                    $('#saveBtn').html("Perbarui");
                    $('#myModal').modal('show');
                    $('#id').val(response.data.id);
                    $('#nama').val(response.data.name);
                    $('#bandwidth').val(response.data.bandwidth_profile);
                    // Format harga dengan pemisah ribuan
                    var hargaFormatted = parseInt(response.data.price, 10).toLocaleString('id');
                    $('#harga').val(hargaFormatted);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Data tidak ditemukan'
                    });
                }
            });
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#saveBtn').click(function(e) {
            e.preventDefault();
            // Reset error messages and styles
            $('#errorNama').hide();
            $('#nama').removeClass(' is-invalid');
            $('#errorBandwidth').hide();
            $('#bandwidth').removeClass(' is-invalid');
            $('#errorPrice').hide();
            $('#price').removeClass(' is-invalid');

            var $btn = $(this);
            $btn.html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Sending..');
            $btn.prop('disabled', true);

            var id = $('#id').val();
            var url = "<?= site_url('internet-packages') ?>";
            var method = "POST";
            // Ambil harga tanpa format ribuan sebelum submit
            var hargaRaw = $('#harga').val().replace(/\D/g, '');
            $('#harga').val(hargaRaw);
            // Ambil CSRF token terbaru dari input hidden
            var csrfName = $('input[name^=csrf]').attr('name');
            var csrfVal = $('input[name^=csrf]').val();
            var data = $('#CreateForm').serialize();
            // Kembalikan format harga setelah submit agar UX tetap baik
            if (hargaRaw.length > 0) {
                $('#harga').val(parseInt(hargaRaw, 10).toLocaleString('id'));
            }
            if (id) {
                // Edit mode: gunakan endpoint update
                url = url + '/' + id;
                data += '&_method=PUT';
            }
            // Tambahkan CSRF token ke data jika belum ada
            if (data.indexOf(csrfName + '=') === -1) {
                data += '&' + encodeURIComponent(csrfName) + '=' + encodeURIComponent(csrfVal);
            }
            $.ajax({
                data: data,
                url: url,
                type: method,
                dataType: 'json',
                success: function(data) {
                    // Update CSRF token jika ada di response
                    if (data.csrf_token) {
                        $('input[name^=csrf]').val(data.csrf_token);
                    }
                    $('#CreateForm').trigger("reset");
                    $('#myModal').modal('hide');
                    table.draw();
                    Swal.fire({
                        icon: data.status === 'success' ? 'success' : 'info',
                        title: data.title || (data.status === 'success' ? 'Berhasil' : 'Info'),
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(data) {
                    // Update CSRF token jika ada di response error
                    if (data.responseJSON && data.responseJSON.csrf_token) {
                        $('input[name^=csrf]').val(data.responseJSON.csrf_token);
                    }
                    if (data.responseJSON && data.responseJSON.errors) {
                        if (data.responseJSON.errors.name !== undefined) {
                            $('#errorNama').show().html(data.responseJSON.errors.name);
                            $('#nama').addClass(' is-invalid');
                        }
                        if (data.responseJSON.errors.bandwidth_profile !== undefined) {
                            $('#errorBandwidth').show().html(data.responseJSON.errors.bandwidth_profile);
                            $('#bandwidth').addClass(' is-invalid');
                        }
                        if (data.responseJSON.errors.price !== undefined) {
                            $('#errorharga').show().html(data.responseJSON.errors.price);
                            $('#harga').addClass(' is-invalid');
                        }
                    }
                },
                complete: function() {
                    $btn.html("Simpan");
                    $btn.prop('disabled', false);
                }
            });
        });

        $('body').on('click', '.deleteData', function() {

            var id = $(this).data("id");

            Swal.fire({
                title: "Apa kamu yakin?",
                text: "Semua invoice yang memakai paket ini akan di hapus ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Ya, hapus saja."
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "<?= site_url('internet-packages') ?>/" + id;
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            '<?= csrf_token() ?>': $('input[name="<?= csrf_token() ?>"]').val(),
                            '_method': 'DELETE'
                        },
                        success: function(data) {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: data.title || 'Berhasil',
                                text: data.message || 'Data berhasil dihapus',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // update CSRF token setelah hapus
                            if (data.csrf_token) {
                                $('input[name="<?= csrf_token() ?>"]').val(data.csrf_token);
                            }
                        },
                        error: function(data) {
                            console.log('Error:', data);
                            Swal.fire({
                                icon: 'error',
                                title: data.responseJSON?.title || 'Error',
                                text: data.responseJSON?.message || 'Terjadi kesalahan.'
                            });
                            // update CSRF token jika ada di response error
                            if (data.responseJSON && data.responseJSON.csrf_token) {
                                $('input[name="<?= csrf_token() ?>"]').val(data.responseJSON.csrf_token);
                            }
                        }
                    });
                }
            })
        });
    });
</script>
<?= $this->endSection() ?>