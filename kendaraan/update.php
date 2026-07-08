<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'kendaraan/index.php');
}

$id = (int)($_POST['id_kendaraan'] ?? 0);
$id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
$no_polisi = strtoupper(trim($_POST['no_polisi'] ?? ''));
$merk = trim($_POST['merk'] ?? '');
$model = trim($_POST['model'] ?? '');
$tahun = $_POST['tahun'] !== '' ? (int)$_POST['tahun'] : null;

if ($id <= 0) {
    setFlash('danger', 'ID Kendaraan tidak valid.');
    redirect(BASE_URL . 'kendaraan/index.php');
}

// Validation
if ($id_pelanggan <= 0) {
    setFlash('danger', 'Pemilik kendaraan wajib dipilih.');
    redirect(BASE_URL . "kendaraan/edit.php?id=$id");
}

if ($no_polisi === '') {
    setFlash('danger', 'No. Polisi wajib diisi.');
    redirect(BASE_URL . "kendaraan/edit.php?id=$id");
}

if ($merk === '') {
    setFlash('danger', 'Merk kendaraan wajib diisi.');
    redirect(BASE_URL . "kendaraan/edit.php?id=$id");
}

if ($tahun !== null) {
    $currentYear = (int)date('Y');
    if ($tahun < 1980 || $tahun > $currentYear) {
        setFlash('danger', 'Tahun pembuatan tidak valid (harus 1980 - ' . $currentYear . ').');
        redirect(BASE_URL . "kendaraan/edit.php?id=$id");
    }
}

// Check unique no_polisi excluding current id
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM kendaraan WHERE no_polisi = ? AND id_kendaraan != ?");
$stmt->bind_param('si', $no_polisi, $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($r['c'] > 0) {
    setFlash('danger', 'No. Polisi "' . $no_polisi . '" sudah digunakan oleh kendaraan lain.');
    redirect(BASE_URL . "kendaraan/edit.php?id=$id");
}

// Update
$stmt = $conn->prepare("UPDATE kendaraan SET id_pelanggan = ?, no_polisi = ?, merk = ?, model = ?, tahun = ? WHERE id_kendaraan = ?");
$modelParam = ($model === '') ? null : $model;
$tahunParam = ($tahun === null) ? null : (int)$tahun;
$stmt->bind_param('isssii', $id_pelanggan, $no_polisi, $merk, $modelParam, $tahunParam, $id);

if ($stmt->execute()) {
    setFlash('success', 'Data kendaraan berhasil diperbarui.');
    redirect(BASE_URL . 'kendaraan/index.php');
} else {
    setFlash('danger', 'Gagal memperbarui data kendaraan: ' . $conn->error);
    redirect(BASE_URL . "kendaraan/edit.php?id=$id");
}
$stmt->close();
