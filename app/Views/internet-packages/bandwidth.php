<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<!-- CSRF Token Meta Tag -->
<meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">

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
                        <h4 class="card-title mb-0">Bandwidth Management</h4>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addBandwidthModal">
                                <i class="bx bx-plus"></i> Add Bandwidth
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="bandwidthTable" class="table table-striped table-hover align-middle table-bordered" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="150">Name</th>
                                        <th width="120">Download Min</th>
                                        <th width="120">Download Max</th>
                                        <th width="120">Upload Min</th>
                                        <th width="120">Upload Max</th>
                                        <th width="80">Status</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bandwidth Modal -->
<div class="modal fade" id="addBandwidthModal" tabindex="-1" aria-labelledby="addBandwidthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBandwidthModalLabel">Add Bandwidth Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addBandwidthForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Profile Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <!-- Unit Selection -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Speed Unit</label>
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" name="speed_unit" id="unit_kbps" value="kbps" checked>
                                    <label class="form-check-label" for="unit_kbps">Kbps</label>
                                </div>
                                <div class="form-check-inline ms-3">
                                    <input class="form-check-input" type="radio" name="speed_unit" id="unit_mbps" value="mbps">
                                    <label class="form-check-label" for="unit_mbps">Mbps</label>
                                </div>
                                <div class="form-text text-muted mt-2">
                                    <small><strong>Note:</strong> 1 Mbps = 1024 Kbps. Values will be automatically converted.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="download_min" class="form-label">Download Min <span id="download_min_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="download_min" name="download_min" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="download_max" class="form-label">Download Max <span id="download_max_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="download_max" name="download_max" min="1" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upload_min" class="form-label">Upload Min <span id="upload_min_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="upload_min" name="upload_min" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upload_max" class="form-label">Upload Max <span id="upload_max_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="upload_max" name="upload_max" min="1" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="add_status" name="status" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Bandwidth Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bandwidth Modal -->
<div class="modal fade" id="editBandwidthModal" tabindex="-1" aria-labelledby="editBandwidthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBandwidthModalLabel">Edit Bandwidth Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBandwidthForm">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_bandwidth_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Profile Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <!-- Unit Selection for Edit -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Speed Unit</label>
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" name="edit_speed_unit" id="edit_unit_kbps" value="kbps" checked>
                                    <label class="form-check-label" for="edit_unit_kbps">Kbps</label>
                                </div>
                                <div class="form-check-inline ms-3">
                                    <input class="form-check-input" type="radio" name="edit_speed_unit" id="edit_unit_mbps" value="mbps">
                                    <label class="form-check-label" for="edit_unit_mbps">Mbps</label>
                                </div>
                                <div class="form-text text-muted mt-2">
                                    <small><strong>Note:</strong> 1 Mbps = 1024 Kbps. Values will be automatically converted.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_download_min" class="form-label">Download Min <span id="edit_download_min_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_download_min" name="download_min" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_download_max" class="form-label">Download Max <span id="edit_download_max_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_download_max" name="download_max" min="1" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_upload_min" class="form-label">Upload Min <span id="edit_upload_min_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_upload_min" name="upload_min" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_upload_max" class="form-label">Upload Max <span id="edit_upload_max_unit">(Kbps)</span> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_upload_max" name="upload_max" min="1" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Bandwidth Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() { // CSRF Token Setup - Read from cookie
        function getCSRFToken() {
            let token = '';
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === '<?= csrf_token() ?>') {
                    token = decodeURIComponent(value);
                    break;
                }
            }

            // Fallback: try to get from meta tag if cookie not found
            if (!token) {
                const metaToken = document.querySelector('meta[name="<?= csrf_token() ?>"]');
                if (metaToken) {
                    token = metaToken.getAttribute('content');
                }
            }

            // Fallback: try to get from form input
            if (!token) {
                const inputToken = document.querySelector('input[name="<?= csrf_token() ?>"]');
                if (inputToken) {
                    token = inputToken.value;
                }
            }

            return token;
        }
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    const token = getCSRFToken();
                    if (token) {
                        xhr.setRequestHeader('<?= csrf_header() ?>', token);
                    }
                }
            },
            error: function(xhr, status, error) {
                // Handle session expiration globally
                if (xhr.status === 401) {
                    Swal.fire({
                        title: 'Session Expired',
                        text: 'Your session has expired. Please login again.',
                        icon: 'warning',
                        confirmButtonText: 'Login',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = '<?= site_url('login') ?>';
                    });
                    return false; // Prevent further processing
                }

                if (xhr.status === 403) {
                    Swal.fire({
                        title: 'Access Denied',
                        text: 'You do not have permission to perform this action.',
                        icon: 'error'
                    });
                    return false;
                }
            }
        });

        // Add custom CSS for better table appearance
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .table-responsive {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }
                #bandwidthTable {
                    min-width: 800px;
                }
                #bandwidthTable th {
                    background-color: #f8f9fa;
                    font-weight: 600;
                    font-size: 12px;
                    text-align: center;
                    vertical-align: middle;
                    white-space: nowrap;
                }
                #bandwidthTable td {
                    font-size: 12px;
                    text-align: center;
                    vertical-align: middle;
                    white-space: nowrap;
                }
                .btn-sm {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.775rem;
                }
                .badge {
                    font-size: 0.7rem;
                }                .modal-lg {
                    max-width: 800px;
                }
            `)
            .appendTo('head');

        // Unit conversion functions
        function updateUnitLabels(unit) {
            const unitText = unit === 'mbps' ? '(Mbps)' : '(Kbps)';
            $('#download_min_unit, #download_max_unit, #upload_min_unit, #upload_max_unit').text(unitText);
        }

        function updateEditUnitLabels(unit) {
            const unitText = unit === 'mbps' ? '(Mbps)' : '(Kbps)';
            $('#edit_download_min_unit, #edit_download_max_unit, #edit_upload_min_unit, #edit_upload_max_unit').text(unitText);
        }

        function convertValues(fromUnit, toUnit, formPrefix = '') {
            const fields = ['download_min', 'download_max', 'upload_min', 'upload_max'];
            const prefix = formPrefix ? formPrefix + '_' : '';

            fields.forEach(field => {
                const input = $('#' + prefix + field);
                let value = parseFloat(input.val()) || 0;

                if (fromUnit === 'kbps' && toUnit === 'mbps') {
                    // Convert Kbps to Mbps
                    value = value / 1024;
                } else if (fromUnit === 'mbps' && toUnit === 'kbps') {
                    // Convert Mbps to Kbps
                    value = value * 1024;
                }

                input.val(formatNumber(value));
            });
        }

        // Handle unit change for Add form
        $('input[name="speed_unit"]').on('change', function() {
            const newUnit = $(this).val();
            const oldUnit = newUnit === 'mbps' ? 'kbps' : 'mbps';

            updateUnitLabels(newUnit);
            convertValues(oldUnit, newUnit);
        });

        // Handle unit change for Edit form
        $('input[name="edit_speed_unit"]').on('change', function() {
            const newUnit = $(this).val();
            const oldUnit = newUnit === 'mbps' ? 'kbps' : 'mbps';

            updateEditUnitLabels(newUnit);
            convertValues(oldUnit, newUnit, 'edit');
        }); // Initialize DataTable with better error handling
        var table = null;

        function initDataTable() {
            // Destroy existing table if it exists
            if (table && $.fn.DataTable.isDataTable('#bandwidthTable')) {
                table.destroy();
                $('#bandwidthTable').empty();
            }

            table = initStandardDataTable('#bandwidthTable', {
                ajax: {
                    url: '<?= site_url('internet-packages/bandwidth/data') ?>',
                    type: 'GET',
                    dataSrc: function(json) {
                        if (!json || !Array.isArray(json.data)) {
                            console.warn('Invalid data received:', json);
                            return [];
                        }
                        return json.data;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable AJAX error:', xhr, error, code);
                        Swal.fire({
                            title: 'Error Loading Data',
                            text: 'Failed to load bandwidth data. Please refresh the page.',
                            icon: 'error',
                            confirmButtonText: 'Refresh',
                            allowOutsideClick: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                columns: [{
                        data: null,
                        title: 'No',
                        width: '50px',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'name',
                        title: 'Name',
                        width: '150px',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    }, {
                        data: 'download_min',
                        title: 'Download Min',
                        width: '120px',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == null || data === '') return '-';
                            const mbps = parseFloat(data) / 1024;
                            return formatNumber(mbps) + ' Mbps';
                        }
                    }, {
                        data: 'download_max',
                        title: 'Download Max',
                        width: '120px',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == null || data === '') return '-';
                            const mbps = parseFloat(data) / 1024;
                            return formatNumber(mbps) + ' Mbps';
                        }
                    }, {
                        data: 'upload_min',
                        title: 'Upload Min',
                        width: '120px',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == null || data === '') return '-';
                            const mbps = parseFloat(data) / 1024;
                            return formatNumber(mbps) + ' Mbps';
                        }
                    }, {
                        data: 'upload_max',
                        title: 'Upload Max',
                        width: '120px',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == null || data === '') return '-';
                            const mbps = parseFloat(data) / 1024;
                            return formatNumber(mbps) + ' Mbps';
                        }
                    },
                    {
                        data: 'status',
                        title: 'Status',
                        width: '80px',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data === 'active') {
                                return '<span class="badge bg-success">Active</span>';
                            } else if (data === 'inactive') {
                                return '<span class="badge bg-danger">Inactive</span>';
                            }
                            return '<span class="badge bg-secondary">Unknown</span>';
                        }
                    },
                    {
                        data: null,
                        title: 'Actions',
                        width: '100px',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!row || !row.id) return '-';
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                            data-id="${row.id}" title="Edit" data-bs-toggle="tooltip">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                            data-id="${row.id}" title="Delete" data-bs-toggle="tooltip">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                responsive: true,
                scrollX: true,
                autoWidth: false,
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    processing: "Loading data...",
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No bandwidth profiles available",
                    zeroRecords: "No matching records found"
                },
                drawCallback: function(settings) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                initComplete: function(settings, json) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        } // Initialize the DataTable
        initDataTable();

        // Safe table reload function
        function reloadTable() {
            if (table && $.fn.DataTable.isDataTable('#bandwidthTable')) {
                try {
                    table.ajax.reload(null, false); // Keep current page
                } catch (e) {
                    console.warn('Table reload failed, reinitializing:', e);
                    initDataTable();
                }
            } else {
                initDataTable();
            }
        }

        // Window and error handling
        $(window).on('beforeunload', function() {
            if (table && $.fn.DataTable.isDataTable('#bandwidthTable')) {
                table.destroy();
            }
        }); // Global error handler for AJAX
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (xhr.status === 401 || xhr.status === 403) {
                let message = 'Your session has expired. Please login again.';

                // Try to get the server message if available
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch (e) {
                    // Use default message
                }

                Swal.fire({
                    title: 'Session Expired',
                    text: message,
                    icon: 'warning',
                    confirmButtonText: 'Login',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '<?= site_url('login') ?>';
                });
                return;
            }

            // Handle other errors with generic message
            if (xhr.status >= 400) {
                let errorMessage = 'An error occurred. Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // Use default message
                }

                Swal.fire('Error', errorMessage, 'error');
            }
        });

        // Ensure table is responsive on window resize
        $(window).on('resize', function() {
            if (table && $.fn.DataTable.isDataTable('#bandwidthTable')) {
                table.columns.adjust();
                if (table.responsive && typeof table.responsive.recalc === 'function') {
                    table.responsive.recalc();
                }
            }
        });

        // Add client-side validation for min/max values
        function validateMinMaxValues() {
            var downloadMin = parseInt($('#download_min').val()) || 0;
            var downloadMax = parseInt($('#download_max').val()) || 0;
            var uploadMin = parseInt($('#upload_min').val()) || 0;
            var uploadMax = parseInt($('#upload_max').val()) || 0;

            if (downloadMax < downloadMin) {
                Swal.fire('Validation Error', 'Maximum download speed must be greater than or equal to minimum download speed', 'error');
                return false;
            }

            if (uploadMax < uploadMin) {
                Swal.fire('Validation Error', 'Maximum upload speed must be greater than or equal to minimum upload speed', 'error');
                return false;
            }

            return true;
        }

        // Add input event listeners for real-time validation
        $('#download_min, #download_max').on('input', function() {
            var downloadMin = parseInt($('#download_min').val()) || 0;
            var downloadMax = parseInt($('#download_max').val()) || 0;

            if (downloadMin > 0 && downloadMax > 0 && downloadMax < downloadMin) {
                $('#download_max').addClass('is-invalid');
                $('#download_max').siblings('.invalid-feedback').remove();
                $('#download_max').after('<div class="invalid-feedback">Max must be ≥ Min</div>');
            } else {
                $('#download_max').removeClass('is-invalid');
                $('#download_max').siblings('.invalid-feedback').remove();
            }
        });

        $('#upload_min, #upload_max').on('input', function() {
            var uploadMin = parseInt($('#upload_min').val()) || 0;
            var uploadMax = parseInt($('#upload_max').val()) || 0;

            if (uploadMin > 0 && uploadMax > 0 && uploadMax < uploadMin) {
                $('#upload_max').addClass('is-invalid');
                $('#upload_max').siblings('.invalid-feedback').remove();
                $('#upload_max').after('<div class="invalid-feedback">Max must be ≥ Min</div>');
            } else {
                $('#upload_max').removeClass('is-invalid');
                $('#upload_max').siblings('.invalid-feedback').remove();
            }
        }); // Add Bandwidth Form Submit
        $('#addBandwidthForm').on('submit', function(e) {
            e.preventDefault();

            // Validate min/max values
            if (!validateMinMaxValues()) {
                return;
            }

            // Prepare data with unit conversion
            var formData = $(this).serializeArray();
            var data = {};

            // Convert form data to object
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });

            // Convert to Kbps for storage if unit is Mbps
            var unit = $('input[name="speed_unit"]:checked').val();
            if (unit === 'mbps') {
                data.download_min = Math.round(parseFloat(data.download_min) * 1024);
                data.download_max = Math.round(parseFloat(data.download_max) * 1024);
                data.upload_min = Math.round(parseFloat(data.upload_min) * 1024);
                data.upload_max = Math.round(parseFloat(data.upload_max) * 1024);
            }

            $.ajax({
                url: '<?= site_url('internet-packages/bandwidth/create') ?>',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#addBandwidthModal').modal('hide');
                        $('#addBandwidthForm')[0].reset(); // Reset unit to Kbps
                        $('#unit_kbps').prop('checked', true);
                        updateUnitLabels('kbps');
                        reloadTable();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    var response = JSON.parse(xhr.responseText);
                    Swal.fire('Error', response.message || 'An error occurred', 'error');
                }
            });
        });

        // Edit Bandwidth
        $('#bandwidthTable').on('click', '.edit-btn', function() {
            var id = $(this).data('id');

            // Get bandwidth data
            $.ajax({
                url: '<?= site_url('internet-packages/bandwidth/') ?>' + id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#edit_bandwidth_id').val(data.id);
                        $('#edit_name').val(data.name);
                        $('#edit_description').val(data.description);
                        $('#edit_status').val(data.status); // Determine best unit to display using helper function
                        var downloadMin = parseInt(data.download_min);
                        var downloadMax = parseInt(data.download_max);
                        var uploadMin = parseInt(data.upload_min);
                        var uploadMax = parseInt(data.upload_max);

                        var bestUnit = getBestUnit(downloadMin, downloadMax, uploadMin, uploadMax);

                        if (bestUnit === 'mbps') {
                            // Convert to Mbps and set Mbps unit
                            $('#edit_download_min').val(formatNumber(downloadMin / 1024));
                            $('#edit_download_max').val(formatNumber(downloadMax / 1024));
                            $('#edit_upload_min').val(formatNumber(uploadMin / 1024));
                            $('#edit_upload_max').val(formatNumber(uploadMax / 1024));
                            $('#edit_unit_mbps').prop('checked', true);
                            updateEditUnitLabels('mbps');
                        } else {
                            // Keep in Kbps
                            $('#edit_download_min').val(downloadMin);
                            $('#edit_download_max').val(downloadMax);
                            $('#edit_upload_min').val(uploadMin);
                            $('#edit_upload_max').val(uploadMax);
                            $('#edit_unit_kbps').prop('checked', true);
                            updateEditUnitLabels('kbps');
                        }

                        $('#editBandwidthModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    var response = JSON.parse(xhr.responseText);
                    Swal.fire('Error', response.message || 'An error occurred', 'error');
                }
            });
        }); // Edit Bandwidth Form Submit
        $('#editBandwidthForm').on('submit', function(e) {
            e.preventDefault();

            var id = $('#edit_bandwidth_id').val();

            // Prepare data with unit conversion
            var formData = $(this).serializeArray();
            var data = {};

            // Convert form data to object
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            }); // Convert to Kbps for storage if unit is Mbps
            var unit = $('input[name="edit_speed_unit"]:checked').val();
            if (unit === 'mbps') {
                data.download_min = Math.round(parseFloat(data.download_min) * 1024);
                data.download_max = Math.round(parseFloat(data.download_max) * 1024);
                data.upload_min = Math.round(parseFloat(data.upload_min) * 1024);
                data.upload_max = Math.round(parseFloat(data.upload_max) * 1024);
            }

            $.ajax({
                url: '<?= site_url('internet-packages/bandwidth/update/') ?>' + id,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#editBandwidthModal').modal('hide');
                        $('#editBandwidthForm')[0].reset(); // Reset unit to Kbps
                        $('#edit_unit_kbps').prop('checked', true);
                        updateEditUnitLabels('kbps');
                        reloadTable();
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {

                    try {
                        var response = JSON.parse(xhr.responseText);
                        Swal.fire('Error', response.message || 'An error occurred', 'error');
                    } catch (e) {
                        Swal.fire('Error', 'An error occurred. Please try again.', 'error');
                    }
                }
            });
        });

        // Modal event handlers

        // Ensure status select is set to default (active) when modal is shown
        $('#addBandwidthModal').on('shown.bs.modal', function() {
            // Set smart defaults for new bandwidth profiles
            $('#unit_mbps').prop('checked', true);
            updateUnitLabels('mbps');

            // Set some example values in Mbps
            $('#download_min').val('1');
            $('#download_max').val('2');
            $('#upload_min').val('0.5');
            $('#upload_max').val('1');

            // Set status to active by default
            $('#add_status').val('active');
        });


        $('#addBandwidthModal').on('hidden.bs.modal', function() {
            $('#addBandwidthForm')[0].reset();
            $('#unit_kbps').prop('checked', true);
            updateUnitLabels('kbps');
            // Reset status to active
            $('#add_status').val('active');
        });

        $('#editBandwidthModal').on('hidden.bs.modal', function() {
            $('#editBandwidthForm')[0].reset();
            $('#edit_unit_kbps').prop('checked', true);
            updateEditUnitLabels('kbps');
        }); // Delete Bandwidth
        $('#bandwidthTable').on('click', '.delete-btn', function() {
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
                    // Get fresh CSRF token
                    const token = getCSRFToken();

                    $.ajax({
                        url: '<?= site_url('internet-packages/bandwidth/') ?>' + id,
                        type: 'POST',
                        data: {
                            '_method': 'DELETE',
                            '<?= csrf_token() ?>': token
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.success) {
                                reloadTable();
                                Swal.fire('Deleted!', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            console.error('Delete error:', xhr.status, xhr.responseText);
                            let errorMessage = 'An error occurred while deleting the bandwidth profile.';

                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // Handle different error formats
                                if (xhr.status === 403) {
                                    errorMessage = 'Permission denied. Please check your session and try again.';
                                } else if (xhr.status === 404) {
                                    errorMessage = 'Bandwidth profile not found.';
                                } else if (xhr.status >= 500) {
                                    errorMessage = 'Server error occurred. Please try again later.';
                                }
                            }

                            Swal.fire('Error', errorMessage, 'error');
                        }
                    });
                }
            });
        });

        // Helper function for clean number formatting (removes unnecessary decimals)
        function formatNumber(value) {
            const num = parseFloat(value);
            return num % 1 === 0 ? num.toString() : num.toFixed(2);
        } // Helper function to determine the best unit for display
        function getBestUnit(downloadMin, downloadMax, uploadMin, uploadMax) {
            // If all values are multiples of 1024 and >= 1024, use Mbps
            return (downloadMin % 1024 === 0 && downloadMax % 1024 === 0 &&
                uploadMin % 1024 === 0 && uploadMax % 1024 === 0 &&
                downloadMin >= 1024) ? 'mbps' : 'kbps';
        }

        // Test delete function
        window.testDeleteFunc = function() {
            const testId = 5; // Use ID 5 which should exist
            const token = getCSRFToken();

            console.log('=== Testing Delete Function ===');
            console.log('Test ID:', testId);
            console.log('CSRF Token:', token);
            console.log('CSRF Token Name:', '<?= csrf_token() ?>');
            console.log('CSRF Header Name:', '<?= csrf_header() ?>');

            $.ajax({
                url: '<?= site_url('internet-packages/bandwidth/') ?>' + testId,
                type: 'POST',
                data: {
                    '_method': 'DELETE',
                    '<?= csrf_token() ?>': token
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('=== Delete Success ===', response);
                    alert('Success: ' + JSON.stringify(response));
                    reloadTable();
                },
                error: function(xhr) {
                    console.log('=== Delete Error ===');
                    console.log('Status:', xhr.status);
                    console.log('Response:', xhr.responseText);
                    alert('Error ' + xhr.status + ': ' + xhr.responseText);
                }
            });
        };
    });
</script>

<?= $this->endSection() ?>