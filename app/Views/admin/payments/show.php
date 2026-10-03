<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head"><h1>Payment #<?= (int) $payment['id'] ?></h1></div>
<dl class="facts">
    <dt>Member</dt><dd><?= esc($payment['first_name'] . ' ' . $payment['last_name']) ?> (<?= esc($payment['member_code']) ?>)</dd>
    <dt>Type</dt><dd><?= esc($payment['type']) ?></dd>
    <dt>Amount</dt><dd><?= esc($payment['amount']) ?></dd>
    <dt>Paid on</dt><dd><?= esc($payment['paid_on']) ?></dd>
    <dt>Description</dt><dd><?= esc($payment['description']) ?></dd>
    <dt>Remark</dt><dd><?= esc($payment['remark']) ?></dd>
</dl>
<a href="<?= site_url('admin/payments') ?>">Back to payments</a>
<?= $this->endSection() ?>
