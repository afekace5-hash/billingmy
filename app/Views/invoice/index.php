<?= $this->extend('layout/default'); ?>

<?= $this->section('styles') ?>
<style>
    .card,
    .card *,
    .card-body {
        border-radius: 0 !important;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 11px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Invoice</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-info btn-sm me-2 custom-radius" id="btnBroadcast" style="display:inline-flex;align-items:center;justify-content:center;">
                            <i class="bx bx-broadcast" style="padding-right: 5px;"></i> Broadcast Invoice
                        </button>
                        <button type="button" class="btn btn-primary btn-sm me-2 custom-radius" id="btnCreateSingle" style="display:inline-flex;align-items:center;justify-content:center;">
                            <i class="bx bx-plus" style="padding-right: 5px;"></i> Create Single Invoice
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm me-2 custom-radius" id="btnFilter" style="display:inline-flex;align-items:center;justify-content:center;">
                            <i class="bx bx-filter" style="padding-right: 5px;"></i> Filter
                        </button>
                        <button type="button" class="btn btn-success btn-sm custom-radius" id="btnExport" style="display:inline-flex;align-items:center;justify-content:center;">
                            <i class="bx bx-download" style="padding-right: 5px;"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widgets -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2">Total Invoice</p>
                                <h3 class="mb-0 text-info" id="widgetTotalInvoice">Rp. 0</h3>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <i class="bx bx-notepad icon-xl text-info mb-0"></i>
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
                                <p class="text-muted mb-2">Total Paid</p>
                                <h3 class="mb-0 text-success" id="widgetTotalPaid">Rp. 0</h3>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <i class="bx bx-check-circle  icon-xl text-success mb-0"></i>
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
                                <p class="text-muted mb-2">Total Unpaid</p>
                                <h3 class="mb-0 text-danger" id="widgetTotalUnpaid">Rp. 0</h3>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <i class="bx bx-x-circle icon-xl text-danger mb-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped align-middle nowrap" id="invoiceTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Action</th>
                                    <th class="text-center">ID</th>
                                    <th>Code</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Status Payment</th>
                                    <th class="text-center">Is Paid?</th>
                                    <th class="text-center">Has Claimed?</th>
                                    <th>Invoice Month</th>
                                    <th>Installed at</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Invoices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="filter_status" class="form-label">Status Payment</label>
                    <select class="form-select" id="filter_status">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="filter_type" class="form-label">Type</label>
                    <select class="form-select" id="filter_type">
                        <option value="">All Types</option>
                        <option value="Bulanan">Bulanan</option>
                        <option value="Instalasi">Instalasi</option>
                        <option value="Prorate">Prorate</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="filter_month" class="form-label">Invoice Month</label>
                    <input type="month" class="form-control" id="filter_month">
                </div>
                <div class="mb-3">
                    <label for="filter_is_paid" class="form-label">Payment Status</label>
                    <select class="form-select" id="filter_is_paid">
                        <option value="">All</option>
                        <option value="1">Paid</option>
                        <option value="0">Unpaid</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnResetFilter">Reset</button>
                <button type="button" class="btn btn-primary" id="btnApplyFilter">Apply Filter</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Single Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Single Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createInvoiceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Select Customer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="periode" class="form-label">Period <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" id="periode" name="periode" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" step="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
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
        // Load widget data
        loadWidgetData();

        // Initialize DataTable
        var table = $('#invoiceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('invoice/data') ?>",
                type: "POST",
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                    // Add filter parameters
                    d.filter_status = $('#filter_status').val();
                    d.filter_type = $('#filter_type').val();
                    d.filter_month = $('#filter_month').val();
                    d.filter_is_paid = $('#filter_is_paid').val();
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    width: '100px',
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-info" onclick="viewInvoice(${row.id})">
                                <i class="bx bx-show"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteInvoice(${row.id})">
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
                    data: 'invoice_number'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'package_name'
                },
                {
                    data: 'total',
                    className: 'text-end'
                },
                {
                    data: 'type',
                    className: 'text-center',
                    render: function(data) {
                        if (data == 'Bulanan') {
                            return '<span class="badge bg-primary">Bulanan</span>';
                        } else if (data == 'Instalasi') {
                            return '<span class="badge bg-warning">Instalasi</span>';
                        } else if (data == 'Prorate') {
                            return '<span class="badge bg-info">Prorate</span>';
                        } else {
                            return '<span class="badge bg-secondary">' + data + '</span>';
                        }
                    }
                },
                {
                    data: 'status_payment',
                    className: 'text-center',
                    render: function(data) {
                        if (data == 'paid') {
                            return '<span class="badge bg-success">Paid</span>';
                        } else if (data == 'pending') {
                            return '<span class="badge bg-warning">Pending</span>';
                        } else {
                            return '<span class="badge bg-danger">Unpaid</span>';
                        }
                    }
                },
                {
                    data: 'is_paid',
                    className: 'text-center',
                    render: function(data) {
                        return data == 1 ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-danger">Pending</span>';
                    }
                },
                {
                    data: 'has_claimed',
                    className: 'text-center',
                    render: function(data) {
                        return data == 1 ? '<span class="badge bg-info">Ready to Claim</span>' : '<span class="badge bg-secondary">Pending Paid</span>';
                    }
                },
                {
                    data: 'invoice_month'
                },
                {
                    data: 'installed_at',
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
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

        function loadWidgetData() {
            $.ajax({
                url: "<?= base_url('invoice/get-widget-data') ?>",
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        $('#widgetTotalInvoice').text('Rp. ' + response.data.total_invoice.toLocaleString('id-ID'));
                        $('#widgetTotalPaid').text('Rp. ' + response.data.total_paid.toLocaleString('id-ID'));
                        $('#widgetTotalUnpaid').text('Rp. ' + response.data.total_unpaid.toLocaleString('id-ID'));
                    }
                }
            });
        }

        // Load customers for dropdown
        function loadCustomers() {
            $.ajax({
                url: "<?= base_url('prorate/get-customers') ?>",
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Select Customer</option>';
                        response.data.forEach(function(customer) {
                            options += `<option value="${customer.id}">${customer.name} - ${customer.service_no}</option>`;
                        });
                        $('#customer_id').html(options);
                    }
                }
            });
        }

        // Create Single Invoice
        $('#btnCreateSingle').click(function() {
            loadCustomers();
            $('#createInvoiceForm')[0].reset();
            $('#createInvoiceModal').modal('show');
        });

        $('#createInvoiceForm').submit(function(e) {
            e.preventDefault();

            var formData = {
                customer_id: $('#customer_id').val(),
                periode: $('#periode').val(),
                amount: $('#amount').val(),
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            };

            $.ajax({
                url: "<?= base_url('invoice/store') ?>",
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#createInvoiceModal').modal('hide');
                        table.ajax.reload();
                        loadWidgetData();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to create invoice', 'error');
                }
            });
        });

        // Broadcast Invoice
        $('#btnBroadcast').click(function() {
            Swal.fire({
                title: 'Broadcast Invoice?',
                text: "This will send invoices to all customers",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, broadcast it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= base_url('invoice/broadcast') ?>",
                        type: "POST",
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success!', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Export
        $('#btnExport').click(function() {
            window.location.href = "<?= base_url('invoice/export') ?>";
        });

        // Filter Modal
        $('#btnFilter').click(function() {
            $('#filterModal').modal('show');
        });

        // Apply Filter
        $('#btnApplyFilter').click(function() {
            table.ajax.reload();
            $('#filterModal').modal('hide');
        });

        // Reset Filter
        $('#btnResetFilter').click(function() {
            $('#filter_status').val('');
            $('#filter_type').val('');
            $('#filter_month').val('');
            $('#filter_is_paid').val('');
            table.ajax.reload();
            $('#filterModal').modal('hide');
        });
    });

    function viewInvoice(id) {
        // Redirect to invoice detail page
        window.location.href = "<?= base_url('invoice/view/') ?>" + id;
    }

    function deleteInvoice(id) {
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
                    url: "<?= base_url('invoice/delete/') ?>" + id,
                    type: "POST",
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#invoiceTable').DataTable().ajax.reload();
                            loadWidgetData();
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