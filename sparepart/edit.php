<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Sparepart tidak valid.');
    redirect(BASE_URL . 'sparepart/index.php');
}

$stmt = $conn->prepare("SELECT * FROM sparepart WHERE id_sparepart = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$sparepart = $result->fetch_assoc();
$stmt->close();

if (!$sparepart) {
    setFlash('danger', 'Sparepart tidak ditemukan.');
    redirect(BASE_URL . 'sparepart/index.php');
}

$pageTitle = 'Edit Sparepart';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Edit Sparepart</h4>
        <span class="text-muted small">Ubah data item: <strong><?php echo e($sparepart['nama_part']); ?></strong></span>
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
        <form action="<?php echo BASE_URL; ?>sparepart/update.php" method="POST" novalidate>
            <input type="hidden" name="id_sparepart" value="<?php echo (int)$sparepart['id_sparepart']; ?>">

            <?php
            $label = 'Nama Sparepart'; $name = 'nama_part'; $type = 'text'; $icon = 'bx-cog'; $value = $sparepart['nama_part']; $required = true;
            include '../includes/components/input.php';
            ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?php
                    $label = 'Jumlah Stok'; $name = 'stok'; $type = 'number'; $icon = 'bx-package'; $value = $sparepart['stok']; $required = true;
                    include '../includes/components/input.php';
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    $label = 'Harga Jual Satuan (Rp)'; $name = 'harga_jual'; $type = 'number'; $icon = 'bx-money'; $value = $sparepart['harga_jual']; $required = true;
                    include '../includes/components/input.php';
                    ?>
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class='bx bx-save me-1'></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>sparepart/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
