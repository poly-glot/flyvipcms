<?php

declare(strict_types=1);

namespace Tests\Support;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

abstract class DatabaseTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;

    protected $migrateOnce = true;

    protected $refresh = false;

    protected $namespace;

    private const array KEPT_TABLES = ['migrations', 'membership_plans', 'contact_types', 'countries', 'country_zones'];

    private static int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $this->resetServices();

        $this->db->resetDataCache();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($this->db->listTables() ?: [] as $table) {
            if (!in_array($table, self::KEPT_TABLES, true)) {
                $this->db->query("TRUNCATE TABLE `{$table}`");
            }
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function row(string $table, array $where = [], string $direction = 'DESC'): array
    {
        $fields = $this->db->getFieldNames($table);
        $this->assertIsArray($fields);
        $order = in_array('id', $fields, true) ? 'id' : $fields[0];
        $row = $this->db->table($table)->where($where)->orderBy($order, $direction)->limit(1)->get()->getRowArray();

        $this->assertNotNull($row, "No row found in {$table}");

        return $row;
    }

    protected function user(int $id): User
    {
        $user = auth()->getProvider()->findById($id);
        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    protected function admin(): int
    {
        return (int) $this->login('admin')->id;
    }

    protected function login(string $group): User
    {
        $n = ++self::$sequence;
        $users = new UserModel();
        $users->save(new User(['username' => "{$group}{$n}", 'email' => "{$group}{$n}@example.test", 'password' => 'Str0ng-Passw0rd-123']));
        $user = $users->findById($users->getInsertID());
        assert($user instanceof User);
        $user->activate();
        $user->addGroup($group);

        return $user;
    }

    protected function member(int $planId = 1, string $yearlyFee = '12000', bool $activate = true, bool $fund = true): int
    {
        $n = ++self::$sequence;
        $userId = service('members')->create(
            ['username' => "member{$n}", 'email' => "member{$n}@example.test", 'password' => 'Str0ng-Passw0rd-123'],
            ['first_name' => 'Test', 'last_name' => "Member{$n}"],
            $planId,
            '5000',
            $yearlyFee,
        );

        if ($activate) {
            service('payments')->record($userId, 'joining', date('Y-m-d'), 'test', $this->admin());
        }

        if ($activate && $fund) {
            service('payments')->record($userId, 'yearly', date('Y-m-d'), 'test', $this->admin());
        }

        return $userId;
    }

    protected function fleet(string $routeCost = '400', string $seatCost = '250', int $capacity = 6): array
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('aircraft_types')->insert(['name' => 'Jet', 'created_at' => $now, 'updated_at' => $now]);
        $typeId = (int) $this->db->insertID();

        $this->db->table('aircrafts')->insert(['name' => 'Test jet', 'aircraft_type_id' => $typeId, 'passenger_capacity' => $capacity, 'total_cost' => $seatCost, 'created_at' => $now, 'updated_at' => $now]);
        $aircraftId = (int) $this->db->insertID();

        $this->db->table('airports')->insertBatch([
            ['name' => 'A', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $fromId = (int) $this->db->insertID();

        $this->db->table('air_routes')->insert(['from_airport_id' => $fromId, 'to_airport_id' => $fromId + 1, 'aircraft_id' => $aircraftId, 'cost' => $routeCost, 'created_at' => $now, 'updated_at' => $now]);

        return ['aircraft' => $aircraftId, 'route' => (int) $this->db->insertID()];
    }

    protected function tomorrow(): string
    {
        return date('Y-m-d', strtotime('+1 day'));
    }
}
