<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationTables extends Migration
{
    public function up(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE reservations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                parent_reservation_id INT UNSIGNED NULL,
                aircraft_id INT UNSIGNED NOT NULL,
                route_id INT UNSIGNED NOT NULL,
                pilot_flight_id INT UNSIGNED NULL,
                flight_date DATE NOT NULL,
                kind ENUM('complete','partial') NOT NULL,
                passengers TINYINT UNSIGNED NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                status ENUM('pending','confirmed','cancelled') NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                INDEX idx_reservations_user (user_id),
                INDEX idx_reservations_flight (aircraft_id, flight_date),
                CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users (id),
                CONSTRAINT fk_reservations_parent FOREIGN KEY (parent_reservation_id) REFERENCES reservations (id),
                CONSTRAINT fk_reservations_aircraft FOREIGN KEY (aircraft_id) REFERENCES aircrafts (id),
                CONSTRAINT fk_reservations_route FOREIGN KEY (route_id) REFERENCES air_routes (id),
                CONSTRAINT fk_reservations_pilot_flight FOREIGN KEY (pilot_flight_id) REFERENCES pilot_flights (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE reservation_seats (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                reservation_id INT UNSIGNED NOT NULL,
                aircraft_id INT UNSIGNED NOT NULL,
                flight_date DATE NOT NULL,
                seat_number TINYINT UNSIGNED NOT NULL,
                UNIQUE KEY uq_seats_flight_seat (aircraft_id, flight_date, seat_number),
                CONSTRAINT fk_seats_reservation FOREIGN KEY (reservation_id) REFERENCES reservations (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE reservation_activities (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                reservation_id INT UNSIGNED NULL,
                description VARCHAR(255) NOT NULL,
                created_at DATETIME NULL,
                INDEX idx_activities_user (user_id),
                CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users (id),
                CONSTRAINT fk_activities_reservation FOREIGN KEY (reservation_id) REFERENCES reservations (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE points_ledger (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                balance_after DECIMAL(12,2) NOT NULL,
                kind ENUM('payment','adjustment','transfer_in','transfer_out','booking','refund') NOT NULL,
                description VARCHAR(255) NOT NULL,
                payment_id INT UNSIGNED NULL,
                transfer_id INT UNSIGNED NULL,
                reservation_id INT UNSIGNED NULL,
                created_at DATETIME NULL,
                INDEX idx_ledger_user (user_id, id),
                CONSTRAINT fk_ledger_user FOREIGN KEY (user_id) REFERENCES users (id),
                CONSTRAINT fk_ledger_payment FOREIGN KEY (payment_id) REFERENCES payments (id),
                CONSTRAINT fk_ledger_transfer FOREIGN KEY (transfer_id) REFERENCES points_transfers (id),
                CONSTRAINT fk_ledger_reservation FOREIGN KEY (reservation_id) REFERENCES reservations (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }

    public function down(): void
    {
        foreach (['points_ledger', 'reservation_activities', 'reservation_seats', 'reservations'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
