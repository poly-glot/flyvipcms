<?php
$display = static function (array $column, array $row): string {
    $cell = $row[$column['name']] ?? '';

    return match ($column['type']) {
        'select' => (string) ($column['options'][$cell] ?? ''),
        'checkbox' => (int) $cell === 1 ? 'Yes' : 'No',
        'date' => $cell === '' ? '' : ui_date((string) $cell),
        default => (string) $cell,
    };
};
?>
<div class="table-wrap">
    <table class="data">
        <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th<?= $column['type'] === 'number' ? ' class="num"' : '' ?>><?= esc($column['label']) ?></th>
                <?php endforeach ?>
                <th><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr data-filter-item data-filter-text="<?= esc(strtolower(implode(' ', array_map(static fn (array $column): string => $display($column, $row), $columns))), 'attr') ?>">
                    <?php foreach ($columns as $index => $column): ?>
                        <?php $text = $display($column, $row); ?>
                        <td<?= $column['type'] === 'number' ? ' class="num"' : '' ?>>
                            <?php if ($column['type'] === 'checkbox'): ?>
                                <span class="pill pill--<?= $text === 'Yes' ? 'active' : 'inactive' ?>"><?= esc($text) ?></span>
                            <?php elseif ($index === 0): ?>
                                <a class="strong-link" href="<?= site_url("admin/{$slug}/{$row['id']}/edit") ?>"><?= esc($text) ?></a>
                            <?php else: ?>
                                <?= esc($text) ?>
                            <?php endif ?>
                        </td>
                    <?php endforeach ?>
                    <td><?= view('admin/crud/row_actions', ['slug' => $slug, 'row' => $row, 'title' => $title]) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
