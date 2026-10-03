<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\DomainRuleViolation;
use App\Services\MemberService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Exceptions\ValidationException;

class Members extends BaseController
{
    public function index(): string
    {
        return view('admin/members/index', [
            'members' => db_connect()->table('members')
                ->select('members.*, profiles.first_name, profiles.last_name, users.username, auth_identities.secret AS email, membership_plans.name AS plan_name')
                ->join('profiles', 'profiles.user_id = members.user_id')
                ->join('users', 'users.id = members.user_id')
                ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'")
                ->join('membership_plans', 'membership_plans.id = members.plan_id')
                ->where('members.deleted_at', null)
                ->orderBy('members.id', 'DESC')
                ->get()
                ->getResultArray(),
            'balances' => $this->balances(),
        ]);
    }

    public function new(): string
    {
        return view('admin/members/form', $this->formData([]) + ['action' => 'admin/members', 'member' => null]);
    }

    public function create(): RedirectResponse
    {
        $rules = MemberService::PROFILE_RULES + $this->accountRules() + [
            'plan_id' => 'required|is_natural_no_zero',
            'joining_fee' => 'required|decimal|greater_than_equal_to[0]',
            'yearly_fee' => 'required|decimal|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithValidationErrors();
        }

        try {
            $userId = service('members')->create(
                $this->request->getPost(['username', 'email', 'password']),
                $this->request->getPost(MemberService::PROFILE_FIELDS),
                (int) $this->request->getPost('plan_id'),
                (string) $this->request->getPost('joining_fee'),
                (string) $this->request->getPost('yearly_fee'),
            );
        } catch (DomainRuleViolation|ValidationException $error) {
            return $this->backWithErrors(['member' => $error->getMessage()]);
        }

        return $this->back("admin/members/{$userId}", 'success', 'Member created. Record the joining fee to activate the membership.');
    }

    public function show(int $userId): string
    {
        $member = $this->member($userId);
        $db = db_connect();

        return view('admin/members/show', [
            'member' => $member,
            'children' => $db->table('members')
                ->select('members.*, profiles.first_name, profiles.last_name, users.username, contact_types.name AS contact_type')
                ->join('profiles', 'profiles.user_id = members.user_id')
                ->join('users', 'users.id = members.user_id')
                ->join('contact_types', 'contact_types.id = members.contact_type_id')
                ->where('members.parent_user_id', $userId)
                ->where('members.deleted_at', null)
                ->get()->getResultArray(),
            'payments' => $db->table('payments')->where('user_id', $userId)->where('deleted_at', null)->orderBy('id', 'DESC')->get()->getResultArray(),
            'ledger' => $db->table('points_ledger')->where('user_id', $userId)->orderBy('id', 'DESC')->limit(25)->get()->getResultArray(),
            'reservations' => service('reservations')->listing()
                ->where('reservations.user_id', $userId)
                ->orderBy('reservations.flight_date', 'DESC')
                ->get()
                ->getResultArray(),
            'balance' => service('points')->balance($userId),
            'contactTypes' => array_column($db->table('contact_types')->get()->getResultArray(), 'name', 'id'),
        ]);
    }

    public function edit(int $userId): string
    {
        $member = $this->member($userId);

        return view('admin/members/form', $this->formData($member) + ['action' => "admin/members/{$userId}", 'member' => $member]);
    }

    public function update(int $userId): RedirectResponse
    {
        $this->member($userId);
        $rules = MemberService::PROFILE_RULES + [
            'username' => "required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username,id,{$userId}]",
            'email' => "required|valid_email|is_unique[auth_identities.secret,user_id,{$userId}]",
            'password' => 'permit_empty|min_length[8]|max_byte[72]',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithValidationErrors();
        }

        try {
            service('members')->update($userId, $this->request->getPost(['username', 'email', 'password']), $this->request->getPost(MemberService::PROFILE_FIELDS));
        } catch (DomainRuleViolation|ValidationException $error) {
            return $this->backWithErrors(['member' => $error->getMessage()]);
        }

        return $this->back("admin/members/{$userId}", 'success', 'Member updated.');
    }

    public function status(int $userId): RedirectResponse
    {
        $this->member($userId);

        if (!$this->validate(['status' => 'required|in_list[inactive,active,banned]'])) {
            return $this->backWithValidationErrors();
        }

        service('members')->setStatus($userId, (string) $this->request->getPost('status'));

        return $this->back("admin/members/{$userId}", 'success', 'Membership status updated.');
    }

    public function addSubMember(int $parentId): RedirectResponse
    {
        $this->member($parentId);
        $rules = MemberService::PROFILE_RULES + $this->accountRules() + ['contact_type_id' => 'required|is_natural_no_zero'];

        if (!$this->validate($rules)) {
            return $this->backWithValidationErrors();
        }

        try {
            service('members')->addSubMember($parentId, $this->request->getPost(['username', 'email', 'password', 'contact_type_id', ...MemberService::PROFILE_FIELDS]));
        } catch (DomainRuleViolation|ValidationException $error) {
            return $this->backWithErrors(['member' => $error->getMessage()]);
        }

        return $this->back("admin/members/{$parentId}", 'success', 'Sub-member added.');
    }

    public function delete(int $userId): RedirectResponse
    {
        $this->member($userId);
        service('members')->remove($userId);

        return $this->back('admin/members', 'success', 'Member and sub-members removed.');
    }

    private function accountRules(): array
    {
        return [
            'username' => 'required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]|max_byte[72]',
        ];
    }

    private function member(int $userId): array
    {
        $member = db_connect()->table('members')
            ->select('members.*, profiles.*, users.username, auth_identities.secret AS email, membership_plans.name AS plan_name')
            ->join('profiles', 'profiles.user_id = members.user_id')
            ->join('users', 'users.id = members.user_id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'")
            ->join('membership_plans', 'membership_plans.id = members.plan_id', 'left')
            ->where('members.user_id', $userId)
            ->where('members.deleted_at', null)
            ->get()
            ->getRowArray();

        return $member ?? throw PageNotFoundException::forPageNotFound();
    }

    private function formData(array $member): array
    {
        return service('members')->profileOptions() + [
            'plans' => array_column(db_connect()->table('membership_plans')->get()->getResultArray(), 'name', 'id'),
            'values' => $member,
        ];
    }

    private function balances(): array
    {
        $db = db_connect();
        $latest = array_column($db->table('points_ledger')->select('MAX(id) AS id')->groupBy('user_id')->get()->getResultArray(), 'id');

        if ($latest === []) {
            return [];
        }

        return array_column($db->table('points_ledger')->select('user_id, balance_after')->whereIn('id', $latest)->get()->getResultArray(), 'balance_after', 'user_id');
    }
}
