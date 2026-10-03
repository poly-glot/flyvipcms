<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\DomainRuleViolation;
use App\Services\PaymentService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use DateTimeImmutable;

class Payments extends BaseController
{
    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $query = db_connect()->table('payments')
            ->select('payments.*, members.member_code, profiles.first_name, profiles.last_name')
            ->join('members', 'members.user_id = payments.user_id')
            ->join('profiles', 'profiles.user_id = payments.user_id')
            ->where('payments.deleted_at', null)
            ->orderBy('payments.id', 'DESC');

        if ($search !== '') {
            $query->groupStart()
                ->like('members.member_code', $search)
                ->orLike('profiles.first_name', $search)
                ->orLike('profiles.last_name', $search)
                ->orLike('payments.remark', $search)
                ->orLike('payments.id', $search)
                ->groupEnd();
        }

        return view('admin/payments/index', ['payments' => $query->get()->getResultArray(), 'search' => $search]);
    }

    public function new(): string
    {
        $selected = (int) ($this->request->getGet('user_id') ?? 0);
        $payments = service('payments');
        $today = new DateTimeImmutable('today');

        return view('admin/payments/form', [
            'members' => $this->primaryMembers(),
            'selected' => $selected,
            'allowed' => $selected > 0 ? $payments->allowedTypes($selected) : [],
            'expected' => $selected > 0 ? $this->expectedAmounts($payments, $selected) : [],
            'termStarts' => $payments->termStartOptions($today),
        ]);
    }

    public function create(): RedirectResponse
    {
        $rules = [
            'user_id' => 'required|is_natural_no_zero',
            'type' => 'required|in_list[joining,yearly,quarterly,bonus,points]',
            'paid_on' => 'required|valid_date[Y-m-d]',
            'remark' => 'required|max_length[255]',
            'amount' => 'permit_empty|decimal|greater_than[0]',
            'quarters' => 'permit_empty|in_list[1,2,3,4]',
            'term_start' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithErrors($this->validator?->getErrors() ?? []);
        }

        $userId = (int) $this->request->getPost('user_id');
        $type = (string) $this->request->getPost('type');

        if (in_array($type, PaymentService::FREE_AMOUNT_TYPES, true) && (string) $this->request->getPost('amount') === '') {
            return $this->backWithErrors(['amount' => 'Amount is required for this payment type.']);
        }

        try {
            $id = service('payments')->record(
                $userId,
                $type,
                (string) $this->request->getPost('paid_on'),
                (string) $this->request->getPost('remark'),
                (int) auth()->id(),
                (string) $this->request->getPost('amount') ?: null,
                (int) ($this->request->getPost('quarters') ?: 1),
                (string) $this->request->getPost('term_start') ?: null,
            );
        } catch (DomainRuleViolation $error) {
            return $this->backWithErrors(['payment' => $error->getMessage()]);
        }

        return $this->back("admin/payments/{$id}", 'success', 'Payment recorded.');
    }

    public function show(int $id): string
    {
        $payment = db_connect()->table('payments')
            ->select('payments.*, members.member_code, profiles.first_name, profiles.last_name')
            ->join('members', 'members.user_id = payments.user_id')
            ->join('profiles', 'profiles.user_id = payments.user_id')
            ->where('payments.id', $id)
            ->get()
            ->getRowArray();

        return view('admin/payments/show', ['payment' => $payment ?? throw PageNotFoundException::forPageNotFound()]);
    }

    private function primaryMembers(): array
    {
        $rows = db_connect()->table('members')
            ->select('members.user_id, members.member_code, profiles.first_name, profiles.last_name')
            ->join('profiles', 'profiles.user_id = members.user_id')
            ->where('members.plan_id IS NOT NULL')
            ->where('members.deleted_at', null)
            ->orderBy('profiles.last_name')
            ->get()
            ->getResultArray();

        return array_column(array_map(
            static fn (array $row): array => ['id' => $row['user_id'], 'label' => "{$row['first_name']} {$row['last_name']} [{$row['member_code']}]"],
            $rows,
        ), 'label', 'id');
    }

    private function expectedAmounts(PaymentService $payments, int $userId): array
    {
        return [
            'joining' => $payments->expectedAmount($userId, 'joining'),
            'yearly' => $payments->expectedAmount($userId, 'yearly'),
            'quarterly' => $payments->expectedAmount($userId, 'quarterly'),
        ];
    }
}
