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
                                        <th>Pelanggan</th>
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
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="customer_search" class="col-form-label">Pilih Pelanggan <span class="text-muted">(Opsional)</span></label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="customer_search" placeholder="Ketik minimal 2 karakter untuk mencari pelanggan..." autocomplete="off">
                                    <div id="customer_dropdown" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto; display: none;">
                                        <!-- Results will be loaded here -->
                                    </div>
                                </div>
                                <input type="hidden" id="selected_customers" name="customer_ids[]" multiple>
                                <div id="selected_customers_display" class="mt-2">
                                    <!-- Selected customers will be shown here -->
                                </div>
                                <small class="text-muted">Masukkan minimal 2 karakter untuk mencari. Klik pelanggan untuk menambah ke daftar.</small>
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
        // Simple delay to ensure DataTables is loaded from layout
        setTimeout(function() {
            if (typeof $.fn.DataTable !== 'undefined') {
                console.log('DataTables is available');
                initializePage();
            } else {
                console.error('DataTables not loaded from layout');
                alert('DataTables library gagal dimuat. Silakan refresh halaman.');
            }
        }, 200);
    });

    function initializePage() {
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
        if ($.fn.dataTable.isDataTable('#biayaTambahanTable')) {
            console.log('DataTable already initialized, destroying first');
            $('#biayaTambahanTable').DataTable().destroy();
        }

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
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    title: 'Pelanggan',
                    render: function(data, type, row) {
                        return `
                            <button type="button" class="btn btn-info btn-sm" onclick="showCurrentAssignments(${row.id})">
                                <i class="bx bx-users"></i> Lihat Pelanggan
                            </button>
                        `;
                    }
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
            initializeCustomerSearch();
            $('#biayaTambahanModal').modal('show');
        });

        // Initialize customer search functionality
        function initializeCustomerSearch() {
            $('#customer_search').off('input').on('input', function() {
                const query = $(this).val();
                if (query.length >= 2) {
                    searchCustomers(query);
                } else {
                    $('#customer_dropdown').hide();
                }
            });

            // Hide dropdown when clicking outside
            $(document).off('click.customerSearch').on('click.customerSearch', function(e) {
                if (!$(e.target).closest('#customer_search, #customer_dropdown').length) {
                    $('#customer_dropdown').hide();
                }
            });
        }

        // Search customers with query
        function searchCustomers(query) {
            $.ajax({
                url: '<?= base_url('biaya_tambahan/searchCustomers') ?>',
                type: 'GET',
                data: {
                    q: query
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(function(customer) {
                            html += `<a class="dropdown-item customer-option" href="#" data-id="${customer.id}" data-name="${customer.nama_customer}" data-username="${customer.username}">
                                <strong>${customer.nama_customer}</strong><br>
                                <small class="text-muted">${customer.username}</small>
                            </a>`;
                        });
                        $('#customer_dropdown').html(html).show();
                    } else {
                        $('#customer_dropdown').html('<div class="dropdown-item text-muted">Tidak ada pelanggan ditemukan</div>').show();
                    }
                },
                error: function() {
                    $('#customer_dropdown').html('<div class="dropdown-item text-danger">Error memuat data</div>').show();
                }
            });
        }

        // Handle customer selection
        $(document).on('click', '.customer-option', function(e) {
            e.preventDefault();
            const customerId = $(this).data('id');
            const customerName = $(this).data('name');
            const customerUsername = $(this).data('username');

            addSelectedCustomer(customerId, customerName, customerUsername);
            $('#customer_search').val('');
            $('#customer_dropdown').hide();
        });

        // Add customer to selected list
        function addSelectedCustomer(id, name, username) {
            // Check if already selected
            if ($(`#selected_customers_display [data-customer-id="${id}"]`).length > 0) {
                return;
            }

            const badge = `<span class="badge bg-primary me-2 mb-1 p-2" data-customer-id="${id}">
                ${name} - ${username}
                <button type="button" class="btn-close btn-close-white ms-2" onclick="removeSelectedCustomer(${id})" style="font-size: 0.7em;"></button>
            </span>`;

            $('#selected_customers_display').append(badge);

            // Update hidden input
            updateSelectedCustomersInput();
        }

        // Remove selected customer
        window.removeSelectedCustomer = function(customerId) {
            $(`#selected_customers_display [data-customer-id="${customerId}"]`).remove();
            updateSelectedCustomersInput();
        };

        // Update hidden input with selected customer IDs
        function updateSelectedCustomersInput() {
            const selectedIds = [];
            $('#selected_customers_display [data-customer-id]').each(function() {
                selectedIds.push($(this).data('customer-id'));
            });

            // Clear existing hidden inputs
            $('input[name="customer_ids[]"]').remove();

            // Add new hidden inputs
            selectedIds.forEach(function(id) {
                $('#selected_customers').after(`<input type="hidden" name="customer_ids[]" value="${id}">`);
            });
        }

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

                    // Load current customer assignments for edit
                    loadCurrentAssignments(id);

                    // Show in existing form (no modal needed)
                    $('html, body').animate({
                        scrollTop: $('#biayaTambahanCard').offset().top - 20
                    }, 500);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Gagal mengambil data', 'error');
            });
        });

        // Load current assignments untuk edit form
        function loadCurrentAssignments(biayaId) {
            $.ajax({
                url: '<?= base_url('biaya_tambahan/getAssignedCustomers') ?>',
                type: 'GET',
                data: {
                    biaya_tambahan_id: biayaId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Clear existing selected customers
                        $('#selected_customers_display').empty();
                        $('input[name="customer_ids[]"]').remove();

                        // Add each assigned customer as selected badge
                        response.data.forEach(function(customer) {
                            addSelectedCustomer(customer.id, customer.nama_customer);
                        });
                    }
                }
            });
        }

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

            // Clear customer search
            $('#customer_search').val('');
            $('#selected_customers_display').empty();
            $('input[name="customer_ids[]"]').remove();
            $('#customer_dropdown').hide();

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

        // Load customer options untuk semua dropdown ketika DataTable selesai di-load
        table.on('draw.dt', function() {
            loadAllCustomerOptions();
        });

        // Load customer options untuk semua select
        function loadAllCustomerOptions() {
            $.ajax({
                url: '<?= base_url('biaya_tambahan/getCustomerOptions') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Pilih Pelanggan --</option>';
                        response.data.forEach(function(customer) {
                            options += `<option value="${customer.id}">${customer.nama_customer} - ${customer.username}</option>`;
                        });

                        $('.customer-select').each(function() {
                            let biayaId = $(this).data('biaya-id');
                            $(this).html(options);

                            // Load current assignments
                            loadCurrentAssignments(biayaId, $(this));
                        });
                    }
                },
                error: function() {
                    $('.customer-select').html('<option value="">Error memuat data</option>');
                }
            });
        }

        // Load assignment saat ini untuk biaya tertentu
        function loadCurrentAssignments(biayaId, selectElement) {
            $.ajax({
                url: '<?= base_url('biaya_tambahan/getAssignedCustomers') ?>',
                type: 'GET',
                data: {
                    biaya_tambahan_id: biayaId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        response.data.forEach(function(customer) {
                            selectElement.find(`option[value="${customer.id}"]`).prop('selected', true);
                        });
                    }
                }
            });
        }

        // Simpan assignment pelanggan
        window.saveCustomerAssignment = function(biayaId) {
            let selectElement = $(`.customer-select[data-biaya-id="${biayaId}"]`);
            let selectedCustomers = selectElement.val();

            if (!selectedCustomers || selectedCustomers.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih minimal satu pelanggan'
                });
                return;
            }

            // Show loading on button
            let saveBtn = selectElement.siblings('.mt-1').find('button:first');
            let originalHtml = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

            $.ajax({
                url: '<?= base_url('biaya_tambahan/assignCustomers') ?>',
                type: 'POST',
                data: {
                    biaya_tambahan_id: biayaId,
                    customer_ids: selectedCustomers,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Terjadi kesalahan'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server'
                    });
                },
                complete: function() {
                    saveBtn.prop('disabled', false).html(originalHtml);
                }
            });
        }

        // Lihat assignment saat ini
        window.showCurrentAssignments = function(biayaId) {
            $.ajax({
                url: '<?= base_url('biaya_tambahan/getAssignedCustomers') ?>',
                type: 'GET',
                data: {
                    biaya_tambahan_id: biayaId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.data.length > 0) {
                            html += '<div class="table-responsive"><table class="table table-striped table-sm">';
                            html += '<thead><tr><th>Nama</th><th>Username</th><th>Status</th></tr></thead><tbody>';

                            response.data.forEach(function(customer) {
                                let statusClass = customer.is_active == '1' ? 'success' : 'danger';
                                let statusText = customer.is_active == '1' ? 'Aktif' : 'Non-aktif';
                                html += `<tr>
                                    <td>${customer.nama_customer}</td>
                                    <td>${customer.username}</td>
                                    <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                                </tr>`;
                            });
                            html += '</tbody></table></div>';
                        } else {
                            html = '<div class="alert alert-info text-center">Belum ada pelanggan yang di-assign</div>';
                        }

                        Swal.fire({
                            title: 'Pelanggan Terdaftar',
                            html: html,
                            width: '600px',
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Gagal memuat data'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memuat data'
                    });
                }
            });
        }
    } // End of initializePage function
</script>

<style>
    .dataTables_wrapper {
        overflow-x: auto;
    }

    .table th,
    .table td {
        white-space: nowrap;
    }

    .customer-assignment {
        min-width: 280px;
        padding: 8px;
    }

    .customer-select {
        font-size: 0.875rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .customer-select:focus {
        border-color: #4285F4;
        box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
    }

    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.25;
    }

    .customer-assignment .mt-1 {
        display: flex;
        gap: 4px;
        justify-content: center;
    }

    /* Customer search styles */
    #customer_dropdown {
        border: 1px solid #ddd;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 1050;
    }

    #customer_dropdown .dropdown-item {
        padding: 0.5rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
    }

    #customer_dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    #customer_dropdown .dropdown-item:last-child {
        border-bottom: none;
    }

    #selected_customers_display .badge {
        font-size: 0.85em;
        position: relative;
    }

    #selected_customers_display .btn-close {
        padding: 0;
        margin: 0;
        font-size: 0.7em;
    }
</style>

<?= $this->endSection() ?>