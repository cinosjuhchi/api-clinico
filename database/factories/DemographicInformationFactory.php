<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DemographicInformation>
 */
class DemographicInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mrn' => 'MRN' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'date_birth' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'nric' => $this->faker->numerify('S######'),
            'address' => $this->faker->address(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->numberBetween(1000, 9999),            
        ];
    }
}
