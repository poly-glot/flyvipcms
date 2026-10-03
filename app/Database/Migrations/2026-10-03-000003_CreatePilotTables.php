<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePilotTables extends Migration
{
    public function up(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE pilots (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(80) NOT NULL,
                last_name VARCHAR(80) NOT NULL,
                birth_date DATE NULL,
                city VARCHAR(80) NULL,
                address VARCHAR(255) NULL,
                country VARCHAR(80) NULL,
                cell_phone VARCHAR(30) NULL,
                work_phone VARCHAR(30) NULL,
                home_phone VARCHAR(30) NULL,
                email_primary VARCHAR(120) NULL,
                email_secondary VARCHAR(120) NULL,
                contract_type VARCHAR(40) NULL,
                basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
                hourly_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
                extra_bonus DECIMAL(12,2) NOT NULL DEFAULT 0,
                training_included VARCHAR(120) NULL,
                notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE pilot_documents (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                pilot_id INT UNSIGNED NOT NULL,
                country VARCHAR(80) NULL,
                doc_type VARCHAR(40) NOT NULL,
                doc_number VARCHAR(60) NOT NULL,
                issued_on DATE NULL,
                expires_on DATE NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                CONSTRAINT fk_pilot_docs_pilot FOREIGN KEY (pilot_id) REFERENCES pilots (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE pilot_certifications (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                pilot_id INT UNSIGNED NOT NULL,
                aircraft_id INT UNSIGNED NULL,
                certificate VARCHAR(120) NOT NULL,
                cert_type VARCHAR(60) NULL,
                agency_school VARCHAR(120) NULL,
                issued_on DATE NULL,
                expires_on DATE NULL,
                due_on DATE NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                comments TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                CONSTRAINT fk_pilot_certs_pilot FOREIGN KEY (pilot_id) REFERENCES pilots (id) ON DELETE CASCADE,
                CONSTRAINT fk_pilot_certs_aircraft FOREIGN KEY (aircraft_id) REFERENCES aircrafts (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE pilot_flights (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                pilot_id INT UNSIGNED NOT NULL,
                aircraft_id INT UNSIGNED NOT NULL,
                route_id INT UNSIGNED NULL,
                flight_date DATE NOT NULL,
                identification VARCHAR(60) NULL,
                call_sign VARCHAR(30) NULL,
                day_hours DECIMAL(6,1) NOT NULL DEFAULT 0,
                night_hours DECIMAL(6,1) NOT NULL DEFAULT 0,
                ifr_hours DECIMAL(6,1) NOT NULL DEFAULT 0,
                landings SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                total_hours DECIMAL(6,1) NOT NULL DEFAULT 0,
                comments TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                INDEX idx_pilot_flights_schedule (aircraft_id, flight_date),
                CONSTRAINT fk_pilot_flights_pilot FOREIGN KEY (pilot_id) REFERENCES pilots (id),
                CONSTRAINT fk_pilot_flights_aircraft FOREIGN KEY (aircraft_id) REFERENCES aircrafts (id),
                CONSTRAINT fk_pilot_flights_route FOREIGN KEY (route_id) REFERENCES air_routes (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }

    public function down(): void
    {
        foreach (['pilot_flights', 'pilot_certifications', 'pilot_documents', 'pilots'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
