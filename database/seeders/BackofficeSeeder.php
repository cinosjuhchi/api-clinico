<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackofficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@clinico.com.my'], // Cek data berdasarkan email
            [
                'password' => bcrypt('Clinico@00'),
                'role' => 'superadmin',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@clinico.com.my'],
            [
                'password' => bcrypt('Clinico@00'),
                'role' => 'admin',
            ]
        );

        // backoffice - employees
        $superAdminEmployee = DB::table('employees')->insertGetId([
            'image_profile' => null,
            'image_signature' => null,
            'branch' => 'Kuantan',
            'position' => 'Pharmacist',
            'mmc' => 885621,
            'apc' => '1251/2024',
            'staff_id' => 'KHK08',
            'tenure' => 'January 2018 - Present',
            'basic_salary' => 8000.00,
            'elaun' => 1000.00,
        ]);

        $adminEmployee = DB::table('employees')->insertGetId([
            'image_profile' => null,
            'image_signature' => null,
            'branch' => 'Johor Bahru Branch',
            'position' => 'Pharmacist',
            'mmc' => 654321,
            'apc' => '1567/2023',
            'staff_id' => 'KH09',
            'tenure' => 'February 2018 - Present',
            'basic_salary' => 5000.00,
            'elaun' => 500.00,
        ]);

        $userClinic = User::firstOrCreate([
            'email' => 'socmed.clinico@gmail.com',
            'role' => 'clinic',
        ]);
        $clinicMuhara = Clinic::firstOrCreate([
            'name' => "Clinic Muhara Malaysia",
            'user_id' => $userClinic->id,
            'status' => true,
        ]);

        // backoffice - staff
        $superAdminStaff = Staff::create([
            'name' => 'Super Admin',
            'clinic_id' => $clinicMuhara->id,
            'user_id' => $superAdmin->id,
            'employee_id' => $superAdminEmployee,
        ]);

        $adminStaff = Staff::create([
            'name' => 'Admin',
            'clinic_id' => $clinicMuhara->id,
            'user_id' => $admin->id,
            'employee_id' => $adminEmployee,
        ]);

        // backoffice - staff demographics
        DB::table('staff_demographics')->insert([
            'name' => $superAdminStaff->name,
            'birth_date' => '1980-01-01',
            'place_of_birth' => 'Kuala Lumpur',
            'gender' => 'male',
            'marital_status' => 'Married',
            'nric' => '800101-01-1234',
            'address' => '123 Street, Kuala Lumpur',
            'country' => 'Malaysia',
            'postal_code' => 12345,
            'email' => $superAdmin->email,
            'phone_number' => $superAdmin->phone_number,
            'staff_id' => $superAdminStaff->id
        ]);

        DB::table('staff_demographics')->insert([
            'name' => $adminStaff->name,
            'birth_date' => '1995-08-24',
            'place_of_birth' => 'Sabah',
            'gender' => 'female',
            'marital_status' => 'Married',
            'nric' => '800101-01-1235',
            'address' => '321 Street, Kuala Lumpur',
            'country' => 'Malaysia',
            'postal_code' => 12345,
            'email' => $admin->email,
            'phone_number' => $admin->phone_number,
            'staff_id' => $adminStaff->id
        ]);

        // backoffice - contribution
        DB::table('staff_contributions')->insert([
            [
                'kwsp_number' => 12345678,
                'kwsp_amount' => 1000.00,
                'perkeso_number' => 87654321,
                'perkeso_amount' => 500.00,
                'tax_number' => 'TX12345',
                'tax_amount' => 800.00,
                'staff_id' => $superAdminStaff->id,
            ],
            [
                'kwsp_number' => 23456789,
                'kwsp_amount' => 800.00,
                'perkeso_number' => 98765432,
                'perkeso_amount' => 400.00,
                'tax_number' => 'TX54321',
                'tax_amount' => 600.00,
                'staff_id' => $adminStaff->id,
            ],
        ]);

        // backoffice - financial
        DB::table('staff_financial_information')->insert([
            [
                'bank_name' => 'Maybank',
                'account_number' => '123456789',
                'staff_id' => $superAdminStaff->id,
            ],
            [
                'bank_name' => 'CIMB',
                'account_number' => '987654321',
                'staff_id' => $adminStaff->id,
            ],
        ]);
    }
}