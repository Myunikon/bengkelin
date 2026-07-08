<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'sparepart/index.php');
}

$nama_part = trim($_POST['nama_part'] ?? '');
$stok = (int)($_POST['stok'] ?? 0);
$harga_jual = (float)($_POST['harga_jual'] ?? 0);

// Validation
if ($nama_part === '') {
    setFlash('danger', 'Nama sparepart wajib diisi.');
    redirect(BASE_URL . 'sparepart/tambah.php');
}

if ($stok < 0) {
    setFlash('danger', 'Stok tidak boleh negatif.');
    redirect(BASE_URL . 'sparepart/tambah.php');
}

if ($harga_jual <= 0) {
    setFlash('danger', 'Harga jual harus lebih besar dari 0.');
    redirect(BASE_URL . 'sparepart/tambah.php');
}

// Insert
$stmt = $conn->prepare("INSERT INTO sparepart (nama_part, stok, harga_jual) VALUES (?, ?, ?)");
$stmt->bind_param('sid', $nama_part, $stok, $harga_jual);

if ($stmt->execute()) {
    setFlash('success', 'Sparepart berhasil ditambahkan.');
    redirect(BASE_URL . 'sparepart/index.php');
} else {
    setFlash('danger', 'Gagal menambahkan sparepart: ' . $conn->error);
    redirect(BASE_URL . 'sparepart/tambah.php');
}
$stmt->close();
