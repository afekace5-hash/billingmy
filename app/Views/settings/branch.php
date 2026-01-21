<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Master Branch &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* Responsive table improvements */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Mobile responsive styles */
    @media (max-width: 768px) {
        .branch_datatable {
            font-size: 11px;
        }

        .branch_datatable td {
            padding: 6px 4px;
            white-space: nowrap;
        }

        .branch_datatable th {
            padding: 8px 4px;
            font-size: 11px;
        }

        .branch_datatable .btn {
            padding: 3px 6px;
            font-size: 10px;
        }

        .badge {
            font-size: 9px;
            padding: 3px 6px;
        }

        .btn-group .btn {
            padding: 2px 4px;
            font-size: 10px;
        }

        /* Hide less important columns on mobile */
        .branch_datatable .hide-mobile {
            display: none !important;
        }
    }

    @media (max-width: 576px) {
        .branch_datatable {
            font-size: 10px;
        }

        .branch_datatable td {
            padding: 4px 2px;
        }

        .branch_datatable .btn {
            padding: 2px 4px;
            font-size: 9px;
        }

        .badge {
            font-size: 8px;
            padding: 2px 4px;
        }
    }

    /* Button group styling */
    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* Improve badge visibility */
    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }

    /* Compact table styling */
    .branch_datatable.table-sm td {
        padding: 0.25rem;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Master Branch</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                            <li class="breadcrumb-item active">Master Branch</li>
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
                        <!-- Alert for migration notice -->
                        <div id="migrationAlert" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            <strong>Database belum siap!</strong> Silakan jalankan migration terlebih dahulu dengan command: <code>php spark migrate</code>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="mb-4">
                            <a href="javascript:void(0)" id="createNewBranch"
                                class="btn btn-primary waves-effect btn-label waves-light">
                                <i class="bx bx-plus label-icon"></i>
                                Create New Branch
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle table-bordered branch_datatable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="80px">Action</th>
                                        <th>ID</th>
                                        <th>Branch</th>
                                        <th>City</th>
                                        <th>Payment Type</th>
                                        <th>Tgl Jatuh Tempo</th>
                                        <th>Day Before Due Date</th>
                                        <th>Created by</th>
                                        <th>Created at</th>
                                        <th>Last update</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Create/Edit Branch -->
        <div class="modal fade" id="branchModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHeading">Add New Branch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="branchForm" name="branchForm" class="form-horizontal">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div id="formErrors" class="alert alert-danger" style="display: none;"></div>

                            <input type="hidden" class="form-control" id="branch_id" name="branch_id">
                            <input type="hidden" class="form-control" id="method" name="method">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="branch_name" class="col-form-label">Branch Name<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="Jakarta Timur (Demo)">
                                        <span id="errorBranchName" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="city" class="col-form-label">City<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="city" name="city" placeholder="KOTA ADM. JAKARTA TIMUR">
                                        <span id="errorCity" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="payment_type" class="col-form-label">Payment Type<span class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_type" name="payment_type">
                                            <option value="">Select Payment Type</option>
                                            <option value="Bayar diawal">Bayar diawal</option>
                                            <option value="Bayar diakhir">Bayar diakhir</option>
                                        </select>
                                        <span id="errorPaymentType" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="due_date" class="col-form-label">Tgl Jatuh Tempo<span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="due_date" name="due_date" placeholder="10" min="1" max="31">
                                        <small class="text-muted">Tanggal jatuh tempo (1-31)</small>
                                        <span id="errorDueDate" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="day_before_due_date" class="col-form-label">Day Before Due Date<span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="day_before_due_date" name="day_before_due_date" placeholder="9" min="0" max="30">
                                        <small class="text-muted">Hari sebelum jatuh tempo untuk reminder</small>
                                        <span id="errorDayBeforeDueDate" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="address" class="col-form-label">Address</label>
                                        <textarea class="form-control" name="address" id="address" rows="3" placeholder="Branch Address"></textarea>
                                        <span id="errorAddress" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="description" class="col-form-label">Description</label>
                                        <textarea class="form-control" name="description" id="description" rows="2" placeholder="Additional notes or description"></textarea>
                                    </div>
                                </div>
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
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('.branch_datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('settings/branch/list') ?>",
                type: "POST",
                data: function(d) {
                    d.<?= csrf_token() ?> = $('meta[name="csrf-token"]').attr('content');
                },
                error: function(xhr, error, code) {
                    console.log('Error:', xhr, error, code);
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        $('#migrationAlert').show();
                        Swal.fire({
                            icon: 'error',
                            title: 'Database Error',
                            html: '<p>Tabel branches belum ada. Silakan jalankan migration:</p><code>php spark migrate</code>',
                            footer: 'Error: ' + xhr.responseJSON.error
                        });
                    }
                }
            },
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'city',
                    name: 'city'
                },
                {
                    data: 'payment_type',
                    name: 'payment_type'
                },
                {
                    data: 'due_date',
                    name: 'due_date'
                },
                {
                    data: 'day_before_due_date',
                    name: 'day_before_due_date',
                    render: function(data, type, row) {
                        return data + ' days';
                    }
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at'
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 10,
            responsive: true,
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Create New Branch Button
        $('#createNewBranch').click(function() {
            $('#saveBtn').val("create");
            $('#branch_id').val('');
            $('#method').val('create');
            $('#branchForm').trigger("reset");
            $('#modalHeading').html("Add New Branch");
            $('#formErrors').hide();
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            $('#branchModal').modal('show');
        });

        // Edit Branch
        $('body').on('click', '.editBranch', function() {
            var branch_id = $(this).data('id');
            $.ajax({
                url: "<?= site_url('settings/branch/edit') ?>/" + branch_id,
                type: "GET",
                dataType: 'json',
                success: function(data) {
                    $('#modalHeading').html("Edit Branch");
                    $('#saveBtn').val("edit");
                    $('#method').val('edit');
                    $('#branchModal').modal('show');
                    $('#branch_id').val(data.id);
                    $('#branch_name').val(data.branch_name);
                    $('#city').val(data.city);
                    $('#payment_type').val(data.payment_type);
                    $('#due_date').val(data.due_date);
                    $('#day_before_due_date').val(data.day_before_due_date);
                    $('#address').val(data.address);
                    $('#description').val(data.description);
                    $('#formErrors').hide();
                    $('.invalid-feedback').hide();
                    $('.form-control').removeClass('is-invalid');
                },
                error: function(data) {
                    console.log('Error:', data);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch branch data'
                    });
                }
            });
        });

        // Save Branch Form
        $('#branchForm').submit(function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            $('#formErrors').hide();

            var formData = new FormData(this);
            var actionUrl = $('#saveBtn').val() == "create" ?
                "<?= site_url('settings/branch/store') ?>" :
                "<?= site_url('settings/branch/update') ?>";

            $.ajax({
                data: formData,
                url: actionUrl,
                type: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#branchForm').trigger("reset");
                        $('#branchModal').modal('hide');
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        if (response.errors) {
                            // Show field-specific errors
                            $.each(response.errors, function(key, value) {
                                var fieldName = key.replace(/_/g, '');
                                fieldName = fieldName.charAt(0).toUpperCase() + fieldName.slice(1);
                                $('#error' + fieldName + ' strong').text(value);
                                $('#error' + fieldName).show();
                                $('#' + key).addClass('is-invalid');
                            });
                        }

                        if (response.message) {
                            $('#formErrors').html(response.message).show();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while saving data'
                    });
                }
            });
        });

        // Delete Branch
        $('body').on('click', '.deleteBranch', function() {
            var branch_id = $(this).data("id");

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
                        type: "POST",
                        url: "<?= site_url('settings/branch/delete') ?>/" + branch_id,
                        data: {
                            <?= csrf_token() ?>: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while deleting data'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>