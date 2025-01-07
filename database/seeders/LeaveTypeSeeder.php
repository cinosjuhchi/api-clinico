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
            ],
            [
                'code' => 'CLL',
                'description' => 'Calamity Leave (Disaster)',
            ],
            [
                'code' => 'CPL FAMILY',
                'description' => 'Compassionate Leave - Family',
            ],
            [
                'code' => 'CPL OTHER',
                'description' => 'Compassionate Leave - Others',
            ],
            [
                'code' => 'HL',
                'description' => 'Hospitalization Leave',
            ],
            [
                'code' => 'MRL',
                'description' => 'Marriage Leave',
            ],
            [
                'code' => 'NPL-QSTAFF',
                'description' => 'Unpaid Leave QuickStaff',
            ],
            [
                'code' => 'PTL',
                'description' => 'Paternity Leave',
            ],
            [
                'code' => 'RL',
                'description' => 'Replacement Leave',
            ],
            [
                'code' => 'SL DOCTOR',
                'description' => 'Sick Leave Doctor',
            ],
            [
                'code' => 'STUDY LEAVE',
                'description' => 'Study Leave',
            ],
            [
                'code' => 'TOL',
                'description' => 'Time Off',
            ],
        ]);
    }
}
