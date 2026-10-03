<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AircraftModel;
use App\Models\AircraftTypeModel;
use App\Models\AppModel;

class Aircrafts extends CrudController
{
    protected function slug(): string
    {
        return 'aircrafts';
    }

    protected function title(): string
    {
        return 'Aircraft';
    }

    protected function model(): AppModel
    {
        return new AircraftModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[100]'],
            ['name' => 'aircraft_type_id', 'label' => 'Type', 'type' => 'select', 'options' => new AircraftTypeModel()->menu(), 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'model', 'label' => 'Model', 'type' => 'text', 'rules' => 'permit_empty|max_length[100]', 'list' => false],
            ['name' => 'manufactured_on', 'label' => 'Manufactured', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]', 'list' => false],
            ['name' => 'passenger_capacity', 'label' => 'Passenger capacity', 'type' => 'number', 'rules' => 'required|is_natural_no_zero|less_than_equal_to[40]'],
            ['name' => 'airframe_hours', 'label' => 'Airframe hours', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'engine_hours_left', 'label' => 'Engine hours (left)', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'engine_hours_right', 'label' => 'Engine hours (right)', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'variable_cost', 'label' => 'Variable cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'fixed_cost', 'label' => 'Fixed cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'total_cost', 'label' => 'Cost per seat', 'type' => 'number', 'rules' => 'required|decimal|greater_than_equal_to[0]'],
            ['name' => 'max_altitude', 'label' => 'Max altitude (ft)', 'type' => 'number', 'rules' => 'permit_empty|is_natural', 'list' => false],
            ['name' => 'gallons_per_hour', 'label' => 'Gallons per hour', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'certification', 'label' => 'Certification', 'type' => 'text', 'rules' => 'permit_empty|max_length[100]', 'list' => false],
            ['name' => 'insurance_cost', 'label' => 'Insurance cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
        ];
    }
}
