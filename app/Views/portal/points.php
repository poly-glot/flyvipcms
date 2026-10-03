<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Points<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>Every credit and charge on the account<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="balance" aria-label="Points balance">
    <div class="balance__figure">
        <span class="balance__label">Available points</span>
        <strong class="balance__value num"><?= esc(ui_points($balance)) ?></strong>
    </div>
    <div class="balance__trend"><?= ui_sparkline(array_column(array_reverse(array_slice($entries, 0, 30)), 'balance_after'), 220, 64) ?></div>
</section>

<section class="page-section">
    <div class="section-head"><h2>History</h2></div>
    <?php if ($entries === []): ?>
        <?= view('partials/empty', ['icon' => 'star', 'title' => 'No points movements yet', 'text' => 'Payments and bookings will show up here as they happen.']) ?>
    <?php else: ?>
        <?= view('partials/ledger', ['entries' => $entries]) ?>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
