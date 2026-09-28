<?php

namespace App\Http\Traits;

trait FormatNumberTrait
{
    /**
     * Shorten a money figure for display, in the reader's own counting system.
     *
     * English groups digits in thousands: 500,000 is "500K" and 1,000,000 is
     * "1M". Japanese does not — it groups in ten-thousands, so the same two
     * figures are 50万 and 100万. Writing "500K - 1M" on a Japanese page is not
     * only English lettering, it is the wrong arithmetic: a reader has to
     * convert the grouping in their head before the number means anything, and
     * on a rate card that is the one number they came for.
     *
     * 万 is 10^4 and 億 is 10^8. There is no unit between them, which is why
     * this is not a translation of the K/M/B ladder but a different ladder.
     *
     * Falls back to the English form for every other locale, so nothing outside
     * Japanese changes.
     *
     * @param  int|float|string|null  $number
     */
    public function formatNumber($number): string
    {
        $number = (float) $number;

        return app()->getLocale() === 'jp'
            ? $this->formatJapaneseNumber($number)
            : $this->formatWesternNumber($number);
    }

    /** 億 (10^8) and 万 (10^4) — the units a Japanese reader counts in. */
    private function formatJapaneseNumber(float $number): string
    {
        if ($number >= 100000000) {
            return $this->trim($number / 100000000).'億';
        }

        if ($number >= 10000) {
            return $this->trim($number / 10000).'万';
        }

        return $this->trim($number);
    }

    private function formatWesternNumber(float $number): string
    {
        if ($number >= 1000000000) {
            return $this->trim($number / 1000000000).'B'; // Billions
        }

        if ($number >= 1000000) {
            return $this->trim($number / 1000000).'M'; // Millions
        }

        if ($number >= 1000) {
            return $this->trim($number / 1000).'K'; // Thousands
        }

        return $this->trim($number);
    }

    /**
     * One decimal place, and none at all when it would be a zero.
     *
     * `round()` returns a float, and echoing 50.0 gives "50" on most builds and
     * "50.0" on others depending on `precision`. Stating it here means 50万
     * rather than 50.0万 wherever this runs.
     */
    private function trim(float $value): string
    {
        $rounded = round($value, 1);

        return rtrim(rtrim(number_format($rounded, 1, '.', ''), '0'), '.');
    }
}
