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
        font-weight: 500;
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
                    <h4 class="mb-sm-0">Manage Users</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary btn-sm me-2" id="btnNewUser">
                            <i class="bx bx-plus"></i> New User
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" id="btnFilter">
                            <i class="bx bx-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped align-middle nowrap" id="usersTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Action</th>
                                    <th class="text-center">ID</th>
                                    <th>Code</th>
                                    <th>Role</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Branches</th>
                                    <th>Join at</th>
                                    <th>Last update</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="Beach Admin">Beach Admin</option>
                                    <option value="Superadmin">Superadmin</option>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Investor">Investor</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Technician">Technician</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branches" class="form-label">Branches</label>
                                <input type="text" class="form-control" id="branches" name="branches" placeholder="Jakarta Timur [Duren]">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="text-muted" id="passwordHint">Min. 6 characters</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
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
        var table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('users/data') ?>",
                type: "POST",
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    width: '100px',
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary" onclick="editUser(${row.id})">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${row.id})">
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
                    data: 'code'
                },
                {
                    data: 'role',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data == 'Beach Admin') badgeClass = 'bg-info';
                        else if (data == 'Superadmin') badgeClass = 'bg-warning text-dark';
                        else if (data == 'Administrator') badgeClass = 'bg-primary';
                        else if (data == 'Investor') badgeClass = 'bg-success';
                        else if (data == 'Sales') badgeClass = 'bg-danger';
                        else if (data == 'Technician') badgeClass = 'bg-dark';

                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                    }
                },
                {
                    data: 'name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'phone'
                },
                {
                    data: 'branches',
                    render: function(data) {
                        return data ? '<span class="badge bg-info">' + data + '</span>' : '-';
                    }
                },
                {
                    data: 'join_at'
                },
                {
                    data: 'last_update'
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

        // New User Button
        $('#btnNewUser').click(function() {
            $('#userForm')[0].reset();
            $('#userId').val('');
            $('#modalTitle').text('New User');
            $('#password').prop('required', true);
            $('#passwordRequired').show();
            $('#passwordHint').text('Min. 6 characters');
            $('#userModal').modal('show');
        });

        // Submit Form
        $('#userForm').submit(function(e) {
            e.preventDefault();

            var userId = $('#userId').val();
            var url = userId ? "<?= base_url('users/update/') ?>" + userId : "<?= base_url('users/store') ?>";

            var formData = {
                name: $('#name').val(),
                email: $('#email').val(),
                code: $('#code').val(),
                role: $('#role').val(),
                phone: $('#phone').val(),
                branches: $('#branches').val(),
                password: $('#password').val(),
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            };

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#userModal').modal('hide');
                        table.ajax.reload();
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
                    Swal.fire('Error', 'Failed to save user', 'error');
                }
            });
        });

        // Filter Button
        $('#btnFilter').click(function() {
            Swal.fire('Info', 'Filter feature coming soon', 'info');
        });
    });

    function editUser(id) {
        $.ajax({
            url: "<?= base_url('users/edit/') ?>" + id,
            type: "GET",
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#userId').val(response.data.id);
                    $('#name').val(response.data.name);
                    $('#email').val(response.data.email);
                    $('#code').val(response.data.code);
                    $('#role').val(response.data.role);
                    $('#phone').val(response.data.phone);
                    $('#branches').val(response.data.branches);
                    $('#password').val('').prop('required', false);
                    $('#passwordRequired').hide();
                    $('#passwordHint').text('Leave blank to keep current password');
                    $('#modalTitle').text('Edit User');
                    $('#userModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    function deleteUser(id) {
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
                    url: "<?= base_url('users/delete/') ?>" + id,
                    type: "POST",
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
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
    }
</script>
<?= $this->endSection(); ?>