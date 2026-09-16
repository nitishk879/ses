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
            // The rest of the 履歴書 header. A Japanese resume prints date of
            // birth, gender and address every time, so leaving them out meant
            // the recruiter still retyped half the identity block from a
            // document the parser had already read.
            'date_of_birth' => self::text($parsed, 'contact.date_of_birth'),
            'gender' => self::gender($parsed),
            'nationality' => self::nationality($parsed),
            'address' => self::text($parsed, 'contact.address'),
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
     * Three paragraphs, in descending order of how much of the candidate is in
     * them:
     *
     * 1. The resume's own profile section (職務要約 / 自己PR / Summary), verbatim.
     *    A CV that has one has already written this blurb, in the candidate's
     *    voice, better than any rearrangement of extracted fields — and the
     *    parser has verified it occurs in the document.
     * 2. The description of the most recent role, again the CV's own wording.
     *    This is what makes the draft say something concrete; without it a CV
     *    with no profile section produced nothing but a list of skill names.
     * 3. The extracted facts: current title, years, skills, certifications,
     *    languages.
     *
     * Still nothing composed. The model is never asked to *write* a summary,
     * because a generated paragraph in a candidate's voice is a claim nobody
     * made — it is asked to copy the one the candidate wrote, or return
     * nothing. Every sentence below is either quoted from the document or
     * assembled from fields already shown elsewhere on the form.
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

        // The experiences arrive newest first, so the first row with a role is
        // the current one — and its description is what the candidate is
        // actually doing today, which is the single most useful line in a blurb.
        $latestRole = $latestWork = null;
        foreach ((array) ($parsed['experiences'] ?? []) as $row) {
            if (($latestRole = self::clean($row['role'] ?? null)) !== null) {
                $latestWork = self::clean($row['summary'] ?? null);
                break;
            }
        }

        $paragraphs = [];

        // The candidate's own profile section, when the CV has one.
        $summary = self::clean($parsed['summary'] ?? null);
        if ($summary !== null) {
            $paragraphs[] = e($summary);
        }

        // Skipped when the profile section already contains it — some resumes
        // open with a summary that is the current role's description repeated,
        // and printing it twice makes the draft look automated, which is
        // exactly the thing that stops a recruiter from trusting the rest.
        if ($latestWork !== null && ($summary === null || ! self::contains($summary, $latestWork))) {
            $paragraphs[] = e($latestWork);
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

        if ($sentences !== []) {
            $paragraphs[] = implode(' ', $sentences);
        }

        return $paragraphs === []
            ? null
            : '<p>'.implode('</p><p>', $paragraphs).'</p>';
    }

    /**
     * Whether one passage already says what another says.
     *
     * Compared with case, whitespace and the full-width/half-width difference
     * folded away, because the same sentence lifted from a Japanese resume into
     * its own summary is routinely re-typed with different spacing.
     */
    private static function contains(string $haystack, string $needle): bool
    {
        // mb_convert_kana over Normalizer::FORM_KC on purpose: mbstring is a
        // Laravel requirement, intl is not declared in composer.json, and this
        // is not worth an undeclared extension. 'a' folds full-width latin and
        // digits down to half-width, which is the difference that actually
        // shows up between a Japanese resume's body and its summary.
        $fold = static fn (string $s): string => preg_replace(
            '/[\s\x{3000}]+/u', '', mb_strtolower(mb_convert_kana($s, 'a'))
        ) ?? '';

        $needle = $fold($needle);

        return $needle !== '' && str_contains($fold($haystack), $needle);
    }

    /**
     * The parser returns a closed set, so this only has to guard the contract.
     *
     * Anything outside it is dropped rather than coerced: a gender the form
     * cannot represent is better left for the person to answer than mapped to
     * whichever option happens to be nearest.
     */
    private static function gender(array $parsed): ?string
    {
        $value = mb_strtolower((string) self::text($parsed, 'contact.gender'));

        return in_array($value, ['male', 'female', 'other'], true) ? $value : null;
    }

    /**
     * The form offers exactly two nationalities, so everything else is "other".
     *
     * Note that "other" is a real answer here, not a fallback for "unknown" —
     * a CV stating Vietnamese nationality genuinely means the non-Japanese
     * option. A CV that states nothing returns null and leaves the field alone.
     */
    private static function nationality(array $parsed): ?string
    {
        $value = self::text($parsed, 'contact.nationality');

        if ($value === null) {
            return null;
        }

        $value = mb_strtolower($value);

        return (str_contains($value, 'japan') || str_contains($value, '日本'))
            ? 'japanese'
            : 'other';
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
