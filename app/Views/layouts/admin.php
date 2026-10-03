<?= $this->extend('layouts/base') ?>

<?= $this->section('body') ?>
<header class="bar">
    <a class="brand" href="<?= site_url('admin') ?>">FLY VIP <small>admin</small></a>
    <nav>
        <?php foreach (['admin' => 'Dashboard', 'admin/members' => 'Members', 'admin/payments' => 'Payments', 'admin/points' => 'Points', 'admin/reservations' => 'Reservations', 'admin/flight-schedule' => 'Schedule', 'admin/aircrafts' => 'Aircraft', 'admin/aircraft-types' => 'Types', 'admin/maintenance' => 'Maintenance', 'admin/airports' => 'Airports', 'admin/routes' => 'Routes', 'admin/pilots' => 'Pilots', 'admin/pilot-documents' => 'Documents', 'admin/pilot-certifications' => 'Certifications'] as $path => $label): ?>
            <a href="<?= site_url($path) ?>" <?= ($path === 'admin' ? uri_string() === $path : str_starts_with(uri_string(), $path)) ? 'aria-current="page"' : '' ?>><?= $label ?></a>
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
