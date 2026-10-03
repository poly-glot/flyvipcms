<?php

declare(strict_types=1);

namespace App\Controllers\Portal;

use App\Models\RouteModel;
use CodeIgniter\HTTP\RedirectResponse;

class Profile extends PortalController
{
    private const array EDITABLE = ['company', 'street_address', 'country_id', 'zone_id', 'city', 'zip_code', 'cellphone', 'office_phone', 'home_phone', 'secondary_phone', 'id_number', 'id_due_on', 'passport_number', 'passport_issued_on', 'passport_due_on', 'favourite_route_id'];

    public function edit(): string
    {
        $db = db_connect();

        return view('portal/profile', [
            'profile' => $db->table('profiles')->where('user_id', auth()->id())->get()->getRowArray(),
            'countries' => array_column($db->table('countries')->where('active', 1)->orderBy('name')->get()->getResultArray(), 'name', 'id'),
            'zones' => array_column($db->table('country_zones')->where('active', 1)->orderBy('name')->get()->getResultArray(), 'name', 'id'),
            'routes' => new RouteModel()->labelled(),
            'readOnly' => !$this->isPrimary($this->member()),
        ]);
    }

    public function update(): RedirectResponse
    {
        if (!$this->isPrimary($this->member())) {
            return $this->back('portal', 'error', 'Only the primary member can edit the profile.');
        }

        $rules = [
            'company' => 'permit_empty|max_length[150]',
            'street_address' => 'permit_empty|max_length[255]',
            'country_id' => 'permit_empty|is_natural_no_zero',
            'zone_id' => 'permit_empty|is_natural_no_zero',
            'city' => 'permit_empty|max_length[100]',
            'zip_code' => 'permit_empty|max_length[20]',
            'cellphone' => 'permit_empty|max_length[30]',
            'office_phone' => 'permit_empty|max_length[30]',
            'home_phone' => 'permit_empty|max_length[30]',
            'secondary_phone' => 'permit_empty|max_length[30]',
            'id_number' => 'permit_empty|max_length[50]',
            'id_due_on' => 'permit_empty|valid_date[Y-m-d]',
            'passport_number' => 'permit_empty|max_length[50]',
            'passport_issued_on' => 'permit_empty|valid_date[Y-m-d]',
            'passport_due_on' => 'permit_empty|valid_date[Y-m-d]',
            'favourite_route_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithErrors($this->validator?->getErrors() ?? []);
        }

        $row = array_map(static fn (mixed $value): mixed => $value === '' ? null : $value, $this->request->getPost(self::EDITABLE));
        db_connect()->table('profiles')->where('user_id', auth()->id())->update($row + ['updated_at' => date('Y-m-d H:i:s')]);

        return $this->back('portal/profile', 'success', 'Profile updated.');
    }
}
