<?php

declare(strict_types=1);

namespace App\Models;

class AircraftRepairModel extends AppModel
{
    protected $table = 'aircraft_repairs';

    protected $allowedFields = [
        'aircraft_id', 'repaired_on', 'code', 'description', 'repair_shop', 'performed_hours',
        'expires_on', 'expiry_hours', 'cost',
    ];
}
