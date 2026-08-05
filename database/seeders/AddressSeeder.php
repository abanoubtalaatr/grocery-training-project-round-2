<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {

            Address::create([
                'user_id' => $user->id,
                'label' => 'Home',
                'full_name' => $user->name,
                'phone' => '1012345678',
                'country_code' => '+20',
                'street_address' => '123 Nile Street',
                'building_number' => '10',
                'floor' => '3',
                'apartment' => '12',
                'landmark' => 'Near City Center',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'postal_code' => '11511',
                'country' => 'Egypt',
                'notes' => 'Leave at the door',
                'is_default' => true,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ]);
        }
    }
}