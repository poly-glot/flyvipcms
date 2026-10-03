<?php foreach (['success' => 'ok', 'error' => 'bad', 'message' => 'ok'] as $key => $class): ?>
    <?php if (session()->getFlashdata($key) !== null): ?>
        <div class="flash flash--<?= $class ?>" role="status"><?= esc(session()->getFlashdata($key)) ?></div>
    <?php endif ?>
<?php endforeach ?>
<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (is_array($errors) && $errors !== []): ?>
    <div class="flash flash--bad" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>
