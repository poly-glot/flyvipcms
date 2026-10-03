<?php

declare(strict_types=1);

namespace App\Models;

class AircraftTypeModel extends AppModel
{
    protected $table = 'aircraft_types';

    protected $allowedFields = ['name'];
}
