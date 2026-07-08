<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';
require_once __DIR__ . '/../includes/components/report_helper.php';

$pageTitle = 'Laporan Pelanggan Baru';

// Ambil data periode (rentang tanggal), lalu extract jadi variabel biasa
$periode = initReportPeriod();
$tgl_awal    = $periode['tgl_awal'];
$tgl_akhir   = $periode['tgl_akhir'];
$preset      = $periode['preset'];
$periodeText = $periode['periodeText'];

// Query pelanggan baru beserta total kendaraan mereka
// Catatan: created_at berupa DATETIME, jadi dibungkus DATE() agar
// perbandingan rentang tanggal tidak meleset karena komponen jam/menit.
$stmt = $conn->prepare("
    SELECT p.id_pelanggan, p.nama, p.no_telp, p.alamat, p.created_at,
           COUNT(k.id_kendaraan) AS jumlah_kendaraan
    FROM pelanggan p
    LEFT JOIN kendaraan k ON p.id_pelanggan = k.id_pelanggan
    WHERE DATE(p.created_at) BETWEEN ? AND ?
    GROUP BY p.id_pelanggan, p.nama, p.no_telp, p.alamat, p.created_at
    ORDER BY p.created_at ASC
");
$stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$total_pelanggan_baru = 0;
$total_kendaraan_baru = 0;
$db_rows = [];

while ($row = $result->fetch_assoc()) {
    $total_pelanggan_baru++;
    $total_kendaraan_baru += (int)$row['jumlah_kendaraan'];
    $db_rows[] = $row;
}

include '../includes/header.php';
include '../includes/sidebar.php';

// RENDER UI 1: Header laporan
$title = 'Laporan Pelanggan Baru';
$icon = 'bx-user-plus';
include __DIR__ . '/../includes/components/report_header.php';

// RENDER UI 2: Filter rentang tanggal
include __DIR__ . '/../includes/components/report_filter.php';

// RENDER UI 3: Statistik ringkasan
$reportStats = [
    ['label' => 'Total Pelanggan Baru', 'value' => $total_pelanggan_baru, 'icon' => 'bx-group', 'color' => 'blue'],
    ['label' => 'Total Kendaraan Terdaftar', 'value' => $total_kendaraan_baru . ' unit', 'icon' => 'bx-car', 'color' => 'green'],
];
include __DIR__ . '/../includes/components/report_stats.php';

// RENDER UI 4: Tabel pelanggan baru
$headers = [
    ['label' => 'No', 'width' => 60],
    ['label' => 'Nama Pelanggan'],
    ['label' => 'No. Telepon'],
    ['label' => 'Alamat'],
    ['label' => 'Tanggal Daftar'],
    ['label' => 'Jumlah Kendaraan', 'width' => 160, 'center' => true]
];

$rows = [];
$no = 1;
foreach ($db_rows as $row) {
    $alamatText = $row['alamat'] ? e($row['alamat']) : '<span class="text-muted">Tidak ada alamat</span>';
    $kendaraanText = '<div class="text-center fw-bold text-primary">' . (int)$row['jumlah_kendaraan'] . ' unit</div>';

    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => e($row['nama']), 'class' => 'fw-semibold text-dark'],
        ['content' => e($row['no_telp'])],
        ['content' => $alamatText],
        ['content' => date('d/m/Y H:i', strtotime($row['created_at']))],
        ['content' => $kendaraanText]
    ];
}

$emptyMessage = "Tidak ada registrasi pelanggan baru pada periode ini.";
include __DIR__ . '/../includes/components/table.php';

include '../includes/footer.php';
