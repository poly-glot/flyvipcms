<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Reservation #<?= (int) $reservation['id'] ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc($reservation['from_name'] . ' to ' . $reservation['to_name']) ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<?php if ($reservation['status'] === 'pending'): ?>
    <form method="post" action="<?= site_url("admin/reservations/{$reservation['id']}/confirm") ?>">
        <?= csrf_field() ?>
        <button class="button" type="submit"><?= ui_icon('check', 'icon icon--sm') ?>Confirm</button>
    </form>
<?php endif ?>
<?php if ($reservation['status'] !== 'cancelled'): ?>
    <form method="post" action="<?= site_url("admin/reservations/{$reservation['id']}/cancel") ?>" data-confirm="The member is refunded and the seats are released." data-confirm-title="Cancel this reservation?" data-confirm-action="Cancel reservation">
        <?= csrf_field() ?>
        <button class="button button--danger" type="submit">Cancel reservation</button>
    </form>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('partials/flight_card', ['reservation' => $reservation, 'href' => site_url("admin/reservations/{$reservation['id']}"), 'actions' => []]) ?>

<div class="detail">
    <div class="detail__main">
        <?php if ($joiners !== []): ?>
            <section class="page-section">
                <div class="section-head"><h2>Seat requests</h2></div>
                <ul class="rows">
                    <?php foreach ($joiners as $joiner): ?>
                        <li>
                            <a class="row row--person" href="<?= site_url("admin/reservations/{$joiner['id']}") ?>">
                                <?= ui_avatar($joiner['first_name'], $joiner['last_name']) ?>
                                <span>
                                    <span class="row__title"><?= esc($joiner['first_name'] . ' ' . $joiner['last_name']) ?></span>
                                    <span class="row__sub"><?= (int) $joiner['passengers'] ?> <?= (int) $joiner['passengers'] === 1 ? 'seat' : 'seats' ?>, <?= esc(ui_points($joiner['amount'])) ?> pts</span>
                                </span>
                                <span class="pill pill--<?= esc($joiner['status'], 'attr') ?>"><?= esc($joiner['status']) ?></span>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </section>
        <?php endif ?>

        <section class="page-section">
            <div class="section-head"><h2>Activity</h2></div>
            <?php if ($activity === []): ?>
                <?= view('partials/empty', ['icon' => 'clock', 'title' => 'No activity yet', 'text' => 'Bookings, confirmations and refunds are logged here.']) ?>
            <?php else: ?>
                <ol class="timeline">
                    <?php foreach ($activity as $item): ?>
                        <li>
                            <span><?= esc($item['description']) ?></span>
                            <time class="muted" datetime="<?= esc($item['created_at'], 'attr') ?>"><?= esc(ui_date($item['created_at'], 'j M Y, H:i')) ?></time>
                        </li>
                    <?php endforeach ?>
                </ol>
            <?php endif ?>
        </section>
    </div>

    <aside class="detail__aside">
        <section class="page-section">
            <h2>Seats</h2>
            <div class="seatmap" data-seatmap data-capacity="<?= (int) $reservation['capacity'] ?>" data-selected="<?= esc(implode(',', $reservation['kind'] === 'complete' ? range(1, (int) $reservation['capacity']) : $seats), 'attr') ?>">
                <div class="cabin" data-seatmap-grid role="group" aria-label="Seat map"></div>
                <p class="seatmap__status">Seats held: <?= esc(implode(', ', $seats) ?: 'none') ?></p>
            </div>
        </section>
        <section class="page-section">
            <h2>Booked by</h2>
            <dl class="facts">
                <div><dt>Member</dt><dd><a href="<?= site_url('admin/members/' . (int) $reservation['user_id']) ?>"><?= esc($reservation['first_name'] . ' ' . $reservation['last_name']) ?></a></dd></div>
                <div><dt>Code</dt><dd><?= esc($reservation['member_code']) ?></dd></div>
                <div><dt>Passengers</dt><dd><?= (int) $reservation['passengers'] ?></dd></div>
                <div><dt>Charged</dt><dd class="num"><?= esc(ui_points($reservation['amount'])) ?> pts</dd></div>
            </dl>
        </section>
    </aside>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?><script src="<?= base_url('js/seatmap.js') ?>" defer></script><?= $this->endSection() ?>
