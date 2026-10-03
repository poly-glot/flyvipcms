<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' · FLY VIP' : 'FLY VIP' ?></title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body class="<?= esc($bodyClass ?? '') ?>">
<?= $this->renderSection('body') ?>
<?= $this->renderSection('scripts') ?>
</body>
</html>
