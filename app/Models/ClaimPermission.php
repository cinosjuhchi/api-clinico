<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clinic_id',
        'claim_item_id',
        'month',
        'amount',
        'attachment',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claimItem()
    {
        return $this->belongsTo(ClaimItem::class);
    }
}
