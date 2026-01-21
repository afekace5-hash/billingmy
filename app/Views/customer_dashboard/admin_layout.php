<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?= $this->renderSection('title') ?> | Customer Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Customer Portal - Billing Internet" name="description" />
    <meta content="Customer Portal" name="author" />
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>backend/assets/images/favicon.png">

    <!-- Sweet Alert-->
    <link href="<?= base_url() ?>backend/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>backend/assets/libs/toastr/toastr.min.css">

    <!-- Bootstrap Css -->
    <link href="<?= base_url() ?>backend/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?= base_url() ?>backend/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- Remix Icon CDN for ri-* icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Boxicons CDN for bx-* icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- App Css-->
    <link href="<?= base_url() ?>backend/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    <!-- Custom Dashboard CSS -->
    <link href="<?= base_url() ?>backend/assets/css/custom-dashboard.css" rel="stylesheet" type="text/css" />

    <!-- Preloader CSS -->
    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .preloader-fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        #preloader {
            transition: opacity 0.5s ease-out;
        }

        body.preloader-active {
            overflow: hidden;
        }

        /* Ensure sidebar is visible */
        .vertical-menu {
            position: fixed !important;
            top: 70px;
            bottom: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .vertical-menu [data-simplebar] {
            height: 100%;
            overflow-y: auto;
        }

        /* Main content adjustment */
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Body and layout wrapper adjustments */
        body {
            background-color: #f8f9fa;
        }

        #layout-wrapper {
            position: relative;
            min-height: 100vh;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .vertical-menu {
                transform: translateX(-100%);
            }

            .vertical-menu.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .customer-header {
                left: 0;
            }
        }

        /* Sidebar Menu Styling */
        #sidebar-menu ul li a {
            display: block;
            padding: 12px 20px;
            color: #545a6d;
            position: relative;
            font-size: 14px;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 2px 10px;
        }

        #sidebar-menu ul li a:hover {
            color: #405189;
            background-color: rgba(64, 81, 137, 0.08);
            text-decoration: none;
        }

        #sidebar-menu ul li a.active {
            color: #405189;
            background-color: rgba(64, 81, 137, 0.15);
            font-weight: 500;
        }

        #sidebar-menu ul li a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: #405189;
            border-radius: 0 3px 3px 0;
        }

        #sidebar-menu ul li a i {
            display: inline-block;
            min-width: 1.75rem;
            padding-bottom: 0.125em;
            font-size: 1.1rem;
            line-height: 1.40625rem;
            vertical-align: middle;
            color: #7b8190;
            transition: all 0.3s ease;
        }

        #sidebar-menu ul li a:hover i,
        #sidebar-menu ul li a.active i {
            color: #405189;
        }

        #sidebar-menu .menu-title {
            padding: 12px 20px;
            letter-spacing: 0.05em;
            pointer-events: none;
            cursor: default;
            font-size: 11px;
            text-transform: uppercase;
            color: #7b8190;
            font-weight: 600;
            margin-top: 10px;
        }

        /* Scrollbar styling for sidebar */
        .vertical-menu::-webkit-scrollbar {
            width: 6px;
        }

        .vertical-menu::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        .vertical-menu::-webkit-scrollbar-thumb {
            background: #c1c9d2;
            border-radius: 3px;
        }

        .vertical-menu::-webkit-scrollbar-thumb:hover {
            background: #a8b1bd;
        }

        /* Mobile Bottom Navigation */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 8px 0 12px;
            border-top: 1px solid #f0f0f0;
        }

        .mobile-bottom-nav .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px;
            color: #A0AEC0;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 4px;
            position: relative;
        }

        .mobile-bottom-nav .nav-link i {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .mobile-bottom-nav .nav-link span {
            font-size: 11px;
            font-weight: 500;
        }

        .mobile-bottom-nav .nav-link.active {
            color: #405189;
        }

        .mobile-bottom-nav .nav-link.active i {
            color: #405189;
        }

        .mobile-bottom-nav .nav-link:hover {
            color: #405189;
        }

        .mobile-bottom-nav .fab-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            border: 4px solid white;
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            cursor: pointer;
            transition: all 0.3s;
        }

        .mobile-bottom-nav .fab-button:active {
            transform: translateX(-50%) scale(0.9);
        }

        @media (max-width: 768px) {
            .mobile-bottom-nav {
                display: block;
            }

            .vertical-menu {
                display: none !important;
            }

            .customer-header {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding-bottom: 70px;
            }

            .page-content {
                padding-bottom: 90px;
                padding-top: 0 !important;
            }
        }

        .customer-header {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            height: 70px;
            z-index: 999;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .customer-header {
                left: 0;
            }
        }

        /* Page content padding */
        .page-content {
            padding-top: 50px;
        }

        .customer-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .status-isolated {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }
    </style>
</head>

<body data-sidebar="light" class="preloader-active">
    <!-- Preloader -->
    <div id="preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); z-index: 9999; display: flex; justify-content: center; align-items: center;">
        <div class="preloader-content" style="text-align: center;">
            <div class="preloader-logo" style="width: 60px; height: 60px; margin: 0 auto 20px; animation: spin 2s linear infinite;">
                <img src="<?= base_url() ?>backend/assets/images/preloader.svg" alt="Loading..." style="width: 100%; height: 100%;">
            </div>
            <div class="preloader-text" style="font-size: 14px; color: #6b7280; font-weight: 500;">Loading...</div>
        </div>
    </div>

    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar" class="customer-header">
            <div class="navbar-header">
                <div class="d-flex align-items-center">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="<?= site_url('customer-portal/dashboard') ?>" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?= base_url() ?>backend/assets/images/logo-sm.png" alt="" height="30">
                            </span>
                            <span class="logo-lg">
                                <img src="<?= base_url() ?>backend/assets/images/svarga.png" alt="" height="35">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <!-- Page Title -->
                    <div class="ms-3">
                        <h5 class="mb-0 text-primary"><?= $this->renderSection('page-title') ?: 'Customer Portal' ?></h5>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <!-- Customer Info -->
                    <div class="d-none d-md-flex align-items-center me-3">
                        <span class="text-muted me-2">Welcome,</span>
                        <span class="text-primary fw-semibold"><?= session('customer_name') ?></span>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="<?= getUserAvatar() ?>"
                                alt="User Avatar" style="width: 32px; height: 32px; object-fit: cover;">
                            <span class="d-none d-xl-inline-block ms-1"><?= session('customer_name') ?></span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="<?= site_url('customer-portal/profile') ?>">
                                <i class="bx bx-user font-size-16 align-middle me-1"></i>
                                <span>Profile</span>
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

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li>
                            <a href="<?= site_url('customer-portal/dashboard') ?>" class="waves-effect <?= uri_string() == 'customer-portal/dashboard' ? 'active' : '' ?>">
                                <i class="bx bx-home-circle"></i>
                                <span key="t-dashboard">Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="<?= site_url('customer-portal/invoices') ?>" class="waves-effect <?= uri_string() == 'customer-portal/invoices' ? 'active' : '' ?>">
                                <i class="bx bx-receipt"></i>
                                <span key="t-invoices">Tagihan Saya</span>
                            </a>
                        </li>

                        <li>
                            <a href="<?= site_url('customer-portal/profile') ?>" class="waves-effect <?= uri_string() == 'customer-portal/profile' ? 'active' : '' ?>">
                                <i class="bx bx-user-circle"></i>
                                <span key="t-profile">Profile Saya</span>
                            </a>
                        </li>

                        <li class="menu-title" key="t-bantuan">Bantuan</li>

                        <li>
                            <a href="#" onclick="contactWhatsApp()" class="waves-effect">
                                <i class="bx bxl-whatsapp"></i>
                                <span key="t-contact">Hubungi CS</span>
                            </a>
                        </li>

                        <li>
                            <a href="<?= site_url('customer-portal/logout') ?>" class="waves-effect">
                                <i class="bx bx-power-off"></i>
                                <span key="t-logout">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Start right Content here -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?= $this->renderSection('content') ?>
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Customer Portal.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Powered by Billing System
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottom-nav">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-3 px-1">
                    <a href="<?= site_url('customer-portal/dashboard') ?>" class="nav-link <?= uri_string() == 'customer-portal/dashboard' ? 'active' : '' ?>">
                        <i class="bx bx-home-circle"></i>
                        <span>Home</span>
                    </a>
                </div>
                <div class="col-3 px-1">
                    <a href="<?= site_url('customer-portal/invoices') ?>" class="nav-link <?= uri_string() == 'customer-portal/invoices' ? 'active' : '' ?>">
                        <i class="bx bx-receipt"></i>
                        <span>Bills</span>
                    </a>
                </div>
                <div class="col-3 px-1">
                    <a href="<?= site_url('customer-portal/profile') ?>" class="nav-link <?= uri_string() == 'customer-portal/profile' ? 'active' : '' ?>">
                        <i class="bx bx-search"></i>
                        <span>Search</span>
                    </a>
                </div>
                <div class="col-3 px-1">
                    <a href="<?= site_url('customer-portal/profile') ?>" class="nav-link <?= uri_string() == 'customer-portal/profile' ? 'active' : '' ?>">
                        <i class="bx bx-headphone"></i>
                        <span>Support</span>
                    </a>
                </div>
            </div>
        </div>
        <!-- FAB Button -->
        <button onclick="showPaymentOptions()" class="fab-button">
            <i class="bx bx-dollar"></i>
        </button>
    </div>

    <!-- JAVASCRIPT -->
    <script src="<?= base_url() ?>backend/assets/js/jquery.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/node-waves/waves.min.js"></script>

    <!-- Sweet Alerts js -->
    <script src="<?= base_url() ?>backend/assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/toastr/toastr.min.js"></script>

    <!-- App js -->
    <script src="<?= base_url() ?>backend/assets/js/app.js"></script>

    <!-- Custom Customer Portal JS -->
    <script>
        // Preloader
        $(window).on('load', function() {
            $('#preloader').addClass('preloader-fade-out');
            $('body').removeClass('preloader-active');
            setTimeout(function() {
                $('#preloader').hide();
            }, 500);
        });

        // Contact WhatsApp function
        function contactWhatsApp() {
            const phoneNumber = '6285183112127'; // Replace with actual CS number
            const message = `Halo, saya pelanggan dengan nomor layanan <?= session('customer_number') ?>. Saya ingin bertanya tentang layanan internet.`;
            const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappURL, '_blank');
        }

        // Mobile sidebar toggle
        $('#vertical-menu-btn').on('click', function(e) {
            e.preventDefault();
            $('.vertical-menu').toggleClass('show');
            $('.sidebar-overlay').toggleClass('show');
            $('body').toggleClass('sidebar-enable');
        });

        // Close sidebar when clicking overlay
        $('.sidebar-overlay').on('click', function() {
            $('.vertical-menu').removeClass('show');
            $('.sidebar-overlay').removeClass('show');
            $('body').removeClass('sidebar-enable');
        });

        // Close sidebar when clicking outside on mobile
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('.vertical-menu, #vertical-menu-btn, .sidebar-overlay').length) {
                    $('.vertical-menu').removeClass('show');
                    $('.sidebar-overlay').removeClass('show');
                    $('body').removeClass('sidebar-enable');
                }
            }
        });

        // Handle window resize
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('.vertical-menu').removeClass('show');
                $('.sidebar-overlay').removeClass('show');
                $('body').removeClass('sidebar-enable');
            }
        });

        // Auto logout on session expiry
        setInterval(function() {
            $.get('<?= site_url('customer-portal/check-session') ?>')
                .fail(function() {
                    Swal.fire({
                        title: 'Session Expired',
                        text: 'Your session has expired. Please login again.',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = '<?= site_url('customer-portal') ?>';
                    });
                });
        }, 300000); // Check every 5 minutes

        // SweetAlert2 configuration
        if (window.Swal) {
            const style = document.createElement('style');
            style.innerHTML = `
                .swal2-container { 
                    z-index: 2050 !important; 
                }
            `;
            document.head.appendChild(style);
        }

        // Show flash messages
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                title: 'Berhasil!',
                text: '<?= session()->getFlashdata('success') ?>',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?= session()->getFlashdata('error') ?>',
                icon: 'error',
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            Swal.fire({
                title: 'Info',
                text: '<?= session()->getFlashdata('info') ?>',
                icon: 'info',
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>