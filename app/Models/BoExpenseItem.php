<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoExpenseItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function expense()
    {
        return $this->belongsTo(BoExpense::class);
    }
}
