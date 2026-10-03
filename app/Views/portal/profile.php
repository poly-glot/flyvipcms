<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Profile<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= $readOnly ? 'View only. Your primary member manages these details.' : 'Keep your contact and travel documents current.' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url('portal/profile') ?>">
    <?= csrf_field() ?>
    <fieldset class="form-section--plain form" <?= $readOnly ? 'disabled' : '' ?>>
        <?= view('partials/profile_fields', ['values' => $profile, 'withName' => false]) ?>
    </fieldset>
    <?php if (!$readOnly): ?>
        <div class="form-actions"><button class="button" type="submit">Save profile</button></div>
    <?php endif ?>
</form>
<?= $this->endSection() ?>
