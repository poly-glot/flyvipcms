<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AircraftTypeModel;
use App\Models\AppModel;

class AircraftTypes extends CrudController
{
    protected function slug(): string
    {
        return 'aircraft-types';
    }

    protected function title(): string
    {
        return 'Aircraft type';
    }

    protected function model(): AppModel
    {
        return new AircraftTypeModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[100]'],
        ];
    }
}
