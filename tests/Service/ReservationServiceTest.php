<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Exceptions\DomainRuleViolation;
use Tests\Support\DatabaseTestCase;

final class ReservationServiceTest extends DatabaseTestCase
{
    public function testCompleteCharterChargesWholeAircraftAndTakesEverySeat(): void
    {
        $fleet = $this->fleet();
        $userId = $this->member();

        $id = service('reservations')->book($userId, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'complete');

        $this->assertSame('8100.00', service('points')->balance($userId));
        $this->assertSame(6, $this->db->table('reservation_seats')->where('reservation_id', $id)->countAllResults());
        $this->assertSame('confirmed', $this->row('reservations', ['id' => $id])['status']);
    }

    public function testPartialOwnerPaysForFullAircraftUpFront(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();

        service('reservations')->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1, 2]);

        $this->assertSame('8100.00', service('points')->balance($owner));
    }

    public function testJoinerPaysShareOwnerIsRefundedAndRequestIsPending(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $joiner = $this->member();
        $reservations = service('reservations');

        $ownerId = $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1, 2]);
        $joinId = $reservations->book($joiner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [3, 4]);

        $this->assertSame('10700.00', service('points')->balance($joiner));
        $this->assertSame('9400.00', service('points')->balance($owner));
        $this->assertSame('pending', $this->row('reservations', ['id' => $joinId])['status']);
        $this->assertSame('2600.00', $this->row('reservations', ['id' => $ownerId])['amount']);
    }

    public function testOnlyOwnerOrAdminConfirmsRequests(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $joiner = $this->member();
        $stranger = $this->member();
        $reservations = service('reservations');

        $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1]);
        $joinId = $reservations->book($joiner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [2]);

        try {
            $reservations->confirm($joinId, $stranger, false);
            $this->fail('Stranger must not confirm');
        } catch (DomainRuleViolation) {
        }

        $reservations->confirm($joinId, $owner, false);

        $this->assertSame('confirmed', $this->row('reservations', ['id' => $joinId])['status']);
    }

    public function testSeatCannotBeBookedTwice(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $joiner = $this->member();
        $reservations = service('reservations');

        $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1, 2]);

        $this->expectException(DomainRuleViolation::class);
        $reservations->book($joiner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [2]);
    }

    public function testCompleteIsRefusedOnceAircraftHasBookings(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $other = $this->member();

        service('reservations')->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1]);

        $this->expectException(DomainRuleViolation::class);
        service('reservations')->book($other, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'complete');
    }

    public function testOwnerCannotJoinOwnFlight(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $reservations = service('reservations');

        $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1]);

        $this->expectException(DomainRuleViolation::class);
        $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [2]);
    }

    public function testInsufficientPointsBlocksBookingWithoutSideEffects(): void
    {
        $fleet = $this->fleet(routeCost: '5000');
        $userId = $this->member();

        try {
            service('reservations')->book($userId, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'complete');
            $this->fail('Expected refusal');
        } catch (DomainRuleViolation) {
            $this->assertSame('12000.00', service('points')->balance($userId));
            $this->assertSame(0, $this->db->table('reservations')->countAllResults());
            $this->assertSame(0, $this->db->table('reservation_seats')->countAllResults());
        }
    }

    public function testOwnerCancellationRefundsOwnerAndEveryJoiner(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $joiner = $this->member();
        $reservations = service('reservations');

        $ownerId = $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1, 2]);
        $reservations->book($joiner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [3, 4]);

        $reservations->cancel($ownerId, $owner, false);

        $this->assertSame('12000.00', service('points')->balance($owner));
        $this->assertSame('12000.00', service('points')->balance($joiner));
        $this->assertSame(0, $this->db->table('reservation_seats')->countAllResults());
        $this->assertSame(0, $this->db->table('reservations')->where('status !=', 'cancelled')->countAllResults());
    }

    public function testJoinerCancellationRefundsJoinerAndRechargesOwner(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $joiner = $this->member();
        $reservations = service('reservations');

        $ownerId = $reservations->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1, 2]);
        $joinId = $reservations->book($joiner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [3, 4]);

        $reservations->cancel($joinId, $joiner, false);

        $this->assertSame('12000.00', service('points')->balance($joiner));
        $this->assertSame('8100.00', service('points')->balance($owner));
        $this->assertSame('3900.00', $this->row('reservations', ['id' => $ownerId])['amount']);
        $this->assertSame(2, $this->db->table('reservation_seats')->countAllResults());
    }

    public function testMemberCannotCancelAnotherMembersReservation(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $other = $this->member();
        $id = service('reservations')->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'complete');

        $this->expectException(DomainRuleViolation::class);
        service('reservations')->cancel($id, $other, false);
    }

    public function testCancelledReservationCannotBeCancelledAgain(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $id = service('reservations')->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'complete');
        service('reservations')->cancel($id, $owner, false);

        $this->expectException(DomainRuleViolation::class);
        service('reservations')->cancel($id, $owner, false);
    }

    public function testInactiveMemberPastDatesAndSubMembersCannotBook(): void
    {
        $fleet = $this->fleet();
        $reservations = service('reservations');
        $inactive = $this->member(activate: false);
        $active = $this->member();
        $parent = $this->member(planId: 2);
        $childId = service('members')->addSubMember($parent, ['username' => 'kid2', 'email' => 'kid2@example.test', 'password' => 'Str0ng-Passw0rd-123', 'contact_type_id' => 3, 'first_name' => 'Kid', 'last_name' => 'Two']);

        foreach ([
            [$inactive, $this->tomorrow()],
            [$active, date('Y-m-d', strtotime('-1 day'))],
            [$childId, $this->tomorrow()],
        ] as [$userId, $date]) {
            try {
                $reservations->book($userId, $fleet['route'], $fleet['aircraft'], $date, 'complete');
                $this->fail("Booking should be refused for user {$userId} on {$date}");
            } catch (DomainRuleViolation) {
            }
        }

        $this->assertSame(0, $this->db->table('reservations')->countAllResults());
    }

    public function testRouteMustMatchAircraft(): void
    {
        $fleet = $this->fleet();
        $other = $this->fleet();
        $userId = $this->member();

        $this->expectException(DomainRuleViolation::class);
        service('reservations')->book($userId, $fleet['route'], $other['aircraft'], $this->tomorrow(), 'complete');
    }

    public function testSeatNumbersMustFitAircraft(): void
    {
        $fleet = $this->fleet(capacity: 4);
        $userId = $this->member();

        $this->expectException(DomainRuleViolation::class);
        service('reservations')->book($userId, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [5]);
    }

    public function testListingJoinsFlightDetailsAndOptionallyTheMember(): void
    {
        $fleet = $this->fleet(capacity: 4);
        $userId = $this->member();
        $id = service('reservations')->book($userId, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'complete');

        $plain = service('reservations')->listing()->where('reservations.id', $id)->get()->getResultArray()[0];
        $withMember = service('reservations')->listing(true)->where('reservations.id', $id)->get()->getResultArray()[0];

        $this->assertSame(4, (int) $plain['capacity']);
        $this->assertArrayNotHasKey('member_code', $plain);
        $this->assertArrayHasKey('member_code', $withMember);
        $this->assertSame($plain['from_name'], $withMember['from_name']);
    }
}
