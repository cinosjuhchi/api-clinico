<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('leave_types')->insert([
            [
                'code' => 'AL',
                'description' => 'Annual Leave',
                'year_ent' => 24,
            ],
            [
                'code' => 'CLL',
                'description' => 'Calamity Leave (Disaster)',
                'year_ent' => 4,
            ],
            [
                'code' => 'CPL FAMILY',
                'description' => 'Compassionate Leave - Family',
                'year_ent' => 4,
            ],
            [
                'code' => 'CPL OTHER',
                'description' => 'Compassionate Leave - Others',
                'year_ent' => 4,
            ],
            [
                'code' => 'HL',
                'description' => 'Hospitalization Leave',
                'year_ent' => 4,
            ],
            [
                'code' => 'MRL',
                'description' => 'Marriage Leave',
                'year_ent' => 3,
            ],
            [
                'code' => 'NPL-QSTAFF',
                'description' => 'Unpaid Leave QuickStaff',
                'year_ent' => 5,
            ],
            [
                'code' => 'PTL',
                'description' => 'Paternity Leave',
                'year_ent' => 3,
            ],
            [
                'code' => 'RL',
                'description' => 'Replacement Leave',
                'year_ent' => 2,
            ],
            [
                'code' => 'SL DOCTOR',
                'description' => 'Sick Leave Doctor',
                'year_ent' => 3,
            ],
            [
                'code' => 'STUDY LEAVE',
                'description' => 'Study Leave',
                'year_ent' => 6,
            ],
            [
                'code' => 'TOL',
                'description' => 'Time Off',
                'year_ent' => 9,
            ],
        ]);
    }
}
