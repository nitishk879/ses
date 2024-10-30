<?php

namespace App\Casts;

use App\Enums\AffiliationEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AffiliationCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     * @throws \Exception
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return AffiliationEnum::toName($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
