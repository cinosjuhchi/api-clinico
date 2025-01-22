<?php

namespace App\Models;

use App\Models\User;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\ParentChronic;
use App\Models\EmergencyContact;
use App\Models\MedicationRecord;
use App\Models\OccupationRecord;
use App\Models\ImmunizationRecord;
use App\Models\ChronicHealthRecord;
use App\Models\PhysicalExamination;
use App\Models\DemographicInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function demographics(): HasOne
    {
        return $this->hasOne(DemographicInformation::class);
    }

    public function chronics(): HasMany
    {
        return $this->hasMany(ChronicHealthRecord::class);
    }

    public function medications(): HasMany
    {
        return $this->hasMany(MedicationRecord::class);
    }

    public function physicalExaminations(): HasOne
    {
        return $this->hasOne(PhysicalExamination::class);
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(ImmunizationRecord::class);
    }

    public function occupation(): HasOne
    {
        return $this->hasOne(OccupationRecord::class);
    }

    public function emergencyContact(): HasOne
    {
        return $this->hasOne(EmergencyContact::class);
    }

    public function parentChronic(): HasOne
    {
        return $this->hasOne(ParentChronic::class);
    }

    public function allergy(): HasOne
    {
        return $this->hasOne(Allergy::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function familyRelationship(): BelongsTo
    {
        return $this->belongsTo(FamilyRelationship::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function patientContact()
    {
        return $this->hasOne(PatientContact::class);
    }

    public function bills()
    {
        return $this->hasMany(Billing::class);
    }
}
