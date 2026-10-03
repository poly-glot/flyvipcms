<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>
<div class="head"><h1>New reservation</h1><span class="muted"><?= esc(number_format((float) $balance, 2)) ?> points available</span></div>
<form class="grid" method="post" action="<?= site_url('portal/reservations') ?>">
    <?= csrf_field() ?>
    <?= $this->include('partials/booking_fields') ?>
    <div class="full"><button type="submit">Book</button> <a href="<?= site_url('portal/reservations') ?>">Cancel</a></div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?><script src="<?= base_url('js/booking.js') ?>"></script><?= $this->endSection() ?>
