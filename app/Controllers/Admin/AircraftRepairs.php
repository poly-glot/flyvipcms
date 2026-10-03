<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AircraftModel;
use App\Models\AircraftRepairModel;
use App\Models\AppModel;

class AircraftRepairs extends CrudController
{
    protected function slug(): string
    {
        return 'maintenance';
    }

    protected function title(): string
    {
        return 'Maintenance record';
    }

    protected function model(): AppModel
    {
        return new AircraftRepairModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'aircraft_id', 'label' => 'Aircraft', 'type' => 'select', 'options' => new AircraftModel()->menu(), 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'repaired_on', 'label' => 'Date', 'type' => 'date', 'rules' => 'required|valid_date[Y-m-d]'],
            ['name' => 'code', 'label' => 'Code', 'type' => 'text', 'rules' => 'required|max_length[50]'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => 'required'],
            ['name' => 'repair_shop', 'label' => 'Repair shop', 'type' => 'text', 'rules' => 'required|max_length[100]'],
            ['name' => 'performed_hours', 'label' => 'Performed hours', 'type' => 'number', 'rules' => 'required|decimal'],
            ['name' => 'expires_on', 'label' => 'Expires on', 'type' => 'date', 'rules' => 'required|valid_date[Y-m-d]'],
            ['name' => 'expiry_hours', 'label' => 'Expiry hours', 'type' => 'number', 'rules' => 'required|decimal'],
            ['name' => 'cost', 'label' => 'Cost', 'type' => 'number', 'rules' => 'required|decimal'],
        ];
    }
}
