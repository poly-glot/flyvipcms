<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public const ACCOUNTS = [
        'admin' => ['admin@flyvip.test', 'FlyVIP-Admin-2026!'],
        'personal' => ['ana.personal@flyvip.test', 'FlyVIP-Personal-2026!'],
        'family' => ['carlos.family@flyvip.test', 'FlyVIP-Family-2026!'],
        'submember' => ['lucia.family@flyvip.test', 'FlyVIP-Submember-2026!'],
        'corporate' => ['acme.corporate@flyvip.test', 'FlyVIP-Corporate-2026!'],
        'prospect' => ['new.prospect@flyvip.test', 'FlyVIP-Prospect-2026!'],
        'banned' => ['banned.member@flyvip.test', 'FlyVIP-Banned-2026!'],
    ];

    public function run(): void
    {
        if (ENVIRONMENT === 'production') {
            throw new RuntimeException('DemoSeeder must never run in production.');
        }

        if ($this->db->table('auth_identities')->where('secret', self::ACCOUNTS['admin'][0])->countAllResults() > 0) {
            return;
        }

        $adminId = $this->createAdmin();
        $this->seedFleet();
        $members = $this->seedMembers($adminId);
        $this->seedBookings($members);
    }

    private function createAdmin(): int
    {
        $users = new UserModel();
        $users->save(new User(['username' => 'admin', 'email' => self::ACCOUNTS['admin'][0], 'password' => self::ACCOUNTS['admin'][1]]));
        $admin = $users->findById($users->getInsertID());
        assert($admin instanceof User);
        $admin->activate();
        $admin->addGroup('admin');

        return (int) $admin->id;
    }

    private function seedFleet(): void
    {
        $now = date('Y-m-d H:i:s');
        $stamp = ['created_at' => $now, 'updated_at' => $now];

        $this->db->table('aircraft_types')->insertBatch([
            ['name' => 'Light jet'] + $stamp,
            ['name' => 'Turboprop'] + $stamp,
        ]);

        $this->db->table('aircrafts')->insertBatch([
            ['name' => 'Citation CJ3', 'aircraft_type_id' => 1, 'model' => 'CJ3', 'passenger_capacity' => 6, 'variable_cost' => 150, 'fixed_cost' => 100, 'total_cost' => 250, 'max_altitude' => 45000] + $stamp,
            ['name' => 'King Air 350', 'aircraft_type_id' => 2, 'model' => '350', 'passenger_capacity' => 8, 'variable_cost' => 110, 'fixed_cost' => 70, 'total_cost' => 180, 'max_altitude' => 35000] + $stamp,
        ]);

        $this->db->table('airports')->insertBatch([
            ['name' => 'Maiquetia (CCS)', 'call_sign' => 'SVMI', 'airport_type' => 'International'] + $stamp,
            ['name' => 'Porlamar (PMV)', 'call_sign' => 'SVMG', 'airport_type' => 'International'] + $stamp,
            ['name' => 'Miami (MIA)', 'call_sign' => 'KMIA', 'airport_type' => 'International'] + $stamp,
            ['name' => 'Maracaibo (MAR)', 'call_sign' => 'SVMC', 'airport_type' => 'International'] + $stamp,
        ]);

        $this->db->table('air_routes')->insertBatch([
            ['from_airport_id' => 1, 'to_airport_id' => 2, 'aircraft_id' => 1, 'distance' => 280, 'flight_time' => '1:00', 'cost' => 400] + $stamp,
            ['from_airport_id' => 1, 'to_airport_id' => 3, 'aircraft_id' => 1, 'distance' => 1230, 'flight_time' => '3:15', 'cost' => 900] + $stamp,
            ['from_airport_id' => 4, 'to_airport_id' => 1, 'aircraft_id' => 2, 'distance' => 360, 'flight_time' => '1:20', 'cost' => 350] + $stamp,
        ]);

        $this->db->table('pilots')->insertBatch([
            ['first_name' => 'Marta', 'last_name' => 'Rojas', 'city' => 'Caracas', 'cell_phone' => '+58 412 555 0101', 'email_primary' => 'marta.rojas@flyvip.test', 'contract_type' => 'Full time', 'hourly_salary' => 90] + $stamp,
            ['first_name' => 'Diego', 'last_name' => 'Salas', 'city' => 'Maracaibo', 'cell_phone' => '+58 414 555 0102', 'email_primary' => 'diego.salas@flyvip.test', 'contract_type' => 'Contractor', 'hourly_salary' => 75] + $stamp,
        ]);

        $this->db->table('pilot_documents')->insert(['pilot_id' => 1, 'doc_type' => 'Passport', 'doc_number' => 'P1234567', 'country' => 'Venezuela', 'expires_on' => date('Y-m-d', strtotime('+3 years'))] + $stamp);
        $this->db->table('pilot_certifications')->insert(['pilot_id' => 1, 'aircraft_id' => 1, 'certificate' => 'Citation CJ3 type rating', 'cert_type' => 'Type rating', 'expires_on' => date('Y-m-d', strtotime('+1 year')), 'active' => 1] + $stamp);
        $this->db->table('pilot_flights')->insertBatch([
            ['pilot_id' => 1, 'aircraft_id' => 1, 'route_id' => 1, 'flight_date' => date('Y-m-d', strtotime('+7 days')), 'identification' => 'FV101', 'total_hours' => 1] + $stamp,
            ['pilot_id' => 2, 'aircraft_id' => 2, 'route_id' => 3, 'flight_date' => date('Y-m-d', strtotime('+9 days')), 'identification' => 'FV201', 'total_hours' => 1.3] + $stamp,
        ]);
        $this->db->table('aircraft_repairs')->insert(['aircraft_id' => 1, 'repaired_on' => date('Y-m-d', strtotime('-30 days')), 'code' => 'A-CHECK', 'description' => 'Scheduled A inspection', 'repair_shop' => 'Maiquetia MRO', 'performed_hours' => 12, 'expires_on' => date('Y-m-d', strtotime('+150 days')), 'expiry_hours' => 200, 'cost' => 4200] + $stamp);
    }

    private function seedMembers(int $adminId): array
    {
        $members = service('members');
        $payments = service('payments');
        $today = date('Y-m-d');

        $personal = $members->create($this->account('ana.personal', 'personal'), $this->profile('Ana', 'Personal', 'Caracas'), 1, '5000', '12000');
        $payments->record($personal, 'joining', $today, 'Wire transfer', $adminId);
        $payments->record($personal, 'yearly', $today, 'Wire transfer', $adminId);

        $family = $members->create($this->account('carlos.family', 'family'), $this->profile('Carlos', 'Family', 'Maracaibo'), 2, '7500', '24000');
        $payments->record($family, 'joining', $today, 'Card', $adminId);
        $payments->record($family, 'yearly', $today, 'Card', $adminId);
        $members->addSubMember($family, $this->account('lucia.family', 'submember') + $this->profile('Lucia', 'Family', 'Maracaibo') + ['contact_type_id' => 2]);

        $corporate = $members->create($this->account('acme.corporate', 'corporate'), $this->profile('Acme', 'Corporate', 'Valencia') + ['company' => 'Acme Holdings'], 3, '10000', '48000');
        $payments->record($corporate, 'joining', $today, 'Wire transfer', $adminId);
        $payments->record($corporate, 'quarterly', $today, 'Wire transfer', $adminId, null, 2);

        $members->create($this->account('new.prospect', 'prospect'), $this->profile('Nora', 'Prospect', 'Caracas'), 1, '5000', '12000');

        $banned = $members->create($this->account('banned.member', 'banned'), $this->profile('Bruno', 'Banned', 'Caracas'), 1, '5000', '12000');
        $payments->record($banned, 'joining', $today, 'Cash', $adminId);
        $members->setStatus($banned, 'banned');

        return ['personal' => $personal, 'family' => $family];
    }

    private function seedBookings(array $members): void
    {
        $date = date('Y-m-d', strtotime('+7 days'));
        $reservations = service('reservations');

        $reservations->book($members['family'], 1, 1, $date, 'partial', [1, 2]);
        $reservations->book($members['personal'], 1, 1, $date, 'partial', [3, 4]);
    }

    private function account(string $username, string $key): array
    {
        return ['username' => $username, 'email' => self::ACCOUNTS[$key][0], 'password' => self::ACCOUNTS[$key][1]];
    }

    private function profile(string $first, string $last, string $city): array
    {
        return ['first_name' => $first, 'last_name' => $last, 'city' => $city, 'cellphone' => '+58 412 555 0100', 'country_id' => 1];
    }
}
