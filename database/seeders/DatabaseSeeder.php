<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Family;
use App\Models\Patient;
use App\Models\Category;
use App\Models\Injection;
use App\Models\Procedure;
use App\Models\Medication;
use App\Models\Appointment;
use Faker\Provider\Medical;
use App\Models\ClinicService;
use App\Models\MedicalRecord;
use App\Models\ClinicLocation;
use App\Models\ClinicSchedule;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;
use App\Models\PregnancyCategory;
use App\Models\FamilyRelationship;
use App\Models\DemographicInformation;
use App\Models\Employee;

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
                'description' => 'Generally acceptable. Controlled studies in pregnant women show no evidence of fatal risk.'
            ],
            [
                'code' => 'B',
                'description' => 'May be acceptable. Either animal studies show no risk but human studies not available or animal studies showed minor risks and human studies done and showed no risk.'
            ],
            [
                'code' => 'C',
                'description' => 'Use with caution if benefits outweigh risk. Animal studies show risk and human studies not available or neither animal nor human studies done.'
            ],
            [
                'code' => 'D',
                'description' => 'Use in LIFE-THREATENING emergencies when no safer drug available. Positive evidence of human fatal risk.'
            ],
            [
                'code' => 'X',
                'description' => 'Do not use in pregnancy. Risk involved outweigh potential benefits. Safer alternatives exist.'
            ],
            [
                'code' => 'N/A',
                'description' => 'Information Not Available.'
            ]
        ];
    
        foreach ($pregnancyCategories as $category) {
            PregnancyCategory::create($category);
        }
                                            
        // Buat data tambahan untuk clinic, doctor, dan patient tertentu
        $userClinic = User::factory()->create([
            'email' => 'socmed.clinico@gmail.com',            
            'role' => 'clinic'
        ]);
        $clinicMuhara = Clinic::factory()->create([
            'name' => "Clinic Muhara Malaysia",
            'user_id' => $userClinic->id,
            'status' => true
        ]);

        // Buat Rooms, Services, Schedules, dan Locations untuk Clinic Muhara
        Room::factory(3)->create(['clinic_id' => $clinicMuhara->id]);
        ClinicService::factory(5)->create(['clinic_id' => $clinicMuhara->id]);
        ClinicSchedule::factory()->create(['clinic_id' => $clinicMuhara->id]);
        ClinicLocation::factory()->create(['clinic_id' => $clinicMuhara->id]);
        Medication::factory(5)->create(['clinic_id' => $clinicMuhara->id]);
        $employeeDoctor = Employee::factory()->create();

        // Buat Dokter untuk Clinic Muhara
        $userDoctor = User::factory()->create([
            'email' => 'pacino447@gmail.com',
            'role' => 'doctor'
        ]);
        $doctor = Doctor::factory()->create([
            'name' => "Muhammad Habibullah Mursalin",
            'user_id' => $userDoctor->id,
            'clinic_id' => $clinicMuhara->id,
            'employee_id' => $employeeDoctor->id,
            'room_id' => $clinicMuhara->rooms->first()->id // Hubungkan dengan room pertama yang dibuat
        ]);

        // Buat Doctor Schedules untuk dokter di Clinic Muhara
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'sunday']);
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'monday']);
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'tuesday']);
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'wednesday']);
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'thursday']);
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'friday']);
        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id, 'clinic_id' => $clinicMuhara->id, 'day' => 'saturday']);

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
            'role' => 'superadmin'
        ]);

        User::factory()->create([
            'email' => 'admin@clinico.com.my',
            'password' => 'Clinico@00',
            'role' => 'admin'
        ]);
    }

}