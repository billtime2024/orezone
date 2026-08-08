<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@oruzone.com'],
            [
                'name' => 'Admin',
                'phone' => '9000000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'is_admin' => true,
            ]
        );

        if (!$admin->profile) {
            UserProfile::create(['user_id' => $admin->id]);
        }
        if (!$admin->wallet) {
            Wallet::create(['user_id' => $admin->id, 'balance' => 0]);
        }

        // Regular user
        $user = User::firstOrCreate(
            ['email' => 'user@oruzone.com'],
            [
                'name' => 'Test User',
                'phone' => '9000000002',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'is_admin' => false,
            ]
        );

        if (!$user->profile) {
            UserProfile::create(['user_id' => $user->id]);
        }
        if (!$user->wallet) {
            Wallet::create(['user_id' => $user->id, 'balance' => 1000]);
        }

        $this->command->info('Admin: admin@oruzone.com / password');
        $this->command->info('User:  user@oruzone.com / password');
    }
}
