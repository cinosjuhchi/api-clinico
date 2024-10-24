<?php

namespace App\Models;

use App\Models\Room;
use App\Models\Clinic;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\DoctorReference;
use App\Models\DoctorBasicSkill;
use App\Models\DoctorDemographic;
use Laravel\Sanctum\HasApiTokens;
use App\Models\DoctorContribution;
use App\Models\DoctorEmergencyContact;
use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorEducationalInformation;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctor extends Authenticatable
{
    use HasFactory, HasApiTokens;

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

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
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
        return $this->hasMany(Appointment::class, 'doctor_id')->where('status', 'consultation');
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
        return $this->belongsTo(Employee::class);
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

    public function spouseInformartion(): HasOne
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


}
