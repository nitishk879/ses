<?php

namespace App\Models;

use App\Casts\ContractCast;
use App\Enums\AffiliationEnum;
use App\Enums\ContractClassificationEnum;
use App\Enums\ParticipationEnum;
use App\Enums\TalentCharEnum;
use App\Enums\WorkLocationEnum;
use App\Http\Traits\HasTalentDocumentTrait;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Talent extends Model
{
    use HasFactory, HasTalentDocumentTrait;

    protected $fillable = [
        'resume',
        'availability',
        'joining_date',
        'affiliation',
        'quasi_delegation_possible',
        'available_for_contract',
        'available_for_dispatch',
        'request_for_contract',
        'remote_work_preferred',
        'work_location_prefer',
        'experience_pr',
        'experience',
        'qualifications',
        'min_monthly_price',
        'max_monthly_price',
        'other_desire_conditions',
        'characteristics',
        'user_id',
        'company_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'affiliation' => AffiliationEnum::class,
            'participation' => ParticipationEnum::class,
            'contract' => ContractCast::class,
            'contracts' => AsEnumCollection::of(ContractClassificationEnum::class),
            'characteristics' => 'array',
            'work_location' => WorkLocationEnum::class,
        ];
    }

    /**
     * Each talent belong to parent User table
     *
     * @return BelongsTo
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A talent can have multiple sub-categories,
     * from where it belongs to Many of them
     *
     * @return BelongsToMany
     */
    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class)->withTimestamps();
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
     * Get locations for the talent.
     *
     * @return MorphToMany
     */
    public function locations(): MorphToMany
    {
        return $this->morphToMany(Location::class, 'locatable', 'locatable');
    }

    /**
     * Each talent belong to parent Company table
     *
     * @return BelongsTo
    */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Contract type, my current contract
     *
     * @return Attribute
     */
    public function myContract(): Attribute
    {
        return Attribute::get(function () {
            if ($this->available_for_dispatch) {
                return ContractClassificationEnum::OUTSOURCING_CONTRACT->value;
            }

            if ($this->available_for_contract) {
                return ContractClassificationEnum::OUTSOURCING_CONTRACT->value;
            }

            if ($this->quasi_delegation_possible) {
                return ContractClassificationEnum::OUTSOURCING->value;
            }

            return null;
        });
    }

    /**
     * Contract type, my current contract
     *
     * @return Attribute
     */
    public function myParticipation(): Attribute
    {
        return Attribute::get(function () {
            if ($this->affiliation==1){
                return ParticipationEnum::IMMEDIATELY->value;
            }
            elseif($this->affiliation==2){
                return ParticipationEnum::FUTURE->value;
            }
            elseif($this->affiliation==3){
                return ParticipationEnum::FROM_DATE->value;
            }
            return null;
        });
    }

    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
     */
    public function salaryRange(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "{$this->formatNumber($this->min_monthly_price)} - {$this->formatNumber($this->max_monthly_price)}",
        );
    }

    /**
     * Let's encode/decode values
     *
     * @return Attribute
     */
    public function characteristics(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Decode the JSON and map to enum names
                $decoded = json_decode($value, true);
                return array_map(fn($val) => TalentCharEnum::toName($val), $decoded);
            },
            set: function ($value) {
                // If setting from an array of enum values, encode it as JSON
                return json_encode($value);
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
     * Talent can Morph to Many industries
     *
     * @return MorphToMany
     */
    public function industries(): MorphToMany
    {
        return $this->morphToMany(Industry::class, 'industriable');
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