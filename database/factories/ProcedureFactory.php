<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Procedure>
 */
class ProcedureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ['Physical Examination',

                'Blood Test',

                'X-Ray',

                'MRI Scan',

                'Vaccination',

                'Stitch Removal',

                'ECG',

                'Ultrasound',

                'Blood Pressure Monitoring',

                'Urine Test'];
        return [
            'name' => fake()->randomElement($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 1000)
        ];
    }
}
