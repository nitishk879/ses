<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     *
    */
    protected $fillable = [
        'title',
        'slug',
        'locatable_type',
        'locatable_id',
        'country_id'
    ];

    /**
     * A location belongs to a country
     *
     * @return BelongsTo
    */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all the posts that are assigned this tag.
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'locatable', 'locatable');
    }

    /**
     * Let's get all talents from  specified location
     *
     * @return MorphToMany
     */
    public function talents(): MorphToMany
    {
        return $this->morphedByMany(Talent::class, 'locatable', 'locatable');
    }
}
