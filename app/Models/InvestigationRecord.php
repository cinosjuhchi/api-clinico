<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function itemsRecord()
    {
        return $this->hasMany(ItemRecord::class);
    }
}
