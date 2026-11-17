<?php

namespace App\Rules;

use App\Models\Breeding;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OneActiveBreedingPerSow implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreBreedingId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // let the "required" rule report empties
        }

        $hasActiveBreeding = Breeding::query()
            ->where('sow_id', $value)
            ->whereIn('pregnancy_status', [Breeding::STATUS_PENDING, Breeding::STATUS_CONFIRMED])
            ->when(
                $this->ignoreBreedingId,
                fn ($query) => $query->whereKeyNot($this->ignoreBreedingId)
            )
            ->exists();

        if ($hasActiveBreeding) {
            $fail('This sow already has an active breeding (pending or confirmed pregnancy).');
        }
    }
}
