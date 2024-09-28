<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FamilyRelationship>
 */
class FamilyRelationshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $relationship = ['Grandpa', 'Grandma', 'Uncle', 'Aunty', 'Brother', 'Sister', 'Father', 'Mother'];
        return [    
            'name' => $this->faker->unique()->randomElement($relationship),
        ];
    }
}
