<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'kendaraan/index.php');
}

$id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
$no_polisi = strtoupper(trim($_POST['no_polisi'] ?? ''));
$merk = trim($_POST['merk'] ?? '');
$model = trim($_POST['model'] ?? '');
$tahun = $_POST['tahun'] !== '' ? (int)$_POST['tahun'] : null;

// Validation
if ($id_pelanggan <= 0) {
    setFlash('danger', 'Pemilik kendaraan wajib dipilih.');
    redirect(BASE_URL . 'kendaraan/tambah.php');
}

if ($no_polisi === '') {
    setFlash('danger', 'No. Polisi wajib diisi.');
    redirect(BASE_URL . 'kendaraan/tambah.php');
}

if ($merk === '') {
    setFlash('danger', 'Merk kendaraan wajib diisi.');
    redirect(BASE_URL . 'kendaraan/tambah.php');
}

if ($tahun !== null) {
    $currentYear = (int)date('Y');
    if ($tahun < 1980 || $tahun > $currentYear) {
        setFlash('danger', 'Tahun pembuatan tidak valid (harus 1980 - ' . $currentYear . ').');
        redirect(BASE_URL . 'kendaraan/tambah.php');
    }
}

// Check if no_polisi is unique
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM kendaraan WHERE no_polisi = ?");
$stmt->bind_param('s', $no_polisi);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($r['c'] > 0) {
    setFlash('danger', 'No. Polisi "' . $no_polisi . '" sudah terdaftar sebelumnya.');
    redirect(BASE_URL . 'kendaraan/tambah.php');
}

// Insert dengan penanganan nullable tahun
$stmt = $conn->prepare("INSERT INTO kendaraan (id_pelanggan, no_polisi, merk, model, tahun) VALUES (?, ?, ?, ?, ?)");
$modelParam = ($model === '') ? null : $model;
$tahunParam = ($tahun === null) ? null : (int)$tahun;
$stmt->bind_param('isssi', $id_pelanggan, $no_polisi, $merk, $modelParam, $tahunParam);

if ($stmt->execute()) {
    setFlash('success', 'Kendaraan berhasil didaftarkan.');
    redirect(BASE_URL . 'kendaraan/index.php');
} else {
    setFlash('danger', 'Gagal mendaftarkan kendaraan: ' . $conn->error);
    redirect(BASE_URL . 'kendaraan/tambah.php');
}
$stmt->close();
