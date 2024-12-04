<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelIdea\Helper\App\Models\_IH_SubCategory_QB;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'display_order'
    ];

    /**
     * There can can be so many sub-categories
     *
     * @return HasMany
    */
    public function subcategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    /**
     * Let's get all projects through sub-categories
     *
     * @return Builder|HasMany|_IH_SubCategory_QB
     */
    public function projects(): _IH_SubCategory_QB|Builder|HasMany
    {
        return $this->subcategories()->with('projects');
    }

    /**
     * Let's count number of projects under a category through sub-category
     *
     * @return Attribute
     */
    public function totalProjects() :Attribute
    {
        return Attribute::make(
            get: fn() => $this->subcategories->flatMap(function ($subcategory){
                return $subcategory->projects->pluck('id');
            })->unique()->count()
        );
    }

    public function talents()
    {
        return $this->subcategories()->with('talent');
    }

    /**
     * Let's count number of projects under a category through sub-category
     *
     * @return Attribute
     */
    public function totalTalent() :Attribute
    {
        return Attribute::make(
            get: fn() => $this->subcategories->flatMap(function ($subcategory){
                return $subcategory->talents->pluck('id');
            })->unique()->count()
        );
    }
}
