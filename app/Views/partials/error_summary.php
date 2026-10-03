<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (is_array($errors) && $errors !== []): ?>
    <div class="summary" role="alert">
        <?= ui_icon('alert') ?>
        <div>
            <strong><?= count($errors) === 1 ? 'One thing needs your attention' : count($errors) . ' things need your attention' ?></strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
<?php endif ?>
