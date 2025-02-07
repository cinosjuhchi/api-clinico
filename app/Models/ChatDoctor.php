<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatDoctor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function onlineConsultation()
    {
        return $this->belongsTo(OnlineConsultation::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient', 'id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor', 'id');
    }
}
