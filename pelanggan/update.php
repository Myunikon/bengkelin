<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'pelanggan/index.php');
}

$id = (int)($_POST['id_pelanggan'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$no_telp = trim($_POST['no_telp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

if ($id <= 0) {
    setFlash('danger', 'ID Pelanggan tidak valid.');
    redirect(BASE_URL . 'pelanggan/index.php');
}

// Validation
if (mb_strlen($nama) < 3) {
    setFlash('danger', 'Nama lengkap wajib diisi minimal 3 karakter.');
    redirect(BASE_URL . "pelanggan/edit.php?id=$id");
}

if (!preg_match('/^[0-9]{10,14}$/', $no_telp)) {
    setFlash('danger', 'No. telepon tidak valid. Harus angka dengan panjang 10-14 digit.');
    redirect(BASE_URL . "pelanggan/edit.php?id=$id");
}

$stmt = $conn->prepare("UPDATE pelanggan SET nama = ?, no_telp = ?, alamat = ? WHERE id_pelanggan = ?");
$alamatParam = ($alamat === '') ? null : $alamat;
$stmt->bind_param('sssi', $nama, $no_telp, $alamatParam, $id);

if ($stmt->execute()) {
    setFlash('success', 'Data pelanggan berhasil diperbarui.');
    redirect(BASE_URL . 'pelanggan/index.php');
} else {
    setFlash('danger', 'Gagal memperbarui data pelanggan: ' . $conn->error);
    redirect(BASE_URL . "pelanggan/edit.php?id=$id");
}
$stmt->close();
