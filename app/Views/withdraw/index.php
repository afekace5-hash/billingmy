<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Withdraw History &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .table-responsive {
        overflow-x: auto;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }

    .modal-header {
        background-color: #1abc9c;
        color: white;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    /* Button group styling */
    .btn-group .btn {
        margin: 0;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Withdraw History</h4>
                    <div class="page-title-right">
                        <button type="button" id="btnRequestWithdraw" class="btn btn-info">
                            <i class="bx bx-dollar"></i> Request Withdraw
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle table-bordered withdraw-datatable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="120px">Action</th>
                                        <th>Code</th>
                                        <th>Bank</th>
                                        <th>Account Name</th>
                                        <th>Amount</th>
                                        <th>Status Payment</th>
                                        <th>Disbursement</th>
                                        <th>Created at</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Request Withdraw -->
        <div class="modal fade" id="withdrawModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Request Withdraw</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="withdrawForm">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <strong>Available Balance:</strong>
                                    <span id="availableBalance" class="fw-bold">Rp 0</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" required min="1" placeholder="100000">
                            </div>

                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <select class="form-select" id="bank_name" name="bank_name" required>
                                    <option value="">Select Bank</option>
                                    <option value="BCA">BCA</option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                    <option value="CIMB Niaga">CIMB Niaga</option>
                                    <option value="Danamon">Danamon</option>
                                    <option value="Permata">Permata</option>
                                    <option value="BTN">BTN</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="account_number" name="account_number" required placeholder="1234567890">
                            </div>

                            <div class="mb-3">
                                <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="account_name" name="account_name" required placeholder="John Doe">
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Detail Withdraw -->
        <div class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Withdraw Detail</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Code</strong></td>
                                <td>: <span id="detail_code"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Amount</strong></td>
                                <td>: <span id="detail_amount"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Bank Name</strong></td>
                                <td>: <span id="detail_bank"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Account Number</strong></td>
                                <td>: <span id="detail_account_number"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Account Name</strong></td>
                                <td>: <span id="detail_account_name"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>: <span id="detail_status"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Notes</strong></td>
                                <td>: <span id="detail_notes"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Admin Notes</strong></td>
                                <td>: <span id="detail_admin_notes"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Created at</strong></td>
                                <td>: <span id="detail_created"></span></td>
                            </tr>
                            <tr id="row_disbursement_info" style="display: none;">
                                <td colspan="2">
                                    <hr>
                                    <h6 class="text-primary"><i class="bx bx-send"></i> Disbursement Info</h6>
                                </td>
                            </tr>
                            <tr id="row_provider" style="display: none;">
                                <td><strong>Provider</strong></td>
                                <td>: <span id="detail_provider"></span></td>
                            </tr>
                            <tr id="row_reference" style="display: none;">
                                <td><strong>Reference ID</strong></td>
                                <td>: <span id="detail_reference"></span></td>
                            </tr>
                            <tr id="row_disburse_status" style="display: none;">
                                <td><strong>Disburse Status</strong></td>
                                <td>: <span id="detail_disburse_status"></span></td>
                            </tr>
                            <tr id="row_fee" style="display: none;">
                                <td><strong>Fee</strong></td>
                                <td>: <span id="detail_fee"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Update Status -->
        <div class="modal fade" id="statusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="statusForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="status_id" name="status_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="admin_notes" class="form-label">Admin Notes</label>
                                <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Optional admin notes"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Auto Disbursement -->
        <div class="modal fade" id="disbursementModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bx bx-send"></i> Auto Disbursement</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="disburse_id">

                        <!-- Provider Selection -->
                        <div class="mb-3">
                            <label for="provider" class="form-label">Payment Gateway <span class="text-danger">*</span></label>
                            <select class="form-select" id="provider" name="provider" required>
                                <option value="flip">FLIP (Recommended)</option>
                                <option value="xendit">XENDIT</option>
                                <option value="midtrans">MIDTRANS IRIS</option>
                            </select>
                            <div class="form-text">Select payment gateway to process disbursement</div>
                        </div>

                        <!-- Balance Check -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-info" id="btnCheckBalance">
                                <i class="bx bx-wallet"></i> Check Balance
                            </button>
                            <span id="balanceInfo" class="ms-2 text-muted"></span>
                        </div>

                        <!-- Bank Account Validation -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnValidateBank">
                                <i class="bx bx-check-circle"></i> Validate Bank Account
                            </button>
                            <div id="validationResult" class="mt-2"></div>
                        </div>

                        <hr>

                        <!-- Withdraw Info -->
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="130"><strong>Code</strong></td>
                                <td>: <span id="disburse_code"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Amount</strong></td>
                                <td>: <span id="disburse_amount" class="text-primary fw-bold"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Bank</strong></td>
                                <td>: <span id="disburse_bank"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Account Number</strong></td>
                                <td>: <span id="disburse_account"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Account Name</strong></td>
                                <td>: <span id="disburse_name"></span></td>
                            </tr>
                        </table>

                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle"></i> Make sure bank account information is correct. Transaction cannot be reversed!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="btnProcessDisburse">
                            <i class="bx bx-check"></i> Process Disbursement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // CSRF Token
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function() {
        console.log('=== WITHDRAW PAGE LOADED ===');
        console.log('jQuery loaded:', typeof jQuery !== 'undefined');
        console.log('jQuery version:', $.fn.jquery);
        console.log('DataTables loaded:', typeof $.fn.DataTable !== 'undefined');
        console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
        console.log('CSRF Token:', csrfToken);
        console.log('CSRF Hash:', csrfHash);
        console.log('Button exists:', $('#btnRequestWithdraw').length);
        console.log('Table exists:', $('.withdraw-datatable').length);

        // Initialize DataTable
        console.log('Initializing DataTable...');
        const table = $('.withdraw-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('withdraw/data') ?>',
                type: 'GET', // Changed to GET to avoid CSRF
                dataSrc: function(json) {
                    console.log('Response:', json);
                    if (json.error) {
                        console.error('Server error:', json.error);
                        alert('Error: ' + json.error);
                    }
                    return json.data || [];
                },
                error: function(xhr, error, code) {
                    console.error('DataTables Error:', error, code);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading data: ' + error + '. Check console for details.');
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).css('padding', '8px');
                    },
                    render: function(data, type, row) {
                        let buttons = '<div class="btn-group" role="group">';

                        // View Detail - Always visible
                        buttons += '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="' + row.id + '" title="View Detail"><i class="bx bx-show"></i></button>';

                        // Auto Disburse button - only for pending
                        if (row.status === 'pending') {
                            buttons += '<button type="button" class="btn btn-sm btn-success btn-disburse" ' +
                                'data-id="' + row.id + '" ' +
                                'data-bank="' + row.bank_name + '" ' +
                                'data-account="' + row.account_number + '" ' +
                                'data-name="' + row.account_name + '" ' +
                                'data-amount="' + row.amount + '" ' +
                                'data-code="' + row.code + '" ' +
                                'title="Auto Disburse"><i class="bx bx-send"></i></button>';
                        }

                        // Check Status button - only for processing with disbursement
                        if (row.status === 'processing' && row.disbursement_reference) {
                            buttons += '<button type="button" class="btn btn-sm btn-primary btn-check-status" data-id="' + row.id + '" title="Check Status"><i class="bx bx-refresh"></i></button>';
                        }

                        // Manual status update - for pending/processing
                        if (row.status === 'pending' || row.status === 'processing') {
                            buttons += '<button type="button" class="btn btn-sm btn-warning btn-status" data-id="' + row.id + '" title="Update Status"><i class="bx bx-edit"></i></button>';
                        }

                        // Delete - for pending/rejected
                        if (row.status === 'pending' || row.status === 'rejected') {
                            buttons += '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' + row.id + '" title="Delete"><i class="bx bx-trash"></i></button>';
                        }

                        buttons += '</div>';
                        return buttons;
                    }
                },
                {
                    data: 'code'
                },
                {
                    data: 'bank_name'
                },
                {
                    data: 'account_name'
                },
                {
                    data: 'amount',
                    render: function(data) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        const badges = {
                            'pending': 'warning',
                            'processing': 'info',
                            'completed': 'success',
                            'rejected': 'danger'
                        };
                        return `<span class="badge bg-${badges[data] || 'secondary'}">${data.toUpperCase()}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        if (row.disbursement_provider) {
                            let statusBadge = '';
                            if (row.disbursement_status === 'SUCCESS' || row.disbursement_status === 'COMPLETED') {
                                statusBadge = '<span class="badge bg-success">SUCCESS</span>';
                            } else if (row.disbursement_status === 'PENDING') {
                                statusBadge = '<span class="badge bg-warning">PENDING</span>';
                            } else if (row.disbursement_status === 'FAILED') {
                                statusBadge = '<span class="badge bg-danger">FAILED</span>';
                            } else {
                                statusBadge = `<span class="badge bg-info">${row.disbursement_status || 'PROCESSING'}</span>`;
                            }

                            let feeText = row.disbursement_fee ? '<br><small>Fee: Rp ' + new Intl.NumberFormat('id-ID').format(row.disbursement_fee) + '</small>' : '';

                            return `
                                <div>
                                    <strong>${row.disbursement_provider.toUpperCase()}</strong><br>
                                    ${statusBadge}
                                    ${feeText}
                                    ${row.disbursement_reference ? '<br><small class="text-muted">Ref: ' + row.disbursement_reference + '</small>' : ''}
                                </div>
                            `;
                        }
                        return '<span class="text-muted">Manual</span>';
                    }
                },
                {
                    data: 'created_at'
                }
            ],
            order: [
                [1, 'desc']
            ],
            drawCallback: function() {
                // Re-initialize tooltips after table draw
                $('[title]').tooltip({
                    container: 'body',
                    trigger: 'hover'
                });
            }
        });

        // Request Withdraw Button Handler
        $('#btnRequestWithdraw').on('click', function() {
            console.log('REQUEST WITHDRAW BUTTON CLICKED!');

            // Reset form
            $('#withdrawForm')[0].reset();

            // Load available balance
            console.log('Loading available balance...');
            $.ajax({
                url: '<?= base_url('withdraw/available-balance') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Balance response:', response);
                    if (response.success) {
                        $('#availableBalance').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.available_balance));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Balance error:', error);
                    $('#availableBalance').text('Rp 0');
                }
            });

            // Show modal - support both Bootstrap 3, 4, and 5
            console.log('Opening modal...');
            var $modal = $('#withdrawModal');
            console.log('Modal element:', $modal.length);

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                // Bootstrap 5
                console.log('Using Bootstrap 5 Modal');
                var myModal = new bootstrap.Modal($modal[0]);
                myModal.show();
            } else if ($.fn.modal) {
                // Bootstrap 3/4
                console.log('Using Bootstrap 3/4 Modal');
                $modal.modal('show');
            } else {
                console.error('Bootstrap modal not available!');
                alert('Modal system not loaded. Please refresh the page.');
            }
        });

        // Submit Withdraw Form
        $('#withdrawForm').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                amount: $('#amount').val(),
                bank_name: $('#bank_name').val(),
                account_number: $('#account_number').val(),
                account_name: $('#account_name').val(),
                notes: $('#notes').val(),
                [csrfToken]: csrfHash
            };

            $.ajax({
                url: '<?= base_url('withdraw/create') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.csrf_hash;

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#withdrawModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to submit withdraw request', 'error');
                }
            });
        });

        // View Detail
        $(document).on('click', '.btn-detail', function() {
            const id = $(this).data('id');

            $.ajax({
                url: '<?= base_url('withdraw/detail/') ?>' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#detail_code').text(data.code);
                        $('#detail_amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.amount));
                        $('#detail_bank').text(data.bank_name);
                        $('#detail_account_number').text(data.account_number);
                        $('#detail_account_name').text(data.account_name);
                        $('#detail_status').html(`<span class="badge bg-${data.status === 'completed' ? 'success' : data.status === 'rejected' ? 'danger' : 'warning'}">${data.status.toUpperCase()}</span>`);
                        $('#detail_notes').text(data.notes || '-');
                        $('#detail_admin_notes').text(data.admin_notes || '-');
                        $('#detail_created').text(data.created_at);

                        // Show disbursement info if available
                        if (data.disbursement_provider) {
                            $('#row_disbursement_info').show();
                            $('#row_provider').show();
                            $('#row_reference').show();
                            $('#row_disburse_status').show();

                            $('#detail_provider').text(data.disbursement_provider.toUpperCase());
                            $('#detail_reference').text(data.disbursement_reference || '-');

                            let statusBadge = '';
                            if (data.disbursement_status === 'SUCCESS' || data.disbursement_status === 'COMPLETED') {
                                statusBadge = '<span class="badge bg-success">SUCCESS</span>';
                            } else if (data.disbursement_status === 'PENDING') {
                                statusBadge = '<span class="badge bg-warning">PENDING</span>';
                            } else if (data.disbursement_status === 'FAILED') {
                                statusBadge = '<span class="badge bg-danger">FAILED</span>';
                            } else {
                                statusBadge = `<span class="badge bg-info">${data.disbursement_status || 'PROCESSING'}</span>`;
                            }
                            $('#detail_disburse_status').html(statusBadge);

                            if (data.disbursement_fee) {
                                $('#row_fee').show();
                                $('#detail_fee').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.disbursement_fee));
                            } else {
                                $('#row_fee').hide();
                            }
                        } else {
                            $('#row_disbursement_info').hide();
                            $('#row_provider').hide();
                            $('#row_reference').hide();
                            $('#row_disburse_status').hide();
                            $('#row_fee').hide();
                        }

                        $('#detailModal').modal('show');
                    }
                }
            });
        });

        // Update Status
        $(document).on('click', '.btn-status', function() {
            const id = $(this).data('id');
            $('#status_id').val(id);
            $('#statusModal').modal('show');
        });

        // Submit Status Form
        $('#statusForm').on('submit', function(e) {
            e.preventDefault();

            const id = $('#status_id').val();
            const formData = {
                status: $('#status').val(),
                admin_notes: $('#admin_notes').val(),
                [csrfToken]: csrfHash
            };

            $.ajax({
                url: '<?= base_url('withdraw/update-status/') ?>' + id,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.csrf_hash;

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#statusModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

        // Delete Withdraw
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('withdraw/delete/') ?>' + id,
                        type: 'DELETE',
                        data: {
                            [csrfToken]: csrfHash
                        },
                        dataType: 'json',
                        success: function(response) {
                            csrfHash = response.csrf_hash;

                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // ==================== AUTO DISBURSEMENT FUNCTIONS ====================

        // Open Disbursement Modal
        $(document).on('click', '.btn-disburse', function() {
            const id = $(this).data('id');
            const bank = $(this).data('bank');
            const account = $(this).data('account');
            const name = $(this).data('name');
            const amount = $(this).data('amount');
            const code = $(this).data('code');

            $('#disburse_id').val(id);
            $('#disburse_code').text(code);
            $('#disburse_amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(amount));
            $('#disburse_bank').text(bank);
            $('#disburse_account').text(account);
            $('#disburse_name').text(name);
            $('#balanceInfo').text('');
            $('#validationResult').html('');

            $('#disbursementModal').modal('show');
        });

        // Check Balance
        $(document).on('click', '#btnCheckBalance', function() {
            const provider = $('#provider').val();
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Checking...');

            $.ajax({
                url: '<?= base_url('withdraw/disbursement-balance') ?>',
                type: 'POST',
                data: {
                    provider: provider,
                    [csrfToken]: csrfHash
                },
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.csrf_hash;
                    btn.prop('disabled', false).html('<i class="bx bx-wallet"></i> Check Balance');

                    if (response.success) {
                        $('#balanceInfo').html(`<strong class="text-success">Balance: Rp ${new Intl.NumberFormat('id-ID').format(response.balance)}</strong>`);
                    } else {
                        $('#balanceInfo').html(`<span class="text-danger">${response.message}</span>`);
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-wallet"></i> Check Balance');
                    $('#balanceInfo').html('<span class="text-danger">Failed to check balance</span>');
                }
            });
        });

        // Validate Bank Account
        $(document).on('click', '#btnValidateBank', function() {
            const provider = $('#provider').val();
            const bankName = $('#disburse_bank').text();
            const accountNumber = $('#disburse_account').text();
            const btn = $(this);

            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Validating...');

            $.ajax({
                url: '<?= base_url('withdraw/validate-bank-account') ?>',
                type: 'POST',
                data: {
                    provider: provider,
                    bank_name: bankName,
                    account_number: accountNumber,
                    [csrfToken]: csrfHash
                },
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.csrf_hash;
                    btn.prop('disabled', false).html('<i class="bx bx-check-circle"></i> Validate Bank Account');

                    if (response.success) {
                        $('#validationResult').html(`
                            <div class="alert alert-success">
                                <i class="bx bx-check-circle"></i> 
                                <strong>Valid!</strong><br>
                                Account Name: <strong>${response.account_name || 'Verified'}</strong>
                            </div>
                        `);
                    } else {
                        $('#validationResult').html(`
                            <div class="alert alert-danger">
                                <i class="bx bx-x-circle"></i> ${response.message}
                            </div>
                        `);
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-check-circle"></i> Validate Bank Account');
                    $('#validationResult').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-x-circle"></i> Failed to validate bank account
                        </div>
                    `);
                }
            });
        });

        // Process Disbursement
        $(document).on('click', '#btnProcessDisburse', function() {
            const id = $('#disburse_id').val();
            const provider = $('#provider').val();
            const btn = $(this);

            Swal.fire({
                title: 'Confirm Disbursement?',
                html: `
                    <p>You are about to transfer money to:</p>
                    <p><strong>Bank:</strong> ${$('#disburse_bank').text()}<br>
                    <strong>Account:</strong> ${$('#disburse_account').text()}<br>
                    <strong>Name:</strong> ${$('#disburse_name').text()}<br>
                    <strong>Amount:</strong> ${$('#disburse_amount').text()}</p>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Process!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Processing...');

                    $.ajax({
                        url: '<?= base_url('withdraw/process-disbursement/') ?>' + id,
                        type: 'POST',
                        data: {
                            provider: provider,
                            [csrfToken]: csrfHash
                        },
                        dataType: 'json',
                        success: function(response) {
                            csrfHash = response.csrf_hash;
                            btn.prop('disabled', false).html('<i class="bx bx-check"></i> Process Disbursement');

                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Disbursement Sent!',
                                    html: `
                                        <p>${response.message}</p>
                                        ${response.reference_id ? '<p><strong>Reference ID:</strong> ' + response.reference_id + '</p>' : ''}
                                        ${response.fee ? '<p><strong>Fee:</strong> Rp ' + new Intl.NumberFormat('id-ID').format(response.fee) + '</p>' : ''}
                                    `,
                                    timer: 3000,
                                    showConfirmButton: true
                                });
                                $('#disbursementModal').modal('hide');
                                table.ajax.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Disbursement Failed',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="bx bx-check"></i> Process Disbursement');

                            let errorMsg = 'Failed to process disbursement';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }

                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        // Check Disbursement Status
        $(document).on('click', '.btn-check-status', function() {
            const id = $(this).data('id');
            const btn = $(this);

            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

            $.ajax({
                url: '<?= base_url('withdraw/check-disbursement/') ?>' + id,
                type: 'POST',
                data: {
                    [csrfToken]: csrfHash
                },
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.csrf_hash;
                    btn.prop('disabled', false).html('<i class="bx bx-refresh"></i>');

                    if (response.success) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Status Updated',
                            html: `
                                <p><strong>Status:</strong> ${response.status}</p>
                                ${response.message ? '<p>' + response.message + '</p>' : ''}
                            `,
                            timer: 2000
                        });
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-refresh"></i>');
                    Swal.fire('Error', 'Failed to check status', 'error');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>