<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineConsultation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function bill()
    {
        return $this->belongsTo(ChatDoctorBill::class);
    }

    public function chats()
    {
        return $this->hasMany(ChatDoctor::class);
    }

    public function patientRelation()
    {
        return $this->belongsTo(User::class, 'patient', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctorRelation()
    {
        return $this->belongsTo(User::class, 'doctor', 'id');
    }

}
