<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse|string
    {
        $user = auth()->user();

        if ($user === null) {
            return view('home');
        }

        return redirect()->to($user->inGroup('admin') ? '/admin' : '/portal');
    }
}
