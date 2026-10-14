<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => 'user_' . Str::random(8),
            'email' => Str::lower(Str::random(8)) . '@example.com',
            'password' => Hash::make('Password123!'),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'phone' => '+2010' . fake()->numberBetween(10000000, 99999999),
            'is_active' => true,
            'email_verified' => true,
        ];
    }
}
