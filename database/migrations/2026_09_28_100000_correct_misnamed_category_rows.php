<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Categories 5 and 6 carry the wrong names.
 *
 * `sub_categories.json` puts Direction, SEO, Email, Social Media, Listing,
 * Affiliate and Movie under category 5, and Web system, UI/UX, Character and
 * Graphic under category 6. Those are Web Marketing and Design. But
 * `categories.json` has "PMO" and "Bridge (SE)" in those two slots, so the
 * registration form drew seven web-marketing checkboxes under a PMO heading and
 * four design ones under Bridge SE — every recruiter tagging a project has been
 * choosing from mislabelled groups.
 *
 * `lang/{en,jp}/common/category.php` settles what was intended: it has
 * `web_marketing` and `design` keys, translated in both languages, that nothing
 * referenced. The two names were overwritten in the dataset at some point and
 * the translations were left behind.
 *
 * Done as a migration, not by re-seeding. CategorySeeder truncates the table,
 * which would cut every `project_sub_category` and talent link that points at
 * these rows. Renaming two rows in place keeps every existing tag intact —
 * those tags were always correct, it was only the heading above them that lied.
 *
 * Guarded on the current slug, so it is a no-op on a database that is already
 * right and safe to run twice.
 */
return new class extends Migration
{
    /** [id, slug it should not have, correct slug, correct title] */
    private const CORRECTIONS = [
        [5, 'pmo', 'web_marketing', 'Web Marketing'],
        [6, 'bridge', 'design', 'Design'],
    ];

    public function up(): void
    {
        foreach (self::CORRECTIONS as [$id, $wrongSlug, $slug, $title]) {
            DB::table('categories')
                ->where('id', $id)
                ->where('slug', $wrongSlug)
                ->update(['slug' => $slug, 'title' => $title, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (self::CORRECTIONS as [$id, $wrongSlug, $slug, $title]) {
            DB::table('categories')
                ->where('id', $id)
                ->where('slug', $slug)
                ->update([
                    'slug' => $wrongSlug,
                    'title' => $wrongSlug === 'pmo' ? 'PMO' : 'Bridge (SE)',
                    'updated_at' => now(),
                ]);
        }
    }
};
