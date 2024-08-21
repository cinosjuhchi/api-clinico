<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'clinic_id' => fake()->numberBetween(1, 10), // Adjust range as needed
            'category_id' => fake()->numberBetween(1, 5), // Adjust range as needed
            'email' => fake()->safeEmail(),
            'password' => bcrypt('password'),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
