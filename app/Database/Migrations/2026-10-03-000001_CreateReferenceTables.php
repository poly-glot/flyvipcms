<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReferenceTables extends Migration
{
    public function up(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE membership_plans (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                code CHAR(1) NOT NULL UNIQUE,
                name VARCHAR(40) NOT NULL,
                owner_prefix CHAR(2) NOT NULL,
                allows_submembers TINYINT(1) NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            INSERT INTO membership_plans (id, code, name, owner_prefix, allows_submembers) VALUES
                (1, 'P', 'FLYVip Personal +', 'PO', 0),
                (2, 'F', 'FLYVip Family +', 'FO', 1),
                (3, 'C', 'FLYVip Corporate +', 'CO', 1)
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE contact_types (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                name VARCHAR(40) NOT NULL,
                suffix CHAR(1) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            INSERT INTO contact_types (id, name, suffix) VALUES
                (1, 'Family member', 'M'),
                (2, 'Partner', 'A'),
                (3, 'Child', 'X'),
                (4, 'Guest', 'X'),
                (5, 'Other', 'X')
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE countries (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(80) NOT NULL,
                iso2 CHAR(2) NOT NULL UNIQUE,
                active TINYINT(1) NOT NULL DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE country_zones (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                country_id INT UNSIGNED NOT NULL,
                name VARCHAR(80) NOT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                CONSTRAINT fk_zones_country FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            INSERT INTO countries (id, name, iso2) VALUES
                (1, 'Venezuela', 'VE'),
                (2, 'United States', 'US'),
                (3, 'United Kingdom', 'GB'),
                (4, 'Colombia', 'CO'),
                (5, 'Panama', 'PA')
            SQL);

        $this->db->query(<<<'SQL'
            INSERT INTO country_zones (country_id, name) VALUES
                (1, 'Distrito Capital'), (1, 'Miranda'), (1, 'Nueva Esparta'), (1, 'Zulia'), (1, 'Carabobo'), (1, 'Anzoategui'),
                (2, 'Florida'), (2, 'New York'), (2, 'Texas'),
                (3, 'England'), (3, 'Scotland'),
                (4, 'Bogota'), (4, 'Antioquia'),
                (5, 'Panama')
            SQL);
    }

    public function down(): void
    {
        foreach (['country_zones', 'countries', 'contact_types', 'membership_plans'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
