<?php
$value = static fn (string $key): string => (string) old($key, $values[$key] ?? '');

$text = static fn (string $key, string $label, string $type = 'text'): string => sprintf(
    '<label>%s<input type="%s" name="%s" value="%s"></label>',
    esc($label),
    $type,
    $key,
    esc($value($key)),
);

$select = static function (string $key, string $label, array $options) use ($value): string {
    $html = sprintf('<label>%s<select name="%s"><option value="">—</option>', esc($label), $key);

    foreach ($options as $id => $name) {
        $html .= sprintf('<option value="%s"%s>%s</option>', esc((string) $id), $value($key) === (string) $id ? ' selected' : '', esc($name));
    }

    return $html . '</select></label>';
};
?>
<?php if ($withName ?? true): ?>
    <?= $text('first_name', 'First name') ?>
    <?= $text('last_name', 'Last name') ?>
<?php endif ?>
<?= $text('company', 'Company') ?>
<?= $text('street_address', 'Street address') ?>
<?= $select('country_id', 'Country', $countries) ?>
<?= $select('zone_id', 'State / region', $zones) ?>
<?= $text('city', 'City') ?>
<?= $text('zip_code', 'Zip code') ?>
<?= $text('cellphone', 'Cell phone') ?>
<?= $text('office_phone', 'Office phone') ?>
<?= $text('home_phone', 'Home phone') ?>
<?= $text('secondary_phone', 'Secondary phone') ?>
<?= $text('id_number', 'ID number') ?>
<?= $text('id_due_on', 'ID due date', 'date') ?>
<?= $text('passport_number', 'Passport number') ?>
<?= $text('passport_issued_on', 'Passport issued', 'date') ?>
<?= $text('passport_due_on', 'Passport due', 'date') ?>
<?= $select('favourite_route_id', 'Route of interest', $routes) ?>
