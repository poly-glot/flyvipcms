<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\FlightPricing;
use App\Domain\Money;
use App\Exceptions\DomainRuleViolation;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

final readonly class ReservationService
{
    use Atomic;

    public function __construct(
        private BaseConnection $db,
        private PointsService $points,
    ) {
    }

    public function book(int $userId, int $routeId, int $aircraftId, string $flightDate, string $kind, array $seats = []): int
    {
        return $this->atomic(function () use ($userId, $routeId, $aircraftId, $flightDate, $kind, $seats): int {
            $this->assertBookable($userId, $flightDate);

            $route = $this->row('air_routes', $routeId, 'Route not found.');
            $aircraft = $this->row('aircrafts', $aircraftId, 'Aircraft not found.');

            if ($route['aircraft_id'] !== null && (int) $route['aircraft_id'] !== $aircraftId) {
                throw new DomainRuleViolation('This route is not operated by the selected aircraft.');
            }

            $capacity = (int) $aircraft['passenger_capacity'];
            $seatPrice = FlightPricing::seatPrice(Money::normalise($route['cost']), Money::normalise($aircraft['total_cost']));
            $owner = $this->flightOwner($aircraftId, $flightDate);

            if ($kind === 'complete') {
                return $this->bookComplete($userId, $route, $aircraft, $flightDate, $capacity, $seatPrice, $owner);
            }

            $seats = $this->validSeats($seats, $capacity);

            return $owner === null
                ? $this->bookPartialOwner($userId, $route, $aircraft, $flightDate, $capacity, $seatPrice, $seats)
                : $this->joinPartial($userId, $route, $aircraft, $flightDate, $seatPrice, $seats, $owner);
        });
    }

    public function confirm(int $reservationId, int $actorId, bool $actorIsAdmin): void
    {
        $this->atomic(function () use ($reservationId, $actorId, $actorIsAdmin): void {
            $reservation = $this->row('reservations', $reservationId, 'Reservation not found.');

            if ($reservation['status'] !== 'pending' || $reservation['parent_reservation_id'] === null) {
                throw new DomainRuleViolation('Only pending seat requests can be confirmed.');
            }

            $owner = $this->row('reservations', (int) $reservation['parent_reservation_id'], 'Flight owner not found.');

            if (!$actorIsAdmin && (int) $owner['user_id'] !== $actorId) {
                throw new DomainRuleViolation('Only the flight owner can confirm seat requests.');
            }

            $this->db->table('reservations')->where('id', $reservationId)->update(['status' => 'confirmed', 'updated_at' => date('Y-m-d H:i:s')]);
            $this->activity((int) $owner['user_id'], $reservationId, 'You confirmed a seat request on your flight');
            $this->activity((int) $reservation['user_id'], $reservationId, 'Your seat request was confirmed');
        });
    }

    public function cancel(int $reservationId, int $actorId, bool $actorIsAdmin): void
    {
        $this->atomic(function () use ($reservationId, $actorId, $actorIsAdmin): void {
            $reservation = $this->row('reservations', $reservationId, 'Reservation not found.');

            if ($reservation['status'] === 'cancelled') {
                throw new DomainRuleViolation('This reservation is already cancelled.');
            }

            if (!$actorIsAdmin && (int) $reservation['user_id'] !== $actorId) {
                throw new DomainRuleViolation('You can only cancel your own reservations.');
            }

            if (!$actorIsAdmin && $reservation['flight_date'] < date('Y-m-d')) {
                throw new DomainRuleViolation('Past flights cannot be cancelled.');
            }

            $reservation['parent_reservation_id'] === null
                ? $this->cancelOwner($reservation)
                : $this->cancelJoiner($reservation);
        });
    }

    public function listing(bool $withMember = false): BaseBuilder
    {
        $query = $this->db->table('reservations')
            ->select('reservations.*, aircrafts.name AS aircraft_name, aircrafts.passenger_capacity AS capacity, from_airport.name AS from_name, to_airport.name AS to_name')
            ->join('aircrafts', 'aircrafts.id = reservations.aircraft_id')
            ->join('air_routes', 'air_routes.id = reservations.route_id')
            ->join('airports AS from_airport', 'from_airport.id = air_routes.from_airport_id')
            ->join('airports AS to_airport', 'to_airport.id = air_routes.to_airport_id');

        if ($withMember) {
            $query->select('members.member_code, profiles.first_name, profiles.last_name')
                ->join('members', 'members.user_id = reservations.user_id')
                ->join('profiles', 'profiles.user_id = reservations.user_id');
        }

        return $query;
    }

    private function bookComplete(int $userId, array $route, array $aircraft, string $date, int $capacity, string $seatPrice, ?array $owner): int
    {
        if ($owner !== null || $this->takenSeatCount((int) $aircraft['id'], $date) > 0) {
            throw new DomainRuleViolation('This aircraft already has bookings on that date.');
        }

        $charge = FlightPricing::charge($seatPrice, $capacity);
        $reservationId = $this->insertReservation($userId, null, $route, $aircraft, $date, 'complete', $capacity, $charge, 'confirmed');

        $this->insertSeats($reservationId, (int) $aircraft['id'], $date, range(1, $capacity));
        $this->points->debit($userId, $charge, 'booking', "Complete charter reservation #{$reservationId}", ['reservation_id' => $reservationId]);
        $this->activity($userId, $reservationId, "You booked a complete charter on {$date}");

        return $reservationId;
    }

    private function bookPartialOwner(int $userId, array $route, array $aircraft, string $date, int $capacity, string $seatPrice, array $seats): int
    {
        $charge = FlightPricing::charge($seatPrice, $capacity);
        $reservationId = $this->insertReservation($userId, null, $route, $aircraft, $date, 'partial', count($seats), $charge, 'confirmed');

        $this->insertSeats($reservationId, (int) $aircraft['id'], $date, $seats);
        $this->points->debit($userId, $charge, 'booking', "Shared flight reservation #{$reservationId} (full aircraft held)", ['reservation_id' => $reservationId]);
        $this->activity($userId, $reservationId, "You opened a shared flight on {$date}");

        return $reservationId;
    }

    private function joinPartial(int $userId, array $route, array $aircraft, string $date, string $seatPrice, array $seats, array $owner): int
    {
        if ($owner['kind'] !== 'partial' || (int) $owner['route_id'] !== (int) $route['id']) {
            throw new DomainRuleViolation('That aircraft is already booked on this date for a different flight.');
        }

        if ((int) $owner['user_id'] === $userId) {
            throw new DomainRuleViolation('You already own this flight.');
        }

        $share = FlightPricing::charge($seatPrice, count($seats));
        $reservationId = $this->insertReservation($userId, (int) $owner['id'], $route, $aircraft, $date, 'partial', count($seats), $share, 'pending');

        $this->insertSeats($reservationId, (int) $aircraft['id'], $date, $seats);
        $this->points->debit($userId, $share, 'booking', "Seat request #{$reservationId} on flight #{$owner['id']}", ['reservation_id' => $reservationId]);
        $this->points->credit((int) $owner['user_id'], $share, 'refund', "Seat share refunded: member joined flight #{$owner['id']}", ['reservation_id' => $reservationId]);
        $this->db->table('reservations')->where('id', $owner['id'])->update(['amount' => Money::sub(Money::normalise($owner['amount']), $share), 'updated_at' => date('Y-m-d H:i:s')]);

        $this->activity($userId, $reservationId, "You requested seats on shared flight #{$owner['id']}; waiting for the owner to confirm");
        $this->activity((int) $owner['user_id'], $reservationId, 'A member requested seats on your flight');

        return $reservationId;
    }

    private function cancelOwner(array $owner): void
    {
        $children = $this->db->table('reservations')->where('parent_reservation_id', $owner['id'])->where('status !=', 'cancelled')->get()->getResultArray();

        foreach ($children as $child) {
            $this->refundAndCancel($child, 'The flight owner cancelled the shared flight');
        }

        $this->refundAndCancel($owner, 'You cancelled your flight');
    }

    private function cancelJoiner(array $joiner): void
    {
        $owner = $this->row('reservations', (int) $joiner['parent_reservation_id'], 'Flight owner not found.');
        $share = Money::normalise($joiner['amount']);

        $this->points->debit((int) $owner['user_id'], $share, 'booking', "Seat share re-charged: member left flight #{$owner['id']}", ['reservation_id' => (int) $joiner['id']], true);
        $this->db->table('reservations')->where('id', $owner['id'])->update(['amount' => Money::add(Money::normalise($owner['amount']), $share), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->activity((int) $owner['user_id'], (int) $joiner['id'], 'A member cancelled their seats on your flight');

        $this->refundAndCancel($joiner, 'You cancelled your seats');
    }

    private function refundAndCancel(array $reservation, string $activity): void
    {
        $amount = Money::normalise($reservation['amount']);

        if (Money::isPositive($amount)) {
            $this->points->credit((int) $reservation['user_id'], $amount, 'refund', "Refund for reservation #{$reservation['id']}", ['reservation_id' => (int) $reservation['id']]);
        }

        $this->db->table('reservation_seats')->where('reservation_id', $reservation['id'])->delete();
        $this->db->table('reservations')->where('id', $reservation['id'])->update(['status' => 'cancelled', 'amount' => '0.00', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->activity((int) $reservation['user_id'], (int) $reservation['id'], $activity);
    }

    private function assertBookable(int $userId, string $flightDate): void
    {
        $member = $this->db->table('members')->where('user_id', $userId)->where('plan_id IS NOT NULL')->where('deleted_at', null)->get()->getRowArray();

        if ($member === null) {
            throw new DomainRuleViolation('Only primary members can book flights.');
        }

        if ($member['status'] !== 'active') {
            throw new DomainRuleViolation('Membership is not active.');
        }

        if ($flightDate < new DateTimeImmutable('today')->format('Y-m-d')) {
            throw new DomainRuleViolation('Flight date must be today or later.');
        }
    }

    private function validSeats(array $seats, int $capacity): array
    {
        $seats = array_values(array_unique(array_map(intval(...), $seats)));

        if ($seats === []) {
            throw new DomainRuleViolation('Select at least one seat.');
        }

        foreach ($seats as $seat) {
            if ($seat < 1 || $seat > $capacity) {
                throw new DomainRuleViolation("Seat {$seat} does not exist on this aircraft.");
            }
        }

        return $seats;
    }

    private function flightOwner(int $aircraftId, string $date): ?array
    {
        return $this->db->table('reservations')
            ->where('aircraft_id', $aircraftId)
            ->where('flight_date', $date)
            ->where('parent_reservation_id', null)
            ->where('status !=', 'cancelled')
            ->get()
            ->getRowArray();
    }

    private function takenSeatCount(int $aircraftId, string $date): int
    {
        return (int) $this->db->table('reservation_seats')->where('aircraft_id', $aircraftId)->where('flight_date', $date)->countAllResults();
    }

    private function insertReservation(int $userId, ?int $parentId, array $route, array $aircraft, string $date, string $kind, int $passengers, string $amount, string $status): int
    {
        $pilotFlight = $this->db->table('pilot_flights')->select('id')->where('aircraft_id', $aircraft['id'])->where('flight_date', $date)->where('deleted_at', null)->get()->getRowArray();

        $this->db->table('reservations')->insert([
            'user_id' => $userId,
            'parent_reservation_id' => $parentId,
            'aircraft_id' => $aircraft['id'],
            'route_id' => $route['id'],
            'pilot_flight_id' => $pilotFlight['id'] ?? null,
            'flight_date' => $date,
            'kind' => $kind,
            'passengers' => $passengers,
            'amount' => $amount,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    private function insertSeats(int $reservationId, int $aircraftId, string $date, array $seats): void
    {
        $taken = array_map(intval(...), array_column(
            $this->db->table('reservation_seats')->select('seat_number')->where('aircraft_id', $aircraftId)->where('flight_date', $date)->whereIn('seat_number', $seats)->get()->getResultArray(),
            'seat_number',
        ));

        if ($taken !== []) {
            throw new DomainRuleViolation('Seat(s) ' . implode(', ', $taken) . ' already booked.');
        }

        $this->db->table('reservation_seats')->insertBatch(array_map(
            static fn (int $seat): array => ['reservation_id' => $reservationId, 'aircraft_id' => $aircraftId, 'flight_date' => $date, 'seat_number' => $seat],
            $seats,
        ));
    }

    private function activity(int $userId, int $reservationId, string $description): void
    {
        $this->db->table('reservation_activities')->insert([
            'user_id' => $userId,
            'reservation_id' => $reservationId,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function row(string $table, int $id, string $message): array
    {
        $row = $this->db->table($table)->where('id', $id)->get()->getRowArray();

        return $row ?? throw new DomainRuleViolation($message);
    }
}
