<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Daftar Mekanik';

$result = $conn->query("SELECT * FROM mekanik ORDER BY status ASC, nama ASC");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Daftar Mekanik</h4>
        <span class="text-muted small">Kelola staf mekanik dan status operasionalnya</span>
    </div>
    <a href="<?php echo BASE_URL; ?>mekanik/tambah.php" class="btn btn-primary btn-sm px-3 py-2 rounded-3">
        <i class="bx bx-plus me-1"></i> Mekanik Baru
    </a>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
        <?php echo e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php
// 1. Definisikan susunan judul kolom tabel komponen
$headers = [
    ['label' => 'No', 'width' => 60],
    ['label' => 'Nama Mekanik'],
    ['label' => 'Status Operasional'],
    ['label' => 'Aksi', 'width' => 140, 'center' => true]
];

// 2. Format baris data database ke format komponen
$rows = [];
$no = 1;
while ($row = $result->fetch_assoc()) {
    $statusColor = statusColor($row['status']);
    $statusBadge = '<span class="badge bg-' . $statusColor . ' bg-opacity-10 text-' . $statusColor . ' fw-semibold px-3 py-2 border rounded-pill">' . ucfirst(e($row['status'])) . '</span>';

    $actionButtons = '
        <div class="d-flex justify-content-center gap-2">
            <a href="edit.php?id='.$row['id_mekanik'].'" class="btn btn-light border btn-sm text-primary" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></a>
            <a href="hapus.php?id='.$row['id_mekanik'].'" class="btn btn-light border btn-sm text-danger btn-confirm" data-confirm="Hapus data mekanik ini? Jika memiliki riwayat kerja, mekanik akan dinonaktifkan secara otomatis." data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></a>
        </div>';

    $rows[] = [
        ['content' => $no++, 'class' => 'text-muted'],
        ['content' => e($row['nama']), 'class' => 'fw-semibold text-dark'],
        ['content' => $statusBadge],
        ['content' => $actionButtons]
    ];
}

$emptyMessage = "Data mekanik belum terdaftar.";

// 3. Panggil Engine Komponen Tabel
include '../includes/components/table.php';
?>

<?php include '../includes/footer.php'; ?>
