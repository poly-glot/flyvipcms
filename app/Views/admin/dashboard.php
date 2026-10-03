<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= esc(date('l j F Y')) ?><?= $this->endSection() ?>
<?= $this->section('actions') ?>
<a class="button button--quiet" href="<?= site_url('admin/members/new') ?>"><?= ui_icon('plus', 'icon icon--sm') ?>Add member</a>
<a class="button" href="<?= site_url('admin/reservations/new') ?>"><?= ui_icon('plane', 'icon icon--sm') ?>New reservation</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$icons = ['Active members' => 'users', 'Sub-members' => 'user', 'Upcoming flights' => 'plane', 'Pending seat requests' => 'clock', 'Aircraft' => 'layers', 'Pilots' => 'award'];
?>
<dl class="stats">
    <?php foreach ($counts as $label => $count): ?>
        <div class="stats__cell">
            <dt><?= ui_icon($icons[$label] ?? 'star', 'icon icon--sm') ?><?= esc($label) ?></dt>
            <dd class="num"><?= (int) $count ?></dd>
        </div>
    <?php endforeach ?>
</dl>

<div class="split">
    <section class="page-section">
        <div class="section-head">
            <h2>Upcoming flights</h2>
            <a href="<?= site_url('admin/reservations') ?>">All reservations</a>
        </div>
        <?php if ($upcoming === []): ?>
            <?= view('partials/empty', ['icon' => 'plane', 'title' => 'No flights on the calendar', 'text' => 'Reservations for today and later will appear here.', 'actionUrl' => site_url('admin/reservations/new'), 'actionLabel' => 'New reservation']) ?>
        <?php else: ?>
            <div class="flights">
                <?php foreach ($upcoming as $reservation): ?>
                    <?= view('partials/flight_card', ['reservation' => $reservation, 'href' => site_url("admin/reservations/{$reservation['id']}"), 'actions' => []]) ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </section>

    <section class="page-section">
        <div class="section-head">
            <h2>Overdue payments</h2>
            <p><?= count($overdue) ?> <?= count($overdue) === 1 ? 'member' : 'members' ?></p>
        </div>
        <?php if ($overdue === []): ?>
            <?= view('partials/empty', ['icon' => 'check', 'title' => 'Nobody is overdue', 'text' => 'Every active member is paid up.']) ?>
        <?php else: ?>
            <ul class="rows">
                <?php foreach ($overdue as $member): ?>
                    <?php $days = intdiv(strtotime($today) - strtotime($member['next_due_on']), 86400); ?>
                    <li>
                        <a class="row row--person" href="<?= site_url('admin/payments/new?user_id=' . (int) $member['user_id']) ?>">
                            <?= ui_avatar($member['first_name'], $member['last_name']) ?>
                            <span>
                                <span class="row__title"><?= esc($member['first_name'] . ' ' . $member['last_name']) ?></span>
                                <span class="row__sub"><?= esc($member['member_code']) ?>, due <?= esc(ui_date($member['next_due_on'])) ?></span>
                            </span>
                            <span class="pill pill--overdue pill--plain"><?= esc($days) ?> <?= $days === 1 ? 'day' : 'days' ?></span>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </section>
</div>
<?= $this->endSection() ?>
