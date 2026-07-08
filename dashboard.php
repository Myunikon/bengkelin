<?php
session_start();
require 'config.php';
require 'includes/auth_check.php';

$pageTitle = 'Dashboard';

// ============================================================
// 1. STATISTIK UTAMA (Kartu ringkasan)
// ============================================================
$stats = [];
$stats['antre']     = $conn->query("SELECT COUNT(*) AS c FROM servis WHERE status = 'antre'")->fetch_assoc()['c'];
$stats['dikerjakan']= $conn->query("SELECT COUNT(*) AS c FROM servis WHERE status = 'dikerjakan'")->fetch_assoc()['c'];
$stats['selesai']   = $conn->query("SELECT COUNT(*) AS c FROM servis WHERE status = 'selesai'")->fetch_assoc()['c'];

$r = $conn->query("
    SELECT COALESCE(SUM(s.biaya_jasa), 0) +
           COALESCE((SELECT SUM(sd.subtotal) FROM servis_details sd JOIN servis sv ON sd.id_servis = sv.id_servis WHERE MONTH(sv.tanggal_masuk) = MONTH(CURDATE()) AND YEAR(sv.tanggal_masuk) = YEAR(CURDATE()) AND sv.status IN ('selesai','diambil')), 0) AS total
    FROM servis s WHERE MONTH(s.tanggal_masuk) = MONTH(CURDATE()) AND YEAR(s.tanggal_masuk) = YEAR(CURDATE()) AND s.status IN ('selesai','diambil')
");
$stats['pendapatan_bulan'] = $r->fetch_assoc()['total'] ?? 0;

// ============================================================
// 2. SERVIS TERBARU (Tabel 5 terakhir)
// ============================================================
$recent = $conn->query("
    SELECT s.id_servis, s.tanggal_masuk, s.status, k.no_polisi, p.nama AS nama_pelanggan
    FROM servis s JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan ORDER BY s.id_servis DESC LIMIT 5
");

// ============================================================
// 3. AKTIVITAS TERKINI (Timeline – dari riwayat_status)
// ============================================================
$act_result = $conn->query("
    SELECT rs.status_baru, rs.waktu_perubahan, rs.keterangan,
           k.no_polisi, k.merk,
           m.nama AS nama_mekanik
    FROM riwayat_status rs
    JOIN servis s ON rs.id_servis = s.id_servis
    JOIN kendaraan k ON s.id_kendaraan = k.id_kendaraan
    LEFT JOIN mekanik m ON s.id_mekanik = m.id_mekanik
    ORDER BY rs.waktu_perubahan DESC
    LIMIT 5
");

$activities = [];
while ($act = $act_result->fetch_assoc()) {
    $waktu     = strtotime($act['waktu_perubahan']);
    $diff      = time() - $waktu;
    if ($diff < 60)          $timeText = 'Baru saja';
    elseif ($diff < 3600)    $timeText = floor($diff / 60) . ' menit yang lalu';
    elseif ($diff < 86400)   $timeText = floor($diff / 3600) . ' jam yang lalu';
    else                     $timeText = date('d M Y H:i', $waktu);

    $titleMap = [
        'antre'      => 'Servis Baru Masuk',
        'dikerjakan' => 'Servis Mulai Dikerjakan',
        'selesai'    => 'Servis Selesai',
        'diambil'    => 'Kendaraan Diambil',
        'dibatalkan' => 'Servis Dibatalkan',
    ];
    $title = $titleMap[$act['status_baru']] ?? ucfirst($act['status_baru']);

    $desc = $act['no_polisi'] . ' – ' . $act['merk'];
    if ($act['nama_mekanik']) {
        $desc .= ' | Mekanik: ' . $act['nama_mekanik'];
    }

    $activities[] = [
        'title'  => $title,
        'desc'   => $desc,
        'time'   => $timeText,
        'status' => $act['status_baru'],
    ];
}

// ============================================================
// 4. CHART – Pendapatan Mingguan (Bulan Ini, real data)
// ============================================================
$year  = date('Y');
$month = date('m');

// Hitung pendapatan per minggu dalam bulan ini (berdasarkan tanggal_masuk)
$revenueWeeks = [0.0, 0.0, 0.0, 0.0];
$rev_res = $conn->query("
    SELECT CEIL(DAY(s.tanggal_masuk) / 7) AS minggu,
           SUM(s.biaya_jasa) +
           COALESCE((SELECT SUM(sd.subtotal) FROM servis_details sd WHERE sd.id_servis = s.id_servis), 0) AS total
    FROM servis s
    WHERE MONTH(s.tanggal_masuk) = $month AND YEAR(s.tanggal_masuk) = $year
      AND s.status IN ('selesai','diambil')
    GROUP BY minggu
    ORDER BY minggu
");
while ($rr = $rev_res->fetch_assoc()) {
    $idx = min((int)$rr['minggu'] - 1, 3);
    $revenueWeeks[$idx] = round((float)$rr['total'] / 1000000, 2); // dalam juta
}
$revenueData = $revenueWeeks;

// ============================================================
// 5. CHART – Volume Servis 3 Bulan Terakhir (real data)
// ============================================================
$serviceData   = [];
$serviceLabels = [];
for ($i = 2; $i >= 0; $i--) {
    $m      = date('m', strtotime("-$i month"));
    $y      = date('Y', strtotime("-$i month"));
    $label  = date('M', strtotime("-$i month"));
    $count  = $conn->query("SELECT COUNT(*) AS c FROM servis WHERE MONTH(tanggal_masuk) = $m AND YEAR(tanggal_masuk) = $y")->fetch_assoc()['c'];
    $serviceLabels[] = $label;
    $serviceData[]   = (int)$count;
}

// ============================================================
// 6. CHART – Top Mekanik Bulan Ini (Doughnut, real data)
// ============================================================
$mech_res = $conn->query("
    SELECT m.nama, COUNT(s.id_servis) AS jumlah
    FROM mekanik m
    JOIN servis s ON m.id_mekanik = s.id_mekanik
    WHERE MONTH(s.tanggal_masuk) = $month AND YEAR(s.tanggal_masuk) = $year
      AND s.status IN ('selesai','diambil')
    GROUP BY m.id_mekanik, m.nama
    ORDER BY jumlah DESC
    LIMIT 5
");
$mechLabels = [];
$mechData   = [];
$mechColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'];
while ($mc = $mech_res->fetch_assoc()) {
    $mechLabels[] = $mc['nama'];
    $mechData[]   = (int)$mc['jumlah'];
}
// Jika belum ada data, tampilkan placeholder
if (empty($mechData)) {
    $mechLabels = ['Belum ada data'];
    $mechData   = [1];
    $mechColors = ['#e5e7eb'];
}

// ============================================================
// 7. STATISTIK REAL: Total Servis & Pelanggan Bulan Ini
// ============================================================
$totalServisBulanIni  = $conn->query("SELECT COUNT(*) AS c FROM servis WHERE MONTH(tanggal_masuk) = $month AND YEAR(tanggal_masuk) = $year")->fetch_assoc()['c'];
$totalPelangganBaru   = $conn->query("SELECT COUNT(*) AS c FROM pelanggan WHERE MONTH(created_at) = $month AND YEAR(created_at) = $year")->fetch_assoc()['c'];

// ============================================================
// 8. TARGET PENDAPATAN DINAMIS (gunakan rata-rata 6 bulan terakhir sebagai target)
// ============================================================
$avg_res = $conn->query("
    SELECT COALESCE(AVG(monthly_total), 0) AS avg_income
    FROM (
        SELECT MONTH(tanggal_masuk) AS bln, YEAR(tanggal_masuk) AS thn,
               SUM(biaya_jasa) AS monthly_total
        FROM servis
        WHERE status IN ('selesai','diambil')
          AND tanggal_masuk >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY bln, thn
    ) AS monthly
");
$avg_income = (float)($avg_res->fetch_assoc()['avg_income'] ?? 0);
// Target = 120% dari rata-rata, minimal Rp 5.000.000
$target_pendapatan = max($avg_income * 1.2, 5000000);
$pencapaian_pct = ($target_pendapatan > 0) ? min(round(($stats['pendapatan_bulan'] / $target_pendapatan) * 100), 100) : 0;
$sisa_target = max($target_pendapatan - $stats['pendapatan_bulan'], 0);

// ============================================================
// 9. PENGINGAT – Stok Menipis (real: stok <= 5)
// ============================================================
$stok_menipis_res = $conn->query("SELECT nama_part, stok FROM sparepart WHERE stok <= 5 ORDER BY stok ASC LIMIT 5");
$stok_menipis = [];
while ($sm = $stok_menipis_res->fetch_assoc()) {
    $stok_menipis[] = $sm;
}

// ============================================================
// RENDER
// ============================================================
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Dashboard</h4>
        <span class="text-muted small">Ringkasan performa bengkel</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <form action="<?= BASE_URL ?>servis/index.php" method="GET" class="d-flex">
            <div class="input-group input-group-sm" style="width: 280px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class='bx bx-search'></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari no polisi / pelanggan...">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            </div>
        </form>
    </div>
</div>

<?php
$reportStats = [
    ['label' => 'Antrian', 'value' => $stats['antre'], 'icon' => 'bx-time-five', 'color' => 'amber'],
    ['label' => 'Dikerjakan', 'value' => $stats['dikerjakan'], 'icon' => 'bx-cog', 'color' => 'blue'],
    ['label' => 'Selesai (belum diambil)', 'value' => $stats['selesai'], 'icon' => 'bx-check-circle', 'color' => 'green'],
    ['label' => 'Pendapatan Bulan Ini', 'value' => rupiah($stats['pendapatan_bulan']), 'icon' => 'bx-money', 'color' => 'purple']
];
include __DIR__ . '/includes/components/report_stats.php';
?>

<?php
include __DIR__ . '/includes/components/charts.php';
?>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold text-muted mb-0">Servis Terbaru</h6>
                <a href="<?= BASE_URL ?>servis/index.php" class="small text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle mb-0" style="font-size: 0.9rem;">
                    <thead class="border-bottom"><tr><th>No. Polisi</th><th>Pelanggan</th><th>Tgl Masuk</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if ($recent->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data servis.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $recent->fetch_assoc()): $bgClass = statusColor($row['status']); ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($row['no_polisi']) ?></td>
                                    <td><?= e($row['nama_pelanggan']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_masuk'])) ?></td>
                                    <td><span class="badge badge-<?= $bgClass ?> rounded-pill px-3 py-2 border"><?= ucfirst(e($row['status'])) ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php include __DIR__ . '/includes/components/timeline.php'; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card stat-card h-100 p-3">
            <h6 class="fw-semibold text-muted mb-3">Top Mekanik Bulan Ini</h6>
            <?php if (count($mechData) === 1 && $mechColors[0] === '#e5e7eb'): ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-75 text-muted">
                    <i class='bx bx-user-x fs-1 opacity-25'></i>
                    <p class="small mt-2">Belum ada data bulan ini.</p>
                </div>
            <?php else: ?>
                <div style="height: 200px; display:flex; justify-content:center;"><canvas id="mechanicChart"></canvas></div>
                <ul class="list-unstyled mt-3 mb-0" style="font-size:0.8rem;">
                    <?php foreach ($mechLabels as $i => $lbl): ?>
                        <li class="d-flex align-items-center gap-2 mb-1">
                            <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:<?= $mechColors[$i % count($mechColors)] ?>;"></span>
                            <span><?= e($lbl) ?></span>
                            <span class="ms-auto fw-semibold text-muted"><?= $mechData[$i] ?> servis</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card stat-card h-100 p-3">
            <h6 class="fw-semibold text-muted mb-3">Pencapaian Target Bulan Ini</h6>
            <div class="text-center mt-2">
                <h2 class="fw-bold <?= $pencapaian_pct >= 100 ? 'text-success' : ($pencapaian_pct >= 70 ? 'text-primary' : 'text-warning') ?> mb-0"><?= $pencapaian_pct ?>%</h2>
                <p class="text-muted small mb-3">Target: <?= rupiah($target_pendapatan) ?></p>
                <div class="progress" style="height: 12px; border-radius: 10px;">
                    <div class="progress-bar <?= $pencapaian_pct >= 100 ? 'bg-success' : ($pencapaian_pct >= 70 ? 'bg-primary' : 'bg-warning') ?>" role="progressbar" style="width: <?= $pencapaian_pct ?>%;"></div>
                </div>
                <p class="small text-muted mt-3">
                    <?php if ($sisa_target > 0): ?>
                        Tersisa <?= rupiah($sisa_target) ?> untuk mencapai target.
                    <?php else: ?>
                        <span class="text-success fw-semibold"><i class='bx bx-trophy me-1'></i>Target bulan ini tercapai!</span>
                    <?php endif; ?>
                </p>
            </div>
            <hr class="my-2">
            <div class="row g-2 text-center" style="font-size:0.8rem;">
                <div class="col-6">
                    <div class="fw-bold text-dark"><?= $totalServisBulanIni ?></div>
                    <div class="text-muted">Total Servis</div>
                </div>
                <div class="col-6">
                    <div class="fw-bold text-dark"><?= $totalPelangganBaru ?></div>
                    <div class="text-muted">Pelanggan Baru</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card stat-card h-100 p-3 <?= empty($stok_menipis) ? '' : 'border-warning border-opacity-50' ?>">
            <h6 class="fw-semibold text-muted mb-3">
                <i class='bx bx-bulb <?= empty($stok_menipis) ? 'text-muted' : 'text-warning' ?>'></i>
                Stok Menipis
            </h6>
            <?php if (empty($stok_menipis)): ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-75 text-muted">
                    <i class='bx bx-check-circle fs-1 text-success opacity-50'></i>
                    <p class="small mt-2">Semua stok dalam kondisi aman.</p>
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush bg-transparent">
                    <?php foreach ($stok_menipis as $sm): ?>
                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center small">
                            <span><i class='bx bx-chevron-right text-warning'></i> <?= e($sm['nama_part']) ?></span>
                            <span class="badge bg-<?= $sm['stok'] <= 0 ? 'danger' : 'warning' ?> rounded-pill"><?= $sm['stok'] <= 0 ? 'Habis' : $sm['stok'] . ' unit' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= BASE_URL ?>sparepart/index.php" class="btn btn-outline-warning btn-sm w-100 mt-3">
                    <i class='bx bx-package me-1'></i> Kelola Sparepart
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (!(count($mechData) === 1 && $mechColors[0] === '#e5e7eb')): ?>
    new Chart(document.getElementById('mechanicChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($mechLabels) ?>,
            datasets: [{
                data: <?= json_encode($mechData) ?>,
                backgroundColor: <?= json_encode(array_slice($mechColors, 0, count($mechData))) ?>,
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
    });
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
