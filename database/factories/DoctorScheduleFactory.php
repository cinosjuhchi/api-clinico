<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $day = [
            'sunday',
            'monday',
            // 'tuesday',
            // 'wednesday',
            // 'thursday',
            // 'friday',
            'saturday',            
        ];
        return [
        'day' => $this->faker->randomElement('saturday'),
        'start_time' => $startTime = $this->faker->time(),
        'end_time' => function() use ($startTime) {
            do {
                $endTime = $this->faker->time();
            } while ($endTime <= $startTime);
            return $endTime;
        }
        ];
    }
}
