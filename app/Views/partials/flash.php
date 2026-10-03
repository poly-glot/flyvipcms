<?php
$toasts = [];

foreach (['success' => 'ok', 'message' => 'ok', 'error' => 'bad'] as $key => $tone) {
    $message = session()->getFlashdata($key);

    if (is_string($message) && $message !== '') {
        $toasts[] = [$tone, $message];
    }
}
?>
<div class="toasts" data-toasts aria-live="polite">
    <?php foreach ($toasts as [$tone, $message]): ?>
        <div class="toast toast--<?= $tone ?>" role="<?= $tone === 'bad' ? 'alert' : 'status' ?>" data-toast data-sticky="<?= $tone === 'bad' ? 'true' : 'false' ?>">
            <?= ui_icon($tone === 'bad' ? 'alert' : 'check') ?>
            <p><?= esc($message) ?></p>
            <button type="button" data-toast-close aria-label="Dismiss"><?= ui_icon('x', 'icon icon--sm') ?></button>
        </div>
    <?php endforeach ?>
</div>
