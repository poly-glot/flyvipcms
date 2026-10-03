<?php
$text = static fn (string $key, string $label, string $type = 'text'): string => ui_field($key, $label, $type, $values[$key] ?? null);
$select = static fn (string $key, string $label, array $options): string => ui_select($key, $label, $options, isset($values[$key]) ? (string) $values[$key] : null, ['blank' => '—']);
?>
<section class="form-section">
    <div class="form-section__intro">
        <h2>Contact</h2>
        <p>Who we speak to and how to reach them.</p>
    </div>
    <div class="fields">
        <?php if ($withName ?? true): ?>
            <?= $text('first_name', 'First name') ?>
            <?= $text('last_name', 'Last name') ?>
        <?php endif ?>
        <?= $text('company', 'Company') ?>
        <?= $text('cellphone', 'Cell phone', 'tel') ?>
        <?= $text('office_phone', 'Office phone', 'tel') ?>
        <?= $text('home_phone', 'Home phone', 'tel') ?>
        <?= $text('secondary_phone', 'Secondary phone', 'tel') ?>
    </div>
</section>
<section class="form-section">
    <div class="form-section__intro">
        <h2>Address</h2>
        <p>Used for invoices and travel documents.</p>
    </div>
    <div class="fields">
        <?= $text('street_address', 'Street address') ?>
        <?= $select('country_id', 'Country', $countries) ?>
        <?= $select('zone_id', 'State / region', $zones) ?>
        <?= $text('city', 'City') ?>
        <?= $text('zip_code', 'Zip code') ?>
    </div>
</section>
<section class="form-section">
    <div class="form-section__intro">
        <h2>Identity</h2>
        <p>Needed to board. Keep expiry dates current.</p>
    </div>
    <div class="fields">
        <?= $text('id_number', 'ID number') ?>
        <?= $text('id_due_on', 'ID due date', 'date') ?>
        <?= $text('passport_number', 'Passport number') ?>
        <?= $text('passport_issued_on', 'Passport issued', 'date') ?>
        <?= $text('passport_due_on', 'Passport due', 'date') ?>
    </div>
</section>
<section class="form-section">
    <div class="form-section__intro">
        <h2>Preferences</h2>
    </div>
    <div class="fields">
        <?= $select('favourite_route_id', 'Route of interest', $routes) ?>
    </div>
</section>
