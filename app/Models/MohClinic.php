<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MohClinic extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
