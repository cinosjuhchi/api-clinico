<?php

namespace App\Observers;

use App\Models\ReferralCode;
use App\Models\User;
use App\Helpers\ReferralCodeHelper;

class UserObserver
{
    public function created(User $user)
    {
        if ($user->role === 'admin') {
            $code = ReferralCodeHelper::generateCode();

            ReferralCode::create([
                'code' => $code,
                'user_id' => $user->id,
            ]);
        }
    }
}
