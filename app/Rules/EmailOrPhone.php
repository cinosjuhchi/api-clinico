<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailOrPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Cek apakah nilai adalah email yang valid
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Cek apakah nilai adalah nomor telepon yang valid
        if (preg_match('/^[0-9]{10,15}$/', $value)) {
            return;
        }

        $fail('The :attribute must be a valid email address or phone number.');
    }
}
