<?php
// Flash alert component
$flash = getFlash();
if ($flash):
    $alertType = htmlspecialchars($flash['type']);
    $alertIcon = 'bi-info-circle-fill';
    
    if ($alertType === 'success') {
        $alertIcon = 'bi-check-circle-fill';
    } elseif ($alertType === 'danger') {
        $alertIcon = 'bi-exclamation-triangle-fill';
    } elseif ($alertType === 'warning') {
        $alertIcon = 'bi-exclamation-circle-fill';
    }
?>
    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show d-flex align-items-center shadow-sm border-0 mb-4 rounded-3" role="alert">
        <i class="bi <?= $alertIcon ?> fs-4 me-3"></i>
        <div class="flex-grow-1">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
