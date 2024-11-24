<?php

namespace App\Models;

use App\Models\EmergencyContact;
use App\Models\MedicationRecord;
use App\Models\OccupationRecord;
use Laravel\Sanctum\HasApiTokens;
use App\Models\ImmunizationRecord;
use App\Models\ChronicHealthRecord;
use App\Models\PhysicalExamination;
use App\Models\DemographicInformation;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'user_id', 'id');
    }
    
    public function family(): HasOne
    {
        return $this->hasOne(Family::class);
    }    

    public function clinic(): HasOne
    {
        return $this->hasOne(Clinic::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function bills() 
    { 
        return $this->hasMany(Billing::class, 'user_id'); 
    }

    public function medicalRecords()
    {
        return $this->hasManyThrough(MedicalRecord::class, Patient::class);
    }

    public function requestUpdate()
    {
        return $this->hasMany(ClinicUpdateRequest::class, 'approved_by');
    }
}
