<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . \DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    public array $default = [];

    public array $tests = [];

    public function __construct()
    {
        parent::__construct();

        $this->default = $this->connection((string) env('DB_NAME', 'flyvip'));
        $this->tests = $this->connection((string) env('DB_TEST_NAME', 'flyvip_test')) + ['DBPrefix' => ''];

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }

    private function connection(string $database): array
    {
        $ssl = filter_var(env('DB_SSL', '0'), \FILTER_VALIDATE_BOOLEAN);

        return [
            'DBDebug' => true,
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'DBCollat' => 'utf8mb4_general_ci',
            'charset' => 'utf8mb4',
            'database' => $database,
            'encrypt' => $ssl ? ['ssl_verify' => false] : false,
            'hostname' => (string) env('DB_HOST', 'db'),
            'username' => (string) env('DB_USER', 'flyvip'),
            'password' => (string) env('DB_PASS', 'flyvip'),
            'port' => (int) env('DB_PORT', 3306),
            'strictOn' => true,
            'failover' => [],
            'numberNative' => false,
            'foundRows' => false,
            'dateFormat' => [
                'date' => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time' => 'H:i:s',
            ],
        ];
    }
}
