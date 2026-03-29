<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NoSpam implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */

    protected array $spamWords = ['viagra', 'casino', 'lottery', 'click here', 'gratis'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lowerValue = strtolower($value);

        foreach ($this->spamWords as $spam) {
            if(str_contains($lowerValue, $spam)) {
                $fail("Kolom: attribute mengandung kata spam");
                return;
            }
        }
    }
}
