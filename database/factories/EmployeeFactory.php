<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch' => $this->faker->word,
            'position' => $this->faker->word,
            'mmc' => $this->faker->randomNumber(),
            'apc' => $this->faker->word,
            'staff_id' => $this->faker->word,
            'tenure' => $this->faker->word,
            'basic_salary' => $this->faker->numberBetween(100000, 200000),
            'elaun' => $this->faker->numberBetween(10000, 20000),
        ];
    }
}
