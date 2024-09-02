<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {        
        Category::factory(10)->create();
        Clinic::factory(10)->create();
        Doctor::factory(20)->create();
        Clinic::factory()->create([
            'name' => "Clinic Muhara Malaysia",
            'email' => "pacino447@gmail.com",            
        ]);
        Doctor::factory()->create([
            'name' => "Muhammad Habibullah Mursalin",
            'email' => "pacino447@gmail.com",
            'phone' => '6287732762247',
        ]);
        User::factory()->create([            
            'email' => 'test@example.com',
            'phone_number' => '6287732762247',
        ]);
    }
}
