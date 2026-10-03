<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<h1>Change password</h1>
<form class="grid" method="post" action="<?= site_url('account/password') ?>">
    <?= csrf_field() ?>
    <label>Current password<input type="password" name="current_password" autocomplete="current-password" required></label>
    <label>New password<input type="password" name="new_password" autocomplete="new-password" minlength="8" required></label>
    <label>Confirm new password<input type="password" name="confirm_password" autocomplete="new-password" required></label>
    <div class="full"><button type="submit">Update password</button></div>
</form>
<?= $this->endSection() ?>
