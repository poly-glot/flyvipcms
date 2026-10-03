<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Exceptions\DomainRuleViolation;
use Tests\Support\DatabaseTestCase;

final class PaymentServiceTest extends DatabaseTestCase
{
    public function testUnpaidMemberMayOnlyPayJoiningFee(): void
    {
        $userId = $this->member(activate: false);

        $this->assertSame(['joining'], service('payments')->allowedTypes($userId));
    }

    public function testJoiningFeeActivatesMembershipAndSetsDueDate(): void
    {
        $userId = $this->member(activate: false);

        service('payments')->record($userId, 'joining', '2026-11-20', 'wire', $this->admin());
        $member = $this->row('members', ['user_id' => $userId]);

        $this->assertSame('active', $member['status']);
        $this->assertSame('2027-01-05', $member['next_due_on']);
        $this->assertSame('0.00', service('points')->balance($userId));
    }

    public function testYearlyFeeCreditsWholeYearAndCreatesFourQuarters(): void
    {
        $userId = $this->member(planId: 2, yearlyFee: '24000');
        $memberId = (int) $this->row('members', ['user_id' => $userId])['id'];

        $this->assertSame('24000.00', service('points')->balance($userId));
        $this->assertSame(4, $this->db->table('membership_quarters')->join('membership_terms', 'membership_terms.id = membership_quarters.term_id')->where('member_id', $memberId)->countAllResults());
    }

    public function testYearlyIsRefusedWhileATermIsPartlyPaidByQuarters(): void
    {
        $userId = $this->member(fund: false);
        service('payments')->record($userId, 'quarterly', date('Y-m-d'), 'wire', $this->admin());

        $this->assertNotContains('yearly', service('payments')->allowedTypes($userId));

        $this->expectException(DomainRuleViolation::class);
        service('payments')->record($userId, 'yearly', date('Y-m-d'), 'again', $this->admin());
    }

    public function testSecondYearlyPrepaysTheTermStartingAfterThePreviousOne(): void
    {
        $userId = $this->member();
        $payments = service('payments');

        $first = $this->row('membership_terms', [], 'ASC');
        $payments->record($userId, 'yearly', date('Y-m-d'), 'prepay', $this->admin());

        $terms = $this->db->table('membership_terms')->orderBy('id')->get()->getResultArray();

        $this->assertCount(2, $terms);
        $this->assertSame(date('Y-m-d', (int) strtotime($first['ends_on'] . ' +1 day')), $terms[1]['starts_on']);
        $this->assertSame('24000.00', service('points')->balance($userId));
    }

    public function testQuarterlyPaymentCreditsQuarterShareAndExtendsTerm(): void
    {
        $userId = $this->member(yearlyFee: '12000', fund: false);
        $payments = service('payments');

        $payments->record($userId, 'quarterly', date('Y-m-d'), 'wire', $this->admin(), null, 2);

        $this->assertSame('6000.00', service('points')->balance($userId));

        $payments->record($userId, 'quarterly', date('Y-m-d'), 'wire', $this->admin(), null, 2);

        $this->assertSame('12000.00', service('points')->balance($userId));
        $this->assertSame(4, $this->db->table('membership_quarters')->countAllResults());
        $this->assertSame(1, $this->db->table('membership_terms')->countAllResults());
        $this->assertContains('yearly', $payments->allowedTypes($userId));
    }

    public function testFixedAmountsIgnoreWhatTheClientSends(): void
    {
        $userId = $this->member(activate: false);

        service('payments')->record($userId, 'joining', date('Y-m-d'), 'wire', $this->admin(), '1.00');

        $this->assertSame('5000.00', $this->row('payments', ['user_id' => $userId])['amount']);
    }

    public function testBonusRequiresActiveMembershipAndCreditsPoints(): void
    {
        $inactive = $this->member(activate: false);

        $this->assertNotContains('bonus', service('payments')->allowedTypes($inactive));

        $active = $this->member(fund: false);
        service('payments')->record($active, 'bonus', date('Y-m-d'), 'promo', $this->admin(), '250');

        $this->assertSame('250.00', service('points')->balance($active));
    }

    public function testBuyPointsCreditsLedger(): void
    {
        $userId = $this->member(fund: false);

        service('payments')->record($userId, 'points', date('Y-m-d'), 'card', $this->admin(), '800');

        $this->assertSame('800.00', service('points')->balance($userId));
    }

    public function testFreeAmountTypesRejectZero(): void
    {
        $userId = $this->member();

        $this->expectException(DomainRuleViolation::class);
        service('payments')->record($userId, 'points', date('Y-m-d'), 'card', $this->admin(), '0');
    }

    public function testSubMembersCannotBePaidFor(): void
    {
        $parent = $this->member(planId: 2);
        $childId = service('members')->addSubMember($parent, ['username' => 'kid1', 'email' => 'kid1@example.test', 'password' => 'Str0ng-Passw0rd-123', 'contact_type_id' => 3, 'first_name' => 'Kid', 'last_name' => 'One']);

        $this->expectException(DomainRuleViolation::class);
        service('payments')->record($childId, 'points', date('Y-m-d'), 'card', $this->admin(), '10');
    }
}
