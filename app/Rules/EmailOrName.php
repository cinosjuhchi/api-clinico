<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailOrName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Periksa apakah value adalah nama yang tidak kosong
        if (is_string($value) && !empty(trim($value))) {
            return;
        }

        // Jika tidak valid, panggil closure $fail
        $fail('The :attribute must be a valid email address or a non-empty name.');
    }
}
