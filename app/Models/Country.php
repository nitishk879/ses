<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable=[
        'country_name',
        'country_code',
        'currency_code',
        'currency_symbol',
        'phone_code',
    ];

    /**
     * There can can be so many locations/cities/states
     *
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}
