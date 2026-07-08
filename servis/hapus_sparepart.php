<?php
header('Content-Type: application/json');
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Hanya menerima request POST.']);
    exit;
}

$id_detail = (int)($_POST['id_detail'] ?? 0);

if ($id_detail <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID detail tidak valid.']);
    exit;
}

// 1. Ambil detail sparepart yang terpasang
$stmt = $conn->prepare("
    SELECT sd.*, s.status 
    FROM servis_details sd
    JOIN servis s ON sd.id_servis = s.id_servis
    WHERE sd.id_detail = ?
");
$stmt->bind_param('i', $id_detail);
$stmt->execute();
$detail = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$detail) {
    echo json_encode(['success' => false, 'message' => 'Detail sparepart terpasang tidak ditemukan.']);
    exit;
}

// Cek apakah status transaksi servis masih aktif (antre/dikerjakan)
if (!in_array($detail['status'], ['antre', 'dikerjakan'], true)) {
    echo json_encode(['success' => false, 'message' => 'Suku cadang tidak dapat dihapus karena servis sudah selesai/tidak aktif.']);
    exit;
}

// Jalankan transaksi database
$conn->begin_transaction();

try {
    // 1. Hapus dari detail
    $stmt = $conn->prepare("DELETE FROM servis_details WHERE id_detail = ?");
    $stmt->bind_param('i', $id_detail);
    $stmt->execute();
    $stmt->close();

    // 2. Kembalikan stok sparepart
    $stmt_stok = $conn->prepare("UPDATE sparepart SET stok = stok + ? WHERE id_sparepart = ?");
    $stmt_stok->bind_param('ii', $detail['qty'], $detail['id_sparepart']);
    $stmt_stok->execute();
    $stmt_stok->close();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus detail sparepart: ' . $e->getMessage()]);
}
