<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // upsert requires a unique key; email is unique in users table.
        User::upsert(
            [
                ['user_cd' => 1, 'name' => 'admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')],
                ['user_cd' => 2, 'name' => 'admin2', 'email' => 'admin2@example.com', 'password' => bcrypt('password')],
                ['user_cd' => 3, 'name' => 'admin3', 'email' => 'admin3@example.com', 'password' => bcrypt('password')],
            ],
            ['email']
        );
    }
}
