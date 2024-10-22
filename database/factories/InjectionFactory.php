<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Injection>
 */
class InjectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ['Measles, Mumps, and Rubella (MMR)', 
         'Diphtheria, Tetanus, and Pertussis (DTaP)', 
         'Hepatitis B', 
         'Hepatitis A', 
         'Influenza (Flu)', 
         'Human Papillomavirus (HPV)', 
         'Polio', 
         'Varicella (Chickenpox)', 
         'Pneumococcal', 
         'Meningococcal'];
        return [
            'name' => fake()->randomElement($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 1000)
        ];
    }
}
