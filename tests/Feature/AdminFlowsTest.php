<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

final class AdminFlowsTest extends DatabaseTestCase
{
    use AuthenticationTesting;
    use FeatureTestTrait;

    public function testAdminCreatesAndDeletesFleetRecords(): void
    {
        $admin = $this->login('admin');

        $this->actingAs($admin)->post('admin/aircraft-types', ['name' => 'Heavy jet'])->assertRedirectTo(site_url('admin/aircraft-types'));
        $this->seeInDatabase('aircraft_types', ['name' => 'Heavy jet']);

        $id = (int) $this->row('aircraft_types', ['name' => 'Heavy jet'])['id'];
        $this->actingAs($admin)->post("admin/aircraft-types/{$id}/delete", [])->assertRedirect();
        $this->assertNotNull($this->row('aircraft_types', ['id' => $id])['deleted_at']);
    }

    public function testAirportTotalCostIsComputedServerSide(): void
    {
        $this->actingAs($this->login('admin'))->post('admin/airports', [
            'name' => 'Costly',
            'travel_allowance' => '10',
            'take_off_cost' => '20',
            'landing_cost' => '30',
            'stay_cost' => '40',
            'airport_cost' => '50',
            'handling_cost' => '60',
            'total_cost' => '1',
        ]);

        $this->assertSame('210.00', $this->row('airports', ['name' => 'Costly'])['total_cost']);
    }

    public function testRouteRejectsSameAirportOnBothEnds(): void
    {
        $this->fleet();
        $airportId = (int) $this->row('airports')['id'];

        $this->actingAs($this->login('admin'))->post('admin/routes', ['from_airport_id' => $airportId, 'to_airport_id' => $airportId, 'cost' => '10']);

        $this->assertSame(1, $this->db->table('air_routes')->countAllResults());
    }

    public function testAdminCreatesMemberThenRecordsJoiningFee(): void
    {
        $admin = $this->login('admin');

        $this->actingAs($admin)->post('admin/members', [
            'username' => 'newbie',
            'email' => 'newbie@example.test',
            'password' => 'Str0ng-Passw0rd-123',
            'first_name' => 'New',
            'last_name' => 'Bie',
            'plan_id' => '1',
            'joining_fee' => '5000',
            'yearly_fee' => '12000',
        ]);

        $newbie = $this->row('users', ['username' => 'newbie']);
        $member = $this->row('members', ['user_id' => $newbie['id']]);
        $this->assertSame('inactive', $member['status']);

        $this->actingAs($admin)->post('admin/payments', [
            'user_id' => (string) $member['user_id'],
            'type' => 'joining',
            'paid_on' => date('Y-m-d'),
            'remark' => 'cash',
        ]);

        $this->assertSame('active', $this->row('members', ['user_id' => $member['user_id']])['status']);
    }

    public function testMemberCreationRejectsWeakAndDuplicateInput(): void
    {
        $admin = $this->login('admin');
        $existing = $this->login('member');

        $this->actingAs($admin)->post('admin/members', [
            'username' => 'dupe',
            'email' => $existing->email,
            'password' => 'short',
            'first_name' => 'D',
            'last_name' => 'Upe',
            'plan_id' => '1',
            'joining_fee' => '1',
            'yearly_fee' => '1',
        ]);

        $this->assertSame(0, $this->db->table('users')->where('username', 'dupe')->countAllResults());
    }

    public function testAdminBooksReservationForMember(): void
    {
        $fleet = $this->fleet();
        $userId = $this->member();

        $this->actingAs($this->login('admin'))->post('admin/reservations', [
            'user_id' => (string) $userId,
            'route_id' => (string) $fleet['route'],
            'aircraft_id' => (string) $fleet['aircraft'],
            'flight_date' => $this->tomorrow(),
            'kind' => 'complete',
        ]);

        $this->assertSame(1, $this->db->table('reservations')->where('user_id', $userId)->countAllResults());
        $this->assertSame('8100.00', service('points')->balance($userId));
    }
}
