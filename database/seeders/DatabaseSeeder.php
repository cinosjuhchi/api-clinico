<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Family;
use App\Models\Patient;
use App\Models\Category;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\ClinicLocation;
use App\Models\ClinicSchedule;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;
use App\Models\FamilyRelationship;
use App\Models\DemographicInformation;

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

        // Buat Users dengan role 'clinic' secara bulk
        $clinics = User::factory(100)->create(['role' => 'clinic'])->map(function ($user) {
            $clinic = Clinic::factory()->create(['user_id' => $user->id]);

            // Buat Rooms, Services, Schedules, dan Locations secara bulk
            Room::factory(3)->create(['clinic_id' => $clinic->id]);
            ClinicService::factory(5)->create(['clinic_id' => $clinic->id]);
            ClinicSchedule::factory()->create(['clinic_id' => $clinic->id]);
            ClinicLocation::factory()->create(['clinic_id' => $clinic->id]);

            return $clinic;
        });

        // Buat Doctors dan relasinya
        foreach ($clinics as $clinic) {
            $rooms = $clinic->rooms; // Ambil rooms dari clinic

            $rooms->each(function ($room) use ($clinic) {
                $doctorUser = User::factory()->create(['role' => 'doctor']);
                $doctor = Doctor::factory()->create([
                    'user_id' => $doctorUser->id,
                    'clinic_id' => $clinic->id,
                    'room_id' => $room->id
                ]);

                // Buat Doctor Schedules secara bulk
                DoctorSchedule::factory(3)->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinic->id]);

                // Buat Patients dan Appointment secara bulk
                $patients = User::factory(5)->create(['role' => 'user'])->map(function ($userPatient) use ($doctor, $clinic) {
                    $family = Family::factory()->create(['user_id' => $userPatient->id]);
                    $patient = Patient::factory()->create(['user_id' => $userPatient->id, 'family_id' => $family->id]);

                    DemographicInformation::factory()->create(['patient_id' => $patient->id]);
                    Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'clinic_id' => $clinic->id]);

                    return $patient;
                });
            });
        }

        // Buat data tambahan untuk clinic, doctor, dan patient tertentu
        $userClinic = User::factory()->create();
        Clinic::factory()->create([
            'name' => "Clinic Muhara Malaysia",
            'user_id' => $userClinic->id
        ]);

        $userDoctor = User::factory()->create([
            'email' => 'pacino447@gmail.com',            
        ]);
        Doctor::factory()->create([
            'name' => "Muhammad Habibullah Mursalin",
            'user_id' => $userDoctor->id,
            'clinic_id' => 1,
        ]);

        $userPatient = User::factory()->create([
            'email' => 'muhabibullah186@gmail.com',            
        ]);
        $family = Family::factory()->create(['user_id' => $userPatient->id]);
        Patient::factory(3)->create(['user_id' => $userPatient->id, 'family_id' => $family->id])->each(function ($patient) {
            DemographicInformation::factory()->create(['patient_id' => $patient->id]);
        });
    }
}
