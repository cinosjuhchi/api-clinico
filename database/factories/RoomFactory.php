<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomNames = [
            'Ruang Konsultasi', 'Ruang Periksa', 'Ruang Tindakan', 
            'Ruang UGD', 'Ruang Radiologi', 'Ruang Laboratorium', 
            'Ruang Kesehatan Gigi', 'Ruang USG', 'Ruang Farmasi', 
            'Ruang Rehabilitasi', 'Ruang Laktasi'
        ];

        // Array untuk tipe ruangan
        $roomTypes = ['ICU', 'General', 'Radiologi', 'Laboratorium', 'Kesehatan Gigi', 'Tindakan'];

        return [
            'name' => $this->faker->randomElement($roomNames), // Pilih secara acak dari nama ruangan
            'clinic_id' => $this->faker->numberBetween(1, 10),            
        ];
    }
}
