<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationAllergie extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
