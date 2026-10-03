<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>
<h1>Welcome, <?= esc($member['first_name']) ?></h1>
<div class="stats">
    <div class="stat"><strong><?= esc(number_format((float) $balance, 2)) ?></strong><span>Available points</span></div>
    <div class="stat"><strong><?= esc($member['member_code']) ?></strong><span>Member ID</span></div>
    <div class="stat"><strong><?= esc($member['plan_name'] ?? 'Family contact') ?></strong><span>Membership</span></div>
    <div class="stat"><strong><span class="pill pill--<?= esc($member['status']) ?>"><?= esc($member['status']) ?></span></strong><span>Status</span></div>
    <?php if ($primary): ?>
        <div class="stat"><strong><?= esc($member['next_due_on'] ?? '—') ?></strong><span>Next payment due</span></div>
    <?php endif ?>
</div>
<?php if (!$primary): ?>
    <p class="muted">You have view-only access to the points and flights of your primary member.</p>
<?php endif ?>
<?php if ($family !== []): ?>
    <h2>Family &amp; contacts</h2>
    <table>
        <thead><tr><th>Code</th><th>Name</th><th>Relationship</th></tr></thead>
        <tbody>
            <?php foreach ($family as $person): ?>
                <tr><td><?= esc($person['member_code']) ?></td><td><?= esc($person['first_name'] . ' ' . $person['last_name']) ?></td><td><?= esc($person['contact_type']) ?></td></tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>
<h2>Recent activity</h2>
<ul class="feed">
    <?php foreach ($activity as $item): ?>
        <li><time><?= esc($item['created_at']) ?></time> <?= esc($item['description']) ?></li>
    <?php endforeach ?>
    <?php if ($activity === []): ?>
        <li class="muted">No activity yet.</li>
    <?php endif ?>
</ul>
<?= $this->endSection() ?>
