<?php

declare(strict_types=1);

namespace App\Models;

class PilotModel extends AppModel
{
    protected $table = 'pilots';

    protected $allowedFields = [
        'first_name', 'last_name', 'birth_date', 'city', 'address', 'country', 'cell_phone', 'work_phone',
        'home_phone', 'email_primary', 'email_secondary', 'contract_type', 'basic_salary', 'hourly_salary',
        'extra_bonus', 'training_included', 'notes',
    ];

    public function menu(string $label = 'name'): array
    {
        $names = [];

        foreach ($this->builder()->where('deleted_at', null)->orderBy('last_name')->get()->getResultArray() as $pilot) {
            $names[$pilot['id']] = $pilot['first_name'] . ' ' . $pilot['last_name'];
        }

        return $names;
    }
}
