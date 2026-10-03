<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head"><h1>Add points</h1></div>
<form class="grid" method="post" action="<?= site_url('admin/points/adjust') ?>">
    <?= csrf_field() ?>
    <label>Member
        <select name="user_id" required>
            <?php foreach ($members as $id => $label): ?>
                <option value="<?= $id ?>" <?= (string) old('user_id') === (string) $id ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <label>Points<input type="number" step="0.01" min="0.01" name="amount" value="<?= esc(old('amount')) ?>" required></label>
    <label>Remark<input type="text" name="remark" value="<?= esc(old('remark')) ?>" required></label>
    <div class="full"><button type="submit">Add points</button> <a href="<?= site_url('admin/points') ?>">Cancel</a></div>
</form>
<?= $this->endSection() ?>
