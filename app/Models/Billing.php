<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function injections()
    {
        return $this->hasMany(InjectionRecord::class, 'billing_id');
    }

    public function procedures()
    {
        return $this->hasMany(ProcedureRecord::class, 'billing_id');
    }

    public function medications()
    {
        return $this->hasMany(MedicationRecord::class, 'billing_id');
    }

    public function service()
    {
        return $this->hasOne(ServiceRecord::class,'billing_id');
    }

    public function investigations()
    {
        return $this->hasMany(InvestigationRecord::class, 'billing_id');
    }

}
