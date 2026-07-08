<?php
/**
 * @var string $tgl_awal
 * @var string $tgl_akhir
 * @var string $preset
 */
?>
<div class="card stat-card mb-4 no-print">
    <div class="card-body p-4">
        <form action="" method="GET" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-medium text-secondary small">Quick Filter</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-2"><i class='bx bx-flash'></i></span>
                    <select id="presetSelect" name="preset" class="form-select border-start-0 shadow-none">
                        <option value="hari_ini" <?= $preset === 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="7_hari" <?= $preset === '7_hari' ? 'selected' : '' ?>>7 Hari Terakhir</option>
                        <option value="30_hari" <?= $preset === '30_hari' ? 'selected' : '' ?>>30 Hari Terakhir</option>
                        <option value="bulan_ini" <?= $preset === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="tahun_ini" <?= $preset === 'tahun_ini' ? 'selected' : '' ?>>Tahun Ini</option>
                        <option value="custom" <?= $preset === 'custom' ? 'selected' : '' ?>>Kustom Tanggal</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-medium text-secondary small">Tanggal Awal</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-2"><i class='bx bx-calendar'></i></span>
                    <input type="date" id="tgl_awal" name="tgl_awal" class="form-control border-start-0 shadow-none" value="<?= $tgl_awal ?>">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-medium text-secondary small">Tanggal Akhir</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-2"><i class='bx bx-calendar-check'></i></span>
                    <input type="date" id="tgl_akhir" name="tgl_akhir" class="form-control border-start-0 shadow-none" value="<?= $tgl_akhir ?>">
                </div>
            </div>

            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                    <a href="?" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium" title="Reset Filter">
                        <i class="bx bx-refresh me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const preset = document.getElementById('presetSelect');
    const tglAwal = document.getElementById('tgl_awal');
    const tglAkhir = document.getElementById('tgl_akhir');

    preset.addEventListener('change', function() {
        const today = new Date();
        let start = new Date();

        switch(this.value) {
            case 'hari_ini':
                start = today;
                break;
            case '7_hari':
                start.setDate(today.getDate() - 7);
                break;
            case '30_hari':
                start.setDate(today.getDate() - 30);
                break;
            case 'bulan_ini':
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                break;
            case 'tahun_ini':
                start = new Date(today.getFullYear(), 0, 1);
                break;
            case 'custom':
                return; // Biarkan user mengisi sendiri
        }

        // Format ke YYYY-MM-DD agar dibaca input type="date"
        tglAwal.value = start.toISOString().split('T')[0];
        tglAkhir.value = today.toISOString().split('T')[0];
    });
});
</script>
