<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Pelanggan tidak valid.');
    redirect(BASE_URL . 'pelanggan/index.php');
}

$stmt = $conn->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$pelanggan = $result->fetch_assoc();
$stmt->close();

if (!$pelanggan) {
    setFlash('danger', 'Pelanggan tidak ditemukan.');
    redirect(BASE_URL . 'pelanggan/index.php');
}

$pageTitle = 'Edit Pelanggan';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Edit Pelanggan</h4>
        <span class="text-muted small">Ubah data pelanggan: <strong><?php echo e($pelanggan['nama']); ?></strong></span>
    </div>
    <a href="<?php echo BASE_URL; ?>pelanggan/index.php" class="btn btn-light border btn-sm px-3 py-2 rounded-3 text-muted">
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
        <form action="<?php echo BASE_URL; ?>pelanggan/update.php" method="POST" novalidate>
            <input type="hidden" name="id_pelanggan" value="<?php echo $pelanggan['id_pelanggan']; ?>">

            <div class="mb-4">
                <label for="nama" class="form-label fw-medium text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3">
                        <i class='bx bx-user'></i>
                    </span>
                    <input type="text" id="nama" name="nama" class="form-control border-start-0 shadow-none ps-0"
                           value="<?php echo e($pelanggan['nama']); ?>" minlength="3" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="no_telp" class="form-label fw-medium text-secondary">No. Telepon (WhatsApp) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3">
                        <i class='bx bx-phone'></i>
                    </span>
                    <input type="text" id="no_telp" name="no_telp" class="form-control border-start-0 shadow-none ps-0"
                           value="<?php echo e($pelanggan['no_telp']); ?>" pattern="[0-9]{10,14}" required>
                </div>
                <div class="form-text small mt-1 text-muted">Hanya angka, panjang 10 sampai 14 digit.</div>
            </div>

            <div class="mb-5">
                <label for="alamat" class="form-label fw-medium text-secondary">Alamat Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3 align-items-start pt-2">
                        <i class='bx bx-map'></i>
                    </span>
                    <textarea id="alamat" name="alamat" class="form-control border-start-0 shadow-none ps-0" rows="3"><?php echo e($pelanggan['alamat'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class='bx bx-save me-1'></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>pelanggan/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
