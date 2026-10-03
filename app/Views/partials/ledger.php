<?php
$byDay = [];

foreach ($entries as $entry) {
    $byDay[substr($entry['created_at'], 0, 10)][] = $entry;
}
?>
<div class="ledger">
    <?php foreach ($byDay as $day => $dayEntries): ?>
        <section class="ledger__day">
            <h3 class="ledger__date"><?= esc(ui_date($day, 'l j F Y')) ?></h3>
            <ul class="rows">
                <?php foreach ($dayEntries as $entry): ?>
                    <?php $credit = (float) $entry['amount'] >= 0; ?>
                    <li class="row ledger__row">
                        <span class="ledger__icon ledger__icon--<?= $credit ? 'in' : 'out' ?>"><?= ui_icon($credit ? 'credit' : 'debit', 'icon icon--sm') ?></span>
                        <span>
                            <span class="row__title"><?= esc($entry['description']) ?></span>
                            <?php if (isset($entry['first_name'])): ?>
                                <span class="row__sub"><?= esc($entry['first_name'] . ' ' . $entry['last_name']) ?> (<?= esc($entry['member_code']) ?>)</span>
                            <?php endif ?>
                        </span>
                        <span class="ledger__figures">
                            <strong class="num <?= $credit ? 'pos' : 'neg' ?>"><?= esc(ui_signed_points($entry['amount'])) ?></strong>
                            <small class="num muted">Balance <?= esc(ui_points($entry['balance_after'])) ?></small>
                        </span>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
    <?php endforeach ?>
</div>
