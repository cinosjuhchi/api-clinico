<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatDoctorBill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function onlineConsultation()
    {
        return $this->hasOne(OnlineConsultation::class);
    }


    

}
