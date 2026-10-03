<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

final class AccessTest extends DatabaseTestCase
{
    use AuthenticationTesting;
    use FeatureTestTrait;

    public function testAnonymousVisitorsAreSentToLogin(): void
    {
        $this->get('admin')->assertRedirectTo(site_url('login'));
        $this->get('portal')->assertRedirectTo(site_url('login'));
        $this->get('/')->assertOK();
    }

    public function testAdminReachesAdminAreaButNotThePortal(): void
    {
        $admin = $this->login('admin');

        $this->actingAs($admin)->get('admin')->assertOK();
        $this->actingAs($admin)->get('admin/members')->assertOK();
        $this->actingAs($admin)->get('portal')->assertRedirect();
    }

    public function testMembersAndSubMembersCannotReachAdmin(): void
    {
        foreach (['member', 'submember'] as $group) {
            $this->actingAs($this->login($group))->get('admin')->assertRedirect();
            $this->actingAs($this->login($group))->get('admin/payments')->assertRedirect();
        }
    }

    public function testHomeRedirectsSignedInUsersByGroup(): void
    {
        $this->actingAs($this->login('admin'))->get('/')->assertRedirectTo(site_url('admin'));
        $this->actingAs($this->login('member'))->get('/')->assertRedirectTo(site_url('portal'));
    }

    public function testRegistrationIsDisabled(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('register');
    }

    public function testLoginPageRenders(): void
    {
        $result = $this->get('login');
        $result->assertOK();
        $result->assertSee('Sign in', 'h1');
    }
}
