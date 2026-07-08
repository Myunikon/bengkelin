<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$pageTitle = 'Daftar Pelanggan';

// Search filter
$search = trim($_GET['search'] ?? '');
$searchParam = "%$search%";

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT p.*, COUNT(k.id_kendaraan) AS jumlah_kendaraan
        FROM pelanggan p
        LEFT JOIN kendaraan k ON p.id_pelanggan = k.id_pelanggan
        WHERE p.nama LIKE ? OR p.no_telp LIKE ?
        GROUP BY p.id_pelanggan
        ORDER BY p.id_pelanggan DESC
    ");
    $stmt->bind_param('ss', $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("
        SELECT p.*, COUNT(k.id_kendaraan) AS jumlah_kendaraan
        FROM pelanggan p
        LEFT JOIN kendaraan k ON p.id_pelanggan = k.id_pelanggan
        GROUP BY p.id_pelanggan
        ORDER BY p.id_pelanggan DESC
    ");
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">Daftar Pelanggan</h4>
        <span class="text-muted small">Kelola data pelanggan bengkel</span>
    </div>
    <a href="<?php echo BASE_URL; ?>pelanggan/tambah.php" class="btn btn-primary btn-sm px-3 py-2 rounded-3">
        <i class="bx bx-plus me-1"></i> Pelanggan Baru
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
            <span class="input-group-text bg-white border-end-0 text-muted">
                <i class="bx bx-search"></i>
            </span>
            <input
                type="text"
                id="searchInput"
                name="search"
                class="form-control border-start-0 border-end-0 ps-0 shadow-none"
                placeholder="Cari nama atau telepon..."
                value="<?php echo e($search); ?>"
            >
            <button type="button" class="input-group-text bg-white border-start-0 text-muted" style="cursor: pointer;" onclick="document.getElementById('searchInput').value=''; document.getElementById('searchInput').focus();" title="Kosongkan ketikan">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <button type="submit" class="btn btn-light border px-3">Cari</button>

        <?php if ($search !== ''): ?>
            <a href="index.php" class="btn btn-outline-danger px-3">
                Reset
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="card stat-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="border-bottom text-muted">
                    <tr>
                        <th width="60" class="ps-4 fw-medium">No</th>
                        <th class="fw-medium">Nama Pelanggan</th>
                        <th class="fw-medium">No. Telepon</th>
                        <th class="fw-medium">Alamat</th>
                        <th class="fw-medium">Kendaraan</th>
                        <th class="fw-medium">Tgl Daftar</th>
                        <th width="140" class="text-center fw-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bx bx-user-x fs-1 text-muted opacity-25"></i>
                                    <p class="text-muted mt-2 mb-0">Data pelanggan tidak ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $no++; ?></td>
                                <td class="fw-semibold text-dark"><?php echo e($row['nama']); ?></td>
                                <td><?php echo e($row['no_telp']); ?></td>
                                <td class="text-muted">
                                    <?php echo $row['alamat'] ? e($row['alamat']) : '—'; ?>
                                </td>
                                <td>
                                    <?php $jk = (int)$row['jumlah_kendaraan']; ?>
                                    <?php if ($jk > 0): ?>
                                        <a href="<?php echo BASE_URL; ?>kendaraan/index.php?search=<?php echo urlencode($row['nama']); ?>"
                                           class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none px-2 py-1"
                                           data-bs-toggle="tooltip" title="Lihat kendaraan milik pelanggan ini">
                                            <i class='bx bx-car me-1'></i><?php echo $jk; ?> kendaraan
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit.php?id=<?php echo $row['id_pelanggan']; ?>"
                                           class="btn btn-light border btn-sm text-primary" data-bs-toggle="tooltip" title="Edit Data">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <?php if ((int)$row['jumlah_kendaraan'] > 0): ?>
                                            <!-- Tombol hapus dinonaktifkan karena masih ada kendaraan -->
                                            <a href="<?php echo BASE_URL; ?>kendaraan/index.php?search=<?php echo urlencode($row['nama']); ?>"
                                               class="btn btn-light border btn-sm text-warning"
                                               data-bs-toggle="tooltip"
                                               title="Hapus <?php echo (int)$row['jumlah_kendaraan']; ?> kendaraan milik pelanggan ini terlebih dahulu">
                                                <i class="bx bx-car"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="hapus.php?id=<?php echo $row['id_pelanggan']; ?>"
                                               class="btn btn-light border btn-sm text-danger btn-confirm"
                                               data-confirm="Hapus pelanggan &quot;<?php echo e($row['nama']); ?>&quot;? Tindakan ini tidak dapat dibatalkan."
                                               data-bs-toggle="tooltip" title="Hapus Pelanggan">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
