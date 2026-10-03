<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Welcome, <?= esc($member['first_name']) ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc($member['member_code']) ?>, <?= esc($member['plan_name'] ?? 'Family contact') ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<?php if ($primary): ?>
    <a class="button" href="<?= site_url('portal/reservations/new') ?>"><?= ui_icon('plane', 'icon icon--sm') ?>Book a flight</a>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="balance" aria-label="Points balance">
    <div class="balance__figure">
        <span class="balance__label">Available points</span>
        <strong class="balance__value num"><?= esc(ui_points($balance)) ?></strong>
        <span class="balance__meta">
            <span class="pill pill--<?= esc($member['status'], 'attr') ?>"><?= esc($member['status']) ?></span>
            <?php if ($primary): ?>
                <span>Next payment due <?= esc(ui_date($member['next_due_on'])) ?></span>
            <?php endif ?>
        </span>
    </div>
    <div class="balance__trend"><?= ui_sparkline($series, 220, 64) ?></div>
</section>

<?php if (!$primary): ?>
    <p class="notice"><?= ui_icon('info', 'icon icon--sm') ?>You have view-only access to the points and flights of your primary member.</p>
<?php endif ?>

<div class="split">
    <section class="page-section">
        <div class="section-head">
            <h2>Next flights</h2>
            <a href="<?= site_url('portal/reservations') ?>">All reservations</a>
        </div>
        <?php if ($upcoming === []): ?>
            <?= view('partials/empty', array_merge(['icon' => 'plane', 'title' => 'No flights booked', 'text' => 'Your upcoming flights will appear here.'], $primary ? ['actionUrl' => site_url('portal/reservations/new'), 'actionLabel' => 'Book a flight'] : [])) ?>
        <?php else: ?>
            <div class="flights">
                <?php foreach ($upcoming as $reservation): ?>
                    <?= view('partials/flight_card', ['reservation' => $reservation, 'href' => '', 'actions' => []]) ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </section>

    <div class="stack">
        <?php if ($family !== []): ?>
            <section class="page-section">
                <div class="section-head"><h2>Family and contacts</h2></div>
                <ul class="rows">
                    <?php foreach ($family as $person): ?>
                        <li class="row row--person">
                            <?= ui_avatar($person['first_name'], $person['last_name']) ?>
                            <span>
                                <span class="row__title"><?= esc($person['first_name'] . ' ' . $person['last_name']) ?></span>
                                <span class="row__sub"><?= esc($person['member_code']) ?></span>
                            </span>
                            <span class="pill pill--accent pill--plain"><?= esc($person['contact_type']) ?></span>
                        </li>
                    <?php endforeach ?>
                </ul>
            </section>
        <?php endif ?>

        <section class="page-section">
            <div class="section-head"><h2>Recent activity</h2></div>
            <?php if ($activity === []): ?>
                <?= view('partials/empty', ['icon' => 'clock', 'title' => 'No activity yet', 'text' => 'Bookings and confirmations will be listed here.']) ?>
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
</div>
<?= $this->endSection() ?>
