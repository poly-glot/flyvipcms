<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Book a flight<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc(ui_points($balance)) ?> points available<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url('portal/reservations') ?>">
    <?= csrf_field() ?>
    <?= view('partials/booking_fields') ?>
    <div class="form-actions">
        <button class="button" type="submit">Book flight</button>
        <a class="button button--quiet" href="<?= site_url('portal/reservations') ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?><script src="<?= base_url('js/seatmap.js') ?>" defer></script><?= $this->endSection() ?>
