<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Payments<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= $search !== '' ? count($payments) . ' matching "' . esc($search) . '"' : 'Latest payments across the club' ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button" href="<?= site_url('admin/payments/new') ?>"><?= ui_icon('plus', 'icon icon--sm') ?>Record payment</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="toolbar" method="get" action="<?= site_url('admin/payments') ?>" role="search">
    <div class="search-form">
        <label class="search">
            <span class="sr-only">Search payments</span>
            <?= ui_icon('search', 'icon icon--sm') ?>
            <input type="search" name="q" value="<?= esc($search, 'attr') ?>" placeholder="Member, code, remark or id">
        </label>
        <button class="button button--quiet" type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <a class="button button--quiet" href="<?= site_url('admin/payments') ?>">Clear</a>
        <?php endif ?>
    </div>
</form>

<?php if ($payments === []): ?>
    <?= view('partials/empty', ['icon' => 'card', 'title' => $search !== '' ? 'No payments match' : 'No payments yet', 'text' => $search !== '' ? 'Check the spelling or search by member code.' : 'Recorded payments appear here with the points they credit.', 'actionUrl' => site_url('admin/payments/new'), 'actionLabel' => 'Record payment']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>Payment</th><th>Member</th><th class="hide-narrow">Code</th><th>Date</th><th>Type</th><th class="hide-narrow">Remark</th><th class="num">Amount</th></tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><a href="<?= site_url("admin/payments/{$payment['id']}") ?>">#<?= (int) $payment['id'] ?></a></td>
                        <td>
                            <span class="cell-person">
                                <?= ui_avatar($payment['first_name'], $payment['last_name'], 'avatar--sm') ?>
                                <?= esc($payment['first_name'] . ' ' . $payment['last_name']) ?>
                            </span>
                        </td>
                        <td class="hide-narrow muted"><?= esc($payment['member_code']) ?></td>
                        <td class="nowrap"><?= esc(ui_date($payment['paid_on'])) ?></td>
                        <td><span class="pill pill--accent pill--plain"><?= esc($payment['type']) ?></span></td>
                        <td class="hide-narrow muted"><?= esc($payment['remark']) ?></td>
                        <td class="num"><?= esc(ui_points($payment['amount'])) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
