<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A number the interview call can actually be placed to.
 *
 * "Required" is not enough on its own. A talent record can hold a perfectly
 * present string — an extension, a landline written with a word in it, a
 * mobile missing its leading zero — and every one of those passes a presence
 * check and then fails at dial time, after the candidate has already been
 * emailed, has chosen a slot, and is sitting waiting for a call.
 *
 * So the rule is the same parse the orchestrator performs before dialling
 * ({@see PhoneNumber::parse}). If it cannot produce E.164 here, it will not
 * produce E.164 then, and the right moment to say so is while a person is
 * still looking at the form.
 */
class DialablePhone implements ValidationRule
{
    public function __construct(
        private readonly ?string $defaultRegion = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $region = $this->defaultRegion
            ?? (string) config('services.interview.default_phone_region', 'JP');

        if (PhoneNumber::parse(is_string($value) ? $value : null, $region) === null) {
            $fail(__('validation.dialable_phone'));
        }
    }
}
