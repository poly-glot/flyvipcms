<ul class="rows pilots">
    <?php foreach ($rows as $row): ?>
        <?php $name = $row['first_name'] . ' ' . $row['last_name']; ?>
        <li data-filter-item data-filter-text="<?= esc(strtolower($name . ' ' . ($row['city'] ?? '') . ' ' . ($row['email_primary'] ?? '')), 'attr') ?>">
            <div class="row row--pilot">
                <?= ui_avatar($row['first_name'], $row['last_name']) ?>
                <a class="row__identity strong-link" href="<?= site_url("admin/{$slug}/{$row['id']}/edit") ?>">
                    <span class="row__title"><?= esc($name) ?></span>
                    <span class="row__sub"><?= esc($row['city'] ?? 'No base city') ?></span>
                </a>
                <span class="row__metric">
                    <span class="row__label">Contact</span>
                    <span><?= esc($row['email_primary'] ?? $row['cell_phone'] ?? '—') ?></span>
                </span>
                <?php if (($row['contract_type'] ?? '') !== ''): ?>
                    <span class="pill pill--accent pill--plain"><?= esc($row['contract_type']) ?></span>
                <?php endif ?>
                <?= view('admin/crud/row_actions', ['slug' => $slug, 'row' => $row, 'title' => $title]) ?>
            </div>
        </li>
    <?php endforeach ?>
</ul>
