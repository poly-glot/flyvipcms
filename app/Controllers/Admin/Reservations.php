<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\DomainRuleViolation;
use App\Models\AircraftModel;
use App\Models\RouteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Reservations extends BaseController
{
    public function index(): string
    {
        return view('admin/reservations/index', [
            'reservations' => service('reservations')->listing(true)
                ->orderBy('reservations.flight_date', 'DESC')
                ->orderBy('reservations.id', 'DESC')
                ->get()
                ->getResultArray(),
        ]);
    }

    public function new(): string
    {
        return view('admin/reservations/form', [
            'members' => $this->memberOptions(service('members')->primaryMembers(true), true),
            'routes' => new RouteModel()->labelled(),
            'aircrafts' => new AircraftModel()->menu(),
            'capacities' => new AircraftModel()->capacities(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $rules = [
            'user_id' => 'required|is_natural_no_zero',
            'route_id' => 'required|is_natural_no_zero',
            'aircraft_id' => 'required|is_natural_no_zero',
            'flight_date' => 'required|valid_date[Y-m-d]',
            'kind' => 'required|in_list[complete,partial]',
            'seats.*' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithValidationErrors();
        }

        try {
            $id = service('reservations')->book(
                (int) $this->request->getPost('user_id'),
                (int) $this->request->getPost('route_id'),
                (int) $this->request->getPost('aircraft_id'),
                (string) $this->request->getPost('flight_date'),
                (string) $this->request->getPost('kind'),
                (array) ($this->request->getPost('seats') ?? []),
            );
        } catch (DomainRuleViolation $error) {
            return $this->backWithErrors(['reservation' => $error->getMessage()]);
        }

        return $this->back("admin/reservations/{$id}", 'success', 'Reservation created.');
    }

    public function show(int $id): string
    {
        $db = db_connect();
        $reservation = service('reservations')->listing(true)
            ->where('reservations.id', $id)
            ->get()
            ->getRowArray() ?? throw PageNotFoundException::forPageNotFound();

        return view('admin/reservations/show', [
            'reservation' => $reservation,
            'seats' => array_column($db->table('reservation_seats')->where('reservation_id', $id)->orderBy('seat_number')->get()->getResultArray(), 'seat_number'),
            'joiners' => $db->table('reservations')
                ->select('reservations.*, profiles.first_name, profiles.last_name')
                ->join('profiles', 'profiles.user_id = reservations.user_id')
                ->where('parent_reservation_id', $id)
                ->get()
                ->getResultArray(),
            'activity' => $db->table('reservation_activities')->where('reservation_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function confirm(int $id): RedirectResponse
    {
        return $this->run($id, 'Seat request confirmed.', static fn () => service('reservations')->confirm($id, (int) auth()->id(), true));
    }

    public function cancel(int $id): RedirectResponse
    {
        return $this->run($id, 'Reservation cancelled and points refunded.', static fn () => service('reservations')->cancel($id, (int) auth()->id(), true));
    }

    private function run(int $id, string $success, callable $action): RedirectResponse
    {
        try {
            $action();
        } catch (DomainRuleViolation $error) {
            return $this->back("admin/reservations/{$id}", 'error', $error->getMessage());
        }

        return $this->back("admin/reservations/{$id}", 'success', $success);
    }
}
