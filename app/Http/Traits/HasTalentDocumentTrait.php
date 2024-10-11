<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasTalentDocumentTrait
{

    /**
     * Update the user's profile photo.
     *
     * @param UploadedFile $document
     * @param string $storagePath
     * @return void
     */
    public function updateTalentDocument(UploadedFile $document, string $storagePath = 'talents/'): void
    {
        tap($this->document, function ($previous) use ($document, $storagePath) {
            $this->fill([
                'resume' => $document->storePubliclyAs(
                    $storagePath,
                    Str::slug($this->user->name, '-') . '.'.$document->getClientOriginalExtension(),
                    ['disk' => $this->talentDocumentDisk()]
                ),
            ]);

            if ($previous) {
                Storage::disk($this->talentDocumentDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the user's profile photo.
     *
     * @return void
     */
    public function deleteTalentDocument(): void
    {
        if (is_null($this->document)) {
            return;
        }

        Storage::disk($this->talentDocumentDisk())->delete($this->document);

        $this->fill([
            'resume' => null,
        ]);
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return Attribute
     */
    public function talentDocumentUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->document
                ? Storage::disk($this->talentDocumentDisk())->url($this->document)
                : $this->defaultTalentDocumentUrl();
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultTalentDocumentUrl(): string
    {
        $name = trim(collect(explode(' ', $this->document))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function talentDocumentDisk(): string
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.profile_photo_disk', 'public');
    }

}
