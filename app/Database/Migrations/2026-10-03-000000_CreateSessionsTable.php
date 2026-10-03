<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSessionsTable extends Migration
{
    public function up(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE ci_sessions (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data BLOB NOT NULL,
                INDEX ci_sessions_timestamp (timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS ci_sessions');
    }
}
