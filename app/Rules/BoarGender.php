<?php

namespace App\Rules;

use App\Models\Pig;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BoarGender implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $boar = Pig::find($value);

        if (! $boar instanceof Pig) {
            $fail('The selected boar does not exist.');

            return;
        }

        if ($boar->sex !== 'male' || $boar->type !== Pig::TYPE_BOAR) {
            $fail('The selected pig must be a boar.');

            return;
        }

        if ($boar->status !== Pig::STATUS_ACTIVE) {
            $fail('The selected boar is not active.');
        }
    }
}
