<?php
/**
 * @var array $headers
 * @var array $rows
 * @var string $emptyMessage
 */

$emptyMessage = $emptyMessage ?? 'Data tidak ditemukan.';
?>
<div class="card stat-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="border-bottom text-muted">
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <th class="fw-medium <?= (isset($header['center']) && $header['center']) ? 'text-center' : '' ?>" style="<?= isset($header['width']) ? 'width:'.$header['width'].'px;' : '' ?>">
                                <?= $header['label'] ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?= count($headers) ?>" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bx bx-file-blank fs-1 text-muted opacity-25"></i>
                                    <p class="text-muted mt-2 mb-0"><?= $emptyMessage ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $cells): ?>
                            <tr>
                                <?php foreach ($cells as $cell): ?>
                                    <td class="<?= isset($cell['class']) ? $cell['class'] : '' ?>">
                                        <?= $cell['content'] ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
