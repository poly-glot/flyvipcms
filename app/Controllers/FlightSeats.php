<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class FlightSeats extends BaseController
{
    public function taken(): ResponseInterface
    {
        $query = ['aircraft_id' => (string) $this->request->getGet('aircraft_id'), 'flight_date' => (string) $this->request->getGet('flight_date')];

        if (!$this->validateData($query, ['aircraft_id' => 'required|is_natural_no_zero', 'flight_date' => 'required|valid_date[Y-m-d]'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON(['taken' => []]);
        }

        $rows = db_connect()->table('reservation_seats')
            ->select('seat_number')
            ->where('aircraft_id', (int) $query['aircraft_id'])
            ->where('flight_date', $query['flight_date'])
            ->orderBy('seat_number')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['taken' => array_map(intval(...), array_column($rows, 'seat_number'))]);
    }
}
