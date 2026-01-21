    <?= $this->extend('layout/default') ?>

    <?= $this->section('title') ?>
    <title>Pengaturan Perusahaan &mdash; Billing Tagihan Internet</title>
    <?= $this->endSection() ?>

    <?= $this->section('content') ?>
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Perusahaan</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Pengaturan</a></li>
                                <li class="breadcrumb-item active">Perusahaan</li>
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
                            <div class="row">
                                <div class="col-sm-4">
                                    <h4 class="card-title mb-4">Pengaturan Perusahaan</h4>
                                </div>
                            </div>

                            <!-- Tab panes -->
                            <form id="CompanyForm" enctype="multipart/form-data" method="POST"
                                action="<?= site_url('settings/company/save') ?>">
                                <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">
                                <div class="row">
                                    <div class="col-lg-12 my-4">
                                        <div class="text-center">
                                            <?php
                                            $logoUrl = isset($company['logo']) && $company['logo'] ? base_url('uploads/' . $company['logo']) : base_url('backend/assets/images/logo-sm.png');
                                            ?>
                                            <img id="preview_logo" src="<?= $logoUrl ?>" alt="Logo Perusahaan" class="" width="200px">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="text-center">
                                            <span class='badge bg-info text-white'>Resolusi gambar optimal: 550x120 piksel</span><br>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="text-center">
                                            <span class='badge bg-secondary' id="upload-file-info"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-4">
                                        <div class="text-center">
                                            <label class="btn btn-success" for="logo">
                                                <input id="logo" name="logo" type="file" style="display:none"
                                                    onchange="$('#upload-file-info').text(this.files[0].name)"
                                                    value="">
                                                <i class="mdi mdi-upload font-size-16"></i>
                                                Pilih Logo
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div>
                                            <label class="form-label">Nama Perusahaan<span class="text-danger">*</span></label>
                                            <input class="form-control" name="id" id="id" type="hidden"
                                                value="<?= isset($company['id']) ? esc($company['id']) : '' ?>">
                                            <input class="form-control" name="name"
                                                id="name" type="text" placeholder="Masukkan nama perusahaan" required
                                                value="<?= isset($company['name']) ? esc($company['name']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div>
                                            <label class="form-label">Email Perusahaan<span class="text-danger">*</span></label>
                                            <input class="form-control" name="email"
                                                id="email" type="email" placeholder="contoh@perusahaan.com" required
                                                value="<?= isset($company['email']) ? esc($company['email']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-4">
                                        <div>
                                            <label class="form-label">Alamat Perusahaan<span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="address"
                                                id="address" rows="3" required
                                                placeholder="Masukkan alamat lengkap perusahaan"><?= isset($company['address']) ? esc($company['address']) : '' ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div>
                                            <label class="form-label">Nomor Telepon<span class="text-danger">*</span></label>
                                            <input class="form-control" name="phone"
                                                id="phone" type="tel" placeholder="Contoh: 021-1234567" required
                                                value="<?= isset($company['phone']) ? esc($company['phone']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div>
                                            <label class="form-label">Situs Web</label>
                                            <input class="form-control" name="website" id="website" type="url"
                                                placeholder="https://www.perusahaan.com"
                                                value="<?= isset($company['website']) ? esc($company['website']) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="bill" role="tabpanel">
                                    <div class="row my-4">

                                    </div>
                                </div>

                                <div class="col-lg-12 text-center">
                                    <hr>
                                    <button type="submit" class="btn btn-primary px-5" id="submitForm">
                                        <i class="mdi mdi-content-save me-2"></i>Perbarui Data Perusahaan
                                    </button>
                                    <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary px-5 ms-2">
                                        <i class="mdi mdi-arrow-left me-2"></i>Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <script>
            // CSRF Setup
            $(function() {
                // Setup CSRF for AJAX requests
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const csrfTokenName = $('meta[name="csrf-token-name"]').attr('content');

                console.log('CSRF Token:', csrfToken); // Debug
                console.log('CSRF Token Name:', csrfTokenName); // Debug

                $.ajaxSetup({
                    beforeSend: function(xhr, settings) {
                        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        }
                    }
                });
            });

            // Form submission handler
            $('#CompanyForm').on('submit', function(e) {
                e.preventDefault();

                const submitBtn = $('#submitForm');
                const originalText = submitBtn.html();
                const formAction = $(this).attr('action');

                console.log('Form action:', formAction); // Debug

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-2"></i>Menyimpan...');

                // Create FormData for file upload
                const formData = new FormData(this);

                // Add CSRF token manually to FormData using correct token name
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                formData.append('csrf_test_name', csrfToken);

                console.log('FormData entries:'); // Debug
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                $.ajax({
                    url: formAction,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        console.log('Success response:', response); // Debug

                        if (response.status === 'success') {
                            toastr.success(response.message || 'Data perusahaan berhasil diperbarui');

                            // Refresh page after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Gagal memperbarui data perusahaan');
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error response:', xhr); // Debug
                        console.error('Response text:', xhr.responseText); // Debug

                        let errorMessage = 'Terjadi kesalahan saat memperbarui data';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('<br>');
                        } else if (xhr.status === 403) {
                            errorMessage = 'Akses ditolak. Silakan login ulang.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Terjadi kesalahan server internal';
                        }

                        toastr.error(errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Logo preview handler
            $(document).ready(function() {
                $('#logo').change(function() {
                    if (this.files && this.files[0]) {
                        // Validate file type
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!allowedTypes.includes(this.files[0].type)) {
                            toastr.error('Format file tidak didukung. Gunakan JPG, PNG, atau GIF');
                            this.value = '';
                            return;
                        }

                        // Validate file size (max 2MB)
                        if (this.files[0].size > 2 * 1024 * 1024) {
                            toastr.error('Ukuran file terlalu besar. Maksimal 2MB');
                            this.value = '';
                            return;
                        }

                        // Show preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#preview_logo').attr('src', e.target.result);
                        }
                        reader.readAsDataURL(this.files[0]);

                        // Show filename
                        $('#upload-file-info').text(this.files[0].name).removeClass('bg-secondary').addClass('bg-success');
                    }
                });
            });

            // Form validation
            $('#name, #email, #address, #phone').on('blur', function() {
                if ($(this).prop('required') && !$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Email validation
            $('#email').on('blur', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (email && !emailRegex.test(email)) {
                    $(this).addClass('is-invalid');
                    toastr.error('Format email tidak valid');
                } else if (email) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Phone validation
            $('#phone').on('input', function() {
                // Allow only numbers, spaces, hyphens, and plus sign
                this.value = this.value.replace(/[^0-9\s\-\+\(\)]/g, '');
            });

            // Website URL validation
            $('#website').on('blur', function() {
                const url = $(this).val();
                if (url && !url.match(/^https?:\/\//)) {
                    $(this).val('https://' + url);
                }
            });
        </script>
        <?= $this->endSection() ?>