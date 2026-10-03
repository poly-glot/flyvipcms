<label>Route
    <select name="route_id" required>
        <?php foreach ($routes as $id => $label): ?>
            <option value="<?= $id ?>" <?= (string) old('route_id') === (string) $id ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach ?>
    </select>
</label>
<label>Aircraft
    <select name="aircraft_id" required data-capacities="<?= esc(json_encode($capacities), 'attr') ?>">
        <?php foreach ($aircrafts as $id => $label): ?>
            <option value="<?= $id ?>" <?= (string) old('aircraft_id') === (string) $id ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach ?>
    </select>
</label>
<label>Flight date<input type="date" name="flight_date" min="<?= date('Y-m-d') ?>" value="<?= esc(old('flight_date')) ?>" required></label>
<label>Reservation type
    <select name="kind" required>
        <option value="partial" <?= old('kind') === 'partial' ? 'selected' : '' ?>>Partial (shared flight, choose seats)</option>
        <option value="complete" <?= old('kind') === 'complete' ? 'selected' : '' ?>>Complete (whole aircraft)</option>
    </select>
</label>
<fieldset class="full seats" data-seats>
    <legend>Seats (partial only)</legend>
    <?php foreach (range(1, 12) as $seat): ?>
        <label class="check seat" data-seat="<?= $seat ?>"><input type="checkbox" name="seats[]" value="<?= $seat ?>" <?= in_array((string) $seat, (array) old('seats', []), true) ? 'checked' : '' ?>> <?= $seat ?></label>
    <?php endforeach ?>
</fieldset>
