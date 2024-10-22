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
         $randomCode = [
            '#001', '#002', '#003', '#004', '#005', '#006', '#007', '#008', '#009', '#010'
         ];
         $randomWord = [
            'MKO', 'NOS', 'MCS', 'SWE', 'FDS', 'MCS', 'SWE', 'FDS'
         ];
        return [
            'name' => fake()->randomElement($name),
            'brand' => fake()->name(),
            'sku_code' => fake()->randomElement($randomCode) . fake()->randomElement($randomWord),  
            'paediatric_dose' => fake()->numberBetween(1, 100),
            'unit' => 'mg/kg',
            'batch' => fake()->numberBetween(1, 100),
            'expired_date' => fake()->date(),
            'total_amount' => fake()->numberBetween(1, 100),
            'pregnancy_category_id' => fake()->numberBetween(1, 6),         
            'price' => fake()->randomFloat(2, 100, 1000)
        ];
    }
}
