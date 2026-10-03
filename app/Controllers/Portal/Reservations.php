<?php

declare(strict_types=1);

namespace App\Controllers\Portal;

use App\Exceptions\DomainRuleViolation;
use App\Models\AircraftModel;
use App\Models\RouteModel;
use CodeIgniter\HTTP\RedirectResponse;

class Reservations extends PortalController
{
    public function index(): string
    {
        $member = $this->member();
        $accountId = $this->accountUserId($member);
        $db = db_connect();

        $own = $db->table('reservations')
            ->select('reservations.*, aircrafts.name AS aircraft_name, from_airport.name AS from_name, to_airport.name AS to_name')
            ->join('aircrafts', 'aircrafts.id = reservations.aircraft_id')
            ->join('air_routes', 'air_routes.id = reservations.route_id')
            ->join('airports AS from_airport', 'from_airport.id = air_routes.from_airport_id')
            ->join('airports AS to_airport', 'to_airport.id = air_routes.to_airport_id')
            ->where('reservations.user_id', $accountId)
            ->orderBy('reservations.flight_date', 'DESC')
            ->get()
            ->getResultArray();

        $ownIds = array_column($own, 'id');
        $requests = $ownIds === [] ? [] : $db->table('reservations')
            ->select('reservations.*, profiles.first_name, profiles.last_name')
            ->join('profiles', 'profiles.user_id = reservations.user_id')
            ->whereIn('reservations.parent_reservation_id', $ownIds)
            ->where('reservations.status', 'pending')
            ->get()
            ->getResultArray();

        return view('portal/reservations', [
            'reservations' => $own,
            'requests' => $requests,
            'canBook' => $this->isPrimary($member),
            'today' => date('Y-m-d'),
        ]);
    }

    public function new(): string
    {
        return view('portal/reservation_form', [
            'routes' => new RouteModel()->labelled(),
            'aircrafts' => new AircraftModel()->menu(),
            'capacities' => array_column(new AircraftModel()->findAll(), 'passenger_capacity', 'id'),
            'balance' => service('points')->balance((int) auth()->id()),
        ]);
    }

    public function create(): RedirectResponse
    {
        $rules = [
            'route_id' => 'required|is_natural_no_zero',
            'aircraft_id' => 'required|is_natural_no_zero',
            'flight_date' => 'required|valid_date[Y-m-d]',
            'kind' => 'required|in_list[complete,partial]',
            'seats.*' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithErrors($this->validator?->getErrors() ?? []);
        }

        try {
            service('reservations')->book(
                (int) auth()->id(),
                (int) $this->request->getPost('route_id'),
                (int) $this->request->getPost('aircraft_id'),
                (string) $this->request->getPost('flight_date'),
                (string) $this->request->getPost('kind'),
                (array) ($this->request->getPost('seats') ?? []),
            );
        } catch (DomainRuleViolation $error) {
            return $this->backWithErrors(['reservation' => $error->getMessage()]);
        }

        return $this->back('portal/reservations', 'success', 'Reservation created.');
    }

    public function confirm(int $id): RedirectResponse
    {
        return $this->run('Seat request confirmed.', static fn () => service('reservations')->confirm($id, (int) auth()->id(), false));
    }

    public function cancel(int $id): RedirectResponse
    {
        return $this->run('Reservation cancelled and points refunded.', static fn () => service('reservations')->cancel($id, (int) auth()->id(), false));
    }

    private function run(string $success, callable $action): RedirectResponse
    {
        try {
            $action();
        } catch (DomainRuleViolation $error) {
            return $this->back('portal/reservations', 'error', $error->getMessage());
        }

        return $this->back('portal/reservations', 'success', $success);
    }
}
