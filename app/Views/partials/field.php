<?php
$name = $field['name'];
$type = $field['type'];
$current = isset($row[$name]) ? (string) $row[$name] : null;
$wide = $type === 'textarea' ? 'field--full' : '';

if ($type === 'checkbox') {
    $value = (string) old($name, $current ?? '');
    echo ui_switch($name, $field['label'], $value === '1' || ($current === null && $value === ''));

    return;
}

if ($type === 'select') {
    echo ui_select($name, $field['label'], $field['options'], $current, ['blank' => '—']);

    return;
}

echo ui_field($name, $field['label'], $type, $current, ['field_class' => $wide, 'step' => $type === 'number' ? 'any' : null]);
