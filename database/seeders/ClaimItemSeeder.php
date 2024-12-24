<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaimItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('claim_items')->insert([
            ['name' => 'PMI'],
            ['name' => 'Medical'],
            ['name' => 'Motor Mileage'],
            ['name' => 'Car Mileage'],
            ['name' => 'Parking'],
            ['name' => 'Toll'],
            ['name' => 'Accomodation'],
            ['name' => 'Meal'],
            ['name' => 'TAXI'],
        ]);
    }
}
