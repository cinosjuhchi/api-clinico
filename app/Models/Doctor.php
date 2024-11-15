<?php

namespace App\Models;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Clinic;
use App\Models\DoctorBasicSkill;
use App\Models\DoctorContribution;
use App\Models\DoctorDemographic;
use App\Models\DoctorEducationalInformation;
use App\Models\DoctorEmergencyContact;
use App\Models\DoctorReference;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $guarded = ['id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function roomsOccupant(): HasMany
    {
        return $this->hasMany(Room::class, 'doctor');
    }

    public function doctorSchedules(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'doctor_schedules')
            ->withPivot('start_time', 'end_time');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
    public function pendingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id')->where('status', 'pending');
    }
    public function consultationAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id')
            ->where('status', 'consultation')
            ->orWhere('status', 'on-consultation');
    }

    public function consultationTakeMedicine()
    {
        return $this->hasMany(Appointment::class, 'doctor_id')->where('status', 'take-medicine');
    }
    public function completedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id')->where('status', 'completed')->orWhere('status', 'take-medicine')
            ->orWhere('status', 'waiting-payment');
    }

    public function demographic(): HasOne
    {
        return $this->hasOne(DoctorDemographic::class);
    }

    public function educational(): HasOne
    {
        return $this->hasOne(DoctorEducationalInformation::class);
    }

    public function reference(): HasOne
    {
        return $this->hasOne(DoctorReference::class);
    }

    public function employmentInformation(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function basicSkills(): HasOne
    {
        return $this->hasOne(DoctorBasicSkill::class);
    }

    public function contributionInfo(): HasOne
    {
        return $this->hasOne(DoctorContribution::class);
    }

    public function emergencyContact(): HasOne
    {
        return $this->hasOne(DoctorEmergencyContact::class);
    }

    public function spouseInformation(): HasOne
    {
        return $this->hasOne(DoctorSpouse::class);
    }

    public function childsInformation(): HasMany
    {
        return $this->hasMany(DoctorChild::class);
    }

    public function parentInformation(): HasOne
    {
        return $this->hasOne(DoctorParent::class);
    }

    public function financialInformation(): HasOne
    {
        return $this->hasOne(FinancialInformation::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Billing::class, 'doctor_id');
    }
}
