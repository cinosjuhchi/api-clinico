<?php

namespace App\Helpers;

use App\Models\BoExpense;
use App\Models\BoInvoice;
use App\Models\ClinicExpense;
use App\Models\ClinicInvoice;
use InvalidArgumentException;

class GenerateUniqueIdHelper
{
    /**
     * Create a new class instance.
     */
    public static function generateInvoiceId($prefix = 'INC', $type = 'bo')
    {
        $lastInvoiceQuery = match ($type) {
            'bo' => BoInvoice::where('unique_id', 'LIKE', $prefix . '%'),
            'clinic' => ClinicInvoice::where('unique_id', 'LIKE', $prefix . '%'),
            default => throw new InvalidArgumentException("Invalid type: $type")
        };

        $lastInvoice = $lastInvoiceQuery->latest('unique_id')
            ->first();

        if (!$lastInvoice) {
            return $prefix . '00000001';
        }

        // Ambil angka setelah prefix
        $currentNumber = intval(substr($lastInvoice->unique_id, strlen($prefix)));
        $newNumber = $currentNumber + 1;

        // Pastikan angka tetap 8 digit
        return $prefix . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }
    public static function generateExpenseId($prefix = 'EXP', $type = 'bo')
    {
        $lastExpenseQuery = match ($type) {
            'bo' => BoExpense::where('unique_id', 'LIKE', $prefix . '%'),
            'clinic' => ClinicExpense::where('unique_id', 'LIKE', $prefix . '%'),
            default => throw new InvalidArgumentException("Invalid type: $type"),
        };
        $lastExpense = $lastExpenseQuery->latest('unique_id')
            ->first();

        if (!$lastExpense) {
            return $prefix . '00000001';
        }

        // Ambil angka setelah prefix
        $currentNumber = intval(substr($lastExpense->unique_id, strlen($prefix)));
        $newNumber = $currentNumber + 1;

        // Pastikan angka tetap 8 digit
        return $prefix . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

}
