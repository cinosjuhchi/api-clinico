<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function demographic(): HasOne
    {
        return $this->hasOne(StaffDemographic::class, 'staff_id');
    }

    public function educational(): HasOne
    {
        return $this->hasOne(StaffEducation::class, 'staff_id');
    }

    public function contributionInfo(): HasOne
    {
        return $this->hasOne(StaffContribution::class, 'staff_id');
    }

    public function emergencyContact(): HasOne
    {
        return $this->hasOne(StaffEmergencyContact::class, 'staff_id');
    }

    public function spouseInformation(): HasOne
    {
        return $this->hasOne(StaffSpouse::class, 'staff_id');
    }

    public function childsInformation(): HasMany
    {
        return $this->hasMany(StaffChildren::class, 'staff_id');
    }

    public function parentInformation(): HasOne
    {
        return $this->hasOne(StaffParent::class, 'staff_id');
    }

    public function reference(): HasOne
    {
        return $this->hasOne(StaffReference::class, 'staff_id');
    }

    public function basicSkills(): HasOne
    {
        return $this->hasOne(StaffBasicSkill::class, 'staff_id');
    }

    public function financialInformation(): HasOne
    {
        return $this->hasOne(StaffFinancialInformation::class, 'staff_id');
    }

    public function employmentInformation(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function staffClinicSchedule(): HasMany
    {
        return $this->hasMany(StaffClinicSchedule::class);
    }

    public function staffSchedule(): HasOne
    {
        return $this->hasOne(StaffSchedule::class);
    }
}
