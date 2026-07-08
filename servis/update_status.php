<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'servis/index.php');
}

$id_servis = (int)($_POST['id_servis'] ?? 0);
$status_baru = trim($_POST['status_baru'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($id_servis <= 0 || $status_baru === '') {
    setFlash('danger', 'Parameter perubahan status tidak lengkap.');
    redirect(BASE_URL . 'servis/index.php');
}

// Ambil status saat ini dan mekanik terkait
$stmt = $conn->prepare("SELECT status, id_mekanik FROM servis WHERE id_servis = ?");
$stmt->bind_param('i', $id_servis);
$stmt->execute();
$servis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$servis) {
    setFlash('danger', 'Transaksi servis tidak ditemukan.');
    redirect(BASE_URL . 'servis/index.php');
}

// Validasi transisi status
if (!isValidTransition($servis['status'], $status_baru)) {
    setFlash('danger', 'Transisi status dari "' . ucfirst($servis['status']) . '" ke "' . ucfirst($status_baru) . '" tidak diperbolehkan.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");
}

// Cek syarat khusus: dikerjakan wajib ada mekanik
if ($status_baru === 'dikerjakan' && !$servis['id_mekanik']) {
    setFlash('danger', 'Servis tidak dapat dikerjakan sebelum mekanik ditugaskan.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");
}

// Jalankan transaksi database
$conn->begin_transaction();

try {
    // 1. Update status servis
    $stmt = $conn->prepare("UPDATE servis SET status = ? WHERE id_servis = ?");
    $stmt->bind_param('si', $status_baru, $id_servis);
    $stmt->execute();
    $stmt->close();

    // 2. Insert riwayat status
    $stmt_log = $conn->prepare("INSERT INTO riwayat_status (id_servis, status_baru, waktu_perubahan, keterangan) VALUES (?, ?, NOW(), ?)");
    $ketParam = ($keterangan === '') ? null : $keterangan;
    $stmt_log->bind_param('iss', $id_servis, $status_baru, $ketParam);
    $stmt_log->execute();
    $stmt_log->close();

    // 3. Update status mekanik jika ada mekanik ditugaskan
    if ($servis['id_mekanik']) {
        $id_mekanik = $servis['id_mekanik'];
        if ($status_baru === 'dikerjakan') {
            // Set mekanik jadi sibuk
            $stmt_m = $conn->prepare("UPDATE mekanik SET status = 'sibuk' WHERE id_mekanik = ?");
            $stmt_m->bind_param('i', $id_mekanik);
            $stmt_m->execute();
            $stmt_m->close();
        } elseif (in_array($status_baru, ['selesai', 'diambil', 'dibatalkan'], true)) {
            // Set mekanik jadi tersedia kembali
            $stmt_m = $conn->prepare("UPDATE mekanik SET status = 'tersedia' WHERE id_mekanik = ?");
            $stmt_m->bind_param('i', $id_mekanik);
            $stmt_m->execute();
            $stmt_m->close();
        }
    }

    $conn->commit();
    setFlash('success', 'Status transaksi berhasil diperbarui menjadi "' . ucfirst($status_baru) . '".');
} catch (Exception $e) {
    $conn->rollback();
    setFlash('danger', 'Sistem error saat merubah status: ' . $e->getMessage());
}

redirect(BASE_URL . "servis/detail.php?id=$id_servis");
