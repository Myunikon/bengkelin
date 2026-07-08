<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'pelanggan/index.php');
}

$nama = trim($_POST['nama'] ?? '');
$no_telp = trim($_POST['no_telp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

// Validation
if (mb_strlen($nama) < 3) {
    setFlash('danger', 'Nama lengkap wajib diisi minimal 3 karakter.');
    redirect(BASE_URL . 'pelanggan/tambah.php');
}

if (!preg_match('/^[0-9]{10,14}$/', $no_telp)) {
    setFlash('danger', 'No. telepon tidak valid. Harus angka dengan panjang 10-14 digit.');
    redirect(BASE_URL . 'pelanggan/tambah.php');
}

$stmt = $conn->prepare("INSERT INTO pelanggan (nama, no_telp, alamat) VALUES (?, ?, ?)");
$alamatParam = ($alamat === '') ? null : $alamat;
$stmt->bind_param('sss', $nama, $no_telp, $alamatParam);

if ($stmt->execute()) {
    setFlash('success', 'Pelanggan baru berhasil ditambahkan.');
    redirect(BASE_URL . 'pelanggan/index.php');
} else {
    setFlash('danger', 'Gagal menambahkan pelanggan: ' . $conn->error);
    redirect(BASE_URL . 'pelanggan/tambah.php');
}
$stmt->close();
