<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url', 'auth', 'ui'];

    protected function back(string $to, string $type, string $message): RedirectResponse
    {
        return redirect()->to($to)->with($type, $message);
    }

    protected function backWithErrors(array $errors): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }

    protected function memberOptions(array $members, bool $withBalance = false): array
    {
        $points = service('points');
        $options = [];

        foreach ($members as $member) {
            $label = "{$member['first_name']} {$member['last_name']} [{$member['member_code']}]";
            $options[$member['user_id']] = $withBalance ? "{$label} - {$points->balance((int) $member['user_id'])} pts" : $label;
        }

        return $options;
    }

    protected function backWithValidationErrors(): RedirectResponse
    {
        return $this->backWithErrors($this->validator?->getErrors() ?? []);
    }
}
