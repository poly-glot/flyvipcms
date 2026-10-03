<?= $this->extend('layouts/base') ?>

<?= $this->section('body') ?>
<main class="narrow hero">
    <h1>FLY VIP</h1>
    <p>Enjoy the benefits of a friendly aircraft, whenever and wherever. The sky at your fingertips.</p>
    <p><a class="button" href="<?= url_to('login') ?>">Member sign in</a></p>
    <p class="muted">Memberships are arranged with our concierge team.</p>
</main>
<?= $this->endSection() ?>
