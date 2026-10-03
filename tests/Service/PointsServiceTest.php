<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Exceptions\DomainRuleViolation;
use Tests\Support\DatabaseTestCase;

final class PointsServiceTest extends DatabaseTestCase
{
    public function testBalanceIsLastLedgerEntry(): void
    {
        $userId = $this->member(activate: false);
        $points = service('points');

        $this->assertSame('0.00', $points->balance($userId));

        $points->credit($userId, '100', 'adjustment', 'seed');
        $points->debit($userId, '30.50', 'booking', 'spend');

        $this->assertSame('69.50', $points->balance($userId));
    }

    public function testDebitBeyondBalanceIsRefusedAndLeavesNoEntry(): void
    {
        $userId = $this->member(activate: false);
        $points = service('points');
        $points->credit($userId, '10', 'adjustment', 'seed');

        try {
            $points->debit($userId, '10.01', 'booking', 'too much');
            $this->fail('Expected overdraft refusal');
        } catch (DomainRuleViolation) {
            $this->assertSame('10.00', $points->balance($userId));
            $this->assertSame(1, $this->db->table('points_ledger')->where('user_id', $userId)->countAllResults());
        }
    }

    public function testForcedDebitMayOverdraw(): void
    {
        $userId = $this->member(activate: false);

        $this->assertSame('-5.00', service('points')->debit($userId, '5', 'booking', 'forced', [], true));
    }

    public function testTransferMovesPointsBetweenMembers(): void
    {
        $sender = $this->member();
        $receiver = $this->member(activate: false);
        $points = service('points');

        $points->transfer($sender, $receiver, '2500', $this->admin());

        $this->assertSame('9500.00', $points->balance($sender));
        $this->assertSame('2500.00', $points->balance($receiver));
    }

    public function testTransferRejectsSameMemberAndOverdraft(): void
    {
        $sender = $this->member();
        $points = service('points');

        $this->expectException(DomainRuleViolation::class);
        $points->transfer($sender, $sender, '1', $this->admin());
    }

    public function testTransferBeyondBalanceChangesNothing(): void
    {
        $sender = $this->member();
        $receiver = $this->member(activate: false);
        $points = service('points');

        try {
            $points->transfer($sender, $receiver, '99999', $this->admin());
            $this->fail('Expected overdraft refusal');
        } catch (DomainRuleViolation) {
            $this->assertSame('12000.00', $points->balance($sender));
            $this->assertSame('0.00', $points->balance($receiver));
        }
    }
}
