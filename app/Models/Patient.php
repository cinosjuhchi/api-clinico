<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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

    public function demographics(): HasMany
    {
        return $this->hasMany(DemographicInformation::class);
    }

    public function chronics(): HasMany
    {
        return $this->hasMany(ChronicHealthRecord::class);
    }

    public function medications(): HasMany
    {
        return $this->hasMany(MedicationRecord::class);
    }

    public function physicalExaminations(): HasMany
    {
        return $this->hasMany(PhysicalExamination::class);
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(ImmunizationRecord::class);
    }

    public function occupations(): HasMany
    {
        return $this->hasMany(OccupationRecord::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }
}
