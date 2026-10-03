<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Points history<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>The 300 most recent movements across all members<?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button button--quiet" href="<?= site_url('admin/points/transfer') ?>"><?= ui_icon('swap', 'icon icon--sm') ?>Transfer</a>
<a class="button" href="<?= site_url('admin/points/adjust') ?>"><?= ui_icon('plus', 'icon icon--sm') ?>Add points</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if ($entries === []): ?>
    <?= view('partials/empty', ['icon' => 'star', 'title' => 'No points movements yet', 'text' => 'Payments, bookings, refunds and transfers will be listed here.', 'actionUrl' => site_url('admin/points/adjust'), 'actionLabel' => 'Add points']) ?>
<?php else: ?>
    <?= view('partials/ledger', ['entries' => $entries]) ?>
<?php endif ?>
<?= $this->endSection() ?>
