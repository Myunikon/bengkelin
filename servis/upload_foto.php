<?php
session_start();
require '../config.php';
require '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'servis/index.php');
}

$id_servis = (int)($_POST['id_servis'] ?? 0);

if ($id_servis <= 0) {
    setFlash('danger', 'ID Servis tidak valid.');
    redirect(BASE_URL . 'servis/index.php');
}

// Cek data servis
$stmt = $conn->prepare("SELECT status FROM servis WHERE id_servis = ?");
$stmt->bind_param('i', $id_servis);
$stmt->execute();
$servis = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$servis) {
    setFlash('danger', 'Transaksi servis tidak ditemukan.');
    redirect(BASE_URL . 'servis/index.php');
}

if (!in_array($servis['status'], ['antre', 'dikerjakan'], true)) {
    setFlash('danger', 'Tidak dapat menambahkan foto karena servis sudah selesai.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");
}

if (empty($_FILES['fotos']['name'][0])) {
    setFlash('danger', 'Pilih minimal satu file foto untuk diunggah.');
    redirect(BASE_URL . "servis/detail.php?id=$id_servis");
}

// Buat folder uploads jika belum ada
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
$max_size = 2 * 1024 * 1024; // 2MB
$success_count = 0;
$errors = [];

$files = $_FILES['fotos'];
$total_files = count($files['name']);

for ($i = 0; $i < $total_files; $i++) {
    $name = $files['name'][$i];
    $tmp_name = $files['tmp_name'][$i];
    $size = $files['size'][$i];
    $error = $files['error'][$i];

    if ($error !== UPLOAD_ERR_OK) {
        $errors[] = "Gagal mengunggah file \"$name\". Code: $error";
        continue;
    }

    if ($size > $max_size) {
        $errors[] = "Ukuran file \"$name\" melebihi batas 2MB.";
        continue;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        $errors[] = "Format file \"$name\" tidak diizinkan. Gunakan JPG, PNG, atau GIF.";
        continue;
    }

    // Rename file dengan salting time() & uniqid()
    $new_filename = time() . '_' . uniqid() . '.' . $ext;
    $target_file = UPLOAD_DIR . $new_filename;

    if (move_uploaded_file($tmp_name, $target_file)) {
        // Simpan ke database
        $stmt_pic = $conn->prepare("INSERT INTO servis_foto (id_servis, path_file, uploaded_at) VALUES (?, ?, NOW())");
        $stmt_pic->bind_param('is', $id_servis, $new_filename);
        if ($stmt_pic->execute()) {
            $success_count++;
        } else {
            $errors[] = "Gagal mencatat foto \"$name\" ke database: " . $conn->error;
            // Hapus file fisik jika gagal insert DB
            unlink($target_file);
        }
        $stmt_pic->close();
    } else {
        $errors[] = "Gagal memindahkan file \"$name\" ke direktori server.";
    }
}

if ($success_count > 0) {
    if (!empty($errors)) {
        setFlash('warning', "$success_count foto berhasil diunggah, namun terdapat beberapa kesalahan: " . implode('; ', $errors));
    } else {
        setFlash('success', "$success_count foto dokumentasi berhasil diunggah.");
    }
} else {
    setFlash('danger', "Gagal mengunggah foto: " . implode('; ', $errors));
}

redirect(BASE_URL . "servis/detail.php?id=$id_servis");
