<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">RouterOS Configuration Detail</h4>
                    <div class="page-title-right">
                        <a href="<?= base_url('router-os-conf') ?>" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Card -->
        <div class="row">
            <div class="col-9">

                <div class="card content-center">
                    <div class="card-header">
                        <h5 class="card-title mb-0" style="color: success;">RouterOS Configuration Detail</h5>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="25%" class="bg-light">ID</th>
                                    <td><?= $data['id_lokasi'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Name</th>
                                    <td><?= $data['name'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Branch</th>
                                    <td><?= $data['lokasi'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Isolir Type</th>
                                    <td>
                                        <?= $data['jenis_isolir'] ?? '-' ?>
                                        <?php if (!empty($data['jenis_isolir'])): ?>
                                            <span class="badge bg-info ms-2"><?= $data['jenis_isolir'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Host</th>
                                    <td><?= $data['ip_router'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Username</th>
                                    <td><?= $data['username'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Password</th>
                                    <td>
                                        <?php if (!empty($data['password'])): ?>
                                            <span class="password-mask">••••••••</span>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="togglePassword()">
                                                <i class="bx bx-show" id="toggleIcon"></i>
                                            </button>
                                            <span class="password-text" style="display:none;"><?= $data['password'] ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Legacy Login</th>
                                    <td><?= ($data['legacy_login'] ?? 0) == 1 ? 'Yes' : 'No' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Remark</th>
                                    <td><?= $data['notes'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Status</th>
                                    <td>
                                        <?php if (($data['is_connected'] ?? 0) == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Error Logs</th>
                                    <td>
                                        <textarea class="form-control" rows="4" readonly><?= $data['error_logs'] ?? '' ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Last Sync</th>
                                    <td><?= $data['last_sync'] ?? 'Never' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Created by</th>
                                    <td><?= $data['created_by'] ?? 'Admin' ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Created at</th>
                                    <td><?= $data['created_at'] ?? '-' ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Action Buttons -->
                        <div class="mt-4">
                            <button class="btn btn-success" onclick="testConnection()">
                                <i class="bx bx-check-circle"></i> Test Connect
                            </button>
                            <button class="btn btn-warning" onclick="syncNow()">
                                <i class="bx bx-sync"></i> Sync Now
                            </button>
                            <a href="<?= base_url('router-os-conf/' . ($data['id_lokasi'] ?? 0) . '/edit') ?>" class="btn btn-info">
                                <i class="bx bx-edit"></i> Edit
                            </a>
                            <button class="btn btn-danger" onclick="deleteRouter(<?= $data['id_lokasi'] ?? 0 ?>)">
                                <i class="bx bx-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function togglePassword() {
        var mask = document.querySelector('.password-mask');
        var text = document.querySelector('.password-text');
        var icon = document.getElementById('toggleIcon');

        if (mask.style.display === 'none') {
            mask.style.display = 'inline';
            text.style.display = 'none';
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
        } else {
            mask.style.display = 'none';
            text.style.display = 'inline';
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
        }
    }

    function testConnection() {
        var id = <?= $data['id_lokasi'] ?? 0 ?>;
        Swal.fire({
            title: 'Testing Connection...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "<?= base_url('router-os-conf/check-connection') ?>",
            type: "POST",
            data: {
                id: id,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire('Success', response.message || 'Connection test successful!', 'success').then(() => {
                        location.reload(); // Reload page to show updated status
                    });
                } else {
                    Swal.fire('Error', response.message || 'Connection failed', 'error');
                }
            },
            error: function() {
                Swal.close();
                Swal.fire('Error', 'Failed to test connection', 'error');
            }
        });
    }

    function syncNow() {
        var id = <?= $data['id_lokasi'] ?? 0 ?>;
        Swal.fire({
            title: 'Syncing with MikroTik...',
            html: 'Comparing billing data with router<br>Please wait...',
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
                    let reportHtml = '<div class="text-left">';
                    if (response.report) {
                        reportHtml += '<p><strong>Sync Summary:</strong></p>';
                        reportHtml += '<ul>';
                        reportHtml += '<li>Accounts Added: ' + (response.report.added || 0) + '</li>';
                        reportHtml += '<li>Accounts Updated: ' + (response.report.updated || 0) + '</li>';
                        reportHtml += '<li>Customers Isolated: ' + (response.report.isolated || 0) + '</li>';
                        reportHtml += '<li>Customers Restored: ' + (response.report.restored || 0) + '</li>';
                        if (response.report.errors && response.report.errors.length > 0) {
                            reportHtml += '<li style="color: #d33;">Errors: ' + response.report.errors.length + '</li>';
                        }
                        reportHtml += '</ul>';
                    }
                    reportHtml += '</div>';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Sync Completed',
                        html: reportHtml + '<hr>' + response.message,
                        width: 600
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Sync failed', 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = 'Failed to sync with MikroTik';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }

    function deleteRouter(id) {
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
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                window.location.href = "<?= base_url('router-os-conf') ?>";
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>