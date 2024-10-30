<?php

namespace App\Casts;

use App\Enums\WorkLocationEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class WorkLocationsCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     * @return WorkLocationEnum[]|array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        // Decode JSON from database to an array of integers and map to WorkLocationEnum instances
        $values = json_decode($value, true) ?? [];
        return array_map(fn($val) => WorkLocationEnum::from((int) $val), $values);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('The given value must be an array of integers or WorkLocationEnum instances.');
        }

        // Map values to integers, converting WorkLocationEnum instances and checking validity
        $enumValues = array_map(function ($item) {
            if ($item instanceof WorkLocationEnum) {
                return $item->value;
            }
            if (is_int($item)) {
                return WorkLocationEnum::tryFrom($item)?->value
                    ?? throw new InvalidArgumentException("Invalid work location value: $item");
            }
            throw new InvalidArgumentException('Each item must be an integer or WorkLocationEnum instance.');
        }, $value);

        // Encode the array as JSON for storage in the database
        return json_encode($enumValues);
    }
}
