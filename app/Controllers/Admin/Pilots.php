<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AppModel;
use App\Models\PilotModel;

class Pilots extends CrudController
{
    protected function slug(): string
    {
        return 'pilots';
    }

    protected function title(): string
    {
        return 'Pilot';
    }

    protected function model(): AppModel
    {
        return new PilotModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'first_name', 'label' => 'First name', 'type' => 'text', 'rules' => 'required|max_length[80]'],
            ['name' => 'last_name', 'label' => 'Last name', 'type' => 'text', 'rules' => 'required|max_length[80]'],
            ['name' => 'birth_date', 'label' => 'Date of birth', 'type' => 'date', 'rules' => 'permit_empty|valid_date[Y-m-d]', 'list' => false],
            ['name' => 'city', 'label' => 'City', 'type' => 'text', 'rules' => 'permit_empty|max_length[80]'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'rules' => 'permit_empty|max_length[255]', 'list' => false],
            ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'rules' => 'permit_empty|max_length[80]', 'list' => false],
            ['name' => 'cell_phone', 'label' => 'Cell phone', 'type' => 'text', 'rules' => 'permit_empty|max_length[30]'],
            ['name' => 'work_phone', 'label' => 'Work phone', 'type' => 'text', 'rules' => 'permit_empty|max_length[30]', 'list' => false],
            ['name' => 'home_phone', 'label' => 'Home phone', 'type' => 'text', 'rules' => 'permit_empty|max_length[30]', 'list' => false],
            ['name' => 'email_primary', 'label' => 'Email', 'type' => 'email', 'rules' => 'permit_empty|valid_email|max_length[120]'],
            ['name' => 'email_secondary', 'label' => 'Secondary email', 'type' => 'email', 'rules' => 'permit_empty|valid_email|max_length[120]', 'list' => false],
            ['name' => 'contract_type', 'label' => 'Contract type', 'type' => 'text', 'rules' => 'permit_empty|max_length[40]'],
            ['name' => 'basic_salary', 'label' => 'Basic salary', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'hourly_salary', 'label' => 'Hourly salary', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'extra_bonus', 'label' => 'Extra bonus', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'training_included', 'label' => 'Training included', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'list' => false],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => 'permit_empty', 'list' => false],
        ];
    }
}
