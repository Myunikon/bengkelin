<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Tambah Sparepart';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Tambah Sparepart</h4>
        <span class="text-muted small">Masukkan detail suku cadang atau item baru</span>
    </div>
    <a href="<?php echo BASE_URL; ?>sparepart/index.php" class="btn btn-light border btn-sm px-3 py-2 rounded-3 text-muted">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
        <?php echo e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card stat-card mx-auto" style="max-width: 800px;">
    <div class="card-body p-4 p-md-5">
        <form action="<?php echo BASE_URL; ?>sparepart/tambah_proses.php" method="POST" novalidate>

            <?php
            $label = 'Nama Sparepart'; $name = 'nama_part'; $type = 'text'; $icon = 'bx-cog'; $placeholder = 'Masukkan nama sparepart / suku cadang'; $required = true;
            include '../includes/components/input.php';
            ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?php
                    $label = 'Jumlah Stok Awal'; $name = 'stok'; $type = 'number'; $icon = 'bx-package'; $value = '0'; $placeholder = '0'; $required = true;
                    include '../includes/components/input.php';
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    $label = 'Harga Jual Satuan (Rp)'; $name = 'harga_jual'; $type = 'number'; $icon = 'bx-money'; $placeholder = 'Contoh: 75000'; $required = true;
                    include '../includes/components/input.php';
                    ?>
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class='bx bx-save me-1'></i> Tambah Sparepart
                </button>
                <a href="<?php echo BASE_URL; ?>sparepart/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
