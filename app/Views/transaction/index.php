<?= $this->extend('layout/default'); ?>

<?= $this->section('styles') ?>
<style>
    .card-summary {
        border-left: 3px solid;
        margin-bottom: 15px;
    }

    .card-summary .card-body {
        padding: 8px 12px;
    }

    .card-income {
        border-left-color: #34c38f;
    }

    .card-outcome {
        border-left-color: #f46a6a;
    }

    .card-saldo {
        border-left-color: #556ee6;
    }

    .summary-value {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 2px 0 0 0;
    }

    .summary-label {
        color: #74788d;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.2px;
        margin: 0;
        line-height: 1;
    }

    .summary-total {
        color: #74788d;
        font-size: 0.6rem;
        margin: 2px 0 0 0;
        line-height: 1;
    }

    .badge {
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: 500;
    }

    .month-selector {
        background: white;
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    /* Fix datepicker z-index and display in modal */
    .datepicker {
        z-index: 9999 !important;
    }

    .datepicker-dropdown {
        z-index: 9999 !important;
        display: block !important;
    }

    .datepicker.dropdown-menu {
        z-index: 9999 !important;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
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
                    <h4 class="mb-sm-0">Transaction</h4>
                </div>
            </div>
        </div>

        <!-- Month/Year Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="month-selector">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="filterMonth">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterYear">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="btnFilter">
                                <i class="bx bx-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card card-summary card-income">
                    <div class="card-body">
                        <p class="summary-label mb-1 text-success">Total Income</p>
                        <p class="summary-total mb-1" id="totalIncomeRaw">Rp. 0</p>
                        <h4 class="summary-value mb-0 text-bold" id="totalIncome">Rp. 0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-summary card-outcome">
                    <div class="card-body">
                        <p class="summary-label text-danger mb-1">Total Outcome</p>
                        <p class="summary-total mb-1" id="totalOutcomeRaw">Rp. 0</p>
                        <h4 class="summary-value mb-0 text-bold" id="totalOutcome">Rp. 0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-summary card-saldo">
                    <div class="card-body">
                        <p class="summary-label text-primary mb-1">Total Saldo</p>
                        <p class="summary-total mb-1" id="totalSaldoRaw">Rp. 0</p>
                        <h4 class="summary-value mb-0 text-bold" id="totalSaldo">Rp. 0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Transaction List</h5>
                            <button type="button" class="btn btn-primary btn-sm" id="btnAddTransaction">
                                <i class="bx bx-plus"></i> Add New
                            </button>
                        </div>
                        <table class="table table-bordered table-striped align-middle nowrap" id="transactionTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 100px;">Action</th>
                                    <th class="text-center" style="width: 60px;">ID</th>
                                    <th>Branch</th>
                                    <th>Date</th>
                                    <th>Transaction</th>
                                    <th>Payment Method</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Created at</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail View Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white" id="modalTitle">Detail Transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <tr>
                                <th width="35%">ID</th>
                                <td id="viewId">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Code</th>
                                <td id="viewCode">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Related ID (Invoice)</th>
                                <td id="viewRelatedId">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Branch</th>
                                <td id="viewBranch">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Date</th>
                                <td id="viewDate">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Payment Method</th>
                                <td id="viewPaymentMethod">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Category</th>
                                <td id="viewCategory">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Attachment</th>
                                <td id="viewAttachment">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Type</th>
                                <td id="viewType">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Amount</th>
                                <td><span class="fw-bold text-success fs-5" id="viewAmount">Rp. 0</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Transaction</th>
                                <td id="viewTransaction">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Description</th>
                                <td id="viewDescription">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created at</th>
                                <td id="viewCreatedAt">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created by</th>
                                <td id="viewCreatedBy">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Last Update</th>
                                <td id="viewUpdatedAt">-</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Updated by</th>
                                <td id="viewUpdatedBy">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<!-- Transaction Form Modal (Add/Edit) -->
<div class="modal fade" id="transactionFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalTitle">Add New Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm">
                <div class="modal-body">
                    <input type="hidden" id="transactionId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <select class="form-select" id="branch" name="branch" required>
                                    <option value="">Select Branch</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Transaction Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="transaction_name" name="transaction_name" required placeholder="e.g., Biaya Teknisi - Paket Internet">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select...</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="E-Wallet">E-Wallet</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Credit Card">Credit Card</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select...</option>
                                    <option value="Operasional - Instalasi">Operasional - Instalasi</option>
                                    <option value="Operasional - Maintenance">Operasional - Maintenance</option>
                                    <option value="Operasional - Marketing">Operasional - Marketing</option>
                                    <option value="Pendapatan - Pembayaran">Pendapatan - Pembayaran</option>
                                    <option value="Pendapatan - Instalasi">Pendapatan - Instalasi</option>
                                    <option value="Operasional - Gaji">Operasional - Gaji</option>
                                    <option value="Operasional - Lainnya">Operasional - Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select...</option>
                                    <option value="in">Income (IN)</option>
                                    <option value="out">Outcome (OUT)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" required min="0" step="0.01" placeholder="100000">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    var table;
    var currentMonth = '<?= $month ?>';
    var currentYear = '<?= $year ?>';

    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            var $branchSelect = $('#branch');
            $branchSelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Branch',
                dropdownParent: $('#transactionFormModal')
            });
            var $paymentSelect = $('#payment_method');
            $paymentSelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Payment Method',
                dropdownParent: $('#transactionFormModal')
            });
            var $categorySelect = $('#category');
            $categorySelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Category',
                dropdownParent: $('#transactionFormModal')
            });
            var $typeSelect = $('#type');
            $typeSelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Type',
                dropdownParent: $('#transactionFormModal')
            });
            // Ensure keyboard and click events open the dropdown inside modal
            $('#transactionFormModal').on('shown.bs.modal', function() {
                $branchSelect.trigger('change.select2');
                $paymentSelect.trigger('change.select2');
                $categorySelect.trigger('change.select2');
                $typeSelect.trigger('change.select2');
            });
        }

        // Load branch options (AJAX, dengan CSRF dan site_url)
        function loadBranches() {
            $.ajax({
                url: "<?= site_url('settings/branch/list') ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.data && Array.isArray(response.data)) {
                        var $branch = $('#branch');
                        $branch.empty().append('<option value="">Select Branch</option>');
                        response.data.forEach(function(branch) {
                            $branch.append('<option value="' + branch.id + '">' + branch.branch_name + '</option>');
                        });
                        // Set value from localStorage if exists
                        var lastBranch = localStorage.getItem('lastBranch');
                        if (lastBranch) {
                            $branch.val(lastBranch).trigger('change');
                        }
                    }
                }
            });
        }
        loadBranches();

        // Simpan branch ke localStorage saat user memilih
        $('#branch').on('change', function() {
            var val = $(this).val();
            if (val) localStorage.setItem('lastBranch', val);
        });
        // Set current month and year
        $('#filterMonth').val(currentMonth);
        $('#filterYear').val(currentYear);

        // Initialize DataTable
        initDataTable();

        // Load summary
        loadSummary();

        // Filter Button
        $('#btnFilter').click(function() {
            currentMonth = $('#filterMonth').val();
            currentYear = $('#filterYear').val();
            table.ajax.reload();
            loadSummary();
        });

        // View Transaction Button
        $('body').on('click', '.viewTransaction', function() {
            var transactionId = $(this).data('id');
            viewTransaction(transactionId);
        });

        // Delete Transaction Button
        $('body').on('click', '.deleteTransaction', function() {
            var transactionId = $(this).data('id');
            deleteTransaction(transactionId);
        });

        // Add New Button
        $('#btnAddTransaction').click(function() {
            $('#transactionForm')[0].reset();
            $('#transactionId').val('');
            $('#formModalTitle').text('Add New Transaction');
            var today = new Date().toISOString().split('T')[0];
            $('#date').val(today);
            $('#transactionFormModal').modal('show');
        });
    });

    // Submit Form
    $('#transactionForm').submit(function(e) {
        e.preventDefault();

        var transactionId = $('#transactionId').val();
        var url = transactionId ?
            "<?= base_url('transaction/update/') ?>" + transactionId :
            "<?= base_url('transaction/store') ?>";

        $.ajax({
            url: url,
            type: "POST",
            data: $(this).serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#transactionFormModal').modal('hide');
                    table.ajax.reload();
                    loadSummary();
                    Swal.fire('Success', response.message, 'success');
                } else {
                    var errorMsg = response.message;
                    if (response.errors) {
                        errorMsg += '<br>' + Object.values(response.errors).join('<br>');
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to save transaction', 'error');
            }
        });
    });


    function initDataTable() {
        table = $('#transactionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('transaction/data') ?>",
                type: "POST",
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                    d.month = currentMonth;
                    d.year = currentYear;
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-info viewTransaction" data-id="${row.id}" title="View Detail">
                                <i class="bx bx-show"></i>
                            </button>
                            <button class="btn btn-sm btn-danger deleteTransaction" data-id="${row.id}" title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        `;
                    }
                },
                {
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'branch_name',
                    render: function(data, type, row) {
                        return data || row.branch || '-';
                    }
                },
                {
                    data: 'date',
                    render: function(data) {
                        return new Date(data).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: 'transaction_name'
                },
                {
                    data: 'payment_method'
                },
                {
                    data: 'category'
                },
                {
                    data: 'description',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'type',
                    className: 'text-center',
                    render: function(data) {
                        if (data === 'in') {
                            return '<span class="badge bg-success">IN</span>';
                        } else {
                            return '<span class="badge bg-danger">OUT</span>';
                        }
                    }
                },
                {
                    data: 'amount',
                    className: 'text-end',
                    render: function(data) {
                        return 'Rp. ' + parseFloat(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleString('id-ID');
                    }
                }
            ],
            order: [
                [3, 'desc']
            ],
            pageLength: 10,
            responsive: false,
            scrollX: true
        });
    }

    function loadSummary() {
        $.ajax({
            url: "<?= base_url('transaction/getSummary') ?>",
            type: "POST",
            data: {
                month: currentMonth,
                year: currentYear,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    var income = parseFloat(response.data.total_income || 0);
                    var outcome = parseFloat(response.data.total_outcome || 0);
                    var saldo = parseFloat(response.data.total_saldo || 0);

                    var systemIncome = parseFloat(response.data.system_income || 0);
                    var systemOutcome = parseFloat(response.data.system_outcome || 0);
                    var systemSaldo = parseFloat(response.data.system_saldo || 0);

                    // Small text (system totals from cash_flow + invoices + gateway)
                    $('#totalIncomeRaw').text('Rp. ' + systemIncome.toLocaleString('id-ID'));
                    $('#totalOutcomeRaw').text('Rp. ' + systemOutcome.toLocaleString('id-ID'));
                    $('#totalSaldoRaw').text('Rp. ' + systemSaldo.toLocaleString('id-ID'));

                    // Large text (transactions table totals)
                    $('#totalIncome').text('Rp. ' + income.toLocaleString('id-ID'));
                    $('#totalOutcome').text('Rp. ' + outcome.toLocaleString('id-ID'));
                    $('#totalSaldo').text('Rp. ' + saldo.toLocaleString('id-ID'));

                    // Change color based on saldo
                    if (saldo >= 0) {
                        $('#totalSaldo').removeClass('text-danger').addClass('text-primary');
                    } else {
                        $('#totalSaldo').removeClass('text-primary').addClass('text-danger');
                    }
                }
            }
        });
    }

    function viewTransaction(id) {
        $.ajax({
            url: "<?= base_url('transaction/edit/') ?>" + id,
            type: "GET",
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;

                    // Populate modal with data
                    $('#viewId').text(data.id || '-');
                    $('#viewCode').text(data.code || '-');
                    $('#viewRelatedId').text(data.related_id || '-');
                    $('#viewBranch').text(data.branch || '-');

                    // Format date
                    var date = data.date ? new Date(data.date).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    }) : '-';
                    $('#viewDate').text(date);

                    $('#viewPaymentMethod').text(data.payment_method || '-');
                    $('#viewCategory').text(data.category || '-');
                    $('#viewAttachment').text(data.attachment || '-');

                    // Type badge
                    var typeHtml = data.type === 'in' || data.type === 'In' ?
                        '<span class="badge bg-success">In</span>' :
                        '<span class="badge bg-danger">Out</span>';
                    $('#viewType').html(typeHtml);

                    // Format amount
                    var amount = parseFloat(data.amount || 0);
                    var amountClass = (data.type === 'in' || data.type === 'In') ? 'text-success' : 'text-danger';
                    $('#viewAmount').removeClass('text-success text-danger').addClass(amountClass);
                    $('#viewAmount').text('Rp. ' + amount.toLocaleString('id-ID'));

                    $('#viewTransaction').text(data.transaction || data.transaction_name || '-');
                    $('#viewDescription').text(data.description || '-');

                    // Format timestamps
                    var createdAt = data.created_at ? new Date(data.created_at).toLocaleString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';
                    $('#viewCreatedAt').text(createdAt);

                    $('#viewCreatedBy').text(data.created_by || 'Admin');

                    var updatedAt = data.updated_at ? data.updated_at : '-';
                    $('#viewUpdatedAt').text(updatedAt);

                    $('#viewUpdatedBy').text(data.updated_by || '-');

                    $('#transactionModal').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'Failed to load transaction', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load transaction', 'error');
            }
        });
    }

    function deleteTransaction(id) {
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
                    url: "<?= base_url('transaction/delete/') ?>" + id,
                    type: "POST",
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            loadSummary();
                            Swal.fire('Deleted!', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to delete transaction', 'error');
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection(); ?>