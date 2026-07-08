<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';
require_once __DIR__ . '/../includes/components/report_helper.php';

$pageTitle = 'Laporan Pendapatan';

// 1. Ambil data periode (rentang tanggal), lalu extract jadi variabel biasa
$periode = initReportPeriod();
$tgl_awal    = $periode['tgl_awal'];
$tgl_akhir   = $periode['tgl_akhir'];
$preset      = $periode['preset'];
$periodeText = $periode['periodeText'];

// 2. Query daftar servis selesai/diambil pada rentang tanggal terpilih
$stmt = $conn->prepare("
    SELECT s.id_servis, s.tanggal_masuk, s.biaya_jasa, k.no_polisi, k.merk,
           p.nama AS nama_pelanggan, m.nama AS nama_mekanik,
           COALESCE((SELECT SUM(sd.subtotal) FROM servis_details sd WHERE sd.id_servis = s.id_servis), 0) AS total_sparepart
    FROM servis s
    JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
    JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
    LEFT JOIN mekanik m ON s.id_mekanik = m.id_mekanik
    WHERE s.tanggal_masuk BETWEEN ? AND ? AND s.status IN ('selesai', 'diambil')
    ORDER BY s.tanggal_masuk ASC, s.id_servis ASC
");
$stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$total_jasa = 0; $total_sparepart = 0; $grand_total = 0; $servis_count = 0;
$db_rows = [];

while ($row = $result->fetch_assoc()) {
    $row_total = (float)$row['biaya_jasa'] + (float)$row['total_sparepart'];
    $total_jasa += (float)$row['biaya_jasa'];
    $total_sparepart += (float)$row['total_sparepart'];
    $grand_total += $row_total;
    $servis_count++;
    $db_rows[] = $row;
}

include '../includes/header.php';
include '../includes/sidebar.php';

// 3. RENDER UI 1: Header laporan
$title = 'Laporan Pendapatan';
$icon = 'bx-money';
// $periodeText sudah didapat dari $periode, tidak perlu dibuat ulang
include __DIR__ . '/../includes/components/report_header.php';

// 4. RENDER UI 2: Komponen filter (rentang tanggal)
include __DIR__ . '/../includes/components/report_filter.php';

// 5. RENDER UI 3: Statistik ringkasan
$reportStats = [
    ['label' => 'Servis Selesai', 'value' => $servis_count . ' Transaksi', 'icon' => 'bx-check-circle', 'color' => 'blue'],
    ['label' => 'Pendapatan Jasa', 'value' => rupiah($total_jasa), 'icon' => 'bx-wrench', 'color' => 'purple'],
    ['label' => 'Pendapatan Sparepart', 'value' => rupiah($total_sparepart), 'icon' => 'bx-cog', 'color' => 'amber'],
    ['label' => 'Total Pendapatan', 'value' => rupiah($grand_total), 'icon' => 'bx-wallet', 'color' => 'green']
];
include __DIR__ . '/../includes/components/report_stats.php';

// 6. RENDER UI 4: Tabel transaksi
$headers = [
    ['label' => 'No', 'width' => 50],
    ['label' => 'No. Transaksi'],
    ['label' => 'Tanggal'],
    ['label' => 'Kendaraan'],
    ['label' => 'Pelanggan'],
    ['label' => 'Mekanik'],
    ['label' => 'Biaya Jasa'],
    ['label' => 'Biaya Sparepart'],
    ['label' => 'Total']
];

$rows = [];
$no = 1;
foreach ($db_rows as $row) {
    $row_total = (float)$row['biaya_jasa'] + (float)$row['total_sparepart'];
    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => '#'.$row['id_servis'], 'class' => 'font-monospace small text-muted'],
        ['content' => date('d/m/Y', strtotime($row['tanggal_masuk']))],
        ['content' => '<span class="fw-semibold font-monospace">'.e($row['no_polisi']).'</span><div class="small text-muted">'.e($row['merk']).'</div>'],
        ['content' => e($row['nama_pelanggan'])],
        ['content' => $row['nama_mekanik'] ? e($row['nama_mekanik']) : '<span class="text-muted">—</span>'],
        ['content' => rupiah($row['biaya_jasa'])],
        ['content' => rupiah($row['total_sparepart'])],
        ['content' => rupiah($row_total), 'class' => 'fw-semibold text-primary']
    ];
}

// Baris total di baris paling bawah tabel
if (!empty($rows)) {
    $rows[] = [
        ['content' => '—', 'class' => 'text-muted'],
        ['content' => '<strong>TOTAL</strong>', 'class' => 'fw-bold'],
        ['content' => ''], ['content' => ''], ['content' => ''], ['content' => ''],
        ['content' => '<strong>'.rupiah($total_jasa).'</strong>', 'class' => 'fw-bold'],
        ['content' => '<strong>'.rupiah($total_sparepart).'</strong>', 'class' => 'fw-bold'],
        ['content' => '<strong>'.rupiah($grand_total).'</strong>', 'class' => 'fw-bold text-success fs-6']
    ];
}

$emptyMessage = "Tidak ada transaksi servis selesai pada periode ini.";
include __DIR__ . '/../includes/components/table.php';

include '../includes/footer.php';
