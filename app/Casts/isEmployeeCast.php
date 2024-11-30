<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class isEmployeeCast implements CastsAttributes
{
    /**
     * Cast the given value.
     * User should have at least talents or has role of talent
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return $model?->company?->talents->count()  >=1 || $model->hasRole('talent');
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
