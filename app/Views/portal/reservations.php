<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1>Reservations</h1>
    <?php if ($canBook): ?>
        <a class="button" href="<?= site_url('portal/reservations/new') ?>">New reservation</a>
    <?php endif ?>
</div>

<?php if ($requests !== []): ?>
    <h2>Seat requests on your flights</h2>
    <table>
        <thead><tr><th>Member</th><th>Date</th><th>Seats</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= esc($request['first_name'] . ' ' . $request['last_name']) ?></td>
                    <td><?= esc($request['flight_date']) ?></td>
                    <td><?= (int) $request['passengers'] ?></td>
                    <td class="actions">
                        <?php if ($canBook): ?>
                            <form method="post" action="<?= site_url("portal/reservations/{$request['id']}/confirm") ?>"><?= csrf_field() ?><button type="submit">Confirm</button></form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<table>
    <thead><tr><th>Date</th><th>Route</th><th>Aircraft</th><th>Kind</th><th>Seats</th><th>Points</th><th>Status</th><th></th></tr></thead>
    <tbody>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?= esc($reservation['flight_date']) ?></td>
                <td><?= esc($reservation['from_name'] . ' → ' . $reservation['to_name']) ?></td>
                <td><?= esc($reservation['aircraft_name']) ?></td>
                <td><?= esc($reservation['kind']) ?></td>
                <td><?= (int) $reservation['passengers'] ?></td>
                <td><?= esc($reservation['amount']) ?></td>
                <td><span class="pill pill--<?= esc($reservation['status']) ?>"><?= esc($reservation['status']) ?></span></td>
                <td class="actions">
                    <?php if ($canBook && $reservation['status'] !== 'cancelled' && $reservation['flight_date'] >= $today): ?>
                        <form method="post" action="<?= site_url("portal/reservations/{$reservation['id']}/cancel") ?>" onsubmit="return confirm('Cancel this reservation?')"><?= csrf_field() ?><button type="submit" class="link danger">Cancel</button></form>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if ($reservations === []): ?>
            <tr><td colspan="8" class="muted">No reservations yet.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
