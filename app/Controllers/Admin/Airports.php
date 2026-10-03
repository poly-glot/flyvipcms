<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AirportModel;
use App\Models\AppModel;

class Airports extends CrudController
{
    protected function slug(): string
    {
        return 'airports';
    }

    protected function title(): string
    {
        return 'Airport';
    }

    protected function model(): AppModel
    {
        return new AirportModel();
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max_length[120]'],
            ['name' => 'call_sign', 'label' => 'Call sign', 'type' => 'text', 'rules' => 'permit_empty|max_length[20]'],
            ['name' => 'airport_type', 'label' => 'Type', 'type' => 'text', 'rules' => 'permit_empty|max_length[40]'],
            ['name' => 'customs_contact', 'label' => 'Customs contact', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'list' => false],
            ['name' => 'fbo_contact', 'label' => 'FBO contact', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'list' => false],
            ['name' => 'hotel_contact', 'label' => 'Hotel contact', 'type' => 'text', 'rules' => 'permit_empty|max_length[120]', 'list' => false],
            ['name' => 'travel_allowance', 'label' => 'Travel allowance', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'take_off_cost', 'label' => 'Take-off cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'landing_cost', 'label' => 'Landing cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'stay_cost', 'label' => 'Stay cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'airport_cost', 'label' => 'Airport cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'handling_cost', 'label' => 'Handling cost', 'type' => 'number', 'rules' => 'permit_empty|decimal', 'list' => false],
            ['name' => 'comments', 'label' => 'Comments', 'type' => 'textarea', 'rules' => 'permit_empty', 'list' => false],
        ];
    }
}
