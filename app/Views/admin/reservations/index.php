<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Reservations<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= count($reservations) ?> on record<?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button" href="<?= site_url('admin/reservations/new') ?>"><?= ui_icon('plus', 'icon icon--sm') ?>New reservation</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if ($reservations === []): ?>
    <?= view('partials/empty', ['icon' => 'ticket', 'title' => 'No reservations yet', 'text' => 'Book a flight for a member and choose their seats.', 'actionUrl' => site_url('admin/reservations/new'), 'actionLabel' => 'New reservation']) ?>
<?php else: ?>
    <?= view('partials/flight_list', [
        'reservations' => $reservations,
        'linkPattern' => 'admin/reservations/{id}',
        'actionsFor' => static fn (array $reservation): array => [],
    ]) ?>
<?php endif ?>
<?= $this->endSection() ?>
