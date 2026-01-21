<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Data Pelanggan &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Custom responsive styles for DataTables */
    @media screen and (max-width: 767px) {

        div.dataTables_wrapper div.dataTables_length,
        /* div.dataTables_wrapper div.dataTables_filter, */
        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate {
            text-align: center;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        /* Make action buttons stack better on mobile */
        .dt-buttons {
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-group {
            display: inline-flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Improve table cell display on mobile */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            top: 50%;
            transform: translateY(-50%);
        }
    }

    /* Customer Status Styling */
    .customer-suspended {
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Row untuk pelanggan overdue - background merah muda */
    tr.customer-overdue {
        background-color: #ffe6e6 !important;
    }

    tr.customer-overdue:hover {
        background-color: #ffd4d4 !important;
    }

    /* Teks merah untuk pelanggan overdue */
    .text-overdue {
        color: #dc3545 !important;
        font-weight: 600 !important;
    }

    .customer-overdue-hover:hover {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    /* Add pulsing animation for overdue customers */
    .customer-suspended {
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    /* Date badge styling */
    .table .badge {
        font-size: 0.75rem;
        font-weight: bold;
        padding: 0.375rem 0.75rem;
    }

    /* Remove ALL rounded corners from cards */
    .card,
    .card *,
    .mini-stats-wid,
    .mini-stats-wid *,
    .card.mini-stats-wid,
    .card-body {
        border-radius: 8px !important;
    }

    /* Date columns responsive */
    @media screen and (max-width: 1200px) {

        .table th:nth-child(7),
        .table td:nth-child(7),
        .table th:nth-child(8),
        .table td:nth-child(8) {
            min-width: 100px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
if (!function_exists('generateNomorLayanan')) {
    function generateNomorLayanan($seed = null)
    {
        $prefix = '141437';
        if ($seed) {
            mt_srand(crc32($seed));
        }
        $random = '';
        for ($i = 0; $i < 6; $i++) {
            $random .= mt_rand(0, 9);
        }
        if ($seed) mt_srand(); // reset
        return $prefix . $random;
    }
}
?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Pelanggan</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Pelanggan</a></li>
                            <li class="breadcrumb-item active">Pelanggan</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row mb-4">
            <div class="col-12 table-responsive">
                <!-- Stats Cards Row -->
                <div class="row">
                    <div class="col-sm-3">
                        <a href="javascript:void(0)" id="newCustomerCard">
                            <div class="card mini-stats-wid" style="border-radius:18px;overflow:hidden;">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3 align-self-center">
                                            <i class="bx bx-user-plus h2 text-info mb-0"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-2">Pelanggan baru bulan ini</p>
                                            <h3 class="mb-0 text-info"><?= esc($countStatus['new_this_month'] ?? 0) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mini-stats-wid" style="border-radius:18px;overflow:hidden;">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3 align-self-center">
                                        <i class="bx bx-user-voice h2 text-success mb-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-2">Pelanggan Aktif</p>
                                        <h3 class="mb-0 text-success"><?= esc($countStatus['active'] ?? 0) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mini-stats-wid" style="border-radius:18px;overflow:hidden;">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3 align-self-center">
                                        <i class="bx bx-user-circle h2 text-danger mb-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-2">Pelanggan Tidak Aktif</p>
                                        <h3 class="mb-0 text-danger"><?= esc($countStatus['inactive'] ?? 0) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <a href="javascript:void(0)" id="suspendedCustomerCard">
                            <div class="card mini-stats-wid" style="border-radius:18px;overflow:hidden;">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3 align-self-center">
                                            <i class="bx bx-pause-circle h2 text-warning mb-0"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-2">Pelanggan Suspend</p>
                                            <h3 class="mb-0 text-warning"><?= esc($countStatus['suspended'] ?? 0) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Main Customer Table Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card" style="border-radius:18px;overflow:hidden;">
                            <div class="card-body">
                                <!-- Action Buttons Row -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="<?= site_url('customers/new') ?>" class="btn btn-primary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                                <i class="bx bx-plus" style="font-size:20px; padding-right:5px;"></i>
                                                Tambah Pelanggan
                                            </a>
                                            <a href="<?= site_url('customers/map-customers') ?>" class="btn btn-success custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                                <i class="bx bx-map" style="font-size:20px; padding-right:5px;"></i>
                                                Maps
                                            </a>
                                            <!-- Statistik Bulanan button removed as requested -->
                                            <!-- Tombol Impor Excel dihapus -->
                                            <a href="<?= site_url('customers/export/excel') ?>" class="btn btn-warning custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                                <i class="bx bx-file" style="font-size:20px; padding-right:5px;"></i>
                                                Ekspor Excel
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                            <!-- Tombol Hapus Filter dihapus -->
                                            <a href="javascript:void(0)" id="manualIsolated" class="btn btn-warning custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                                <i class="bx bx-lock" style="font-size:20px; padding-right:5px;"></i>
                                                Manual Isolir
                                            </a>
                                            <a href="javascript:void(0)" id="openIsolated" class="btn btn-success custom-radius" style="display:inline-flex;align-items:center;justify-content:center;" data-url="<?= site_url('customers/modal/open-isolated') ?>">
                                                <i class="bx bx-lock-open" style="font-size:20px; padding-right:5px;"></i>
                                                Buka Isolir
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filters Section -->
                                <!-- Filter section dihapus sesuai permintaan -->
                                <div class="table-responsive">
                                    <table id="customerTable" class="table table-striped table-hover align-middle table-bordered nowrap" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center align-middle" style="width: 50px;">
                                                    <input type="checkbox" id="checkAll" class="form-check-input">
                                                </th>
                                                <th class="text-center align-middle" style="width: 50px;">#</th>
                                                <th class="text-center">Tindakan</th>
                                                <th class="align-middle">Nama Pelanggan</th>
                                                <th>No. Layanan</th>
                                                <th>PPPoE Username</th>
                                                <th class="text-center">Tanggal Tempo</th>
                                                <th>Telepon</th>
                                                <th>Paket Internet</th>
                                                <th>Harga</th>
                                                <th class="text-center">Metode Langganan</th>
                                                <th class="text-center">Status</th>
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

            <!-- Static Backdrop Modal -->
            <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
                aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="" id="formDeleteAll">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Hapus Data Terpilih</h5>
                                <button type="button" class="btn-close cancelDelete" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin akan menghapus data yang terpilih ?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light cancelDelete" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" id="okButton">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- container-fluid -->
</div>
<!-- End Page-content -->

<!-- Custom CSS for better mobile display -->
<style>
    /* Better mobile scrolling for DataTables */
    .dataTables_wrapper .dataTables_scroll {
        overflow: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Improve table on small screens */
    @media screen and (max-width: 767px) {
        .table-responsive {
            border: none;
            margin-bottom: 0;
        }

        .dataTables_wrapper .dataTables_length,
        /* .dataTables_wrapper .dataTables_filter {
            text-align: left !important;
            margin-bottom: 0.5rem;
        } */

        .dataTables_info,
        .dataTables_paginate {
            margin-top: 0.5rem !important;
            width: 100%;
            text-align: center !important;
        }

        /* Ensure buttons don't overflow */
        .btn {
            margin-bottom: 5px;
            white-space: nowrap;
        }

        /* Make filter dropdowns full width on mobile */
        /* .form-control,
        .select2-container {
            width: 100% !important;
        } */
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Add the JavaScript file that contains the initStandardDataTable function -->
<script src="<?= base_url() ?>backend/assets/js/custom.js"></script>
<!-- Fallback in case the function is not defined in the included file -->
<script>
    // Fallback implementation of initStandardDataTable if not defined in custom.js
    if (typeof window.initStandardDataTable !== 'function') {
        window.standardDataTableConfig = {
            processing: true,
            serverSide: false,
            responsive: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No data available",
                infoFiltered: "(filtered from _MAX_ total records)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        };

        window.initStandardDataTable = function(selector, config = {}) {
            const finalConfig = {
                ...window.standardDataTableConfig,
                ...config
            };
            return $(selector).DataTable(finalConfig);
        };
    }

    // Add the missing initServerSideDataTable function
    if (typeof window.initServerSideDataTable !== 'function') {
        window.serverSideDataTableConfig = {
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No data available",
                infoFiltered: "(filtered from _MAX_ total records)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        };

        window.initServerSideDataTable = function(selector, config = {}) {
            const finalConfig = {
                ...window.serverSideDataTableConfig,
                ...config
            };
            return $(selector).DataTable(finalConfig);
        };
    }
</script>

<script>
    // Debug mode - set to true to enable debugging
    window.debugMode = false;

    // Show Notify if success flashdata exists
    <?php if (session()->getFlashdata('success')): ?>
        $(function() {
            showToastMessage('success', "<?= esc(session()->getFlashdata('success'), 'js') ?>", 'Berhasil');
            // Refresh the table when returning from edit/create to show updated data
            if (typeof table !== 'undefined' && table) {
                // Clear state and force full reload
                table.state.clear();
                setTimeout(function() {
                    table.ajax.reload(null, false); // false preserves current page
                }, 500);
            }
        });
    <?php endif; ?>

    // Also handle error messages
    <?php if (session()->getFlashdata('error')): ?>
        $(function() {
            showToastMessage('error', "<?= esc(session()->getFlashdata('error'), 'js') ?>", 'Error');
        });
    <?php endif; ?>

    $(document).on({
        ajaxStart: function() {
            $('#myLoading').removeClass("dontDisplay").addClass("d-flex");
        },
        ajaxStop: function() {
            $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
        }
    });

    function showToastMessage(status, messageText, title) {
        // Use toastr instead of Notify
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "10000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Map status to toastr methods
        switch (status) {
            case 'success':
                toastr.success(messageText, title);
                break;
            case 'error':
                toastr.error(messageText, title);
                break;
            case 'warning':
                toastr.warning(messageText, title);
                break;
            case 'info':
                toastr.info(messageText, title);
                break;
            default:
                toastr.info(messageText, title);
        }
    }
</script>
<script>
    $(function() {
        // Global function to escape HTML content
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        var isMobile = false; //initiate as false
        // device detection
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) ||
            /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-|0|1)|47|mc|nd|ri)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
            isMobile = true;

            // Add special handling for mobile devices if needed
            $(document).ready(function() {
                // Ensure all select2 dropdowns are 100% width on mobile
                $('.select2-container').css('width', '100%');

                // Adjust table container for better mobile scrolling
                $('.dataTables_wrapper').css({
                    'overflow-x': 'auto',
                    '-webkit-overflow-scrolling': 'touch'
                });
            });
        }
        var role = "true";
        var showActionColumn = role == 'true' ? true : false;
        var table; // Deklarasikan table secara global
        var debounceSearch; // Deklarasikan debounceSearch secara global
        // Inisialisasi DataTables hanya jika tabel ada di DOM
        if ($('#customerTable').length > 0) {
            if ($.fn.DataTable.isDataTable('#customerTable')) {
                $('#customerTable').DataTable().destroy();
            }
            table = initStandardDataTable('#customerTable', {
                serverSide: true,
                processing: true,
                pageLength: 15,
                lengthMenu: [
                    [10, 15, 25, 50, 100, -1],
                    [10, 15, 25, 50, 100, "Semua"]
                ],
                searching: false, // Filter dihapus
                order: [
                    [2, 'asc']
                ], // Default sort by name
                stateSave: false, // Disable state saving to ensure fresh data
                responsive: false, // Disable responsive behavior
                scrollX: true, // Enable horizontal scrolling
                scrollCollapse: true, // Enable scroll collapse
                columnDefs: [{
                        width: '50px',
                        targets: 0
                    }, // Checkbox
                    {
                        width: '50px',
                        targets: 1
                    }, // Number
                    {
                        width: '120px',
                        targets: 2
                    }, // Actions
                    {
                        width: '200px',
                        targets: 3
                    }, // Name
                    {
                        width: '120px',
                        targets: 6
                    }, // Tanggal Tempo
                    {
                        width: '100px',
                        targets: -1
                    }, // Actions (last column)
                    {
                        width: '100px',
                        targets: -2
                    }, // Status
                    {
                        width: '120px',
                        targets: -3
                    } // Subscription Method
                ],
                // === DOM LAYOUT ===
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                ajax: {
                    url: "<?= site_url('customers/data') ?>",
                    type: "POST",
                    data: function(d) {
                        // Tambahkan CSRF token ke data POST
                        d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                        // Semua filter dihapus
                    },
                    beforeSend: function(xhr) {
                        // Add CSRF header for additional protection
                        xhr.setRequestHeader('X-CSRF-TOKEN', '<?= csrf_hash() ?>');
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables AJAX error:', xhr.responseText);
                        Swal.fire('Error', 'Server response is not valid JSON. Check console for details.', 'error');
                    }
                },
                columns: [{
                        data: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return row.action || '-';
                        }
                    },
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            var name = row.nama_pelanggan || data || '-';
                            if (row.is_overdue && row.is_active !== 'Aktif') {
                                return '<span class="text-overdue" title="Pelanggan telat bayar">' + name + '</span>';
                            }
                            return name;
                        }
                    },
                    {
                        data: 'service_no',
                        render: function(data, type, row) {
                            var serviceNo = row.nomor_layanan || data || '-';
                            if (serviceNo === '' || serviceNo === '-') {
                                return '<span class="badge bg-secondary text-white d-inline-block text-center" style="min-width: 130px; font-size: 13px; padding: 8px 12px;">-</span>';
                            }
                            return '<span class="badge bg-primary text-white d-inline-block text-center" style="min-width: 130px; font-size: 13px; padding: 8px 12px;">' + serviceNo + '</span>';
                        }
                    },
                    {
                        data: 'pppoe_username',
                        render: function(data, type, row) {
                            if (!row.pppoe_username || row.pppoe_username === '-') {
                                return '<span class="text-muted">-</span>';
                            }
                            return '<span class="text-primary">' + row.pppoe_username + '</span>';
                        }
                    },
                    {
                        data: 'tgl_tempo',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!row.tgl_tempo) return '<span class="text-muted">-</span>';
                            var tempoDate = new Date(row.tgl_tempo);
                            var currentDate = new Date();
                            var year = tempoDate.getFullYear();
                            var month = String(tempoDate.getMonth() + 1).padStart(2, '0');
                            var day = String(tempoDate.getDate()).padStart(2, '0');
                            var hours = String(tempoDate.getHours()).padStart(2, '0');
                            var minutes = String(tempoDate.getMinutes()).padStart(2, '0');
                            var seconds = String(tempoDate.getSeconds()).padStart(2, '0');
                            var formattedDate = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
                            if (tempoDate < currentDate && row.is_active !== 'Aktif') {
                                return '<span class="text-danger fw-bold" title="Sudah melewati jatuh tempo">' + formattedDate + '</span>';
                            } else {
                                return '<span class="text-dark" title="Tanggal jatuh tempo">' + formattedDate + '</span>';
                            }
                        }
                    },
                    {
                        data: 'phone_number',
                        render: function(data, type, row) {
                            return row.phone_number || data || '-';
                        }
                    },
                    {
                        data: 'package',
                        render: function(data, type, row) {
                            if (window.debugMode) {
                                console.log('Package data for row:', {
                                    package: row.package,
                                    data: data,
                                    row: row
                                });
                            }
                            return row.package || data || '-';
                        }
                    },
                    {
                        data: 'price',
                        render: function(data, type, row) {
                            return row.price || '-';
                        }
                    },
                    {
                        data: 'subscription_method',
                        className: 'text-center',
                        render: function(data, type, row) {
                            var method = row.subscription_method || data || '-';
                            if (method === '-' || method === '' || method === null) {
                                return '<span class="text-muted">-</span>';
                            }
                            if (method.toLowerCase() === 'prepaid' || method.toLowerCase() === 'prabayar') {
                                return '<span class="text-info fw-bold">PRABAYAR</span>';
                            } else if (method.toLowerCase() === 'postpaid' || method.toLowerCase() === 'pascabayar') {
                                return '<span class="text-primary fw-bold">PASCABAYAR</span>';
                            } else if (method.toLowerCase() === 'online') {
                                return '<span class="text-success fw-bold">ONLINE</span>';
                            } else {
                                return '<span class="text-warning fw-bold">' + method.toUpperCase() + '</span>';
                            }
                        }
                    },
                    {
                        data: 'is_active',
                        className: 'text-center',
                        render: function(data, type, row) {
                            var isOverdue = row.is_overdue || false;
                            if (isOverdue && row.is_active !== 'Aktif') {
                                return '<span class="badge bg-danger text-white fw-bold" title="Pelanggan telat bayar - melewati jatuh tempo"><i class="bx bx-x-circle me-1"></i>TIDAK AKTIF</span>';
                            } else if (row.is_active === 'Aktif') {
                                return '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>' + row.is_active + '</span>';
                            } else if (row.is_active === 'Tidak Aktif') {
                                return '<span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i>' + row.is_active + '</span>';
                            } else if (row.is_active === 'Isolir') {
                                return '<span class="badge bg-warning text-dark"><i class="bx bx-lock me-1"></i>' + row.is_active + '</span>';
                            } else {
                                return row.is_active || '-';
                            }
                        }
                    }
                ],
                rowCallback: function(row, data, index) {
                    if (data.is_overdue && data.is_active !== 'Aktif') {
                        $(row).addClass('customer-overdue');
                    }
                }
            });
        }

        if (typeof table !== 'undefined' && table) {
            // Target input bawaan DataTables dengan improved debouncing
            $('#searchTableList').on('keyup', function() {
                clearTimeout(debounceSearch);
                const searchTerm = $(this).val();

                debounceSearch = setTimeout(() => {
                    table.draw();
                }, 300); // Reduced delay untuk responsiveness yang lebih baik
            });

            // Add real-time feedback untuk search
            $('#searchTableList').on('input', function() {
                const searchTerm = $(this).val();
                if (searchTerm.length === 0) {
                    clearTimeout(debounceSearch);
                    table.draw();
                }
            });

            table.on('draw.dt', function() {
                $("#checkAll").prop('checked', false);
            });

            // Handle window resize events to ensure proper table rendering
            $(window).on('resize', function() {
                // Adjust table layout when window resizes
                if (table) {
                    table.columns.adjust().draw();
                }
            });
        }
        var idDelete = [];

        $('#deleteSelected').on('click', function() {
            $('.deleteCheckbox').each(function(i, chk) {
                if (chk.checked) {
                    idDelete.push($(this).val());
                }
            });
            // console.log(idDelete);
            if (idDelete.length > 0) {
                $('#deleteModal').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'tidak ada data yang di pilih!',
                });
            }
        });

        $('#okButton').on('click', function(e) {
            e.preventDefault();
            if (idDelete.length > 0) {
                $(this).html('<i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Menghapus..');
                $('#okButton').prop('disabled', true);
                var url = "<?= site_url('customers/delete') ?>";

                // Prepare data with CSRF token dan _method spoofing
                var deleteData = {
                    'id': idDelete,
                    '_method': 'DELETE',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                };

                $.ajax({
                    data: deleteData,
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#deleteModal').modal('hide');
                        table.draw();
                        $('#okButton').html("Hapus");
                        $('#okButton').prop('disabled', false);

                        if (data.csrfName && data.csrfHash) {
                            $('meta[name="csrf-name"]').attr('content', data.csrfName);
                            $('meta[name="csrf-token"]').attr('content', data.csrfHash);
                        }
                        showToastMessage(data.status, data.message, data.title || (data.status === 'success' ? 'Berhasil' : 'Error'));
                    },
                    error: function(xhr, status, error) {
                        $('#okButton').html("Hapus");
                        $('#okButton').prop('disabled', false);

                        console.log('Bulk delete error:', xhr.responseText);
                        var errorMessage = 'Gagal menghapus data. Silakan coba lagi.';

                        // Try to parse error response
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                var errorData = JSON.parse(xhr.responseText);
                                if (errorData.message) {
                                    errorMessage = errorData.message;
                                }
                            } catch (e) {
                                if (xhr.statusText && xhr.statusText !== 'error') {
                                    errorMessage = xhr.statusText;
                                }
                            }
                        }

                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }
        });

        $('body').on('click', '.deleteData', function() {
            var id = $(this).data("id");
            Swal.fire({
                title: "Apa kamu yakin?",
                text: "Anda tidak akan dapat mengembalikan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Ya, hapus saja."
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "<?= site_url('customers') ?>/:id";
                    url = url.replace(':id', id);

                    // Prepare data with CSRF token dan _method spoofing
                    var deleteData = {
                        '_method': 'DELETE',
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    };

                    $.ajax({
                        data: deleteData,
                        url: url,
                        type: "POST",
                        dataType: 'json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', '<?= csrf_hash() ?>');
                        },
                        success: function(data) {
                            table.draw();
                            if (data.status === 'success') {
                                showToastMessage('success', data.message, data.title || 'Berhasil');
                            } else {
                                showToastMessage('error', data.message || 'Terjadi kesalahan', data.title || 'Error');
                            }
                            if (data.csrfName && data.csrfHash) {
                                $('meta[name="csrf-name"]').attr('content', data.csrfName);
                                $('meta[name="csrf-token"]').attr('content', data.csrfHash);
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.log('Delete error:', xhr.responseText);
                            var errorMessage = 'Gagal menghapus data. Silakan coba lagi.';

                            // Try to parse error response
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    var errorData = JSON.parse(xhr.responseText);
                                    if (errorData.message) {
                                        errorMessage = errorData.message;
                                    }
                                } catch (e) {
                                    // If not JSON, use status text or default message
                                    if (xhr.statusText && xhr.statusText !== 'error') {
                                        errorMessage = xhr.statusText;
                                    }
                                }
                            }

                            showToastMessage('error', errorMessage, 'Error');
                        }
                    });
                }
            });
        });

        $(function() {
            // Toggle import/export filters dihapus

            // Handler import dihapus

            $('#manualIsolated').off('click').on('click', function(e) {
                e.preventDefault();
                var url = "<?= site_url('customers/modal/manual-isolated') ?>";
                $.get({
                    url: url,
                    success: function(data) {
                        $('#globalModal .modal-content').html(data);
                        $('#globalModal').modal('show');
                    },
                    error: function(data) {
                        console.log('Error:', data);
                        showToastMessage('error', data.responseJSON?.message || 'Terjadi kesalahan', 'Error');
                    }
                });
            });

            $('#openIsolated').off('click').on('click', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                $.get({
                    url: url,
                    success: function(data) {
                        $('#globalModal .modal-content').html(data);
                        $('#globalModal').modal('show');
                    },
                    error: function(data) {
                        showToastMessage('error', data.responseJSON?.message || 'Terjadi kesalahan', 'Error');
                    }
                });
            });

            // Handle checkbox selection untuk bulk isolir
            $(document).on('change', '.customer-checkbox', function() {
                // Optional: Update UI or visual feedback here if needed
            });

            // Select all checkbox
            $(document).on('change', '#selectAll', function() {
                $('.customer-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Handle new customer card click to navigate to monthly stats
            $('#newCustomerCard').off('click').on('click', function(e) {
                e.preventDefault();
                window.location.href = "<?= site_url('customers/monthly-stats-page') ?>";
            });

            // Handle suspended customer card click to filter suspended customers
            // Handler filter suspended customer dihapus
        });
    });
</script>
<?= $this->endSection() ?>