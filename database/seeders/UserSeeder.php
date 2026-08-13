<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the accounts that do NOT need a separate profile table:
 * the administrator and the reception desk staff.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            ['name' => 'Admin User',      'email' => 'admin@clinic.com',      'phone' => '01000000001', 'role' => User::ROLE_ADMIN],
            ['name' => 'Mona Adel',       'email' => 'reception@clinic.com',  'phone' => '01000000002', 'role' => User::ROLE_RECEPTION],
            ['name' => 'Hoda Kamal',      'email' => 'reception2@clinic.com', 'phone' => '01000000003', 'role' => User::ROLE_RECEPTION],
        ];

        foreach ($staff as $person) {
            User::firstOrCreate(['email' => $person['email']], $person + [
                'password' => 'password123',   // the model hashes this automatically
                'status'   => 'active',
            ]);
        }
    }
}
