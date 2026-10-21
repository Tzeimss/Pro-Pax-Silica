<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialAdmins extends Seeder
{
    public function run()
    {
        $founderPassword = env('SUNSON_FOUNDER_ADMIN_PASSWORD');
        $itPassword = env('SUNSON_IT_ADMIN_PASSWORD');

        if (! is_string($founderPassword) || strlen($founderPassword) < 12
            || ! is_string($itPassword) || strlen($itPassword) < 12
        ) {
            throw new \RuntimeException(
                'Set distinct SUNSON_FOUNDER_ADMIN_PASSWORD and SUNSON_IT_ADMIN_PASSWORD values '
                . 'of at least 12 characters in .env before seeding administrators.'
            );
        }

        $accounts = [
            [
                'first_name' => 'Katherine',
                'last_name' => 'Sinagaraw',
                'birthday' => '1990-07-01',
                'email' => 'katherine.sinagaraw@sunsonsolar.com',
                'username' => 'KittyKat16',
                'phone' => 'Company Phone',
                'password' => $founderPassword,
            ],
            [
                'first_name' => 'Sol',
                'last_name' => 'Solis',
                'birthday' => '1967-01-08',
                'email' => 'sol.solis@sunsonsolar.com',
                'username' => 'admin',
                'phone' => 'Company Phone',
                'password' => $itPassword,
            ],
        ];

        $users = $this->db->table('users');
        foreach ($accounts as $account) {
            $conflict = $users->groupStart()->where('username', $account['username'])->orWhere('email', $account['email'])->groupEnd()->countAllResults();
            if ($conflict) {
                echo 'Skipped bootstrap admin ' . $account['username'] . ': a username or email conflict already exists.' . PHP_EOL;
                continue;
            }

            $users->insert([
                'first_name' => $account['first_name'],
                'last_name' => $account['last_name'],
                'middle_name' => null,
                'birthday' => $account['birthday'],
                'gender' => 'prefer-not-to-say',
                'email' => $account['email'],
                'phone' => $account['phone'],
                'address' => 'Not provided',
                'username' => $account['username'],
                'password_hash' => password_hash($account['password'], PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => 1,
                'must_change_password' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
