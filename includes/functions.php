<?php

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function rupiah(float $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function isValidTransition(string $from, string $to): bool
{
    return isset(STATUS_TRANSITIONS[$from]) && in_array($to, STATUS_TRANSITIONS[$from], true);
}

function statusColor(string $status): string
{
    $map = [
        'antre'      => 'warning',
        'dikerjakan' => 'primary',
        'selesai'    => 'success',
        'diambil'    => 'purple',
        'dibatalkan' => 'danger',
        'tersedia'   => 'success',
        'sibuk'      => 'warning',
        'nonaktif'   => 'secondary'
    ];
    return $map[$status] ?? 'secondary';
}


function statusLabel(string $status): string
{
    $labels = [
        'dikerjakan' => 'Dikerjakan (Mulai Servis)',
        'selesai'    => 'Selesai (Siap Diambil)',
        'diambil'    => 'Diambil (Selesai Pembayaran)',
        'dibatalkan' => 'Batalkan Servis'
    ];
    return $labels[$status] ?? ucfirst($status);
}


// ============================================================
// Navigasi & Redirect
// ============================================================

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * @param string $dir  Nama folder halaman (contoh: 'pelanggan', 'laporan', atau 'root' untuk file di root project)
 * @param string $file Nama file spesifik (opsional). Kalau kosong, dianggap aktif selama $dir cocok.
 */
function isActive(string $dir, string $file = ''): string
{
    static $currentFile = null;
    static $currentDir = null;

    if ($currentFile === null) {
        $currentFile = basename($_SERVER['PHP_SELF']);
        $currentDir  = basename(dirname($_SERVER['PHP_SELF']));
    }

    if ($dir === 'root') {
        return ($currentFile === $file) ? 'active' : '';
    }

    if ($currentDir === $dir) {
        if ($file !== '') {
            return ($currentFile === $file) ? 'active' : '';
        }
        return 'active';
    }

    return '';
}


// ============================================================
// Flash Message
// ============================================================

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash; // bisa berisi 'raw' => true untuk pesan HTML
    }
    return null;
}
