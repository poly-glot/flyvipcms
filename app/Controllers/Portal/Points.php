<?php

declare(strict_types=1);

namespace App\Controllers\Portal;

class Points extends PortalController
{
    public function index(): string
    {
        $accountId = $this->accountUserId($this->member());

        return view('portal/points', [
            'balance' => service('points')->balance($accountId),
            'entries' => db_connect()->table('points_ledger')->where('user_id', $accountId)->orderBy('id', 'DESC')->limit(100)->get()->getResultArray(),
        ]);
    }
}
