<?= $this->extend('layouts/shell') ?>

<?php
$editing = $row !== [];
$essentials = array_values(array_filter($fields, static fn (array $f): bool => $f['list'] ?? true));
$details = array_values(array_filter($fields, static fn (array $f): bool => !($f['list'] ?? true)));
?>
<?= $this->section('title') ?><?= $editing ? 'Edit' : 'New' ?> <?= esc(strtolower($title)) ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc(ui_nav_label("admin/{$slug}", $title . 's')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url($action) ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Essentials</h2>
            <p>What shows up in the list.</p>
        </div>
        <div class="fields">
            <?php foreach ($essentials as $field): ?>
                <?= view('partials/field', ['field' => $field, 'row' => $row]) ?>
            <?php endforeach ?>
        </div>
    </section>
    <?php if ($details !== []): ?>
        <section class="form-section">
            <div class="form-section__intro">
                <h2>More details</h2>
                <p>Optional information kept on the record.</p>
            </div>
            <div class="fields">
                <?php foreach ($details as $field): ?>
                    <?= view('partials/field', ['field' => $field, 'row' => $row]) ?>
                <?php endforeach ?>
            </div>
        </section>
    <?php endif ?>
    <div class="form-actions">
        <button class="button" type="submit">Save <?= esc(strtolower($title)) ?></button>
        <a class="button button--quiet" href="<?= site_url("admin/{$slug}") ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
