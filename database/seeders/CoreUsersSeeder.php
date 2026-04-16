<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreUsersSeeder extends Seeder
{
    /**
     * Seed only the base administrator account.
     */
    public function run(): void
    {
        $admin = [
            'name' => 'Jesus Rojas',
            'email' => 'jesusrojasbarqto.10@gmail.com',
            'role' => User::ROLE_ADMIN,
        ];

        $user = User::firstOrCreate(
            ['email' => $admin['email']],
            [
                'name' => $admin['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => $admin['role'],
            ],
        );

        $user->forceFill([
            'name' => $admin['name'],
            'role' => $admin['role'],
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }
}
