<?php

namespace App\Models;

use App\Models\Room;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\ClinicLocation;
use App\Models\ClinicSchedule;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Clinic extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $guarded = ['id'];


    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'clinic_id');
    }
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'clinic_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(ClinicLocation::class, 'clinic_id');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(ClinicSchedule::class, 'clinic_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(ClinicService::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    public function pendingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'pending');
    }

    public function completedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'completed');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class, 'clinic_id');
    }
}