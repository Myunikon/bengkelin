<?php

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' – ' : ''; ?><?php echo APP_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- CSS Kustom -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';

        // Cegah flash sidebar saat load (dari localStorage)
        (function () {
            if (localStorage.getItem('sidebar_state') === 'closed') {
                document.documentElement.classList.add('sidebar-pre-closed');
            }
        })();
    </script>
    <style>
        /* Flash prevention */
        html.sidebar-pre-closed #sidebar    { width: var(--sidebar-col-w, 68px) !important; }
        html.sidebar-pre-closed #main-content { margin-left: var(--sidebar-col-w, 68px) !important; }
        html.sidebar-pre-closed .navbar       { margin-left: 68px !important; }

        body { padding-top: 64px; } /* tinggi navbar */
    </style>
</head>
<body>

<!-- ===== NAVBAR (Bootstrap) ===== -->
<nav class="navbar navbar-expand-md navbar-light bg-white fixed-top shadow-sm">
    <div class="container-fluid px-3">

        <!-- Tombol Menu Mobile & Brand (hanya muncul di layar HP/Tablet) -->
        <button id="mobile-sidebar-toggle" class="btn btn-light border btn-sm d-md-none me-2" title="Buka Menu">
            <i class="bx bx-menu fs-4"></i>
        </button>
        <span class="fw-semibold d-md-none fs-6 text-dark mb-0">Bengkelin</span>

        <!-- Bagian kanan (user info, logout) -->
        <div class="d-flex align-items-center gap-2 ms-auto">

            <!-- Flash message (jika ada) -->
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show mb-0 py-1 px-3" role="alert" style="font-size:0.8rem; max-width:500px;">
                    <?php echo !empty($flash['raw']) ? $flash['message'] : e($flash['message']); ?>
                    <button type="button" class="btn-close p-1" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- User info -->
            <div class="d-flex align-items-center bg-light rounded-pill px-3 py-1">
                <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:30px;height:30px;font-size:0.7rem;font-weight:700;">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </span>
                <span class="small fw-medium"><?php echo e($_SESSION['username'] ?? 'Admin'); ?></span>
            </div>

            <!-- Logout -->
            <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bx bx-log-out me-1"></i> Logout
            </a>
        </div>

    </div>
</nav>

<!-- ===== WRAPPER (konten utama + sidebar) ===== -->
<div class="wrapper">
