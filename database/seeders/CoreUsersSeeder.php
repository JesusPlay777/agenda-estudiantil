<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreUsersSeeder extends Seeder
{
    /**
     * Seed base users for each role.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Jesus Rojas',
                'email' => 'jesusrojasbarqto.10@gmail.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'J Rojas Teacher',
                'email' => 'jrojastest777@gmail.com',
                'role' => User::ROLE_TEACHER,
            ],
            [
                'name' => 'Francisco Miranda',
                'email' => 'francisco.francisco.miranda@gmail.com',
                'role' => User::ROLE_STUDENT,
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role' => $data['role'],
                ],
            );

            $user->forceFill([
                'name' => $data['name'],
                'role' => $data['role'],
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }
    }
}
