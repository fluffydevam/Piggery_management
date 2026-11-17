<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Guards against IDOR: a form post can carry any foreign key, and query
 * scoping alone does not stop someone from submitting another farm's id.
 *
 * The model's global user scope does the real work here: if the row belongs
 * to a different account it simply will not be found.
 */
class OwnedByCurrentUser implements ValidationRule
{
    /**
     * @param  class-string<Model>  $model
     */
    public function __construct(private string $model) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! $this->model::query()->whereKey($value)->exists()) {
            $fail('The selected :attribute is invalid.');
        }
    }
}
