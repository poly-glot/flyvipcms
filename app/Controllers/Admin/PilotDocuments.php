<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AppModel;
use App\Models\PilotDocumentModel;
use App\Models\PilotModel;

class PilotDocuments extends CrudController
{
    protected function slug(): string
    {
        return 'pilot-documents';
    }

    protected function title(): string
    {
        return 'Pilot document';
    }

    protected function model(): AppModel
    {
        return new PilotDocumentModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'pilot_id', 'label' => 'Pilot', 'type' => 'select', 'options' => new PilotModel()->menu(), 'rules' => 'required|is_natural_no_zero'],
            ['name' => 'doc_type', 'label' => 'Type', 'type' => 'text', 'rules' => 'required|max_length[40]'],
            ['name' => 'doc_number', 'label' => 'Number', 'type' => 'text', 'rules' => 'required|max_length[60]'],
            ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'rules' => 'permit_empty|max_length[80]'],
            ['name' => 'issued_on', 'label' => 'Issued', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]'],
            ['name' => 'expires_on', 'label' => 'Expires', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]'],
        ];
    }
}
