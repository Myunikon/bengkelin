<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Kendaraan tidak valid.');
    redirect(BASE_URL . 'kendaraan/index.php');
}

$stmt = $conn->prepare("SELECT * FROM kendaraan WHERE id_kendaraan = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$kendaraan = $result->fetch_assoc();
$stmt->close();

if (!$kendaraan) {
    setFlash('danger', 'Kendaraan tidak ditemukan.');
    redirect(BASE_URL . 'kendaraan/index.php');
}

// Ambil semua pelanggan untuk dropdown
$pelanggan_res = $conn->query("SELECT id_pelanggan, nama, no_telp FROM pelanggan ORDER BY nama ASC");

$pageTitle = 'Edit Kendaraan';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bx bx-edit-alt text-primary me-2"></i> Edit Kendaraan
        </h4>
        <p class="text-secondary small mb-0">Ubah data kendaraan: <?php echo e($kendaraan['no_polisi']); ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>kendaraan/index.php" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<!-- Flash Message -->
<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show" role="alert">
        <?php echo e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Form Card -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0 fw-semibold">
            <i class="bx bx-car me-2 text-primary"></i> Form Edit Kendaraan
        </h5>
    </div>
    <div class="card-body">
        <form action="<?php echo BASE_URL; ?>kendaraan/update.php" method="POST" novalidate>
            <input type="hidden" name="id_kendaraan" value="<?php echo $kendaraan['id_kendaraan']; ?>">

            <!-- Dropdown Pemilik -->
            <div class="mb-3">
                <label for="id_pelanggan" class="form-label fw-semibold">Pemilik (Pelanggan) <span class="text-danger">*</span></label>
                <select id="id_pelanggan" name="id_pelanggan" class="form-select" required>
                    <?php while ($p = $pelanggan_res->fetch_assoc()): ?>
                        <option value="<?php echo $p['id_pelanggan']; ?>" <?php echo ($p['id_pelanggan'] == $kendaraan['id_pelanggan']) ? 'selected' : ''; ?>>
                            <?php echo e($p['nama']); ?> (<?php echo e($p['no_telp']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- No Polisi -->
            <div class="mb-3">
                <label for="no_polisi" class="form-label fw-semibold">No. Polisi (Plat Nomor) <span class="text-danger">*</span></label>
                <input type="text" id="no_polisi" name="no_polisi" class="form-control"
                       value="<?php echo e($kendaraan['no_polisi']); ?>" required>
            </div>

            <!-- Merk & Model (2 kolom) -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="merk" class="form-label fw-semibold">Merk Kendaraan <span class="text-danger">*</span></label>
                    <input type="text" id="merk" name="merk" class="form-control"
                           value="<?php echo e($kendaraan['merk']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="model" class="form-label fw-semibold">Model / Tipe</label>
                    <input type="text" id="model" name="model" class="form-control"
                           value="<?php echo e($kendaraan['model'] ?? ''); ?>">
                </div>
            </div>

            <!-- Tahun -->
            <div class="mt-3">
                <label for="tahun" class="form-label fw-semibold">Tahun Pembuatan</label>
                <input type="number" id="tahun" name="tahun" class="form-control"
                       value="<?php echo $kendaraan['tahun'] ? (int)$kendaraan['tahun'] : ''; ?>"
                       min="1980" max="<?php echo date('Y') + 1; ?>">
                <div id="tahun-error" class="invalid-feedback"></div>
            </div>

            <!-- Tombol -->
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>kendaraan/index.php" class="btn btn-outline-secondary">
                    <i class="bx bx-x me-1"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
