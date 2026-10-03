<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1>Reservation #<?= (int) $reservation['id'] ?></h1>
    <div>
        <?php if ($reservation['status'] === 'pending'): ?>
            <form class="inline" method="post" action="<?= site_url("admin/reservations/{$reservation['id']}/confirm") ?>"><?= csrf_field() ?><button type="submit">Confirm</button></form>
        <?php endif ?>
        <?php if ($reservation['status'] !== 'cancelled'): ?>
            <form class="inline" method="post" action="<?= site_url("admin/reservations/{$reservation['id']}/cancel") ?>" onsubmit="return confirm('Cancel and refund?')"><?= csrf_field() ?><button type="submit" class="danger">Cancel</button></form>
        <?php endif ?>
    </div>
</div>
<dl class="facts">
    <dt>Date</dt><dd><?= esc($reservation['flight_date']) ?></dd>
    <dt>Kind</dt><dd><?= esc($reservation['kind']) ?></dd>
    <dt>Status</dt><dd><span class="pill pill--<?= esc($reservation['status']) ?>"><?= esc($reservation['status']) ?></span></dd>
    <dt>Passengers</dt><dd><?= (int) $reservation['passengers'] ?></dd>
    <dt>Seats</dt><dd><?= esc(implode(', ', $seats) ?: '—') ?></dd>
    <dt>Points charged</dt><dd><?= esc($reservation['amount']) ?></dd>
</dl>
<?php if ($joiners !== []): ?>
    <h2>Seat requests</h2>
    <table>
        <thead><tr><th>#</th><th>Member</th><th>Seats</th><th>Points</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($joiners as $joiner): ?>
                <tr><td><a href="<?= site_url("admin/reservations/{$joiner['id']}") ?>"><?= (int) $joiner['id'] ?></a></td><td><?= esc($joiner['first_name'] . ' ' . $joiner['last_name']) ?></td><td><?= (int) $joiner['passengers'] ?></td><td><?= esc($joiner['amount']) ?></td><td><?= esc($joiner['status']) ?></td></tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>
<h2>Activity</h2>
<ul class="feed">
    <?php foreach ($activity as $item): ?>
        <li><time><?= esc($item['created_at']) ?></time> <?= esc($item['description']) ?></li>
    <?php endforeach ?>
</ul>
<?= $this->endSection() ?>
