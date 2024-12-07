<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Administration',
            'Cardiology',
            'Dermatology',
            'Dispensary',
            'Emergency Medicine',
            'Endocrinology',
            'Gastroenterology',
            'General Surgery',
            'General Practitioner',
            'Geriatrics',
            'Gynecology/Obstetrics (OB/GYN)',
            'Hematology',
            'Human Resources',
            'Infectious Diseases',
            'Internal Medicine',
            'Locum',
            'Media & Marketing',
            'Nephrology',
            'Neurology',
            'Nursing',
            'Oncology',
            'Orthopedics',
            'Otolaryngology (ENT)',
            'Pediatrics',
            'Pharmacy',
            'Psychiatry',
            'Pulmonology',
            'Radiology',
            'Rheumatology',
            'Stock & Logistic',
            'Urology',
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
