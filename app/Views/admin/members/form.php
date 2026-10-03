<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $editing = $member !== null; ?>
<div class="head"><h1><?= $editing ? 'Edit member' : 'New member' ?></h1></div>
<form class="grid" method="post" action="<?= site_url($action) ?>">
    <?= csrf_field() ?>
    <fieldset class="full grid">
        <legend>Account</legend>
        <label>Username<input type="text" name="username" value="<?= esc(old('username', $member['username'] ?? '')) ?>" required></label>
        <label>Email<input type="email" name="email" value="<?= esc(old('email', $member['email'] ?? '')) ?>" required></label>
        <label>Password<?= $editing ? ' (leave blank to keep)' : '' ?><input type="password" name="password" autocomplete="new-password" <?= $editing ? '' : 'required' ?>></label>
    </fieldset>
    <?php if (!$editing): ?>
        <fieldset class="full grid">
            <legend>Membership</legend>
            <label>Plan
                <select name="plan_id" required>
                    <?php foreach ($plans as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (string) old('plan_id') === (string) $id ? 'selected' : '' ?>><?= esc($name) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
            <label>Joining fee<input type="number" step="0.01" min="0" name="joining_fee" value="<?= esc(old('joining_fee', '5000')) ?>" required></label>
            <label>Yearly fee (points)<input type="number" step="0.01" min="0.01" name="yearly_fee" value="<?= esc(old('yearly_fee', '12000')) ?>" required></label>
        </fieldset>
    <?php endif ?>
    <fieldset class="full grid">
        <legend>Profile</legend>
        <?= view('partials/profile_fields', ['values' => $values]) ?>
    </fieldset>
    <div class="full">
        <button type="submit">Save</button>
        <a href="<?= site_url($editing ? "admin/members/{$member['user_id']}" : 'admin/members') ?>">Cancel</a>
    </div>
</form>
<?= $this->endSection() ?>
