<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DoctorSchedule extends Model
{
    use HasFactory;

    public function doctor(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_id')
            ->withPivot('start_time', 'end_time')
        ;
    }
}
