<?php

namespace App\Helpers;

use App\Models\BoExpense;
use App\Models\BoInvoice;

class GenerateUniqueIdHelper
{
    /**
     * Create a new class instance.
     */
    public static function generateInvoiceId($prefix = 'INC')
    {
        $lastInvoice = BoInvoice::where('unique_id', 'LIKE', $prefix . '%')
            ->latest('unique_id')
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
    public static function generateExpenseId($prefix = 'EXP')
    {
        $lastExpense = BoExpense::where('unique_id', 'LIKE', $prefix . '%')
            ->latest('unique_id')
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
