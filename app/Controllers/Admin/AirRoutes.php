<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AircraftModel;
use App\Models\AirportModel;
use App\Models\AppModel;
use App\Models\RouteModel;

class AirRoutes extends CrudController
{
    protected function slug(): string
    {
        return 'routes';
    }

    protected function title(): string
    {
        return 'Route';
    }

    protected function model(): AppModel
    {
        return new RouteModel();
    }

    protected function fields(): array
    {
        $airports = new AirportModel()->menu();

        return [
            ['name' => 'from_airport_id', 'label' => 'From', 'type' => 'select', 'options' => $airports, 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'to_airport_id', 'label' => 'To', 'type' => 'select', 'options' => $airports, 'rules' => 'required|is_natural_no_zero|differs[from_airport_id]'],
            ['name' => 'aircraft_id', 'label' => 'Aircraft', 'type' => 'select', 'options' => new AircraftModel()->menu(), 'rules' => 'permit_empty|is_natural_no_zero'],
            ['name' => 'distance', 'label' => 'Distance (nm)', 'type' => 'number', 'rules' => 'permit_empty|decimal'],
            ['name' => 'flight_time', 'label' => 'Flight time', 'type' => 'text', 'rules' => 'permit_empty|max_length[20]'],
            ['name' => 'ete', 'label' => 'ETE', 'type' => 'text', 'rules' => 'permit_empty|max_length[20]', 'list' => false],
            ['name' => 'actual_ete', 'label' => 'Actual ETE', 'type' => 'text', 'rules' => 'permit_empty|max_length[20]', 'list' => false],
            ['name' => 'cost', 'label' => 'Cost per seat', 'type' => 'number', 'rules' => 'required|decimal|greater_than_equal_to[0]'],
        ];
    }
}
