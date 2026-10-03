<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $today = date('Y-m-d');

        return view('admin/dashboard', [
            'counts' => [
                'Active members' => $db->table('members')->where('plan_id IS NOT NULL')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
                'Sub-members' => $db->table('members')->where('parent_user_id IS NOT NULL')->where('deleted_at', null)->countAllResults(),
                'Upcoming flights' => $db->table('reservations')->where('parent_reservation_id', null)->where('status !=', 'cancelled')->where('flight_date >=', $today)->countAllResults(),
                'Pending seat requests' => $db->table('reservations')->where('status', 'pending')->countAllResults(),
                'Aircraft' => $db->table('aircrafts')->where('deleted_at', null)->countAllResults(),
                'Pilots' => $db->table('pilots')->where('deleted_at', null)->countAllResults(),
            ],
            'overdue' => $db->table('members')
                ->select('members.user_id, members.member_code, members.next_due_on, profiles.first_name, profiles.last_name')
                ->join('profiles', 'profiles.user_id = members.user_id')
                ->where('members.plan_id IS NOT NULL')
                ->where('members.deleted_at', null)
                ->where('members.next_due_on <', $today)
                ->orderBy('members.next_due_on')
                ->get()
                ->getResultArray(),
            'upcoming' => service('reservations')->listing(true)
                ->where('reservations.parent_reservation_id', null)
                ->where('reservations.status !=', 'cancelled')
                ->where('reservations.flight_date >=', $today)
                ->orderBy('reservations.flight_date')
                ->limit(5)
                ->get()
                ->getResultArray(),
            'today' => $today,
        ]);
    }
}
