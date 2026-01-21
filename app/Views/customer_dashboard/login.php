<!doctype html>
<html lang="id" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>Portal Pelanggan - Billing System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Customer Portal Login" name="description" />
    <meta content="Kimonet Digital Synergy" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>backend/assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="<?= base_url() ?>backend/assets/login/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?= base_url() ?>backend/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?= base_url() ?>backend/assets/login/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .account-pages {
            position: relative;
            z-index: 1;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e3e6f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .bg-primary-subtle {
            background: #f8f9fa !important;
            border-bottom: 1px solid #e3e6f0;
        }

        .text-primary {
            color: #495057 !important;
        }

        .text-white {
            color: #495057 !important;
        }

        .text-white-50 {
            color: #6c757d !important;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>

    <!-- App js -->

</head>

<body>

    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary-subtle">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-white p-4">
                                        <h5 class="text-white">Portal Pelanggan</h5>
                                        <p class="text-white-50">Masuk ke dashboard Anda</p>
                                        <img src="<?= base_url() ?>backend/assets/images/logo akanet.png" alt="" class="img-fluid" style="max-height: 40px;">
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="<?= base_url() ?>backend/assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="auth-logo">
                                <a href="<?= site_url('customer-portal') ?>" class="auth-logo-dark">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="<?= base_url() ?>backend/assets/images/logo-sm.png" alt="" class="rounded-circle" height="45">
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <?php if (session()->getFlashdata('error')) : ?>
                                <div class="alert alert-danger alert-border-left alert-dismissible fade show mb-xl-0 material-shadow" role="alert">
                                    <i class="ri-error-warning-line me-3 align-middle fs-16"></i>Gagal,
                                    <?= session()->getFlashdata('error') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if (session()->getFlashdata('success')) : ?>
                                <div class="alert alert-success alert-border-left alert-dismissible fade show mb-xl-0 material-shadow" role="alert">
                                    <i class="ri-check-line me-3 align-middle fs-16"></i>Berhasil,
                                    <?= session()->getFlashdata('success') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <div class="p-2">
                                <form class="form-horizontal" method="POST" action="<?= site_url('customer-portal/login') ?>">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="credentials" class="form-label">Nomor Layanan / WhatsApp</label>
                                        <input type="text" class="form-control" id="credentials" name="credentials" placeholder="Masukkan nomor layanan atau WhatsApp" required>
                                        <small class="text-muted">
                                            <i class="ri-information-line me-1"></i>
                                            Contoh: 141437382732 atau 6285183112127
                                        </small>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                                            <i class="ri-login-box-line me-2"></i>Masuk ke Dashboard
                                        </button>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <div class="signin-other-title">
                                            <h5 class="font-size-14 mb-3 title text-muted">Bantuan</h5>
                                        </div>
                                        <p class="text-muted mb-2">
                                            <i class="ri-phone-line me-2"></i>
                                            Hubungi customer service jika mengalami kesulitan login
                                        </p>
                                        <p class="text-muted">
                                            <i class="ri-time-line me-2"></i>
                                            Layanan 24/7
                                        </p>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="mt-5 text-center">

                        <div>
                            <p class="text-muted">
                                © <script>
                                    document.write(new Date().getFullYear())
                                </script> by Masyarakat
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- end account-pages -->

    <!-- JAVASCRIPT -->
    <script src="<?= base_url() ?>backend/assets/login/js/jquery.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/login/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/node-waves/waves.min.js"></script>

    <!-- App js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto focus on input
            const credentialsInput = document.getElementById('credentials');
            if (credentialsInput) {
                credentialsInput.focus();

                // Format nomor input - allow only numbers
                credentialsInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    e.target.value = value;
                });
            }
        });
    </script>
    <script src="<?= base_url() ?>backend/assets/login/js/app-login.js"></script>
</body>

</html>