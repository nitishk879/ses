<?php

namespace App\Casts;

use App\Enums\LangEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class LanguagesCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?LangEnum
    {
        // Ensure the value is a valid LangEnum value before returning it
        $integer = (int) json_decode($value, true);
        return LangEnum::tryFrom($integer) ?? throw new InvalidArgumentException("Invalid value for language enum.");
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        // Ensure the value is a LangEnum instance before storing
        if (! $value instanceof LangEnum) {
            throw new InvalidArgumentException("The given value is not a valid LangEnum instance.");
        }
        return $value->value;
    }
}
