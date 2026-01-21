<?= $this->extend('layout/default'); ?>

<?= $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create New Ticket</h4>
                    <div class="page-title-right">
                        <a href="<?= base_url('ticket'); ?>" class="btn btn-secondary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                            <i class="bx bx-arrow-back" style="font-size:20px; padding-right:5px;"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Form Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (session()->getFlashdata('error')) : ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bx bx-error-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('success')) : ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bx bx-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('errors')) : ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bx bx-error-circle me-2"></i>
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('ticket/store'); ?>" method="POST" enctype="multipart/form-data">
                            <?= csrf_field(); ?>

                            <!-- Customer Information Section -->
                            <div class="mb-4">
                                <h5 class="card-title mb-3">Customer Information</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customer_id" class="form-label">Select Customer <span class="text-danger">*</span></label>
                                            <select class="form-select" id="customer_id" name="customer_id" onchange="loadCustomerData()" required>
                                                <option value="">Choose existing customer...</option>
                                                <!-- Customer options will be loaded here -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama_pelanggan" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control"
                                                id="nama_pelanggan"
                                                name="nama_pelanggan"
                                                value="<?= old('nama_pelanggan') ?>"
                                                placeholder="Enter customer name"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nomor_layanan" class="form-label">Service Number</label>
                                            <input type="text"
                                                class="form-control"
                                                id="nomor_layanan"
                                                name="nomor_layanan"
                                                value="<?= old('nomor_layanan') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="no_wa" class="form-label">WhatsApp Number</label>
                                            <input type="tel"
                                                class="form-control"
                                                id="no_wa"
                                                name="no_wa"
                                                value="<?= old('no_wa') ?>">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label">Address</label>
                                            <textarea class="form-control"
                                                id="alamat"
                                                name="alamat"
                                                rows="2"><?= old('alamat') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ticket Information Section -->
                            <div class="mb-4">
                                <h5 class="card-title mb-3">Ticket Information</h5>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control"
                                                id="subject"
                                                name="subject"
                                                value="<?= old('subject') ?>"
                                                placeholder="Brief description of the issue"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">-- Select Category --</option>
                                                <option value="teknis" <?= old('category') == 'teknis' ? 'selected' : '' ?>>Technical</option>
                                                <option value="billing" <?= old('category') == 'billing' ? 'selected' : '' ?>>Billing</option>
                                                <option value="layanan" <?= old('category') == 'layanan' ? 'selected' : '' ?>>Service</option>
                                                <option value="instalasi" <?= old('category') == 'instalasi' ? 'selected' : '' ?>>Installation</option>
                                                <option value="pemasangan" <?= old('category') == 'pemasangan' ? 'selected' : '' ?>>Installation (New)</option>
                                                <option value="gangguan" <?= old('category') == 'gangguan' ? 'selected' : '' ?>>Issue</option>
                                                <option value="lainnya" <?= old('category') == 'lainnya' ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                            <select class="form-select" id="priority" name="priority" required>
                                                <option value="">-- Select Priority --</option>
                                                <option value="low" <?= old('priority') == 'low' ? 'selected' : '' ?>>Low</option>
                                                <option value="medium" <?= old('priority') == 'medium' ? 'selected' : '' ?>>Medium</option>
                                                <option value="high" <?= old('priority') == 'high' ? 'selected' : '' ?>>High</option>
                                                <option value="urgent" <?= old('priority') == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="add_status" name="status">
                                                <option value="open" <?= old('status', 'open') == 'open' ? 'selected' : '' ?>>Open</option>
                                                <option value="in_progress" <?= old('status') == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="resolved" <?= old('status') == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                <option value="closed" <?= old('status') == 'closed' ? 'selected' : '' ?>>Closed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control"
                                                id="description"
                                                name="description"
                                                rows="4"
                                                placeholder="Please provide detailed information about your issue..."
                                                required><?= old('description') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Attachment Section -->
                            <div class="mb-4">
                                <h5 class="card-title mb-3">Attachment (Optional)</h5>
                                <div class="mb-3">
                                    <label for="attachment" class="form-label">Upload File</label>
                                    <input type="file"
                                        class="form-control"
                                        id="attachment"
                                        name="attachment"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <div class="form-text">Supported formats: JPG, PNG, PDF, DOC, DOCX (Max: 5MB)</div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="text-end">
                                <a href="<?= base_url('ticket'); ?>" class="btn btn-secondary me-2 custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="bx bx-x me-1" style="font-size:20px; padding-right:5px;"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="bx bx-check me-1" style="font-size:20px; padding-right:5px;"></i>Create Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function() {
            // Load customers on page load
            loadCustomers();

            // File upload validation
            $('#attachment').on('change', function() {
                var file = this.files[0];
                if (file && file.size > 5242880) { // 5MB
                    alert('File size must be less than 5MB');
                    $(this).val('');
                }
            });
        });

        // Load customers list
        function loadCustomers() {
            $.ajax({
                url: '<?= base_url('customer/getCustomerOptions') ?>',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var options = '<option value="">Choose existing customer...</option>';
                        response.data.forEach(function(customer) {
                            options += `<option value="${customer.id}" data-customer='${JSON.stringify(customer)}'>${customer.nama_pelanggan} - ${customer.nomor_layanan || 'No Service Number'}</option>`;
                        });
                        $('#customer_id').html(options);
                    }
                },
                error: function() {
                    console.log('Failed to load customers');
                }
            });
        }

        // Load customer data when selected
        function loadCustomerData() {
            var selectedOption = $('#customer_id option:selected');
            var customerData = selectedOption.data('customer');

            if (customerData) {
                $('#nama_pelanggan').val(customerData.nama_pelanggan || '');
                $('#nomor_layanan').val(customerData.nomor_layanan || '');
                $('#no_wa').val(customerData.no_wa || '');
                $('#alamat').val(customerData.alamat || '');
            } else {
                // Clear fields if no customer selected
                $('#nama_pelanggan').val('');
                $('#nomor_layanan').val('');
                $('#no_wa').val('');
                $('#alamat').val('');
            }
        }
    </script>
    <?= $this->endSection(); ?>

    <?= $this->endSection(); ?>