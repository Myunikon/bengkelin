<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Daftar Servis';

// Filter status
$validStatus = ['antre','dikerjakan','selesai','diambil','dibatalkan'];
$filterStatus = $_GET['status'] ?? '';
if ($filterStatus && !in_array($filterStatus, $validStatus, true)) $filterStatus = '';

// Filter pencarian (dari dashboard search bar)
$search = trim($_GET['search'] ?? '');

// Build query dengan dukungan search
$conditions = [];
$params      = [];
$types        = '';

if ($filterStatus) {
    $conditions[] = "s.status = ?";
    $params[]     = $filterStatus;
    $types        .= 's';
}
if ($search !== '') {
    $searchParam   = "%$search%";
    $conditions[]  = "(k.no_polisi LIKE ? OR p.nama LIKE ?)";
    $params[]      = $searchParam;
    $params[]      = $searchParam;
    $types         .= 'ss';
}

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
$sql = "
    SELECT s.id_servis, s.tanggal_masuk, s.status, s.biaya_jasa,
           k.no_polisi, k.merk,
           p.nama  AS nama_pelanggan,
           m.nama  AS nama_mekanik
    FROM   servis s
    JOIN   kendaraan k ON s.id_kendaraan = k.id_kendaraan
    JOIN   pelanggan p ON k.id_pelanggan = p.id_pelanggan
    LEFT JOIN mekanik m ON s.id_mekanik  = m.id_mekanik
    $where
    ORDER BY s.id_servis DESC
";
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Hitung per-status untuk badge counter
$counts = [];
$cr = $conn->query("SELECT status, COUNT(*) c FROM servis GROUP BY status");
while ($row = $cr->fetch_assoc()) $counts[$row['status']] = $row['c'];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Daftar Transaksi Servis</h4>
        <span class="text-muted small">Kelola alur kerja dan antrean servis kendaraan</span>
    </div>
    <a href="<?= BASE_URL ?>servis/tambah.php" class="btn btn-primary btn-sm px-3 py-2 rounded-3">
        <i class="bx bx-plus me-1"></i> Servis Baru
    </a>
</div>

<?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    <div class="d-flex flex-wrap gap-2">
        <a href="?status=<?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-sm <?= !$filterStatus ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Semua <span class="badge bg-secondary text-white ms-1"><?= array_sum($counts) ?></span>
        </a>
        <?php 
        $badgeStyles = [
            'antre'      => 'background-color: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3);',
            'dikerjakan' => 'background-color: rgba(59, 130, 246, 0.15); color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.3);',
            'selesai'    => 'background-color: rgba(16, 185, 129, 0.15); color: #15803d; border: 1px solid rgba(16, 185, 129, 0.3);',
            'diambil'    => 'background-color: rgba(168, 85, 247, 0.15); color: #7e22ce; border: 1px solid rgba(168, 85, 247, 0.3);',
            'dibatalkan' => 'background-color: rgba(239, 68, 68, 0.15); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.3);',
        ];
        foreach ($validStatus as $st): 
            $countValue = $counts[$st] ?? 0; 
            $styleAttr = $badgeStyles[$st] ?? '';
        ?>
            <a href="?status=<?= $st ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-sm <?= $filterStatus === $st ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
                <?= ucfirst($st) ?>
                <span class="badge ms-1" style="<?= $styleAttr ?>"><?= $countValue ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <form action="" method="GET" class="d-flex ms-auto">
        <?php if ($filterStatus): ?><input type="hidden" name="status" value="<?= e($filterStatus) ?>"><?php endif; ?>
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari no polisi / pelanggan..." value="<?= e($search) ?>" style="min-width:200px;">
            <button type="submit" class="btn btn-light border">Cari</button>
            <?php if ($search): ?><a href="?status=<?= e($filterStatus) ?>" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
        </div>
    </form>
</div>

<?php if ($search): ?>
    <div class="alert alert-info alert-dismissible fade show py-2 px-3 small mb-3 border-0 rounded-3" role="alert">
        <i class='bx bx-search me-1'></i> Menampilkan hasil pencarian untuk: <strong><?= e($search) ?></strong>
        <a href="?status=<?= e($filterStatus) ?>" class="ms-2 text-decoration-none">Hapus filter</a>
        <button type="button" class="btn-close p-1" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php
$headers = [
    ['label' => 'No. Tiket', 'width' => 100],
    ['label' => 'Kendaraan'],
    ['label' => 'Nama Pelanggan'],
    ['label' => 'Mekanik'],
    ['label' => 'Tgl Masuk'],
    ['label' => 'Status', 'width' => 130, 'center' => true],
    ['label' => 'Aksi', 'width' => 80, 'center' => true]
];

$rows = [];
while ($row = $result->fetch_assoc()) {
    $statusColor = statusColor($row['status']);
    $statusBadge = '<div class="text-center"><span class="badge bg-' . $statusColor . ' bg-opacity-10 text-' . $statusColor . ' fw-semibold px-3 py-2 border rounded-pill">' . ucfirst(e($row['status'])) . '</span></div>';

    $platText = '<span class="badge bg-light text-dark fw-mono font-monospace border px-2 py-1">' . e($row['no_polisi']) . '</span><div class="small text-muted mt-1">' . e($row['merk']) . '</div>';

    $actionButton = '
        <div class="text-center">
            <a href="' . BASE_URL . 'servis/detail.php?id=' . $row['id_servis'] . '" class="btn btn-light border btn-sm text-primary" data-bs-toggle="tooltip" title="Lihat Detail Transaksi">
                <i class="bx bx-show shadow-none"></i>
            </a>
        </div>';

    $rows[] = [
        ['content' => '#'.$row['id_servis'], 'class' => 'font-monospace small text-muted'],
        ['content' => $platText],
        ['content' => e($row['nama_pelanggan']), 'class' => 'fw-semibold text-dark'],
        ['content' => $row['nama_mekanik'] ? e($row['nama_mekanik']) : '<span class="text-muted fst-italic">Belum ada</span>'],
        ['content' => date('d M Y', strtotime($row['tanggal_masuk']))],
        ['content' => $statusBadge],
        ['content' => $actionButton]
    ];
}

$emptyMessage = $search
    ? 'Tidak ada hasil untuk pencarian "' . e($search) . '"' . ($filterStatus ? ' dengan status "' . ucfirst($filterStatus) . '"' : '') . '.'
    : ($filterStatus ? 'Tidak ada data servis dengan status "' . ucfirst($filterStatus) . '".' : 'Belum ada antrean data servis.');
include __DIR__ . '/../includes/components/table.php';
?>

<?php include '../includes/footer.php'; ?>
