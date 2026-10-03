<div class="data__actions">
    <a class="icon-button" href="<?= site_url("admin/{$slug}/{$row['id']}/edit") ?>" aria-label="Edit <?= esc(strtolower($title), 'attr') ?>" title="Edit"><?= ui_icon('pencil', 'icon icon--sm') ?></a>
    <form method="post" action="<?= site_url("admin/{$slug}/{$row['id']}/delete") ?>" data-confirm="This cannot be undone." data-confirm-title="Delete this <?= esc(strtolower($title), 'attr') ?>?" data-confirm-action="Delete">
        <?= csrf_field() ?>
        <button class="icon-button icon-button--danger" type="submit" aria-label="Delete <?= esc(strtolower($title), 'attr') ?>" title="Delete"><?= ui_icon('trash', 'icon icon--sm') ?></button>
    </form>
</div>
