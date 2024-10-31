<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Clinic;
use App\Models\ClinicLocation;
use App\Models\ClinicSchedule;
use App\Models\ClinicService;
use App\Models\DemographicInformation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\Family;
use App\Models\FamilyRelationship;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PregnancyCategory;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat Category dan FamilyRelationship secara bulk
        Category::factory(6)->create();
        FamilyRelationship::factory(8)->create();

        $pregnancyCategories = [
            [
                'code' => 'A',
                'description' => 'Generally acceptable. Controlled studies in pregnant women show no evidence of fatal risk.',
            ],
            [
                'code' => 'B',
                'description' => 'May be acceptable. Either animal studies show no risk but human studies not available or animal studies showed minor risks and human studies done and showed no risk.',
            ],
            [
                'code' => 'C',
                'description' => 'Use with caution if benefits outweigh risk. Animal studies show risk and human studies not available or neither animal nor human studies done.',
            ],
            [
                'code' => 'D',
                'description' => 'Use in LIFE-THREATENING emergencies when no safer drug available. Positive evidence of human fatal risk.',
            ],
            [
                'code' => 'X',
                'description' => 'Do not use in pregnancy. Risk involved outweigh potential benefits. Safer alternatives exist.',
            ],
            [
                'code' => 'N/A',
                'description' => 'Information Not Available.',
            ],
        ];

        foreach ($pregnancyCategories as $category) {
            PregnancyCategory::create($category);
        }

        // Buat data tambahan untuk clinic, doctor, dan patient tertentu
        $userClinic = User::factory()->create([
            'email' => 'socmed.clinico@gmail.com',
            'role' => 'clinic',
        ]);
        $clinicMuhara = Clinic::factory()->create([
            'name' => "Clinic Muhara Malaysia",
            'user_id' => $userClinic->id,
            'status' => true,
        ]);

        // Buat Rooms, Services, Schedules, dan Locations untuk Clinic Muhara
        ClinicService::factory(5)->create(['clinic_id' => $clinicMuhara->id]);
        ClinicSchedule::factory()->create(['clinic_id' => $clinicMuhara->id]);
        ClinicLocation::factory()->create(['clinic_id' => $clinicMuhara->id]);
        Medication::factory(5)->create(['clinic_id' => $clinicMuhara->id]);
        $employeeDoctor = Employee::factory()->create();

        // Buat Dokter untuk Clinic Muhara
        $userDoctor = User::factory()->create([
            'email' => 'pacino447@gmail.com',
            'role' => 'doctor',
        ]);
        $doctor = Doctor::factory()->create([
            'name' => "Muhammad Habibullah Mursalin",
            'user_id' => $userDoctor->id,
            'clinic_id' => $clinicMuhara->id,
            'employee_id' => $employeeDoctor->id,
        ]);
        $indexRoom = 1;
        $indexRoom = 1;
        $rooms = Room::factory()->count(3)->sequence(function () use (&$indexRoom, $clinicMuhara, $doctor) {return ['clinic_id' => $clinicMuhara->id, 'occupant_id' => $doctor->id, 'room_number' => $indexRoom++];})->create();

        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // Metode untuk membuat jadwal dengan menggunakan ruangan secara bergantian
        $doctorSchedules = collect($daysOfWeek)->map(function ($day) use ($doctor, $clinicMuhara, $rooms) {
            return DoctorSchedule::factory()->create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinicMuhara->id,
                'room_id' => $rooms->random()->id, // Pilih ruangan secara acak
                'day' => $day,
            ]);
        });

        // Buat Pasien dan Appointment di Clinic Muhara
        $userPatient = User::factory()->create([
            'email' => 'muhabibullah186@gmail.com',
        ]);
        $family = Family::factory()->create(['user_id' => $userPatient->id]);
        Patient::factory(3)->create(['user_id' => $userPatient->id, 'family_id' => $family->id])->each(function ($patient) use ($doctor, $clinicMuhara) {
            DemographicInformation::factory()->create(['patient_id' => $patient->id]);
            Appointment::factory()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinicMuhara->id,
            ]);
        });

        User::factory()->create([
            'email' => 'superadmin@clinico.com.my',
            'password' => 'Clinico@00',
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'email' => 'admin@clinico.com.my',
            'password' => 'Clinico@00',
            'role' => 'admin',
        ]);
    }

}
