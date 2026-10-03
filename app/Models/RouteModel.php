<?php

declare(strict_types=1);

namespace App\Models;

class RouteModel extends AppModel
{
    protected $table = 'air_routes';

    protected $allowedFields = ['from_airport_id', 'to_airport_id', 'aircraft_id', 'distance', 'flight_time', 'ete', 'actual_ete', 'cost'];

    public function labelled(): array
    {
        $airports = new AirportModel()->menu();
        $labels = [];

        foreach ($this->builder()->where('deleted_at', null)->orderBy('id')->get()->getResultArray() as $route) {
            $labels[$route['id']] = ($airports[$route['from_airport_id']] ?? '?') . ' → ' . ($airports[$route['to_airport_id']] ?? '?');
        }

        return $labels;
    }
}
