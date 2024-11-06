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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Clinic extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $guarded = ['id'];

    protected $table = 'clinics';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'clinic_id');
    }
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'clinic_id');
    }

    public function doctorSchedule(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'clinic_id');
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

    public function investigations(): HasMany
    {
        return $this->hasMany(InvestigationClinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    public function pendingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'pending');
    }
    public function consultationTakeMedicine()
    {
        return $this->hasMany(Appointment::class, 'doctor_id')->where('status', 'take-medicine');
    }
    public function completedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'completed');
    }
    public function consultationAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'consultation');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class, 'clinic_id');
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class, 'clinic_id');
    }
    public function injections(): HasMany
    {
        return $this->hasMany(Injection::class, 'clinic_id');
    }

    public function employments(): HasManyThrough
    {
        return $this->hasManyThrough(Employee::class, Doctor::class);
    }

    public function financial(): HasOne
    {
        return $this->hasOne(ClinicFinancial::class, 'clinic_id');
    }
}