<?php
session_start();
require 'config.php';

if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . 'dashboard.php');
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – <?php echo APP_NAME; ?></title>

    <!-- Bootstrap 5 (CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Ikon Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- (Opsional) CSS minimal untuk override / efek unik -->
    <style>
        /* Hanya dua hal yang tidak tersedia di Bootstrap: font-family dan dekorasi garis lengkung */
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Efek lingkaran samar (dapat dihapus jika tidak ingin) */
        .login-sidebar::before {
            content: "";
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 40%;
            pointer-events: none;
        }
        .login-sidebar {
            position: relative;
            overflow: hidden;
        }
        /* Agar form tidak terlalu lebar */
        .login-form {
            max-width: 420px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">

        <!-- Kolom kiri (branding) – menggunakan class Bootstrap -->
        <div class="col-lg-6 d-none d-lg-flex login-sidebar bg-primary bg-gradient align-items-center justify-content-center p-5 text-white">
            <div class="position-relative" style="max-width: 480px; z-index: 2;">
                <div class="mb-4">
                    <i class="bx bx-wrench display-2"></i>
                </div>
                <h1 class="display-4 fw-bold mb-3"><?php echo APP_NAME; ?>!</h1>
                <p class="fs-5 opacity-75 lh-base">
                    Kelola manajemen kendaraan dengan sistem
                </p>
            </div>
        </div>

        <!-- Kolom kanan (form login) -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-sm-5">
            <div class="login-form">

                <!-- Header form -->
                <div class="mb-5">
                    <div class="h4 fw-bold text-dark d-flex align-items-center mb-4">
                        <i class="bx bx-wrench text-primary me-2"></i> <?php echo APP_NAME; ?>
                    </div>
                    <h2 class="fw-bold text-dark mb-2">Selamat Datang</h2>
                    <p class="text-secondary small">
                       Masuk untuk mengakses halaman dashboard
                    </p>
                </div>

                <!-- Error alert (menggunakan Bootstrap alert) -->
                <?php if ($error === '1'): ?>
                <div class="alert alert-danger border-0 bg-danger-subtle text-danger rounded-3 small mb-4" role="alert">
                    <i class="bx bx-error-circle me-1 align-middle"></i> Username atau password salah.
                </div>
                <?php endif; ?>

                <!-- Form login -->
                <form action="<?php echo BASE_URL; ?>login_proses.php" method="POST" novalidate>
                    <input type="hidden" name="redirect" value="<?php echo e($_GET['redirect'] ?? ''); ?>">
                    <div class="mb-4">
                        <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-control py-2"
                               placeholder="Masukkan username Anda"
                               required
                               autofocus
                               autocomplete="username">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control py-2"
                               placeholder="Masukkan password Anda"
                               required
                               autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-medium rounded-3 shadow-sm">
                        Masuk
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

<!-- Script Bootstrap (jQuery tidak wajib untuk Bootstrap 5) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
