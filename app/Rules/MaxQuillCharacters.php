<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxQuillCharacters implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __construct(private int $max) {}
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Pastikan value string
        if (! is_string($value)) {
            return;
        }

        // 1️⃣ Hilangkan HTML tag
        $textOnly = trim(strip_tags($value));

        // 2️⃣ Hitung karakter (support emoji & UTF-8)
        $length = mb_strlen($textOnly);

        // 3️⃣ Validasi
        if ($length > $this->max) {
            $fail("Tidak boleh lebih dari {$this->max} karakter.");
        }
    }
}
