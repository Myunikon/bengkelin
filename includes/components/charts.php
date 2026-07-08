<?php
/**
 * @var array $revenueData  Array data mingguan pendapatan (ex: [12.5, 15.0, 9.2, 14.8])
 * @var array $serviceData  Array volume servis bulanan (ex: [45, 62, 58])
 * @var array $serviceLabels Array label bulan untuk servis (ex: ['Mei', 'Jun', 'Jul'])
 */

// Menyediakan nilai default jika data belum dikirim dari file utama
$revenueData   = $revenueData ?? [0, 0, 0, 0];
$serviceData   = $serviceData ?? [0, 0, 0];
$serviceLabels = $serviceLabels ?? ['Awal', 'Tengah', 'Akhir'];
?>
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card stat-card h-100 p-3">
            <h6 class="fw-semibold text-muted mb-3">Pendapatan Mingguan (Bulan Ini)</h6>
            <div style="height: 250px;"><canvas id="revenueChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card stat-card h-100 p-3">
            <h6 class="fw-semibold text-muted mb-3">Volume Servis (3 Bulan Terakhir)</h6>
            <div style="height: 250px;"><canvas id="serviceChart"></canvas></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Line Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            datasets: [{
                label: 'Pendapatan (Juta Rp)',
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 2. Bar Chart
    new Chart(document.getElementById('serviceChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($serviceLabels) ?>,
            datasets: [{
                label: 'Jumlah Servis',
                data: <?= json_encode($serviceData) ?>,
                backgroundColor: ['#e5e7eb', '#e5e7eb', '#10b981'],
                borderRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
});
</script>
