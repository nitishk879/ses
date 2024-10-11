<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'street_address',
        'city',
        'state',
        'zip_code',
        'country',
        'company_id',
    ];

    /**
     * An address belongs to the company
     *
     * @return BelongsTo
    */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
