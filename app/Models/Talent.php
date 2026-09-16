<?php

namespace App\Models;

use App\Casts\AffiliationCast;
use App\Casts\ContractCast;
use App\Casts\WorkLocationCast;
use App\Casts\WorkLocationsCast;
use App\Enums\AffiliationEnum;
use App\Enums\ContractClassificationEnum;
use App\Enums\ParticipationEnum;
use App\Enums\TalentCharEnum;
use App\Enums\WorkLocationEnum;
use App\Http\Traits\FormatNumberTrait;
use App\Http\Traits\HasTalentDocumentTrait;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Talent extends Model
{
    use HasFactory, SoftDeletes, FormatNumberTrait, HasTalentDocumentTrait;

    protected $fillable = [
        'resume',
        'availability',
        'joining_date',
        'affiliation',
        'cover_letter',
        'privacy',
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
            'affiliation' => AffiliationCast::class,
            'participation' => ParticipationEnum::class,
            'contract' => ContractCast::class,
            'contracts' => AsEnumCollection::of(ContractClassificationEnum::class),
            'characteristics' => 'array',
            'work_location_prefer' => 'array',
            'availability' => ParticipationEnum::class,
            'joining_date' => 'datetime',
            'privacy' => 'boolean',
        ];
    }

    /**
     * Each talent belong to parent User table
     *
     * @return BelongsTo
    */
    /**
     * The candidate's number in a form a carrier will route, or null.
     *
     * The phone lives on `users`, not on `talent` — there is no phone column
     * here at all — and every value in the table today is a bare local string
     * that Twilio rejects. Normalising is therefore not a nicety: without it
     * the automated interview cannot dial a single existing candidate.
     *
     * Null means "not dialable", which the orchestrator treats as a reason to
     * fail the attempt with a message a recruiter can act on rather than as a
     * call worth attempting.
     */
    public function interviewPhone(?string $defaultRegion = null): ?PhoneNumber
    {
        $this->loadMissing('user');

        return PhoneNumber::parse(
            $this->user?->phone,
            $defaultRegion ?? config('services.interview.default_phone_region', 'JP')
        );
    }

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
     * The structured form of this candidate's resume.
     *
     * @return HasOne
     */
    public function aiResumeParse(): HasOne
    {
        return $this->hasOne(AiResumeParse::class);
    }

    /**
     * Match scores for this candidate across projects.
     *
     * @return HasMany
     */
    public function aiMatches(): HasMany
    {
        return $this->hasMany(AiMatch::class);
    }

    /**
     * Let's get all Projects related to the talent
     *
     * @return BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('status', 'interview_count', 'remarks')
            ->withTimestamps();
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
                return ContractClassificationEnum::DISPATCH_CONTRACT->value;
            }

            if ($this->available_for_contract) {
                return ContractClassificationEnum::OUTSOURCING_CONTRACT->value;
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
//    public function affiliation(): Attribute
//    {
//        return Attribute::make(
//            get: fn (mixed $value) => $value ? AffiliationEnum::toName($this->affiliation->value) : '',
//        );
//    }

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
                return array_map(fn(int $val) => TalentCharEnum::toName($val), $decoded);
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
            get: function () {
                // Tolerant of what is actually in the column, not only of what
                // the form posts. `work_location_prefer` is cast to array, but
                // rows exist holding a bare scalar (the factory writes one, and
                // so did an older form), and array_map over an int is a
                // TypeError — which took out the whole profile modal, not just
                // this one line of it.
                $values = $this->work_location_prefer;

                if (blank($values)) {
                    return [];
                }

                return array_values(array_filter(array_map(
                    fn ($val) => WorkLocationEnum::toName($val),
                    is_array($values) ? $values : [$values]
                )));
            },
            set: function ($value) {
                // If setting from an array of enum values, encode it as JSON
                return json_encode($value);
            }
        );
    }

    /**
     * Always an array of location ids, whatever the row happens to hold.
     *
     * Older rows store a bare scalar (`'3'`) rather than a list (`'[3]'`), so
     * the `array` cast alone hands callers an int. `in_array()` and `foreach`
     * over an int are fatal, which is what took out the talent edit form.
     *
     * Read-only on purpose: the cast still encodes on write, and this replaces
     * a snake_case `work_location_prefer(): Attribute` that Eloquent could
     * never call — it looks accessors up by camelCase, so toArray() reached for
     * `workLocationPrefer()` and died. Naming it correctly both fixes that and
     * gives every caller the same shape.
     */
    public function workLocationPrefer(): Attribute
    {
        return Attribute::get(function (mixed $value): array {
            $decoded = is_string($value) ? json_decode($value, true) : $value;

            if (blank($decoded)) {
                return [];
            }

            return array_values(is_array($decoded) ? $decoded : [$decoded]);
        });
    }

//    /**
//     * Let's fetch salary range min-max
//     *
//     * @return Attribute
//     */
//    public function workLocationPreferred(): Attribute
//    {
//        return Attribute::make(
//            get: fn (mixed $value) => $this->work_location_prefer ? WorkLocationEnum::toName($this->work_location_prefer) : '',
//        );
//    }

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
     * Let's get list of user's Fav talent
     *
     * @return BelongsToMany
     */
    public function favourite(): BelongsToMany
    {
        return $this->morphToMany(User::class, 'favourite_talent');
    }

    /**
     * Talent can has many interviews
     * @return Talent|HasMany
     */
    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }
}
