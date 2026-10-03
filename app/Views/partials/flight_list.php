<?php
$today = $today ?? date('Y-m-d');
$upcoming = array_reverse(array_filter($reservations, static fn (array $item): bool => $item['flight_date'] >= $today && $item['status'] !== 'cancelled'));
$earlier = array_filter($reservations, static fn (array $item): bool => $item['flight_date'] < $today || $item['status'] === 'cancelled');
$groups = ['Upcoming' => $upcoming, 'Earlier' => $earlier];
?>
<?php foreach ($groups as $label => $group): ?>
    <?php if ($group !== []): ?>
        <section class="page-section">
            <div class="section-head"><h2><?= $label ?></h2><p><?= count($group) ?> <?= count($group) === 1 ? 'flight' : 'flights' ?></p></div>
            <div class="flights">
                <?php foreach ($group as $reservation): ?>
                    <?= view('partials/flight_card', ['reservation' => $reservation, 'href' => $linkPattern === '' ? '' : site_url(str_replace('{id}', (string) $reservation['id'], $linkPattern)), 'actions' => $actionsFor($reservation)]) ?>
                <?php endforeach ?>
            </div>
        </section>
    <?php endif ?>
<?php endforeach ?>
