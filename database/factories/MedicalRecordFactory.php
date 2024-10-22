<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
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
        return [
            'patient_condition' => $this->faker->sentence(),
            'diagnosis' => $this->faker->name(),                                    
            'consultation_note' => $this->faker->sentence(),
            'physical_examination' => $this->faker->sentence(),
            'blood_pressure' => $this->faker->sentence(),
            'sp02' => $this->faker->numberBetween(70, 100),
            'temperature' => $this->faker->numberBetween(32, 45)
        ];
    }
}