<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head"><h1>Record payment</h1></div>

<form class="inline" method="get" action="<?= site_url('admin/payments/new') ?>">
    <label>Member
        <select name="user_id" onchange="this.form.submit()">
            <option value="">Select a member…</option>
            <?php foreach ($members as $id => $label): ?>
                <option value="<?= $id ?>" <?= $selected === (int) $id ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <noscript><button type="submit">Load</button></noscript>
</form>

<?php if ($selected > 0): ?>
    <form class="grid" method="post" action="<?= site_url('admin/payments') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= $selected ?>">
        <label>Payment type
            <select name="type" required>
                <?php foreach ($allowed as $type): ?>
                    <option value="<?= $type ?>" <?= old('type') === $type ? 'selected' : '' ?>>
                        <?= esc(ucfirst($type)) ?><?= isset($expected[$type]) ? ' — ' . esc($expected[$type]) . ($type === 'quarterly' ? ' per quarter' : '') : '' ?>
                    </option>
                <?php endforeach ?>
            </select>
        </label>
        <label>Amount (bonus / points only)<input type="number" step="0.01" min="0.01" name="amount" value="<?= esc(old('amount')) ?>"></label>
        <label>Quarters (quarterly only)
            <select name="quarters">
                <?php foreach ([1, 2, 3, 4] as $n): ?>
                    <option value="<?= $n ?>"><?= $n ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <label>Term start (yearly only)
            <select name="term_start">
                <?php foreach ($termStarts as $start): ?>
                    <option value="<?= esc($start) ?>"><?= esc($start) ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <label>Paid on<input type="date" name="paid_on" value="<?= esc(old('paid_on', date('Y-m-d'))) ?>" required></label>
        <label>Remark (mode of payment)<input type="text" name="remark" value="<?= esc(old('remark')) ?>" required></label>
        <div class="full">
            <button type="submit">Record payment</button>
            <a href="<?= site_url('admin/payments') ?>">Cancel</a>
        </div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
