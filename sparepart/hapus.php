<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Sparepart tidak valid.');
    redirect(BASE_URL . 'sparepart/index.php');
}

// Cek apakah sparepart terpakai di detail transaksi servis
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM servis_details WHERE id_sparepart = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($r['c'] > 0) {
    setFlash('danger', 'Sparepart tidak bisa dihapus karena sudah pernah terpakai dalam transaksi servis.');
    redirect(BASE_URL . 'sparepart/index.php');
}

// Hapus sparepart
$stmt = $conn->prepare("DELETE FROM sparepart WHERE id_sparepart = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    setFlash('success', 'Sparepart berhasil dihapus.');
} else {
    setFlash('danger', 'Gagal menghapus sparepart: ' . $conn->error);
}
$stmt->close();

redirect(BASE_URL . 'sparepart/index.php');
