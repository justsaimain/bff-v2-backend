<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->numberBetween($min = 100000000, $max = 900000000),
            'phone_verified_at' => now(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'region' => $this->faker->randomElement(['Mandalay', 'Yangon', 'Naypyidaw']),
            'fav_team' => $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10]),
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
