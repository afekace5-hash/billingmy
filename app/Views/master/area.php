<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Master Area &mdash; Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Responsive table improvements */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Mobile responsive styles */
    @media (max-width: 768px) {
        .area_datatable {
            font-size: 11px;
        }

        .area_datatable td {
            padding: 6px 4px;
            white-space: nowrap;
        }

        .area_datatable th {
            padding: 8px 4px;
            font-size: 11px;
        }

        .area_datatable .btn {
            padding: 3px 6px;
            font-size: 10px;
        }

        .badge {
            font-size: 9px;
            padding: 3px 6px;
        }

        .btn-group .btn {
            padding: 2px 4px;
            font-size: 10px;
        }
    }

    @media (max-width: 576px) {
        .area_datatable {
            font-size: 10px;
        }

        .area_datatable td {
            padding: 4px 2px;
        }

        .area_datatable .btn {
            padding: 2px 4px;
            font-size: 9px;
        }
    }

    /* Button group styling */
    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* Map styling */
    #map {
        height: 400px;
        width: 100%;
        border-radius: 4px;
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }

    .coordinates-info {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .map-instructions {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Master Area</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                            <li class="breadcrumb-item active">Master Area</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <a href="javascript:void(0)" id="createNewArea"
                                    class="btn btn-success waves-effect btn-label waves-light">
                                    <i class="bx bx-plus label-icon"></i>
                                    Create New Area
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle table-bordered area_datatable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="80px">Action</th>
                                        <th>ID</th>
                                        <th>Branch</th>
                                        <th>Area Name</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
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

        <!-- Modal for Create/Edit Area -->
        <div class="modal fade" id="areaModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHeading">Add New Area</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="areaForm" name="areaForm" class="form-horizontal">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div id="formErrors" class="alert alert-danger" style="display: none;"></div>

                            <input type="hidden" class="form-control" id="area_id" name="area_id">
                            <input type="hidden" class="form-control" id="method" name="method">

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
                                        <span id="errorBranchId" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="area_name" class="col-form-label">Area Name<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="area_name" name="area_name" placeholder="Area Kelapa Dua Wetan" required>
                                        <span id="errorAreaName" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label class="col-form-label">Select Location on Map<span class="text-danger">*</span></label>
                                        <div id="map"></div>
                                        <p class="map-instructions">
                                            <i class="bx bx-map-pin me-1"></i>
                                            Click on the map to set the location, or drag the marker to adjust
                                        </p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="latitude" class="col-form-label">Latitude<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" placeholder="-6.339450730898" required readonly>
                                        <span id="errorLatitude" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="longitude" class="col-form-label">Longitude<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" placeholder="106.88302721229" required readonly>
                                        <span id="errorLongitude" class="invalid-feedback text-danger" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Tips:</strong> Click anywhere on the map to set the area location. You can also search for an address or drag the marker.
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

        // Map variables
        let map;
        let marker;
        const defaultLat = -6.2088;
        const defaultLng = 106.8456;

        // Initialize map
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
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    map.setView([userLat, userLng], 15);
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

        var table = $('.area_datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('master/area/data') ?>",
                type: "POST",
                data: function(d) {
                    d.<?= csrf_token() ?> = $('meta[name="csrf-token"]').attr('content');
                },
                error: function(xhr, error, code) {
                    console.log('Error:', xhr, error, code);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load area data. Please try again.'
                    });
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary editArea" data-id="${data}" title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger deleteArea" data-id="${data}" title="Delete">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name',
                    render: function(data) {
                        return data ? `<span class="badge bg-info">${data}</span>` : '-';
                    }
                },
                {
                    data: 'area_name',
                    name: 'area_name'
                },
                {
                    data: 'latitude',
                    name: 'latitude',
                    render: function(data) {
                        return data ? parseFloat(data).toFixed(6) : '-';
                    }
                },
                {
                    data: 'longitude',
                    name: 'longitude',
                    render: function(data) {
                        return data ? parseFloat(data).toFixed(6) : '-';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
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
                    name: 'updated_at',
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

        $('#createNewArea').click(function() {
            $('#saveBtn').val("create");
            $('#area_id').val('');
            $('#areaForm').trigger("reset");
            $('#modalHeading').html("Add New Area");
            $('#areaModal').modal('show');
            $('#formErrors').hide();
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');

            setTimeout(function() {
                initMap();
            }, 300);
        });

        $('body').on('click', '.editArea', function() {
            var area_id = $(this).data('id');
            $.get("<?= site_url('master/area/detail/') ?>" + area_id, function(data) {
                if (data.success) {
                    $('#modalHeading').html("Edit Area");
                    $('#saveBtn').val("edit");
                    $('#areaModal').modal('show');
                    $('#area_id').val(data.data.id);
                    $('#branch_id').val(data.data.branch_id);
                    $('#area_name').val(data.data.area_name);
                    $('#latitude').val(data.data.latitude);
                    $('#longitude').val(data.data.longitude);
                    $('#formErrors').hide();
                    $('.invalid-feedback').hide();
                    $('.form-control').removeClass('is-invalid');

                    setTimeout(function() {
                        initMap();
                        loadExistingMarker(parseFloat(data.data.latitude), parseFloat(data.data.longitude));
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

        $('body').on('click', '.deleteArea', function() {
            var area_id = $(this).data("id");
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
                        url: "<?= site_url('master/area/delete/') ?>" + area_id,
                        success: function(data) {
                            if (data.success) {
                                table.draw();
                                Swal.fire(
                                    'Deleted!',
                                    data.message,
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message,
                                    'error'
                                );
                            }
                        },
                        error: function(data) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong!',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        $('#areaForm').submit(function(e) {
            e.preventDefault();

            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            $('#formErrors').hide();

            var formData = new FormData(this);
            var actionType = $('#saveBtn').val();
            var url = actionType === "create" ?
                "<?= site_url('master/area/create') ?>" :
                "<?= site_url('master/area/update/') ?>" + $('#area_id').val();

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.success) {
                        $('#areaForm').trigger("reset");
                        $('#areaModal').modal('hide');
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
                                $('#error' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1)).show().find('strong').text(value);
                                $('#' + key).addClass('is-invalid');
                            });
                            errorHtml += '</ul>';
                            $('#formErrors').html(errorHtml).show();
                        } else {
                            $('#formErrors').html('<p>' + data.message + '</p>').show();
                        }
                    }
                },
                error: function(data) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong! Please try again.'
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>