<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    
    public function demographic(): HasOne
    {
        return $this->hasOne(DemographicInformation::class, 'user_id', 'id');
    }
    public function chronic(): HasOne
    {
        return $this->hasOne(ChronicHealthRecord::class, 'user_id', 'id');
    }
    public function medication(): HasOne
    {
        return $this->hasOne(MedicationRecord::class, 'user_id', 'id');
    }
    public function physical(): HasOne
    {
        return $this->hasOne(PhysicalExamination::class, 'user_id', 'id');
    }
    public function occupation(): HasOne
    {
        return $this->hasOne(OccupationRecord::class, 'user_id', 'id');
    }
    public function immunization(): HasOne
    {
        return $this->hasOne(ImmunizationRecord::class, 'user_id', 'id');
    }
    public function emergency(): HasOne
    {
        return $this->hasOne(EmergencyContactInformation::class, 'user_id', 'id');
    }

}
