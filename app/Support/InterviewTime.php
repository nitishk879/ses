<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\App;

/**
 * How an interview time is written, everywhere it is written.
 *
 * This exists because the format string was copied into seven places — the
 * invitation email, the slot picker, the confirmation page, two dashboard
 * views, the slot model and a controller. Seven copies of a decision is seven
 * chances to change six of them, and the one that gets missed is the one in
 * the email, which is the only one a candidate ever reads.
 *
 * Two rules the format has to satisfy:
 *
 * * **Never ambiguous.** A bare "2:00" on a phone in another country is a
 *   missed interview. The hour always carries AM/PM (or 午前/午後), and the
 *   line always carries the zone abbreviation.
 * * **Written the way the reader writes time.** English readers expect
 *   "2:00 PM"; Japanese business writing puts the era-less date first and the
 *   period before the hour. Rendering one convention to both audiences makes
 *   the email look machine-generated in at least one of them.
 */
class InterviewTime
{
    /**
     * A complete, unambiguous date and time — what goes in the email.
     *
     * en: `Mon, 15 Sep 2026 — 2:00 PM (JST)`
     * jp: `2026年9月15日(月) 午後2:00 (JST)`
     */
    public static function full(?CarbonInterface $when, string $timezone): string
    {
        if (! $when) {
            return '—';
        }

        $local = self::localise($when, $timezone);

        return self::isJapanese()
            ? $local->translatedFormat('Y年n月j日(D) ').self::clock($local).' ('.$local->format('T').')'
            : $local->translatedFormat('D, j M Y').' — '.self::clock($local).' ('.$local->format('T').')';
    }

    /**
     * The same, without the year — for tables where the year is obvious from
     * context and the column is narrow.
     *
     * en: `15 Sep, 2:00 PM (JST)`
     * jp: `9月15日 午後2:00 (JST)`
     */
    public static function short(?CarbonInterface $when, string $timezone): string
    {
        if (! $when) {
            return '—';
        }

        $local = self::localise($when, $timezone);

        return self::isJapanese()
            ? $local->translatedFormat('n月j日 ').self::clock($local).' ('.$local->format('T').')'
            : $local->translatedFormat('j M').', '.self::clock($local).' ('.$local->format('T').')';
    }

    /**
     * Just the clock part, with the meridiem.
     *
     * `g:i A` and not `H:i`: 24-hour time is unambiguous but it is not what
     * the AM/PM half of the world reads fluently, and a time that has to be
     * converted in the reader's head is a time that gets converted wrongly.
     *
     * Japanese puts the period *before* the hour — 午後2:00, never 2:00午後 —
     * so it cannot be produced by reordering the English format.
     */
    public static function clock(CarbonInterface $local): string
    {
        if (self::isJapanese()) {
            return ($local->hour < 12 ? '午前' : '午後').$local->format('g:i');
        }

        return $local->format('g:i A');
    }

    /**
     * The instant in the reader's zone, with a locale Carbon understands.
     *
     * This app's locale is `jp`; Carbon's Japanese locale is `ja`. Left alone,
     * `translatedFormat('D')` silently falls back to English and a Japanese
     * email reads `2026年9月21日(Mon)` — the one word in the line that is not
     * Japanese, in the one place a candidate is looking for the day.
     */
    private static function localise(CarbonInterface $when, string $timezone): CarbonInterface
    {
        return $when->copy()
            ->setTimezone($timezone)
            ->locale(self::isJapanese() ? 'ja' : App::getLocale());
    }

    private static function isJapanese(): bool
    {
        return str_starts_with(App::getLocale(), 'j');
    }
}
