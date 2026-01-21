<?= $this->extend('layout/default'); ?>

<?= $this->section('styles') ?>
<link href="<?= base_url() ?>backend/assets/libs/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
<style>
    /* Remove ALL rounded corners from cards */
    .card,
    .card *,
    .card-body {
        border-radius: 0 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Prorate</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary" id="btnAddNew">
                            <i class="bx bx-plus"></i> Add New
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Prorate Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped align-middle nowrap" id="prorateTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Action</th>
                                    <th class="text-center">ID</th>
                                    <th>Invoice Month</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th class="text-end">Prorate</th>
                                    <th>Description</th>
                                    <th class="text-center">Created at</th>
                                    <th class="text-center">Last updated</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="prorateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Prorate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="prorateForm">
                <div class="modal-body">
                    <input type="hidden" id="prorateId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select" id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="invoice_month" class="form-label">Invoice Month <span class="text-danger">*</span></label>
                                <input type="month" class="form-control" id="invoice_month" name="invoice_month" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="DD/MM/YYYY" required readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="DD/MM/YYYY" required readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="prorate_amount" class="form-label">Prorate Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="prorate_amount" name="prorate_amount" step="0.01" required readonly>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" readonly></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url() ?>backend/assets/js/custom.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#prorateTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('prorate/data') ?>",
                type: "POST",
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    width: '80px',
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-danger" onclick="deleteProrate(${row.id})">
                                <i class="bx bx-trash"></i>
                            </button>
                        `;
                    }
                },
                {
                    data: 'id',
                    className: 'text-center',
                    width: '60px'
                },
                {
                    data: 'invoice_month'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'package'
                },
                {
                    data: 'prorate_amount',
                    className: 'text-end',
                    render: function(data) {
                        return 'Rp. ' + parseFloat(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'description'
                },
                {
                    data: 'created_at',
                    className: 'text-center'
                },
                {
                    data: 'updated_at',
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'asc']
            ],
            pageLength: 10,
            responsive: false,
            scrollX: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Load customers for dropdown
        function loadCustomers() {
            $.ajax({
                url: "<?= base_url('prorate/get-customers') ?>",
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Select Customer</option>';
                        response.data.forEach(function(customer) {
                            options += `<option value="${customer.id}" data-package="${customer.package}" data-price="${customer.price}">${customer.name} - ${customer.service_no}</option>`;
                        });
                        $('#customer_id').html(options);
                    }
                }
            });
        }

        // Calculate prorate when dates change
        $('#customer_id, #start_date, #end_date').on('change', function() {
            var customerId = $('#customer_id').val();
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            if (customerId && startDate && endDate) {
                var selectedOption = $('#customer_id option:selected');
                var price = parseFloat(selectedOption.data('price'));

                // Calculate days
                var start = new Date(startDate);
                var end = new Date(endDate);
                var diffTime = Math.abs(end - start);
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                // Get days in month
                var daysInMonth = new Date(start.getFullYear(), start.getMonth() + 1, 0).getDate();

                // Calculate prorate
                var prorateAmount = (price / daysInMonth) * diffDays;
                $('#prorate_amount').val(prorateAmount.toFixed(0));

                // Set description
                var startFormatted = start.toLocaleDateString('id-ID', {
                    day: '2-digit'
                });
                var endFormatted = end.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
                var monthName = start.toLocaleDateString('id-ID', {
                    month: 'long',
                    year: 'numeric'
                });

                $('#description').val(`Prorate dari tgl ${startFormatted} sampai ${endFormatted.split(' ')[0]} bulan ${monthName}`);
            }
        });

        // Add new button
        $('#btnAddNew').click(function() {
            $('#prorateForm')[0].reset();
            $('#prorateId').val('');
            $('#modalTitle').text('Add Prorate');
            loadCustomers();
            $('#prorateModal').modal('show');
        });

        // Submit form
        $('#prorateForm').submit(function(e) {
            e.preventDefault();

            var formData = {
                id: $('#prorateId').val(),
                customer_id: $('#customer_id').val(),
                invoice_month: $('#invoice_month').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                prorate_amount: $('#prorate_amount').val(),
                description: $('#description').val(),
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            };

            $.ajax({
                url: "<?= base_url('prorate/save') ?>",
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#prorateModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to save prorate', 'error');
                }
            });
        });
    });

    function deleteProrate(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('prorate/delete/') ?>" + id,
                    type: "POST",
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#prorateTable').DataTable().ajax.reload();
                            Swal.fire('Deleted!', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection(); ?>