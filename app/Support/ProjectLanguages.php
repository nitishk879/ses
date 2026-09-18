<?php

namespace App\Support;

use App\Enums\LangEnum;
use App\Models\Project;

/** The language requirements a recruiter set on the project form. */
final class ProjectLanguages
{
    /** Canonical name per language code. */
    private const NAMES = [
        1 => 'English',  // LangEnum::en
        2 => 'Japanese', // LangEnum::jp
    ];

    /**
     * Language names required by this project's form, in a stable order.
     *
     * @return array<int, string>
     */
    public static function forProject(Project $project): array
    {
        $names = [];

        foreach (self::codes($project) as $code) {
            // One radio value can mean two languages: "bilingual" expands to
            // English and Japanese.
            foreach (LangEnum::toArray($code) as $expanded) {
                if (isset(self::NAMES[$expanded])) {
                    $names[$expanded] = self::NAMES[$expanded];
                }
            }
        }

        ksort($names);

        return array_values($names);
    }

    /**
     * The raw codes on the column, whatever shape it was written in.
     *
     * @return array<int, int>
     */
    private static function codes(Project $project): array
    {
        $raw = $project->getRawOriginal('languages');

        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode((string) $raw, true);

        if ($decoded === null) {
            $decoded = $raw;
        }

        return array_values(array_filter(
            array_map(
                static fn ($v) => is_numeric($v) ? (int) $v : null,
                is_array($decoded) ? $decoded : [$decoded]
            ),
            static fn ($v) => $v !== null
        ));
    }
}
