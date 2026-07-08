<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Kendaraan tidak valid.');
    redirect(BASE_URL . 'kendaraan/index.php');
}

// Cek apakah memiliki data servis
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM servis WHERE id_kendaraan = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($r['c'] > 0) {
    setFlash('danger', 'Kendaraan tidak bisa dihapus karena memiliki riwayat servis.');
    redirect(BASE_URL . 'kendaraan/index.php');
}

// Hapus kendaraan
$stmt = $conn->prepare("DELETE FROM kendaraan WHERE id_kendaraan = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    setFlash('success', 'Data kendaraan berhasil dihapus.');
} else {
    setFlash('danger', 'Gagal menghapus kendaraan: ' . $conn->error);
}
$stmt->close();

redirect(BASE_URL . 'kendaraan/index.php');
