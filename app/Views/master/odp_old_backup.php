<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Master ODP &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
        .odp_datatable {
            font-size: 11px;
        }

        .odp_datatable td {
            padding: 6px 4px;
            white-space: nowrap;
        }

        .odp_datatable th {
            padding: 8px 4px;
            font-size: 11px;
        }

        .odp_datatable .btn {
            padding: 3px 6px;
            font-size: 10px;
        }

        .badge {
            font-size: 9px;
            padding: 3px 6px;
        }
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    #map {
        height: 350px;
        width: 100%;
        border-radius: 4px;
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }

    .map-instructions {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Manage ODP</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                            <li class="breadcrumb-item active">ODP</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <a href="javascript:void(0)" id="createNewOdp" class="btn btn-success waves-effect btn-label waves-light">
                                    <i class="bx bx-plus label-icon"></i>
                                    Create New ODP
                                </a>
                                <a href="javascript:void(0)" id="filterBtn" class="btn btn-info waves-effect">
                                    <i class="bx bx-filter"></i>
                                    Filter
                                </a>
                            </div>
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

        <!-- Modal for Create/Edit ODP -->
        <div class="modal fade" id="odpModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHeading">Add New ODP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="odpForm" name="odpForm" class="form-horizontal">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div id="formErrors" class="alert alert-danger" style="display: none;"></div>

                            <input type="hidden" id="odp_id" name="odp_id">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="branch_id" class="col-form-label">Branch<span class="text-danger">*</span></label>
                                        <select class="form-select" id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            <?php foreach ($branches as $branch): ?>
                                                <option value="<?= $branch['id'] ?>"><?= esc($branch['branch_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span id="errorBranchId" class="invalid-feedback text-danger"></span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="area_id" class="col-form-label">Area<span class="text-danger">*</span></label>
                                        <select class="form-select" id="area_id" name="area_id" required>
                                            <option value="">Select Area</option>
                                        </select>
                                        <span id="errorAreaId" class="invalid-feedback text-danger"></span>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label for="odp_name" class="col-form-label">ODP Name<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="odp_name" name="odp_name" placeholder="ODP 02-XAZJ- DEPAN TOKO BUAH" required>
                                        <span id="errorOdpName" class="invalid-feedback text-danger"></span>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label for="core" class="col-form-label">Core<span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="core" name="core" placeholder="8" min="1" required>
                                        <small class="text-muted">Jumlah core tersedia</small>
                                        <span id="errorCore" class="invalid-feedback text-danger"></span>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label class="col-form-label">Select Location on Map</label>
                                        <div id="map"></div>
                                        <p class="map-instructions">
                                            <i class="bx bx-map-pin me-1"></i>
                                            Click on the map to set location, or drag the marker
                                        </p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="latitude" class="col-form-label">Latitude</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="longitude" class="col-form-label">Longitude</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="address" class="col-form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat lokasi ODP (opsional)"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="saveBtn" class="btn btn-success" value="create">
                                <i class="bx bx-save me-1"></i> Save changes
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let map;
        let marker;
        const defaultLat = -6.2088;
        const defaultLng = 106.8456;

        function initMap() {
            if (map) {
                map.remove();
            }

            map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            map.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    map.setView([position.coords.latitude, position.coords.longitude], 15);
                });
            }
        }

        function setMarker(lat, lng) {
            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            $('#latitude').val(lat.toFixed(8));
            $('#longitude').val(lng.toFixed(8));

            marker.on('dragend', function(e) {
                const position = e.target.getLatLng();
                $('#latitude').val(position.lat.toFixed(8));
                $('#longitude').val(position.lng.toFixed(8));
            });
        }

        function loadExistingMarker(lat, lng) {
            if (lat && lng) {
                map.setView([lat, lng], 15);
                setMarker(lat, lng);
            }
        }

        var table = $('.odp_datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('master/odp/data') ?>",
                type: "POST",
                data: function(d) {
                    d.<?= csrf_token() ?> = $('meta[name="csrf-token"]').attr('content');
                },
                error: function(xhr, error, code) {
                    console.log('Error:', xhr, error, code);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load ODP data.'
                    });
                }
            },
            columns: [{
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary editOdp" data-id="${data}" title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger deleteOdp" data-id="${data}" title="Delete">
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
                    data: 'branch_name',
                    render: function(data) {
                        return data ? `<span class="badge bg-info">${data}</span>` : '-';
                    }
                },
                {
                    data: 'area_name',
                    render: function(data) {
                        return data ? `<span class="badge bg-success">${data}</span>` : '-';
                    }
                },
                {
                    data: 'odp_name'
                },
                {
                    data: 'customer_active',
                    render: function(data) {
                        return `<span class="badge bg-primary">${data || 0} Customer</span>`;
                    }
                },
                {
                    data: 'core',
                    render: function(data) {
                        return `<span class="badge bg-secondary">${data || 0} Core</span>`;
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        if (data) {
                            let date = new Date(data);
                            return date.toLocaleDateString('id-ID', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            }) + ' ' + date.toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }
                        return '-';
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data) {
                        if (data) {
                            let date = new Date(data);
                            return date.toLocaleDateString('id-ID', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            }) + ' ' + date.toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }
                        return '-';
                    }
                }
            ],
            order: [
                [1, 'desc']
            ],
            responsive: true
        });

        // Load areas when branch is selected
        $('#branch_id').change(function() {
            var branchId = $(this).val();
            if (branchId) {
                $.get("<?= site_url('master/area/by-branch/') ?>" + branchId, function(data) {
                    if (data.success) {
                        $('#area_id').empty().append('<option value="">Select Area</option>');
                        $.each(data.data, function(key, area) {
                            $('#area_id').append('<option value="' + area.id + '">' + area.area_name + '</option>');
                        });
                    }
                });
            } else {
                $('#area_id').empty().append('<option value="">Select Area</option>');
            }
        });

        $('#createNewOdp').click(function() {
            $('#saveBtn').val("create");
            $('#odp_id').val('');
            $('#odpForm').trigger("reset");
            $('#modalHeading').html("Add New ODP");
            $('#odpModal').modal('show');
            $('#formErrors').hide();
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            $('#area_id').empty().append('<option value="">Select Branch First</option>');

            setTimeout(function() {
                initMap();
            }, 300);
        });

        $('body').on('click', '.editOdp', function() {
            var odp_id = $(this).data('id');
            $.get("<?= site_url('master/odp/detail/') ?>" + odp_id, function(data) {
                if (data.success) {
                    $('#modalHeading').html("Edit ODP");
                    $('#saveBtn').val("edit");
                    $('#odpModal').modal('show');
                    $('#odp_id').val(data.data.id);
                    $('#branch_id').val(data.data.branch_id).trigger('change');

                    setTimeout(function() {
                        $('#area_id').val(data.data.area_id);
                    }, 500);

                    $('#odp_name').val(data.data.odp_name);
                    $('#core').val(data.data.core);
                    $('#latitude').val(data.data.latitude);
                    $('#longitude').val(data.data.longitude);
                    $('#address').val(data.data.address);
                    $('#formErrors').hide();
                    $('.invalid-feedback').hide();
                    $('.form-control').removeClass('is-invalid');

                    setTimeout(function() {
                        initMap();
                        if (data.data.latitude && data.data.longitude) {
                            loadExistingMarker(parseFloat(data.data.latitude), parseFloat(data.data.longitude));
                        }
                    }, 300);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            });
        });

        $('body').on('click', '.deleteOdp', function() {
            var odp_id = $(this).data("id");
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
                        type: "DELETE",
                        url: "<?= site_url('master/odp/delete/') ?>" + odp_id,
                        success: function(data) {
                            if (data.success) {
                                table.draw();
                                Swal.fire('Deleted!', data.message, 'success');
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong!', 'error');
                        }
                    });
                }
            });
        });

        $('#odpForm').submit(function(e) {
            e.preventDefault();

            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            $('#formErrors').hide();

            var formData = new FormData(this);
            var actionType = $('#saveBtn').val();
            var url = actionType === "create" ?
                "<?= site_url('master/odp/create') ?>" :
                "<?= site_url('master/odp/update/') ?>" + $('#odp_id').val();

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.success) {
                        $('#odpForm').trigger("reset");
                        $('#odpModal').modal('hide');
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        if (data.errors) {
                            var errorHtml = '<ul class="mb-0">';
                            $.each(data.errors, function(key, value) {
                                errorHtml += '<li>' + value + '</li>';
                                var fieldId = key.replace(/_/g, '');
                                $('#error' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1)).show().text(value);
                                $('#' + key).addClass('is-invalid');
                            });
                            errorHtml += '</ul>';
                            $('#formErrors').html(errorHtml).show();
                        } else {
                            $('#formErrors').html('<p>' + data.message + '</p>').show();
                        }
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong!'
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>