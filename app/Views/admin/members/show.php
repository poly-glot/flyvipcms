<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1><?= esc($member['first_name'] . ' ' . $member['last_name']) ?> <small><?= esc($member['member_code']) ?></small></h1>
    <div>
        <a class="button" href="<?= site_url("admin/members/{$member['user_id']}/edit") ?>">Edit</a>
        <?php if ($member['plan_id'] !== null): ?>
            <a class="button" href="<?= site_url('admin/payments/new?user_id=' . $member['user_id']) ?>">Record payment</a>
        <?php endif ?>
    </div>
</div>

<div class="stats">
    <div class="stat"><strong><?= esc(number_format((float) $balance, 2)) ?></strong><span>Points</span></div>
    <div class="stat"><strong><span class="pill pill--<?= esc($member['status']) ?>"><?= esc($member['status']) ?></span></strong><span>Status</span></div>
    <div class="stat"><strong><?= esc($member['next_due_on'] ?? '—') ?></strong><span>Next payment due</span></div>
    <div class="stat"><strong><?= esc($member['plan_name'] ?? 'Sub-member') ?></strong><span>Plan</span></div>
</div>

<dl class="facts">
    <dt>Username</dt><dd><?= esc($member['username']) ?></dd>
    <dt>Email</dt><dd><?= esc($member['email']) ?></dd>
    <dt>Phone</dt><dd><?= esc($member['cellphone'] ?? '—') ?></dd>
    <dt>Company</dt><dd><?= esc($member['company'] ?? '—') ?></dd>
    <dt>Passport</dt><dd><?= esc($member['passport_number'] ?? '—') ?> (due <?= esc($member['passport_due_on'] ?? '—') ?>)</dd>
</dl>

<form class="inline" method="post" action="<?= site_url("admin/members/{$member['user_id']}/status") ?>">
    <?= csrf_field() ?>
    <label>Membership status
        <select name="status">
            <?php foreach (['inactive', 'active', 'banned'] as $status): ?>
                <option value="<?= $status ?>" <?= $member['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <button type="submit">Update</button>
</form>

<?php if ($member['plan_id'] !== null): ?>
    <h2>Sub-members</h2>
    <table>
        <thead><tr><th>Code</th><th>Name</th><th>Username</th><th>Relationship</th></tr></thead>
        <tbody>
            <?php foreach ($children as $child): ?>
                <tr><td><?= esc($child['member_code']) ?></td><td><?= esc($child['first_name'] . ' ' . $child['last_name']) ?></td><td><?= esc($child['username']) ?></td><td><?= esc($child['contact_type']) ?></td></tr>
            <?php endforeach ?>
            <?php if ($children === []): ?>
                <tr><td colspan="4" class="muted">No sub-members.</td></tr>
            <?php endif ?>
        </tbody>
    </table>
    <details>
        <summary>Add sub-member</summary>
        <form class="grid" method="post" action="<?= site_url("admin/members/{$member['user_id']}/sub-members") ?>">
            <?= csrf_field() ?>
            <label>First name<input type="text" name="first_name" required></label>
            <label>Last name<input type="text" name="last_name" required></label>
            <label>Username<input type="text" name="username" required></label>
            <label>Email<input type="email" name="email" required></label>
            <label>Password<input type="password" name="password" autocomplete="new-password" required></label>
            <label>Relationship
                <select name="contact_type_id">
                    <?php foreach ($contactTypes as $id => $name): ?>
                        <option value="<?= $id ?>"><?= esc($name) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
            <div class="full"><button type="submit">Add sub-member</button></div>
        </form>
    </details>

    <h2>Payments</h2>
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Amount</th><th>Remark</th></tr></thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr><td><a href="<?= site_url("admin/payments/{$payment['id']}") ?>"><?= (int) $payment['id'] ?></a></td><td><?= esc($payment['paid_on']) ?></td><td><?= esc($payment['type']) ?></td><td><?= esc($payment['amount']) ?></td><td><?= esc($payment['remark']) ?></td></tr>
            <?php endforeach ?>
            <?php if ($payments === []): ?>
                <tr><td colspan="5" class="muted">No payments yet.</td></tr>
            <?php endif ?>
        </tbody>
    </table>

    <h2>Points history</h2>
    <table>
        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Balance</th></tr></thead>
        <tbody>
            <?php foreach ($ledger as $entry): ?>
                <tr><td><?= esc($entry['created_at']) ?></td><td><?= esc($entry['description']) ?></td><td class="<?= (float) $entry['amount'] < 0 ? 'neg' : 'pos' ?>"><?= esc($entry['amount']) ?></td><td><?= esc($entry['balance_after']) ?></td></tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <h2>Reservations</h2>
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Kind</th><th>Status</th><th>Points</th></tr></thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr><td><a href="<?= site_url("admin/reservations/{$reservation['id']}") ?>"><?= (int) $reservation['id'] ?></a></td><td><?= esc($reservation['flight_date']) ?></td><td><?= esc($reservation['kind']) ?></td><td><?= esc($reservation['status']) ?></td><td><?= esc($reservation['amount']) ?></td></tr>
            <?php endforeach ?>
            <?php if ($reservations === []): ?>
                <tr><td colspan="5" class="muted">No reservations.</td></tr>
            <?php endif ?>
        </tbody>
    </table>
<?php endif ?>

<form method="post" action="<?= site_url("admin/members/{$member['user_id']}/delete") ?>" onsubmit="return confirm('Remove this member and their sub-members?')">
    <?= csrf_field() ?>
    <button type="submit" class="danger">Remove member</button>
</form>
<?= $this->endSection() ?>
