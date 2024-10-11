<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "title",
        "slug",
        "minimum_price",
        "maximum_price",
        "skill_matching",
        "accept",
        "remote_operation_possible",
        "contract_start_date",
        "contract_end_date",
        "possible_to_continue",
        "project_description",
        "personnel_requirement",
        "project_finalized",
        "trade_classification",
        "contract_classification",
        "deadline",
        "affiliation",
        "number_of_application",
        "number_of_interviewers",
        "commercial_flow",
        "person_in_charge",
        "is_public",
        "company_info_disclose",
        "company_id",
        "user_id",
        "updater_id",
        "deleter_id"
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'contract_start_date' => 'datetime',
            'contract_end_date' => 'datetime',
            'affiliation' => 'array',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Project Belongs to company
     *
     * @returns BelongsTo
    */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Project Belongs to Many sub-categories
     *
     * @returns BelongsToMany
    */

    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class)->as('business_area')->withTimestamps();
    }

    /**
     * Project favorite by company can be Many
     *
     * @returns BelongsToMany
    */
    public function favoriteByCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_favorite_project', 'project_id', 'company_id');
    }

    /**
     * Get locations for the talent.
     *
     * @return MorphToMany
     */
    public function locations(): MorphToMany
    {
        return $this->morphToMany(Location::class, 'locatable', 'locatable');
    }

    /**
     * Project has Many Features
     *
     * @return BelongsToMany
    */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    /**
     * Project has Many Features
     *
     * @return HasOne
    */
    public function detail(): HasOne
    {
        return $this->hasOne(ProjectDetail::class);
    }

    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
    */
    public function salaryRange(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "{$this->formatNumber($this->minimum_price)} - {$this->formatNumber($this->maximum_price)}",
        );
    }

    /**
     * Let's format Numbers
     *
     * @param $number
     * @return string
     */
    public function formatNumber($number): string
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B'; // Billions
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M'; // Millions
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K'; // Thousands
        } else {
            return $number; // Less than 1000, no abbreviation
        }
    }
}
