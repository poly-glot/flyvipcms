<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Entities\User;
use Config\Auth;

class Account extends BaseController
{
    public function password(): string
    {
        return view('account/password');
    }

    public function updatePassword(): RedirectResponse
    {
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|max_byte[72]|differs[current_password]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return $this->backWithErrors($this->validator?->getErrors() ?? []);
        }

        $user = auth()->user();
        assert($user instanceof User);

        $valid = auth()->check(['email' => (string) $user->email, 'password' => (string) $this->request->getPost('current_password')]);

        if (!$valid->isOK()) {
            return $this->backWithErrors(['current_password' => 'Current password is incorrect.']);
        }

        $strength = new Passwords(config(Auth::class))->check((string) $this->request->getPost('new_password'), $user);

        if (!$strength->isOK()) {
            return $this->backWithErrors(['new_password' => $strength->reason()]);
        }

        $user->fill(['password' => (string) $this->request->getPost('new_password')]);
        auth()->getProvider()->save($user);

        return $this->back('account/password', 'success', 'Password updated.');
    }
}
