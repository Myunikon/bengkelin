<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Daftar Sparepart';

// Filter pencarian
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $searchParam = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM sparepart WHERE nama_part LIKE ? ORDER BY nama_part ASC");
    $stmt->bind_param('s', $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM sparepart ORDER BY id_sparepart DESC");
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Daftar Sparepart</h4>
        <span class="text-muted small">Kelola stok barang dan harga jual suku cadang</span>
    </div>
    <a href="<?php echo BASE_URL; ?>sparepart/tambah.php" class="btn btn-primary btn-sm px-3 py-2 rounded-3">
        <i class="bx bx-plus me-1"></i> Sparepart Baru
    </a>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
        <?php echo e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Search Box -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <form action="" method="GET" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 500px;">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
            <input type="text" id="searchSp" name="search" class="form-control border-start-0 border-end-0 ps-0 shadow-none"
                   placeholder="Cari nama sparepart..." value="<?php echo e($search); ?>">
            <button type="button" class="input-group-text bg-white border-start-0 text-muted" style="cursor:pointer;"
                    onclick="document.getElementById('searchSp').value=''; this.closest('form').submit();">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <button type="submit" class="btn btn-light border px-3">Cari</button>
        <?php if ($search !== ''): ?>
            <a href="index.php" class="btn btn-outline-danger px-3">Reset</a>
        <?php endif; ?>
    </form>
</div>

<?php
// Alert stok menipis (stok <= 5)
$low_stock = $conn->query("SELECT nama_part, stok FROM sparepart WHERE stok <= 5 ORDER BY stok ASC LIMIT 3");
if ($low_stock->num_rows > 0):
    $items = [];
    while ($ls = $low_stock->fetch_assoc()) $items[] = '<strong>' . e($ls['nama_part']) . '</strong> (' . ($ls['stok'] <= 0 ? 'Habis' : $ls['stok'] . ' unit') . ')';
?>
<div class="alert alert-warning alert-dismissible fade show rounded-3 border-0 shadow-sm small mb-4" role="alert">
    <i class='bx bx-error me-1'></i> <strong>Stok menipis:</strong> <?= implode(', ', $items) ?>.
    <button type="button" class="btn-close p-1" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php
// 1. Definisikan susunan judul kolom tabel komponen
$headers = [
    ['label' => 'No', 'width' => 60],
    ['label' => 'Nama Sparepart'],
    ['label' => 'Stok', 'width' => 120, 'center' => true],
    ['label' => 'Harga Jual', 'width' => 180],
    ['label' => 'Aksi', 'width' => 140, 'center' => true]
];

// 2. Format baris data database ke format komponen
$rows = [];
$no = 1;
while ($row = $result->fetch_assoc()) {
    $stok = (int)$row['stok'];
    if ($stok <= 0) $color = 'danger';
    elseif ($stok <= 5) $color = 'warning';
    else $color = 'success';

    $stokBadge = '<div class="text-center"><span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' fw-semibold px-3 py-2 border rounded-pill">' . $stok . '</span></div>';

    $actionButtons = '
        <div class="d-flex justify-content-center gap-2">
            <a href="edit.php?id='.$row['id_sparepart'].'" class="btn btn-light border btn-sm text-primary" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></a>
            <a href="hapus.php?id='.$row['id_sparepart'].'" class="btn btn-light border btn-sm text-danger btn-confirm" data-confirm="Hapus sparepart ini? Data yang sudah terkait dengan transaksi servis lama tidak dapat dihapus." data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></a>
        </div>';

    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => e($row['nama_part']), 'class' => 'fw-semibold text-dark'],
        ['content' => $stokBadge],
        ['content' => rupiah($row['harga_jual']), 'class' => 'fw-semibold text-secondary'],
        ['content' => $actionButtons]
    ];
}

$emptyMessage = $search
    ? 'Tidak ada sparepart yang cocok dengan pencarian "' . e($search) . '".'
    : 'Belum ada data sparepart yang terdaftar.';

// 3. Panggil Engine Komponen Tabel
include '../includes/components/table.php';
?>

<?php include '../includes/footer.php'; ?>
