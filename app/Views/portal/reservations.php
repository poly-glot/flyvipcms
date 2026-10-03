<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Reservations<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= count($reservations) ?> on your account<?= $this->endSection() ?>
<?= $this->section('actions') ?>
<?php if ($canBook): ?>
    <a class="button" href="<?= site_url('portal/reservations/new') ?>"><?= ui_icon('plus', 'icon icon--sm') ?>Book a flight</a>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if ($requests !== []): ?>
    <section class="page-section">
        <div class="section-head"><h2>Seat requests on your flights</h2><p>Confirm to share your flight.</p></div>
        <ul class="rows">
            <?php foreach ($requests as $request): ?>
                <li class="row row--request">
                    <?= ui_avatar($request['first_name'], $request['last_name']) ?>
                    <span>
                        <span class="row__title"><?= esc($request['first_name'] . ' ' . $request['last_name']) ?></span>
                        <span class="row__sub"><?= (int) $request['passengers'] ?> <?= (int) $request['passengers'] === 1 ? 'seat' : 'seats' ?> on <?= esc(ui_date($request['flight_date'])) ?></span>
                    </span>
                    <?php if ($canBook): ?>
                        <form method="post" action="<?= site_url("portal/reservations/{$request['id']}/confirm") ?>">
                            <?= csrf_field() ?>
                            <button class="button button--small" type="submit"><?= ui_icon('check', 'icon icon--sm') ?>Confirm</button>
                        </form>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
    </section>
<?php endif ?>

<?php if ($reservations === []): ?>
    <?= view('partials/empty', array_merge(['icon' => 'ticket', 'title' => 'No reservations yet', 'text' => 'When you book a flight it will appear here with its seats.'], $canBook ? ['actionUrl' => site_url('portal/reservations/new'), 'actionLabel' => 'Book a flight'] : [])) ?>
<?php else: ?>
    <?= view('partials/flight_list', [
        'reservations' => $reservations,
        'today' => $today,
        'linkPattern' => '',
        'actionsFor' => static fn (array $reservation): array => $canBook && $reservation['status'] !== 'cancelled' && $reservation['flight_date'] >= $today
            ? [['label' => 'Cancel reservation', 'url' => site_url("portal/reservations/{$reservation['id']}/cancel"), 'title' => 'Cancel this reservation?', 'confirm' => 'Your points are refunded and the seats are released.']]
            : [],
    ]) ?>
<?php endif ?>
<?= $this->endSection() ?>
