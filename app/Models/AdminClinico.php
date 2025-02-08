<?php

namespace App\Models;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Employee;
use App\Models\StaffParent;
use App\Models\StaffSpouse;
use App\Models\StaffChildren;
use App\Models\StaffEducation;
use App\Models\StaffReference;
use App\Models\StaffBasicSkill;
use App\Models\StaffDemographic;
use App\Models\StaffContribution;
use App\Models\StaffEmergencyContact;
use Illuminate\Database\Eloquent\Model;
use App\Models\StaffFinancialInformation;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminClinico extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function demographic(): HasOne
    {
        return $this->hasOne(BoDemographic::class, 'admin_clinico_id');
    }

    public function educational(): HasOne
    {
        return $this->hasOne(BoEducationInformation::class, 'admin_clinico_id');
    }

    public function contributionInfo(): HasOne
    {
        return $this->hasOne(BoContributionInfo::class, 'admin_clinico_id');
    }

    public function emergencyContact(): HasOne
    {
        return $this->hasOne(BoEmergencyContact::class, 'admin_clinico_id');
    }

    public function spouseInformation(): HasOne
    {
        return $this->hasOne(BoSpouseInformation::class, 'admin_clinico_id');
    }

    public function childsInformation(): HasMany
    {
        return $this->hasMany(BoChildren::class, 'admin_clinico_id');
    }

    public function parentInformation(): HasOne
    {
        return $this->hasOne(BoParent::class, 'admin_clinico_id');
    }

    public function reference(): HasOne
    {
        return $this->hasOne(BoReference::class, 'admin_clinico_id');
    }

    public function basicSkills(): HasOne
    {
        return $this->hasOne(BoBasicSkill::class, 'admin_clinico_id');
    }

    public function financialInformation(): HasOne
    {
        return $this->hasOne(BoFinancial::class, 'admin_clinico_id');
    }

    public function employmentInformation(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class, 'admin_clinico_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'admin_id', 'user_id');
    }

}
