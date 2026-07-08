<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';
require_once __DIR__ . '/../includes/components/report_helper.php';

$pageTitle = 'Laporan Kinerja Mekanik';

$periode = initReportPeriod();
$tgl_awal    = $periode['tgl_awal'];
$tgl_akhir   = $periode['tgl_akhir'];
$preset      = $periode['preset'];
$periodeText = $periode['periodeText'];

// Query kinerja mekanik
$stmt = $conn->prepare("
    SELECT m.id_mekanik, m.nama, m.status AS status_aktif,
           COUNT(s.id_servis) AS jumlah_servis,
           COALESCE(SUM(s.biaya_jasa), 0) AS total_jasa
    FROM mekanik m
    LEFT JOIN servis s ON m.id_mekanik = s.id_mekanik
         AND s.tanggal_masuk BETWEEN ? AND ?
         AND s.status IN ('selesai', 'diambil')
    GROUP BY m.id_mekanik, m.nama, m.status
    ORDER BY jumlah_servis DESC, total_jasa DESC
");
$stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$total_mekanik = 0;
$total_servis_selesai = 0;
$total_kontribusi = 0;
$db_rows = [];

while ($row = $result->fetch_assoc()) {
    $total_mekanik++;
    $total_servis_selesai += (int)$row['jumlah_servis'];
    $total_kontribusi += (float)$row['total_jasa'];
    $db_rows[] = $row;
}

$mekanik_teratas = $db_rows[0]['nama'] ?? '—';

include '../includes/header.php';
include '../includes/sidebar.php';

$title = 'Laporan Kinerja Mekanik';
$icon = 'bx-bar-chart-alt-2';
include __DIR__ . '/../includes/components/report_header.php';

include __DIR__ . '/../includes/components/report_filter.php';

$reportStats = [
    ['label' => 'Total Mekanik', 'value' => $total_mekanik, 'icon' => 'bx-group', 'color' => 'blue'],
    ['label' => 'Total Servis Selesai', 'value' => $total_servis_selesai, 'icon' => 'bx-check-circle', 'color' => 'green'],
    ['label' => 'Mekanik Teratas', 'value' => e($mekanik_teratas), 'icon' => 'bx-medal', 'color' => 'amber'],
    ['label' => 'Total Kontribusi Jasa', 'value' => rupiah($total_kontribusi), 'icon' => 'bx-wallet', 'color' => 'purple']
];
include __DIR__ . '/../includes/components/report_stats.php';

$headers = [
    ['label' => 'No', 'width' => 60],
    ['label' => 'Nama Mekanik'],
    ['label' => 'Status Kerja Saat Ini', 'center' => true],
    ['label' => 'Jumlah Servis Selesai', 'width' => 180, 'center' => true],
    ['label' => 'Total Kontribusi Jasa', 'width' => 200]
];

$rows = [];
$no = 1;
foreach ($db_rows as $row) {
    $color = statusColor($row['status_aktif']);
    $statusBadge = '<div class="text-center"><span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' fw-semibold px-3 py-2 border rounded-pill">' . ucfirst(e($row['status_aktif'])) . '</span></div>';
    $jumlahServisText = '<div class="text-center fw-bold text-dark">' . (int)$row['jumlah_servis'] . '</div>';

    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => e($row['nama']), 'class' => 'fw-semibold text-dark'],
        ['content' => $statusBadge],
        ['content' => $jumlahServisText],
        ['content' => rupiah($row['total_jasa']), 'class' => 'fw-semibold text-primary']
    ];
}

$emptyMessage = "Tidak ada data mekanik terdaftar.";
include __DIR__ . '/../includes/components/table.php';

include '../includes/footer.php';
