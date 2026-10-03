<?php
$name = $field['name'];
$type = $field['type'];
$value = old($name, $row[$name] ?? '');
$id = 'f_' . $name;
?>
<?php if ($type === 'checkbox'): ?>
    <label class="check"><input type="checkbox" name="<?= esc($name) ?>" value="1" <?= (string) $value === '1' || (!isset($row[$name]) && $value === '') ? 'checked' : '' ?>> <?= esc($field['label']) ?></label>
<?php else: ?>
    <label for="<?= $id ?>"><?= esc($field['label']) ?>
        <?php if ($type === 'select'): ?>
            <select id="<?= $id ?>" name="<?= esc($name) ?>">
                <option value="">—</option>
                <?php foreach ($field['options'] as $optionValue => $optionLabel): ?>
                    <option value="<?= esc($optionValue) ?>" <?= (string) $value === (string) $optionValue ? 'selected' : '' ?>><?= esc($optionLabel) ?></option>
                <?php endforeach ?>
            </select>
        <?php elseif ($type === 'textarea'): ?>
            <textarea id="<?= $id ?>" name="<?= esc($name) ?>" rows="3"><?= esc($value) ?></textarea>
        <?php else: ?>
            <input id="<?= $id ?>" type="<?= esc($type) ?>" name="<?= esc($name) ?>" value="<?= esc($value) ?>" <?= $type === 'number' ? 'step="any"' : '' ?>>
        <?php endif ?>
    </label>
<?php endif ?>
