<?php

namespace App\Helpers;

class PermissionHelper
{
    public static function uploadAttachment($id, $file, $locationTarget)
    {
        $filename = $id . "_" . time() . "_" . $file->getClientOriginalName();
        $path = $file->storeAs('public/' . $locationTarget, $filename);
        return $path;
    }
}
