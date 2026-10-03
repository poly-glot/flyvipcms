<?= $this->extend('layouts/shell') ?>

<?php
$columns = array_values(array_filter($fields, static fn (array $f): bool => ($f['list'] ?? true) && $f['type'] !== 'textarea'));
$heading = ui_nav_label("admin/{$slug}", $title . 's');
$presenter = $slug === 'pilots' ? 'admin/crud/rows_pilots' : 'admin/crud/rows_table';
?>
<?= $this->section('title') ?><?= esc($heading) ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= count($rows) ?> <?= count($rows) === 1 ? 'record' : 'records' ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button" href="<?= site_url("admin/{$slug}/new") ?>"><?= ui_icon('plus', 'icon icon--sm') ?>Add <?= esc(strtolower($title)) ?></a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if ($rows === []): ?>
    <?= view('partials/empty', ['icon' => 'inbox', 'title' => 'Nothing here yet', 'text' => 'Add the first ' . strtolower($title) . ' to get started.', 'actionUrl' => site_url("admin/{$slug}/new"), 'actionLabel' => 'Add ' . strtolower($title)]) ?>
<?php else: ?>
    <div data-filter>
        <div class="toolbar">
            <label class="search">
                <span class="sr-only">Search <?= esc(strtolower($heading)) ?></span>
                <?= ui_icon('search', 'icon icon--sm') ?>
                <input type="search" placeholder="Search <?= esc(strtolower($heading), 'attr') ?>" data-filter-input autocomplete="off">
            </label>
        </div>
        <?= view($presenter, ['rows' => $rows, 'columns' => $columns, 'slug' => $slug, 'title' => $title]) ?>
        <div hidden data-filter-empty>
            <?= view('partials/empty', ['icon' => 'search', 'title' => 'No matches', 'text' => 'Try a different search term.']) ?>
        </div>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
