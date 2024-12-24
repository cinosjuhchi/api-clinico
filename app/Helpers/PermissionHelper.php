<?php

namespace App\Helpers;

class PermissionHelper
{
    public static function uploadAttachment($id, $file, $locationTarget)
    {
        $filename = time() . "0" . $id . "." . $file->getClientOriginalExtension();
        $path = $file->storeAs($locationTarget, $filename, 'public');
        return $path;
    }
}
