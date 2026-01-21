<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title><?= $title ?> &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><?= $title ?></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Router</a></li>
                            <li class="breadcrumb-item active">PPPoE</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?= base_url('pppoe-accounts/create') ?>" class="btn btn-success me-2">
                                    <i class="bx bx-plus me-1"></i>Create New
                                </a>
                                <button type="button" class="btn btn-secondary me-2" id="filterBtn">
                                    <i class="bx bx-filter me-1"></i>Filter
                                </button>
                                <button type="button" class="btn btn-primary me-2" id="syncBtn">
                                    <i class="bx bx-refresh me-1"></i>Sync
                                </button>
                                <a href="<?= base_url('pppoe-accounts/export') ?>" class="btn btn-info">
                                    <i class="bx bx-download me-1"></i>Export
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card (Hidden by default) -->
        <div class="row" id="filterCard" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Branch/Server</label>
                                <select class="form-select" id="filterServer">
                                    <option value="0">All Servers</option>
                                    <?php foreach ($servers as $server): ?>
                                        <option value="<?= $server['id_lokasi'] ?>"><?= $server['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Disabled Status</label>
                                <select class="form-select" id="filterDisabled">
                                    <option value="">All Status</option>
                                    <option value="0">Active</option>
                                    <option value="1">Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" id="applyFilter">
                                    <i class="bx bx-check me-1"></i>Apply Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PPOE Account List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">PPOE Account List</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dt-responsive nowrap w-100" id="pppoeTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action</th>
                                        <th>ID</th>
                                        <th>PPPoe ID</th>
                                        <th>Branch</th>
                                        <th>Customer</th>
                                        <th>Disabled</th>
                                        <th>Username</th>
                                        <th>Remote Address</th>
                                        <th>MAC Address</th>
                                        <th>Local Address</th>
                                        <th>Last Sync</th>
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
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PPPoE Account Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function() {
        // Initialize DataTable
        var table = $('#pppoeTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('pppoe-accounts/get-data') ?>',
                type: 'POST',
                data: function(d) {
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                    d.filterServer = $('#filterServer').val();
                    d.filterDisabled = $('#filterDisabled').val();
                }
            },
            columns: [{
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'id'
                },
                {
                    data: 'pppoe_id'
                },
                {
                    data: 'branch'
                },
                {
                    data: 'customer'
                },
                {
                    data: 'disabled'
                },
                {
                    data: 'username'
                },
                {
                    data: 'remote_address'
                },
                {
                    data: 'mac_address'
                },
                {
                    data: 'local_address'
                },
                {
                    data: 'last_sync'
                },
                {
                    data: 'created_at'
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 10,
            responsive: true
        });

        // Filter Button
        $('#filterBtn').on('click', function() {
            $('#filterCard').slideToggle();
        });

        // Apply Filter
        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        // Sync Button
        $('#syncBtn').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i>Syncing...');

            $.ajax({
                url: '<?= base_url('pppoe-accounts/sync') ?>',
                type: 'POST',
                data: {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000
                        });
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to sync PPPoE accounts'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-refresh me-1"></i>Sync');
                }
            });
        });

        // View PPPoE
        $('body').on('click', '.viewPppoe', function() {
            var id = $(this).data('id');
            // Load details via AJAX
            $('#viewModal').modal('show');
        });

        // Edit PPPoE
        $('body').on('click', '.editPppoe', function() {
            var id = $(this).data('id');
            Swal.fire('Info', 'Edit functionality coming soon!', 'info');
        });

        // Delete PPPoE
        $('body').on('click', '.deletePppoe', function() {
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
                        url: '<?= base_url('pppoe-accounts/delete') ?>/' + id,
                        type: 'POST',
                        data: {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to delete PPPoE account', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>