<?= $this->extend('layouts/base') ?>

<?= $this->section('body') ?>
<header class="bar">
    <a class="brand" href="<?= site_url('portal') ?>">FLY VIP</a>
    <nav>
        <?php foreach (['portal' => 'Account', 'portal/points' => 'Points', 'portal/reservations' => 'Reservations', 'portal/profile' => 'Profile'] as $path => $label): ?>
            <a href="<?= site_url($path) ?>" <?= ($path === 'portal' ? uri_string() === $path : str_starts_with(uri_string(), $path)) ? 'aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach ?>
    </nav>
    <form class="logout" method="post" action="<?= url_to('logout') ?>">
        <?= csrf_field() ?>
        <a href="<?= site_url('account/password') ?>"><?= esc(auth()->user()?->username) ?></a>
        <button type="submit">Sign out</button>
    </form>
</header>
<main>
    <?= $this->include('partials/flash') ?>
    <?= $this->renderSection('content') ?>
</main>
<?= $this->endSection() ?>
