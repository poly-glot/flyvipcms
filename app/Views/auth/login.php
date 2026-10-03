<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Sign in<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="gate__heading">
    <h1>Sign in</h1>
    <p class="muted">Welcome back. Manage your flights and points.</p>
</div>
<?= view('partials/error_summary') ?>
<form class="form" method="post" action="<?= url_to('login') ?>">
    <?= csrf_field() ?>
    <div class="fields">
        <?= ui_field('email', 'Email', 'email', null, ['autocomplete' => 'email', 'autofocus' => true, 'field_class' => 'field--full', 'required' => true]) ?>
        <?= ui_field('password', 'Password', 'password', null, ['autocomplete' => 'current-password', 'field_class' => 'field--full', 'required' => true]) ?>
    </div>
    <div class="form-actions form-actions--bare">
        <button class="button button--block" type="submit">Sign in</button>
    </div>
</form>
<p class="muted gate__note">Memberships are arranged with our concierge team.</p>
<?= $this->endSection() ?>
