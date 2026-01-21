<!doctype html>
<html lang="en">

<head>

  <meta charset="utf-8" />
  <title>Dashboard | Billing Tagihan Internet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Aplikasi Billing Tagihan Internet untuk manajemen pelanggan, pembayaran, dan monitoring layanan ISP. Mudah, cepat, dan aman untuk kebutuhan administrasi internet Anda.">
  <meta name="keywords" content="billing internet, tagihan internet, ISP, manajemen pelanggan, pembayaran online, monitoring layanan, aplikasi billing, difihome, kimo jaringan, selaras digital synergy">
  <meta name="robots" content="index, follow">
  <meta name="author" content="Selaras Digital Synergy">
  <!-- CSRF Token for AJAX -->
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-token-name" content="<?= csrf_token() ?>">
  <!-- App favicon -->
  <link rel="shortcut icon" href="<?= base_url() ?>backend/assets/images/favicon.png">
  <link rel="stylesheet" href="<?= base_url() ?>backend/assets/css/prettify.css">
  <link href="<?= base_url() ?>backend/assets/libs/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
  <!-- Sweet Alert-->
  <link href="<?= base_url() ?>backend/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" type="text/css" href="<?= base_url() ?>backend/assets/libs/toastr/toastr.min.css">
  <!-- DataTables -->
  <link href="<?= base_url() ?>backend/assets/libs/datatables/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="<?= base_url() ?>backend/assets/libs/datatables/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

  <!-- Leaflet CSS for maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <!-- Bootstrap Css -->
  <link href="<?= base_url() ?>backend/assets/css/custom.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
  <link href="<?= base_url() ?>backend/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
  <!-- Icons Css -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="<?= base_url() ?>backend/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <!-- Remix Icon CDN for ri-* icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <!-- Boxicons CDN for bx-* icons -->
  <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet" />
  <style>
    html,
    body {
      font-family: 'Lexend Deca', ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }
  </style>
  <!-- App Css-->
  <link href="<?= base_url() ?>backend/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
  <!-- Custom Dashboard CSS -->
  <link href="<?= base_url() ?>backend/assets/css/custom-dashboard.css" rel="stylesheet" type="text/css" />
  <!-- Image Fallback CSS -->
  <link href="<?= base_url() ?>backend/assets/css/image-fallback.css" rel="stylesheet" type="text/css" />
  <!-- Custom Menu CSS -->
  <link href="<?= base_url() ?>backend/assets/css/custom-menu.css" rel="stylesheet" type="text/css" />
  <!-- Menu Alignment CSS -->
  <link href="<?= base_url() ?>backend/assets/css/menu-alignment.css" rel="stylesheet" type="text/css" />
  <!-- Menu Final Fixes CSS -->
  <link href="<?= base_url() ?>backend/assets/css/menu-final-fixes.css" rel="stylesheet" type="text/css" />
  <!-- Enhanced Preloader CSS -->
  <link href="<?= base_url() ?>backend/assets/css/preloader-fixes.css" rel="stylesheet" type="text/css" />

  <!-- Preloader CSS -->
  <style>
    @keyframes pulse {

      0%,
      100% {
        transform: scale(0.8);
        opacity: 0.5;
      }

      50% {
        transform: scale(1.2);
        opacity: 1;
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

    /* Prevent scrolling when preloader is active */
    body.preloader-active {
      overflow: hidden;
    }

    /* Global Table Header Gradient Styling */
    .table thead.table-dark,
    .table thead.table-light,
    .table thead {
      background: linear-gradient(to right, #2751a0ff 0%, #1fabecff 100%) !important;
    }

    .table thead.table-dark th,
    .table thead.table-light th,
    .table thead th {
      color: #ffffff !important;
      font-weight: 500 !important;
      text-transform: capitalize;
      font-size: 0.90rem;
      letter-spacing: 0.3px;
      border-color: rgba(255, 255, 255, 0.1) !important;
      padding: 0.75rem 0.75rem;
      vertical-align: middle;
      background: transparent !important;
    }

    /* Sorting indicators styling */
    .table thead th.sorting:before,
    .table thead th.sorting_asc:before,
    .table thead th.sorting_desc:before,
    .table thead th.sorting:after,
    .table thead th.sorting_asc:after,
    .table thead th.sorting_desc:after {
      color: rgba(255, 255, 255, 0.7) !important;
    }
  </style>

  <!-- App js -->
  <!-- <script src="<?= base_url() ?>backend/assets/js/plugin.js"></script> -->


</head>

<body data-sidebar="light" class="preloader-active">


  <!-- Preloader -->
  <div id="preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); z-index: 9999; display: flex; justify-content: center; align-items: center;">
    <div class="preloader-content" style="text-align: center;">
      <div class="preloader-logo" style="width: 80px; height: 80px; margin: 0 auto 20px; animation: pulse 1.5s ease-in-out infinite; border-radius: 50%; border: 8px solid #667eea; background: transparent; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);">
      </div>
      <div class="preloader-text" style="font-size: 14px; color: #6b7280; font-weight: 500;">Loading...</div>
    </div>
  </div>

  <!-- <body data-layout="horizontal" data-topbar="dark"> -->

  <!-- Begin page -->
  <div id="layout-wrapper">


    <header id="page-topbar">
      <div class="navbar-header">
        <div class="d-flex align-items-center">
          <!-- LOGO -->
          <div class="navbar-brand-box horizontal-logo">
            <a href="index.html" class="logo logo-dark">
              <span class="logo-sm">
                <img src="<?= base_url() ?>backend/assets/images/logo-sm.png" alt="" height="30">
              </span>
              <span class="logo-lg">
                <img src="<?= base_url() ?>backend/assets/images/difihome.png" alt="" height="35">
              </span>
            </a>

            <a href="index.html" class="logo logo-light">
              <span class="logo-sm">
                <img src="<?= base_url() ?>backend/assets/images/logo-sm.png" alt="" height="30">
              </span>
              <span class="logo-lg">
                <img src="<?= base_url() ?>backend/assets/images/difihome.png" alt="" height="35">
              </span>
            </a>
          </div>
          <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
            <i class="fa fa-fw fa-bars"></i>
          </button>

          <!-- App Search-->
          <form class="app-search d-none d-lg-block">
            <div class="position-relative">
              <input type="text" class="form-control" placeholder="Search..." style="padding-left: 2.2rem;">
              <span class="bx bx-search-alt" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; color: #888;"></span>
            </div>
          </form>
        </div>

        <div class="d-flex">

          <div class="dropdown d-inline-block d-lg-none ms-2">
            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
              data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="mdi mdi-magnify"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
              aria-labelledby="page-header-search-dropdown">

              <form class="p-3">
                <div class="form-group m-0">
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="dropdown d-none d-lg-inline-block ms-1">
            <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
              <i class="bx bx-fullscreen"></i>
            </button>
          </div>



          <div class="dropdown d-inline-block">
            <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
              data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img class="rounded-circle header-profile-user" src="<?= getUserAvatar() ?>"
                alt="User Avatar" style="width: 32px; height: 32px; object-fit: contain; border: 1px solid #dee2e6;">
              <span class="d-none d-xl-inline-block ms-1" key="t-henry"></span>
              <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <!-- item-->
              <a class="dropdown-item text-danger" href="<?= site_url('auth/logout') ?>">
                <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                <span key="t-logout">Logout</span>
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
            <?= $this->include('layout/menu') ?>
          </ul>
        </div>
        <!-- Sidebar -->
      </div>
    </div>
    <!-- Left Sidebar End -->

    <!-- Start right Content here -->
    <script src="<?= base_url() ?>backend/assets/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/libs/toastr/toastr.min.js"></script>
    <script src="<?= base_url() ?>backend/assets/js/toastr.init.js"></script>
    <!-- Sweet Alerts js -->
    <script src="<?= base_url() ?>backend/assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- Sweet alert init js-->
    <script src="<?= base_url() ?>backend/assets/js/sweet-alerts.init.js"></script>

    <div class="main-content">

      <?= $this->renderSection('content') ?>

      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <script>
                document.write(new Date().getFullYear())
              </script> © Selaras Digital Synergy.
            </div>
            <div class="col-sm-6">
              <div class="text-sm-end d-none d-sm-block">
                Design & Develop by Afikrfkn
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <!-- end main content-->

  </div>
  <!-- END layout-wrapper -->


  <!-- JAVASCRIPT -->
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.js"></script>
  <script>
    // Always show SweetAlert2 above Bootstrap modal, and center vertically in viewport
    if (window.Swal) {
      // Always set z-index and vertical centering for SweetAlert2
      document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.innerHTML = `
          .swal2-container { 
            z-index: 2050 !important; 
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
          }
          .swal2-popup { 
            position: relative !important;
            top: auto !important; 
            left: auto !important; 
            transform: none !important; 
            margin: 0 auto !important;
            max-width: 90vw !important;
            max-height: 90vh !important;
          }
          body.modal-open .swal2-container { z-index: 2050 !important; }
          .swal2-backdrop-show { background: rgba(0, 0, 0, 0.4) !important; }
          
          /* Additional positioning fixes for all screen sizes */
          @media (max-width: 768px) {
            .swal2-popup {
              max-width: 95vw !important;
              margin: 10px !important;
            }
          }
          
          /* Fix for loading state */
          .swal2-loading .swal2-popup {
            position: relative !important;
          }
        `;
        document.head.appendChild(style);
      });

      // Set default configurations for all Swal instances
      Swal.mixin({
        customClass: {
          container: 'swal2-container-center'
        },
        position: 'center',
        backdrop: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true
      });

      // Force focus to Swal when open, so not trapped in modal
      document.addEventListener('shown.bs.modal', function() {
        setTimeout(function() {
          const swal = document.querySelector('.swal2-container');
          if (swal) swal.focus();
        }, 100);
      });
    }
  </script>
  <!-- Jika ingin pakai lokal, ganti baris di atas dengan baris di bawah ini dan pastikan file sudah asli -->
  <!-- <script src="<?= base_url() ?>backend/assets/js/sweetalert2.min.js"></script> -->
  <script src="<?= base_url() ?>backend/assets/libs/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/metismenu/metisMenu.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/simplebar/simplebar.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/node-waves/waves.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/datepicker/js/bootstrap-datepicker.min.js"></script>
  <!--datatable js-->
  <script src="<?= base_url() ?>backend/assets/libs/datatables/js/jquery.dataTables.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/datatables/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/datatables/js/datatables.init.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/datatables/js/dataTables.buttons.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/libs/datatables/js/buttons.bootstrap4.min.js"></script>
  <!-- toastr plugin -->
  <script src="<?= base_url() ?>backend/assets/libs/toastr/toastr.min.js"></script>
  <!-- apexcharts -->
  <script src="<?= base_url() ?>backend/assets/libs/apexcharts/apexcharts.min.js"></script>
  <!-- fontawesome icons init -->
  <!-- jquery step -->
  <!-- form wizard init -->
  <script src="<?= base_url() ?>backend/assets/js/fontawesome.init.js"></script>

  <!-- dashboard init -->
  <!-- <script src="<?= base_url() ?>backend/assets/js/dashboard.init.js"></script> -->
  <!-- toastr init -->
  <script src="<?= base_url() ?>backend/assets/js/toastr.init.js"></script>
  <!--select2 cdn-->
  <!-- App js -->
  <script src="<?= base_url() ?>backend/assets/js/select2.init.js"></script>
  <script src="<?= base_url() ?>backend/assets/js/parsley.min.js"></script>
  <script src="<?= base_url() ?>backend/assets/js/form-validation.init.js"></script>
  <script src="<?= base_url() ?>backend/assets/js/app.js"></script>
  <script src="<?= base_url() ?>backend/assets/js/custom.js"></script>
  <!-- Custom menu JavaScript -->
  <script src="<?= base_url() ?>backend/assets/js/custom-menu.js"></script>


  <!-- Enhanced Preloader & Form Management -->
  <script src="<?= base_url() ?>backend/assets/js/preloader-manager.js"></script>
  <script src="<?= base_url() ?>backend/assets/js/form-handler.js"></script>

  <script>
    // Legacy function support untuk backward compatibility
    // Tetapi jangan panggil window.showPreloader karena akan recursive
    function showPreloader(requestId) {
      // Do nothing or show a simple preloader if needed
      // Jangan panggil window.showPreloader() karena akan infinite loop
      console.log('showPreloader called with requestId:', requestId);
    }

    function hidePreloader(requestId) {
      // Do nothing or hide preloader if needed
      console.log('hidePreloader called with requestId:', requestId);
    }

    // Enhanced preloader with better modal handling
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Enhanced preloader system loaded');

      // Handle navigation links
      document.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' &&
          !e.target.getAttribute('href').startsWith('#') &&
          !e.target.hasAttribute('data-bs-toggle') &&
          !e.target.closest('.modal')) {
          showPreloader('navigation');
        }
      });

      // Handle regular form submissions (non-AJAX)
      document.addEventListener('submit', function(e) {
        const form = e.target;
        const formId = form.id || '';
        const isAjaxForm = form.hasAttribute('data-ajax') ||
          form.classList.contains('ajax-form') ||
          formId.includes('GroupProfile') ||
          formId.includes('PackageProfile') ||
          formId.includes('Form');

        if (!isAjaxForm && !form.closest('.modal')) {
          showPreloader('form_submit');
        }
      });
    });
  </script>

  <!-- Menu alignment fix script -->
  <script src="<?= base_url() ?>backend/assets/js/menu-alignment-fix.js"></script>
  <!-- Menu alignment helper script -->
  <script src="<?= base_url() ?>backend/assets/js/menu-alignment-helper.js"></script>
  <!-- Menu dropdown fix script -->
  <script src="<?= base_url() ?>backend/assets/js/menu-dropdown-fix.js"></script>

  <!-- Render scripts section -->
  <?= $this->renderSection('scripts') ?>

  <?= $this->renderSection('javascript') ?>

  <?= $this->renderSection('js') ?>

  <!-- Fallback: force hide preloader after 3 seconds -->
  <script>
    window.addEventListener('load', function() {
      setTimeout(function() {
        var preloader = document.getElementById('preloader');
        if (preloader && preloader.style.display !== 'none') {
          preloader.classList.add('preloader-fade-out');
          setTimeout(function() {
            preloader.style.display = 'none';
            preloader.style.opacity = '0';
            preloader.style.visibility = 'hidden';
            document.body.classList.remove('preloader-active');
            preloader.classList.remove('preloader-fade-out');
          }, 600);
        }
      }, 3000);
    });
  </script>
</body>

</html>