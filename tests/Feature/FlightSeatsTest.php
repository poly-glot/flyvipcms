<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

final class FlightSeatsTest extends DatabaseTestCase
{
    use AuthenticationTesting;
    use FeatureTestTrait;

    public function testReturnsSeatsAlreadyBookedOnTheFlight(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        $viewer = $this->member();
        service('reservations')->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [1, 3]);

        $result = $this->actingAs($this->user($viewer))->get('flights/taken-seats', ['aircraft_id' => (string) $fleet['aircraft'], 'flight_date' => $this->tomorrow()]);

        $result->assertOK();
        $this->assertSame(['taken' => [1, 3]], json_decode((string) $result->getJSON(), true));
    }

    public function testOtherFlightsAndDatesDoNotLeakSeats(): void
    {
        $fleet = $this->fleet();
        $owner = $this->member();
        service('reservations')->book($owner, $fleet['route'], $fleet['aircraft'], $this->tomorrow(), 'partial', [2]);

        $result = $this->actingAs($this->user($owner))->get('flights/taken-seats', ['aircraft_id' => (string) $fleet['aircraft'], 'flight_date' => date('Y-m-d', strtotime('+9 days'))]);

        $this->assertSame(['taken' => []], json_decode((string) $result->getJSON(), true));
    }

    public function testRejectsMalformedQuery(): void
    {
        $viewer = $this->member();

        $this->actingAs($this->user($viewer))->get('flights/taken-seats', ['aircraft_id' => 'x', 'flight_date' => 'tomorrow'])->assertStatus(422);
    }

    public function testAnonymousVisitorsAreSentToLogin(): void
    {
        $this->get('flights/taken-seats', ['aircraft_id' => '1', 'flight_date' => $this->tomorrow()])->assertRedirect();
    }
}
