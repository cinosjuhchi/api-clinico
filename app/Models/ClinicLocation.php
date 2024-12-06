<?php

namespace App\Models;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClinicLocation extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function clinic(): BelongsTo 
    {
        return $this->belongsTo(Clinic::class);
    }
}
