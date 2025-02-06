<?php

namespace App\Helpers;

use App\Models\ReferralCode; // Import model

class ReferralCodeHelper
{
    public static function generateCode()
    {
        $prefix = 'CMSD';
        $codeExists = true;

        while ($codeExists) {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
            $codeExists = ReferralCode::where('code', $code)->exists();
        }

        return $code;
    }
}
