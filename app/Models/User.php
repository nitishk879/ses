<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\isAdminCast;
use App\Casts\isEmployeeCast;
use App\Casts\isEmployerCast;
use App\Casts\LanguagesCast;
use App\Enums\GenderEnum;
use App\Enums\LangEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'firstname',
        'lastname',
        'email',
        'username',
        'phone',
        'date_of_birth',
        'gender',
        'nationality',
        'country',
        'languages',
        'nearest_station_prefecture',
        'nearest_station_line',
        'station_name',
        'is_public',
        'address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'datetime',
            'gender' => GenderEnum::class,
            'nationality' => 'string',
            'languages' => 'array',
            'is_admin' => isAdminCast::class,
            'is_employer' => isEmployerCast::class,
            'is_employee' => isEmployeeCast::class,
            'is_talent' => 'bool',
            'last_login' => 'datetime'
        ];
    }

    /**
     * Interact with the user's Nationality.
     */
    protected function nationality(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }
    /**
     * Interact with the user's age,
     * whereas,
     * age should be calculated from date of birth.
     */
    public function age(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->date_of_birth)->age ?? '',
        );
    }

    /**
     * Get the user's full name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "{$this->firstname} {$this->lastname}",
        );
    }

    /**
     * Get the user's full name.
     */
    protected function shortName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => mb_substr($this->firstname, 0, 1) .". ". mb_substr($this->lastname, 0, 1) .".",
        );
    }

    /**
     * Talent can be favorite by companies
     *
     * @returns BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }


    /**
     * Check if User has Role
     *
     * @param $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if ($this->roles()->where('slug', '=', $role)->first()) {
            return true;
        }

        return false;
    }

    /**
     * Talent can be favorite by companies
     *
     * @returns BelongsToMany
     */
    public function favoriteByCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_favorite_user', 'user_id', 'company_id');
    }

    /**
     * Talent's preferred locations
     *
     * @returns MorphMany
     */
    public function workLocations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    /**
     * User has One Talent
     *
     * @return HasOne
    */
    public function talent(): HasOne
    {
        return $this->hasOne(Talent::class);
    }

    /**
     * A representative can have a registered company
     *
     * @return HasOne
    */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Let's encode/decode all languages for the projects.
     *
     * @return Attribute
     */
//    public function languages(): Attribute
//    {
//        return Attribute::make(
//            get: function ($value) {
//                // Decode the JSON and map to enum names
//                $decoded = json_decode($value, true);
//                return !is_array($decoded) ? null: array_map(fn($val) => LangEnum::toName($val), $decoded);
//            },
//            set: function ($value) {
//                // If setting from an array of enum values, encode it as JSON
//                return json_encode($value);
//            }
//        );
//    }


    /**
     * Get the user's full name.
     */
    protected function lastLogin(): Attribute
    {
        return Attribute::make(
            get: fn () => optional(
                $session = \DB::table('sessions')
                    ->where('user_id', $this->id)
                    ->orderBy('last_activity', 'desc')
                    ->first()
            )->last_activity ? Carbon::createFromTimestamp(optional($session)->last_activity)->diffForHumans() : null
        );
    }
}
