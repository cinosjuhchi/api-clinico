<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicExpense extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'addition' => 'array'
    ];

    public function items()
    {
        return $this->hasMany(ClinicExpenseItem::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
