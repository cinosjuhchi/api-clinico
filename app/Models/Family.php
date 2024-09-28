<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Family extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'family_id');
    }
}
