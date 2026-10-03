<?php

declare(strict_types=1);

namespace App\Models;

class PilotFlightModel extends AppModel
{
    protected $table = 'pilot_flights';

    protected $allowedFields = ['pilot_id', 'aircraft_id', 'route_id', 'flight_date', 'identification', 'call_sign', 'day_hours', 'night_hours', 'ifr_hours', 'landings', 'total_hours', 'comments'];
}
