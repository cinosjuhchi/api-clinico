<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\V1\UserController;


Route::get('/', function (){
    // Di controller atau tempat Anda mengakses file
    dd([
        'file_exists' => Storage::disk('public')->exists('image_profile/Q0UQIET5vipCAsSUNNEc6den3782kZa2hHJ3cZQf.png'),
        'full_path' => storage_path('app/public/image_profile/Q0UQIET5vipCAsSUNNEc6den3782kZa2hHJ3cZQf.png'),
        'storage_url' => Storage::url('image_profile/Q0UQIET5vipCAsSUNNEc6den3782kZa2hHJ3cZQf.png')
    ]);    
});
