<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Payment #<?= (int) $payment['id'] ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc(ui_date($payment['paid_on'])) ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button button--quiet" href="<?= site_url('admin/payments') ?>">Back to payments</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="page-section receipt">
    <div class="receipt__amount">
        <span class="muted">Amount</span>
        <strong class="num"><?= esc(ui_points($payment['amount'])) ?></strong>
        <span class="pill pill--accent pill--plain"><?= esc($payment['type']) ?></span>
    </div>
    <dl class="facts">
        <div><dt>Member</dt><dd><a href="<?= site_url('admin/members/' . (int) $payment['user_id']) ?>"><?= esc($payment['first_name'] . ' ' . $payment['last_name']) ?></a> <span class="muted">(<?= esc($payment['member_code']) ?>)</span></dd></div>
        <div><dt>Paid on</dt><dd><?= esc(ui_date($payment['paid_on'])) ?></dd></div>
        <div><dt>Description</dt><dd><?= esc($payment['description']) ?></dd></div>
        <div><dt>Remark</dt><dd><?= esc($payment['remark']) ?></dd></div>
    </dl>
</section>
<?= $this->endSection() ?>
