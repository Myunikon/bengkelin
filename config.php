<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Makassar');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bengkel');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die('<p style="font-family:sans-serif;color:red;">Koneksi database gagal: ' . $conn->connect_error . '</p>');
}

$conn->set_charset('utf8mb4');

define('APP_NAME',  'Bengkelin');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if (in_array(basename($dir), ['servis', 'laporan', 'pelanggan', 'kendaraan', 'mekanik', 'sparepart', 'includes'], true)) {
    $dir = dirname($dir);
}
$dir = rtrim(str_replace('\\', '/', $dir), '/') . '/';
define('BASE_URL', $protocol . $host . $dir);

define('UPLOAD_DIR', __DIR__ . '/assets/uploads/servis/');
define('UPLOAD_URL',  BASE_URL . 'assets/uploads/servis/');

define('STATUS_TRANSITIONS', [
    'antre'      => ['dikerjakan', 'dibatalkan'],
    'dikerjakan' => ['selesai'],
    'selesai'    => ['diambil'],
    'diambil'    => [],
    'dibatalkan' => [],
]);

require_once __DIR__ . '/includes/functions.php';
