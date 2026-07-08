<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'sparepart/index.php');
}

$id = (int)($_POST['id_sparepart'] ?? 0);
$nama_part = trim($_POST['nama_part'] ?? '');
$stok = (int)($_POST['stok'] ?? 0);
$harga_jual = (float)($_POST['harga_jual'] ?? 0);

if ($id <= 0) {
    setFlash('danger', 'ID Sparepart tidak valid.');
    redirect(BASE_URL . 'sparepart/index.php');
}

// Validation
if ($nama_part === '') {
    setFlash('danger', 'Nama sparepart wajib diisi.');
    redirect(BASE_URL . "sparepart/edit.php?id=$id");
}

if ($stok < 0) {
    setFlash('danger', 'Stok tidak boleh negatif.');
    redirect(BASE_URL . "sparepart/edit.php?id=$id");
}

if ($harga_jual <= 0) {
    setFlash('danger', 'Harga jual harus lebih besar dari 0.');
    redirect(BASE_URL . "sparepart/edit.php?id=$id");
}

// Update
$stmt = $conn->prepare("UPDATE sparepart SET nama_part = ?, stok = ?, harga_jual = ? WHERE id_sparepart = ?");
$stmt->bind_param('sidi', $nama_part, $stok, $harga_jual, $id);

if ($stmt->execute()) {
    setFlash('success', 'Data sparepart berhasil diperbarui.');
    redirect(BASE_URL . 'sparepart/index.php');
} else {
    setFlash('danger', 'Gagal memperbarui data sparepart: ' . $conn->error);
    redirect(BASE_URL . "sparepart/edit.php?id=$id");
}
$stmt->close();
