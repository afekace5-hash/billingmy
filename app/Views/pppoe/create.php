<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Create PPPoE Account &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Create PPPoE Account</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('pppoe-accounts') ?>">PPPoE</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">Form Create PPOE</h5>
                    </div>
                    <div class="card-body">
                        <form id="pppoeForm" method="POST" action="<?= base_url('pppoe-accounts/store') ?>">
                            <?= csrf_field() ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="server_id" class="form-label">RouterOS <span class="text-danger">*</span></label>
                                        <select class="form-select" id="server_id" name="server_id" required>
                                            <option value="">Select RouterOS</option>
                                            <?php foreach ($servers as $server): ?>
                                                <option value="<?= $server['id_lokasi'] ?>"><?= $server['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="local_address" class="form-label">Local IP Address (optional)</label>
                                        <input type="text" class="form-control" id="local_address" name="local_address" placeholder="ex: 10.10.10.2">
                                    </div>

                                    <div class="mb-3">
                                        <label for="comment" class="form-label">Comment (optional)</label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Comment"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Customer Package <span class="text-danger">*</span></label>
                                        <select class="form-select" id="customer_id" name="customer_id" required>
                                            <option value="">Select Customer Package</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?= $customer['id_customers'] ?>" data-username="<?= $customer['pppoe_username'] ?>">
                                                    <?= $customer['nama_pelanggan'] ?> - <?= $customer['nomor_layanan'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="admin@kreativalbi.id" required>
                                        <small class="text-muted">Otomatis terisi jika customer sudah memiliki PPPoE username</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="remote_address" class="form-label">Remote IP Address (optional)</label>
                                        <input type="text" class="form-control" id="remote_address" name="remote_address" placeholder="ex: 10.10.10.1">
                                    </div>

                                    <div class="mb-3">
                                        <label for="profile_name" class="form-label">Profile (optional)</label>
                                        <input type="text" class="form-control" id="profile_name" name="profile_name" placeholder="ex: 10mbps">
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes (optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= base_url('pppoe-accounts') ?>" class="btn btn-secondary">
                                            <i class="bx bx-x me-1"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="bx bx-save me-1"></i>Create
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function() {
        // When customer is selected, auto-fill username if exists
        $('#customer_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var username = selectedOption.data('username');
            if (username && username.trim() !== '') {
                $('#username').val(username).prop('readonly', true);
            } else {
                $('#username').val('').prop('readonly', false).focus();
            }
        });

        // Submit Form
        $('#pppoeForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var submitBtn = $('#submitBtn');
            var originalHtml = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i>Processing...');

            $.ajax({
                url: '<?= base_url('pppoe-accounts/store') ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = '<?= base_url('pppoe-accounts') ?>';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                        submitBtn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Failed to create PPPoE account';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg
                    });
                    submitBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>