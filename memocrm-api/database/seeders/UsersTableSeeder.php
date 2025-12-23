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
                ['name' => 'admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')],
                ['name' => 'admin2', 'email' => 'admin2@example.com', 'password' => bcrypt('password')],
                ['name' => 'admin3', 'email' => 'admin3@example.com', 'password' => bcrypt('password')],
            ],
            ['email']
        );
    }
}
