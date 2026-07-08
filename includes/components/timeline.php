<?php
/**
 * @var array $activities
 * Format array:
 * [
 * ['title' => 'Judul', 'desc' => 'Deskripsi', 'time' => '10 mnt lalu', 'status' => 'dikerjakan/antre/diambil']
 * ]
 */
$activities = $activities ?? [];
?>
<div class="card stat-card h-100 p-3">
    <h6 class="fw-semibold text-muted mb-4">Aktivitas Terkini</h6>
    <div class="timeline ms-2">
        <?php if (empty($activities)): ?>
            <p class="text-muted small py-3 text-center">Belum ada aktivitas tercatat hari ini.</p>
        <?php else: ?>
            <?php foreach ($activities as $act): ?>
                <div class="timeline-item">
                    <div class="timeline-dot <?= isset($act['status']) ? e($act['status']) : 'antre' ?>">
                        <i class='bx <?= $act['status'] === 'diambil' ? 'bx-check' : ($act['status'] === 'dikerjakan' ? 'bx-wrench' : 'bx-log-in') ?>'></i>
                    </div>
                    <div>
                        <p class="mb-0 small fw-semibold"><?= e($act['title']) ?></p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;"><?= e($act['desc']) ?></p>
                        <small class="text-muted" style="font-size: 0.7rem;"><?= e($act['time']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
