<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AircraftModel;
use App\Models\AppModel;
use App\Models\PilotFlightModel;
use App\Models\PilotModel;
use App\Models\RouteModel;

class PilotFlights extends CrudController
{
    protected function slug(): string
    {
        return 'flight-schedule';
    }

    protected function title(): string
    {
        return 'Scheduled flight';
    }

    protected function model(): AppModel
    {
        return new PilotFlightModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'flight_date', 'label' => 'Date', 'type' => 'date', 'rules' => 'required|valid_date[Y-m-d]'],
            ['name' => 'pilot_id', 'label' => 'Pilot', 'type' => 'select', 'options' => new PilotModel()->menu(), 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'aircraft_id', 'label' => 'Aircraft', 'type' => 'select', 'options' => new AircraftModel()->menu(), 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'route_id', 'label' => 'Route', 'type' => 'select', 'options' => new RouteModel()->labelled(), 'rules' => 'permit_empty|is_natural_no_zero'],
            ['name' => 'identification', 'label' => 'Flight identification', 'type' => 'text', 'rules' => 'permit_empty|max_length[60]'],
            ['name' => 'call_sign', 'label' => 'Call sign', 'type' => 'text', 'rules' => 'permit_empty|max_length[30]', 'list' => false],
            ['name' => 'day_hours', 'label' => 'Day hours', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'night_hours', 'label' => 'Night hours', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'ifr_hours', 'label' => 'IFR hours', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'landings', 'label' => 'Landings', 'type' => 'number', 'rules' => 'permit_empty|is_natural', 'list' => false],
            ['name' => 'total_hours', 'label' => 'Total hours', 'type' => 'number', 'rules' => 'permit_empty|decimal'],
            ['name' => 'comments', 'label' => 'Comments', 'type' => 'textarea', 'rules' => 'permit_empty', 'list' => false],
        ];
    }
}
