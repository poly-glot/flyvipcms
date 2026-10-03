<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Exceptions\DomainRuleViolation;
use Tests\Support\DatabaseTestCase;

final class MemberServiceTest extends DatabaseTestCase
{
    public function testPrimaryMemberGetsPlanPrefixedCode(): void
    {
        $userId = $this->member(planId: 2, activate: false);
        $member = $this->row('members', ['user_id' => $userId]);

        $this->assertSame(sprintf('FO-%03d', $userId), $member['member_code']);
        $this->assertSame(sprintf('F-%03d', $userId), $member['membership_name']);
        $this->assertSame('inactive', $member['status']);
    }

    public function testPersonalPlanRefusesSubMembers(): void
    {
        $parent = $this->member(planId: 1, activate: false);

        $this->expectException(DomainRuleViolation::class);
        service('members')->addSubMember($parent, $this->subData());
    }

    public function testFamilyPlanAcceptsSubMemberWithRelationshipSuffix(): void
    {
        $parent = $this->member(planId: 2, activate: false);
        $childId = service('members')->addSubMember($parent, $this->subData(contactType: 2));

        $child = $this->row('members', ['user_id' => $childId]);

        $this->assertSame(sprintf('FA-%03d', $childId), $child['member_code']);
        $this->assertSame($parent, (int) $child['parent_user_id']);
        $this->assertNull($child['plan_id']);
    }

    public function testSubMemberLoginsAreInSubmemberGroup(): void
    {
        $parent = $this->member(planId: 3, activate: false);
        $childId = service('members')->addSubMember($parent, $this->subData());

        $user = $this->user($childId);

        $this->assertTrue($user->inGroup('submember'));
        $this->assertFalse($user->inGroup('admin'));
    }

    public function testRemovingParentRemovesChildrenAndBansLogins(): void
    {
        $parent = $this->member(planId: 2, activate: false);
        $childId = service('members')->addSubMember($parent, $this->subData());

        service('members')->remove($parent);

        $this->assertSame(0, $this->db->table('members')->whereIn('user_id', [$parent, $childId])->where('deleted_at', null)->countAllResults());
        $this->assertTrue($this->user($childId)->isBanned());
    }

    public function testBanningAndRestoringStatus(): void
    {
        $userId = $this->member();

        service('members')->setStatus($userId, 'banned');
        $this->assertTrue($this->user($userId)->isBanned());

        service('members')->setStatus($userId, 'active');
        $this->assertFalse($this->user($userId)->isBanned());
    }

    private function subData(int $contactType = 1): array
    {
        static $n = 0;
        ++$n;

        return [
            'username' => "sub{$n}x",
            'email' => "sub{$n}x@example.test",
            'password' => 'Str0ng-Passw0rd-123',
            'contact_type_id' => $contactType,
            'first_name' => 'Sub',
            'last_name' => 'Member',
        ];
    }

    public function testPrimaryMembersExcludeSubMembersAndFilterByActiveStatus(): void
    {
        $parent = $this->member(planId: 2, activate: false);
        $active = $this->member();
        service('members')->addSubMember($parent, $this->subData(contactType: 2));

        $all = array_map(intval(...), array_column(service('members')->primaryMembers(), 'user_id'));
        $activeOnly = array_map(intval(...), array_column(service('members')->primaryMembers(true), 'user_id'));

        $this->assertEqualsCanonicalizing([$parent, $active], $all);
        $this->assertSame([$active], $activeOnly);
    }
}
