<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class CreateAdmin extends BaseCommand
{
    protected $group = 'App';

    protected $name = 'app:create-admin';

    protected $description = 'Creates an admin user. Password comes from ADMIN_PASSWORD or an interactive prompt.';

    protected $usage = 'app:create-admin <username> <email>';

    protected $arguments = [
        'username' => 'Admin username',
        'email' => 'Admin email (used to sign in)',
    ];

    public function run(array $params): int
    {
        $username = $params[0] ?? CLI::prompt('Username', null, 'required');
        $email = $params[1] ?? CLI::prompt('Email', null, 'required|valid_email');
        $password = (string) (env('ADMIN_PASSWORD') ?: CLI::prompt('Password', null, 'required|min_length[12]'));

        $users = new UserModel();
        $users->save(new User(['username' => $username, 'email' => $email, 'password' => $password]));

        $admin = $users->findById($users->getInsertID());
        assert($admin instanceof User);
        $admin->activate();
        $admin->addGroup('admin');

        CLI::write("Admin {$email} created.", 'green');

        return EXIT_SUCCESS;
    }
}
