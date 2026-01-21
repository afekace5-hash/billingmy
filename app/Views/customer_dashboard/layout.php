<!doctype html>
<html lang="id" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title><?= $this->renderSection('title') ?> - Portal Pelanggan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Customer Portal" name="description" />
    <meta content="Billing System" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="<?= base_url() ?>assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?= base_url() ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?= base_url() ?>assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <!-- Sweet Alert-->
    <link href="<?= base_url() ?>assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />

    <style>
        .navbar-brand-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .navbar-nav .nav-link {
            color: #495057 !important;
        }

        .navbar-nav .nav-link:hover {
            color: #667eea !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            border: none;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }

        .page-content {
            padding-top: 20px;
        }

        .customer-sidebar {
            background: #fff;
            border-right: 1px solid #e9ecef;
            min-height: calc(100vh - 70px);
        }

        .customer-nav .nav-link {
            color: #6c757d;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 4px;
        }

        .customer-nav .nav-link:hover,
        .customer-nav .nav-link.active {
            background-color: #f8f9fa;
            color: #667eea;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .navbar-brand-box {
                width: 100%;
            }

            .navbar-brand-box .logo-lg {
                font-size: 16px;
            }

            .topnav {
                background: #fff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            }

            .topnav .navbar-nav {
                padding: 10px;
            }

            .topnav .nav-link {
                padding: 10px 15px;
                border-radius: 6px;
                margin-bottom: 5px;
            }

            .page-content {
                padding: 15px 10px;
            }

            .card {
                margin-bottom: 15px;
            }

            .card-body {
                padding: 15px;
            }

            h4,
            .font-size-18 {
                font-size: 16px !important;
            }

            h5 {
                font-size: 14px !important;
            }

            .table {
                font-size: 12px;
            }

            .btn {
                padding: 8px 12px;
                font-size: 13px;
            }

            .badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            .avatar-lg {
                width: 50px;
                height: 50px;
            }

            .avatar-lg i {
                font-size: 20px !important;
            }

            /* Hide some elements on mobile */
            .d-none.d-xl-inline-block {
                display: none !important;
            }

            .page-title-right {
                display: none;
            }

            .footer .text-sm-end {
                text-align: center !important;
                margin-top: 10px;
            }

            /* Make tables scrollable */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Small mobile devices */
        @media (max-width: 576px) {

            .col-xl-3,
            .col-md-6 {
                margin-bottom: 10px;
            }

            .card-body .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .card-body .d-flex .avatar-lg {
                margin: 0 auto 10px;
            }

            .card-body .d-flex .text-end {
                text-align: center !important;
                margin-top: 10px;
            }

            h4.mb-0 {
                font-size: 18px !important;
            }

            .font-size-14 {
                font-size: 13px !important;
            }
        }
    </style>
</head>

<body data-sidebar="light">
    <div id="layout-wrapper">
        <!-- Header -->
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="<?= site_url('customer-portal/dashboard') ?>" class="logo logo-light">
                            <span class="logo-sm">
                                <i class="bx bx-wifi font-size-22 text-white"></i>
                            </span>
                            <span class="logo-lg">
                                <span class="text-white fw-bold">Portal Pelanggan</span>
                            </span>
                        </a>
                    </div>

                    <button type="button" class="Btn btn btn-sm px-3 font-size-16 d-lg-none header-item" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>

                <div class="d-flex">
                    <!-- Customer Info Dropdown -->
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="<?= base_url() ?>assets/images/users/avatar-1.jpg"
                                alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1 fw-medium"><?= session()->get('customer_name') ?></span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a class="dropdown-item" href="<?= site_url('customer-portal/profile') ?>">
                                <i class="bx bx-user font-size-16 align-middle me-1"></i>
                                <span>Profil</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="<?= site_url('customer-portal/logout') ?>">
                                <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Navigation Bar -->
        <div class="topnav">
            <div class="container-fluid">
                <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
                    <div class="collapse navbar-collapse" id="topnav-menu-content">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link <?= (current_url() == site_url('customer-portal/dashboard')) ? 'active' : '' ?>"
                                    href="<?= site_url('customer-portal/dashboard') ?>">
                                    <i class="bx bx-home-circle me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos(current_url(), 'customer/invoices') !== false) ? 'active' : '' ?>"
                                    href="<?= site_url('customer-portal/invoices') ?>">
                                    <i class="bx bx-receipt me-2"></i>Tagihan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos(current_url(), 'customer/profile') !== false) ? 'active' : '' ?>"
                                    href="<?= site_url('customer-portal/profile') ?>">
                                    <i class="bx bx-user me-2"></i>Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="contactCS()">
                                    <i class="bx bx-phone me-2"></i>Bantuan
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?= $this->renderSection('content') ?>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= date('Y') ?> © Portal Pelanggan.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Sistem Manajemen Tagihan Internet
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="<?= base_url() ?>assets/libs/jquery/jquery.min.js"></script>
    <script src="<?= base_url() ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="<?= base_url() ?>assets/libs/simplebar/simplebar.min.js"></script>
    <script src="<?= base_url() ?>assets/libs/node-waves/waves.min.js"></script>

    <!-- Sweet Alerts js -->
    <script src="<?= base_url() ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- App js -->
    <script src="<?= base_url() ?>assets/js/app.js"></script>

    <script>
        // Global functions
        function contactCS() {
            const phoneNumber = '6285183112127';
            const customerNumber = '<?= session()->get('customer_number') ?>';
            const message = `Halo, saya pelanggan dengan nomor layanan ${customerNumber}. Saya ingin bertanya tentang layanan internet.`;
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Toast notifications
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '<?= session()->getFlashdata('success') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: '<?= session()->getFlashdata('error') ?>',
                showConfirmButton: false,
                timer: 3000
            });
        <?php endif; ?>
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>