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

        $gram = [
            '100mg', '200mg', '300mg', '400mg', '500mg',
            '600mg', '700mg'
        ];

        return [
            'name' => $this->faker->randomElement($names) . ' ' . $this->faker->randomElement($gram),
            'description' => $this->faker->sentence,
            'stock' => $this->faker->numberBetween(10, 500), // Stok lebih realistis untuk apotek/klinik
            'price' => $this->faker->randomFloat(2, 5, 200) // Harga dalam rentang yang lebih masuk akal, misal Rp 5 - Rp 200
        ];
    }
}