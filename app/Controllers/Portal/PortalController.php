<?php

declare(strict_types=1);

namespace App\Controllers\Portal;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

abstract class PortalController extends BaseController
{
    protected function member(): array
    {
        $member = db_connect()->table('members')
            ->select('members.*, profiles.first_name, profiles.last_name, membership_plans.name AS plan_name')
            ->join('profiles', 'profiles.user_id = members.user_id')
            ->join('membership_plans', 'membership_plans.id = members.plan_id', 'left')
            ->where('members.user_id', auth()->id())
            ->where('members.deleted_at', null)
            ->get()
            ->getRowArray();

        return $member ?? throw PageNotFoundException::forPageNotFound();
    }

    protected function accountUserId(array $member): int
    {
        return (int) ($member['parent_user_id'] ?? $member['user_id']);
    }

    protected function isPrimary(array $member): bool
    {
        return $member['plan_id'] !== null;
    }
}
