<?php

namespace App\Support;

use App\Enums\LangEnum;

/**
 * Turn a parsed resume into values for the talent form.
 *
 * The point of this class is that mapping a CV onto a form is a judgement
 * call, not a rename, and judgement calls belong somewhere they can be read
 * and argued with rather than scattered through a controller and a script tag.
 *
 * Two rules run through all of it:
 *
 * * **Only what the document says.** The parser already drops anything it
 *   cannot find verbatim in the source. Nothing here adds a default, a
 *   placeholder or a guess — a field the CV is silent about comes back absent,
 *   and the person filling the form sees an empty input, which is the truth.
 * * **Suggest, never decide.** Every value here is going to be read, corrected
 *   and submitted by a human. That is why the result also names which fields
 *   were touched: a silently pre-filled form is one where a wrong extraction
 *   gets saved because nobody knew to look at it.
 */
class ResumeFormSuggestions
{
    /**
     * @param  array<string, mixed>  $parsed  a ParsedResume payload
     * @return array{fields: array<string, mixed>, filled: array<int, string>, unmapped_skills: array<int, string>}
     */
    public static function fromParsedResume(array $parsed): array
    {
        $fields = array_filter([
            'firstname' => self::givenName($parsed),
            'lastname' => self::familyName($parsed),
            'email' => self::text($parsed, 'contact.email'),
            // The number the screening call gets placed to. Extracted and
            // verified against the document by the parser, so what arrives
            // here occurs verbatim on the CV — it is not a reconstruction.
            'phone' => self::text($parsed, 'contact.phone'),
            'work_experience' => self::experienceYears($parsed),
            'education' => self::educationHtml($parsed),
            'experience' => self::experienceHtml($parsed),
            'cover_letter' => self::coverLetterHtml($parsed),
            'language' => self::language($parsed),
            'subcategory' => self::subCategoryIds($parsed),
        ], static fn ($value) => $value !== null && $value !== [] && $value !== '');

        return [
            'fields' => $fields,
            'filled' => array_keys($fields),
            // Skills the CV states that SES has no category for. Worth showing:
            // they are the candidate's real strengths, and the form has no box
            // for them, so they would otherwise vanish between the two systems.
            'unmapped_skills' => array_values(array_filter(
                (array) ($parsed['unmapped_skills'] ?? []),
                static fn ($s) => is_string($s) && trim($s) !== ''
            )),
        ];
    }

    // ── name ─────────────────────────────────────────────────────────────── #

    private static function givenName(array $parsed): ?string
    {
        return self::text($parsed, 'contact.given_name')
            ?? self::splitFullName($parsed)[1];
    }

    private static function familyName(array $parsed): ?string
    {
        return self::text($parsed, 'contact.family_name')
            ?? self::splitFullName($parsed)[0];
    }

    /**
     * Last resort when the parser returned a whole name but no parts.
     *
     * Family name first, which is the written order in Japanese and the order
     * a Japanese resume prints. Anything that is not cleanly two parts is left
     * alone rather than guessed at — a three-part Western name split by this
     * rule would put the wrong word in both boxes, and a wrong name is worse
     * than an empty one because it looks answered.
     *
     * @return array{0: ?string, 1: ?string} [family, given]
     */
    private static function splitFullName(array $parsed): array
    {
        $full = self::text($parsed, 'contact.full_name');

        if ($full === null) {
            return [null, null];
        }

        // Handles the ideographic space a Japanese resume typically uses.
        $parts = preg_split('/[\s\x{3000}]+/u', $full, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return count($parts) === 2 ? [$parts[0], $parts[1]] : [null, null];
    }

    // ── the rest of the form ─────────────────────────────────────────────── #

    /**
     * Whole years, because the form asks for years.
     *
     * Rounded down: claiming 3 years' experience for someone with 30 months is
     * a claim the document does not make, and it is the recruiter's name on it.
     *
     * Under twelve months returns null rather than 0. A literal "0" is both a
     * pre-filled answer and a wrong one — it reads as "no experience" for a
     * candidate who has five months of it, and because the box is filled
     * nobody looks at it again. An empty box asks the question instead.
     */
    private static function experienceYears(array $parsed): ?int
    {
        $months = $parsed['total_experience_months'] ?? null;

        if (! is_numeric($months) || $months < 12) {
            return null;
        }

        return intdiv((int) $months, 12);
    }

    private static function educationHtml(array $parsed): ?string
    {
        $items = [];

        foreach ((array) ($parsed['education'] ?? []) as $row) {
            $line = self::joinNonEmpty([
                $row['institution'] ?? null,
                $row['degree'] ?? null,
                $row['field_of_study'] ?? null,
            ], ' — ');

            if ($line !== null) {
                $items[] = $line;
            }
        }

        return self::htmlList($items);
    }

    private static function experienceHtml(array $parsed): ?string
    {
        $items = [];

        foreach ((array) ($parsed['experiences'] ?? []) as $row) {
            $headline = self::joinNonEmpty([
                $row['role'] ?? null,
                $row['company'] ?? null,
            ], ' — ');

            if ($headline === null) {
                continue;
            }

            $period = self::joinNonEmpty([$row['start'] ?? null, $row['end'] ?? null], ' – ');
            if ($period !== null) {
                $headline .= ' ('.$period.')';
            }

            $line = '<strong>'.e($headline).'</strong>';

            $summary = self::clean($row['summary'] ?? null);
            if ($summary !== null) {
                $line .= '<br>'.e($summary);
            }

            // Already escaped piece by piece — htmlList() must not escape again.
            $items[] = new PreEscaped($line);
        }

        return self::htmlList($items);
    }

    /**
     * A first draft of the profile blurb, built from what the CV actually says.
     *
     * Not a written summary — the model is not asked to compose one, because a
     * generated paragraph in a candidate's voice is a claim nobody made. This
     * is the same extracted facts in a sentence the recruiter then edits.
     */
    private static function coverLetterHtml(array $parsed): ?string
    {
        $years = self::experienceYears($parsed);

        // `raw`, not `canonical`. The canonical form is the SES sub-category a
        // skill maps onto — "Ruby on Rails" and "Sidekiq" both canonicalise to
        // "Backend" — which is the right answer for the skill checkboxes and
        // the wrong one here: a blurb reading "Key skills: Backend, Backend,
        // Database" says less than the CV did. This line is about what the
        // candidate wrote.
        $topSkills = [];
        foreach ((array) ($parsed['skills'] ?? []) as $skill) {
            $name = self::clean($skill['raw'] ?? null) ?? self::clean($skill['canonical'] ?? null);

            if ($name !== null && ! in_array(mb_strtolower($name), array_map('mb_strtolower', $topSkills), true)) {
                $topSkills[] = $name;
            }
        }

        $latestRole = null;
        foreach ((array) ($parsed['experiences'] ?? []) as $row) {
            if (($latestRole = self::clean($row['role'] ?? null)) !== null) {
                break;
            }
        }

        $sentences = [];

        if ($latestRole !== null || $years !== null) {
            $sentences[] = trim(implode(' ', array_filter([
                $latestRole !== null ? e($latestRole).'.' : null,
                $years !== null ? $years.'+ years of professional experience.' : null,
            ])));
        }

        if ($topSkills !== []) {
            $sentences[] = 'Key skills: '.e(implode(', ', array_slice($topSkills, 0, 12))).'.';
        }

        $certifications = array_values(array_filter(array_map(
            [self::class, 'clean'],
            (array) ($parsed['certifications'] ?? [])
        )));

        if ($certifications !== []) {
            $sentences[] = 'Certifications: '.e(implode(', ', $certifications)).'.';
        }

        foreach ((array) ($parsed['languages'] ?? []) as $row) {
            $language = self::clean($row['language'] ?? null);
            if ($language === null) {
                continue;
            }
            $level = self::clean($row['level'] ?? null);
            $sentences[] = 'Language: '.e($language).($level !== null ? ' ('.e($level).')' : '').'.';
        }

        return $sentences === [] ? null : '<p>'.implode(' ', $sentences).'</p>';
    }

    /**
     * Map stated languages onto the form's single-choice dropdown.
     *
     * Deliberately conservative: only Japanese and English decide this, since
     * they are the two the option list can express. A CV listing neither
     * leaves the field alone rather than defaulting to Japanese — which would
     * be a fluency claim invented by this function.
     */
    private static function language(array $parsed): ?int
    {
        $japanese = $english = false;

        foreach ((array) ($parsed['languages'] ?? []) as $row) {
            $name = mb_strtolower((string) ($row['language'] ?? ''));

            if (str_contains($name, 'japan') || str_contains($name, '日本')) {
                $japanese = true;
            }
            if (str_contains($name, 'english') || str_contains($name, '英')) {
                $english = true;
            }
        }

        return match (true) {
            $japanese && $english => LangEnum::bl->value,
            $japanese => LangEnum::jp->value,
            $english => LangEnum::en->value,
            default => null,
        };
    }

    /**
     * @return array<int, int> SES sub_categories.id the CV's skills mapped to
     */
    private static function subCategoryIds(array $parsed): array
    {
        $ids = [];

        foreach ((array) ($parsed['skills'] ?? []) as $skill) {
            $id = $skill['sub_category_id'] ?? null;
            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    // ── small helpers ────────────────────────────────────────────────────── #

    private static function text(array $parsed, string $path): ?string
    {
        return self::clean(data_get($parsed, $path));
    }

    private static function clean(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value === '' ? null : $value;
    }

    /** @param array<int, string|null> $parts */
    private static function joinNonEmpty(array $parts, string $glue): ?string
    {
        $kept = array_values(array_filter(array_map([self::class, 'clean'], $parts)));

        return $kept === [] ? null : implode($glue, $kept);
    }

    /** @param array<int, string|PreEscaped> $items */
    private static function htmlList(array $items): ?string
    {
        if ($items === []) {
            return null;
        }

        $lis = array_map(
            static fn ($item) => '<li>'.($item instanceof PreEscaped ? (string) $item : e($item)).'</li>',
            $items
        );

        return '<ul>'.implode('', $lis).'</ul>';
    }
}
