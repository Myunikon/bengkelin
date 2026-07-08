<?php
// includes/report_helper.php

function initReportPeriod() {
    // Menghitung tanggal default (Awal bulan ini sampai hari ini)
    $firstDayOfMonth = date('Y-m-01');
    $today = date('Y-m-d');

    // Menangkap input dari filter atau menggunakan default
    $tgl_awal  = $_GET['tgl_awal'] ?? $firstDayOfMonth;
    $tgl_akhir = $_GET['tgl_akhir'] ?? $today;
    $preset    = $_GET['preset'] ?? 'bulan_ini';

    // Format tampilan teks di Header Laporan (Contoh: 01 Jul 2026 s/d 07 Jul 2026)
    $periodeText = date('d M Y', strtotime($tgl_awal)) . ' s/d ' . date('d M Y', strtotime($tgl_akhir));

    return [
        'tgl_awal'    => $tgl_awal,
        'tgl_akhir'   => $tgl_akhir,
        'preset'      => $preset,
        'periodeText' => $periodeText
    ];
}
