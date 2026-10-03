<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1>Dashboard</h1>
<div class="stats">
    <?php foreach ($counts as $label => $count): ?>
        <div class="stat"><strong><?= (int) $count ?></strong><span><?= esc($label) ?></span></div>
    <?php endforeach ?>
</div>
<h2>Overdue payments</h2>
<table>
    <thead><tr><th>Member</th><th>Name</th><th>Due since</th></tr></thead>
    <tbody>
        <?php foreach ($overdue as $row): ?>
            <tr><td><?= esc($row['member_code']) ?></td><td><?= esc($row['first_name'] . ' ' . $row['last_name']) ?></td><td><?= esc($row['next_due_on']) ?></td></tr>
        <?php endforeach ?>
        <?php if ($overdue === []): ?>
            <tr><td colspan="3" class="muted">Nobody is overdue.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
