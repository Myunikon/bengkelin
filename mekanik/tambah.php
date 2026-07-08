<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Tambah Mekanik';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Tambah Mekanik</h4>
        <span class="text-muted small">Daftarkan staf mekanik baru ke dalam sistem</span>
    </div>
    <a href="<?php echo BASE_URL; ?>mekanik/index.php" class="btn btn-light border btn-sm px-3 py-2 rounded-3 text-muted">
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
        <form action="<?php echo BASE_URL; ?>mekanik/tambah_proses.php" method="POST" novalidate>

            <?php
            $label = 'Nama Mekanik'; $name = 'nama'; $type = 'text'; $icon = 'bx-wrench'; $placeholder = 'Masukkan nama lengkap mekanik'; $required = true; $formText = 'Minimal panjang nama 3 karakter.';
            include '../includes/components/input.php';
            ?>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class='bx bx-save me-1'></i> Tambah Mekanik
                </button>
                <a href="<?php echo BASE_URL; ?>mekanik/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
