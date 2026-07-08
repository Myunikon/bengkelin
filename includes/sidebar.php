<aside class="sidebar d-flex flex-column" id="sidebar">

    <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-2">
        <span class="sidebar-title fw-semibold fs-6">Bengkelin</span>
        <button id="sidebar-toggle" class="btn btn-sm border-0" title="Toggle Sidebar">
            <i class="bx bx-menu fs-4"></i>
        </button>
    </div>

    <nav class="nav flex-column flex-grow-1 px-2 py-3">
        <a href="<?php echo BASE_URL; ?>dashboard.php"
           class="nav-link <?php echo isActive('root', 'dashboard.php'); ?>"
           data-bs-toggle="tooltip"
           title="Dashboard">
            <i class="bx bx-grid-alt me-2"></i>
            <span class="menu-text">Dashboard</span>
        </a>

        <div class="sidebar-divider"></div>

        <div class="sidebar-heading">Master Data</div>

        <a href="<?php echo BASE_URL; ?>pelanggan/index.php"
           class="nav-link <?php echo isActive('pelanggan'); ?>"
           data-bs-toggle="tooltip"
           title="Pelanggan">
            <i class="bx bx-user me-2"></i>
            <span class="menu-text">Pelanggan</span>
        </a>

        <a href="<?php echo BASE_URL; ?>kendaraan/index.php"
           class="nav-link <?php echo isActive('kendaraan'); ?>"
           data-bs-toggle="tooltip"
           title="Kendaraan">
            <i class="bx bx-car me-2"></i>
            <span class="menu-text">Kendaraan</span>
        </a>

        <a href="<?php echo BASE_URL; ?>mekanik/index.php"
           class="nav-link <?php echo isActive('mekanik'); ?>"
           data-bs-toggle="tooltip"
           title="Mekanik">
            <i class="bx bx-wrench me-2"></i>
            <span class="menu-text">Mekanik</span>
        </a>

        <a href="<?php echo BASE_URL; ?>sparepart/index.php"
           class="nav-link <?php echo isActive('sparepart'); ?>"
           data-bs-toggle="tooltip"
           title="Sparepart">
            <i class="bx bx-cog me-2"></i>
            <span class="menu-text">Sparepart</span>
        </a>

        <div class="sidebar-divider"></div>

        <div class="sidebar-heading">Transaksi</div>

        <a href="<?php echo BASE_URL; ?>servis/index.php"
           class="nav-link <?php echo isActive('servis'); ?>"
           data-bs-toggle="tooltip"
           title="Servis">
            <i class="bx bx-task me-2"></i>
            <span class="menu-text">Servis</span>
        </a>

        <div class="sidebar-divider"></div>

        <div class="sidebar-heading">Laporan</div>

        <a href="<?php echo BASE_URL; ?>laporan/pendapatan.php"
           class="nav-link <?php echo isActive('laporan', 'pendapatan.php'); ?>"
           data-bs-toggle="tooltip"
           title="Pendapatan">
            <i class="bx bx-money me-2"></i>
            <span class="menu-text">Pendapatan</span>
        </a>

        <a href="<?php echo BASE_URL; ?>laporan/mekanik.php"
           class="nav-link <?php echo isActive('laporan', 'mekanik.php'); ?>"
           data-bs-toggle="tooltip"
           title="Kinerja Mekanik">
            <i class="bx bx-bar-chart-square me-2"></i>
            <span class="menu-text">Kinerja Mekanik</span>
        </a>

        <a href="<?php echo BASE_URL; ?>laporan/sparepart.php"
           class="nav-link <?php echo isActive('laporan', 'sparepart.php'); ?>"
           data-bs-toggle="tooltip"
           title="Pemakaian Sparepart">
            <i class="bx bx-package me-2"></i>
            <span class="menu-text">Pemakaian Sparepart</span>
        </a>

        <a href="<?php echo BASE_URL; ?>laporan/pelanggan.php"
           class="nav-link <?php echo isActive('laporan', 'pelanggan.php'); ?>"
           data-bs-toggle="tooltip"
           title="Pelanggan Baru">
            <i class="bx bx-group me-2"></i>
            <span class="menu-text">Pelanggan Baru</span>
        </a>
    </nav>
</aside>

<main class="main-content" id="main-content">
