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
            'series' => array_column(array_reverse($db->table('points_ledger')->select('balance_after')->where('user_id', $accountId)->orderBy('id', 'DESC')->limit(30)->get()->getResultArray()), 'balance_after'),
            'upcoming' => service('reservations')->listing()
                ->where('reservations.user_id', $accountId)
                ->where('reservations.status !=', 'cancelled')
                ->where('reservations.flight_date >=', date('Y-m-d'))
                ->orderBy('reservations.flight_date')
                ->limit(3)
                ->get()
                ->getResultArray(),
            'activity' => $db->table('reservation_activities')->where('user_id', $accountId)->orderBy('id', 'DESC')->limit(10)->get()->getResultArray(),
        ]);
    }
}
