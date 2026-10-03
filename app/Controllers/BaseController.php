<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url', 'auth'];

    protected function back(string $to, string $type, string $message): RedirectResponse
    {
        return redirect()->to($to)->with($type, $message);
    }

    protected function backWithErrors(array $errors): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}
