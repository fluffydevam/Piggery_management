<?php

namespace App\Rules;

use App\Models\Litter;
use App\Models\Pig;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The medical log "subject" is polymorphic, so the owner check has to resolve
 * the submitted type first instead of trusting it.
 */
class TreatableBelongsToCurrentUser implements ValidationRule
{
    /** @var list<class-string> */
    private const ALLOWED = [Pig::class, Litter::class];

    public function __construct(private ?string $type) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! in_array($this->type, self::ALLOWED, true)) {
            $fail('The selected subject type is invalid.');

            return;
        }

        if (! $this->type::query()->whereKey($value)->exists()) {
            $fail('The selected :attribute is invalid.');
        }
    }
}
