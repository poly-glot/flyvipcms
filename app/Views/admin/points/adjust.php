<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Add points<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>Credit a member's balance outside the payment flow.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url('admin/points/adjust') ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Adjustment</h2>
            <p>The remark is stored in the ledger entry.</p>
        </div>
        <div class="fields">
            <?= ui_select('user_id', 'Member', $members, null, ['field_class' => 'field--full', 'required' => true]) ?>
            <?= ui_field('amount', 'Points', 'number', null, ['min' => '0.01', 'required' => true, 'step' => '0.01']) ?>
            <?= ui_field('remark', 'Remark', 'text', null, ['required' => true]) ?>
        </div>
    </section>
    <div class="form-actions">
        <button class="button" type="submit">Add points</button>
        <a class="button button--quiet" href="<?= site_url('admin/points') ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
