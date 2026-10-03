<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head"><h1>New reservation</h1></div>
<form class="grid" method="post" action="<?= site_url('admin/reservations') ?>">
    <?= csrf_field() ?>
    <label>Member
        <select name="user_id" required>
            <?php foreach ($members as $id => $label): ?>
                <option value="<?= $id ?>" <?= (string) old('user_id') === (string) $id ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <?= $this->include('partials/booking_fields') ?>
    <div class="full"><button type="submit">Book</button> <a href="<?= site_url('admin/reservations') ?>">Cancel</a></div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?><script src="<?= base_url('js/booking.js') ?>"></script><?= $this->endSection() ?>
