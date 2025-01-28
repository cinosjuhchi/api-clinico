<?php

namespace App\Models;

use App\Models\Appointment;
use App\Models\ClinicLocation;
use App\Models\ClinicSchedule;
use App\Models\ClinicService;
use App\Models\Doctor;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Clinic extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $guarded = ['id'];

    protected $table = 'clinics';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function doctorUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class, // Target model
            Doctor::class, // Intermediate model
            'clinic_id', // Foreign key on doctors table
            'id', // Foreign key on users table
            'id', // Local key on clinics table
            'user_id' // Local key on doctors table
        )->where('users.role', 'doctor');
    }
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'clinic_id');
    }
    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class, 'clinic_id');
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
    public function consultationTakeMedicine(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'take-medicine');
    }
    public function completedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'completed');
    }
    public function consultationAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'consultation');
    }
    public function onConsultationAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id')->where('status', 'on-consultation');
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

    public function doctorEmployments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Employee::class, // Model tujuan akhir
            Doctor::class, // Model perantara
            'clinic_id', // Foreign key di tabel doctors yang merujuk ke clinic
            'id', // Foreign key di tabel employees
            'id', // Primary key di tabel clinics
            'employee_id' // Foreign key di tabel doctors yang merujuk ke employees
        );
    }

    public function staffEmployments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Employee::class, // Model tujuan akhir
            Staff::class, // Model perantara
            'clinic_id', // Foreign key di tabel staff yang merujuk ke clinic
            'id', // Foreign key di tabel employees
            'id', // Primary key di tabel clinics
            'employee_id' // Foreign key di tabel staff yang merujuk ke employees
        );
    }

    public function financial(): HasOne
    {
        return $this->hasOne(ClinicFinancial::class, 'clinic_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Billing::class, 'clinic_id');
    }

    public function requestUpdate()
    {
        return $this->hasMany(ClinicUpdateRequest::class, 'clinic_id');
    }

    public function images()
    {
        return $this->hasMany(ClinicImage::class, 'clinic_id');
    }

    public function leaveTypeDetails()
    {
        return $this->hasMany(LeaveTypeDetail::class);
    }

    public function settlements()
    {
        return $this->hasMany(ClinicSettlement::class);
    }
}
