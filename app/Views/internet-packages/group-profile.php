<?= $this->extend('layout/default') ?>

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
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                        <div>
                            <h4 class="card-title mb-0 text-white">
                                <i class="bx bx-network-chart me-2"></i>Group Profile Management
                            </h4>
                            <small class="text-white-50">Manage PPPoE group profiles and IP address pools</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addGroupProfileModal">
                                <i class="bx bx-plus me-1"></i> Add Group Profile
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info border-0 bg-light-info">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-info-circle me-2 text-info"></i>
                                        <div>
                                            <strong>Group Profiles</strong> define network configurations for customer groups including IP pools,
                                            address ranges, and router assignments for PPPoE connections.
                                            <br><small><i class="bx bx-sync"></i> <strong>Auto-Sync:</strong> Changes are automatically synchronized to MikroTik routers upon save.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Responsive Table with Card Layout for Mobile -->
                        <div class="d-none d-lg-block">
                            <!-- Desktop Table View -->
                            <div class="table-responsive">
                                <table id="groupProfileTable" class="table table-striped table-hover align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th style="min-width: 200px;">Group Name</th>
                                            <th class="text-center" style="width: 100px;">Type</th>
                                            <th class="text-center" style="width: 120px;">Parent Pool</th>
                                            <th class="text-center" style="width: 110px;">Module</th>
                                            <th class="text-center" style="width: 140px;">Local Address</th>
                                            <th class="text-center" style="width: 140px;">First Address</th>
                                            <th class="text-center" style="width: 140px;">Last Address</th>
                                            <th class="text-center" style="width: 150px;">Router [NAS]</th>
                                            <th class="text-center" style="width: 100px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-lg-none" id="mobileCardView">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" id="mobileSearch" class="form-control" placeholder="Search group profiles...">
                                    </div>
                                </div>
                            </div>
                            <div id="mobileCards" class="row g-3">
                                <!-- Mobile cards will be populated here -->
                            </div>
                            <div class="text-center mt-3">
                                <button id="loadMoreBtn" class="btn btn-outline-primary" style="display: none;">
                                    <i class="bx bx-plus"></i> Load More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Group Profile Modal -->
<div class="modal fade" id="addGroupProfileModal" tabindex="-1" aria-labelledby="addGroupProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGroupProfileModalLabel">Add Group Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addGroupProfileForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Profile Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <small class="form-text text-muted">e.g., PLATINUM PLUS Up to 15 MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="data_owner" class="form-label">Router [NAS]</label>
                                <select class="form-select" id="data_owner" name="data_owner">
                                    <option value="">- Select Router -</option>
                                    <!-- Router options will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="router_type" class="form-label">Router/Type</label>
                                <select class="form-select" id="router_type" name="router_type">
                                    <option value="PPP">PPP</option>
                                    <option value="DHCP">DHCP</option>
                                    <option value="Static">Static</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="ip_pool_module" class="form-label">IP Pool Module</label>
                                <select class="form-select" id="ip_pool_module" name="ip_pool_module">
                                    <option value="GROUP ONLY ( FOR HOTSPOT ONLY )">GROUP ONLY ( FOR HOTSPOT ONLY )</option>
                                    <option value="MikroTik IP Pool ( Not Global )">MikroTik IP Pool ( Not Global )</option>
                                    <option value="Radius SQL-IP-POOL ( Global )" selected>Radius SQL-IP-POOL ( Global )</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="parent_pool" class="form-label">Select Parent Pool (Optional)</label>
                                <select class="form-select" id="parent_pool" name="parent_pool">
                                    <option value="">- Create new pool for this group -</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="local_address" class="form-label">Local Address</label>
                                <input type="text" class="form-control" id="local_address" name="local_address" value="172.16.1.1">
                                <small class="form-text text-muted">DO NOT INCLUDE IN IP POOL NAME</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ip_range_start" class="form-label">IP Range IP Start</label>
                                <input type="text" class="form-control" id="ip_range_start" name="ip_range_start" value="172.16.1.2">
                                <small class="form-text text-muted">START WITH SECOND IP ADDRESS (X.X.X.2)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ip_range_end" class="form-label">IP Range IP End</label>
                                <input type="text" class="form-control" id="ip_range_end" name="ip_range_end" value="172.16.1.254">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="dns_server" class="form-label">DNS Server</label>
                                <input type="text" class="form-control" id="dns_server" name="dns_server" value="8.8.8.8,8.8.4.4">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="parent_queue" class="form-label">Parent Queue - Optional</label>
                                <input type="text" class="form-control" id="parent_queue" name="parent_queue" placeholder="mikrotik simple queue parent name">
                                <div class="mt-2">
                                    <small class="form-text text-muted">
                                        • LEAVE EMPTY IF YOU DON'T WANT TO CHANGE THE CURRENT PARENT QUEUE<br>
                                        • TO UPDATE OR DELETE A CURRENT PARENT QUEUE<br>
                                        • TYPE NEW PARENT QUEUE NAME TO CHANGE THE CURRENT PARENT QUEUE<br>
                                        • CHECK THE LOWERCASE OR UPPERCASE<br>
                                        • DO NOT USE SYMBOLS/SPECIAL CHARACTERS FOR THE MIKROTIK QUEUE NAME
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Note:</strong>
                        <ul class="mb-0">
                            <li>If you change the range of the ip pool, user ip address in this group will be re-assigned</li>
                            <li>After changing range of the ip pool, all users in this group should be disconnected to get new ip address</li>
                            <li>In this situation, not all users can be disconnected, so disconnect them through mikrotik</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Group Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Group Profile Modal -->
<div class="modal fade" id="editGroupProfileModal" tabindex="-1" aria-labelledby="editGroupProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGroupProfileModalLabel">Edit Group Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGroupProfileForm">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_group_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Profile Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_data_owner" class="form-label">Router [NAS]</label>
                                <select class="form-select" id="edit_data_owner" name="data_owner">
                                    <option value="">- Select Router -</option>
                                    <!-- Router options will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_router_type" class="form-label">Router/Type</label>
                                <select class="form-select" id="edit_router_type" name="router_type">
                                    <option value="PPP">PPP</option>
                                    <option value="DHCP">DHCP</option>
                                    <option value="Static">Static</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_ip_pool_module" class="form-label">IP Pool Module</label>
                                <select class="form-select" id="edit_ip_pool_module" name="ip_pool_module">
                                    <option value="GROUP ONLY ( FOR HOTSPOT ONLY )">GROUP ONLY ( FOR HOTSPOT ONLY )</option>
                                    <option value="MikroTik IP Pool ( Not Global )">MikroTik IP Pool ( Not Global )</option>
                                    <option value="Radius SQL-IP-POOL ( Global )">Radius SQL-IP-POOL ( Global )</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_parent_pool" class="form-label">Select Parent Pool (Optional)</label>
                                <select class="form-select" id="edit_parent_pool" name="parent_pool">
                                    <option value="">- Create new pool for this group -</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_local_address" class="form-label">Local Address</label>
                                <input type="text" class="form-control" id="edit_local_address" name="local_address">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_ip_range_start" class="form-label">IP Range IP Start</label>
                                <input type="text" class="form-control" id="edit_ip_range_start" name="ip_range_start">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_ip_range_end" class="form-label">IP Range IP End</label>
                                <input type="text" class="form-control" id="edit_ip_range_end" name="ip_range_end">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_dns_server" class="form-label">DNS Server</label>
                                <input type="text" class="form-control" id="edit_dns_server" name="dns_server">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Group Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // CSRF token configuration
        const csrfName = $('meta[name="csrf-name"]').attr('content');
        const csrfHash = $('meta[name="csrf-token"]').attr('content');

        // Setup AJAX defaults
        $.ajaxSetup({
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (csrfHash) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfHash);
                }
            }
        });

        // Responsive CSS for modern table design
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                /* Modern responsive table design */
                #groupProfileTable {
                    font-size: 14px;
                    border-collapse: separate;
                    border-spacing: 0;
                }
                
                #groupProfileTable thead th {
                    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
                    color: white;
                    font-weight: 600;
                    font-size: 13px;
                    padding: 12px 8px;
                    border: none;
                    text-align: center;
                    white-space: nowrap;
                }
                
                #groupProfileTable tbody td {
                    padding: 10px 8px;
                    font-size: 13px;
                    border-bottom: 1px solid #e9ecef;
                    vertical-align: middle;
                }
                
                #groupProfileTable tbody tr:hover {
                    background-color: #f8f9fa;
                    transition: background-color 0.2s ease;
                }
                
                /* Mobile card design */
                .group-profile-card {
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    padding: 16px;
                    background: white;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    transition: all 0.2s ease;
                }
                
                .group-profile-card:hover {
                    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
                    transform: translateY(-2px);
                }
                
                .group-profile-card .card-title {
                    font-size: 16px;
                    font-weight: 600;
                    color: #495057;
                    margin-bottom: 12px;
                }
                
                .group-profile-card .info-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 8px;
                    padding: 4px 0;
                }
                
                .group-profile-card .info-label {
                    font-size: 12px;
                    color: #6c757d;
                    font-weight: 500;
                    min-width: 80px;
                }
                
                .group-profile-card .info-value {
                    font-size: 13px;
                    color: #495057;
                    text-align: right;
                    font-family: monospace;
                }
                
                .badge {
                    font-size: 11px;
                    padding: 4px 8px;
                    font-weight: 500;
                }
                
                .btn-group .btn {
                    padding: 6px 12px;
                    font-size: 12px;
                    border-radius: 4px;
                    margin: 0 2px;
                }
                
                /* Custom scrollbar */
                .table-responsive::-webkit-scrollbar {
                    height: 6px;
                }
                
                .table-responsive::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 3px;
                }
                
                .table-responsive::-webkit-scrollbar-thumb {
                    background: #c1c1c1;
                    border-radius: 3px;
                }
                
                .table-responsive::-webkit-scrollbar-thumb:hover {
                    background: #a8a8a8;
                }
                
                /* Responsive adjustments */
                @media (max-width: 991.98px) {
                    .table-responsive {
                        display: none !important;
                    }
                }
                
                @media (min-width: 992px) {
                    #mobileCardView {
                        display: none !important;
                    }
                }
            `)
            .appendTo('head');

        let allData = [];
        let displayedItems = 0;
        const itemsPerPage = 10; // Initialize DataTable for desktop
        var table = initStandardDataTable('#groupProfileTable', {
            ajax: {
                url: '<?= site_url('internet-packages/group-profile/data') ?>',
                type: 'GET',
                dataSrc: function(json) {
                    allData = json.data || [];
                    updateMobileView();
                    return json.data || [];
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    responsivePriority: 1,
                    render: function(data, type, row) {
                        return '<input type="checkbox" class="form-check-input row-select" value="' + row.id + '">';
                    }
                },
                {
                    data: 'name',
                    responsivePriority: 2,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        if (data.length > 25) {
                            return '<span title="' + data + '" class="text-truncate d-inline-block" style="max-width: 180px;">' + data + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'router_type',
                    className: 'text-center',
                    responsivePriority: 3,
                    render: function(data, type, row) {
                        const routerType = data || 'PPP';
                        const badgeClass = routerType === 'PPP' ? 'bg-primary' :
                            routerType === 'DHCP' ? 'bg-success' : 'bg-info';
                        return '<span class="badge ' + badgeClass + '">' + routerType + '</span>';
                    }
                },
                {
                    data: 'parent_pool',
                    className: 'text-center',
                    responsivePriority: 5,
                    render: function(data, type, row) {
                        return data ? '<span class="badge bg-secondary">' + data + '</span>' :
                            '<span class="text-muted">NONE</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    responsivePriority: 6,
                    render: function(data, type, row) {
                        return '<small class="text-primary">sql-ippool</small>';
                    }
                },
                {
                    data: 'local_address',
                    className: 'text-center',
                    responsivePriority: 7,
                    render: function(data, type, row) {
                        return data ? '<code>' + data + '</code>' : '<span class="text-muted">172.16.1.1</span>';
                    }
                },
                {
                    data: 'ip_range_start',
                    className: 'text-center',
                    responsivePriority: 8,
                    render: function(data, type, row) {
                        return data ? '<code>' + data + '</code>' : '<span class="text-muted">172.16.1.2</span>';
                    }
                },
                {
                    data: 'ip_range_end',
                    className: 'text-center',
                    responsivePriority: 9,
                    render: function(data, type, row) {
                        return data ? '<code>' + data + '</code>' : '<span class="text-muted">172.16.1.254</span>';
                    }
                },
                {
                    data: 'routers',
                    className: 'text-center',
                    responsivePriority: 4,
                    render: function(data, type, row) {
                        if (data && data !== 'No router selected') {
                            return '<span class="badge bg-success">' +
                                (data.length > 15 ? data.substring(0, 15) + '...' : data) + '</span>';
                        }
                        return '<span class="badge bg-warning text-dark">No router</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    responsivePriority: 1,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                        data-id="${row.id}" title="Edit" data-bs-toggle="tooltip">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success verify-btn" 
                                        data-id="${row.id}" data-name="${row.name}" title="Verify MikroTik Sync" data-bs-toggle="tooltip">
                                    <i class="bx bx-check-circle"></i>
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
            order: [
                [1, 'asc']
            ],
            language: {
                search: "Search Group Profiles:",
                lengthMenu: "Show _MENU_ group profiles",
                info: "Showing _START_ to _END_ of _TOTAL_ group profiles",
                infoEmpty: "No group profiles found",
                zeroRecords: "No matching group profiles found",
                emptyTable: "No group profiles available"
            }
        });

        // Mobile view functions
        function updateMobileView(searchTerm = '') {
            const filteredData = allData.filter(item => {
                if (!searchTerm) return true;
                const search = searchTerm.toLowerCase();
                return (item.name && item.name.toLowerCase().includes(search)) ||
                    (item.router_type && item.router_type.toLowerCase().includes(search)) ||
                    (item.local_address && item.local_address.toLowerCase().includes(search));
            });

            displayedItems = 0;
            $('#mobileCards').empty();
            loadMoreItems(filteredData);
        }

        function loadMoreItems(data) {
            const endIndex = Math.min(displayedItems + itemsPerPage, data.length);
            for (let i = displayedItems; i < endIndex; i++) {
                const item = data[i];
                const card = createMobileCard(item);
                $('#mobileCards').append(card);
            }
            displayedItems = endIndex;

            // Show/hide load more button
            if (displayedItems < data.length) {
                $('#loadMoreBtn').show();
            } else {
                $('#loadMoreBtn').hide();
            }
        }

        function createMobileCard(item) {
            const routerType = item.router_type || 'PPP';
            const badgeClass = routerType === 'PPP' ? 'bg-primary' :
                routerType === 'DHCP' ? 'bg-success' : 'bg-info';

            return `
                <div class="col-12">
                    <div class="group-profile-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="card-title">${item.name || '-'}</div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                        data-id="${item.id}">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success verify-btn" 
                                        data-id="${item.id}" data-name="${item.name}">
                                    <i class="bx bx-check-circle"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                        data-id="${item.id}">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Type:</span>
                            <span class="badge ${badgeClass}">${routerType}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Pool:</span>
                            <span class="info-value">${item.parent_pool || 'NONE'}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Local:</span>
                            <span class="info-value"><code>${item.local_address || ''}</code></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Range:</span>
                            <span class="info-value">
                                <code>${item.ip_range_start || ''}</code> - 
                                <code>${item.ip_range_end || ''}</code>
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Router:</span>
                            <span class="badge ${item.routers && item.routers !== 'No router selected' ? 'bg-success' : 'bg-warning text-dark'}">
                                ${item.routers && item.routers !== 'No router selected' ? item.routers : 'No router'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }

        // Mobile search functionality
        $('#mobileSearch').on('input', function() {
            const searchTerm = $(this).val();
            updateMobileView(searchTerm);
        });

        // Load more button
        $('#loadMoreBtn').on('click', function() {
            const searchTerm = $('#mobileSearch').val();
            const filteredData = allData.filter(item => {
                if (!searchTerm) return true;
                const search = searchTerm.toLowerCase();
                return (item.name && item.name.toLowerCase().includes(search)) ||
                    (item.router_type && item.router_type.toLowerCase().includes(search)) ||
                    (item.local_address && item.local_address.toLowerCase().includes(search));
            });
            loadMoreItems(filteredData);
        });

        // Add Group Profile Form Submit - Enhanced dengan sistem baru
        $('#addGroupProfileForm').on('submit', function(e) {
            e.preventDefault();

            // Setup loading state dengan FormHandler pattern
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.attr('data-original-text', originalText);
            submitBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Creating...').prop('disabled', true);

            // Use PreloaderManager AJAX wrapper untuk konsistensi
            const ajaxOptions = {
                url: '<?= site_url('internet-packages/group-profile/create') ?>',
                type: 'POST',
                data: $(this).serialize(),
                showPreloader: false, // Modal form tidak perlu global preloader
                success: function(response) {
                    console.log('CREATE Response:', response);

                    if (response.success) {
                        // Hide modal
                        $('#addGroupProfileModal').modal('hide');

                        // Reset form
                        $('#addGroupProfileForm')[0].reset();

                        // Reload data
                        if (typeof table !== 'undefined' && table.ajax) {
                            table.ajax.reload();
                        }

                        // Reload mobile view data
                        $.ajax({
                            url: '<?= site_url('internet-packages/group-profile/data') ?>',
                            type: 'GET',
                            success: function(json) {
                                allData = json.data || [];
                                if (typeof updateMobileView === 'function') {
                                    updateMobileView($('#mobileSearch').val());
                                }
                            }
                        });

                        // Show success message
                        let message = response.message;
                        let icon = 'success';

                        if (response.warning) {
                            icon = 'warning';
                            message += '\n\nWarning: ' + response.warning;
                        }

                        if (response.mikrotik_sync) {
                            console.log('MikroTik sync response:', response.mikrotik_sync);
                            if (response.mikrotik_sync.success) {
                                message += '\n\n✅ Successfully synced to MikroTik routers';
                                if (response.mikrotik_sync.results && response.mikrotik_sync.results.length > 0) {
                                    message += '\n\nDetails:';
                                    response.mikrotik_sync.results.forEach(function(result) {
                                        const status = result.success ? '✅' : '❌';
                                        const duration = result.duration ? ` (${result.duration}s)` : '';
                                        message += '\n' + status + ' ' + result.router + ': ' + result.message + duration;
                                    });
                                }
                            } else {
                                message += '\n\n❌ Failed to sync to MikroTik: ' + (response.mikrotik_sync.message || 'Unknown error');
                                if (response.mikrotik_sync.results && response.mikrotik_sync.results.length > 0) {
                                    message += '\n\nDetails:';
                                    response.mikrotik_sync.results.forEach(function(result) {
                                        const status = result.success ? '✅' : '❌';
                                        const duration = result.duration ? ` (${result.duration}s)` : '';
                                        message += '\n' + status + ' ' + result.router + ': ' + result.message + duration;
                                    });
                                }
                            }
                        } else {
                            console.log('No mikrotik_sync in response');
                        }

                        Swal.fire('Success', message, icon);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    Swal.fire('Error', errorMessage, 'error');
                },
                complete: function() {
                    // Reset button state
                    submitBtn.html(originalText).prop('disabled', false);
                }
            };

            // Use PreloaderManager if available, otherwise use regular AJAX
            if (window.PreloaderManager) {
                window.PreloaderManager.ajaxWrapper(ajaxOptions);
            } else {
                $.ajax(ajaxOptions);
            }
        });
    });

    // Edit Group Profile Form Submit
    $('#editGroupProfileForm').on('submit', function(e) {
        e.preventDefault();

        var id = $('#edit_group_id').val();
        var formData = $(this).serializeArray();

        // Tambahkan CSRF token secara eksplisit
        formData.push({
            name: csrfName,
            value: csrfHash
        });

        console.log('Submitting edit form for ID:', id);
        console.log('Form data:', formData);

        if (!id) {
            Swal.fire('Error', 'No group profile ID found', 'error');
            return;
        }

        // Show loading state on submit button
        const submitBtn = $('#editGroupProfileForm button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Updating...').prop('disabled', true);

        $.ajax({
            url: '<?= site_url('internet-packages/group-profile/update/') ?>' + id,
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                console.log('UPDATE Response:', response);

                // Hide any preloaders immediately on success
                $('.preloader, #preloader').hide();

                if (response.success) {
                    $('#editGroupProfileModal').modal('hide');
                    $('#editGroupProfileForm')[0].reset();

                    // Wait for modal to be fully hidden before showing success message
                    $('#editGroupProfileModal').one('hidden.bs.modal', function() {
                        // Ensure preloader is hidden
                        $('.preloader, #preloader').hide();

                        // Reload both views
                        table.ajax.reload();

                        // Reload mobile view data
                        $.ajax({
                            url: '<?= site_url('internet-packages/group-profile/data') ?>',
                            type: 'GET',
                            success: function(json) {
                                allData = json.data || [];
                                updateMobileView($('#mobileSearch').val());
                            }
                        });

                        // Show detailed success message with MikroTik sync status
                        let message = response.message || 'Group profile updated successfully';
                        let icon = 'success';

                        if (response.warning) {
                            icon = 'warning';
                            message += '\n\nWarning: ' + response.warning;
                        }

                        if (response.mikrotik_sync) {
                            console.log('MikroTik sync response:', response.mikrotik_sync);
                            if (response.mikrotik_sync.success) {
                                message += '\n\n✅ Successfully synced to MikroTik routers';
                                if (response.mikrotik_sync.results && response.mikrotik_sync.results.length > 0) {
                                    message += '\n\nDetails:';
                                    response.mikrotik_sync.results.forEach(function(result) {
                                        const status = result.success ? '✅' : '❌';
                                        const duration = result.duration ? ` (${result.duration}s)` : '';
                                        message += '\n' + status + ' ' + result.router + ': ' + result.message + duration;
                                    });
                                }
                            } else {
                                message += '\n\n❌ Failed to sync to MikroTik: ' + (response.mikrotik_sync.message || 'Unknown error');
                                if (response.mikrotik_sync.results && response.mikrotik_sync.results.length > 0) {
                                    message += '\n\nDetails:';
                                    response.mikrotik_sync.results.forEach(function(result) {
                                        const status = result.success ? '✅' : '❌';
                                        const duration = result.duration ? ` (${result.duration}s)` : '';
                                        message += '\n' + status + ' ' + result.router + ': ' + result.message + duration;
                                    });
                                }
                            }
                        } else {
                            console.log('No mikrotik_sync in response');
                        }

                        Swal.fire('Success', message, icon);
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to update group profile', 'error');
                }
            },
            error: function(xhr) {
                console.error('Edit submission error:', xhr);

                // Hide preloader on error
                $('.preloader, #preloader').hide();

                let errorMessage = 'An error occurred while updating';
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    console.error('Error parsing response:', e);
                    errorMessage = 'Failed to update group profile';
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                // Restore button state and ensure preloader is hidden
                submitBtn.html(originalText).prop('disabled', false);
                $('.preloader, #preloader').hide();
            }
        });
    });

    // Event handlers for both desktop and mobile
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');

        // Show loading state
        $(this).prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

        // Get group profile data
        $.ajax({
            url: '<?= site_url('internet-packages/group-profile/') ?>' + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var data = response.data;

                    // Populate form fields
                    $('#edit_group_id').val(data.id);
                    $('#edit_name').val(data.name || '');
                    $('#edit_router_type').val(data.router_type || 'PPP');
                    $('#edit_ip_pool_module').val(data.ip_pool_module || 'Radius SQL-IP-POOL ( Global )');
                    $('#edit_parent_pool').val(data.parent_pool || '');
                    $('#edit_local_address').val(data.local_address || '172.16.1.1');
                    $('#edit_ip_range_start').val(data.ip_range_start || '172.16.1.2');
                    $('#edit_ip_range_end').val(data.ip_range_end || '172.16.1.254');
                    $('#edit_dns_server').val(data.dns_server || '8.8.8.8,8.8.4.4');

                    // Store the data_owner value to restore after router loading
                    window.editDataOwnerValue = data.data_owner || '';

                    // Show modal
                    $('#editGroupProfileModal').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'Failed to load group profile data', 'error');
                }
            },
            error: function(xhr) {
                console.log('Edit error:', xhr);
                try {
                    var response = JSON.parse(xhr.responseText);
                    Swal.fire('Error', response.message || 'An error occurred while loading data', 'error');
                } catch (e) {
                    Swal.fire('Error', 'Failed to load group profile data', 'error');
                }
            },
            complete: function() {
                // Restore button state
                $('.edit-btn[data-id="' + id + '"]').prop('disabled', false).html('<i class="bx bx-edit"></i>');
            }
        });
    });

    // Delete handler for both desktop and mobile
    $(document).on('click', '.delete-btn', function() {
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
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the group profile.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '<?= site_url('internet-packages/group-profile/') ?>' + id,
                    type: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        [csrfName]: csrfHash
                    },
                    success: function(response) {
                        console.log('Delete response:', response);
                        if (response.success) {
                            // Reload both desktop and mobile views
                            table.ajax.reload();

                            // Reload mobile view data
                            $.ajax({
                                url: '<?= site_url('internet-packages/group-profile/data') ?>',
                                type: 'GET',
                                success: function(json) {
                                    allData = json.data || [];
                                    updateMobileView($('#mobileSearch').val());
                                }
                            });

                            Swal.fire('Deleted!', response.message || 'Group profile deleted successfully', 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete group profile', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', xhr, status, error);

                        let errorMessage = 'An error occurred while deleting';
                        try {
                            if (xhr.responseJSON) {
                                errorMessage = xhr.responseJSON.message || errorMessage;
                            } else if (xhr.responseText) {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || errorMessage;
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }

                        // Handle specific error cases
                        if (xhr.status === 401 || xhr.status === 403) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Session Expired',
                                text: 'Your session has expired. Please login again.',
                                confirmButtonText: 'Login',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = '<?= site_url('auth/login') ?>';
                            });
                        } else if (xhr.status === 419) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'CSRF Token Expired',
                                text: 'Security token expired. Please refresh the page and try again.',
                                confirmButtonText: 'Refresh Page',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', errorMessage, 'error');
                        }
                    }
                });
            }
        });
    });

    // Select All functionality (desktop only)
    $('#selectAll').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.row-select').prop('checked', isChecked);
    });

    // Individual checkbox change (desktop only)
    $('#groupProfileTable').on('change', '.row-select', function() {
        var totalRows = $('.row-select').length;
        var checkedRows = $('.row-select:checked').length;

        if (checkedRows === totalRows) {
            $('#selectAll').prop('checked', true).prop('indeterminate', false);
        } else if (checkedRows === 0) {
            $('#selectAll').prop('checked', false).prop('indeterminate', false);
        } else {
            $('#selectAll').prop('checked', false).prop('indeterminate', true);
        }
    });

    // Load routers for dropdown
    function loadRouters() {
        console.log('Loading routers...');

        // Clear existing options first
        $('#data_owner').empty().append('<option value="">- Loading... -</option>');

        $.ajax({
            url: '<?= site_url('routers/data') ?>',
            type: 'GET',
            dataType: 'json',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                console.log('Router data response:', response);

                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $('#data_owner').empty().append('<option value="">- Select Router -</option>');

                    response.data.forEach(function(router) {
                        const label = router.label || (router.nama + ' (' + router.ip_router + ')');
                        const option = `<option value="${router.nama}">${label}</option>`;
                        $('#data_owner').append(option);
                    });

                    console.log('Routers loaded successfully:', response.data.length, 'routers');
                } else {
                    console.warn('No router data available or bad response');
                    $('#data_owner').empty().append('<option value="">- No routers available -</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load routers:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });

                // Try to parse error response
                let errorMessage = 'Failed to load routers';
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    }
                } catch (e) {
                    // Ignore JSON parse error
                }

                // Show error and fallback to default options
                $('#data_owner').empty().append('<option value="">- Error loading routers -</option>');

                // Add some default options for testing
                const defaultRouters = [{
                        nama: 'Router Pusat Jakarta',
                        ip_router: '192.168.1.1',
                        label: 'Router Pusat Jakarta (192.168.1.1)'
                    },
                    {
                        nama: 'Router Cabang Bandung',
                        ip_router: '192.168.2.1',
                        label: 'Router Cabang Bandung (192.168.2.1)'
                    },
                    {
                        nama: 'Router Remote Tunnel',
                        ip_router: 'id-14.hostddns.us:8211',
                        label: 'Router Remote Tunnel (id-14.hostddns.us:8211)'
                    }
                ];

                defaultRouters.forEach(function(router) {
                    const option = `<option value="${router.nama}">${router.label}</option>`;
                    $('#data_owner').append(option);
                });
                console.log('Added fallback router options');
            }
        });
    }

    // Load routers specifically for edit modal
    function loadRoutersForEdit() {
        console.log('Loading routers for edit modal...');

        // Clear existing options first
        $('#edit_data_owner').empty().append('<option value="">- Loading... -</option>');

        $.ajax({
            url: '<?= site_url('routers/data') ?>',
            type: 'GET',
            dataType: 'json',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                console.log('Router data response for edit:', response);

                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $('#edit_data_owner').empty().append('<option value="">- Select Router -</option>');

                    response.data.forEach(function(router) {
                        const label = router.label || (router.nama + ' (' + router.ip_router + ')');
                        const option = `<option value="${router.nama}">${label}</option>`;
                        $('#edit_data_owner').append(option);
                    });

                    console.log('Routers loaded successfully for edit:', response.data.length, 'routers');

                    // Restore edit_data_owner value if it exists
                    if (window.editDataOwnerValue) {
                        $('#edit_data_owner').val(window.editDataOwnerValue);
                        console.log('Restored edit_data_owner value:', window.editDataOwnerValue);
                    }
                } else {
                    console.warn('No router data available or bad response for edit');
                    $('#edit_data_owner').empty().append('<option value="">- No routers available -</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load routers for edit:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });

                // Show error and fallback to default options
                $('#edit_data_owner').empty().append('<option value="">- Error loading routers -</option>');

                // Add some default options for testing
                const defaultRouters = [{
                        nama: 'Router Pusat Jakarta',
                        ip_router: '192.168.1.1',
                        label: 'Router Pusat Jakarta (192.168.1.1)'
                    },
                    {
                        nama: 'Router Cabang Bandung',
                        ip_router: '192.168.2.1',
                        label: 'Router Cabang Bandung (192.168.2.1)'
                    },
                    {
                        nama: 'Router Remote Tunnel',
                        ip_router: 'id-14.hostddns.us:8211',
                        label: 'Router Remote Tunnel (id-14.hostddns.us:8211)'
                    }
                ];

                defaultRouters.forEach(function(router) {
                    const option = `<option value="${router.nama}">${router.label}</option>`;
                    $('#edit_data_owner').append(option);
                });
                console.log('Added fallback router options for edit');

                // Restore edit_data_owner value if it exists
                if (window.editDataOwnerValue) {
                    $('#edit_data_owner').val(window.editDataOwnerValue);
                    console.log('Restored edit_data_owner value from fallback:', window.editDataOwnerValue);
                }
            }
        });
    }

    // Load routers when modals open
    $('#addGroupProfileModal').on('shown.bs.modal', function() {
        loadRouters();
    });

    $('#editGroupProfileModal').on('shown.bs.modal', function() {
        console.log('Edit modal opened, loading routers...');
        // Load routers and then restore the selected value
        loadRoutersForEdit();
    });

    // Modal hidden events
    $('#addGroupProfileModal').on('hidden.bs.modal', function() {
        $('#addGroupProfileForm')[0].reset();
    });

    $('#editGroupProfileModal').on('hidden.bs.modal', function() {
        $('#editGroupProfileForm')[0].reset();
        // Clear the stored value
        window.editDataOwnerValue = null;
        // Ensure preloader is hidden when modal is closed
        $('.preloader, #preloader').hide();
    });

    // Modal event handlers for preloader management
    $('#addGroupProfileModal').on('show.bs.modal', function() {
        // Hide preloader when modal is about to be shown
        $('.preloader, #preloader').hide();
    });

    $('#addGroupProfileModal').on('shown.bs.modal', function() {
        // Ensure preloader is hidden when modal is fully shown
        $('.preloader, #preloader').hide();
    });

    $('#addGroupProfileModal').on('hidden.bs.modal', function() {
        // Reset form and hide preloader when modal is closed
        $('#addGroupProfileForm')[0].reset();
        $('.preloader, #preloader').hide();
    });

    $('#editGroupProfileModal').on('show.bs.modal', function() {
        // Hide preloader when edit modal is about to be shown
        $('.preloader, #preloader').hide();
    });

    $('#editGroupProfileModal').on('shown.bs.modal', function() {
        // Ensure preloader is hidden when edit modal is fully shown
        $('.preloader, #preloader').hide();
    });

    // Global AJAX error handling improvement
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.log('AJAX Error:', {
            url: settings.url,
            status: xhr.status,
            error: error,
            responseText: xhr.responseText
        });

        // Always hide preloader on AJAX errors
        $('.preloader, #preloader').hide();

        // Handle specific error cases
        if (xhr.status === 403) {
            Swal.fire('Access Denied', 'Session may have expired. Please refresh and try again.', 'error');
        } else if (xhr.status === 500) {
            Swal.fire('Server Error', 'An internal server error occurred. Please try again later.', 'error');
        }
    });

    // Additional safety: Hide preloader after any SweetAlert is shown
    const originalSwalFire = Swal.fire;
    Swal.fire = function(...args) {
        // Hide preloader before showing any Swal dialog
        $('.preloader, #preloader').hide();
        return originalSwalFire.apply(this, args);
    };

    // Safety interval to ensure preloader doesn't get stuck
    setInterval(function() {
        // Check if there are any visible modals
        const hasVisibleModal = $('.modal:visible').length > 0;
        const hasActiveAjax = $.active > 0;

        // If no modals are visible and no AJAX requests are active, hide preloader
        if (!hasVisibleModal && !hasActiveAjax) {
            $('.preloader, #preloader').hide();
        }
    }, 2000); // Check every 2 seconds
</script>

<?= $this->endSection() ?>