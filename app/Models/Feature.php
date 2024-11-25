<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug'
    ];

    /**
     * A Single Feature Can belong to Many Project
     *
     * @return BelongsToMany
    */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }
}
