<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Give every project a URL key.
 *
 * `slug` is Project::getRouteKeyName(), and it was filled with
 * `Str::slug($title)`, which drops every character it cannot romanise. A title
 * written in Japanese slugs to an empty string — and on this site that is every
 * real title. Two such projects collide, `route('project.show', $project)`
 * throws UrlGenerationException for a missing parameter, and the project list
 * returns a 500 while drawing a link to one of them.
 *
 * Rows already in that state cannot fix themselves, because the slug is written
 * once at creation. This backfills them with the id, which is the shortest key
 * that is guaranteed to exist and guaranteed to be unique. Projects whose title
 * does romanise keep their readable slug, so no working URL changes.
 *
 * Also repairs duplicates: `unique:projects` guards the title, never the slug,
 * so two different Latin titles could already share one ("Web System" and
 * "Web-System" both give web-system). The first row keeps the slug; later ones
 * take a numeric suffix, oldest first, so the longest-lived URL is the one that
 * survives.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')
            ->select('id', 'slug')
            ->orderBy('id')
            ->whereNull('deleted_at')
            ->chunkById(200, function ($projects) {
                foreach ($projects as $project) {
                    if (trim((string) $project->slug) === '') {
                        DB::table('projects')
                            ->where('id', $project->id)
                            ->update(['slug' => (string) $project->id]);
                    }
                }
            });

        $this->deduplicate();
    }

    /** Later rows sharing a slug take a suffix; the oldest keeps the URL. */
    private function deduplicate(): void
    {
        $duplicated = DB::table('projects')
            ->whereNull('deleted_at')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        foreach ($duplicated as $slug) {
            $ids = DB::table('projects')
                ->where('slug', $slug)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->pluck('id')
                ->slice(1);

            foreach ($ids as $id) {
                DB::table('projects')->where('id', $id)->update(['slug' => "{$slug}-{$id}"]);
            }
        }
    }

    /**
     * Not reversed.
     *
     * The previous state was an empty or duplicated slug, which is a broken URL
     * and a 500 on the list page. Restoring it would be restoring the fault,
     * and nothing downstream depends on these rows having no key.
     */
    public function down(): void
    {
        //
    }
};
