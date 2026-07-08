<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Servis tidak valid.');
    redirect(BASE_URL . 'servis/index.php');
}

// Ambil data servis utama
$stmt = $conn->prepare("
    SELECT s.*,
           k.no_polisi, k.merk, k.model, k.tahun,
           p.nama AS nama_pelanggan, p.no_telp AS telp_pelanggan, p.alamat AS alamat_pelanggan,
           m.nama AS nama_mekanik, m.status AS status_mekanik
    FROM servis s
    JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
    JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
    LEFT JOIN mekanik m ON s.id_mekanik = m.id_mekanik
    WHERE s.id_servis = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$servis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$servis) {
    setFlash('danger', 'Transaksi servis tidak ditemukan.');
    redirect(BASE_URL . 'servis/index.php');
}

// Ambil sparepart yang terpasang
$details = $conn->query("
    SELECT sd.*, sp.nama_part
    FROM servis_details sd
    JOIN sparepart sp ON sd.id_sparepart = sp.id_sparepart
    WHERE sd.id_servis = $id
");

$total_sp = 0;
$installed_parts = [];
while ($d = $details->fetch_assoc()) {
    $total_sp += $d['subtotal'];
    $installed_parts[] = $d;
}

// Query sparepart tersedia untuk dropdown
$spareparts_available = $conn->query("SELECT * FROM sparepart ORDER BY nama_part ASC");

// Query mekanik tersedia
$mekanik_available = $conn->query("SELECT * FROM mekanik WHERE status = 'tersedia' ORDER BY nama ASC");

// Query riwayat status
$timeline = $conn->query("
    SELECT * FROM riwayat_status
    WHERE id_servis = $id
    ORDER BY waktu_perubahan DESC
");

// Query foto dokumentasi
$photos = $conn->query("
    SELECT * FROM servis_foto
    WHERE id_servis = $id
    ORDER BY uploaded_at DESC
");

$pageTitle = 'Detail Servis #' . $servis['id_servis'];
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Breadcrumb & Header -->
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>servis/index.php">Servis</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail #<?= $servis['id_servis'] ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">
            <i class="bx bx-wrench text-primary me-2"></i> Servis #<?= $servis['id_servis'] ?>
        </h4>
        <p class="text-secondary small mb-0">Terdaftar pada <?= date('d M Y', strtotime($servis['tanggal_masuk'])) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>servis/index.php" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bx bx-printer me-1"></i> Cetak Invoice
        </button>
    </div>
</div>

<!-- Flash Message -->
<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Dua Kolom Utama -->
<div class="row g-4">

    <!-- Kolom Kiri -->
    <div class="col-lg-6">
        <!-- Card Info Utama -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-semibold mb-0">Informasi Kendaraan & Pelanggan</h5>
                <span class="badge bg-<?= statusColor($servis['status']) ?> bg-opacity-10 text-<?= statusColor($servis['status']) ?> fw-semibold">
                    <?= strtoupper(e($servis['status'])) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="small text-secondary mb-1">No. Polisi</div>
                        <div class="fw-bold font-monospace text-primary" style="font-size:1.15rem;"><?= e($servis['no_polisi']) ?></div>
                        <div class="small text-secondary mt-3 mb-1">Spesifikasi Kendaraan</div>
                        <div class="fw-semibold"><?= e($servis['merk']) ?> <?= e($servis['model'] ?? '') ?> (<?= $servis['tahun'] ?? '–' ?>)</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-secondary mb-1">Pelanggan (Pemilik)</div>
                        <div class="fw-semibold"><?= e($servis['nama_pelanggan']) ?></div>
                        <div class="small text-secondary mt-3 mb-1">No. Telp</div>
                        <div><?= e($servis['telp_pelanggan']) ?></div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row">
                    <div class="col-md-6">
                        <div class="small text-secondary mb-1">Mekanik Terpilih</div>
                        <?php if ($servis['id_mekanik']): ?>
                            <div><i class="bx bx-user-voice text-primary me-1"></i> <strong><?= e($servis['nama_mekanik']) ?></strong></div>
                        <?php else: ?>
                            <div class="text-danger"><i class="bx bx-error-circle me-1"></i> Belum ditentukan</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-secondary mb-1">Biaya Jasa</div>
                        <div class="fw-bold"><?= rupiah($servis['biaya_jasa']) ?></div>
                    </div>
                </div>

                <?php if ($servis['keterangan']): ?>
                    <hr class="my-3">
                    <div class="small text-secondary mb-1">Keluhan / Keterangan</div>
                    <div class="p-2 bg-light rounded-3 border">"<?= e($servis['keterangan']) ?>"</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card Sparepart Terpasang -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-semibold mb-0"><i class="bx bx-cog me-2"></i> Suku Cadang Terpasang</h5>
                <span class="fw-bold text-primary">Total: <span id="total-sparepart"><?= rupiah($total_sp) ?></span></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0" id="sparepart-table">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Sparepart</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                                <?php if (in_array($servis['status'], ['antre','dikerjakan'])): ?>
                                    <th class="text-center" width="60">Hapus</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="sparepart-tbody">
                            <?php if (empty($installed_parts)): ?>
                                <tr><td colspan="<?= in_array($servis['status'], ['antre','dikerjakan']) ? 5 : 4 ?>" class="text-center text-muted py-3">Belum ada sparepart terpasang</td></tr>
                            <?php else: ?>
                                <?php foreach ($installed_parts as $part): ?>
                                    <tr data-id-sp="<?= $part['id_sparepart'] ?>" data-id-detail="<?= $part['id_detail'] ?>">
                                        <td><?= e($part['nama_part']) ?></td>
                                        <td class="text-end"><?= (int)$part['qty'] ?></td>
                                        <td class="text-end"><?= rupiah($part['harga_satuan']) ?></td>
                                        <td class="text-end fw-semibold"><?= rupiah($part['subtotal']) ?></td>
                                        <?php if (in_array($servis['status'], ['antre','dikerjakan'])): ?>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm btn-del-sp" data-id="<?= $part['id_detail'] ?>">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Form Tambah Sparepart -->
                <?php if (in_array($servis['status'], ['antre','dikerjakan'])): ?>
                    <hr>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small">Pilih Sparepart</label>
                            <select id="sp-select" class="form-select form-select-sm">
                                <option value="">-- Pilih --</option>
                                <?php while ($sp = $spareparts_available->fetch_assoc()): ?>
                                    <option value="<?= $sp['id_sparepart'] ?>" data-stok="<?= $sp['stok'] ?>" data-harga="<?= $sp['harga_jual'] ?>">
                                        <?= e($sp['nama_part']) ?> (Stok: <?= $sp['stok'] ?>) - <?= rupiah($sp['harga_jual']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Qty</label>
                            <input type="number" id="sp-qty" class="form-control form-control-sm" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="btn-add-sparepart" data-id-servis="<?= $servis['id_servis'] ?>" class="btn btn-primary btn-sm w-100">
                                <i class="bx bx-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col">
                            <small class="text-muted" id="sp-stok-info"></small>
                            <small class="text-muted ms-3" id="sp-harga-info"></small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-lg-6">
        <!-- Card Kontrol Workflow -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title fw-semibold mb-0">Kontrol Alur Kerja</h5>
            </div>
            <div class="card-body">
                <!-- Assign Mekanik -->
                <?php if ($servis['status'] === 'antre'): ?>
                    <form action="<?= BASE_URL ?>servis/assign_mekanik.php" method="POST" class="mb-3">
                        <input type="hidden" name="id_servis" value="<?= $servis['id_servis'] ?>">
                        <div class="row g-2">
                            <div class="col">
                                <label class="form-label small">Tugaskan Mekanik</label>
                                <select name="id_mekanik" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih --</option>
                                    <?php while ($m = $mekanik_available->fetch_assoc()): ?>
                                        <option value="<?= $m['id_mekanik'] ?>" <?= ($m['id_mekanik'] == $servis['id_mekanik']) ? 'selected' : '' ?>>
                                            <?= e($m['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-auto align-self-end">
                                <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Update Status -->
                <?php
                $allowed = STATUS_TRANSITIONS[$servis['status']] ?? [];
                ?>
                <?php if (!empty($allowed)): ?>
                    <form action="<?= BASE_URL ?>servis/update_status.php" method="POST">
                        <input type="hidden" name="id_servis" value="<?= $servis['id_servis'] ?>">
                        <div class="mb-2">
                            <label class="form-label small">Perbarui Status</label>
                            <select name="status_baru" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Status --</option>
                                <?php foreach ($allowed as $next): ?>
                                    <option value="<?= $next ?>">
                                        <?= statusLabel($next) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Catatan (Opsional)</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Oli mesin sudah diganti..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">Simpan Perubahan Status</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-lock-alt me-1"></i> Alur servis selesai. Status tidak dapat diubah lagi.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card Foto Dokumentasi -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-semibold mb-0"><i class="bx bx-image me-2"></i> Dokumentasi Foto</h5>
            </div>
            <div class="card-body">
                <?php if ($photos->num_rows > 0): ?>
                    <div class="row g-2">
                        <?php while ($p = $photos->fetch_assoc()): ?>
                            <div class="col-4 col-sm-3">
                                <div class="photo-item rounded-3 overflow-hidden border" style="aspect-ratio:1; cursor:pointer;" title="Diunggah: <?= date('d/m H:i', strtotime($p['uploaded_at'])) ?>">
                                    <img src="<?= UPLOAD_URL . $p['path_file'] ?>" alt="Foto" class="w-100 h-100 object-fit-cover">
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small text-center py-3 mb-0">Belum ada dokumentasi foto.</p>
                <?php endif; ?>

                <?php if (in_array($servis['status'], ['antre','dikerjakan'])): ?>
                    <hr>
                    <form action="<?= BASE_URL ?>servis/upload_foto.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_servis" value="<?= $servis['id_servis'] ?>">
                        <div class="mb-2">
                            <label for="foto-input" class="file-label d-flex align-items-center justify-content-center gap-2 border border-dashed rounded-3 p-2 bg-light" style="cursor:pointer;">
                                <i class="bx bx-upload fs-5"></i> Pilih & Unggah Foto Baru
                            </label>
                            <input type="file" name="fotos[]" id="foto-input" class="d-none" multiple accept="image/*">
                        </div>
                        <div id="foto-names" class="small text-muted mb-2"></div>
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Mulai Unggah</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card Timeline -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title fw-semibold mb-0"><i class="bx bx-list-ul me-2"></i> Riwayat Aktivitas</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php while ($t = $timeline->fetch_assoc()): ?>
                        <div class="timeline-item d-flex gap-3">
                            <div class="timeline-dot bg-<?= statusColor($t['status_baru']) ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;">
                                <i class="bx bx-circle text-white" style="font-size:10px;"></i>
                            </div>
                            <div>
                                <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($t['waktu_perubahan'])) ?></div>
                                <div class="fw-semibold">Status diubah ke: <span class="badge bg-<?= statusColor($t['status_baru']) ?> bg-opacity-10 text-<?= statusColor($t['status_baru']) ?> fw-semibold"><?= ucfirst(e($t['status_baru'])) ?></span></div>
                                <?php if ($t['keterangan']): ?>
                                    <div class="small text-secondary fst-italic">"<?= e($t['keterangan']) ?>"</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
