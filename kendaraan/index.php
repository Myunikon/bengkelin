<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Daftar Kendaraan';

$search = trim($_GET['search'] ?? '');
$searchParam = "%$search%";

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT k.*, p.nama AS nama_pemilik
        FROM kendaraan k
        JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
        WHERE k.no_polisi LIKE ? OR k.merk LIKE ? OR p.nama LIKE ?
        ORDER BY k.id_kendaraan DESC
    ");
    $stmt->bind_param('sss', $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("
        SELECT k.*, p.nama AS nama_pemilik
        FROM kendaraan k
        JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
        ORDER BY k.id_kendaraan DESC
    ");
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Daftar Kendaraan</h4>
        <span class="text-muted small">Kelola kendaraan pelanggan yang terdaftar</span>
    </div>
    <a href="<?php echo BASE_URL; ?>kendaraan/tambah.php" class="btn btn-primary btn-sm px-3 py-2 rounded-3">
        <i class="bx bx-plus me-1"></i> Kendaraan Baru
    </a>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
        <?php echo e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <form action="" method="GET" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 500px;">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
            <input type="text" id="searchInput" name="search" class="form-control border-start-0 border-end-0 ps-0 shadow-none" placeholder="Cari plat, merk, atau pemilik..." value="<?php echo e($search); ?>">
            <button type="button" class="input-group-text bg-white border-start-0 text-muted" style="cursor: pointer;" onclick="document.getElementById('searchInput').value=''; document.getElementById('searchInput').focus();"><i class="bx bx-x"></i></button>
        </div>
        <button type="submit" class="btn btn-light border px-3">Cari</button>
        <?php if ($search !== ''): ?>
            <a href="index.php" class="btn btn-outline-danger px-3">Reset</a>
        <?php endif; ?>
    </form>
</div>

<?php
// 1. Definisikan susunan judul kolom table komponen
$headers = [
    ['label' => 'No', 'width' => 60],
    ['label' => 'No. Polisi'],
    ['label' => 'Merk'],
    ['label' => 'Model'],
    ['label' => 'Tahun'],
    ['label' => 'Pemilik'],
    ['label' => 'Aksi', 'width' => 140, 'center' => true]
];

// 2. Format baris data database ke format komponen
$rows = [];
$no = 1;
while ($row = $result->fetch_assoc()) {
    $platBadge = '<span class="badge bg-light text-dark fw-mono font-monospace border px-2 py-1.5">' . e($row['no_polisi']) . '</span>';
    $modelText = $row['model'] ? e($row['model']) : '<span class="text-muted">—</span>';
    $tahunText = $row['tahun'] ? (int)$row['tahun'] : '<span class="text-muted">—</span>';

    $actionButtons = '
        <div class="d-flex justify-content-center gap-2">
            <a href="edit.php?id='.$row['id_kendaraan'].'" class="btn btn-light border btn-sm text-primary" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></a>
            <a href="hapus.php?id='.$row['id_kendaraan'].'" class="btn btn-light border btn-sm text-danger btn-confirm" data-confirm="Hapus kendaraan ini?" data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></a>
        </div>';

    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => $platBadge],
        ['content' => e($row['merk'])],
        ['content' => $modelText],
        ['content' => $tahunText, 'class' => 'text-muted'],
        ['content' => e($row['nama_pemilik']), 'class' => 'fw-semibold'],
        ['content' => $actionButtons]
    ];
}

$emptyMessage = "Data kendaraan tidak ditemukan.";

// 3. Panggil Engine Komponen Tabel
include '../includes/components/table.php';
?>

<?php include '../includes/footer.php'; ?>
