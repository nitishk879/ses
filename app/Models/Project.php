<?php

namespace App\Models;

use App\Casts\LanguagesCast;
use App\Enums\CommercialFlow;
use App\Enums\ContractClassificationEnum;
use App\Enums\InterviewEnum;
use App\Enums\LangEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\TradeClassification;
use App\Enums\WorkLocationEnum;
use App\Http\Traits\FormatNumberTrait;
use App\Http\Traits\ProjectTalentMatchingTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, FormatNumberTrait, ProjectTalentMatchingTrait;

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
        "languages",
        "experience",
        "affiliation",
        "number_of_application",
        "number_of_interviewers",
        "commercial_flow",
        "work_location_prefer",
        "person_in_charge",
        "experience",
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
            'commercial_flow' => CommercialFlow::class,
            'number_of_interviewers' => InterviewEnum::class,
            'trade_classification' => TradeClassification::class,
            'contract_classification' => ContractClassificationEnum::class,
            'languages' => LanguagesCast::class,
            'work_location_prefer' => 'array',
            'project_status' => ProjectStatusEnum::class,
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
     * Let's get categories directory
     *
     * @return HasManyThrough
     */
    public function categories(): HasManyThrough
    {
        return $this->hasManyThrough(
            Category::class,
            SubCategory::class,
            'id', // Foreign key on sub_categories table
            'id', // Foreign key on categories table
            'id', // Local key on projects table
            'category_id' // Local key on sub_categories table that points to categories
        );
    }

    /**
     * Let's get all talents related to project
     *
     * @return BelongsToMany
     */
    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class)
            ->withPivot('status', 'interview_count', 'remarks')
            ->withTimestamps();
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
     * Project can Morph to Many industries
     *
     * @return MorphToMany
    */
    public function industries(): MorphToMany
    {
        return $this->morphToMany(Industry::class, 'industriable');
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
     * Let's make project id based on
     * company id, company Owner id and project id
     *
     * @return Attribute
    */
    public function projectId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "PRJ-{$this->company->id}-{$this->company->owner->id}-{$this->id}",
        );
    }

    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
    */
    public function totalInterviews(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->number_of_interviewers ? $this->number_of_interviewers->name : '',
        );
    }

    /**
     * Let's encode/decode all languages for the projects.
     *
     * @return Attribute
    */
    public function languages(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Decode the JSON and map to enum names
                $decoded = json_decode($value, true);
                return array_map(fn($val) => LangEnum::toName($val), $decoded);
            },
            set: function ($value) {
                // If setting from an array of enum values, encode it as JSON
                return $value; //json_encode($value);
            }
        );
    }


    /**
     * Let's encode/decode values
     *
     * @return Attribute
     */
    public function workLocation(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Decode the JSON and map to enum names
                $decoded = json_decode($value, true);
                return array_map(fn($val) => WorkLocationEnum::toName($val), $decoded);
            },
            set: function ($value) {
                // If setting from an array of enum values, encode it as JSON
                return json_encode($value);
            }
        );
    }


    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
     */
//    public function workLocationPreferred(): Attribute
//    {
//        return Attribute::make(
//            get: fn (mixed $value) => $this->work_location_prefer ? WorkLocationEnum::toName($this->work_location_prefer) : '',
//        );
//    }
    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
    */
    public function projectStatus(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->commercial_flow ? $this->commercial_flow->name : '',
        );
    }

    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
    */
    public function projectFlow(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->trade_classification ? $this->trade_classification->name : '',
        );
    }

    /**
     * Contract Classification is contract type in the project
     *
     * @return Attribute
    */
    public function contractType(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->contract_classification ? $this->contract_classification->value : '',
        );
    }
}
