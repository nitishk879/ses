<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedTitle;
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
    use HasFactory, HasTranslatedTitle, SoftDeletes;

    /** Shared with SubCategory: one file, keyed by slug. */
    protected const TITLE_TRANSLATIONS = 'common/category';

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
     * Categories a person can actually pick something from.
     *
     * A category with no sub-categories renders as a heading with nothing under
     * it — "ERP (SAP)" and "Project Management" both do, because the dataset
     * never gave them children. That is not a choice, it is a gap in the page,
     * and a recruiter reading it cannot tell whether the options failed to load
     * or do not exist.
     *
     * The talent screens already filtered this way inline; naming the rule here
     * means the project form and the search panels cannot drift from them, and
     * the day those two categories get sub-categories they appear on every
     * screen at once with nothing else to change.
     *
     * Eager-loads the children, because every caller immediately loops them —
     * without it each category costs its own query.
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->whereHas('subcategories')
            ->with('subcategories')
            ->orderBy('display_order');
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
