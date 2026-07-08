<?php
/**
 * @var string $label
 * @var string $name
 * @var string $type
 * @var string $icon
 * @var string|null $value
 * @var string|null $placeholder
 * @var bool|null $required
 * @var string|null $formText
 */

$value       = $value ?? '';
$requiredAttr = isset($required) && $required ? 'required' : '';
$placeholder  = $placeholder ?? '';
$id           = $name;
?>

<div class="mb-4">
    <label for="<?= $id ?>" class="form-label fw-medium text-secondary">
        <?= $label ?> <?= isset($required) && $required ? '<span class="text-danger">*</span>' : '' ?>
    </label>
    <div class="input-group">
        <span class="input-group-text bg-light border-end-0 text-muted px-3">
            <i class='bx <?= $icon ?>'></i>
        </span>
        <?php if ($type === 'textarea'): ?>
            <textarea id="<?= $id ?>" name="<?= $name ?>" class="form-control border-start-0 shadow-none ps-0" rows="3" placeholder="<?= $placeholder ?>" <?= $requiredAttr ?>><?= e($value) ?></textarea>
        <?php else: ?>
            <input type="<?= $type ?>" id="<?= $id ?>" name="<?= $name ?>" class="form-control border-start-0 shadow-none ps-0" value="<?= e($value) ?>" placeholder="<?= $placeholder ?>" <?= $requiredAttr ?>>
        <?php endif; ?>
    </div>
    <?php if (isset($formText)): ?>
        <div class="form-text small mt-1 text-muted"><?= $formText ?></div>
    <?php endif; ?>
</div>
<?php
// Reset variabel agar tidak bocor ke include berikutnya
unset($label, $name, $type, $icon, $value, $placeholder, $required, $requiredAttr, $formText, $id);
?>
