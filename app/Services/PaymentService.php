<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Money;
use App\Domain\QuarterCalendar;
use App\Exceptions\DomainRuleViolation;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

final readonly class PaymentService
{
    use Atomic;

    public const array FREE_AMOUNT_TYPES = ['bonus', 'points'];

    public function __construct(
        private BaseConnection $db,
        private PointsService $points,
    ) {
    }

    public function allowedTypes(int $userId): array
    {
        $member = $this->primaryMember($userId);

        if ($member['joining_fee_paid_at'] === null) {
            return ['joining'];
        }

        $types = ['quarterly', 'points'];

        if ($this->canStartYearlyTerm((int) $member['id'])) {
            array_unshift($types, 'yearly');
        }

        if ($member['status'] === 'active') {
            $types[] = 'bonus';
        }

        return $types;
    }

    public function expectedAmount(int $userId, string $type, int $quarters = 1): ?string
    {
        $member = $this->primaryMember($userId);

        return match ($type) {
            'joining' => Money::normalise($member['joining_fee']),
            'yearly' => Money::normalise($member['yearly_fee']),
            'quarterly' => Money::mul(Money::quarter(Money::normalise($member['yearly_fee'])), $quarters),
            default => null,
        };
    }

    public function record(int $userId, string $type, string $paidOn, string $remark, int $adminId, ?string $freeAmount = null, int $quarters = 1, ?string $termStart = null): int
    {
        return $this->atomic(function () use ($userId, $type, $paidOn, $remark, $adminId, $freeAmount, $quarters, $termStart): int {
            if (!in_array($type, $this->allowedTypes($userId), true)) {
                throw new DomainRuleViolation("A {$type} payment is not allowed for this member right now.");
            }

            $member = $this->primaryMember($userId);
            $paidDate = new DateTimeImmutable($paidOn);
            $amount = $this->expectedAmount($userId, $type, $quarters) ?? Money::normalise($freeAmount);

            if (!Money::isPositive($amount)) {
                throw new DomainRuleViolation('Payment amount must be greater than zero.');
            }

            $paymentId = $this->insertPayment($userId, $type, $amount, $paidDate, $remark, $adminId);

            match ($type) {
                'joining' => $this->applyJoining($member, $paidDate),
                'yearly' => $this->applyYearly($member, $paymentId, $paidDate, $termStart),
                'quarterly' => $this->applyQuarterly($member, $paymentId, $paidDate, $quarters),
                'bonus' => $this->points->credit($userId, $amount, 'payment', 'Bonus points', ['payment_id' => $paymentId]),
                'points' => $this->points->credit($userId, $amount, 'payment', 'Points purchased', ['payment_id' => $paymentId]),
                default => throw new DomainRuleViolation("Unknown payment type {$type}."),
            };

            return $paymentId;
        });
    }

    public function termStartOptions(DateTimeImmutable $today): array
    {
        $current = QuarterCalendar::quarterStart($today);

        return [$current->format('Y-m-d'), $current->modify('+3 months')->format('Y-m-d')];
    }

    private function applyJoining(array $member, DateTimeImmutable $paidOn): void
    {
        $this->db->table('members')->where('id', $member['id'])->update([
            'joining_fee_paid_at' => $paidOn->format('Y-m-d H:i:s'),
            'status' => 'active',
            'next_due_on' => QuarterCalendar::firstDueAfterJoining($paidOn)->format('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function applyYearly(array $member, int $paymentId, DateTimeImmutable $paidOn, ?string $termStart): void
    {
        $startDate = $this->nextTermStart((int) $member['id'], $paidOn, $termStart);
        $start = $startDate->format('Y-m-d');
        $termId = $this->insertTerm((int) $member['id'], $startDate);
        $yearly = Money::normalise($member['yearly_fee']);

        foreach ([1, 2, 3, 4] as $quarter) {
            $this->insertQuarter($termId, $quarter, $startDate, $paymentId, $quarter === 1 ? $yearly : '0.00');
        }

        $this->points->credit((int) $member['user_id'], $yearly, 'payment', "Yearly fee {$start}", ['payment_id' => $paymentId]);
        $this->setNextDue((int) $member['id'], QuarterCalendar::termEnd($startDate));
    }

    private function nextTermStart(int $memberId, DateTimeImmutable $paidOn, ?string $requested): DateTimeImmutable
    {
        $last = $this->latestTerm($memberId);

        if ($last !== null) {
            return new DateTimeImmutable($last['ends_on'])->modify('+1 day');
        }

        $options = $this->termStartOptions($paidOn);
        $start = $requested ?? $options[0];

        if (!in_array($start, $options, true)) {
            throw new DomainRuleViolation('Choose the current or the next quarter as the term start.');
        }

        return new DateTimeImmutable($start);
    }

    private function applyQuarterly(array $member, int $paymentId, DateTimeImmutable $paidOn, int $quarters): void
    {
        if ($quarters < 1 || $quarters > 4) {
            throw new DomainRuleViolation('Pay between one and four quarters at a time.');
        }

        $perQuarter = Money::quarter(Money::normalise($member['yearly_fee']));
        $lastEnd = $paidOn;

        for ($i = 0; $i < $quarters; ++$i) {
            [$termId, $termStart, $quarter] = $this->nextOpenQuarter((int) $member['id'], $paidOn);
            $this->insertQuarter($termId, $quarter, $termStart, $paymentId, $perQuarter);
            $lastEnd = QuarterCalendar::quarterEnd(QuarterCalendar::quarterStartInTerm($termStart, $quarter));
        }

        $this->points->credit((int) $member['user_id'], Money::mul($perQuarter, $quarters), 'payment', "Quarterly fee x{$quarters}", ['payment_id' => $paymentId]);
        $this->setNextDue((int) $member['id'], $lastEnd);
    }

    private function nextOpenQuarter(int $memberId, DateTimeImmutable $paidOn): array
    {
        $term = $this->latestTerm($memberId);

        if ($term !== null) {
            $paid = $this->paidQuarters((int) $term['id']);

            if ($paid < 4) {
                return [(int) $term['id'], new DateTimeImmutable($term['starts_on']), $paid + 1];
            }

            $start = new DateTimeImmutable($term['ends_on'])->modify('+1 day');
        } else {
            $start = QuarterCalendar::nextQuarterStart($paidOn);
        }

        return [$this->insertTerm($memberId, $start), $start, 1];
    }

    private function canStartYearlyTerm(int $memberId): bool
    {
        $term = $this->latestTerm($memberId);

        return $term === null || $this->paidQuarters((int) $term['id']) >= 4;
    }

    private function latestTerm(int $memberId): ?array
    {
        return $this->db->table('membership_terms')->where('member_id', $memberId)->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
    }

    private function paidQuarters(int $termId): int
    {
        return (int) $this->db->table('membership_quarters')->where('term_id', $termId)->countAllResults();
    }

    private function insertPayment(int $userId, string $type, string $amount, DateTimeImmutable $paidOn, string $remark, int $adminId): int
    {
        $this->db->table('payments')->insert([
            'user_id' => $userId,
            'type' => $type,
            'description' => ucfirst($type) . ' payment',
            'amount' => $amount,
            'paid_on' => $paidOn->format('Y-m-d'),
            'remark' => $remark,
            'created_by' => $adminId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    private function insertTerm(int $memberId, DateTimeImmutable $start): int
    {
        $this->db->table('membership_terms')->insert([
            'member_id' => $memberId,
            'starts_on' => $start->format('Y-m-d'),
            'ends_on' => QuarterCalendar::termEnd($start)->format('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    private function insertQuarter(int $termId, int $quarter, DateTimeImmutable $termStart, int $paymentId, string $points): void
    {
        $start = QuarterCalendar::quarterStartInTerm($termStart, $quarter);

        $this->db->table('membership_quarters')->insert([
            'term_id' => $termId,
            'payment_id' => $paymentId,
            'quarter' => $quarter,
            'starts_on' => $start->format('Y-m-d'),
            'ends_on' => QuarterCalendar::quarterEnd($start)->format('Y-m-d'),
            'points_loaded' => $points,
        ]);
    }

    private function setNextDue(int $memberId, DateTimeImmutable $periodEnd): void
    {
        $this->db->table('members')->where('id', $memberId)->update([
            'next_due_on' => QuarterCalendar::dueAfter($periodEnd)->format('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function primaryMember(int $userId): array
    {
        $member = $this->db->table('members')->where('user_id', $userId)->where('plan_id IS NOT NULL')->where('deleted_at', null)->get()->getRowArray();

        return $member ?? throw new DomainRuleViolation('Payments can only be recorded for primary members.');
    }
}
