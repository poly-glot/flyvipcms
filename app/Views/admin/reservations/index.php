<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="head">
    <h1>Reservations</h1>
    <a class="button" href="<?= site_url('admin/reservations/new') ?>">New reservation</a>
</div>
<table>
    <thead><tr><th>#</th><th>Date</th><th>Member</th><th>Route</th><th>Aircraft</th><th>Kind</th><th>Seats</th><th>Points</th><th>Status</th></tr></thead>
    <tbody>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><a href="<?= site_url("admin/reservations/{$reservation['id']}") ?>"><?= (int) $reservation['id'] ?></a></td>
                <td><?= esc($reservation['flight_date']) ?></td>
                <td><?= esc($reservation['first_name'] . ' ' . $reservation['last_name']) ?> (<?= esc($reservation['member_code']) ?>)</td>
                <td><?= esc($reservation['from_name'] . ' → ' . $reservation['to_name']) ?></td>
                <td><?= esc($reservation['aircraft_name']) ?></td>
                <td><?= esc($reservation['kind']) ?><?= $reservation['parent_reservation_id'] !== null ? ' (joined #' . (int) $reservation['parent_reservation_id'] . ')' : '' ?></td>
                <td><?= (int) $reservation['passengers'] ?></td>
                <td><?= esc($reservation['amount']) ?></td>
                <td><span class="pill pill--<?= esc($reservation['status']) ?>"><?= esc($reservation['status']) ?></span></td>
            </tr>
        <?php endforeach ?>
        <?php if ($reservations === []): ?>
            <tr><td colspan="9" class="muted">No reservations.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
