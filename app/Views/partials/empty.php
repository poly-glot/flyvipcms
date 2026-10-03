<div class="empty">
    <span class="empty__art"><?= ui_icon($icon ?? 'inbox', 'icon icon--lg') ?></span>
    <h3><?= esc($title) ?></h3>
    <p><?= esc($text) ?></p>
    <?php if (isset($actionUrl)): ?>
        <a class="button" href="<?= esc($actionUrl, 'attr') ?>"><?= ui_icon('plus', 'icon icon--sm') ?><?= esc($actionLabel) ?></a>
    <?php endif ?>
</div>
