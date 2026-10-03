<?php

declare(strict_types=1);

namespace App\Models;

class PilotCertificationModel extends AppModel
{
    protected $table = 'pilot_certifications';

    protected $allowedFields = ['pilot_id', 'aircraft_id', 'certificate', 'cert_type', 'agency_school', 'issued_on', 'expires_on', 'due_on', 'active', 'comments'];
}
