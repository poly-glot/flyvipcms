<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>New reservation<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>Book a flight on behalf of a member.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url('admin/reservations') ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Member</h2>
            <p>The member's points are charged when you book.</p>
        </div>
        <div class="fields">
            <?= ui_select('user_id', 'Member', $members, null, ['field_class' => 'field--full', 'required' => true]) ?>
        </div>
    </section>
    <?= view('partials/booking_fields') ?>
    <div class="form-actions">
        <button class="button" type="submit">Book flight</button>
        <a class="button button--quiet" href="<?= site_url('admin/reservations') ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?><script src="<?= base_url('js/seatmap.js') ?>" defer></script><?= $this->endSection() ?>
