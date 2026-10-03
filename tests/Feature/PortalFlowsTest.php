<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

final class PortalFlowsTest extends DatabaseTestCase
{
    use AuthenticationTesting;
    use FeatureTestTrait;

    public function testMemberSeesOwnBalanceOnDashboard(): void
    {
        $userId = $this->member();
        $user = $this->user($userId);

        $result = $this->actingAs($user)->get('portal');
        $result->assertOK();
        $result->assertSee('12,000.00');
    }

    public function testMemberBooksAndCancelsFromPortal(): void
    {
        $fleet = $this->fleet();
        $userId = $this->member();
        $user = $this->user($userId);

        $this->actingAs($user)->post('portal/reservations', [
            'route_id' => (string) $fleet['route'],
            'aircraft_id' => (string) $fleet['aircraft'],
            'flight_date' => $this->tomorrow(),
            'kind' => 'complete',
        ]);

        $id = (int) $this->row('reservations', ['user_id' => $userId])['id'];
        $this->assertSame('8100.00', service('points')->balance($userId));

        $this->actingAs($user)->post("portal/reservations/{$id}/cancel", []);

        $this->assertSame('12000.00', service('points')->balance($userId));
    }

    public function testSubMemberSeesParentAccountButCannotBook(): void
    {
        $fleet = $this->fleet();
        $parent = $this->member(planId: 2);
        $childId = service('members')->addSubMember($parent, ['username' => 'kid3', 'email' => 'kid3@example.test', 'password' => 'Str0ng-Passw0rd-123', 'contact_type_id' => 3, 'first_name' => 'Kid', 'last_name' => 'Three']);
        $child = $this->user($childId);

        $result = $this->actingAs($child)->get('portal');
        $result->assertOK();
        $result->assertSee('12,000.00');

        $this->actingAs($child)->post('portal/reservations', [
            'route_id' => (string) $fleet['route'],
            'aircraft_id' => (string) $fleet['aircraft'],
            'flight_date' => $this->tomorrow(),
            'kind' => 'complete',
        ]);

        $this->assertSame(0, $this->db->table('reservations')->countAllResults());
    }

    public function testMemberUpdatesOwnProfileButNotOthers(): void
    {
        $mine = $this->member();
        $other = $this->member();
        $user = $this->user($mine);

        $this->actingAs($user)->post('portal/profile', ['city' => 'Valencia', 'user_id' => (string) $other]);

        $this->assertSame('Valencia', $this->row('profiles', ['user_id' => $mine])['city']);
        $this->assertNotSame('Valencia', $this->row('profiles', ['user_id' => $other])['city']);
    }
}
