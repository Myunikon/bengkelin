<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID mekanik tidak valid.');
    redirect(BASE_URL . 'mekanik/index.php');
}

$stmt = $conn->prepare("SELECT * FROM mekanik WHERE id_mekanik = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$mekanik = $result->fetch_assoc();
$stmt->close();

if (!$mekanik) {
    setFlash('danger', 'Mekanik tidak ditemukan.');
    redirect(BASE_URL . 'mekanik/index.php');
}

$pageTitle = 'Edit Mekanik';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Edit Mekanik</h4>
        <span class="text-muted small">Ubah data operasional mekanik: <strong><?php echo e($mekanik['nama']); ?></strong></span>
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
        <form action="<?php echo BASE_URL; ?>mekanik/update.php" method="POST" novalidate>
            <input type="hidden" name="id_mekanik" value="<?php echo (int)$mekanik['id_mekanik']; ?>">

            <?php
            $label = 'Nama Mekanik'; $name = 'nama'; $type = 'text'; $icon = 'bx-wrench'; $value = $mekanik['nama']; $required = true;
            include '../includes/components/input.php';
            ?>

            <div class="mb-5">
                <label for="status" class="form-label fw-medium text-secondary">Status Operasional <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3"><i class='bx bx-git-commit'></i></span>
                    <select id="status" name="status" class="form-select border-start-0 shadow-none" required>
                        <option value="tersedia" <?php echo ($mekanik['status'] === 'tersedia') ? 'selected' : ''; ?>>Tersedia (Ready)</option>
                        <option value="sibuk"    <?php echo ($mekanik['status'] === 'sibuk')    ? 'selected' : ''; ?>>Sibuk (Sedang Bekerja)</option>
                        <option value="nonaktif" <?php echo ($mekanik['status'] === 'nonaktif') ? 'selected' : ''; ?>>Nonaktif (Cuti/Resign)</option>
                    </select>
                </div>
                <div class="form-text small mt-1 text-muted">Gunakan status "Nonaktif" untuk membekukan akun mekanik tanpa menghapus riwayat datanya.</div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class='bx bx-save me-1'></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>mekanik/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
