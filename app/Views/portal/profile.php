<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>
<h1>Profile</h1>
<form class="grid" method="post" action="<?= site_url('portal/profile') ?>">
    <?= csrf_field() ?>
    <fieldset class="full grid" <?= $readOnly ? 'disabled' : '' ?>>
        <?= view('partials/profile_fields', ['values' => $profile, 'withName' => false]) ?>
    </fieldset>
    <?php if (!$readOnly): ?>
        <div class="full"><button type="submit">Save profile</button></div>
    <?php endif ?>
</form>
<?= $this->endSection() ?>
