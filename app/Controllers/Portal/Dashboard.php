<?php

declare(strict_types=1);

namespace App\Controllers\Portal;

class Dashboard extends PortalController
{
    public function index(): string
    {
        $member = $this->member();
        $accountId = $this->accountUserId($member);
        $db = db_connect();

        return view('portal/dashboard', [
            'member' => $member,
            'primary' => $this->isPrimary($member),
            'balance' => service('points')->balance($accountId),
            'family' => $db->table('members')
                ->select('members.member_code, profiles.first_name, profiles.last_name, contact_types.name AS contact_type')
                ->join('profiles', 'profiles.user_id = members.user_id')
                ->join('contact_types', 'contact_types.id = members.contact_type_id')
                ->where('members.parent_user_id', $accountId)
                ->where('members.deleted_at', null)
                ->get()->getResultArray(),
            'activity' => $db->table('reservation_activities')->where('user_id', $accountId)->orderBy('id', 'DESC')->limit(10)->get()->getResultArray(),
        ]);
    }
}
