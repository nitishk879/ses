<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Lang;

/**
 * A reference row whose name has to be readable in the language of the page.
 *
 * Locations, categories and sub-categories are all the same shape: a row seeded
 * once from a fixed list, carrying an English `title` and a `slug` that nobody
 * ever sees. Every screen printed `title` straight out, so those lists stayed in
 * English however the interface was set — and the fix is the same each time, so
 * it is written once here rather than three times.
 *
 * Translating rather than rewriting the column is deliberate. These rows are
 * pointed at by projects and talents, the slug is already stable and unique, and
 * one row per real-world thing is what keeps a filter working across languages.
 * A second column, or a second row per language, would put the English and the
 * Japanese out of step the first time somebody edited one of them.
 *
 * Implementors declare where their translations live:
 *
 *     protected const TITLE_TRANSLATIONS = 'locations';
 *
 * Read the result as `$model->display_title`. `title` keeps meaning the raw
 * column, so queries, slugs, seeds and exports are untouched.
 */
trait HasTranslatedTitle
{
    /**
     * This row's name in the language the reader is using.
     *
     * Falls back to the stored title when the slug has no entry — an
     * untranslated word is readable, and a missing translation rendering as
     * nothing is the failure that made the commercial-flow dropdown look empty.
     */
    protected function displayTitle(): Attribute
    {
        return Attribute::get(function (): string {
            $key = static::TITLE_TRANSLATIONS.'.'.$this->slug;

            // Current locale only. With the fallback locale enabled an
            // English-only entry would be served to a Japanese reader as
            // though it were a translation, which is the bug this removes.
            return Lang::has($key, null, false)
                ? (string) __($key)
                : (string) $this->title;
        });
    }
}
