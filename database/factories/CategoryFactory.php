<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $specializations = [
            'Cardiologist',
            'Dentist',
            'Neurologist',
            'Orthopedic',
            'Pediatrician',
            'Dermatologist'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($specializations),
        ];
    }
}
