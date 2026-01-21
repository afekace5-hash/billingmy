<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Master ODP &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .table-responsive {
        overflow-x: auto;
    }

    #map {
        height: 400px;
        width: 100%;
        border-radius: 4px;
    }

    .modal-header {
        background-color: #1abc9c;
        color: white;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
</style>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4">
                            <button type="button" id="createNewOdp" class="btn btn-success">
                                <i class="bx bx-plus"></i> Create New ODP
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle table-bordered odp_datatable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="80px">Action</th>
                                        <th>ID</th>
                                        <th>Branch</th>
                                        <th>Area</th>
                                        <th>ODP Name</th>
                                        <th>Customer Active</th>
                                        <th>Core</th>
                                        <th>Created at</th>
                                        <th>Last update</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="odpModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Form New ODP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="odpForm">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <input type="hidden" id="odp_id" name="odp_id">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="area_id" class="form-label">Area <span class="text-danger">*</span></label>
                                        <select class="form-select" id="area_id" name="area_id" required>
                                            <option value="">Select Area</option>
                                            <?php foreach ($areas as $area): ?>
                                                <option value="<?= $area['id'] ?>"><?= esc($area['area_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="odp_name" class="form-label">ODP Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="odp_name" name="odp_name" required placeholder="ODP Depan Pembensin Ciledug">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="core" class="form-label">Core <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="core" name="core" required placeholder="8 Core">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                                        <select class="form-select" id="district" name="district" required>
                                            <option value="">Select District</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="village" class="form-label">Village <span class="text-danger">*</span></label>
                                        <select class="form-select" id="village" name="village" required disabled>
                                            <option value="">Select Village</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="parent_odp" class="form-label">Parent ODP</label>
                                        <select class="form-select" id="parent_odp" name="parent_odp">
                                            <option value="">None</option>
                                        </select>
                                        <small class="text-muted">Select parent ODP if this ODP connects to existing ODP</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Jl H Oemar Said - ODP Depan Pembensin Ciledug"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Point ODP <span class="text-danger">*</span></label>
                                <p class="text-muted small mb-2">
                                    <i class="bx bx-info-circle"></i> Klik pada peta untuk menentukan lokasi ODP.
                                    Marker biru bisa di-drag untuk menyesuaikan posisi.
                                </p>
                                <div id="map"></div>
                                <small class="text-muted">
                                    Lat: <span id="displayLat" class="text-primary">-</span>,
                                    Lng: <span id="displayLng" class="text-primary">-</span>
                                </small>
                            </div>

                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal List Customers -->
        <div class="modal fade" id="customersModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">List Customers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Package</th>
                                        <th>Status</th>
                                        <th>Created at</th>
                                    </tr>
                                </thead>
                                <tbody id="customersTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // CSRF Token
        let csrfToken = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        // Kabupaten Batang ID
        const BATANG_CITY_ID = 3325;

        $(document).ready(function() {
            let currentMarker = null;
            let currentEditId = null;
            let defaultMarker = null;
            let existingOdpMarkers = [];
            let connectionLine = null;

            // Load districts on page load
            loadDistricts();

            // Load parent ODP when area changes
            $('#area_id').on('change', function() {
                const areaId = $(this).val();
                if (areaId) {
                    loadParentOdpOptions(areaId);
                    loadExistingOdpMarkers(areaId);
                } else {
                    $('#parent_odp').html('<option value="">None</option>');
                    clearExistingOdpMarkers();
                }
            });

            // Draw connection line when parent selected
            $('#parent_odp').on('change', function() {
                drawConnectionLine();
            });

            // Load villages when district changes
            $('#district').on('change', function() {
                const districtId = $(this).val();
                if (districtId) {
                    loadVillages(districtId);
                } else {
                    $('#village').html('<option value="">Select Village</option>').prop('disabled', true);
                }
            });

            // Function to load districts
            function loadDistricts() {
                $('#district').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: '<?= base_url('api/wilayah/districts/') ?>' + BATANG_CITY_ID,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const data = response.data || response;
                        $('#district').html('<option value="">Select District</option>').prop('disabled', false);

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(district) {
                                $('#district').append(
                                    $('<option></option>')
                                    .attr('value', district.id)
                                    .text(district.nama)
                                );
                            });
                        }
                    },
                    error: function() {
                        $('#district').html('<option value="">Error loading districts</option>').prop('disabled', false);
                        Swal.fire('Error', 'Failed to load districts', 'error');
                    }
                });
            }

            // Function to load villages
            function loadVillages(districtId) {
                $('#village').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: '<?= base_url('api/wilayah/villages/') ?>' + districtId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const data = response.data || response;
                        $('#village').html('<option value="">Select Village</option>').prop('disabled', false);

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(village) {
                                $('#village').append(
                                    $('<option></option>')
                                    .attr('value', village.id)
                                    .text(village.nama)
                                );
                            });
                        }
                    },
                    error: function() {
                        $('#village').html('<option value="">Error loading villages</option>').prop('disabled', false);
                        Swal.fire('Error', 'Failed to load villages', 'error');
                    }
                });
            }

            // Initialize map
            const map = L.map('map').setView([-6.9055, 109.7387], 13); // Kabupaten Batang, Jawa Tengah

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Red icon for existing ODPs
            const redIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Blue icon for selected point
            const blueIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Map click handler
            map.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(8);
                const lng = e.latlng.lng.toFixed(8);

                if (currentMarker) {
                    map.removeLayer(currentMarker);
                }

                currentMarker = L.marker(e.latlng, {
                    draggable: true,
                    icon: blueIcon
                }).addTo(map);

                $('#latitude').val(lat);
                $('#longitude').val(lng);
                $('#displayLat').text(lat);
                $('#displayLng').text(lng);

                currentMarker.on('dragend', function(e) {
                    const position = e.target.getLatLng();
                    const newLat = position.lat.toFixed(8);
                    const newLng = position.lng.toFixed(8);
                    $('#latitude').val(newLat);
                    $('#longitude').val(newLng);
                    $('#displayLat').text(newLat);
                    $('#displayLng').text(newLng);
                    drawConnectionLine();
                });

                drawConnectionLine();
            });

            // Initialize DataTable
            const table = $('.odp_datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= base_url('master/odp/data') ?>',
                    type: 'POST',
                    data: function(d) {
                        d[csrfToken] = csrfHash;
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id}">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        `;
                        }
                    },
                    {
                        data: 'id'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'area_name'
                    },
                    {
                        data: 'odp_name'
                    },
                    {
                        data: 'customer_active',
                        render: function(data, type, row) {
                            const count = data || 0;
                            const badgeClass = count > 0 ? 'bg-success' : 'bg-secondary';
                            return `<a href="javascript:void(0)" class="btn-view-customers" data-id="${row.id}" data-odp="${row.odp_name}" style="cursor:pointer;">
                                <span class="badge ${badgeClass}">${count} Customer${count !== 1 ? 's' : ''}</span>
                            </a>`;
                        }
                    },
                    {
                        data: 'core'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

            // Create new ODP
            $('#createNewOdp').click(function() {
                $('#odpForm')[0].reset();
                $('#odp_id').val('');
                currentEditId = null;

                if (currentMarker) {
                    map.removeLayer(currentMarker);
                    currentMarker = null;
                }

                $('#latitude').val('');
                $('#longitude').val('');
                $('#village').html('<option value="">Select Village</option>').prop('disabled', true);
                loadDistricts(); // Reload districts
                $('#modalTitle').text('Form New ODP');
                $('.modal-footer button[type="submit"]').text('Create');
                $('#odpModal').modal('show');

                // Fix map display after modal is shown
                setTimeout(function() {
                    map.invalidateSize();
                    map.setView([-6.9055, 109.7387], 13); // Center to Kabupaten Batang
                }, 300);
            });

            // Form submission
            $('#odpForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    area_id: $('#area_id').val(),
                    odp_name: $('#odp_name').val(),
                    district: $('#district').val(),
                    village: $('#village').val(),
                    parent_odp: $('#parent_odp').val(),
                    core: $('#core').val(),
                    latitude: $('#latitude').val(),
                    longitude: $('#longitude').val(),
                    address: $('#address').val()
                };

                const url = currentEditId ?
                    '<?= base_url('master/odp/update/') ?>' + currentEditId :
                    '<?= base_url('master/odp/create') ?>';

                formData[csrfToken] = csrfHash;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#odpModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message || 'Operation failed', 'error');
                        }
                        csrfHash = response.csrf_hash;
                    },
                    error: function(xhr) {
                        let message = 'Failed to save ODP';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            // Edit ODP
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                currentEditId = id;

                $.ajax({
                    url: '<?= base_url('master/odp/detail/') ?>' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            const data = response.data;
                            console.log('Data:', data);

                            $('#modalTitle').text('Edit ODP');
                            $('.modal-footer button[type="submit"]').text('Update');
                            $('#odpModal').modal('show');

                            // Set form values after modal is shown
                            setTimeout(function() {
                                // Fix map display
                                map.invalidateSize();

                                $('#odp_id').val(id);
                                $('#area_id').val(data.area_id);
                                $('#odp_name').val(data.odp_name);
                                $('#parent_odp').val(data.parent_odp || '');
                                $('#core').val(data.core);
                                $('#latitude').val(data.latitude);
                                $('#longitude').val(data.longitude);
                                $('#address').val(data.address);

                                console.log('Values set:', {
                                    area: $('#area_id').val(),
                                    odp_name: $('#odp_name').val(),
                                    core: $('#core').val(),
                                    latitude: data.latitude,
                                    longitude: data.longitude
                                });

                                // Load districts first, then set value
                                loadDistricts();
                                setTimeout(function() {
                                    $('#district').val(data.district);
                                    if (data.district) {
                                        loadVillages(data.district);
                                        setTimeout(function() {
                                            $('#village').val(data.village);
                                        }, 500);
                                    }
                                }, 500);

                                // Set marker if coordinates exist
                                if (data.latitude && data.longitude && parseFloat(data.latitude) !== 0 && parseFloat(data.longitude) !== 0) {
                                    const lat = parseFloat(data.latitude);
                                    const lng = parseFloat(data.longitude);

                                    console.log('Setting marker at:', lat, lng);

                                    if (currentMarker) {
                                        map.removeLayer(currentMarker);
                                    }

                                    currentMarker = L.marker([lat, lng], {
                                        draggable: true,
                                        icon: blueIcon
                                    }).addTo(map);
                                    map.setView([lat, lng], 16);

                                    currentMarker.on('dragend', function(e) {
                                        const position = e.target.getLatLng();
                                        $('#latitude').val(position.lat.toFixed(8));
                                        $('#longitude').val(position.lng.toFixed(8));
                                        $('#displayLat').text(position.lat.toFixed(8));
                                        $('#displayLng').text(position.lng.toFixed(8));
                                        drawConnectionLine();
                                    });

                                    drawConnectionLine();
                                } else {
                                    console.warn('⚠️ No valid coordinates for this ODP. Click map to set location.');
                                    // Center to default location if no coordinates
                                    map.setView([-6.9055, 109.7387], 13); // Kabupaten Batang
                                }
                            }, 300);
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load ODP data', 'error');
                    }
                });
            });

            // Delete ODP
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');

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
                            url: '<?= base_url('master/odp/delete/') ?>' + id,
                            type: 'DELETE',
                            data: {
                                [csrfToken]: csrfHash
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                                csrfHash = response.csrf_hash;
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete ODP', 'error');
                            }
                        });
                    }
                });
            });

            // View customers
            $(document).on('click', '.btn-view-customers', function() {
                const odpId = $(this).data('id');
                const odpName = $(this).data('odp');

                $('#customersModal .modal-title').text('List Customers - ' + odpName);
                $('#customersTableBody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                $('#customersModal').modal('show');

                $.ajax({
                    url: '<?= base_url('master/odp/customers/') ?>' + odpId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && Array.isArray(response.data)) {
                            if (response.data.length > 0) {
                                let html = '';
                                response.data.forEach(function(customer) {
                                    const statusBadge = customer.status === 'Active' ?
                                        '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-secondary">Inactive</span>';

                                    html += `
                                        <tr>
                                            <td>
                                                <a href="https://wa.me/${customer.phone}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="bx bxl-whatsapp"></i>
                                                </a>
                                            </td>
                                            <td>${customer.name}</td>
                                            <td>${customer.phone}</td>
                                            <td>${customer.package}</td>
                                            <td>${statusBadge}</td>
                                            <td>${customer.created_at}</td>
                                        </tr>
                                    `;
                                });
                                $('#customersTableBody').html(html);
                            } else {
                                $('#customersTableBody').html('<tr><td colspan="6" class="text-center">No customers found</td></tr>');
                            }
                        } else {
                            $('#customersTableBody').html('<tr><td colspan="6" class="text-center text-danger">Failed to load customers</td></tr>');
                        }
                    },
                    error: function() {
                        $('#customersTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading customers</td></tr>');
                    }
                });
            });

            // Reset modal
            $('#odpModal').on('hidden.bs.modal', function() {
                $('#odpForm')[0].reset();
                currentEditId = null;

                if (currentMarker) {
                    map.removeLayer(currentMarker);
                    currentMarker = null;
                }

                clearConnectionLine();
                clearExistingOdpMarkers();

                $('#latitude').val('');
                $('#longitude').val('');
                $('#modalTitle').text('Form New ODP');
                $('.modal-footer button[type="submit"]').text('Create');
            });

            // Function to load parent ODP options based on area
            function loadParentOdpOptions(areaId) {
                $.ajax({
                    url: '<?= base_url('master/odp/by-area/') ?>' + areaId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#parent_odp').html('<option value="">None</option>');

                        if (response.success && Array.isArray(response.data)) {
                            response.data.forEach(function(odp) {
                                // Exclude current ODP when editing
                                if (currentEditId != odp.id) {
                                    const option = $('<option></option>')
                                        .attr('value', odp.id)
                                        .attr('data-lat', odp.latitude)
                                        .attr('data-lng', odp.longitude)
                                        .text(odp.odp_name);
                                    $('#parent_odp').append(option);

                                    console.log('Added parent ODP:', odp.odp_name, 'Lat:', odp.latitude, 'Lng:', odp.longitude);
                                }
                            });
                        }
                    },
                    error: function() {
                        $('#parent_odp').html('<option value="">None</option>');
                        console.error('Failed to load parent ODP options');
                    }
                });
            }

            // Function to load existing ODP markers on map
            function loadExistingOdpMarkers(areaId) {
                clearExistingOdpMarkers();

                $.ajax({
                    url: '<?= base_url('master/odp/by-area/') ?>' + areaId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && Array.isArray(response.data)) {
                            response.data.forEach(function(odp) {
                                if (odp.latitude && odp.longitude) {
                                    const marker = L.marker([odp.latitude, odp.longitude], {
                                        icon: redIcon
                                    }).addTo(map);

                                    marker.bindPopup(` < b > $ {
                odp.odp_name
            } < /b><br>Active: ${odp.customer_active}<br>Core: ${odp.core}`);
                                    marker.odpData = {
                                        id: odp.id,
                                        name: odp.odp_name,
                                        lat: odp.latitude,
                                        lng: odp.longitude
                                    };

                                    existingOdpMarkers.push(marker);
                                }
                            });

                            // Fit map to show all markers
                            if (existingOdpMarkers.length > 0) {
                                const group = L.featureGroup(existingOdpMarkers);
                                map.fitBounds(group.getBounds().pad(0.1));
                            }
                        }
                    },
                    error: function() {
                        console.error('Failed to load existing ODPs');
                    }
                });
            }

            // Function to clear existing ODP markers
            function clearExistingOdpMarkers() {
                existingOdpMarkers.forEach(function(marker) {
                    map.removeLayer(marker);
                });
                existingOdpMarkers = [];
            }

            // Function to draw connection line
            function drawConnectionLine() {
                clearConnectionLine();

                const parentOdpId = $('#parent_odp').val();

                console.log('Drawing connection line, parentOdpId:', parentOdpId, 'currentMarker:', currentMarker);

                if (!parentOdpId || !currentMarker) {
                    console.log('No parent or no marker, skipping line');
                    return;
                }

                const parentOption = $('#parent_odp option:selected');
                const parentLat = parseFloat(parentOption.attr('data-lat'));
                const parentLng = parseFloat(parentOption.attr('data-lng'));

                console.log('Parent coordinates:', parentLat, parentLng);

                if (parentLat && parentLng && !isNaN(parentLat) && !isNaN(parentLng)) {
                    const currentPos = currentMarker.getLatLng();

                    console.log('Creating polyline from', [parentLat, parentLng], 'to', [currentPos.lat, currentPos.lng]);

                    connectionLine = L.polyline([
                        [parentLat, parentLng],
                        [currentPos.lat, currentPos.lng]
                    ], {
                        color: 'black',
                        dashArray: '5, 10',
                        weight: 2
                    }).addTo(map);

                    console.log('Polyline created successfully');
                } else {
                    console.log('Invalid parent coordinates');
                }
            }

            // Function to clear connection line
            function clearConnectionLine() {
                if (connectionLine) {
                    map.removeLayer(connectionLine);
                    connectionLine = null;
                }
            }
        });
    </script>

    <?= $this->endSection() ?>