<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasCompanyLogoTrait
{

    /**
     * Update the user's profile photo.
     *
     * @param UploadedFile $photo
     * @param string $storagePath
     * @return void
     */
    public function updateCompanyLogo(UploadedFile $photo, string $storagePath = 'companies/'): void
    {
        tap($this->company_logo, function ($previous) use ($photo, $storagePath) {
            $this->fill([
                'company_logo_url' => $photo->storePubliclyAs(
                    $storagePath,
                    Str::slug($this->state['company_name'], '-') . '.'.$photo->getClientOriginalExtension(),
                    ['disk' => $this->companyLogoDisk()]
                ),
            ]);

            if ($previous) {
                Storage::disk($this->companyLogoDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the user's profile photo.
     *
     * @return void
     */
    public function deleteCompanyLogo(): void
    {
        if (is_null($this->company_logo)) {
            return;
        }

        Storage::disk($this->companyLogoDisk())->delete($this->company_logo);

        $this->fill([
            'company_logo_url' => null,
        ]);
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return Attribute
     */
    public function CompanyLogoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->company_logo
                ? Storage::disk($this->companyLogoDisk())->url($this->company_logo)
                : $this->defaultCompanyLogoUrl();
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultCompanyLogoUrl(): string
    {
        $name = trim(collect(explode(' ', $this->company_name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function companyLogoDisk(): string
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.profile_photo_disk', 'public');
    }

}
