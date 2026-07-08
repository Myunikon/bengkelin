<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'mekanik/index.php');
}

$id = (int)($_POST['id_mekanik'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$status = trim($_POST['status'] ?? '');

if ($id <= 0) {
    setFlash('danger', 'ID Mekanik tidak valid.');
    redirect(BASE_URL . 'mekanik/index.php');
}

// Validasi
if (mb_strlen($nama) < 3) {
    setFlash('danger', 'Nama lengkap mekanik wajib diisi minimal 3 karakter.');
    redirect(BASE_URL . "mekanik/edit.php?id=$id");
}

if (!in_array($status, ['tersedia', 'sibuk', 'nonaktif'])) {
    setFlash('danger', 'Status operasional tidak valid.');
    redirect(BASE_URL . "mekanik/edit.php?id=$id");
}

// UPDATE di tabel mekanik
$stmt = $conn->prepare("UPDATE mekanik SET nama = ?, status = ? WHERE id_mekanik = ?");
$stmt->bind_param('ssi', $nama, $status, $id);

if ($stmt->execute()) {
    setFlash('success', 'Data mekanik berhasil diperbarui.');
    redirect(BASE_URL . 'mekanik/index.php');
} else {
    setFlash('danger', 'Gagal memperbarui data mekanik: ' . $conn->error);
    redirect(BASE_URL . "mekanik/edit.php?id=$id");
}
$stmt->close();
