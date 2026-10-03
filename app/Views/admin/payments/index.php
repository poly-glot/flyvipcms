<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1>Payments</h1>
    <a class="button" href="<?= site_url('admin/payments/new') ?>">Record payment</a>
</div>
<form class="inline" method="get" action="<?= site_url('admin/payments') ?>">
    <label>Search<input type="search" name="q" value="<?= esc($search) ?>" placeholder="member, code, remark, id"></label>
    <button type="submit">Search</button>
</form>
<table>
    <thead><tr><th>#</th><th>Member</th><th>Code</th><th>Date</th><th>Type</th><th>Amount</th><th>Remark</th></tr></thead>
    <tbody>
        <?php foreach ($payments as $payment): ?>
            <tr>
                <td><a href="<?= site_url("admin/payments/{$payment['id']}") ?>"><?= (int) $payment['id'] ?></a></td>
                <td><?= esc($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                <td><?= esc($payment['member_code']) ?></td>
                <td><?= esc($payment['paid_on']) ?></td>
                <td><?= esc($payment['type']) ?></td>
                <td><?= esc($payment['amount']) ?></td>
                <td><?= esc($payment['remark']) ?></td>
            </tr>
        <?php endforeach ?>
        <?php if ($payments === []): ?>
            <tr><td colspan="7" class="muted">No payments.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
