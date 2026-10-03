<?= $this->extend('layouts/shell') ?>

<?= $this->section('title') ?>Record payment<?= $this->endSection() ?>
<?= $this->section('subtitle') ?>Fees are calculated from the member's stored plan.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="get" action="<?= site_url('admin/payments/new') ?>">
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Member</h2>
            <p>Only primary members can pay. Sub-members use the primary account.</p>
        </div>
        <div class="fields">
            <div class="field field--full">
                <label for="f_member">Member</label>
                <select id="f_member" name="user_id" data-autosubmit>
                    <option value="">Select a member</option>
                    <?php foreach ($members as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $selected === (int) $id ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <noscript><button class="button" type="submit">Load member</button></noscript>
        </div>
    </section>
</form>

<?php if ($selected > 0): ?>
    <form class="form" method="post" action="<?= site_url('admin/payments') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= $selected ?>">
        <section class="form-section">
            <div class="form-section__intro">
                <h2>Payment type</h2>
                <p>The joining fee comes first. After that the other types unlock.</p>
            </div>
            <fieldset class="choices">
                <legend class="sr-only">Payment type</legend>
                <?php foreach ($allowed as $position => $type): ?>
                    <label class="choice">
                        <input type="radio" name="type" value="<?= esc($type, 'attr') ?>" <?= old('type', $allowed[0]) === $type ? 'checked' : '' ?>>
                        <span class="choice__body">
                            <strong><?= esc(ucfirst($type)) ?></strong>
                            <span class="muted num"><?= isset($expected[$type]) ? esc($expected[$type]) . ($type === 'quarterly' ? ' per quarter' : '') : 'Free amount' ?></span>
                        </span>
                    </label>
                <?php endforeach ?>
            </fieldset>
        </section>
        <section class="form-section">
            <div class="form-section__intro">
                <h2>Details</h2>
                <p>Amounts for joining, yearly and quarterly are set by the plan.</p>
            </div>
            <div class="fields">
                <div class="contents" data-when="type:bonus,points" hidden>
                    <?= ui_field('amount', 'Amount', 'number', null, ['min' => '0.01', 'step' => '0.01']) ?>
                </div>
                <div class="contents" data-when="type:quarterly" hidden>
                    <?= ui_select('quarters', 'Quarters to pay', [1 => '1 quarter', 2 => '2 quarters', 3 => '3 quarters', 4 => '4 quarters']) ?>
                </div>
                <div class="contents" data-when="type:yearly" hidden>
                    <?= ui_select('term_start', 'Term start', array_combine($termStarts, array_map(static fn (string $start): string => ui_date($start), $termStarts))) ?>
                </div>
                <?= ui_field('paid_on', 'Paid on', 'date', date('Y-m-d'), ['required' => true]) ?>
                <?= ui_field('remark', 'Mode of payment', 'text', null, ['field_class' => 'field--full', 'hint' => 'For example bank transfer or card.', 'required' => true]) ?>
            </div>
        </section>
        <div class="form-actions">
            <button class="button" type="submit">Record payment</button>
            <a class="button button--quiet" href="<?= site_url('admin/payments') ?>">Cancel</a>
        </div>
    </form>
<?php endif ?>
<?= $this->endSection() ?>
