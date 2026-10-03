(() => {
    const aircraft = document.querySelector('select[name="aircraft_id"]');
    const kind = document.querySelector('select[name="kind"]');
    const seatsBox = document.querySelector('[data-seats]');

    if (!aircraft || !kind || !seatsBox) {
        return;
    }

    const capacities = JSON.parse(aircraft.dataset.capacities);

    const sync = () => {
        const capacity = Number(capacities[aircraft.value] || 0);
        const partial = kind.value === 'partial';

        seatsBox.hidden = !partial;

        seatsBox.querySelectorAll('[data-seat]').forEach((seat) => {
            const visible = Number(seat.dataset.seat) <= capacity;
            seat.hidden = !visible;
            seat.querySelector('input').disabled = !visible || !partial;
        });
    };

    aircraft.addEventListener('change', sync);
    kind.addEventListener('change', sync);
    sync();
})();
