<?php

namespace App\Helpers;

use App\Models\Employee;

class GenerateStaffIdHelper
{
    /**
     * Create a new class instance.
     */
    public static function generate($prefix = 'KH')
    {
        $lastEmployee = Employee::orderBy('staff_id', 'desc')
            ->where('staff_id', 'LIKE', $prefix . '%')
            ->first();

        if (!$lastEmployee) {
            return $prefix . '01';
        }

        $currentNumber = intval(substr($lastEmployee->staff_id, 2));
        $newNumber = $currentNumber + 1;

        return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }
}
