<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1>Members</h1>
    <a class="button" href="<?= site_url('admin/members/new') ?>">Add member</a>
</div>
<table>
    <thead><tr><th>Code</th><th>Name</th><th>Email</th><th>Plan</th><th>Yearly fee</th><th>Points</th><th>Status</th><th>Next due</th><th></th></tr></thead>
    <tbody>
        <?php foreach ($members as $member): ?>
            <tr>
                <td><?= esc($member['member_code']) ?></td>
                <td><?= esc($member['first_name'] . ' ' . $member['last_name']) ?></td>
                <td><?= esc($member['email']) ?></td>
                <td><?= esc($member['plan_name']) ?></td>
                <td><?= esc(number_format((float) $member['yearly_fee'], 2)) ?></td>
                <td><?= esc(number_format((float) ($balances[$member['user_id']] ?? 0), 2)) ?></td>
                <td><span class="pill pill--<?= esc($member['status']) ?>"><?= esc($member['status']) ?></span></td>
                <td><?= esc($member['next_due_on'] ?? '—') ?></td>
                <td class="actions"><a href="<?= site_url("admin/members/{$member['user_id']}") ?>">View</a></td>
            </tr>
        <?php endforeach ?>
        <?php if ($members === []): ?>
            <tr><td colspan="9" class="muted">No members yet.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
