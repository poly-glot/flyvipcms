<section class="form-section">
    <div class="form-section__intro">
        <h2>Flight</h2>
        <p>Pick the route, the aircraft and the day you want to fly.</p>
    </div>
    <div class="fields">
        <?= ui_select('route_id', 'Route', $routes, null, ['field_class' => 'field--full', 'required' => true]) ?>
        <?= ui_select('aircraft_id', 'Aircraft', $aircrafts, null, ['data-capacities' => json_encode($capacities), 'required' => true]) ?>
        <?= ui_field('flight_date', 'Flight date', 'date', null, ['min' => date('Y-m-d'), 'required' => true]) ?>
    </div>
</section>
<section class="form-section">
    <div class="form-section__intro">
        <h2>Seats</h2>
        <p>Complete takes the whole aircraft. Partial shares it, and you choose your seats.</p>
    </div>
    <div class="booking-seats">
        <?= ui_segmented('kind', 'Reservation type', ['partial' => 'Partial', 'complete' => 'Complete'], (string) old('kind', 'partial')) ?>
        <p class="booking-seats__note" data-complete-note hidden>Every seat on this aircraft is reserved for you and your guests.</p>
        <div class="seatmap" data-seatmap data-url="<?= site_url('flights/taken-seats') ?>" data-selected="<?= esc(implode(',', (array) old('seats', [])), 'attr') ?>">
            <div class="seatmap__legend" aria-hidden="true">
                <span><i class="seat-key"></i>Available</span>
                <span><i class="seat-key seat-key--selected"></i>Yours</span>
                <span><i class="seat-key seat-key--taken"></i>Taken</span>
            </div>
            <div class="cabin" data-seatmap-grid role="group" aria-label="Seat map"></div>
            <p class="seatmap__status" data-seatmap-status aria-live="polite">Choose an aircraft to see its seats.</p>
        </div>
    </div>
</section>
