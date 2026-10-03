<?= $this->extend('layouts/shell') ?>

<?php $editing = $member !== null; ?>
<?= $this->section('title') ?><?= $editing ? 'Edit member' : 'New member' ?><?= $this->endSection() ?>
<?= $this->section('subtitle') ?><?= $editing ? esc($member['member_code']) : 'Create the account, then record the joining fee.' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<form class="form" method="post" action="<?= site_url($action) ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section__intro">
            <h2>Account</h2>
            <p>How the member signs in.</p>
        </div>
        <div class="fields">
            <?= ui_field('username', 'Username', 'text', $member['username'] ?? null, ['required' => true]) ?>
            <?= ui_field('email', 'Email', 'email', $member['email'] ?? null, ['required' => true]) ?>
            <?= ui_field('password', 'Password', 'password', null, ['autocomplete' => 'new-password', 'hint' => $editing ? 'Leave blank to keep the current password.' : 'At least 12 characters.', 'required' => !$editing]) ?>
        </div>
    </section>
    <?php if (!$editing): ?>
        <section class="form-section">
            <div class="form-section__intro">
                <h2>Membership</h2>
                <p>Fees are stored with the member and drive every payment.</p>
            </div>
            <div class="fields">
                <?= ui_select('plan_id', 'Plan', $plans, null, ['required' => true]) ?>
                <?= ui_field('joining_fee', 'Joining fee', 'number', '5000', ['min' => '0', 'required' => true, 'step' => '0.01']) ?>
                <?= ui_field('yearly_fee', 'Yearly fee (points)', 'number', '12000', ['min' => '0.01', 'required' => true, 'step' => '0.01']) ?>
            </div>
        </section>
    <?php endif ?>
    <?= view('partials/profile_fields', ['values' => $values]) ?>
    <div class="form-actions">
        <button class="button" type="submit">Save member</button>
        <a class="button button--quiet" href="<?= site_url($editing ? "admin/members/{$member['user_id']}" : 'admin/members') ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
