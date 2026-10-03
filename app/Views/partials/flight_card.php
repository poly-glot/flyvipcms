<?php
$timestamp = strtotime($reservation['flight_date']);
$capacity = (int) ($reservation['capacity'] ?? 0);
$seatsHeld = $reservation['kind'] === 'complete' ? $capacity : (int) $reservation['passengers'];
$actions = $actions ?? [];
$joined = $reservation['parent_reservation_id'] !== null;
$tag = $href === '' ? 'span' : 'a';
?>
<article class="flight<?= $reservation['status'] === 'cancelled' ? ' flight--cancelled' : '' ?>">
    <time class="flight__date" datetime="<?= esc($reservation['flight_date'], 'attr') ?>">
        <span class="flight__day"><?= date('j', $timestamp) ?></span>
        <span class="flight__month"><?= date('M', $timestamp) ?></span>
    </time>
    <div class="flight__main">
        <h3 class="flight__route">
            <<?= $tag ?> class="flight__link"<?= $href === '' ? '' : ' href="' . esc($href, 'attr') . '"' ?>>
                <span class="flight__place"><?= esc($reservation['from_name']) ?></span>
                <span class="flight__path" aria-hidden="true"><?= ui_icon('plane', 'icon icon--sm') ?></span>
                <span class="sr-only">to</span>
                <span class="flight__place"><?= esc($reservation['to_name']) ?></span>
            </<?= $tag ?>>
        </h3>
        <p class="flight__meta">
            <span><?= ui_icon('plane', 'icon icon--sm') ?><?= esc($reservation['aircraft_name']) ?></span>
            <span><?= $reservation['kind'] === 'complete' ? 'Whole aircraft' : 'Shared flight' ?><?= $joined ? ' (joined #' . (int) $reservation['parent_reservation_id'] . ')' : '' ?></span>
            <?php if (isset($reservation['first_name'])): ?>
                <span><?= ui_icon('user', 'icon icon--sm') ?><?= esc($reservation['first_name'] . ' ' . $reservation['last_name']) ?></span>
            <?php endif ?>
        </p>
    </div>
    <div class="flight__side">
        <span class="pill pill--<?= esc($reservation['status'], 'attr') ?>"><?= esc($reservation['status']) ?></span>
        <?php if ($capacity > 0): ?>
            <?= ui_pips($seatsHeld, $capacity) ?>
        <?php endif ?>
        <span class="flight__cost num"><?= esc(ui_points($reservation['amount'])) ?> pts</span>
    </div>
    <?php if ($actions !== []): ?>
        <div class="flight__actions">
            <?php foreach ($actions as $action): ?>
                <form method="post" action="<?= esc($action['url'], 'attr') ?>"<?= isset($action['confirm']) ? ' data-confirm="' . esc($action['confirm'], 'attr') . '" data-confirm-title="' . esc($action['title'], 'attr') . '" data-confirm-action="' . esc($action['label'], 'attr') . '"' : '' ?>>
                    <?= csrf_field() ?>
                    <button class="button button--small <?= isset($action['confirm']) ? 'button--danger' : '' ?>" type="submit"><?= esc($action['label']) ?></button>
                </form>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</article>
