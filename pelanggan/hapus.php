<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID Pelanggan tidak valid.');
    redirect(BASE_URL . 'pelanggan/index.php');
}

// Ambil nama pelanggan untuk pesan yang lebih informatif
$stmt = $conn->prepare("SELECT nama FROM pelanggan WHERE id_pelanggan = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$pelanggan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pelanggan) {
    setFlash('danger', 'Pelanggan tidak ditemukan.');
    redirect(BASE_URL . 'pelanggan/index.php');
}

// Cek apakah masih memiliki kendaraan – ambil detail plat nomornya
$stmt = $conn->prepare("SELECT no_polisi, merk FROM kendaraan WHERE id_pelanggan = ? ORDER BY no_polisi ASC");
$stmt->bind_param('i', $id);
$stmt->execute();
$kendaraanResult = $stmt->get_result();
$stmt->close();

if ($kendaraanResult->num_rows > 0) {
    $platList = [];
    while ($k = $kendaraanResult->fetch_assoc()) {
        $platList[] = '<strong>' . htmlspecialchars($k['no_polisi'], ENT_QUOTES) . '</strong> (' . htmlspecialchars($k['merk'], ENT_QUOTES) . ')';
    }
    $jumlah    = count($platList);
    $nama      = htmlspecialchars($pelanggan['nama'], ENT_QUOTES);
    $platStr   = implode(', ', $platList);
    $linkKend  = BASE_URL . 'kendaraan/index.php?search=' . urlencode($pelanggan['nama']);

    $msg  = "Pelanggan <strong>{$nama}</strong> tidak bisa dihapus karena masih memiliki ";
    $msg .= "<strong>{$jumlah} kendaraan</strong> terdaftar: {$platStr}. ";
    $msg .= "<a href=\"{$linkKend}\" class=\"alert-link\">Hapus kendaraan tersebut terlebih dahulu</a>, lalu coba hapus pelanggan ini kembali.";

    $_SESSION['flash'] = ['type' => 'danger', 'message' => $msg, 'raw' => true];
    redirect(BASE_URL . 'pelanggan/index.php');
}

// Tidak ada kendaraan – aman untuk dihapus
$stmt = $conn->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    setFlash('success', 'Pelanggan "' . $pelanggan['nama'] . '" berhasil dihapus.');
} else {
    setFlash('danger', 'Gagal menghapus pelanggan: ' . $conn->error);
}
$stmt->close();

redirect(BASE_URL . 'pelanggan/index.php');
