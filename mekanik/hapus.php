<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Mekanik tidak valid.');
    redirect(BASE_URL . 'mekanik/index.php');
}

// Cek apakah ada tugas servis aktif (antre/dikerjakan)
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM servis WHERE id_mekanik = ? AND status IN ('antre', 'dikerjakan')");
$stmt->bind_param('i', $id);
$stmt->execute();
$active = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($active['c'] > 0) {
    setFlash('danger', 'Mekanik tidak bisa dihapus atau dinonaktifkan karena sedang menangani servis aktif.');
    redirect(BASE_URL . 'mekanik/index.php');
}

// Cek apakah mekanik memiliki riwayat servis apa saja (selesai/diambil/dibatalkan)
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM servis WHERE id_mekanik = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$history = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($history['c'] > 0) {
    // Soft delete: set status to 'nonaktif'
    $stmt = $conn->prepare("UPDATE mekanik SET status = 'nonaktif' WHERE id_mekanik = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        setFlash('success', 'Mekanik memiliki riwayat kerja, status berhasil dinonaktifkan (soft-delete).');
    } else {
        setFlash('danger', 'Gagal menonaktifkan mekanik: ' . $conn->error);
    }
    $stmt->close();
} else {
    // Hard delete: delete permanently
    $stmt = $conn->prepare("DELETE FROM mekanik WHERE id_mekanik = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        setFlash('success', 'Data mekanik berhasil dihapus secara permanen.');
    } else {
        setFlash('danger', 'Gagal menghapus mekanik: ' . $conn->error);
    }
    $stmt->close();
}

redirect(BASE_URL . 'mekanik/index.php');
