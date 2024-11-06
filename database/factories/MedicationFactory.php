<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medication>
 */
class MedicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Ciprofloxacin', 'Metformin', 'Lisinopril', 
            'Simvastatin', 'Amlodipine', 'Metoprolol', 'Atorvastatin', 'Omeprazole', 'Furosemide',
            'Aspirin', 'Azithromycin', 'Doxycycline', 'Levothyroxine', 'Cetirizine', 'Loratadine',
            'Clopidogrel', 'Warfarin', 'Insulin', 'Albuterol', 'Ranitidine', 'Prednisone', 
            'Diazepam', 'Tramadol', 'Hydrochlorothiazide', 'Montelukast', 'Losartan', 'Gabapentin',
            'Sertraline', 'Fluoxetine', 'Clonazepam', 'Atenolol', 'Fexofenadine', 'Sulfamethoxazole',
            'Hydrocodone', 'Oxycodone', 'Pantoprazole', 'Esomeprazole', 'Citalopram', 'Spironolactone',
            'Zolpidem', 'Propranolol', 'Meloxicam', 'Tamsulosin', 'Mirtazapine', 'Valsartan', 'Naproxen', 
            'Lorazepam'
        ];        
         $randomCode = [
            '#001', '#002', '#003', '#004', '#005', '#006', '#007', '#008', '#009', '#010'
         ];
         $randomWord = [
            'MKO', 'NOS', 'MCS', 'SWE', 'FDS', 'MCS', 'SWE', 'FDS'
         ];

        $gram = [
            '100mg', '200mg', '300mg', '400mg', '500mg',
            '600mg', '700mg'
        ];

        return [
            'name' => $this->faker->randomElement($names) . ' ' . $this->faker->randomElement($gram),
            'brand' => fake()->name(),
            'sku_code' => fake()->randomElement($randomCode) . fake()->randomElement($randomWord),  
            'paediatric_dose' => fake()->numberBetween(1, 100),
            'unit' => 'mg/kg',
            'batch' => fake()->numberBetween(1, 100),
            'expired_date' => fake()->date(),
            'total_amount' => fake()->numberBetween(1, 100),
            'pregnancy_category_id' => fake()->numberBetween(1, 6),         
            'price' => fake()->randomFloat(2, 100, 500),
            'manufacture' => fake()->name(),
            'supplier' => fake()->name(),
            'for' => fake()->name(),
        ];
    }
}