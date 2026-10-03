<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1>Points history</h1>
    <div>
        <a class="button" href="<?= site_url('admin/points/adjust') ?>">Add points</a>
        <a class="button" href="<?= site_url('admin/points/transfer') ?>">Transfer</a>
    </div>
</div>
<table>
    <thead><tr><th>Date</th><th>Member</th><th>Code</th><th>Description</th><th>Amount</th><th>Balance</th></tr></thead>
    <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= esc($entry['created_at']) ?></td>
                <td><?= esc($entry['first_name'] . ' ' . $entry['last_name']) ?></td>
                <td><?= esc($entry['member_code']) ?></td>
                <td><?= esc($entry['description']) ?></td>
                <td class="<?= (float) $entry['amount'] < 0 ? 'neg' : 'pos' ?>"><?= esc($entry['amount']) ?></td>
                <td><?= esc($entry['balance_after']) ?></td>
            </tr>
        <?php endforeach ?>
        <?php if ($entries === []): ?>
            <tr><td colspan="6" class="muted">No points movements yet.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
