<?php

namespace App\Models;

use App\Http\Traits\FormatNumberTrait;
use App\Http\Traits\HasCompanyLogoTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class Company extends Model
{
    use HasFactory, SoftDeletes, FormatNumberTrait, HasCompanyLogoTrait, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "company_logo",
        "company_name",
        "company_email",
        "industry",
        "company_url",
        "company_location",
        "telephone_number",
        "established",
        "number_of_employees",
        "capital",
        "dispatch_business_license",
        "area_of_expertise",
        "specialized_industries",
        "introduction",
        "company_information_disclose",
        "user_id",
        "updater_id",
        "deleter_id"
    ];

    /**
     * Route notifications for the mail channel.
     *
     * @return  array<string, string>|string
     */
    public function routeNotificationForMail(Notification $notification): array|string
    {
        // Return email address only...
        return $this->company_email;
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'company_logo_url' => 'string',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capital' => 'string',
        ];
    }

    /**
     * A particular company belongs to the user table i.e. company owner
     *
     * @return BelongsTo
    */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A company can have multiple projects.
     *
     * @returns HasMany
    */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * A company can have multiple projects.
     *
     * @returns HasMany
    */
    public function talents(): HasMany
    {
        return $this->hasMany(Talent::class);
    }

    /**
     * A company can have multiple addresses.
     *
     * @returns HasMany
    */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * A company can have multiple favourite Projects
     *
     * @returns BelongsToMany
    */
    public function favoriteProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'company_favorite_project', 'company_id', 'project_id');
    }

    /**
     * A company can have multiple favourite Talents
     *
     * @returns BelongsToMany
    */
    public function favoriteTalent(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_favorite_user', 'company_id', 'user_id');
    }

    /**
     * Let's fetch salary range min-max
     *
     * @return Attribute
     */
    public function capital(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "{$this->formatNumber($value)}",
        );
    }
}
