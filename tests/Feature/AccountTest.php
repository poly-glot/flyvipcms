<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

final class AccountTest extends DatabaseTestCase
{
    use AuthenticationTesting;
    use FeatureTestTrait;

    public function testPasswordChangeRequiresLogin(): void
    {
        $this->get('account/password')->assertRedirect();
    }

    public function testUserChangesPasswordWithCorrectCurrentPassword(): void
    {
        $user = $this->login('member');
        $this->actingAs($user)->post('account/password', [
            'current_password' => 'Str0ng-Passw0rd-123',
            'new_password' => 'An0ther-Str0ng-Pass-456',
            'confirm_password' => 'An0ther-Str0ng-Pass-456',
        ]);

        $this->assertTrue(auth()->check(['email' => (string) $user->email, 'password' => 'An0ther-Str0ng-Pass-456'])->isOK());
    }

    public function testWrongCurrentPasswordChangesNothing(): void
    {
        $user = $this->login('admin');
        $this->actingAs($user)->post('account/password', [
            'current_password' => 'not-my-password',
            'new_password' => 'An0ther-Str0ng-Pass-456',
            'confirm_password' => 'An0ther-Str0ng-Pass-456',
        ]);

        $this->assertTrue(auth()->check(['email' => (string) $user->email, 'password' => 'Str0ng-Passw0rd-123'])->isOK());
    }
}
