<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Login | Billing Tagihan Internet </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>backend/assets/images/logo-sm.png">

    <!-- Bootstrap Css -->
    <link href="<?= base_url() ?>backend/assets/login/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?= base_url() ?>backend/assets/login/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- Remix Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Material Design Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <!-- App Css-->
    <link href="<?= base_url() ?>backend/assets/login/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <!-- Login Ornaments CSS -->
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
        }

        .account-pages {
            position: relative;
            z-index: 1;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 60px rgba(52, 152, 219, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
        }

        .bg-primary-subtle {
            background: linear-gradient(135deg, #e3f2fd 0%, #b3e0fc 100%) !important;
        }
    </style>

    <!-- App js -->

</head>

<body>
    <!-- ...existing code... -->

    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary-subtle">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Welcome Back !</h5>
                                        <p>Login untuk melanjutkan.</p>
                                        <img src="<?= base_url() ?>backend/assets/images/difihome.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="<?= base_url() ?>backend/assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <!-- <div class="auth-logo">
                                <a href="index.html" class="auth-logo-dark">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="<?= base_url() ?>backend/assets/images/logo-sm.png" alt="" class="rounded-circle" height="45">
                                        </span>
                                    </div>
                                </a>
                            </div> -->
                            <?php if (session()->getFlashdata('error')) : ?>
                                <div class="alert alert-danger alert-border-left alert-dismissible fade show mb-xl-0 material-shadow" role="alert">
                                    <i class="ri-error-warning-line me-3 align-middle fs-16"></i>Gagal,
                                    <?= session()->getFlashdata('error') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <div class="p-2">
                                <form class="form-horizontal" method="POST" action="<?= site_url('auth/loginProcess') ?>">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Email</label>
                                        <input type="text" class="form-control" id="username" name="email_user" placeholder="Enter username">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input name="password" type="password" class="form-control" id="password-input" placeholder="Enter password" aria-label="Password" aria-describedby="password-addon">
                                            <button class="btn btn-light" type="button" id="password-addon">
                                                <i class="ri-eye-line" id="password-icon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-check">
                                        <label class="form-check-label" for="remember-check">
                                            Remember me
                                        </label>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Log In</button>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <a href="auth-recoverpw.html" class="text-muted"><i class="ri-lock-line me-1"></i> Forgot your password?</a>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="mt-5 text-center">

                        <div>
                            <p>Don't have an account ? <a href="auth-register.html" class="fw-medium text-primary"> Signup now </a> </p>
                            <p>© <script>
                                    document.write(new Date().getFullYear())
                                </script> by Selaras Digital Synergy</p>
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
            const passwordInput = document.getElementById('password-input');
            const passwordToggle = document.getElementById('password-addon');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordToggle && passwordInput && passwordIcon) {
                passwordToggle.addEventListener('click', function() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        passwordIcon.className = 'ri-eye-off-line';
                    } else {
                        passwordInput.type = 'password';
                        passwordIcon.className = 'ri-eye-line';
                    }
                });
            }
        });
    </script>
    <script src="<?= base_url() ?>backend/assets/login/js/app-login.js"></script>

    <!-- Fallback password toggle script -->
    <script>
        (function() {
            var passwordInput = document.getElementById('password-input');
            var passwordToggle = document.getElementById('password-addon');
            var passwordIcon = document.getElementById('password-icon');
            if (passwordToggle && passwordInput && passwordIcon) {
                passwordToggle.addEventListener('click', function() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        passwordIcon.className = 'ri-eye-off-line';
                    } else {
                        passwordInput.type = 'password';
                        passwordIcon.className = 'ri-eye-line';
                    }
                });
            }
        })();
    </script>
</body>

</html>