<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

// Hanya terima request dengan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'mekanik/index.php');
}

// Ambil & sanitasi input nama mekanik dari form
$nama = trim($_POST['nama'] ?? '');

// Validasi: Nama wajib diisi dan minimal 3 karakter
if (mb_strlen($nama) < 3) {
    setFlash('danger', 'Nama lengkap mekanik wajib diisi minimal 3 karakter.');
    redirect(BASE_URL . 'mekanik/tambah.php');
}

// INSERT ke tabel mekanik (secara default status baru diset 'tersedia')
$stmt = $conn->prepare("INSERT INTO mekanik (nama, status) VALUES (?, 'tersedia')");
$stmt->bind_param('s', $nama);

if ($stmt->execute()) {
    setFlash('success', 'Mekanik "' . $nama . '" berhasil ditambahkan.');
} else {
    setFlash('danger', 'Gagal menyimpan data mekanik: ' . $conn->error);
}

$stmt->close();
redirect(BASE_URL . 'mekanik/index.php');
