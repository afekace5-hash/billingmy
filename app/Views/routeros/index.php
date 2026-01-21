<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Router OS Configuration</h4>
                </div>
            </div>
        </div>

        <!-- RouterOS Account List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">RouterOS Account List</h5>
                            <button type="button" class="btn btn-success btn-sm" id="btnAdd">
                                <i class="bx bx-plus"></i> Add New
                            </button>
                        </div>

                        <table class="table table-bordered table-striped align-middle nowrap" id="routerTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                    <th class="text-center" style="width: 60px;">ID</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Host / IP</th>
                                    <th>Isolir Type</th>
                                    <th>Prefix Email</th>
                                    <th>Last Sync</th>
                                    <th>Created at</th>
                                    <th>Last updated</th>
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
<div class="modal fade" id="routerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white" id="modalTitle">Form RouterOS Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="routerForm">
                <div class="modal-body">
                    <input type="hidden" id="routerId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">RouterOS Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="ex: RouterOS Main">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Isolir Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="isolir_type" name="isolir_type" required>
                                    <option value="">Select Isolir</option>
                                    <option value="Isolir via PPPoe (Static)">Isolir via PPPoe (Static)</option>
                                    <option value="Isolir via PPPoe (Dynamic)">Isolir via PPPoe (Dynamic)</option>
                                    <option value="Isolir via Static IP">Isolir via Static IP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <select class="form-select" id="branch" name="branch" required>
                                    <option value="">Select Branch</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Host / IP Router <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="host" name="host" required placeholder="ex: 10.10.10.10">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="admin@kreativabill.id">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Local IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="local_ip" name="local_ip" required placeholder="ex: 10.10.10.1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Enable Legacy Login</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="legacy_login" name="legacy_login" value="1">
                                    <label class="form-check-label" for="legacy_login">
                                        Enable
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Remote URL <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="remote_url" name="remote_url" required placeholder="ex: https://10.10.10.1:4444">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Comment NAT <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="comment_nat" name="comment_nat" required placeholder="ex: REMOTEACCESSCUSTOMERS">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Prefix Email <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="prefix_email" name="prefix_email" required placeholder="ex: @hns.id">
                                <div class="alert alert-info mt-2 py-2 px-3" role="alert">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Prefix email digunakan untuk setiap pembuatan user ppoe. Contoh user@hns.id
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary px-4">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var table;

    $(document).ready(function() {
        // Initialize DataTable
        table = $('#routerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('router-os-conf/data') ?>",
                type: "POST",
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables Ajax error:', xhr.responseText);
                    Swal.fire('Error', 'Failed to load data: ' + thrown, 'error');
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-secondary btnSync" data-id="${row.id}" title="Sync">
                                <i class="bx bx-sync"></i>
                            </button>
                            <button class="btn btn-sm btn-info btnView" data-id="${row.id}" title="View">
                                <i class="bx bx-show"></i>
                            </button>
                            <button class="btn btn-sm btn-primary btnEdit" data-id="${row.id}" title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btnDelete" data-id="${row.id}" title="Delete">
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
                    data: 'name'
                },
                {
                    data: 'branch'
                },
                {
                    data: 'host'
                },
                {
                    data: 'isolir_type'
                },
                {
                    data: 'prefix_email',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'last_sync',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleString('id-ID') : '-';
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data) {
                        return data ? data : '-';
                    }
                }
            ],
            order: [
                [1, 'asc']
            ],
            pageLength: 10,
            responsive: false,
            scrollX: true
        });

        // Load branches
        loadBranches();

        // Add button
        $('#btnAdd').click(function() {
            $('#routerForm')[0].reset();
            $('#routerId').val('');
            $('#modalTitle').text('Add RouterOS Account');
            $('#routerModal').modal('show');
        });

        // View button - redirect to detail page
        $('body').on('click', '.btnView', function() {
            var id = $(this).data('id');
            window.location.href = "<?= base_url('router-os-conf/') ?>" + id;
        });

        // Sync button
        $('body').on('click', '.btnSync', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Syncing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "<?= base_url('router-os-conf/sync/') ?>" + id,
                type: "POST",
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        table.ajax.reload();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message || 'Sync failed', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Failed to sync', 'error');
                }
            });
        });

        // Edit button
        $('body').on('click', '.btnEdit', function() {
            var id = $(this).data('id');
            $.ajax({
                url: "<?= base_url('router-os-conf/') ?>" + id + "/edit",
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#routerId').val(data.id);
                        $('#name').val(data.name);
                        $('#branch').val(data.branch).trigger('change');
                        $('#host').val(data.host);
                        $('#username').val(data.username);
                        $('#password').val(data.password);
                        $('#port').val(data.port);
                        $('#isolir_type').val(data.isolir_type);
                        $('#prefix_email').val(data.prefix_email);
                        $('#modalTitle').text('Edit RouterOS Account');
                        $('#routerModal').modal('show');
                    }
                }
            });
        });

        // Delete button
        $('body').on('click', '.btnDelete', function() {
            var id = $(this).data('id');
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
                        url: "<?= base_url('router-os-conf/') ?>" + id,
                        type: "DELETE",
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Form submit
        $('#routerForm').submit(function(e) {
            e.preventDefault();
            var id = $('#routerId').val();
            var url = id ?
                "<?= base_url('router-os-conf/') ?>" + id :
                "<?= base_url('router-os-conf') ?>";

            $.ajax({
                url: url,
                type: "POST",
                data: $(this).serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#routerModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to save data', 'error');
                }
            });
        });
    });

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
                        $branch.append('<option value="' + branch.branch_name + '">' + branch.branch_name + '</option>');
                    });
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>