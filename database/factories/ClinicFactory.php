<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company() . ' Clinic'; // Menghasilkan nama
        return [
            'name' => $name, // Menyimpan nama ke dalam array            
            'slug' => Str::slug($name), // Menggunakan nama untuk membuat slug
            'description' => fake()->paragraph(),
            'address' => fake()->address(),            
        ];
    }
}
