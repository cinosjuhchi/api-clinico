<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicService>
 */
class ClinicServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            'X-ray',
            'General Consultation',
            'Pediatrics',
            'Dermatology',
            'Cardiology',
            'Dentistry',
            'Orthopedics',
            'Gynecology',
            'Ophthalmology',
            'Physiotherapy',
            'Radiology',
            'ENT (Ear, Nose, Throat)',
            'Neurology',
            'Psychiatry',
            'Oncology',
            'Urology',
            'Nutrition Counseling',
            'Immunization Services',
            'Emergency Care',
            'Laboratory Services',
            'Pharmacy'
        ];

        return [
            'name' => $this->faker->randomElement($services),  
            'price' => $this->faker->numberBetween(1000, 10000),
            'category_id' => $this->faker->numberBetween(1, 6),
            'description' => $this->faker->sentence(),
        ];
    }
}
