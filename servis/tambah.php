<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Tambah Servis';

// Ambil semua kendaraan beserta data pemilik untuk dropdown
$kendaraan_res = $conn->query("
    SELECT k.id_kendaraan, k.no_polisi, k.merk, k.model, p.nama AS nama_pemilik
    FROM kendaraan k
    JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
    ORDER BY k.no_polisi ASC
");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Tambah Antrean Servis</h4>
        <span class="text-muted small">Buat tiket transaksi atau penugasan servis baru</span>
    </div>
    <a href="<?= BASE_URL ?>servis/index.php" class="btn btn-light border btn-sm px-3 py-2 rounded-3 text-muted">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card stat-card mx-auto" style="max-width: 800px;">
    <div class="card-body p-4 p-md-5">
        <form action="<?= BASE_URL ?>servis/tambah_proses.php" method="POST" novalidate>

            <div class="mb-4">
                <label for="id_kendaraan" class="form-label fw-medium text-secondary">Pilih Kendaraan <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3"><i class='bx bx-car'></i></span>
                    <select id="id_kendaraan" name="id_kendaraan" class="form-select border-start-0 shadow-none" required autofocus>
                        <option value="">-- Pilih Plat Nomor / Pemilik --</option>
                        <?php while ($k = $kendaraan_res->fetch_assoc()): ?>
                            <option value="<?= $k['id_kendaraan'] ?>">
                                <?= e($k['no_polisi']) ?> - <?= e($k['merk']) ?> (Pemilik: <?= e($k['nama_pemilik']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <?php
                    $label = 'Tanggal Masuk'; $name = 'tanggal_masuk'; $type = 'date'; $icon = 'bx-calendar'; $value = date('Y-m-d'); $required = true;
                    include __DIR__ . '/../includes/components/input.php';
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    $label = 'Estimasi Biaya Jasa awal (Rp)'; $name = 'biaya_jasa'; $type = 'number'; $icon = 'bx-money'; $value = '0'; $placeholder = '0'; $required = true;
                    include __DIR__ . '/../includes/components/input.php';
                    ?>
                </div>
            </div>

            <?php
            $label = 'Keluhan / Keterangan Masalah'; $name = 'keterangan'; $type = 'textarea'; $icon = 'bx-comment-detail'; $placeholder = 'Contoh: Ganti oli mesin, rem belakang berbunyi nyaring, atau mesin brebet...';
            include __DIR__ . '/../includes/components/input.php';
            ?>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                    <i class="bx bx-save me-1"></i> Simpan Transaksi
                </button>
                <a href="<?= BASE_URL ?>servis/index.php" class="btn btn-light border px-4 py-2 rounded-3 text-muted fw-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
