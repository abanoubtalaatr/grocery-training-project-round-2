<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentUserSeeder extends Seeder
{
    private const DEV_PASSWORD = 'password';

    public function run(): void
    {
        $this->createCustomer();
        $this->createAdmin();
        $this->createDriver();
        $this->createSupport();

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info(' Development Users Seeded');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Customer', 'dev_customer@gmail.com', self::DEV_PASSWORD],
                ['Admin', 'admin@example.test', self::DEV_PASSWORD],
                ['Driver', 'driver@example.test', self::DEV_PASSWORD],
                ['Support Agent', 'support@example.test', self::DEV_PASSWORD],
            ]
        );
    }

    private function createCustomer(): void
    {
        $customer = User::updateOrCreate(
            ['email' => 'dev_customer@gmail.com'],
            [
                'username' => 'dev_customer',
                'firstname' => 'Dev',
                'lastname' => 'Customer',
                'phone' => '+201111111111',
                'password' => Hash::make(self::DEV_PASSWORD),
                'email_verified' => true,
                'email_verified_at' => now(),
                'agree_terms' => true,
                'is_active' => true,
                'is_admin' => false,
                'role' => 'customer',
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer->id, 'label' => 'Home'],
            [
                'full_name' => 'Dev Customer',
                'phone' => '1000000001',
                'country_code' => '+20',
                'street_address' => '123 Test Street',
                'building_number' => '10',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'Egypt',
                'is_default' => true,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ]
        );
    }

    private function createAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.test'],
            [
                'username' => 'dev_admin',
                'firstname' => 'Dev',
                'lastname' => 'Admin',
                'password' => Hash::make(self::DEV_PASSWORD),
                'email_verified' => true,
                'email_verified_at' => now(),
                'agree_terms' => true,
                'is_active' => true,
                'is_admin' => true,
                'role' => 'admin',
            ]
        );
    }

    private function createDriver(): void
    {
        User::updateOrCreate(
            ['email' => 'driver@example.test'],
            [
                'username' => 'dev_driver',
                'firstname' => 'Dev',
                'lastname' => 'Driver',
                'phone' => '+201000000002',
                'password' => Hash::make(self::DEV_PASSWORD),
                'email_verified' => true,
                'email_verified_at' => now(),
                'agree_terms' => true,
                'is_active' => true,
                'is_admin' => false,
                'role' => 'delivery_partner',
            ]
        );
    }

    private function createSupport(): void
    {
        User::updateOrCreate(
            ['email' => 'support@example.test'],
            [
                'username' => 'dev_support',
                'firstname' => 'Dev',
                'lastname' => 'Support',
                'password' => Hash::make(self::DEV_PASSWORD),
                'email_verified' => true,
                'email_verified_at' => now(),
                'agree_terms' => true,
                'is_active' => true,
                'is_admin' => false,
                'role' => 'support_agent',
            ]
        );
    }
}
