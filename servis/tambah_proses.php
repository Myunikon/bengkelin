<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'servis/index.php');
}

$id_kendaraan = (int)($_POST['id_kendaraan'] ?? 0);
$tanggal_masuk = trim($_POST['tanggal_masuk'] ?? '');
$biaya_jasa = (float)($_POST['biaya_jasa'] ?? 0);
$keterangan = trim($_POST['keterangan'] ?? '');

// Validation
if ($id_kendaraan <= 0) {
    setFlash('danger', 'Kendaraan wajib dipilih.');
    redirect(BASE_URL . 'servis/tambah.php');
}

if ($tanggal_masuk === '') {
    setFlash('danger', 'Tanggal masuk wajib diisi.');
    redirect(BASE_URL . 'servis/tambah.php');
}

// Validasi: tanggal tidak boleh lebih dari 7 hari ke depan
$maxDate = date('Y-m-d', strtotime('+7 days'));
if ($tanggal_masuk > $maxDate) {
    setFlash('danger', 'Tanggal masuk tidak boleh lebih dari 7 hari ke depan.');
    redirect(BASE_URL . 'servis/tambah.php');
}

if ($biaya_jasa < 0) {
    setFlash('danger', 'Biaya jasa tidak boleh kurang dari 0.');
    redirect(BASE_URL . 'servis/tambah.php');
}

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Insert Servis
    $stmt = $conn->prepare("INSERT INTO servis (id_kendaraan, id_mekanik, tanggal_masuk, status, biaya_jasa, keterangan) VALUES (?, NULL, ?, 'antre', ?, ?)");
    $ketParam = ($keterangan === '') ? null : $keterangan;
    $stmt->bind_param('isds', $id_kendaraan, $tanggal_masuk, $biaya_jasa, $ketParam);
    $stmt->execute();
    $id_servis = $conn->insert_id;
    $stmt->close();

    // 2. Insert Riwayat Status
    $stmt_log = $conn->prepare("INSERT INTO riwayat_status (id_servis, status_baru, waktu_perubahan, keterangan) VALUES (?, 'antre', NOW(), 'Pendaftaran servis baru (masuk antrean).')");
    $stmt_log->bind_param('i', $id_servis);
    $stmt_log->execute();
    $stmt_log->close();

    $conn->commit();
    setFlash('success', 'Transaksi servis berhasil didaftarkan.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");

} catch (Exception $e) {
    $conn->rollback();
    setFlash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    redirect(BASE_URL . 'servis/tambah.php');
}
