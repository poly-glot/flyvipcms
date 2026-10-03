<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\DomainRuleViolation;
use CodeIgniter\HTTP\RedirectResponse;

class Points extends BaseController
{
    public function index(): string
    {
        return view('admin/points/index', [
            'entries' => db_connect()->table('points_ledger')
                ->select('points_ledger.*, members.member_code, profiles.first_name, profiles.last_name')
                ->join('members', 'members.user_id = points_ledger.user_id')
                ->join('profiles', 'profiles.user_id = points_ledger.user_id')
                ->orderBy('points_ledger.id', 'DESC')
                ->limit(300)
                ->get()
                ->getResultArray(),
        ]);
    }

    public function adjust(): string
    {
        return view('admin/points/adjust', ['members' => $this->members()]);
    }

    public function storeAdjustment(): RedirectResponse
    {
        if (!$this->validate(['user_id' => 'required|is_natural_no_zero', 'amount' => 'required|decimal|greater_than[0]', 'remark' => 'required|max_length[255]'])) {
            return $this->backWithValidationErrors();
        }

        service('points')->credit(
            (int) $this->request->getPost('user_id'),
            (string) $this->request->getPost('amount'),
            'adjustment',
            'Points added by admin: ' . $this->request->getPost('remark'),
        );

        return $this->back('admin/points', 'success', 'Points added.');
    }

    public function transfer(): string
    {
        return view('admin/points/transfer', ['members' => $this->members()]);
    }

    public function storeTransfer(): RedirectResponse
    {
        if (!$this->validate(['sender_id' => 'required|is_natural_no_zero', 'receiver_id' => 'required|is_natural_no_zero|differs[sender_id]', 'amount' => 'required|decimal|greater_than[0]'])) {
            return $this->backWithValidationErrors();
        }

        try {
            service('points')->transfer(
                (int) $this->request->getPost('sender_id'),
                (int) $this->request->getPost('receiver_id'),
                (string) $this->request->getPost('amount'),
                (int) auth()->id(),
            );
        } catch (DomainRuleViolation $error) {
            return $this->backWithErrors(['transfer' => $error->getMessage()]);
        }

        return $this->back('admin/points', 'success', 'Points transferred.');
    }

    private function members(): array
    {
        return $this->memberOptions(service('members')->primaryMembers(), true);
    }
}
