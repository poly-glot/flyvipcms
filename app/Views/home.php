<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Private jet membership<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="gate__heading">
    <h1>Fly when you want, where you want.</h1>
    <p class="muted">Enjoy the benefits of a friendly aircraft, whenever and wherever. The sky at your fingertips.</p>
</div>
<div class="form-actions form-actions--bare">
    <a class="button button--block" href="<?= url_to('login') ?>">Member sign in</a>
</div>
<p class="muted gate__note">Memberships are arranged with our concierge team.</p>
<?= $this->endSection() ?>
