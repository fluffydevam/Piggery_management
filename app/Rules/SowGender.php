<?php

namespace App\Rules;

use App\Models\Pig;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SowGender implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $sow = Pig::find($value);

        if (! $sow instanceof Pig) {
            $fail('The selected sow does not exist.');

            return;
        }

        if ($sow->sex !== 'female' || ! in_array($sow->type, [Pig::TYPE_SOW, Pig::TYPE_GILT], true)) {
            $fail('The selected pig must be a female sow or gilt.');

            return;
        }

        if ($sow->status !== Pig::STATUS_ACTIVE) {
            $fail('The selected sow is not active.');
        }
    }
}
