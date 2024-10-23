<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Industry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "title",
        "slug",
        "description"
    ];

    /**
     * industry can have many projects
     *
     * @return MorphToMany
     */

    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'industriables');
    }

    /**
     * Get all talents that are assigned this industry.
     */
    public function talents(): MorphToMany
    {
        return $this->morphedByMany(Talent::class, 'industriables');
    }
}
