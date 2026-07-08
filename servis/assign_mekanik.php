<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'servis/index.php');
}

$id_servis = (int)($_POST['id_servis'] ?? 0);
$id_mekanik = (int)($_POST['id_mekanik'] ?? 0);

if ($id_servis <= 0 || $id_mekanik <= 0) {
    setFlash('danger', 'Parameter tidak lengkap.');
    redirect(BASE_URL . 'servis/index.php');
}

// Ambil data status servis
$stmt = $conn->prepare("SELECT status FROM servis WHERE id_servis = ?");
$stmt->bind_param('i', $id_servis);
$stmt->execute();
$servis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$servis) {
    setFlash('danger', 'Transaksi servis tidak ditemukan.');
    redirect(BASE_URL . 'servis/index.php');
}

if ($servis['status'] !== 'antre') {
    setFlash('danger', 'Mekanik hanya dapat ditugaskan saat servis masih berstatus antre.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");
}

// Cek apakah mekanik benar-benar tersedia
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM mekanik WHERE id_mekanik = ? AND status = 'tersedia'");
$stmt->bind_param('i', $id_mekanik);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($m['c'] === 0) {
    setFlash('danger', 'Mekanik yang dipilih sedang tidak tersedia / sibuk.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");
}

// Update mekanik servis
$stmt = $conn->prepare("UPDATE servis SET id_mekanik = ? WHERE id_servis = ?");
$stmt->bind_param('ii', $id_mekanik, $id_servis);

if ($stmt->execute()) {
    setFlash('success', 'Mekanik berhasil ditugaskan ke transaksi ini.');
} else {
    setFlash('danger', 'Gagal menugaskan mekanik: ' . $conn->error);
}
$stmt->close();

redirect(BASE_URL . "servis/detail.php?id=$id_servis");
