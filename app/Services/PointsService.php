<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Money;
use App\Exceptions\DomainRuleViolation;
use CodeIgniter\Database\BaseConnection;

final readonly class PointsService
{
    use Atomic;

    public function __construct(private BaseConnection $db)
    {
    }

    public function balance(int $userId): string
    {
        $row = $this->db->table('points_ledger')
            ->select('balance_after')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return Money::normalise($row['balance_after'] ?? '0');
    }

    public function credit(int $userId, string $amount, string $kind, string $description, array $references = []): string
    {
        return $this->post($userId, Money::normalise($amount), $kind, $description, $references, false);
    }

    public function debit(int $userId, string $amount, string $kind, string $description, array $references = [], bool $allowOverdraft = false): string
    {
        return $this->post($userId, Money::negate(Money::normalise($amount)), $kind, $description, $references, $allowOverdraft);
    }

    public function transfer(int $senderId, int $receiverId, string $amount, int $adminId): int
    {
        if ($senderId === $receiverId) {
            throw new DomainRuleViolation('Sender and receiver must be different members.');
        }

        if (!Money::isPositive($amount)) {
            throw new DomainRuleViolation('Transfer amount must be greater than zero.');
        }

        return $this->atomic(function () use ($senderId, $receiverId, $amount, $adminId): int {
            $this->db->table('points_transfers')->insert([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $amount,
                'created_by' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $transferId = (int) $this->db->insertID();

            $this->debit($senderId, $amount, 'transfer_out', "Points transferred to member #{$receiverId} (transfer #{$transferId})", ['transfer_id' => $transferId]);
            $this->credit($receiverId, $amount, 'transfer_in', "Points received from member #{$senderId} (transfer #{$transferId})", ['transfer_id' => $transferId]);

            return $transferId;
        });
    }

    private function post(int $userId, string $signedAmount, string $kind, string $description, array $references, bool $allowOverdraft): string
    {
        return $this->atomic(function () use ($userId, $signedAmount, $kind, $description, $references, $allowOverdraft): string {
            $this->db->query('SELECT id FROM users WHERE id = ? FOR UPDATE', [$userId]);

            $balanceAfter = Money::add($this->balance($userId), $signedAmount);

            if (!$allowOverdraft && Money::compare($balanceAfter, '0') < 0) {
                throw new DomainRuleViolation('Not enough points for this operation.');
            }

            $this->db->table('points_ledger')->insert([
                'user_id' => $userId,
                'amount' => $signedAmount,
                'balance_after' => $balanceAfter,
                'kind' => $kind,
                'description' => $description,
                'payment_id' => $references['payment_id'] ?? null,
                'transfer_id' => $references['transfer_id'] ?? null,
                'reservation_id' => $references['reservation_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return $balanceAfter;
        });
    }
}
