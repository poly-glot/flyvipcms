<?php
ob_start();
$this->renderSection('title');
$documentTitle = trim(strip_tags((string) ob_get_clean()));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title><?= $documentTitle !== '' ? esc($documentTitle) . ' · FLY VIP' : 'FLY VIP' ?></title>
    <script>
        try {
            if (localStorage.getItem('flyvip.sidebar') === 'collapsed') {
                document.documentElement.dataset.sidebar = 'collapsed';
            }
        } catch (error) {}
    </script>
    <?php foreach (['tokens', 'ui', 'shell', 'auth', 'people', 'dashboard', 'flights', 'ledger'] as $sheet): ?>
        <link rel="stylesheet" href="<?= base_url("css/{$sheet}.css") ?>">
    <?php endforeach ?>
</head>
<body class="<?= esc($bodyClass ?? '') ?>">
<?= view('partials/icons') ?>
<?= $this->renderSection('body') ?>
<?= view('partials/flash') ?>
<dialog class="dialog" id="confirm-dialog" aria-labelledby="confirm-title" aria-describedby="confirm-message">
    <form method="dialog">
        <h2 id="confirm-title">Are you sure?</h2>
        <p id="confirm-message"></p>
        <div class="dialog__actions">
            <button class="button button--quiet" value="cancel">Keep it</button>
            <button class="button button--danger" value="confirm" id="confirm-accept">Confirm</button>
        </div>
    </form>
</dialog>
<?php foreach (['shell', 'toast', 'confirm', 'forms', 'filter'] as $script): ?>
    <script src="<?= base_url("js/{$script}.js") ?>" defer></script>
<?php endforeach ?>
<?= $this->renderSection('scripts') ?>
</body>
</html>
