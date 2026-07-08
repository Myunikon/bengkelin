<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Tambah Kendaraan';
$pelanggan_res = $conn->query("SELECT id_pelanggan, nama, no_telp FROM pelanggan ORDER BY nama ASC");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Tambah Kendaraan Baru</h4>
        <span class="text-muted small">Daftarkan kendaraan pelanggan ke dalam sistem</span>
    </div>
    <a href="<?php echo BASE_URL; ?>kendaraan/index.php" class="btn btn-light border btn-sm px-3 py-2 rounded-3 text-muted">
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
        <form action="<?php echo BASE_URL; ?>kendaraan/tambah_proses.php" method="POST" novalidate>

            <div class="mb-4">
                <label for="id_pelanggan" class="form-label fw-medium text-secondary">Pemilik (Pelanggan) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3"><i class='bx bx-user-pin'></i></span>
                    <select id="id_pelanggan" name="id_pelanggan" class="form-select border-start-0 shadow-none" required autofocus>
                        <option value="">-- Pilih Pemilik --</option>
                        <?php while ($p = $pelanggan_res->fetch_assoc()): ?>
                            <option value="<?php echo $p['id_pelanggan']; ?>">
                                <?php echo e($p['nama']); ?> (<?php echo e($p['no_telp']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <?php
            $label = 'No. Polisi (Plat Nomor)'; $name = 'no_polisi'; $type = 'text'; $icon = 'bx-id-card'; $placeholder = 'Contoh: DD 1234 XX'; $required = true;
            include '../includes/components/input.php';

            $label = 'Merk Kendaraan'; $name = 'merk'; $type = 'text'; $icon = 'bx-purchase-tag'; $placeholder = 'Contoh: Honda, Toyota, Yamaha'; $required = true;
            include '../includes/components/input.php';

            $label = 'Model / Tipe'; $name = 'model'; $type = 'text'; $icon = 'bx-car'; $placeholder = 'Contoh: Vario 150, Avanza Veloz';
            include '../includes/components/input.php';

            $label = 'Tahun Pembuatan'; $name = 'tahun'; $type = 'number'; $icon = 'bx-calendar'; $placeholder = 'Contoh: 2018'; $formText = 'Tahun pembuatan minimal tahun 1980.';
            include '../includes/components/input.php';
            ?>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class='bx bx-save me-1'></i> Daftarkan Kendaraan
                </button>
                <a href="<?php echo BASE_URL; ?>kendaraan/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
