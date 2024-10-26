<?php

namespace App\Models;

use App\Models\Clinic;
use App\Models\InvestigationItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestigationClinic extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function items()
    {
        return $this->hasMany(InvestigationItem::class);
    }
}
