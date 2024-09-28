<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Family;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
use App\Notifications\SetUpProfileNotification;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {        

        Category::factory(6)->create();
        FamilyRelationship::factory(8)->create();
        $clinics = Clinic::factory(10)->create();

        $clinics->each(function ($clinic) {
            // Buat 10 room untuk setiap clinic
            $rooms = Room::factory(3)->create([
                'clinic_id' => $clinic->id
            ]);
            ClinicService::factory(5)->create([
                'clinic_id' => $clinic->id
            ]);
            ClinicSchedule::factory()->create([
                'clinic_id' => $clinic->id
            ]);
            ClinicLocation::factory()->create([
                'clinic_id' => $clinic->id
            ]);
            // Buat 5 doctor untuk setiap room di clinic yang sama
            $rooms->each(function ($room) use ($clinic) {
                $doctors = Doctor::factory(1)->create([
                    'clinic_id' => $clinic->id,
                    'room_id' => $room->id,
                ]);
                $doctors->each(function ($doctor) use ($clinic) {
                    DoctorSchedule::factory(3)->create([
                        'doctor_id' => $doctor->id,
                        'clinic_id' => $clinic->id
                    ]);
                    $user = User::factory(5)->create();
                    $user->each(function ($user) use ($doctor, $clinic) {
                        // Buat doctor setelah user dibuat
                        $family = Family::factory()->create([
                            'user_id' => $user->id
                        ]);
                        $user->notify(new SetUpProfileNotification());                        
                        $patients = Patient::factory()->create([
                            'user_id' => $user->id,
                            'family_id' => $family->id,                            
                        ]);
                        $patients->each(function ($patient) use($doctor, $clinic) { 
                            DemographicInformation::factory()->create([
                                'patient_id' => $patient->id
                            ]);
                            Appointment::factory()->create([
                                'patient_id' => $patient->id,
                                'doctor_id' => $doctor->id,
                                'clinic_id' => $clinic->id,                                
                            ]);
                        });
                    });
                });
            });
        });
        Clinic::factory()->create([
            'name' => "Clinic Muhara Malaysia",
            'email' => "pacino447@gmail.com",            
        ]);
        Doctor::factory()->create([
            'name' => "Muhammad Habibullah Mursalin",
            'email' => "pacino447@gmail.com",
            'phone' => '6287732762247',
            'clinic_id' => 1,
        ]);       
        User::factory()->create([
            'email' => 'muhabibullah186@gmail.com',
            'phone_number' => '6287732762247',
        ])->each(function ($user) {
            // Buat patient setelah user dibuat
            $user->notify(new SetUpProfileNotification());
            $family = Family::factory()->create([
                'user_id' => $user->id
            ]);            
            Patient::factory(3)->create([
                'user_id' => $user->id,
                'family_id' => $family->id,                
            ])->each(function ($patient) {
                DemographicInformation::factory()->create([
                    'patient_id' => $patient->id
                ]);
            });
        });
    }
}
