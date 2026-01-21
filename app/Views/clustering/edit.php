<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Edit Cluster - Billingkimo</title>
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<!-- OpenStreetMap dengan Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 400px !important;
        width: 100% !important;
        min-height: 350px;
        border: 4px solid #007bff !important;
        border-radius: 8px;
        position: relative;
        z-index: 1;
        background-color: #ffffff !important;
        overflow: visible !important;
    }

    /* CSS Reset untuk Leaflet */
    #map * {
        box-sizing: content-box !important;
    }

    #map img {
        max-width: none !important;
        height: auto !important;
        width: auto !important;
    }

    /* Form styling */
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Map container styling */
    .map-container {
        position: relative;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        min-height: 450px;
        height: 450px !important;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .col-md-6 {
        min-height: 500px !important;
        height: auto !important;
    }

    /* Ensure proper map sizing - simplified */
    .leaflet-container {
        height: 400px !important;
        width: 100% !important;
    }

    /* Card styling */
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }

    .card-header .card-title {
        color: white;
    }

    /* Location button styling */
    #getLocationBtn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }

    /* Info text styling */
    .text-muted {
        font-size: 0.875rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        #map {
            height: 300px;
        }
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
                    <h4 class="mb-sm-0 font-size-18">Edit Cluster</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('clustering') ?>">Clustering</a></li>
                            <li class="breadcrumb-item active">Edit Cluster</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <!-- Form Section - Left Side -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-edit text-white"></i> Form Edit Cluster
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="editForm" action="<?= site_url('clustering/' . $cluster->id_clustering) ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="PUT">

                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="bx bx-buildings text-primary"></i> Nama Cluster <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Contoh: ODP SERVER 1, BTS SERVER 1"
                                    value="<?= esc($cluster->name ?? '') ?>" required>
                                <span id="error_name" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>

                            <div class="mb-3">
                                <label for="number_of_ports" class="form-label">
                                    <i class="bx bx-plug text-primary"></i> Jumlah Port
                                </label>
                                <input type="text" class="form-control" id="number_of_ports" name="number_of_ports"
                                    placeholder="Contoh: 1,2,3"
                                    value="<?= esc($cluster->number_of_ports ?? '') ?>">
                                <small class="text-muted">Opsional - Pisahkan dengan koma jika lebih dari satu</small>
                                <span id="error_number_of_ports" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>

                            <div class="mb-3">
                                <label for="type_option" class="form-label">
                                    <i class="bx bx-network-chart text-primary"></i> Jenis Teknologi <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="type_option" id="type_option" required>
                                    <option value="">-- Pilih Teknologi --</option>
                                    <option value="FTTH" <?= (isset($cluster->type_option) && $cluster->type_option == 'FTTH') ? 'selected' : '' ?>>FTTH (Fiber To The Home)</option>
                                    <option value="WIRELESS" <?= (isset($cluster->type_option) && $cluster->type_option == 'WIRELESS') ? 'selected' : '' ?>>WIRELESS</option>
                                </select>
                                <span id="error_type_option" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>

                            <div class="mb-3">
                                <label for="server_location_id" class="form-label">
                                    <i class="bx bx-server text-primary"></i> Pilih Lokasi Server <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="lokasi_server_id" id="server_location_id" required>
                                    <option value="">-- Pilih Lokasi Server --</option>
                                    <?php if (isset($servers) && !empty($servers)): ?>
                                        <?php foreach ($servers as $server): ?>
                                            <option value="<?= esc($server['id_lokasi']) ?>"
                                                <?= (isset($cluster->lokasi_server_id) && $cluster->lokasi_server_id == $server['id_lokasi']) ? 'selected' : '' ?>>
                                                <?= esc($server['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <span id="error_lokasi_server_id" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bx bx-map-pin text-primary"></i> Koordinat Peta
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="latitude" id="latitude"
                                            placeholder="Latitude" value="<?= esc($cluster->latitude ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="longitude" id="longitude"
                                            placeholder="Longitude" value="<?= esc($cluster->longitude ?? '') ?>">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="bx bx-info-circle"></i> Klik di peta untuk memilih koordinat secara otomatis
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    <i class="bx bx-map text-primary"></i> Alamat Lengkap
                                </label>
                                <textarea class="form-control" name="address" id="address" rows="3"
                                    placeholder="Masukkan alamat lengkap cluster (Jalan, RT/RW, Kelurahan, dll)"><?= esc($cluster->address ?? '') ?></textarea>
                                <span id="error_address" class="invalid-feedback text-danger" role="alert">
                                    <strong></strong>
                                </span>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="<?= site_url('clustering') ?>" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back"></i> Kembali
                                </a>
                                <button type="submit" id="saveBtn" class="btn btn-success">
                                    <i class="bx bx-save"></i> Update Cluster
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Map Section - Right Side -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-map text-white"></i> Pilih Lokasi di Peta
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="map-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button type="button" class="btn btn-sm btn-primary" id="getGPS">
                                    <i class="bx bx-current-location"></i> Dapatkan GPS
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearMap">
                                    <i class="bx bx-x"></i> Clear Marker
                                </button>
                            </div>
                            <div id="map"></div>
                            <div class="alert alert-info py-2 mb-0 mt-3">
                                <i class="bx bx-info-circle me-1"></i>
                                <small>Klik di peta untuk menentukan lokasi cluster atau gunakan tombol "Dapatkan GPS" untuk menggunakan lokasi saat ini</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- OpenStreetMap dengan Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let marker;
    let isMapInitialized = false;

    function showMapError(msg) {
        const mapContainer = document.getElementById('map');
        if (mapContainer) {
            mapContainer.innerHTML = `<div style="color:red;padding:1em;background:#fff;border:1px solid #f00;">${msg}</div>`;
        }
    }

    // Inisialisasi map dengan delay dan error handling
    function initMap() {
        if (isMapInitialized) return;

        // Check Leaflet
        if (typeof L === 'undefined') {
            showMapError('Leaflet JS library tidak dimuat. Cek koneksi internet dan pastikan <script src=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.js\"> ada di halaman.');
            return;
        }

        // Pastikan container map ada dan terlihat
        const mapContainer = document.getElementById('map');
        if (!mapContainer) {
            showMapError('Container map tidak ditemukan. Cek HTML dan pastikan <div id=\"map\"></div> ada.');
            return;
        }
        if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
            showMapError('Container map tidak punya ukuran. Cek CSS parent dan pastikan map terlihat.');
            return;
        }

        try {
            // Default koordinat (Jakarta)
            const defaultLat = -6.2088;
            const defaultLng = 106.8456;

            // Ambil koordinat existing jika ada
            const existingLat = parseFloat($('#latitude').val()) || defaultLat;
            const existingLng = parseFloat($('#longitude').val()) || defaultLng;

            // Hapus map existing jika ada
            if (map) {
                map.remove();
                map = null;
            }

            // Clear container content
            mapContainer.innerHTML = '';

            // Buat map baru dengan opsi yang lebih robust
            map = L.map('map', {
                center: [existingLat, existingLng],
                zoom: existingLat !== defaultLat ? 15 : 13,
                zoomControl: true,
                scrollWheelZoom: true,
                doubleClickZoom: true,
                dragging: true,
                tap: true,
                touchZoom: true,
                preferCanvas: false
            });

            // Add tile layer dengan error handling
            const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors',
                crossOrigin: true
            });

            tileLayer.on('tileerror', function(error) {
                showMapError('Gagal memuat tile peta. Cek koneksi internet atau coba refresh halaman.');
            });

            tileLayer.addTo(map);

            // Fallback: jika map tetap kosong setelah 2 detik, tampilkan pesan
            setTimeout(function() {
                if (mapContainer && mapContainer.innerHTML.trim() === '') {
                    showMapError('Map sudah diinisialisasi, tapi tidak ada tile yang tampil. Cek koneksi internet, CORS, atau CSS parent.');
                }
            }, 2000);

            // Set existing marker jika ada koordinat
            if ($('#latitude').val() && $('#longitude').val()) {
                marker = L.marker([existingLat, existingLng], {
                    draggable: true
                }).addTo(map);

                // Event ketika marker di-drag
                marker.on('dragend', function(e) {
                    const position = marker.getLatLng();
                    $('#latitude').val(position.lat.toFixed(6));
                    $('#longitude').val(position.lng.toFixed(6));
                });
            }

            // Event click pada map
            map.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);

                // Update input fields
                $('#latitude').val(lat);
                $('#longitude').val(lng);

                // Update atau buat marker baru
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {
                        draggable: true
                    }).addTo(map);

                    // Event ketika marker di-drag
                    marker.on('dragend', function(e) {
                        const position = marker.getLatLng();
                        $('#latitude').val(position.lat.toFixed(6));
                        $('#longitude').val(position.lng.toFixed(6));
                    });
                }
            });

            isMapInitialized = true;

            console.log('Map berhasil diinisialisasi');

        } catch (error) {
            console.error('Error inisialisasi map:', error);
            $('#map').html('<div class="alert alert-warning">Gagal memuat peta. Silakan refresh halaman.<br>Error: ' + error.message + '</div>');
        }
    }

    $(document).ready(function() {
        console.log('Document ready, starting map initialization...');

        // Coba inisialisasi langsung tanpa delay
        setTimeout(function() {
            console.log('Attempting direct map initialization...');

            if (typeof L === 'undefined') {
                $('#map').html('<div class="alert alert-danger">Leaflet library tidak dimuat!</div>');
                return;
            }

            try {
                // Hapus map existing jika ada
                if (map) {
                    map.remove();
                    map = null;
                }

                // Default koordinat Jakarta
                const lat = -6.2088;
                const lng = 106.8456;

                console.log('Creating map with coordinates:', lat, lng);

                // Tampilkan loading message
                $('#map').html('<div style="display:flex;align-items:center;justify-content:center;height:400px;color:#666;font-size:16px;"><div>Loading map tiles...</div></div>');

                // Buat map langsung
                map = L.map('map').setView([lat, lng], 13);

                console.log('Map created, adding tiles...');

                // Coba tile provider yang berbeda - CartoDB Positron
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap © CartoDB',
                    subdomains: 'abcd'
                }).addTo(map);

                // Fallback ke OpenStreetMap jika CartoDB gagal
                setTimeout(() => {
                    console.log('Adding fallback OSM tiles...');
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);
                }, 2000);

                console.log('Tiles added successfully');

                // Force resize dan redraw dengan multiple attempts
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize(true);
                        map._resetView(map.getCenter(), map.getZoom(), true);
                        console.log('Map size invalidated and view reset');

                        // Double refresh
                        setTimeout(() => {
                            map.invalidateSize(true);
                            map.redraw();
                            console.log('Map second refresh');
                        }, 500);

                        // Triple refresh
                        setTimeout(() => {
                            map.invalidateSize(true);
                            map.eachLayer(function(layer) {
                                if (layer.redraw) {
                                    layer.redraw();
                                }
                            });
                            console.log('Map triple refresh with layer redraw');
                        }, 1000);
                    }
                }, 100);

                // Tambahkan event handler
                map.on('click', function(e) {
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);

                    $('#latitude').val(lat);
                    $('#longitude').val(lng);

                    if (marker) {
                        marker.setLatLng(e.latlng);
                    } else {
                        marker = L.marker(e.latlng, {
                            draggable: true
                        }).addTo(map);

                        marker.on('dragend', function(e) {
                            const position = marker.getLatLng();
                            $('#latitude').val(position.lat.toFixed(6));
                            $('#longitude').val(position.lng.toFixed(6));
                        });
                    }
                });

                // Set existing marker jika ada koordinat
                const existingLat = parseFloat($('#latitude').val());
                const existingLng = parseFloat($('#longitude').val());
                if (!isNaN(existingLat) && !isNaN(existingLng)) {
                    marker = L.marker([existingLat, existingLng], {
                        draggable: true
                    }).addTo(map);
                    map.setView([existingLat, existingLng], 15);

                    marker.on('dragend', function(e) {
                        const position = marker.getLatLng();
                        $('#latitude').val(position.lat.toFixed(6));
                        $('#longitude').val(position.lng.toFixed(6));
                    });
                }

                isMapInitialized = true;

            } catch (error) {
                console.error('Error creating map:', error);
                $('#map').html('<div class="alert alert-danger">Error: ' + error.message + '</div>');
            }
        }, 1000);

        // Re-initialize map pada window resize
        $(window).on('resize', function() {
            if (map) {
                setTimeout(() => {
                    map.invalidateSize();
                }, 300);
            }
        });

        // Handle coordinate input manual
        $('#latitude, #longitude').on('change', function() {
            const lat = parseFloat($('#latitude').val());
            const lng = parseFloat($('#longitude').val());

            if (!isNaN(lat) && !isNaN(lng) && map) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);

                    marker.on('dragend', function(e) {
                        const position = marker.getLatLng();
                        $('#latitude').val(position.lat.toFixed(6));
                        $('#longitude').val(position.lng.toFixed(6));
                    });
                }
                map.setView([lat, lng], 15);
            }
        });

        // GPS Button handler
        $('#getGPS').on('click', function() {
            const $btn = $(this);

            if (navigator.geolocation) {
                $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Mencari...');

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude.toFixed(6);
                        const lng = position.coords.longitude.toFixed(6);

                        $('#latitude').val(lat);
                        $('#longitude').val(lng);

                        // Update map view dan marker
                        if (map) {
                            map.setView([lat, lng], 15);

                            if (marker) {
                                marker.setLatLng([lat, lng]);
                            } else {
                                marker = L.marker([lat, lng], {
                                    draggable: true
                                }).addTo(map);

                                marker.on('dragend', function(e) {
                                    const position = marker.getLatLng();
                                    $('#latitude').val(position.lat.toFixed(6));
                                    $('#longitude').val(position.lng.toFixed(6));
                                });
                            }
                        }

                        $btn.prop('disabled', false).html('<i class="bx bx-current-location"></i> Dapatkan GPS');

                        Swal.fire({
                            icon: 'success',
                            title: 'Lokasi ditemukan!',
                            text: 'Koordinat GPS berhasil didapatkan',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    function(error) {
                        $btn.prop('disabled', false).html('<i class="bx bx-current-location"></i> Dapatkan GPS');

                        let message = 'Tidak dapat mengakses GPS';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                message = 'Izin akses lokasi ditolak';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message = 'Informasi lokasi tidak tersedia';
                                break;
                            case error.TIMEOUT:
                                message = 'Timeout mendapatkan lokasi';
                                break;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'GPS Error',
                            text: message
                        });
                    }
                );
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'GPS tidak didukung',
                    text: 'Browser Anda tidak mendukung geolocation'
                });
            }
        });

        // Clear map handler
        $('#clearMap').on('click', function() {
            $('#latitude').val('');
            $('#longitude').val('');

            if (marker && map) {
                map.removeLayer(marker);
                marker = null;
            }

            // Reset map view ke default
            if (map) {
                map.setView([-6.2088, 106.8456], 13);
            }
        });

        // Handle form submission
        $('#editForm').on('submit', function(e) {
            e.preventDefault();

            // Validasi koordinat
            if (!$('#latitude').val() || !$('#longitude').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Koordinat Belum Dipilih',
                    text: 'Silakan klik pada peta untuk memilih lokasi clustering'
                });
                return;
            }

            const formData = new FormData(this);
            const $btn = $('#saveBtn');

            $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Menyimpan...');

            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang menyimpan data clustering',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '<?= base_url('clustering') ?>';
                        });
                    } else {
                        // Handle validation errors
                        if (response.errors) {
                            $('.form-control, .form-select').removeClass('is-invalid');
                            $('.invalid-feedback strong').text('');

                            $.each(response.errors, function(field, message) {
                                let fieldId = field;
                                if (field === 'lokasi_server_id') {
                                    fieldId = 'server_location_id';
                                }

                                $('#' + fieldId).addClass('is-invalid');
                                $('#error_' + field + ' strong').text(message);
                            });
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Terjadi kesalahan validasi'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Ajax Error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem'
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="bx bx-save"></i> Update Cluster');
                }
            });
        });

        // Force refresh map setelah beberapa detik
        setTimeout(() => {
            if (map && isMapInitialized) {
                console.log('Final map resize...');
                map.invalidateSize();
            } else if (!isMapInitialized) {
                console.log('Map not initialized yet, trying again...');
                initMap();
            }
        }, 2000);

        // Additional fallback untuk memastikan map muncul
        setTimeout(() => {
            if (!isMapInitialized) {
                console.log('Final attempt to initialize map...');
                initMap();
            }
        }, 5000);
    });
</script>
<?= $this->endSection() ?>