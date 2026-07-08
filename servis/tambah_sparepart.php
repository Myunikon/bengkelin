<?php
header('Content-Type: application/json');
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Hanya menerima request POST.']);
    exit;
}

$id_servis = (int)($_POST['id_servis'] ?? 0);
$id_sparepart = (int)($_POST['id_sparepart'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);

if ($id_servis <= 0 || $id_sparepart <= 0 || $qty <= 0) {
    echo json_encode(['success' => false, 'message' => 'Input data tidak lengkap atau tidak valid.']);
    exit;
}

// 1. Cek status servis
$stmt = $conn->prepare("SELECT status FROM servis WHERE id_servis = ?");
$stmt->bind_param('i', $id_servis);
$stmt->execute();
$servis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$servis) {
    echo json_encode(['success' => false, 'message' => 'Transaksi servis tidak ditemukan.']);
    exit;
}

if (!in_array($servis['status'], ['antre', 'dikerjakan'], true)) {
    echo json_encode(['success' => false, 'message' => 'Sparepart hanya bisa ditambahkan saat servis masih aktif (antre/dikerjakan).']);
    exit;
}

// 2. Cek apakah sparepart sudah ada (tidak boleh duplikat sparepart dalam 1 servis)
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM servis_details WHERE id_servis = ? AND id_sparepart = ?");
$stmt->bind_param('ii', $id_servis, $id_sparepart);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($r['c'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Sparepart ini sudah terpasang. Hapus terlebih dahulu untuk mengubah jumlah.']);
    exit;
}

// 3. Ambil data stok & harga sparepart
$stmt = $conn->prepare("SELECT nama_part, stok, harga_jual FROM sparepart WHERE id_sparepart = ?");
$stmt->bind_param('i', $id_sparepart);
$stmt->execute();
$sp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sp) {
    echo json_encode(['success' => false, 'message' => 'Data sparepart tidak ditemukan.']);
    exit;
}

if ($qty > (int)$sp['stok']) {
    echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi. Tersedia: ' . $sp['stok'] . ' unit.']);
    exit;
}

// Jalankan transaksi: simpan detail & kurangi stok
$conn->begin_transaction();

try {
    $harga_satuan = (float)$sp['harga_jual'];
    $subtotal = $qty * $harga_satuan;

    // 1. Simpan detail
    $stmt = $conn->prepare("INSERT INTO servis_details (id_servis, id_sparepart, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iiidd', $id_servis, $id_sparepart, $qty, $harga_satuan, $subtotal);
    $stmt->execute();
    $id_detail = $conn->insert_id;
    $stmt->close();

    // 2. Kurangi stok
    $stmt_stok = $conn->prepare("UPDATE sparepart SET stok = stok - ? WHERE id_sparepart = ?");
    $stmt_stok->bind_param('ii', $qty, $id_sparepart);
    $stmt_stok->execute();
    $stmt_stok->close();

    $conn->commit();

    // Return data row baru untuk di-append di jQuery
    echo json_encode([
        'success' => true,
        'row' => [
            'id_detail' => $id_detail,
            'id_sparepart' => $id_sparepart,
            'nama_part' => $sp['nama_part'],
            'qty' => $qty,
            'harga_satuan' => $harga_satuan,
            'subtotal' => $subtotal
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
}
