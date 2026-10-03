<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMemberTables extends Migration
{
    public function up(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE profiles (
                user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                company VARCHAR(150) NULL,
                street_address VARCHAR(255) NULL,
                country_id INT UNSIGNED NULL,
                zone_id INT UNSIGNED NULL,
                city VARCHAR(100) NULL,
                zip_code VARCHAR(20) NULL,
                cellphone VARCHAR(30) NULL,
                office_phone VARCHAR(30) NULL,
                home_phone VARCHAR(30) NULL,
                secondary_phone VARCHAR(30) NULL,
                id_number VARCHAR(50) NULL,
                id_due_on DATE NULL,
                passport_number VARCHAR(50) NULL,
                passport_issued_on DATE NULL,
                passport_due_on DATE NULL,
                favourite_route_id INT UNSIGNED NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                CONSTRAINT fk_profiles_country FOREIGN KEY (country_id) REFERENCES countries (id),
                CONSTRAINT fk_profiles_zone FOREIGN KEY (zone_id) REFERENCES country_zones (id),
                CONSTRAINT fk_profiles_route FOREIGN KEY (favourite_route_id) REFERENCES air_routes (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE members (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                plan_id TINYINT UNSIGNED NULL,
                parent_user_id INT UNSIGNED NULL,
                contact_type_id TINYINT UNSIGNED NULL,
                member_code VARCHAR(12) NOT NULL UNIQUE,
                membership_name VARCHAR(12) NOT NULL,
                joining_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
                yearly_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
                joining_fee_paid_at DATETIME NULL,
                status ENUM('inactive','active','banned') NOT NULL DEFAULT 'inactive',
                next_due_on DATE NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                INDEX idx_members_parent (parent_user_id),
                CONSTRAINT fk_members_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                CONSTRAINT fk_members_plan FOREIGN KEY (plan_id) REFERENCES membership_plans (id),
                CONSTRAINT fk_members_parent FOREIGN KEY (parent_user_id) REFERENCES users (id),
                CONSTRAINT fk_members_contact FOREIGN KEY (contact_type_id) REFERENCES contact_types (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE payments (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                type ENUM('joining','yearly','quarterly','bonus','points') NOT NULL,
                description VARCHAR(255) NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                paid_on DATE NOT NULL,
                remark VARCHAR(255) NOT NULL,
                created_by INT UNSIGNED NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                INDEX idx_payments_user (user_id),
                CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users (id),
                CONSTRAINT fk_payments_creator FOREIGN KEY (created_by) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE membership_terms (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                member_id INT UNSIGNED NOT NULL,
                starts_on DATE NOT NULL,
                ends_on DATE NOT NULL,
                created_at DATETIME NULL,
                CONSTRAINT fk_terms_member FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE membership_quarters (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                term_id INT UNSIGNED NOT NULL,
                payment_id INT UNSIGNED NULL,
                quarter TINYINT UNSIGNED NOT NULL,
                starts_on DATE NOT NULL,
                ends_on DATE NOT NULL,
                points_loaded DECIMAL(12,2) NOT NULL DEFAULT 0,
                UNIQUE KEY uq_quarters_term_quarter (term_id, quarter),
                CONSTRAINT fk_quarters_term FOREIGN KEY (term_id) REFERENCES membership_terms (id) ON DELETE CASCADE,
                CONSTRAINT fk_quarters_payment FOREIGN KEY (payment_id) REFERENCES payments (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $this->db->query(<<<'SQL'
            CREATE TABLE points_transfers (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                sender_id INT UNSIGNED NOT NULL,
                receiver_id INT UNSIGNED NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                created_by INT UNSIGNED NOT NULL,
                created_at DATETIME NULL,
                CONSTRAINT fk_transfers_sender FOREIGN KEY (sender_id) REFERENCES users (id),
                CONSTRAINT fk_transfers_receiver FOREIGN KEY (receiver_id) REFERENCES users (id),
                CONSTRAINT fk_transfers_creator FOREIGN KEY (created_by) REFERENCES users (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }

    public function down(): void
    {
        foreach (['points_transfers', 'membership_quarters', 'membership_terms', 'payments', 'members', 'profiles'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}
