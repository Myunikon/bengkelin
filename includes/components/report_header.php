<?php
/**
 * @var string $title
 * @var string $icon
 * @var string $periodeText
 */
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bx <?= $icon ?> text-primary me-2"></i> <?= $title ?>
        </h4>
        <span class="text-muted small">Periode Laporan: <strong><?= $periodeText ?></strong></span>
    </div>
    <button onclick="window.print()" class="btn btn-light border btn-sm px-3 py-2 rounded-3 no-print">
        <i class="bx bx-printer me-1"></i> Cetak Laporan
    </button>
</div>
