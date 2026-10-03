<?php

declare(strict_types=1);

namespace App\Models;

class AircraftModel extends AppModel
{
    protected $table = 'aircrafts';

    protected $allowedFields = [
        'name', 'aircraft_type_id', 'model', 'manufactured_on', 'passenger_capacity', 'airframe_hours',
        'engine_hours_left', 'engine_hours_right', 'variable_cost', 'fixed_cost', 'total_cost',
        'max_altitude', 'gallons_per_hour', 'certification', 'insurance_cost',
    ];
}
