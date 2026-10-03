<?php

declare(strict_types=1);

namespace App\Controllers\Portal;

use App\Services\MemberService;
use CodeIgniter\HTTP\RedirectResponse;

class Profile extends PortalController
{
    public function edit(): string
    {
        return view('portal/profile', service('members')->profileOptions() + [
            'profile' => db_connect()->table('profiles')->where('user_id', auth()->id())->get()->getRowArray(),
            'readOnly' => !$this->isPrimary($this->member()),
        ]);
    }

    public function update(): RedirectResponse
    {
        if (!$this->isPrimary($this->member())) {
            return $this->back('portal', 'error', 'Only the primary member can edit the profile.');
        }

        $fields = $this->editableFields();

        if (!$this->validate(array_intersect_key(MemberService::PROFILE_RULES, array_flip($fields)))) {
            return $this->backWithValidationErrors();
        }

        $row = array_map(static fn (mixed $value): mixed => $value === '' ? null : $value, $this->request->getPost($fields));
        db_connect()->table('profiles')->where('user_id', auth()->id())->update($row + ['updated_at' => date('Y-m-d H:i:s')]);

        return $this->back('portal/profile', 'success', 'Profile updated.');
    }

    private function editableFields(): array
    {
        return array_values(array_diff(MemberService::PROFILE_FIELDS, ['first_name', 'last_name']));
    }
}
