<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>Kelola Diskon Tagihan<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Kelola Diskon Tagihan</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Diskon Tagihan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-all me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-receipt me-2"></i>Tagihan Belum Dibayar
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" id="bulkDiscountBtn" disabled>
                                <i class="bx bx-gift me-1"></i> Diskon Massal
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="refreshTable()">
                                <i class="bx bx-refresh me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="invoicesTable" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Invoice No</th>
                                        <th>Customer</th>
                                        <th>No. Layanan</th>
                                        <th>Periode</th>
                                        <th>Tagihan</th>
                                        <th>Diskon Saat Ini</th>
                                        <th>Total</th>
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

<!-- Apply Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">
                    <i class="bx bx-gift me-2"></i>Berikan Diskon
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="discountForm">
                <div class="modal-body">
                    <input type="hidden" id="invoice_id" name="invoice_id">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Customer & Invoice</label>
                            <div id="invoiceInfo" class="alert alert-info mb-3">
                                <strong id="customerName">-</strong><br>
                                <span class="text-muted">Invoice: <span id="invoiceNo">-</span></span><br>
                                <span class="text-muted">Total Tagihan: Rp <span id="totalBill">-</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="discount_type" class="form-label">Tipe Diskon</label>
                            <select class="form-select" id="discount_type" name="discount_type" required>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="percent">Persentase (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="discount_amount" class="form-label">Jumlah Diskon</label>
                            <input type="number" class="form-control" id="discount_amount" name="discount_amount"
                                min="0" step="0.01" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="reason" class="form-label">Alasan Diskon</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"
                                placeholder="Masukkan alasan pemberian diskon..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Perhatian:</strong> Diskon akan langsung diterapkan pada tagihan ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="applyDiscountBtn">
                        <i class="bx bx-check me-1"></i> Terapkan Diskon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Discount Modal -->
<div class="modal fade" id="bulkDiscountModal" tabindex="-1" aria-labelledby="bulkDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDiscountModalLabel">
                    <i class="bx bx-gift me-2"></i>Diskon Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkDiscountForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Jumlah Invoice Terpilih: <span id="selectedCount">0</span></strong>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bulk_discount_type" class="form-label">Tipe Diskon</label>
                            <select class="form-select" id="bulk_discount_type" name="discount_type" required>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="percent">Persentase (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bulk_discount_amount" class="form-label">Jumlah Diskon</label>
                            <input type="number" class="form-control" id="bulk_discount_amount" name="discount_amount"
                                min="0" step="0.01" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="bulk_reason" class="form-label">Alasan Diskon</label>
                            <textarea class="form-control" id="bulk_reason" name="reason" rows="3"
                                placeholder="Masukkan alasan pemberian diskon massal..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Perhatian:</strong> Diskon akan diterapkan pada semua tagihan yang dipilih.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="applyBulkDiscountBtn">
                        <i class="bx bx-check me-1"></i> Terapkan Diskon Massal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#invoicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('invoice-discount/data') ?>',
                type: 'POST'
            },
            columns: [{
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return `<input type="checkbox" class="invoice-checkbox" value="${data.id}">`;
                    }
                },
                {
                    data: 'invoice_no'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'nomor_layanan'
                },
                {
                    data: 'periode'
                },
                {
                    data: 'bill',
                    render: function(data) {
                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'current_discount',
                    render: function(data) {
                        return data > 0 ? 'Rp ' + parseFloat(data).toLocaleString('id-ID') : '-';
                    }
                },
                {
                    data: 'total',
                    render: function(data) {
                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        let actions = `
                        <button class="btn btn-success btn-sm" onclick="showDiscountModal(${data.id}, '${data.customer_name}', '${data.invoice_no}', ${data.bill})">
                            <i class="bx bx-gift"></i> Diskon
                        </button>`;

                        if (data.current_discount > 0) {
                            actions += `
                            <button class="btn btn-warning btn-sm ms-1" onclick="removeDiscount(${data.id})">
                                <i class="bx bx-x"></i>
                            </button>`;
                        }

                        return actions;
                    }
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 25,
            responsive: true,
            language: {
                processing: "Sedang memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Select All functionality
        $('#selectAll').on('change', function() {
            $('.invoice-checkbox').prop('checked', this.checked);
            updateBulkDiscountBtn();
        });

        // Individual checkbox change
        $(document).on('change', '.invoice-checkbox', function() {
            const totalCheckboxes = $('.invoice-checkbox').length;
            const checkedCheckboxes = $('.invoice-checkbox:checked').length;

            $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            updateBulkDiscountBtn();
        });

        // Update bulk discount button
        function updateBulkDiscountBtn() {
            const checkedCount = $('.invoice-checkbox:checked').length;
            $('#bulkDiscountBtn').prop('disabled', checkedCount === 0);
            $('#selectedCount').text(checkedCount);
        }

        // Bulk discount button click
        $('#bulkDiscountBtn').on('click', function() {
            const checkedCount = $('.invoice-checkbox:checked').length;
            $('#selectedCount').text(checkedCount);
            $('#bulkDiscountModal').modal('show');
        });

        // Apply single discount form
        $('#discountForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $('#applyDiscountBtn');
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Menerapkan...');

            $.ajax({
                url: '<?= base_url('invoice-discount/applyDiscount') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#discountModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-check me-1"></i> Terapkan Diskon');
                }
            });
        });

        // Apply bulk discount form
        $('#bulkDiscountForm').on('submit', function(e) {
            e.preventDefault();

            const selectedIds = [];
            $('.invoice-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire('Error!', 'Pilih minimal 1 invoice', 'error');
                return;
            }

            const btn = $('#applyBulkDiscountBtn');
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Menerapkan...');

            const formData = $(this).serialize() + '&invoice_ids=' + selectedIds.join(',');

            $.ajax({
                url: '<?= base_url('invoice-discount/bulkDiscount') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#bulkDiscountModal').modal('hide');
                        table.ajax.reload();
                        $('.invoice-checkbox').prop('checked', false);
                        $('#selectAll').prop('checked', false);
                        updateBulkDiscountBtn();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-check me-1"></i> Terapkan Diskon Massal');
                }
            });
        });
    });

    // Show discount modal
    function showDiscountModal(invoiceId, customerName, invoiceNo, totalBill) {
        $('#invoice_id').val(invoiceId);
        $('#customerName').text(customerName);
        $('#invoiceNo').text(invoiceNo);
        $('#totalBill').text(parseFloat(totalBill).toLocaleString('id-ID'));
        $('#discountForm')[0].reset();
        $('#invoice_id').val(invoiceId); // Reset again after form reset
        $('#discountModal').modal('show');
    }

    // Remove discount
    function removeDiscount(invoiceId) {
        Swal.fire({
            title: 'Hapus Diskon?',
            text: 'Apakah Anda yakin ingin menghapus diskon dari tagihan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('invoice-discount/removeDiscount') ?>',
                    type: 'POST',
                    data: {
                        invoice_id: invoiceId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            $('#invoicesTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    // Refresh table
    function refreshTable() {
        $('#invoicesTable').DataTable().ajax.reload();
    }
</script>
<?= $this->endSection() ?>