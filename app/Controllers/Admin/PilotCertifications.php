<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AircraftModel;
use App\Models\AppModel;
use App\Models\PilotCertificationModel;
use App\Models\PilotModel;

class PilotCertifications extends CrudController
{
    protected function slug(): string
    {
        return 'pilot-certifications';
    }

    protected function title(): string
    {
        return 'Pilot certification';
    }

    protected function model(): AppModel
    {
        return new PilotCertificationModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'pilot_id', 'label' => 'Pilot', 'type' => 'select', 'options' => new PilotModel()->menu(), 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'certificate', 'label' => 'Certificate', 'type' => 'text', 'rules' => 'required|max_length[120]'],
            ['name' => 'aircraft_id', 'label' => 'Aircraft', 'type' => 'select', 'options' => new AircraftModel()->menu(), 'rules' => 'permit_empty|is_natural_no_zero'],
            ['name' => 'cert_type', 'label' => 'Type', 'type' => 'text', 'rules' => 'permit_empty|max_length[60]', 'list' => false],
            ['name' => 'agency_school', 'label' => 'Agency / school', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'list' => false],
            ['name' => 'issued_on', 'label' => 'Issued', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]'],
            ['name' => 'expires_on', 'label' => 'Expires', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]'],
            ['name' => 'due_on', 'label' => 'Renewal due', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]', 'list' => false],
            ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox'],
            ['name' => 'comments', 'label' => 'Comments', 'type' => 'textarea', 'rules' => 'permit_empty', 'list' => false],
        ];
    }
}
