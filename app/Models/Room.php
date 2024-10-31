<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function occupant(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'occupant_id');
    }
}
