<?php
namespace App\Service;

use App\Models\User;


class CheckUser
{
    public function checkUserExist($id)
    {
        return User::find($id) !== null;
    }    
}
