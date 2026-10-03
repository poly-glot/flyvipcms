<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>
<h1>Points</h1>
<div class="stats"><div class="stat"><strong><?= esc(number_format((float) $balance, 2)) ?></strong><span>Available points</span></div></div>
<table>
    <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Balance</th></tr></thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr><td><?= esc($entry['created_at']) ?></td><td><?= esc($entry['description']) ?></td><td class="<?= (float) $entry['amount'] < 0 ? 'neg' : 'pos' ?>"><?= esc($entry['amount']) ?></td><td><?= esc($entry['balance_after']) ?></td></tr>
        <?php endforeach ?>
        <?php if ($entries === []): ?>
            <tr><td colspan="4" class="muted">No points movements yet.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
