<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';
require __DIR__ . '/../includes/components/report_helper.php';

$pageTitle = 'Laporan Pemakaian Sparepart';

// Ambil data periode (rentang tanggal), lalu extract jadi variabel biasa
$periode = initReportPeriod();
$tgl_awal    = $periode['tgl_awal'];
$tgl_akhir   = $periode['tgl_akhir'];
$preset      = $periode['preset'];
$periodeText = $periode['periodeText'];

// Query disesuaikan: pakai rentang tanggal, bukan bulan/tahun
$stmt = $conn->prepare("
    SELECT sp.id_sparepart, sp.nama_part, sp.stok AS stok_sekarang,
           SUM(sd.qty) AS total_terpakai, AVG(sd.harga_satuan) AS avg_harga, SUM(sd.subtotal) AS total_nilai
    FROM sparepart sp
    JOIN servis_details sd ON sp.id_sparepart = sd.id_sparepart
    JOIN servis s ON sd.id_servis = s.id_servis
    WHERE s.tanggal_masuk BETWEEN ? AND ? AND s.status IN ('selesai', 'diambil')
    GROUP BY sp.id_sparepart, sp.nama_part, sp.stok
    ORDER BY total_terpakai DESC
");
$stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

include '../includes/header.php';
include '../includes/sidebar.php';

$title = 'Laporan Pemakaian Sparepart';
$icon = 'bx-package';
// $periodeText sudah didapat dari $periode, tidak perlu dibuat ulang

include '../includes/components/report_header.php';
include '../includes/components/report_filter.php';

$headers = [
    ['label' => 'No', 'width' => 60],
    ['label' => 'Nama Sparepart'],
    ['label' => 'Total Terpakai', 'width' => 150, 'center' => true],
    ['label' => 'Rata-rata Harga', 'width' => 160],
    ['label' => 'Total Nilai Pemakaian', 'width' => 200],
    ['label' => 'Stok Saat Ini', 'width' => 150, 'center' => true]
];

$rows = [];
$no = 1;
while ($row = $result->fetch_assoc()) {
    $stok = (int)$row['stok_sekarang'];
    if ($stok <= 0) $stokColor = 'danger';
    elseif ($stok <= 5) $stokColor = 'warning';
    else $stokColor = 'success';

    $stokBadge = '<div class="text-center"><span class="badge bg-' . $stokColor . ' bg-opacity-10 text-' . $stokColor . ' fw-semibold px-3 py-2 border rounded-pill">' . $stok . ' unit</span></div>';
    $terpakaiText = '<div class="text-center fw-bold text-dark">' . (int)$row['total_terpakai'] . ' unit</div>';

    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => e($row['nama_part']), 'class' => 'fw-semibold text-dark'],
        ['content' => $terpakaiText],
        ['content' => rupiah($row['avg_harga'])],
        ['content' => rupiah($row['total_nilai']), 'class' => 'fw-semibold text-primary'],
        ['content' => $stokBadge]
    ];
}

$emptyMessage = "Tidak ada pemakaian sparepart pada periode ini.";
include '../includes/components/table.php';

include '../includes/footer.php';
