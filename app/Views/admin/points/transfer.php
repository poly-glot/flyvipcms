<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Transfer points<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>Move points from one member to another.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url('admin/points/transfer') ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Transfer</h2>
            <p>The sender must have enough points to cover it.</p>
        </div>
        <div class="fields">
            <?= ui_select('sender_id', 'From member', $members, null, ['required' => true]) ?>
            <?= ui_select('receiver_id', 'To member', $members, null, ['required' => true]) ?>
            <?= ui_field('amount', 'Points', 'number', null, ['min' => '0.01', 'required' => true, 'step' => '0.01']) ?>
        </div>
    </section>
    <div class="form-actions">
        <button class="button" type="submit">Transfer points</button>
        <a class="button button--quiet" href="<?= site_url('admin/points') ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
