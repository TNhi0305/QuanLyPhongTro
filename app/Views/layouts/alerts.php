<?php
$flash = get_flash();
if ($flash):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $icon = match($type) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-octagon-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        default   => 'bi-info-circle-fill'
    };
?>
<div class="alert alert-<?= e($type) ?> alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bi <?= $icon ?> fs-5 me-2"></i>
    <div class="flex-grow-1">
        <?= $message ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
