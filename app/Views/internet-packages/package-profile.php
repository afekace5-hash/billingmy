<?= $this->extend('layout/default') ?>

<?= $this->section('css') ?>
<style>
    .fs-24 {
        font-size: 24px !important;
    }

    .card-header .card-title {
        margin-bottom: 0;
    }

    .package-stats .card {
        transition: transform 0.2s ease-in-out;
    }

    .package-stats .card:hover {
        transform: translateY(-2px);
    }

    .table th {
        font-weight: 600;
        color: #495057;
        border-top: none;
    }

    .btn-group .btn {
        border-radius: 4px !important;
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .modal-header.bg-primary {
        border-bottom: none;
    }

    .modal-header.bg-warning {
        border-bottom: none;
    }

    .modal-header.bg-info {
        border-bottom: none;
    }

    .form-text {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    .table-nowrap td {
        white-space: nowrap;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .preview-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .burst-calculation {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .bandwidth-display .alert {
        border-left: 4px solid #0dcaf0;
    }

    .bandwidth-display .bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef !important;
    }

    .burst-formula {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    /* Fix for select dropdown display */
    .form-select {
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #212529;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .form-select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Ensure select text is always visible */
    .form-select:not([size]):not([multiple]) {
        height: calc(1.5em + 0.75rem + 2px);
    }

    /* Fix for form-select inside input-group */
    .input-group .form-select {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
        margin-left: -1px;
    }

    .input-group .input-group-text+.form-select {
        border-left: 0;
    }

    /* Ensure input-group has proper alignment */
    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
    }

    .input-group>.form-select {
        position: relative;
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
    }

    /* Additional fixes for select in input-group */
    .input-group .form-select:not([multiple]):not([size]) {
        height: calc(1.5em + 0.75rem + 2px);
    }

    /* Ensure seamless border connection */
    .input-group .input-group-text {
        border-right: 0;
    }

    .input-group .form-select {
        border-left: 0;
    }

    /* Fix for focus state */
    .input-group .form-select:focus {
        z-index: 3;
        border-color: #86b7fe;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075), 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .input-group .form-select {
        background-position: right 0.75rem center;
        padding-right: 2.25rem;
    }

    /* Fix any z-index issues */
    .input-group .form-select:focus {
        z-index: 3;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Ensure input-group-text has proper styling */
    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        text-align: center;
        white-space: nowrap;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .input-group .input-group-text:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .max-height-200 {
        max-height: 200px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><?= $page_title ?></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <?php foreach ($breadcrumb as $label => $url): ?>
                                <?php if ($url): ?>
                                    <li class="breadcrumb-item"><a href="<?= site_url($url) ?>"><?= $label ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active"><?= $label ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                <i class="bx bx-package text-primary me-2"></i>
                                Package Profile Management
                            </h4>
                            <p class="text-muted mb-0 mt-1">Manage internet package profiles with bandwidth and pricing</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                                <i class="bx bx-refresh"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageProfileModal">
                                <i class="bx bx-plus"></i> Add Package Profile
                            </button>
                        </div>
                    </div>
                    <div class="card-body"> <!-- Statistics Cards -->
                        <div class="row mb-4 package-stats">
                            <div class="col-md-3">
                                <div class="card bg-soft-primary border-0 h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-package text-primary fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0" id="totalPackages">-</h5>
                                                <p class="text-muted mb-0">Total Packages</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-soft-success border-0 h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-check-circle text-success fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0" id="activePackages">-</h5>
                                                <p class="text-muted mb-0">Active Packages</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-soft-warning border-0 h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-pause-circle text-warning fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0" id="inactivePackages">-</h5>
                                                <p class="text-muted mb-0">Inactive Packages</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-soft-info border-0 h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-money text-info fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0" id="avgPrice">-</h5>
                                                <p class="text-muted mb-0">Average Price</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="packageProfileTable" class="table table-hover align-middle table-nowrap dt-responsive" style="width:100%">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Package Profile Modal -->
<div class="modal fade" id="addPackageProfileModal" tabindex="-1" aria-labelledby="addPackageProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addPackageProfileModalLabel">
                    <i class="bx bx-plus-circle me-2"></i>Add New Package Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPackageProfileForm">
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="bx bx-info-circle me-1"></i>Basic Information
                            </h6>

                            <div class="mb-3">
                                <label for="name" class="form-label">Package Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-package"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter package name" required>
                                </div>
                                <div class="form-text">Unique name for the internet package</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter package description"></textarea>
                                <div class="form-text">Optional description for this package</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="price" name="price" min="0" step="1000" placeholder="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="validity_period" class="form-label">Validity Period (days) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="validity_period" name="validity_period" value="30" min="1" required>
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="grace_period" class="form-label">Grace Period (days)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="grace_period" name="grace_period" value="3" min="0">
                                            <span class="input-group-text">days</span>
                                        </div>
                                        <div class="form-text">Grace period after expiry</div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="add_status" class="form-label">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                                            <select class="form-select" id="add_status" name="status">
                                                <option value="active" selected>Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="form-text">Package availability status</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration -->
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="bx bx-cog me-1"></i>Configuration
                            </h6>
                            <div class="mb-3">
                                <label for="bandwidth_profile_id" class="form-label">Bandwidth Profile</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-tachometer"></i></span>
                                    <select class="form-select" id="bandwidth_profile_id" name="bandwidth_profile_id">
                                        <option value="">Select Bandwidth Profile</option>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                </div>
                                <div class="form-text">Choose bandwidth limits for this package</div>

                                <!-- Bandwidth Display -->
                                <div id="bandwidth_display" class="mt-2 bandwidth-display" style="display: none;">
                                    <div class="alert alert-info mb-0 p-3">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <label class="form-label mb-1"><strong>Max Limit (MIR)</strong></label>
                                                <div class="form-control-plaintext bg-light border rounded p-2" id="max_limit_display">-</div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label mb-1"><strong>Limit at (CIR)</strong></label>
                                                <div class="form-control-plaintext bg-light border rounded p-2" id="limit_at_display">-</div>
                                            </div>
                                        </div>

                                        <!-- Optional Burst Calculation Section -->
                                        <div class="mt-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="show_burst_calc" checked>
                                                <label class="form-check-label" for="show_burst_calc">
                                                    <strong>Show Burst Calculations</strong>
                                                    <small class="text-muted d-block">Optional MikroTik burst parameters</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div id="burst_calc_section" class="mt-3">
                                            <label class="form-label mb-1">
                                                <strong>Burst - Threshold - Time - Priority</strong>
                                                <span class="text-muted">(Editable)</span>
                                            </label>

                                            <!-- Editable Burst Parameters -->
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Burst Limit</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" id="burst_limit_down" name="burst_limit_down" placeholder="8M" value="">
                                                        <span class="input-group-text">/</span>
                                                        <input type="text" class="form-control" id="burst_limit_up" name="burst_limit_up" placeholder="8M" value="">
                                                    </div>
                                                    <small class="text-muted">Down/Up</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Burst Threshold</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" id="burst_threshold_down" name="burst_threshold_down" placeholder="4M" value="">
                                                        <span class="input-group-text">/</span>
                                                        <input type="text" class="form-control" id="burst_threshold_up" name="burst_threshold_up" placeholder="4M" value="">
                                                    </div>
                                                    <small class="text-muted">Down/Up</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Burst Time</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" id="burst_time_down" name="burst_time_down" placeholder="8" value="8" min="1">
                                                        <span class="input-group-text">/</span>
                                                        <input type="number" class="form-control" id="burst_time_up" name="burst_time_up" placeholder="8" value="8" min="1">
                                                    </div>
                                                    <small class="text-muted">Down/Up (sec)</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Priority</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" id="burst_priority_down" name="burst_priority_down" placeholder="8" value="8" min="1" max="8">
                                                        <span class="input-group-text">/</span>
                                                        <input type="number" class="form-control" id="burst_priority_up" name="burst_priority_up" placeholder="8" value="8" min="1" max="8">
                                                    </div>
                                                    <small class="text-muted">Down/Up (1-8)</small>
                                                </div>
                                            </div>

                                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="auto_calculate_burst">
                                                    <i class="bx bx-calculator"></i> Auto Calculate
                                                </button>
                                                <small class="text-success" id="burst_calculation">
                                                    Manual burst parameters - click Auto Calculate for defaults
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="group_profile_id" class="form-label">Group Profile (PPPoE Profile)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-group"></i></span>
                                    <select class="form-select" id="group_profile_id" name="group_profile_id">
                                        <option value="">Select Group Profile</option>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                </div>
                                <div class="form-text">Choose PPPoE profile configuration for new customers</div>
                            </div>
                            <div class="mb-3">
                                <label for="default_profile_mikrotik" class="form-label">Default Profile MikroTik</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-server"></i></span>
                                    <select class="form-select" id="default_profile_mikrotik" name="default_profile_mikrotik">
                                        <option value="">Select Default Profile</option>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                </div>
                                <div class="form-text">Default profile from MikroTik for this package</div>
                            </div>
                            <div class="mb-3">
                                <label for="auto_renewal" class="form-label">Auto Renewal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-refresh"></i></span>
                                    <select class="form-select" id="auto_renewal" name="auto_renewal">
                                        <option value="0" selected>Disabled</option>
                                        <option value="1">Enabled</option>
                                    </select>
                                </div>
                                <div class="form-text">Automatic subscription renewal</div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="bx bx-show me-1"></i>Package Preview
                            </h6>
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Package:</strong> <span id="preview-name">-</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Price:</strong> <span id="preview-price">-</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Validity:</strong> <span id="preview-validity">-</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Status:</strong> <span id="preview-status">-</span>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <strong>Bandwidth Profile:</strong> <span id="preview-bandwidth">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>PPPoE Profile:</strong> <span id="preview-group-profile">-</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Default Profile MikroTik:</strong> <span id="preview-default-profile">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bx bx-save"></i> Save Package Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Package Profile Modal -->
<div class="modal fade" id="editPackageProfileModal" tabindex="-1" aria-labelledby="editPackageProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPackageProfileModalLabel">
                    <i class="bx bx-edit me-2"></i>Edit Package Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPackageProfileForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-warning border-bottom pb-2 mb-3">
                                <i class="bx bx-info-circle me-1"></i>Basic Information
                            </h6>

                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Package Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-package"></i></span>
                                    <input type="text" class="form-control" id="edit_name" name="name" placeholder="Enter package name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Enter package description"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_price" class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="edit_price" name="price" min="0" step="1000" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_validity_period" class="form-label">Validity Period (days) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="edit_validity_period" name="validity_period" min="1" required>
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_grace_period" class="form-label">Grace Period (days)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="edit_grace_period" name="grace_period" min="0">
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_status" class="form-label">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                                            <select class="form-select" id="edit_status" name="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="form-text">Package availability status</div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Configuration -->
                        <div class="col-md-6">
                            <h6 class="text-warning border-bottom pb-2 mb-3">
                                <i class="bx bx-cog me-1"></i>Configuration
                            </h6>

                            <div class="mb-3">
                                <label for="edit_bandwidth_profile_id" class="form-label">Bandwidth Profile</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-tachometer"></i></span>
                                    <select class="form-select" id="edit_bandwidth_profile_id" name="bandwidth_profile_id">
                                        <option value="">Select Bandwidth Profile</option>
                                    </select>
                                </div>
                                <div class="form-text">Choose bandwidth limits for this package</div>

                                <!-- Bandwidth Display for Edit -->
                                <div id="edit_bandwidth_display" class="mt-2 bandwidth-display" style="display: none;">
                                    <div class="alert alert-warning mb-0 p-3">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <label class="form-label mb-1"><strong>Max Limit (MIR)</strong></label>
                                                <div class="form-control-plaintext bg-light border rounded p-2" id="edit_max_limit_display">-</div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label mb-1"><strong>Limit at (CIR)</strong></label>
                                                <div class="form-control-plaintext bg-light border rounded p-2" id="edit_limit_at_display">-</div>
                                            </div>
                                        </div>

                                        <!-- Optional Burst Calculation Section -->
                                        <div class="mt-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_show_burst_calc" checked>
                                                <label class="form-check-label" for="edit_show_burst_calc">
                                                    <strong>Show Burst Calculations</strong>
                                                    <small class="text-muted d-block">Optional MikroTik burst parameters</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div id="edit_burst_calc_section" class="mt-3">
                                            <label class="form-label mb-1">
                                                <strong>Burst - Threshold - Time - Priority</strong>
                                                <span class="text-muted">(Editable)</span>
                                            </label>

                                            <!-- Editable Burst Parameters -->
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Burst Limit</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" id="edit_burst_limit_down" name="burst_limit_down" placeholder="8M" value="">
                                                        <span class="input-group-text">/</span>
                                                        <input type="text" class="form-control" id="edit_burst_limit_up" name="burst_limit_up" placeholder="8M" value="">
                                                    </div>
                                                    <small class="text-muted">Down/Up</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Burst Threshold</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" id="edit_burst_threshold_down" name="burst_threshold_down" placeholder="4M" value="">
                                                        <span class="input-group-text">/</span>
                                                        <input type="text" class="form-control" id="edit_burst_threshold_up" name="burst_threshold_up" placeholder="4M" value="">
                                                    </div>
                                                    <small class="text-muted">Down/Up</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Burst Time</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" id="edit_burst_time_down" name="burst_time_down" placeholder="8" value="8" min="1">
                                                        <span class="input-group-text">/</span>
                                                        <input type="number" class="form-control" id="edit_burst_time_up" name="burst_time_up" placeholder="8" value="8" min="1">
                                                    </div>
                                                    <small class="text-muted">Down/Up (sec)</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-1"><strong>Priority</strong></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" id="edit_burst_priority_down" name="burst_priority_down" placeholder="8" value="8" min="1" max="8">
                                                        <span class="input-group-text">/</span>
                                                        <input type="number" class="form-control" id="edit_burst_priority_up" name="burst_priority_up" placeholder="8" value="8" min="1" max="8">
                                                    </div>
                                                    <small class="text-muted">Down/Up (1-8)</small>
                                                </div>
                                            </div>

                                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                                <button type="button" class="btn btn-sm btn-outline-warning" id="edit_auto_calculate_burst">
                                                    <i class="bx bx-calculator"></i> Auto Calculate
                                                </button>
                                                <small class="text-success" id="edit_burst_calculation">
                                                    Manual burst parameters - click Auto Calculate for defaults
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_group_profile_id" class="form-label">Group Profile</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-group"></i></span>
                                    <select class="form-select" id="edit_group_profile_id" name="group_profile_id">
                                        <option value="">Select Group Profile</option>
                                    </select>
                                </div>
                                <div class="form-text">Choose network group configuration</div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_default_profile_mikrotik" class="form-label">Default Profile MikroTik</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-server"></i></span>
                                    <select class="form-select" id="edit_default_profile_mikrotik" name="default_profile_mikrotik">
                                        <option value="">Select Default Profile</option>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                </div>
                                <div class="form-text">Default profile from MikroTik for this package</div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_auto_renewal" class="form-label">Auto Renewal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-refresh"></i></span>
                                    <select class="form-select" id="edit_auto_renewal" name="auto_renewal">
                                        <option value="0">Disabled</option>
                                        <option value="1">Enabled</option>
                                    </select>
                                </div>
                                <div class="form-text">Automatic subscription renewal</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="bx bx-save"></i> Update Package Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Package Profile Modal -->
<div class="modal fade" id="viewPackageProfileModal" tabindex="-1" aria-labelledby="viewPackageProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewPackageProfileModalLabel">
                    <i class="bx bx-show me-2"></i>Package Profile Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-info border-bottom pb-2 mb-3">Basic Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Package Name:</strong></td>
                                <td id="view_name">-</td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td id="view_description">-</td>
                            </tr>
                            <tr>
                                <td><strong>Price:</strong></td>
                                <td id="view_price" class="text-success fw-bold">-</td>
                            </tr>
                            <tr>
                                <td><strong>Validity Period:</strong></td>
                                <td id="view_validity_period">-</td>
                            </tr>
                            <tr>
                                <td><strong>Grace Period:</strong></td>
                                <td id="view_grace_period">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-info border-bottom pb-2 mb-3">Configuration</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Bandwidth Profile:</strong></td>
                                <td id="view_bandwidth_profile">-</td>
                            </tr>
                            <tr>
                                <td><strong>Group Profile:</strong></td>
                                <td id="view_group_profile">-</td>
                            </tr>
                            <tr>
                                <td><strong>Auto Renewal:</strong></td>
                                <td id="view_auto_renewal">-</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td id="view_status">-</td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td id="view_created_at">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var packageProfileTable; // Global DataTable instance

    $(document).ready(function() {
        // Load initial data when page loads
        loadProfiles(); // Load bandwidth and group profiles
        loadPPPoEProfilesOnLoad(); // Auto load PPPoE profiles from MikroTik

        // Initialize DataTable with destroy option to allow reinitialization
        packageProfileTable = $('#packageProfileTable').DataTable({
            destroy: true, // Allow reinitialization
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url('api/package-profiles/datatable') ?>',
                type: 'POST',
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable error:', error);
                    Swal.fire('Error', 'Failed to load package profiles data', 'error');
                }
            },
            columns: [{
                    title: '#',
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle',
                    defaultContent: '',
                },
                {
                    title: 'Package Name',
                    data: 'name',
                    className: 'align-middle',
                    render: function(data, type, row) {
                        var html = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' + data + '</a></h5>';
                        if (row.description) {
                            html += '<p class="text-muted mb-0">' + row.description + '</p>';
                        }
                        return html;
                    }
                },
                {
                    title: 'Price',
                    data: 'price',
                    className: 'align-middle',
                    render: function(data) {
                        return data ? '<span class="fw-medium">Rp ' + new Intl.NumberFormat('id-ID').format(data) + '</span>' : '-';
                    }
                },
                {
                    title: 'Profile',
                    data: null,
                    className: 'align-middle',
                    render: function(data) {
                        var html = '';
                        if (data.bandwidth_profile) {
                            html += '<span class="badge bg-info mb-1"><i class="bx bx-wifi me-1"></i>' + data.bandwidth_profile + '</span><br>';
                        }
                        if (data.group_profile) {
                            html += '<span class="badge bg-success"><i class="bx bx-group me-1"></i>' + data.group_profile + '</span>';
                        }
                        return html || '-';
                    }
                },
                {
                    title: 'Default Profile',
                    data: 'default_profile_mikrotik',
                    className: 'align-middle',
                    render: function(data) {
                        if (!data) return '-';
                        // Remove anything after ' (' or ' [' including the space
                        var nameOnly = data.replace(/\s*[\[(].*$/, '');
                        return '<span class="badge bg-secondary">' + nameOnly + '</span>';
                    }
                },
                {
                    title: 'Validity',
                    data: 'validity_period',
                    className: 'align-middle',
                    render: function(data, type, row) {
                        var html = '<span class="text-primary">' + data + ' days</span>';
                        if (row.grace_period > 0) {
                            html += '<br><small class="text-muted">Grace: ' + row.grace_period + ' days</small>';
                        }
                        return html;
                    }
                },
                {
                    title: 'Status',
                    data: 'status',
                    className: 'align-middle text-center',
                    render: function(data, type, row) {
                        var renewalBadge = row.auto_renewal == 1 ?
                            '<span class="badge bg-info" data-bs-toggle="tooltip" title="Auto Renewal"><i class="bx bx-refresh"></i></span> ' : '';
                        return renewalBadge + (data === 'active' ?
                            '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-danger">Inactive</span>');
                    }
                },
                {
                    title: 'Actions',
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info view-btn" data-id="${row.id}" title="View Details">
                                    <i class="bx bx-show"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${row.id}" title="Edit Package">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete Package">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            order: [
                [1, 'asc']
            ],
            pageLength: 25,
            responsive: true,
            language: {
                search: "Search packages:",
                lengthMenu: "Show _MENU_ packages per page",
                info: "Showing _START_ to _END_ of _TOTAL_ packages",
                infoEmpty: "No packages found",
                zeroRecords: "No matching packages found",
                emptyTable: "No packages available"
            },
            rowCallback: function(row, data, displayIndex, displayIndexFull) {
                var api = this.api();
                var pageInfo = api.page.info();
                // Nomor urut global (bukan hanya per halaman)
                var index = pageInfo.start + displayIndex + 1;
                $('td:eq(0)', row).html(index);
            }
        });

        // Update statistics function
        function updateStatistics(data) {
            const total = data.length;
            const active = data.filter(item => item.status === 'active').length;
            const inactive = total - active;

            // Calculate average price
            const prices = data.filter(item => item.price && !isNaN(item.price))
                .map(item => parseFloat(item.price));
            const avgPrice = prices.length > 0 ?
                prices.reduce((a, b) => a + b, 0) / prices.length : 0;

            // Update UI
            $('#totalPackages').text(total);
            $('#activePackages').text(active);
            $('#inactivePackages').text(inactive);
            $('#avgPrice').text(avgPrice > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(avgPrice)) : '-');
        }

        // Update statistics after DataTable loads data
        packageProfileTable.on('xhr', function(e, settings, json) {
            if (json && json.data) {
                updateStatistics(json.data);
            }
        });

        // Store bandwidth profiles for display
        var bandwidthProfiles = {};

        // Load Bandwidth and Group Profiles for dropdowns
        function loadProfiles() {
            // Load Bandwidth Profiles
            $.get('<?= site_url('internet-packages/bandwidth/data') ?>', function(data) {
                var bandwidthSelect = $('#bandwidth_profile_id, #edit_bandwidth_profile_id');
                bandwidthSelect.empty().append('<option value="">Select Bandwidth Profile</option>');
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(profile) {
                        // Store profile data for bandwidth display
                        bandwidthProfiles[profile.id] = profile;

                        const optionText = profile.name + ' (' + profile.download_min + '-' + profile.download_max + '/' + profile.upload_min + '-' + profile.upload_max + ' Kbps)';
                        bandwidthSelect.append('<option value="' + profile.id + '">' + optionText + '</option>');
                    });
                }
            }).fail(function() {
                console.warn('Could not load bandwidth profiles');
            });

            // Load Group Profiles
            $.get('<?= site_url('internet-packages/group-profile/data') ?>', function(data) {
                var groupSelect = $('#group_profile_id, #edit_group_profile_id');
                groupSelect.empty().append('<option value="">Select Group Profile</option>');
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(profile) {
                        groupSelect.append('<option value="' + profile.id + '">' + profile.name + '</option>');
                    });
                }
            }).fail(function() {
                console.warn('Could not load group profiles');
            });
        }

        // Function to format speed
        function formatSpeed(speedKbps) {
            if (speedKbps >= 1024) {
                return Math.round(speedKbps / 1024) + 'M';
            }
            return speedKbps + 'K';
        } // Function to display bandwidth information and auto-fill burst calculations
        function displayBandwidthInfo(profileId, prefix = '') {
            const displayElement = $('#' + prefix + 'bandwidth_display');

            if (profileId && bandwidthProfiles[profileId]) {
                const profile = bandwidthProfiles[profileId];

                // Format speeds
                const downloadMax = formatSpeed(profile.download_max);
                const uploadMax = formatSpeed(profile.upload_max);
                const downloadMin = formatSpeed(profile.download_min);
                const uploadMin = formatSpeed(profile.upload_min);

                // Update display with actual values
                $('#' + prefix + 'max_limit_display').text(downloadMax + '/' + uploadMax);
                $('#' + prefix + 'limit_at_display').text(downloadMin + '/' + uploadMin);

                // Auto-calculate and fill burst parameters
                autoCalculateBurst(profileId, prefix);

                // Show the display with slide animation
                displayElement.slideDown();
            } else {
                // Hide the display when no profile is selected
                displayElement.slideUp();
            }
        }

        // Function to auto-calculate burst parameters
        function autoCalculateBurst(profileId, prefix = '') {
            if (profileId && bandwidthProfiles[profileId]) {
                const profile = bandwidthProfiles[profileId];

                // Calculate burst parameters according to MikroTik standards
                const downloadBurstLimit = Math.round(profile.download_max * 1.5);
                const uploadBurstLimit = Math.round(profile.upload_max * 1.5);
                const downloadBurstThreshold = Math.round(profile.download_max * 0.75);
                const uploadBurstThreshold = Math.round(profile.upload_max * 0.75);

                // Format burst values
                const downloadBurstLimitFormatted = formatSpeed(downloadBurstLimit);
                const uploadBurstLimitFormatted = formatSpeed(uploadBurstLimit);
                const downloadBurstThresholdFormatted = formatSpeed(downloadBurstThreshold);
                const uploadBurstThresholdFormatted = formatSpeed(uploadBurstThreshold);

                // Fill the input fields
                $('#' + prefix + 'burst_limit_down').val(downloadBurstLimitFormatted);
                $('#' + prefix + 'burst_limit_up').val(uploadBurstLimitFormatted);
                $('#' + prefix + 'burst_threshold_down').val(downloadBurstThresholdFormatted);
                $('#' + prefix + 'burst_threshold_up').val(uploadBurstThresholdFormatted);
                $('#' + prefix + 'burst_time_down').val(8);
                $('#' + prefix + 'burst_time_up').val(8);
                $('#' + prefix + 'burst_priority_down').val(8);
                $('#' + prefix + 'burst_priority_up').val(8);

                // Update calculation message
                const actualBurstDownloadTime = (downloadBurstThreshold / downloadBurstLimit * 8).toFixed(1);
                const actualBurstUploadTime = (uploadBurstThreshold / uploadBurstLimit * 8).toFixed(1);
                const calculationDetails = 'Auto-calculated. Actual burst duration: ~' + actualBurstDownloadTime + 's/' + actualBurstUploadTime + 's';
                $('#' + prefix + 'burst_calculation').text(calculationDetails);
            }
        } // Update preview function
        function updatePreview() {
            $('#preview-name').text($('#name').val() || '-');

            const price = $('#price').val();
            $('#preview-price').text(price ? 'Rp ' + new Intl.NumberFormat('id-ID').format(price) : '-');

            const validity = $('#validity_period').val();
            $('#preview-validity').text(validity ? validity + ' days' : '-');

            const status = $('#add_status').val();
            console.log('Status value:', status, 'Selected index:', $('#add_status').prop('selectedIndex'));
            $('#preview-status').text(status ? status.charAt(0).toUpperCase() + status.slice(1) : '-');

            // Update bandwidth profile preview
            const bandwidthProfile = $('#bandwidth_profile_id option:selected').text();
            $('#preview-bandwidth').text(bandwidthProfile !== 'Select Bandwidth Profile' ? bandwidthProfile : '-');

            // Update PPPoE profile preview
            const pppoeProfile = $('#group_profile_id option:selected').text();
            $('#preview-group-profile').text(pppoeProfile !== 'Select Group Profile' ? pppoeProfile : '-');

            // Update default profile MikroTik preview
            const defaultProfile = $('#default_profile_mikrotik option:selected').text();
            $('#preview-default-profile').text(defaultProfile !== 'Select Default Profile' ? defaultProfile : '-');
        } // Function to ensure select displays correctly
        function fixSelectDisplay() {
            // Force refresh of select elements without breaking layout
            $('.form-select').each(function() {
                var $this = $(this);
                var currentVal = $this.val();
                if (currentVal) {
                    // Use a more gentle approach that doesn't break input-group layout
                    $this.val(currentVal);
                    // Trigger a gentle refresh instead of hide/show
                    $this[0].offsetHeight; // Force browser reflow
                }
            });
        } // Add preview update listeners
        $('#name, #price, #validity_period, #add_status, #bandwidth_profile_id, #group_profile_id, #default_profile_mikrotik').on('input change', updatePreview);

        // Bandwidth profile change handlers
        $('#bandwidth_profile_id').on('change', function() {
            displayBandwidthInfo($(this).val());
        });

        $('#edit_bandwidth_profile_id').on('change', function() {
            displayBandwidthInfo($(this).val(), 'edit_');
        });

        // Burst calculation toggle handlers
        $('#show_burst_calc').on('change', function() {
            if ($(this).is(':checked')) {
                $('#burst_calc_section').slideDown();
            } else {
                $('#burst_calc_section').slideUp();
            }
        });
        $('#edit_show_burst_calc').on('change', function() {
            if ($(this).is(':checked')) {
                $('#edit_burst_calc_section').slideDown();
            } else {
                $('#edit_burst_calc_section').slideUp();
            }
        });

        // Auto-calculate button handlers
        $('#auto_calculate_burst').on('click', function() {
            const profileId = $('#bandwidth_profile_id').val();
            if (profileId) {
                autoCalculateBurst(profileId);
            } else {
                Swal.fire('Info', 'Please select a bandwidth profile first', 'info');
            }
        });

        $('#edit_auto_calculate_burst').on('click', function() {
            const profileId = $('#edit_bandwidth_profile_id').val();
            if (profileId) {
                autoCalculateBurst(profileId, 'edit_');
            } else {
                Swal.fire('Info', 'Please select a bandwidth profile first', 'info');
            }
        }); // Load profiles when modal is opened
        $('#addPackageProfileModal').on('show.bs.modal', function() {
            // Reset form first
            $('#addPackageProfileForm')[0].reset();

            // Force select the active status by setting it directly
            const statusSelect = $('#add_status');
            statusSelect.empty(); // Clear existing options
            statusSelect.append('<option value="active">Active</option>');
            statusSelect.append('<option value="inactive">Inactive</option>');
            statusSelect.val('active').prop('selected', true);

            // Set other default values
            $('#grace_period').val(3);
            $('#validity_period').val(30);
            $('#auto_renewal').val(0);

            // Trigger change events to update any listeners
            statusSelect.trigger('change');
            $('#grace_period').trigger('input');
            $('#validity_period').trigger('input');

            // Update preview
            updatePreview();

            // Load both bandwidth/group profiles and PPPoE profiles
            loadProfiles();
            loadPPPoEProfilesOnLoad(); // Load PPPoE profiles from MikroTik

            // Hide bandwidth display initially
            $('#bandwidth_display').hide();
            // Initialize burst calculation toggle
            $('#show_burst_calc').prop('checked', true);
            $('#burst_calc_section').show();
        }); // Load profiles when edit modal is opened (but don't interfere with form population)
        $('#editPackageProfileModal').on('shown.bs.modal', function() {
            // Only load profiles if they haven't been loaded yet
            if ($('#edit_bandwidth_profile_id option').length <= 1) {
                loadProfiles();
            }
            // Initialize burst calculation toggle
            $('#edit_show_burst_calc').prop('checked', true);
            $('#edit_burst_calc_section').show();
        });

        // Reset modals when hidden
        $('#addPackageProfileModal').on('hidden.bs.modal', function() {
            $('#addPackageProfileForm')[0].reset();

            // Reset to default values
            $('#add_status').val('active');
            $('#grace_period').val(3);
            $('#validity_period').val(30);

            $('#show_burst_calc').prop('checked', true);
            $('#burst_calc_section').show();
            displayBandwidthInfo(null);
            updatePreview();
        });

        $('#editPackageProfileModal').on('hidden.bs.modal', function() {
            $('#edit_show_burst_calc').prop('checked', true);
            $('#edit_burst_calc_section').show();
            displayBandwidthInfo(null, 'edit_');
        }); // Add Package Profile Form Submit
        $('#addPackageProfileForm').on('submit', function(e) {
            e.preventDefault();

            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Saving...').prop('disabled', true);

            // Prepare form data with CSRF token
            const formData = $(this).serialize();
            const csrfData = '&<?= csrf_token() ?>=<?= csrf_hash() ?>';

            $.ajax({
                url: '<?= site_url('internet-packages/package-profile/create') ?>',
                type: 'POST',
                data: formData + csrfData,
                success: function(response) {
                    // Sembunyikan preloader jika ada
                    $('.preloader, #preloader').hide();
                    if (response.success) {
                        // Tutup modal dulu, lalu setelah benar2 tertutup, tampilkan Swal
                        $('#addPackageProfileModal').modal('hide');
                        $('#addPackageProfileModal').one('hidden.bs.modal', function() {
                            $('.preloader, #preloader').hide();
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            });
                            $('#addPackageProfileForm')[0].reset();
                            if (typeof packageProfileTable !== 'undefined') {
                                packageProfileTable.ajax.reload(null, false);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                error: function(xhr) {
                    $('.preloader, #preloader').hide();
                    let errorMessage = 'An error occurred while saving';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) errorMessage = response.message;
                    } catch (e) {}
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        }); // Edit Package Profile Form Submit
        $('#editPackageProfileForm').on('submit', function(e) {
            e.preventDefault();

            const id = $('#edit_id').val();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Updating...').prop('disabled', true); // Prepare form data with CSRF token for PUT request
            const formData = $(this).serialize();
            const csrfData = '&<?= csrf_token() ?>=<?= csrf_hash() ?>';
            const allData = formData + csrfData; // Debug logging
            console.log('Edit form ID:', id);
            console.log('Sending PUT data:', allData);
            console.log('Form serialized:', formData);
            console.log('URL:', '<?= site_url('internet-packages/package-profile/') ?>' + id);

            $.ajax({
                url: '<?= site_url('internet-packages/package-profile/') ?>' + id,
                type: 'PUT',
                data: allData,
                success: function(response) {
                    // Sembunyikan preloader jika ada
                    $('.preloader, #preloader').hide();
                    if (response.success) {
                        $('#editPackageProfileModal').modal('hide');
                        $('#editPackageProfileModal').one('hidden.bs.modal', function() {
                            $('.preloader, #preloader').hide();
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            });
                            $('#editPackageProfileForm')[0].reset();
                            if (typeof packageProfileTable !== 'undefined') {
                                packageProfileTable.ajax.reload(null, false);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                error: function(xhr) {
                    $('.preloader, #preloader').hide();
                    let errorMessage = 'An error occurred while updating';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.errors) {
                            // Show specific validation errors
                            const errorList = Object.values(response.errors).join('\n');
                            errorMessage = 'Validation errors:\n' + errorList;
                        } else if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        }); // Edit button click handler
        $('#packageProfileTable').on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            console.log('Edit button clicked for ID:', id);

            // Get the row data directly from DataTable as a fallback
            const row = packageProfileTable.row($(this).closest('tr'));
            const rowData = row.data();
            console.log('Row data from DataTable:', rowData);

            // Use the ID from row data if available
            const actualId = rowData ? rowData.id : id;
            console.log('Using ID:', actualId);

            // Get package profile data
            $.get('<?= site_url('internet-packages/package-profile/') ?>' + actualId, function(data) {
                console.log('Retrieved data:', data);
                if (data.success) {
                    const profile = data.data;

                    // Load profiles first if not already loaded
                    if ($('#edit_bandwidth_profile_id option').length <= 1) {
                        loadProfiles();
                    }

                    // Show modal first
                    $('#editPackageProfileModal').modal('show');

                    // Populate form after a short delay to ensure modal is fully shown
                    setTimeout(function() {
                        $('#edit_id').val(profile.id);
                        $('#edit_name').val(profile.name);
                        $('#edit_description').val(profile.description || '');
                        $('#edit_price').val(profile.price);
                        $('#edit_validity_period').val(profile.validity_period);
                        $('#edit_grace_period').val(profile.grace_period || 0);
                        $('#edit_auto_renewal').val(profile.auto_renewal || 0);
                        $('#edit_status').val(profile.status);
                        $('#edit_bandwidth_profile_id').val(profile.bandwidth_profile_id || '');
                        $('#edit_group_profile_id').val(profile.group_profile_id || '');
                        $('#edit_default_profile_mikrotik').val(profile.default_profile_mikrotik || '');

                        // Trigger bandwidth display after setting values
                        if (profile.bandwidth_profile_id) {
                            displayBandwidthInfo(profile.bandwidth_profile_id, 'edit_');
                        }

                        console.log('Form populated with values:', {
                            name: $('#edit_name').val(),
                            price: $('#edit_price').val(),
                            validity_period: $('#edit_validity_period').val()
                        });
                    }, 300);
                } else {
                    Swal.fire('Error!', 'Could not load package profile data', 'error');
                }
            }).fail(function() {
                Swal.fire('Error!', 'Could not load package profile data', 'error');
            });
        }); // View button click handler
        $('#packageProfileTable').on('click', '.view-btn', function() {
            const id = $(this).data('id');

            // Get the row data directly from DataTable as a fallback
            const row = packageProfileTable.row($(this).closest('tr'));
            const rowData = row.data();
            const actualId = rowData ? rowData.id : id;

            // Get package profile data
            $.get('<?= site_url('internet-packages/package-profile/') ?>' + actualId, function(data) {
                if (data.success) {
                    const profile = data.data;

                    // Populate view modal
                    $('#view_name').text(profile.name || '-');
                    $('#view_description').text(profile.description || 'No description');
                    $('#view_price').text(profile.price ? 'Rp ' + new Intl.NumberFormat('id-ID').format(profile.price) : '-');
                    $('#view_validity_period').text(profile.validity_period ? profile.validity_period + ' days' : '-');
                    $('#view_grace_period').text(profile.grace_period ? profile.grace_period + ' days' : '0 days');
                    $('#view_bandwidth_profile').text(profile.bandwidth_profile || 'Not assigned');
                    $('#view_group_profile').text(profile.group_profile || 'Not assigned');
                    $('#view_auto_renewal').text(profile.auto_renewal == 1 ? 'Enabled' : 'Disabled');
                    $('#view_status').html(profile.status === 'active' ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-danger">Inactive</span>');
                    $('#view_created_at').text(profile.created_at ?
                        new Date(profile.created_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '-');

                    // Show modal
                    $('#viewPackageProfileModal').modal('show');
                } else {
                    Swal.fire('Error!', 'Could not load package profile data', 'error');
                }
            }).fail(function() {
                Swal.fire('Error!', 'Could not load package profile data', 'error');
            });
        }); // Delete Package Profile
        $('#packageProfileTable').on('click', '.delete-btn', function() {
            var id = $(this).data('id');

            // Get the row data directly from DataTable as a fallback
            const row = packageProfileTable.row($(this).closest('tr'));
            const rowData = row.data();
            const actualId = rowData ? rowData.id : id;

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= site_url('internet-packages/package-profile/') ?>' + actualId,
                        type: 'DELETE',
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                packageProfileTable.ajax.reload();
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function(xhr) {
                            var response = JSON.parse(xhr.responseText);
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'An error occurred',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Function to load PPPoE profiles from MikroTik
    function loadPPPoEProfilesOnLoad() {
        // Show loading in both select fields
        $('#group_profile_id').html('<option value="">Loading PPPoE profiles...</option>');
        $('#default_profile_mikrotik').html('<option value="">Loading default profiles...</option>');

        $.ajax({
            url: '<?= base_url('api/mikrotik/pppoe-profiles') ?>',
            type: 'GET',
            dataType: 'json',
            timeout: 30000, // 30 second timeout (increased from 10 seconds)
            beforeSend: function() {
                // Optional: Show loading indicator
                console.log('Loading PPPoE profiles from MikroTik...');
            },
            success: function(response) {
                console.log('PPPoE profiles response:', response);
                if (response.success && response.data && response.data.length > 0) {
                    populatePPPoESelect(response.data);
                    console.log('Loaded ' + response.data.length + ' PPPoE profiles from MikroTik');
                } else {
                    // MikroTik connection failed but returned graceful response
                    console.warn('MikroTik connection issue:', response.message);
                    $('#group_profile_id').html('<option value="">MikroTik offline - using database</option>');
                    $('#default_profile_mikrotik').html('<option value="">MikroTik offline - using database</option>');

                    // Load from database as fallback without showing error popup
                    loadGroupProfilesFromDB();
                }
            },
            error: function(xhr, status, error) {
                console.warn('AJAX error loading PPPoE profiles:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });

                // Silently fallback to database without showing error popup
                $('#group_profile_id').html('<option value="">MikroTik offline - using database</option>');
                $('#default_profile_mikrotik').html('<option value="">MikroTik offline - using database</option>');

                // Load from database as fallback
                loadGroupProfilesFromDB();
            },
            complete: function() {
                console.log('PPPoE profiles loading attempt completed');
            }
        });
    }





    // Function to load group profiles from database as fallback
    function loadGroupProfilesFromDB() {
        $.get('<?= site_url('internet-packages/group-profile/data') ?>', function(data) {
            var groupSelect = $('#group_profile_id, #edit_group_profile_id');
            groupSelect.empty().append('<option value="">Select Group Profile</option>');
            if (data.data && data.data.length > 0) {
                data.data.forEach(function(profile) {
                    groupSelect.append('<option value="' + profile.id + '">' + profile.name + '</option>');
                });
                console.log('Loaded group profiles from database as fallback');
            }
        }).fail(function() {
            console.warn('Could not load group profiles from database');
        });
    } // Function to populate PPPoE select dropdown
    function populatePPPoESelect(profiles) {
        // Only populate default_profile_mikrotik and edit_default_profile_mikrotik from PPPoE API
        var defaultSelect = $('#default_profile_mikrotik');
        defaultSelect.empty();
        defaultSelect.append('<option value="">Select Default Profile</option>');
        profiles.forEach(function(profile) {
            var optionText = profile.name; // Hanya nama saja
            var defaultOption = new Option(optionText, profile.name);
            defaultOption.setAttribute('data-profile', JSON.stringify(profile));
            defaultSelect.append(defaultOption);
        });

        // For edit modal
        var editDefaultSelect = $('#edit_default_profile_mikrotik');
        if (editDefaultSelect.length) {
            editDefaultSelect.empty();
            editDefaultSelect.append('<option value="">Select Default Profile</option>');
            profiles.forEach(function(profile) {
                var optionText = profile.name; // Hanya nama saja
                var option = new Option(optionText, profile.name);
                option.setAttribute('data-profile', JSON.stringify(profile));
                editDefaultSelect.append(option);
            });
        }
    }
</script>
<?= $this->endSection() ?>