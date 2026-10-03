<?= $this->extend('layouts/auth') ?>

<?= $this->section('main') ?>
<section class="card">
    <h1>Sign in</h1>
    <?= $this->include('partials/flash') ?>
    <form method="post" action="<?= url_to('login') ?>">
        <?= csrf_field() ?>
        <label>Email
            <input type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button type="submit">Sign in</button>
    </form>
</section>
<?= $this->endSection() ?>
