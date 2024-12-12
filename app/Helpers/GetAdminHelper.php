<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class GetAdminHelper
{
    /**
     * Create a new class instance.
     */
    public static function getAdminData()
    {
        $user = Auth::user();   
        if($user->role == 'superadmin' || $user->role == 'admin')
        {            
            return $user;
        }
        return null;
    }
    
}
