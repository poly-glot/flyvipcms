<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DomainRuleViolation;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

final readonly class MemberService
{
    use Atomic;

    public const array PROFILE_FIELDS = [
        'first_name', 'last_name', 'company', 'street_address', 'country_id', 'zone_id', 'city', 'zip_code',
        'cellphone', 'office_phone', 'home_phone', 'secondary_phone', 'id_number', 'id_due_on',
        'passport_number', 'passport_issued_on', 'passport_due_on', 'favourite_route_id',
    ];

    public function __construct(
        private BaseConnection $db,
        private UserModel $users,
    ) {
    }

    public function create(array $account, array $profile, int $planId, string $joiningFee, string $yearlyFee, array $subMembers = []): int
    {
        return $this->atomic(function () use ($account, $profile, $planId, $joiningFee, $yearlyFee, $subMembers): int {
            $plan = $this->plan($planId);
            $user = $this->createLogin($account, 'member');
            $userId = (int) $user->id;

            $this->insertProfile($userId, $profile);
            $this->db->table('members')->insert([
                'user_id' => $userId,
                'plan_id' => $planId,
                'member_code' => sprintf('%s-%03d', $plan['owner_prefix'], $userId),
                'membership_name' => sprintf('%s-%03d', $plan['code'], $userId),
                'joining_fee' => $joiningFee,
                'yearly_fee' => $yearlyFee,
                'status' => 'inactive',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($subMembers as $sub) {
                $this->addSubMember($userId, $sub);
            }

            return $userId;
        });
    }

    public function addSubMember(int $parentUserId, array $data): int
    {
        return $this->atomic(function () use ($parentUserId, $data): int {
            $parent = $this->db->table('members')->where('user_id', $parentUserId)->where('deleted_at', null)->get()->getRowArray();

            if ($parent === null || $parent['plan_id'] === null) {
                throw new DomainRuleViolation('Sub-members can only be attached to a primary member.');
            }

            $plan = $this->plan((int) $parent['plan_id']);

            if ((int) $plan['allows_submembers'] !== 1) {
                throw new DomainRuleViolation("The {$plan['name']} plan does not allow sub-members.");
            }

            $contactType = $this->db->table('contact_types')->where('id', (int) $data['contact_type_id'])->get()->getRowArray();

            if ($contactType === null) {
                throw new DomainRuleViolation('Unknown contact type.');
            }

            $user = $this->createLogin($data, 'submember');
            $userId = (int) $user->id;

            $this->insertProfile($userId, $data);
            $this->db->table('members')->insert([
                'user_id' => $userId,
                'parent_user_id' => $parentUserId,
                'contact_type_id' => (int) $contactType['id'],
                'member_code' => sprintf('%s%s-%03d', $plan['code'], $contactType['suffix'], $userId),
                'membership_name' => sprintf('%s-%03d', $plan['code'], $userId),
                'status' => 'inactive',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $userId;
        });
    }

    public function update(int $userId, array $account, array $profile): void
    {
        $this->atomic(function () use ($userId, $account, $profile): void {
            $user = $this->users->findById($userId);

            if (!$user instanceof User) {
                throw new DomainRuleViolation('Member not found.');
            }

            $user->fill(array_filter([
                'username' => $account['username'] ?? null,
                'email' => $account['email'] ?? null,
                'password' => $account['password'] ?? null,
            ], static fn (mixed $value): bool => $value !== null && $value !== ''));
            $this->users->save($user);

            $this->db->table('profiles')->where('user_id', $userId)->update($this->profileRow($profile) + ['updated_at' => date('Y-m-d H:i:s')]);
        });
    }

    public function setStatus(int $userId, string $status): void
    {
        $user = $this->users->findById($userId);

        if (!$user instanceof User) {
            throw new DomainRuleViolation('Member not found.');
        }

        $this->atomic(function () use ($userId, $user, $status): void {
            $this->db->table('members')->where('user_id', $userId)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
            $status === 'banned' ? $user->ban('Banned by admin') : $user->unBan();
        });
    }

    public function remove(int $userId): void
    {
        $this->atomic(function () use ($userId): void {
            $ids = array_map(intval(...), array_column(
                $this->db->table('members')->select('user_id')->where('parent_user_id', $userId)->where('deleted_at', null)->get()->getResultArray(),
                'user_id',
            ));
            $ids[] = $userId;

            foreach ($ids as $id) {
                $this->db->table('members')->where('user_id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
                $user = $this->users->findById($id);

                if ($user instanceof User) {
                    $user->ban('Member removed');
                }
            }
        });
    }

    private function plan(int $planId): array
    {
        $plan = $this->db->table('membership_plans')->where('id', $planId)->get()->getRowArray();

        return $plan ?? throw new DomainRuleViolation('Unknown membership plan.');
    }

    private function createLogin(array $account, string $group): User
    {
        $this->users->save(new User([
            'username' => $account['username'],
            'email' => $account['email'],
            'password' => $account['password'],
        ]));

        $user = $this->users->findById($this->users->getInsertID());
        assert($user instanceof User);

        $user->activate();
        $user->addGroup($group);

        return $user;
    }

    private function insertProfile(int $userId, array $profile): void
    {
        $this->db->table('profiles')->insert($this->profileRow($profile) + [
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function profileRow(array $profile): array
    {
        $row = [];

        foreach (self::PROFILE_FIELDS as $field) {
            $value = $profile[$field] ?? null;
            $row[$field] = $value === '' ? null : $value;
        }

        return $row;
    }
}
