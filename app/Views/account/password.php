<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Password<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>Change the password you use to sign in.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url('account/password') ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Sign-in password</h2>
            <p>Use at least 8 characters. It must differ from your current password.</p>
        </div>
        <div class="fields fields--narrow">
            <?= ui_field('current_password', 'Current password', 'password', null, ['autocomplete' => 'current-password', 'field_class' => 'field--full', 'required' => true]) ?>
            <?= ui_field('new_password', 'New password', 'password', null, ['autocomplete' => 'new-password', 'field_class' => 'field--full', 'minlength' => 8, 'required' => true]) ?>
            <?= ui_field('confirm_password', 'Confirm new password', 'password', null, ['autocomplete' => 'new-password', 'field_class' => 'field--full', 'required' => true]) ?>
        </div>
    </section>
    <div class="form-actions"><button class="button" type="submit">Update password</button></div>
</form>
<?= $this->endSection() ?>
