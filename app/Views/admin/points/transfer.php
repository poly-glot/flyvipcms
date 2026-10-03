<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head"><h1>Transfer points</h1></div>
<form class="grid" method="post" action="<?= site_url('admin/points/transfer') ?>">
    <?= csrf_field() ?>
    <?php foreach (['sender_id' => 'From member', 'receiver_id' => 'To member'] as $name => $label): ?>
        <label><?= $label ?>
            <select name="<?= $name ?>" required>
                <?php foreach ($members as $id => $text): ?>
                    <option value="<?= $id ?>" <?= (string) old($name) === (string) $id ? 'selected' : '' ?>><?= esc($text) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    <?php endforeach ?>
    <label>Points<input type="number" step="0.01" min="0.01" name="amount" value="<?= esc(old('amount')) ?>" required></label>
    <div class="full"><button type="submit">Transfer</button> <a href="<?= site_url('admin/points') ?>">Cancel</a></div>
</form>
<?= $this->endSection() ?>
