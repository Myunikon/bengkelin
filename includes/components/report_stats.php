<?php
/**
 * @var array $reportStats
 * Format $reportStats = [
 * ['label' => 'Total', 'value' => '10', 'icon' => 'bx-check', 'color' => 'blue']
 * ]
 */
?>
<div class="row g-3 mb-4">
    <?php foreach ($reportStats as $stat): ?>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="stat-icon <?= $stat['color'] ?> me-3">
                        <i class="bx <?= $stat['icon'] ?>"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;"><?= $stat['label'] ?></h6>
                        <h5 class="mb-0 fw-bold text-dark"><?= $stat['value'] ?></h5>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
