<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $columns = array_values(array_filter($fields, static fn (array $f): bool => ($f['list'] ?? true) && $f['type'] !== 'textarea')); ?>
<div class="head">
    <h1><?= esc($title) ?>s</h1>
    <a class="button" href="<?= site_url("admin/{$slug}/new") ?>">Add</a>
</div>
<table>
    <thead>
        <tr>
            <?php foreach ($columns as $column): ?>
                <th><?= esc($column['label']) ?></th>
            <?php endforeach ?>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <?php $cell = $row[$column['name']] ?? ''; ?>
                    <td><?= esc($column['type'] === 'select' ? ($column['options'][$cell] ?? '') : ($column['type'] === 'checkbox' ? ((int) $cell === 1 ? 'Yes' : 'No') : $cell)) ?></td>
                <?php endforeach ?>
                <td class="actions">
                    <a href="<?= site_url("admin/{$slug}/{$row['id']}/edit") ?>">Edit</a>
                    <form method="post" action="<?= site_url("admin/{$slug}/{$row['id']}/delete") ?>" onsubmit="return confirm('Delete this record?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if ($rows === []): ?>
            <tr><td colspan="<?= count($columns) + 1 ?>" class="muted">Nothing here yet.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?= $this->endSection() ?>
