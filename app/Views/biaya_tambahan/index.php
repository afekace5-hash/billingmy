<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Biaya Tambahan - PT. KIMONET DIGITAL SYNERGY</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- CSRF Token -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Biaya Tambahan</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Billing</a></li>
                            <li class="breadcrumb-item active">Biaya Tambahan</li>
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h4 class="card-title">Data Biaya Tambahan</h4>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" id="createNewBiaya" class="btn btn-primary waves-effect btn-label waves-light">
                                    <i class="bx bx-plus label-icon"></i>
                                    Tambah
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle nowrap w-100" id="biayaTambahanTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Biaya</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
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

<!-- Modal for Create/Edit -->
<div class="modal fade" id="biayaTambahanModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="biayaTambahanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="biayaTambahanModalLabel">Tambah Biaya Tambahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="biayaTambahanForm" name="biayaTambahanForm" class="form-horizontal">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="biaya_id" name="biaya_id" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kategori" class="col-form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="kategori" name="kategori">
                                    <option value="">Pilih Kategori</option>
                                    <option value="Sewa Alat">Sewa Alat</option>
                                    <option value="Biaya Registrasi">Biaya Registrasi</option>
                                    <option value="Biaya Operasional">Biaya Operasional</option>
                                    <option value="Biaya Maintenance">Biaya Maintenance</option>
                                    <option value="Biaya Administrasi">Biaya Administrasi</option>
                                    <option value="Diskon">Diskon</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <div class="invalid-feedback" id="error_kategori"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_biaya" class="col-form-label">Nama Biaya <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_biaya" name="nama_biaya" placeholder="Contoh: Sewa Router, Biaya Domain">
                                <div class="invalid-feedback" id="error_nama_biaya"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jumlah" class="col-form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="jumlah" name="jumlah" placeholder="0 (negatif untuk diskon)">
                                <div class="invalid-feedback" id="error_jumlah"></div>
                                <small class="text-muted">
                                    Masukkan jumlah positif untuk biaya tambahan, negatif untuk diskon.<br>
                                    Contoh: 50000 = Biaya Tambahan Rp 50.000, -30000 = Diskon Rp 30.000
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal" class="col-form-label">Tanggal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>">
                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                </div>
                                <div class="invalid-feedback" id="error_tanggal"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                                <div class="invalid-feedback" id="error_status"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="deskripsi" class="col-form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi atau keterangan biaya tambahan"></textarea>
                                <div class="invalid-feedback" id="error_deskripsi"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="saveBtn" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let table;

    $(document).ready(function() {
        // Check if toastr is available
        if (typeof toastr === 'undefined') {
            console.error('Toastr is not loaded!');
            // Fallback ke alert
            window.showToast = function(message, type) {
                alert(type + ': ' + message);
            };
        } else {
            console.log('Toastr is available');
            window.showToast = function(message, title, type) {
                if (type === 'success') {
                    toastr.success(message, title);
                } else {
                    toastr.error(message, title);
                }
            };
        }

        // Configure toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Initialize DataTable
        table = $('#biayaTambahanTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            language: {
                url: "/backend/assets/libs/datatables/i18n/id.json"
            },
            ajax: {
                url: "/biaya_tambahan/data",
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {
                    // Add CSRF token
                    var csrfName = $('input[name*="csrf"]').attr('name');
                    var csrfHash = $('input[name*="csrf"]').val();
                    if (csrfName && csrfHash) {
                        d[csrfName] = csrfHash;
                    }
                },
                error: function(xhr, error, code) {
                    console.log('Ajax Error:', error);
                    console.log('Response:', xhr.responseText);
                    console.log('Status:', xhr.status);
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama_biaya',
                    name: 'nama_biaya'
                },
                {
                    data: 'kategori',
                    name: 'kategori'
                },
                {
                    data: 'jumlah',
                    name: 'jumlah',
                    className: 'text-end',
                    render: function(data, type, row) {
                        const amount = parseInt(data);
                        if (amount < 0) {
                            return '<span class="text-success">Rp ' + Math.abs(amount).toLocaleString('id-ID') + ' (Diskon)</span>';
                        } else {
                            return '<span class="text-primary">Rp ' + amount.toLocaleString('id-ID') + '</span>';
                        }
                    }
                },
                {
                    data: 'tanggal',
                    name: 'tanggal'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [4, 'desc']
            ] // Order by tanggal desc
        });

        // Hide preloader jika ada setelah DataTables selesai reload
        $('#biayaTambahanTable').on('draw.dt', function() {
            $('#preloader, .preloader').hide();
        });

        // Initialize Select2
        if (typeof $.fn.select2 === 'function') {
            $("#kategori").select2({
                dropdownParent: $('#biayaTambahanModal'),
                placeholder: "Pilih Kategori",
                width: '100%',
                allowClear: true
            });
        }

        // Format currency input (allow negative numbers)
        $("#jumlah").on('keyup', function() {
            let value = $(this).val();
            let isNegative = value.startsWith('-');
            let cleanValue = value.replace(/[^\d]/g, '');
            
            if (cleanValue.length > 0) {
                let formatted = parseInt(cleanValue).toLocaleString('id-ID');
                if (isNegative) {
                    formatted = '-' + formatted;
                }
                $(this).val(formatted);
            } else if (isNegative && cleanValue.length === 0) {
                $(this).val('-');
            }
        });

        // Allow numbers and minus sign in jumlah input
        $('#jumlah').on('keypress', function(e) {
            let charCode = (e.which) ? e.which : event.keyCode;
            let char = String.fromCharCode(charCode);
            
            // Allow minus sign only at the beginning
            if (char === '-' && this.selectionStart === 0 && this.value.indexOf('-') === -1) {
                return true;
            }
            
            // Allow only numbers
            if (char.match(/[^0-9]/g)) {
                return false;
            }
            
            return true;
        });

        // Create new biaya tambahan
        $('#createNewBiaya').click(function() {
            resetForm();
            $('#biayaTambahanModalLabel').html('Tambah Biaya Tambahan');
            $('#saveBtn').html('Simpan');
            $('#biayaTambahanModal').modal('show');
        });

        // Reset button ketika modal ditutup
        $('#biayaTambahanModal').on('hidden.bs.modal', function() {
            let id = $('#biaya_id').val();
            let buttonText = id ? 'Update' : 'Simpan';
            $('#saveBtn').html(buttonText).prop('disabled', false);
        });

        // Edit biaya tambahan
        $('body').on('click', '.editData', function() {
            resetForm();
            let id = $(this).data('id');

            $.get('/biaya_tambahan/edit/' + id, function(response) {
                if (response.status === 'success') {
                    let data = response.data;
                    $('#biayaTambahanModalLabel').html('Edit Biaya Tambahan');
                    $('#saveBtn').html('Update');
                    $('#biaya_id').val(data.id);
                    $('#kategori').val(data.kategori).trigger('change');
                    $('#nama_biaya').val(data.nama_biaya);
                    $('#jumlah').val(data.jumlah < 0 ? data.jumlah : parseInt(data.jumlah).toLocaleString('id-ID'));
                    $('#tanggal').val(data.tanggal);
                    $('#status').val(data.status);
                    $('#deskripsi').val(data.deskripsi);
                    $('#biayaTambahanModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Gagal mengambil data', 'error');
            });
        });

        // Save biaya tambahan
        $('#biayaTambahanForm').on('submit', async function(e) {
            e.preventDefault();
            resetValidation();

            let id = $('#biaya_id').val();
            let url = id ? '/biaya_tambahan/update/' + id : '/biaya_tambahan/create';
            let buttonTextOriginal = id ? 'Update' : 'Simpan';

            // Set loading state
            const $saveBtn = $('#saveBtn');
            $saveBtn.html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Menyimpan...').prop('disabled', true);

            try {
                let formData = $(this).serialize();
                let jumlahValue = $('#jumlah').val();
                let isNegative = jumlahValue.startsWith('-');
                let cleanValue = jumlahValue.replace(/[^\d]/g, '');
                
                if (isNegative) {
                    cleanValue = '-' + cleanValue;
                }
                
                formData = formData.replace(/jumlah=[^&]*/, 'jumlah=' + cleanValue);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                // Reset button
                $saveBtn.html(buttonTextOriginal).prop('disabled', false).removeClass('disabled');
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.innerHTML = buttonTextOriginal;
                saveBtn.disabled = false;
                saveBtn.removeAttribute('disabled');
                saveBtn.classList.remove('disabled', 'loading', 'btn-loading');
                saveBtn.style.pointerEvents = 'auto';
                saveBtn.style.opacity = '1';

                $('#biayaTambahanModal').modal('hide');
                table.ajax.reload();

                if (data.status === 'success') {
                    showToast(data.message, data.title, 'success');
                } else {
                    showToast(data.message, data.title, 'error');
                }

            } catch (error) {
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.innerHTML = buttonTextOriginal;
                saveBtn.disabled = false;
                saveBtn.removeAttribute('disabled');
                saveBtn.classList.remove('disabled', 'loading', 'btn-loading');
                showToast('Terjadi kesalahan: ' + error.message, 'Error', 'error');
            }
        });

        // Delete biaya tambahan
        $('body').on('click', '.deleteData', function() {
            let id = $(this).data("id");

            Swal.fire({
                title: "Apakah Anda Yakin?",
                text: "Data yang dihapus tidak dapat dikembalikan",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    let csrfToken = $('input[name*="csrf"]').val();
                    let csrfName = $('input[name*="csrf"]').attr('name');
                    let data = {};
                    data[csrfName] = csrfToken;

                    $.ajax({
                        type: "POST",
                        url: "/biaya_tambahan/delete/" + id,
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                table.ajax.reload();
                                showToast(response.message, response.title, 'success');
                            } else {
                                showToast(response.message, 'Error', 'error');
                            }
                        },
                        error: function(xhr) {
                            let response = xhr.responseJSON;
                            Swal.fire('Error', response.message || 'Gagal menghapus data', 'error');
                        }
                    });
                }
            });
        });

        function resetForm() {
            $('#biayaTambahanForm')[0].reset();
            $('#biaya_id').val('');
            $('#kategori').val('').trigger('change');
            $('#tanggal').val('<?= date('Y-m-d') ?>');

            // Reset button state
            $('#saveBtn').html('Simpan');
            $('#saveBtn').prop('disabled', false);

            resetValidation();
        }

        function resetValidation() {
            $('.form-control, .form-select').removeClass('is-invalid');
            $('.invalid-feedback').html('');
        }

        function showValidationErrors(errors) {
            console.log('Showing validation errors:', errors);
            $.each(errors, function(field, message) {
                $('#' + field).addClass('is-invalid');
                $('#error_' + field).html(message);
            });
        }
    });
</script>

<style>
    .dataTables_wrapper {
        overflow-x: auto;
    }

    .table th,
    .table td {
        white-space: nowrap;
    }
</style>

<?= $this->endSection() ?>