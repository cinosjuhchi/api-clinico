<?php
namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence() . ' Appointment';
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => 'pending',
            'visit_purpose' => fake()->sentence(),
            'current_condition' => fake()->sentence(),
            'waiting_number' => fake()->numberBetween(1, 100),
            'appointment_date' => fake()->date(), // Menghasilkan tanggal antara sekarang hingga 1 tahun ke depan
        ];
    }
}