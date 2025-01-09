<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientContact extends Model
{
    use HasFactory;

    protected $fillable = [
        "email",
        "phone",
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
