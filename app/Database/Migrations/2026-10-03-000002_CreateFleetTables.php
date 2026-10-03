<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFleetTables extends Migration
{
    public function up(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE aircraft_types (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE aircrafts (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                aircraft_type_id INT UNSIGNED NOT NULL,
                model VARCHAR(100) NULL,
                manufactured_on DATE NULL,
                passenger_capacity TINYINT UNSIGNED NOT NULL DEFAULT 1,
                airframe_hours DECIMAL(10,1) NOT NULL DEFAULT 0,
                engine_hours_left DECIMAL(10,1) NOT NULL DEFAULT 0,
                engine_hours_right DECIMAL(10,1) NOT NULL DEFAULT 0,
                variable_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                fixed_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                max_altitude INT UNSIGNED NULL,
                gallons_per_hour DECIMAL(8,2) NULL,
                certification VARCHAR(100) NULL,
                insurance_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                CONSTRAINT fk_aircrafts_type FOREIGN KEY (aircraft_type_id) REFERENCES aircraft_types (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE aircraft_repairs (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                aircraft_id INT UNSIGNED NOT NULL,
                repaired_on DATE NOT NULL,
                code VARCHAR(50) NOT NULL,
                description TEXT NOT NULL,
                repair_shop VARCHAR(100) NOT NULL,
                performed_hours DECIMAL(8,1) NOT NULL,
                expires_on DATE NOT NULL,
                expiry_hours DECIMAL(8,1) NOT NULL,
                cost DECIMAL(12,2) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                CONSTRAINT fk_repairs_aircraft FOREIGN KEY (aircraft_id) REFERENCES aircrafts (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE airports (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                call_sign VARCHAR(20) NULL,
                airport_type VARCHAR(40) NULL,
                customs_contact VARCHAR(120) NULL,
                fbo_contact VARCHAR(120) NULL,
                hotel_contact VARCHAR(120) NULL,
                travel_allowance DECIMAL(12,2) NOT NULL DEFAULT 0,
                take_off_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                landing_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                stay_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                airport_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                handling_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                comments TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE air_routes (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                from_airport_id INT UNSIGNED NOT NULL,
                to_airport_id INT UNSIGNED NOT NULL,
                aircraft_id INT UNSIGNED NULL,
                distance DECIMAL(10,1) NULL,
                flight_time VARCHAR(20) NULL,
                ete VARCHAR(20) NULL,
                actual_ete VARCHAR(20) NULL,
                cost DECIMAL(12,2) NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                CONSTRAINT fk_routes_from FOREIGN KEY (from_airport_id) REFERENCES airports (id),
                CONSTRAINT fk_routes_to FOREIGN KEY (to_airport_id) REFERENCES airports (id),
                CONSTRAINT fk_routes_aircraft FOREIGN KEY (aircraft_id) REFERENCES aircrafts (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }

    public function down(): void
    {
        foreach (['air_routes', 'airports', 'aircraft_repairs', 'aircrafts', 'aircraft_types'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
