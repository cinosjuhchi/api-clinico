<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PregnancyCategory extends Model
{
    use HasFactory;

    public function injections()
    {
        return $this->hasMany(Injection::class, 'pregnancy_category_id');
    }
}
