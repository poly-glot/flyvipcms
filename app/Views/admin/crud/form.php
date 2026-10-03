<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1><?= $row === [] ? 'New' : 'Edit' ?> <?= esc(strtolower($title)) ?></h1>
</div>
<form class="grid" method="post" action="<?= site_url($action) ?>">
    <?= csrf_field() ?>
    <?php foreach ($fields as $field): ?>
        <?= view('partials/field', ['field' => $field, 'row' => $row]) ?>
    <?php endforeach ?>
    <div class="full">
        <button type="submit">Save</button>
        <a href="<?= site_url("admin/{$slug}") ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
