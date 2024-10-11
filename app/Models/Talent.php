<?php

namespace App\Models;

use App\Casts\ContractCast;
use App\Enums\AffiliationEnum;
use App\Enums\ContractClassificationEnum;
use App\Enums\ParticipationEnum;
use App\Http\Traits\HasTalentDocumentTrait;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            'characteristics' => 'array'
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
}
